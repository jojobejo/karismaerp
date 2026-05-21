<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_SalesOrder.php  — REVISI ALUR BARU
 *
 * PERUBAHAN UTAMA:
 *  - SO tidak lagi memiliki no_faktur. No_faktur ada di tbso_faktur_penjualan.
 *  - SO memiliki status: draft → open → completed | cancelled
 *  - Faktur Penjualan dibuat dari SO yang berstatus open (bisa >1 faktur per SO).
 *  - Pengiriman parsial: qty_order vs qty_faktur vs qty_outstanding.
 *  - Qty Reserved berjalan di tberp_stock_batch: saat SO direkam → RESERVE,
 *    saat difakturkan → OUT dan qty_reserved ikut berkurang.
 *  - SO dianggap Completed apabila seluruh qty_outstanding = 0.
 *
 * TABEL BARU yang dibutuhkan:
 *  - tbso_faktur_penjualan  : header faktur (no_faktur, id_so, ...)
 *  - tbso_faktur_detail     : detail faktur (per baris barang per faktur)
 *  - tbso_sales_order       : HAPUS kolom no_faktur (sudah pindah ke tabel faktur)
 *  - tbso_sales_order_detail: tambah kolom qty_faktur, qty_outstanding
 *
 * DDL RINGKAS (jalankan sekali di DB):
 * -----------------------------------------------------------------------
 * ALTER TABLE tbso_sales_order
 *   DROP COLUMN IF EXISTS no_faktur,
 *   MODIFY COLUMN status ENUM('draft','open','completed','cancelled')
 *     NOT NULL DEFAULT 'draft';
 *
 * ALTER TABLE tbso_sales_order_detail
 *   ADD COLUMN qty_faktur    DECIMAL(12,3) NOT NULL DEFAULT 0
 *     COMMENT 'total qty yang sudah dibuat faktur',
 *   ADD COLUMN qty_outstanding DECIMAL(12,3) GENERATED ALWAYS AS
 *     (qty - qty_faktur) STORED
 *     COMMENT 'sisa qty belum difakturkan';
 *
 * CREATE TABLE IF NOT EXISTS tbso_faktur_penjualan (
 *   id_faktur       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *   no_faktur       VARCHAR(30) NOT NULL UNIQUE,
 *   id_so           INT UNSIGNED NOT NULL,
 *   no_so           VARCHAR(30) NOT NULL,
 *   kd_customer     VARCHAR(20) NOT NULL,
 *   customer_name   VARCHAR(100),
 *   gudang_id       VARCHAR(10),
 *   tanggal_faktur  DATE NOT NULL,
 *   total_tonase    DECIMAL(12,6) DEFAULT 0,
 *   total_kubikasi  DECIMAL(12,6) DEFAULT 0,
 *   catatan         TEXT,
 *   status          ENUM('draft','confirmed','cancelled') DEFAULT 'draft',
 *   create_by       VARCHAR(50),
 *   create_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
 *   update_by       VARCHAR(50),
 *   update_at       DATETIME ON UPDATE CURRENT_TIMESTAMP,
 *   INDEX idx_id_so (id_so),
 *   INDEX idx_no_faktur (no_faktur)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 * CREATE TABLE IF NOT EXISTS tbso_faktur_detail (
 *   id_faktur_detail INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *   id_faktur        INT UNSIGNED NOT NULL,
 *   no_faktur        VARCHAR(30) NOT NULL,
 *   id_so            INT UNSIGNED NOT NULL,
 *   id_so_detail     INT UNSIGNED NOT NULL,
 *   kd_barang        VARCHAR(30) NOT NULL,
 *   nama_barang      VARCHAR(150),
 *   no_lot           VARCHAR(50),
 *   expired_date     DATE,
 *   qty              DECIMAL(12,3) NOT NULL,
 *   qty_box          DECIMAL(12,3) DEFAULT 0,
 *   qty_satuan       DECIMAL(12,3) DEFAULT 0,
 *   isi_per_box      INT DEFAULT 1,
 *   satuan           VARCHAR(20),
 *   hrg_satuan       DECIMAL(16,2) DEFAULT 0,
 *   hrg_pokok        DECIMAL(16,2) DEFAULT 0,
 *   disc             DECIMAL(5,2) DEFAULT 0,
 *   pajak            DECIMAL(5,2) DEFAULT 0,
 *   subtotal_before_disc DECIMAL(16,2) DEFAULT 0,
 *   subtotal_after_disc  DECIMAL(16,2) DEFAULT 0,
 *   total_harga      DECIMAL(16,2) DEFAULT 0,
 *   berat_gram       DECIMAL(12,4) DEFAULT 0,
 *   kubikasi_m3      DECIMAL(12,6) DEFAULT 0,
 *   tonase_satuan    DECIMAL(12,6) DEFAULT 0,
 *   kubikasi_satuan  DECIMAL(12,6) DEFAULT 0,
 *   gudang_id        VARCHAR(10),
 *   create_by        VARCHAR(50),
 *   INDEX idx_no_faktur (no_faktur),
 *   INDEX idx_id_so_detail (id_so_detail)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * -----------------------------------------------------------------------
 */

class M_SalesOrder extends CI_Model
{
    const BATAS_TONASE   = 6;
    const BATAS_KUBIKASI = 9;

    // ================================================================
    // HELPER — TANGGAL
    // ================================================================

    private function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if (!$raw) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m))
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        return $raw;
    }

    // YYYY-MM-DD → DD/MM/YYYY
    private function _toViewDate($ymd)
    {
        $ymd = $this->_normalizeDate($ymd);
        if (!$ymd) return '';
        $p = explode('-', $ymd);
        return count($p) === 3 ? $p[2] . '/' . $p[1] . '/' . $p[0] : $ymd;
    }

    private function _stockQtyColumn()
    {
        return $this->db->field_exists('qty', 'tberp_stock_batch') ? 'qty' : 'qty_on_hand';
    }

    // ================================================================
    // GENERATE NOMOR
    // ================================================================

    /**
     * Generate No. SO — format: SO/YYYYMM/XXXX
     */
    public function generate_no_so()
    {
        $prefix = 'SO/' . date('dmy') . '/';

        $row = $this->db
            ->like('no_so', $prefix, 'after')
            ->order_by('no_so', 'DESC')
            ->limit(1)
            ->get('tbso_sales_order')
            ->row();

        if ($row) {
            $last = (int)substr($row->no_so, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    /**
     * Generate No. Faktur — format: INV/YYYYMM/XXXX
     * Faktur sekarang hidup di tbso_faktur_penjualan.
     */
    public function generate_no_faktur()
    {
        $prefix = 'INV' . date('dmy');

        $row = $this->db
            ->like('no_faktur', $prefix, 'after')
            ->order_by('no_faktur', 'DESC')
            ->limit(1)
            ->get('tbso_faktur_penjualan')
            ->row();

        if ($row) {
            $last = (int)substr($row->no_faktur, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    // ================================================================
    // MASTER DATA
    // ================================================================

    public function get_customers()
    {
        return $this->db->order_by('nama_customer', 'ASC')
            ->get('tb_customer')
            ->result_array();
    }

    public function get_customer($id)
    {
        return $this->db->get_where('tb_customer', ['id' => $id])->row_array();
    }

    public function get_gudang_list()
    {
        return $this->db
            ->where('is_active', 1)
            ->where_in('tipe', ['INDUK'])
            ->order_by('nama_gudang', 'ASC')
            ->get('tb_gudang')
            ->result_array();
    }

    public function get_tax_list()
    {
        return $this->db->order_by('nm_tax', 'ASC')
            ->get('tb_set_tax')
            ->result_array();
    }

    public function get_approver_list()
    {
        return $this->db
            ->select('id, nm_karyawan, jobdesk, departemen')
            ->where('nm_karyawan !=', '')
            ->order_by('nm_karyawan', 'ASC')
            ->get('tb_karyawan')
            ->result_array();
    }

    // ================================================================
    // MASTER BARANG
    // ================================================================

    public function get_detail_barang($kd_barang)
    {
        $row = $this->db->get_where('tb_master_barang_all', ['kd_barang' => $kd_barang])->row_array();
        if (!$row) return null;
        return $this->_normalize_barang($row);
    }

    private function _get_master_bulk(array $kd_list)
    {
        if (empty($kd_list)) return [];
        $rows = $this->db->where_in('kd_barang', array_unique($kd_list))
            ->get('tb_master_barang_all')
            ->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['kd_barang']] = $this->_normalize_barang($r);
        }
        return $map;
    }

    private function _normalize_barang(array $row)
    {
        $berat = 0;
        foreach (['berat', 'berat_gram', 'weight', 'berat_satuan', 'gr'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $berat = (float)$row[$c];
                break;
            }
        }
        $kubikasi = 0;
        foreach (['kubikasi', 'kubikasi_m3', 'volume', 'kubik', 'cbm'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $kubikasi = (float)$row[$c];
                break;
            }
        }
        $hpp = 0;
        foreach (['hpp', 'harga_pokok', 'cost', 'cogs', 'h_pokok'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $hpp = (float)$row[$c];
                break;
            }
        }

        $p = (int)($row['p'] ?? $row['panjang'] ?? $row['length'] ?? 0);
        $l = (int)($row['l'] ?? $row['lebar']   ?? $row['width']  ?? 0);
        $t = (int)($row['t'] ?? $row['tinggi']  ?? $row['height'] ?? 0);

        $isi = 0;
        foreach (['isi_box', 'qty_isi', 'isi', 'isi_per_box', 'qty_per_box', 'jumlah_isi'] as $c) {
            if (array_key_exists($c, $row) && (int)$row[$c] > 0) {
                $isi = (int)$row[$c];
                break;
            }
        }
        if ($isi < 1 && $p > 0 && $l > 0 && $t > 0) $isi = $p * $l * $t;
        if ($isi < 1) $isi = 1;

        $satuan = '';
        foreach (['satuan', 'unit', 'uom', 'satuan_kecil'] as $c) {
            if (!empty($row[$c])) {
                $satuan = (string)$row[$c];
                break;
            }
        }

        $row['berat_gram']  = $berat;
        $row['kubikasi_m3'] = $kubikasi;
        $row['hpp']         = $hpp;
        $row['p']           = $p;
        $row['l']           = $l;
        $row['t']           = $t;
        $row['isi_per_box'] = $isi;
        $row['satuan']      = $satuan;

        return $row;
    }

    // ================================================================
    // STOK
    // ================================================================

    public function get_available_stock_with_dimensi($gudang_id = null, $kd_barang = null)
    {
        $gudang_id_str = !empty($gudang_id) ? (string)$gudang_id : null;
        $qty_col = $this->_stockQtyColumn();

        $this->db->select('sb.id AS stock_batch_id, sb.kd_barang, sb.gudang_id, sb.no_lot, sb.expired_date,
                           sb.' . $qty_col . ' AS qty_on_hand,
                           COALESCE(sb.qty_reserved, 0) AS qty_reserved,
                           (sb.' . $qty_col . ' - COALESCE(sb.qty_reserved, 0)) AS available_stock,
                           mb.nama_barang AS nama_barang', false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tb_master_barang_all mb', 'mb.kd_barang = sb.kd_barang', 'left');

        if (!empty($kd_barang))     $this->db->where('sb.kd_barang', $kd_barang);
        if (!empty($gudang_id_str)) {
            $this->db->where(
                "CAST(sb.gudang_id AS CHAR) = CAST('" . $this->db->escape_str($gudang_id_str) . "' AS CHAR)"
            );
        }

        $this->db->having('available_stock >', 0);
        $this->db->order_by('sb.kd_barang', 'ASC');
        $this->db->order_by('sb.no_lot', 'ASC');
        $this->db->order_by('sb.expired_date', 'ASC');
        $this->db->order_by('sb.id', 'ASC');

        $stocks = $this->db->get()->result_array();

        if (empty($stocks)) return [];

        $master = $this->_get_master_bulk(array_column($stocks, 'kd_barang'));

        foreach ($stocks as &$row) {
            $kd = $row['kd_barang'];
            $m  = $master[$kd] ?? [];

            if (empty($row['nama_barang']) && !empty($m['nama_barang'])) {
                $row['nama_barang'] = $m['nama_barang'];
            }

            $row['berat_gram']  = $m['berat_gram']  ?? 0;
            $row['kubikasi_m3'] = $m['kubikasi_m3'] ?? 0;
            $row['hpp']         = $m['hpp']         ?? 0;
            $row['p']           = $m['p']           ?? 0;
            $row['l']           = $m['l']           ?? 0;
            $row['t']           = $m['t']           ?? 0;
            $row['isi_per_box'] = $m['isi_per_box'] ?? 1;
            $row['satuan']      = $m['satuan']      ?? ($row['satuan'] ?? '');

            if (isset($row['expired_date'])) {
                $row['exp_date'] = $this->_normalizeDate($row['expired_date']);
                $row['gudang']   = (string)($row['gudang_id'] ?? '');
            } else {
                $row['exp_date'] = $this->_normalizeDate($row['exp_date'] ?? '');
                $row['gudang']   = (string)($row['gudang'] ?? '');
            }

            $row['gudang_id'] = (string)($row['gudang_id'] ?? $row['gudang'] ?? '');

            $av  = (float)($row['available_stock'] ?? 0);
            $isi = max(1, (int)$row['isi_per_box']);

            $row['available_box']  = (int)floor($av / $isi);
            $row['available_ecer'] = (int)fmod($av, $isi);
        }
        unset($row);

        return $stocks;
    }

    /**
     * Cek stok satu item dari tberp_stock_batch.
     */
    public function cek_stock($kd_barang, $exp_date, $gudang_id, $no_lot = null)
    {
        $ymd = $this->_normalizeDate($exp_date);
        $qty_col = $this->_stockQtyColumn();

        $sql = "SELECT kd_barang,
                       MAX(gudang_id) AS gudang_id,
                       " . (!empty($no_lot) ? "MAX(no_lot)" : "NULL") . " AS no_lot,
                       expired_date,
                       SUM({$qty_col}) AS qty_on_hand,
                       SUM(COALESCE(qty_reserved, 0)) AS qty_reserved,
                       (SUM({$qty_col}) - SUM(COALESCE(qty_reserved, 0))) AS available_stock
                FROM tberp_stock_batch
                WHERE kd_barang = ? AND expired_date = ?";
        $params = [$kd_barang, $ymd];

        if (!empty($gudang_id)) {
            $sql .= " AND gudang_id = ?";
            $params[] = $gudang_id;
        }

        if (!empty($no_lot)) {
            $sql .= " AND no_lot = ?";
            $params[] = $no_lot;
        }

        $sql .= " GROUP BY kd_barang, expired_date
                LIMIT 1";
        return $this->db->query($sql, $params)->row_array();
    }

    // ================================================================
    // LIST SO
    // ================================================================

    public function get_all_so($filter = [])
    {
        $this->db->select('
            so.*,
            c.nama_customer,
            c.regional,
            COUNT(sd.id)                                                        AS jumlah_item,
            SUM(CASE WHEN (sd.qty - COALESCE(sd.qty_faktur, 0)) <= 0
                    THEN 1 ELSE 0 END)                                         AS jumlah_item_diterima,
            COALESCE(SUM(sd.qty), 0)                                            AS total_qty_order,
            COALESCE(SUM(sd.qty_faktur), 0)                                     AS total_qty_faktur,
            COALESCE(SUM(sd.qty_outstanding), 0)                                AS total_qty_outstanding
        ');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left');
        $this->db->join('tbso_sales_order_detail sd', 'sd.id_so = so.id_so', 'left');

        if (!empty($filter['status']))      $this->db->where('so.status', $filter['status']);
        if (!empty($filter['date1']))       $this->db->where('so.tanggal_transaksi >=', $filter['date1']);
        if (!empty($filter['date2']))       $this->db->where('so.tanggal_transaksi <=', $filter['date2']);
        if (!empty($filter['customer_id'])) $this->db->where('so.kd_customer', $filter['customer_id']);

        $this->db->group_by('so.id_so');
        $this->db->order_by('so.create_at', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_so($id_so)
    {
        $this->db->select('so.*, c.nama_customer, c.regional');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left');
        $this->db->where('so.id_so', $id_so);
        return $this->db->get()->row_array();
    }

    /**
     * Detail baris SO — termasuk qty_faktur & qty_outstanding.
     * PK tabel adalah `id`; di-alias menjadi `id_so_detail` agar
     * kode controller & view konsisten memakai nama id_so_detail.
     */
    public function get_so_detail($id_so)
    {
        // Alias id → id_so_detail agar tidak perlu ubah controller/view
        $rows = $this->db
            ->select('d.id AS id_so_detail, d.*')
            ->from('tbso_sales_order_detail d')
            ->where('d.id_so', $id_so)
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $row['berat_gram']      = (float)($row['berat_gram']       ?? 0);
            $row['kubikasi_m3']     = (float)($row['kubikasi_m3']      ?? 0);
            $row['hrg_pokok']       = (float)($row['hrg_pokok']        ?? 0);
            $row['disc']            = (float)($row['disc']             ?? 0);
            $row['qty_faktur']      = (float)($row['qty_faktur']       ?? 0);
            // qty_outstanding adalah GENERATED COLUMN; fallback manual jika null
            $row['qty_outstanding'] = (float)($row['qty_outstanding']
                ?? ($row['qty'] - $row['qty_faktur']));

            if (!isset($row['qty_box']) || $row['qty_box'] === null) {
                $isi               = max(1, (int)($row['isi_per_box'] ?? 1));
                $row['qty_box']    = floor((float)$row['qty'] / $isi);
                $row['qty_satuan'] = fmod((float)$row['qty'], $isi);
            }
        }
        unset($row);

        return $rows;
    }

    // ================================================================
    // SIMPAN SO (BARU — tanpa no_faktur)
    // ================================================================

    /**
     * Simpan Sales Order baru.
     * Status awal: 'draft'.
     * no_faktur TIDAK ada di SO — faktur dibuat terpisah lewat buat_faktur().
     * Qty Reserved tetap berjalan saat SO dibuat agar stok ter-lock.
     */
    public function simpan_so($header, $details)
    {
        $this->db->trans_start();

        // Header SO — pastikan tidak ada kolom no_faktur
        $so_data = [
            'no_so'             => $header['no_so'],
            'tanggal_transaksi' => $header['tanggal_transaksi'],
            'kd_customer'       => $header['kd_customer'],
            'customer_name'     => $header['customer_name'],
            'gudang_id'         => $header['gudang_id'],
            'batas_tonase'      => $header['batas_tonase'],
            'batas_kubikasi'    => $header['batas_kubikasi'],
            'total_tonase'      => $header['total_tonase'],
            'total_kubikasi'    => $header['total_kubikasi'],
            'status'            => 'draft',
            'catatan'           => $header['catatan'] ?? null,
            'create_by'         => $header['create_by'],
            'create_at'         => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbso_sales_order', $so_data);
        $id_so    = $this->db->insert_id();
        $no_so    = $header['no_so'];

        foreach ($details as $d) {
            $d['id_so']          = $id_so;
            $d['no_so']          = $no_so;
            $d['qty_faktur']     = 0;   // belum ada faktur
            // qty_outstanding = generated column di DB, tidak perlu diisi
            $this->db->insert('tbso_sales_order_detail', $d);

        }

        // Update jumlah_item
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id_so : false;
    }

    // ================================================================
    // REKAM SO (Draft → Open)
    // ================================================================

    /**
     * Mengubah status SO dari 'draft' menjadi 'open'.
     * SO berstatus open sudah dapat dibuatkan Faktur Penjualan.
     */
    public function rekam_so($id_so, $update_by)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so || $so['status'] !== 'draft') return false;

        $details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();

        $this->db->trans_start();

        foreach ($details as $d) {
            $reserved = $this->_reservasi_stok(
                $so['no_so'],
                $d['id'],
                $d['kd_barang'],
                $d['expired_date'],
                $d['no_lot'] ?? null,
                $so['gudang_id'],
                $d['qty']
            );

            if (!$reserved) {
                $this->db->trans_rollback();
                return false;
            }
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => 'open',
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ================================================================
    // UPDATE SO (hanya boleh saat Draft)
    // ================================================================

    public function update_so($id_so, $header, $details)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so || $so['status'] !== 'draft') return false;

        $this->db->trans_start();

        $no_so    = $so['no_so'];
        $gudang_id = $header['gudang_id'];

        // Update header
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'tanggal_transaksi' => $header['tanggal_transaksi'],
            'kd_customer'       => $header['kd_customer'],
            'customer_name'     => $header['customer_name'],
            'gudang_id'         => $gudang_id,
            'batas_tonase'      => $header['batas_tonase'],
            'batas_kubikasi'    => $header['batas_kubikasi'],
            'total_tonase'      => $header['total_tonase'],
            'total_kubikasi'    => $header['total_kubikasi'],
            'catatan'           => $header['catatan'] ?? null,
            'update_by'         => $header['update_by'],
            'update_at'         => date('Y-m-d H:i:s'),
        ]);

        // Hapus detail lama
        $this->db->delete('tbso_sales_order_detail', ['id_so' => $id_so]);

        // Insert detail baru. Draft belum menyentuh stok.
        foreach ($details as $d) {
            $d['id_so']      = $id_so;
            $d['no_so']      = $no_so;
            $d['qty_faktur'] = 0;
            $this->db->insert('tbso_sales_order_detail', $d);
        }

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', ['jumlah_item' => count($details)]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ================================================================
    // FAKTUR PENJUALAN
    // ================================================================

    /**
     * Ambil daftar faktur milik sebuah SO.
     */
    public function get_faktur_by_so($id_so)
    {
        return $this->db
            ->where('id_so', $id_so)
            ->order_by('create_at', 'ASC')
            ->get('tbso_faktur_penjualan')
            ->result_array();
    }

    /**
     * Ambil header faktur berdasarkan id_faktur.
     */
    public function get_faktur($id_faktur)
    {
        $this->db->select('f.*, c.nama_customer');
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->where('f.id_faktur', $id_faktur);
        return $this->db->get()->row_array();
    }

    /**
     * Ambil detail baris faktur.
     */
    public function get_faktur_detail($id_faktur)
    {
        return $this->db
            ->get_where('tbso_faktur_detail', ['id_faktur' => $id_faktur])
            ->result_array();
    }

    private function _pending_faktur_rute_sql($routeFilter = false)
    {
        $whereRoute = $routeFilter ? " AND COALESCE(NULLIF(c.kd_rute, ''), 'TANPA_RUTE') = ? " : "";

        return "
            SELECT
                f.id_faktur,
                f.no_faktur,
                f.no_so,
                f.kd_customer,
                COALESCE(f.customer_name, c.nama_customer) AS customer_name,
                f.tanggal_faktur,
                f.status,
                so.id_so,
                c.nama_kios,
                c.alamat_kios,
                c.regional,
                c.kd_rute AS kd_rute_customer,
                COALESCE(NULLIF(c.kd_rute, ''), 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                COUNT(DISTINCT fd.kd_barang) AS total_barang,
                SUM(fd.qty) AS total_qty,
                COALESCE(
                    NULLIF(f.total_tonase, 0),
                    ROUND(SUM(fd.qty * COALESCE(NULLIF(fd.berat_gram, 0), mb.berat, 0)) / 1000000, 6)
                ) AS total_tonase,
                COALESCE(
                    NULLIF(f.total_kubikasi, 0),
                    ROUND(SUM(fd.qty * COALESCE(NULLIF(fd.kubikasi_m3, 0), mb.kubikasi, 0)), 6)
                ) AS total_kubikasi
            FROM tbso_faktur_penjualan f
            JOIN tbso_faktur_detail fd ON fd.id_faktur = f.id_faktur
            LEFT JOIN tbso_sales_order so ON so.id_so = f.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = c.kd_rute
            LEFT JOIN tb_master_barang_all mb ON mb.kd_barang = fd.kd_barang
            WHERE f.status = 'confirmed'
            AND NOT EXISTS (
                SELECT 1 FROM tb_detail_do d
                WHERE d.kd_faktur = f.no_faktur
            )
            AND NOT EXISTS (
                SELECT 1 FROM tb_tmp_detaildo t
                WHERE t.kd_faktur = f.no_faktur
            )
            {$whereRoute}
            GROUP BY
                f.id_faktur, f.no_faktur, f.no_so, f.kd_customer,
                f.customer_name, f.tanggal_faktur, f.status,
                f.total_tonase, f.total_kubikasi, so.id_so,
                c.nama_customer, c.nama_kios, c.alamat_kios,
                c.regional, c.kd_rute, r.keterangan
        ";
    }

    public function get_pending_faktur_rute_summary()
    {
        $sql = "
            SELECT
                x.kd_rute,
                x.nama_rute,
                COUNT(*) AS total_faktur,
                ROUND(COALESCE(SUM(x.total_tonase), 0), 3) AS total_tonase,
                ROUND(COALESCE(SUM(x.total_kubikasi), 0), 4) AS total_kubikasi
            FROM (
                " . $this->_pending_faktur_rute_sql(false) . "
            ) x
            GROUP BY x.kd_rute, x.nama_rute
            ORDER BY x.kd_rute ASC
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_pending_faktur_by_rute($kd_rute = '')
    {
        $kd_rute = trim((string)$kd_rute);
        $params = [];
        $sql = $this->_pending_faktur_rute_sql($kd_rute !== '');

        if ($kd_rute !== '') {
            $params[] = $kd_rute;
        }

        $sql .= " ORDER BY tanggal_faktur DESC, no_faktur DESC";
        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Buat Faktur Penjualan dari SO yang sudah berstatus 'open'.
     *
     * $faktur_header: array berisi no_faktur, tanggal_faktur, catatan, create_by, dsb.
     * $faktur_items : array baris item. Setiap item WAJIB punya:
     *                 id_so_detail, kd_barang, qty (≤ qty_outstanding), ...
     *
     * Alur stok:
     *  1. OUT stok sejumlah qty faktur (stok keluar fisik).
     *  2. Kurangi qty_reserved di tberp_stock_batch.
     *  3. Tambah qty_faktur di tbso_sales_order_detail.
     *  5. Cek apakah semua outstanding = 0 → ubah status SO ke 'completed'.
     */
    public function buat_faktur($id_so, $faktur_header, $faktur_items)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so || $so['status'] !== 'open') return false;

        // Validasi: qty faktur tidak boleh melebihi outstanding
        $errors = $this->_validasi_qty_faktur($id_so, $faktur_items);
        if (!empty($errors)) return ['errors' => $errors];

        $this->db->trans_start();

        $no_faktur = $faktur_header['no_faktur'];
        $gudang_id = $so['gudang_id'];

        // ── Insert header faktur ────────────────────────────────────
        $fh = [
            'no_faktur'     => $no_faktur,
            'id_so'         => $id_so,
            'no_so'         => $so['no_so'],
            'kd_customer'   => $so['kd_customer'],
            'customer_name' => $so['customer_name'],
            'gudang_id'     => $gudang_id,
            'tanggal_faktur' => $faktur_header['tanggal_faktur'],
            'catatan'       => $faktur_header['catatan'] ?? null,
            'status'        => 'confirmed',
            'create_by'     => $faktur_header['create_by'],
            'create_at'     => date('Y-m-d H:i:s'),
        ];

        $optional_header_fields = [
            'tanggal_jatuh_tempo',
            'salesman',
            'cara_pembayaran',
            'jtempo',
            'tempo',
        ];
        foreach ($optional_header_fields as $field) {
            if ($this->db->field_exists($field, 'tbso_faktur_penjualan')) {
                $fh[$field] = $faktur_header[$field] ?? null;
            }
        }

        // Hitung total tonase & kubikasi faktur ini
        $total_tonase   = 0;
        $total_kubikasi = 0;
        foreach ($faktur_items as $item) {
            $total_tonase   += (float)$item['qty'] * ((float)($item['berat_gram'] ?? 0) / 1000000);
            $total_kubikasi += (float)$item['qty'] * (float)($item['kubikasi_m3'] ?? 0);
        }
        $fh['total_tonase']   = round($total_tonase, 6);
        $fh['total_kubikasi'] = round($total_kubikasi, 6);

        $this->db->insert('tbso_faktur_penjualan', $fh);
        $id_faktur = $this->db->insert_id();

        // ── Insert detail faktur + update stok ─────────────────────
        foreach ($faktur_items as $item) {
            $qty_item = (float)$item['qty'];

            $fd = [
                'id_faktur'           => $id_faktur,
                'no_faktur'           => $no_faktur,
                'id_so'               => $id_so,
                'id_so_detail'        => $item['id_so_detail'],
                'kd_barang'           => $item['kd_barang'],
                'nama_barang'         => $item['nama_barang']    ?? '',
                'no_lot'              => $item['no_lot']         ?? null,
                'expired_date'        => $this->_normalizeDate($item['expired_date'] ?? ''),
                'qty'                 => $qty_item,
                'qty_box'             => $item['qty_box']        ?? 0,
                'qty_satuan'          => $item['qty_satuan']     ?? 0,
                'isi_per_box'         => $item['isi_per_box']    ?? 1,
                'satuan'              => $item['satuan']         ?? '',
                'hrg_satuan'          => $item['hrg_satuan']     ?? 0,
                'hrg_pokok'           => $item['hrg_pokok']      ?? 0,
                'disc'                => $item['disc']           ?? 0,
                'pajak'               => $item['pajak']          ?? 0,
                'subtotal_before_disc' => $item['subtotal_before_disc'] ?? 0,
                'subtotal_after_disc'  => $item['subtotal_after_disc']  ?? 0,
                'total_harga'         => $item['total_harga']    ?? 0,
                'berat_gram'          => $item['berat_gram']     ?? 0,
                'kubikasi_m3'         => $item['kubikasi_m3']    ?? 0,
                'gudang_id'           => $gudang_id,
                'create_by'           => $faktur_header['create_by'],
            ];
            $this->db->insert('tbso_faktur_detail', $fd);

            $exp_normalized = $this->_normalizeDate($item['expired_date'] ?? '');

            // 1. Update tberp_stock_batch: kurangi qty fisik & qty_reserved
            try {
                $qty_col = $this->_stockQtyColumn();
                $this->db->where('kd_barang', $item['kd_barang']);
                $this->db->where('gudang_id', $gudang_id);
                if (!empty($item['no_lot']))  $this->db->where('no_lot', $item['no_lot']);
                if (!empty($exp_normalized))  $this->db->where('expired_date', $exp_normalized);
                $this->db->where($qty_col . ' >=', $qty_item);
                $this->db->where('qty_reserved >=', $qty_item);
                $this->db->set($qty_col, $qty_col . ' - ' . $qty_item, false);
                $this->db->set('qty_reserved', 'qty_reserved - ' . $qty_item, false);
                $this->db->set('update_at', date('Y-m-d H:i:s'));
                $this->db->update('tberp_stock_batch');

                if ($this->db->affected_rows() < 1) {
                    $this->db->trans_rollback();
                    return false;
                }

                $this->db->insert('tberp_stock_ledger', [
                    'kd_barang'    => $item['kd_barang'],
                    'gudang_id'    => $gudang_id,
                    'no_lot'       => $item['no_lot'] ?? null,
                    'expired_date' => $exp_normalized,
                    'qty'          => $qty_item,
                    'tipe'         => 'OUT',
                    'ref_no'       => $so['no_so'],
                    'ref_type'     => 'FAKTUR PENJUALAN',
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            } catch (Exception $e) {
                $this->db->trans_rollback();
                return false;
            }

            // 3. Tambah qty_faktur di SO detail
            $this->db->where('id', $item['id_so_detail']);
            $this->db->set('qty_faktur', 'qty_faktur + ' . $qty_item, false);
            $this->db->update('tbso_sales_order_detail');
        }

        // ── Cek apakah semua outstanding = 0 → Completed ────────────
        $this->_cek_dan_complete_so($id_so);

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id_faktur : false;
    }

    /**
     * Validasi bahwa qty faktur tidak melebihi qty_outstanding masing-masing detail.
     */
    private function _validasi_qty_faktur($id_so, array $faktur_items)
    {
        $errors = [];
        foreach ($faktur_items as $item) {
            $sd = $this->db->get_where('tbso_sales_order_detail', [
                'id' => $item['id_so_detail'],
                'id_so'        => $id_so,
            ])->row_array();

            if (!$sd) {
                $errors[] = "Item SO (id: {$item['id_so_detail']}) tidak ditemukan.";
                continue;
            }

            $outstanding = (float)$sd['qty'] - (float)$sd['qty_faktur'];
            $diminta     = (float)$item['qty'];

            if ($diminta <= 0) {
                $errors[] = "Qty faktur untuk <b>{$sd['nama_barang']}</b> harus lebih dari 0.";
            } elseif ($diminta > $outstanding) {
                $errors[] = "Qty faktur untuk <b>{$sd['nama_barang']}</b> melebihi outstanding. "
                    . "Outstanding: {$outstanding} pcs, Diminta: {$diminta} pcs.";
            }
        }
        return $errors;
    }

    /**
     * Periksa apakah semua baris SO sudah terpenuh (outstanding = 0).
     * Jika ya, ubah status SO menjadi 'completed'.
     */
    private function _cek_dan_complete_so($id_so)
    {
        $rows = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        $all_done = true;
        foreach ($rows as $r) {
            $outstanding = (float)$r['qty'] - (float)$r['qty_faktur'];
            if ($outstanding > 0.001) {
                $all_done = false;
                break;
            }
        }
        if ($all_done) {
            $this->db->where('id_so', $id_so);
            $this->db->update('tbso_sales_order', [
                'status'    => 'completed',
                'update_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ================================================================
    // HELPER STOK — reservasi
    // ================================================================

    /**
     * Reservasi stok: insert ke tberp_stock_ledger (RESERVE)
     *                 + update tberp_stock_batch.qty_reserved.
     */
    private function _reservasi_stok(
        $no_so, $id_so_detail,
        $kd_barang, $exp_date, $no_lot,
        $gudang_id, $qty
    ) {
        $exp_normalized = $this->_normalizeDate($exp_date);

        try {
            $qty_col = $this->_stockQtyColumn();
            $this->db->where('kd_barang', $kd_barang);
            $this->db->where('gudang_id', $gudang_id);
            if (!empty($no_lot))         $this->db->where('no_lot', $no_lot);
            if (!empty($exp_normalized)) $this->db->where('expired_date', $exp_normalized);
            $this->db->where('(' . $qty_col . ' - COALESCE(qty_reserved, 0)) >=', (float)$qty, false);
            $this->db->set('qty_reserved', 'qty_reserved + ' . (float)$qty, false);
            $this->db->set('update_at', date('Y-m-d H:i:s'));
            $this->db->update('tberp_stock_batch');
            if ($this->db->affected_rows() < 1) return false;

            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd_barang,
                'gudang_id'    => $gudang_id,
                'no_lot'       => $no_lot,
                'expired_date' => $exp_normalized,
                'qty'          => $qty,
                'tipe'         => 'RESERVE',
                'ref_no'       => $no_so,
                'ref_type'     => 'SALES_ORDER',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) { return false; }

        return true;
    }

    /**
     * Kurangi qty_reserved batch tanpa mengubah stok fisik.
     */
    private function _kurangi_reserved_batch(
        $no_so, $kd_barang, $exp_date, $no_lot,
        $gudang_id, $qty
    ) {
        $exp_normalized = $this->_normalizeDate($exp_date);

        // tberp_stock_batch — JANGAN kurangi qty_on_hand di sini
        try {
            $this->db->where('kd_barang', $kd_barang);
            $this->db->where('gudang_id', $gudang_id);
            if (!empty($no_lot))         $this->db->where('no_lot', $no_lot);
            if (!empty($exp_normalized)) $this->db->where('expired_date', $exp_normalized);
            $this->db->set('qty_reserved', 'GREATEST(0, qty_reserved - ' . (float)$qty . ')', false);
            $this->db->set('update_at', date('Y-m-d H:i:s'));
            $this->db->update('tberp_stock_batch');

            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd_barang,
                'gudang_id'    => $gudang_id,
                'no_lot'       => $no_lot,
                'expired_date' => $exp_normalized,
                'qty'          => $qty,
                'tipe'         => 'CANCEL_RESERVE',
                'ref_no'       => $no_so,
                'ref_type'     => 'SALES_ORDER_CANCEL',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) { /* ignore */ }
    }

    // ================================================================
    // VALIDASI STOK
    // ================================================================

    /**
     * Validasi stok saat membuat/update SO.
     * $exclude_id_so: lewati reserved milik SO ini sendiri (saat edit).
     */
    public function validasi_stok($details, $gudang_id, $exclude_id_so = null)
    {
        $errors = [];

        foreach ($details as $d) {
            $stock     = $this->cek_stock($d['kd_barang'], $d['expired_date'], $gudang_id);
            $available = $stock ? (float)$stock['available_stock'] : 0;

            $available = round($available, 3);
            $diminta   = round((float)$d['qty'], 3);

            if ($diminta > $available) {
                $isi      = max(1, (int)($d['isi_per_box'] ?? 1));
                $av_box   = (int)floor($available / $isi);
                $av_ecer  = (int)fmod($available, $isi);
                $req_box  = (int)($d['qty_box']    ?? 0);
                $req_ecer = (int)($d['qty_satuan'] ?? 0);

                $errors[] = "Stok tidak cukup: <b>{$d['nama_barang']}</b> "
                    . "(Exp: {$d['expired_date']}) — "
                    . "Diminta: {$req_box} box + {$req_ecer} pcs = {$diminta} pcs, "
                    . "Tersedia: {$av_box} box + {$av_ecer} pcs = {$available} pcs";
            }
        }
        return $errors;
    }

    // ================================================================
    // VALIDASI TONASE + KUBIKASI
    // ================================================================

    public function validasi_tonase_kubikasi(
        $details,
        $batas_tonase   = self::BATAS_TONASE,
        $batas_kubikasi = self::BATAS_KUBIKASI
    ) {
        $batas_tonase   = ($batas_tonase   > 0) ? (float)$batas_tonase   : self::BATAS_TONASE;
        $batas_kubikasi = ($batas_kubikasi > 0) ? (float)$batas_kubikasi : self::BATAS_KUBIKASI;

        $total_tonase = 0; $total_kubikasi = 0;
        foreach ($details as $d) {
            $qty = (float)($d['qty'] ?? 0);
            $total_tonase   += $qty * ((float)($d['berat_gram']  ?? 0) / 1000000);
            $total_kubikasi += $qty *  (float)($d['kubikasi_m3'] ?? 0);
        }

        $total_tonase   = round($total_tonase,   6);
        $total_kubikasi = round($total_kubikasi, 6);
        $warnings = [];

        if ($total_tonase > $batas_tonase && $total_kubikasi <= $batas_kubikasi)
            $warnings[] = "Tonase melebihi batas (" . round($total_tonase, 3) . " ton &gt; {$batas_tonase} ton).";
        elseif ($total_kubikasi > $batas_kubikasi && $total_tonase <= $batas_tonase)
            $warnings[] = "Kubikasi melebihi batas (" . round($total_kubikasi, 4) . " m³ &gt; {$batas_kubikasi} m³).";
        elseif ($total_tonase > $batas_tonase && $total_kubikasi > $batas_kubikasi)
            $warnings[] = "Tonase (" . round($total_tonase, 3) . " ton) DAN kubikasi ("
                . round($total_kubikasi, 4) . " m³) melebihi batas!";

        return [
            'total_tonase'   => $total_tonase,
            'total_kubikasi' => $total_kubikasi,
            'batas_tonase'   => $batas_tonase,
            'batas_kubikasi' => $batas_kubikasi,
            'warnings'       => $warnings,
        ];
    }

    // ================================================================
    // UPDATE STATUS SO
    // ================================================================

    public function update_status($id_so, $status, $update_by)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so) return false;

        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => $status,
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        if ($status === 'cancelled') {
            $gudang_id = $so['gudang_id'];
            $details  = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();

            foreach ($details as $d) {
                $outstanding = (float)$d['qty'] - (float)$d['qty_faktur'];
                if ($outstanding <= 0) continue;

                $this->_kurangi_reserved_batch(
                    $so['no_so'],
                    $d['kd_barang'], $d['expired_date'],
                    $d['no_lot'] ?? '', $gudang_id,
                    $outstanding
                );
            }
        }

        return true;
    }

    // ================================================================
    // APPROVAL
    // ================================================================

    // public function simpan_request_approval($no_so, $keterangan, $req_by, $approve_by)
    // {
    //     $ada = $this->db->get_where('tbso_so_approval', [
    //         'no_so'  => $no_so,
    //         'status' => 'pending',
    //     ])->row_array();

    //     if (!$ada) {
    //         $this->db->insert('tbso_so_approval', [
    //             'no_so'      => $no_so,
    //             'tipe'       => 'harga',
    //             'keterangan' => $keterangan,
    //             'req_by'     => $req_by,
    //             'approve_by' => $approve_by,
    //             'status'     => 'pending',
    //             'req_at'     => date('Y-m-d H:i:s'),
    //         ]);
    //     }

    //     $this->db->where('no_so', $no_so);
    //     $this->db->update('tbso_sales_order', [
    //         'status'     => 'waiting_approval',
    //         'approve_by' => $approve_by,
    //     ]);
    // }

    // public function get_pending_approval($approve_by = null)
    // {
    //     $this->db->select('ap.*, so.customer_name, so.tanggal_transaksi, so.total_tonase, so.total_kubikasi');
    //     $this->db->from('tbso_so_approval ap');
    //     $this->db->join('tbso_sales_order so', 'so.no_so = ap.no_so', 'left');
    //     $this->db->where('ap.status', 'pending');
    //     if (!empty($approve_by)) $this->db->where('ap.approve_by', $approve_by);
    //     $this->db->order_by('ap.req_at', 'DESC');
    //     return $this->db->get()->result_array();
    // }

    // public function proses_approval($id, $status, $note, $act_by)
    // {
    //     $this->db->where('id', $id);
    //     $this->db->update('tbso_so_approval', [
    //         'status' => $status,
    //         'note'   => $note,
    //         'act_by' => $act_by,
    //         'act_at' => date('Y-m-d H:i:s'),
    //     ]);

    //     $row = $this->db->get_where('tbso_so_approval', ['id' => $id])->row_array();
    //     if ($row) {
    //         $new_status = ($status === 'approved') ? 'draft' : 'draft'; // kembali ke draft, bukan langsung open
    //         $this->db->where('no_so', $row['no_so']);
    //         $this->db->update('tbso_sales_order', ['status' => $new_status]);
    //     }
    // }

    // ================================================================
    // LIST DO (tidak berubah — DO tetap dari luar modul SO)
    // ================================================================

    public function get_do_for_sales()
    {
        return $this->db->query("
            SELECT
                a.kd_do                    AS kddo,
                a.tgl_create               AS createat,
                a.tgl_pengiriman           AS tglkirim,
                a.nolambung                AS nopol,
                a.regional                 AS rute,
                a.status,
                a.sales_confirm_status,
                a.sales_confirm_by,
                a.sales_confirm_at,
                a.sales_confirm_note,
                (SELECT COUNT(DISTINCT kd_barang) FROM tb_detail_do WHERE kd_do = a.kd_do) AS totalbarang,
                (SELECT COUNT(DISTINCT kd_faktur)  FROM tb_detail_do WHERE kd_do = a.kd_do) AS totalfaktur
            FROM tb_do a
            WHERE a.status IN (2, 3)
              AND (SELECT COUNT(DISTINCT kd_faktur) FROM tb_detail_do WHERE kd_do = a.kd_do) > 0
            ORDER BY a.tgl_create DESC
        ")->result();
    }

    public function update_sales_confirm($kd_do, $action, $confirm_by, $note = '')
    {
        $now = date('Y-m-d H:i:s');
        $this->db->where('kd_do', $kd_do);
        $this->db->update('tb_do', [
            'sales_confirm_status' => $action,
            'sales_confirm_by'     => $confirm_by,
            'sales_confirm_at'     => $now,
            'sales_confirm_note'   => $note,
            'status'               => ($action === 'siap') ? 3 : 2,
        ]);
        $this->db->insert('tb_log_confirm_sales', [
            'kd_do'      => $kd_do,
            'action'     => $action,
            'note'       => $note,
            'confirm_by' => $confirm_by,
            'confirm_at' => $now,
        ]);
        return $this->db->affected_rows();
    }

    public function get_log_confirm_sales($kd_do)
    {
        return $this->db->query("
            SELECT * FROM tb_log_confirm_sales
            WHERE kd_do = ? ORDER BY confirm_at DESC
        ", [$kd_do])->result();
    }

    public function insertlog_do($data)
    {
        return $this->db->insert('tb_log_do', $data);
    }
}
