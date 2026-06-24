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
 *  - Qty Reserved berjalan di tberp_stock_batch: saat draft SO dibuat -> RESERVE,
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
 *   status          ENUM('draft','confirmed','proses_do','selesai_do','cancelled') DEFAULT 'draft',
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
    const BATAS_TONASE   = 7;
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
        return $this->db->field_exists('qty_on_hand', 'tberp_stock_batch') ? 'qty_on_hand' : 'qty';
    }

    private function _prepareSalesOrderDetailData(array $detail)
    {
        if (!$this->db->field_exists('harga_approval_by', 'tbso_sales_order_detail')) {
            unset($detail['harga_approval_by']);
        }
        return $detail;
    }

    private function _stockBatchIdForMovement($kd_barang, $exp_date, $no_lot, $gudang_id, $qty, $mode)
    {
        $qty_col = $this->_stockQtyColumn();
        $exp_normalized = $this->_normalizeDate($exp_date);

        $this->db->select('id');
        $this->db->from('tberp_stock_batch');
        $this->db->where('kd_barang', $kd_barang);
        $this->db->where('gudang_id', $gudang_id);
        if (!empty($no_lot)) {
            $this->db->where('no_lot', $no_lot);
        }
        if (!empty($exp_normalized)) {
            $this->db->where('expired_date', $exp_normalized);
        }

        if ($mode === 'reserve') {
            $this->db->where('(' . $qty_col . ' - COALESCE(qty_reserved, 0)) >=', (float)$qty, false);
        } elseif ($mode === 'invoice') {
            $this->db->where($qty_col . ' >=', (float)$qty);
            $this->db->where('qty_reserved >=', (float)$qty);
        } elseif ($mode === 'release') {
            $this->db->where('qty_reserved >', 0);
        }

        $this->db->order_by('expired_date', 'ASC');
        $this->db->order_by('id', 'ASC');
        $this->db->limit(1);

        $row = $this->db->get()->row_array();
        return $row ? (int)$row['id'] : 0;
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
     * Generate No. Faktur — format: [prefix urutan user]INVDDMMYYXXXX
     * Faktur sekarang hidup di tbso_faktur_penjualan.
     */
    public function generate_no_faktur($user_prefix = '')
    {
        $user_prefix = preg_replace('/[^A-Z]/', '', strtoupper((string)$user_prefix));
        $prefix = $user_prefix . 'INV' . date('dmy');

        $row = $this->db->query("
            SELECT nomor
            FROM (
                SELECT no_faktur AS nomor
                FROM tbso_faktur_penjualan
                WHERE no_faktur LIKE ?
                UNION
                SELECT kd_faktur AS nomor
                FROM tb_detail_do
                WHERE kd_faktur LIKE ?
                UNION
                SELECT kd_faktur AS nomor
                FROM tb_tmp_detaildo
                WHERE kd_faktur LIKE ?
            ) faktur_terpakai
            ORDER BY nomor DESC
            LIMIT 1
        ", [$prefix . '%', $prefix . '%', $prefix . '%'])->row();

        if ($row) {
            $last = (int)substr($row->nomor, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    public function is_no_faktur_used($no_faktur)
    {
        $row = $this->db->query("
            SELECT nomor
            FROM (
                SELECT no_faktur AS nomor
                FROM tbso_faktur_penjualan
                WHERE no_faktur = ?
                UNION
                SELECT kd_faktur AS nomor
                FROM tb_detail_do
                WHERE kd_faktur = ?
                UNION
                SELECT kd_faktur AS nomor
                FROM tb_tmp_detaildo
                WHERE kd_faktur = ?
            ) faktur_terpakai
            LIMIT 1
        ", [$no_faktur, $no_faktur, $no_faktur])->row();

        return !empty($row);
    }

    // ================================================================
    // MASTER DATA
    // ================================================================

    public function get_customers($nama_sales = null)
    {
        $nama_sales = trim((string)$nama_sales);

        if ($nama_sales !== '' && $this->db->field_exists('nama_sales', 'tb_customer')) {
            $this->db->where(
                'LOWER(TRIM(nama_sales)) = ' . $this->db->escape(strtolower($nama_sales)),
                null,
                false
            );
        }

        return $this->db
            ->order_by('nama_customer', 'ASC')
            ->get('tb_customer')
            ->result_array();
    }

    public function is_customer_for_sales($kd_customer, $nama_sales)
    {
        $kd_customer = trim((string)$kd_customer);
        $nama_sales  = trim((string)$nama_sales);

        if ($kd_customer === '' || $nama_sales === '') {
            return false;
        }

        if (!$this->db->field_exists('nama_sales', 'tb_customer')) {
            return true;
        }

        return $this->db
            ->where('kd_customer', $kd_customer)
            ->where(
                'LOWER(TRIM(nama_sales)) = ' . $this->db->escape(strtolower($nama_sales)),
                null,
                false
            )
            ->count_all_results('tb_customer') > 0;
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

    public function get_available_stock_with_dimensi($gudang_id = null, $kd_barang = null, $exclude_id_so = null)
    {
        $gudang_id_str = !empty($gudang_id) ? (string)$gudang_id : null;
        $qty_col = $this->_stockQtyColumn();
        $exclude_so = $exclude_id_so
            ? $this->db->get_where('tbso_sales_order', ['id_so' => $exclude_id_so])->row_array()
            : null;

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

        $this->db->order_by('sb.kd_barang', 'ASC');
        $this->db->order_by('sb.expired_date', 'ASC');
        $this->db->order_by('sb.no_lot', 'ASC');
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

            $own_reserved = 0;
            if ($exclude_so && (string)($exclude_so['gudang_id'] ?? '') === (string)($row['gudang_id'] ?? '')) {
                $own_reserved = $this->_reserved_qty_for_so_batch(
                    $exclude_id_so,
                    $row['kd_barang'],
                    $row['exp_date'] ?? $row['expired_date'] ?? '',
                    $row['no_lot'] ?? null
                );
            }

            $av  = (float)($row['available_stock'] ?? 0) + $own_reserved;
            if ($av <= 0) {
                $row = null;
                continue;
            }

            $row['own_reserved_stock'] = $own_reserved;
            $row['available_stock']    = $av;
            $isi = max(1, (int)$row['isi_per_box']);

            $row['available_box']  = (int)floor($av / $isi);
            $row['available_ecer'] = (int)fmod($av, $isi);
        }
        unset($row);

        return array_values(array_filter($stocks));
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
            c.kd_rute AS customer_kd_rute,
            COUNT(sd.id)                                                        AS jumlah_item,
            SUM(CASE WHEN (sd.qty - COALESCE(sd.qty_faktur, 0)) <= 0
                    THEN 1 ELSE 0 END)                                         AS jumlah_item_diterima,
            COALESCE(SUM(sd.qty), 0)                                            AS total_qty_order,
            COALESCE(SUM(sd.qty_faktur), 0)                                     AS total_qty_faktur,
            COALESCE(SUM(GREATEST(sd.qty - COALESCE(sd.qty_faktur, 0), 0)), 0)   AS total_qty_outstanding
        ');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left');
        $this->db->join('tbso_sales_order_detail sd', 'sd.id_so = so.id_so', 'left');

        if (!empty($filter['status']))      $this->db->where('so.status', $filter['status']);
        if (!empty($filter['exclude_status'])) {
            $exclude_status = is_array($filter['exclude_status']) ? $filter['exclude_status'] : [$filter['exclude_status']];
            $this->db->where_not_in('so.status', array_filter($exclude_status));
        }
        if (!empty($filter['date1']))       $this->db->where('so.tanggal_transaksi >=', $filter['date1']);
        if (!empty($filter['date2']))       $this->db->where('so.tanggal_transaksi <=', $filter['date2']);
        if (!empty($filter['customer_id'])) $this->db->where('c.id', $filter['customer_id']);
        if (!empty($filter['create_by']))   $this->db->where('so.create_by', $filter['create_by']);

        $this->db->group_by('so.id_so');
        $this->db->order_by('so.tanggal_transaksi', 'DESC');
        $this->db->order_by('so.id_so', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_admin_sc_ready_so($filter = [])
    {
        $this->db->select('
            so.*,
            c.nama_customer,
            c.nama_kios,
            c.regional,
            c.kd_rute AS customer_kd_rute,
            COUNT(sd.id) AS jumlah_item,
            SUM(CASE WHEN GREATEST(COALESCE(sd.qty_siap_faktur, sd.qty) - COALESCE(sd.qty_faktur, 0), 0) > 0
                THEN 1 ELSE 0 END) AS jumlah_item_siap_faktur,
            COALESCE(SUM(sd.qty), 0) AS total_qty_order,
            COALESCE(SUM(sd.qty_faktur), 0) AS total_qty_faktur,
            COALESCE(SUM(GREATEST(sd.qty - COALESCE(sd.qty_faktur, 0), 0)), 0) AS total_qty_outstanding,
            COALESCE(SUM(GREATEST(COALESCE(sd.qty_siap_faktur, sd.qty) - COALESCE(sd.qty_faktur, 0), 0)), 0) AS total_qty_siap_faktur,
            COALESCE(SUM(COALESCE(sd.qty_tidak_terkirim, 0)), 0) AS total_qty_tidak_terkirim,
            (
                SELECT COUNT(*)
                FROM tbso_faktur_penjualan fp
                WHERE fp.id_so = so.id_so
                AND fp.status <> \'cancelled\'
            ) AS jumlah_faktur,
            (
                SELECT fp.id_faktur
                FROM tbso_faktur_penjualan fp
                WHERE fp.id_so = so.id_so
                AND fp.status <> \'cancelled\'
                ORDER BY fp.create_at DESC, fp.id_faktur DESC
                LIMIT 1
            ) AS latest_id_faktur
        ');
        $this->db->from('tbso_sales_order so');
        $this->db->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left');
        $this->db->join('tbso_sales_order_detail sd', 'sd.id_so = so.id_so', 'left');
        $this->db->where_in('so.status', ['siap_faktur', 'partial']);

        if (!empty($filter['date1']))       $this->db->where('so.tanggal_transaksi >=', $filter['date1']);
        if (!empty($filter['date2']))       $this->db->where('so.tanggal_transaksi <=', $filter['date2']);
        if (!empty($filter['customer_id'])) $this->db->where('c.id', $filter['customer_id']);
        if (!empty($filter['create_by']))   $this->db->where('so.create_by', $filter['create_by']);
        if (!empty($filter['kd_rute'])) {
            $this->db->where(
                "COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute) = " . $this->db->escape($filter['kd_rute']),
                null,
                false
            );
        }

        $this->db->group_by('so.id_so');
        $this->db->having('total_qty_siap_faktur >', 0);
        $this->db->order_by('so.update_at', 'DESC');
        $this->db->order_by('so.tanggal_transaksi', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_admin_sc_faktur_selesai($filter = [])
    {
        $detail_summary = "
            SELECT
                id_faktur,
                COUNT(*) AS total_barang,
                COALESCE(SUM(qty), 0) AS total_qty,
                COALESCE(SUM(subtotal_after_disc), 0) AS total_nilai_faktur,
                COALESCE(SUM(
                    CASE
                        WHEN subtotal_after_disc > 0 THEN subtotal_after_disc * (COALESCE(pajak, 0) / 100)
                        ELSE (qty * hrg_satuan * (1 - (COALESCE(disc, 0) / 100))) * (COALESCE(pajak, 0) / 100)
                    END
                ), 0) AS total_pajak,
                COALESCE(SUM(total_harga), 0) AS grand_total
            FROM tbso_faktur_detail
            GROUP BY id_faktur
        ";

        $this->db->select('
            f.*,
            so.id_so,
            so.kd_rute AS so_kd_rute,
            c.nama_customer,
            c.nama_kios,
            c.regional,
            c.kd_rute AS customer_kd_rute,
            COALESCE(fs.total_barang, 0) AS total_barang,
            COALESCE(fs.total_qty, 0) AS total_qty,
            COALESCE(fs.total_nilai_faktur, 0) AS total_nilai_faktur,
            COALESCE(fs.total_pajak, 0) AS total_pajak,
            COALESCE(fs.grand_total, 0) AS grand_total
        ');
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tbso_sales_order so', 'so.id_so = f.id_so', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->join('(' . $detail_summary . ') fs', 'fs.id_faktur = f.id_faktur', 'left');
        $this->db->where_not_in('f.status', ['draft', 'cancelled']);

        if (!empty($filter['date1']))       $this->db->where('f.tanggal_faktur >=', $filter['date1']);
        if (!empty($filter['date2']))       $this->db->where('f.tanggal_faktur <=', $filter['date2']);
        if (!empty($filter['customer_id'])) $this->db->where('c.id', $filter['customer_id']);
        if (!empty($filter['create_by']))   $this->db->where('f.create_by', $filter['create_by']);
        if (!empty($filter['kd_rute'])) {
            $this->db->where(
                "COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute) = " . $this->db->escape($filter['kd_rute']),
                null,
                false
            );
        }

        $this->db->order_by('f.tanggal_faktur', 'DESC');
        $this->db->order_by('f.create_at', 'DESC');
        $this->db->order_by('f.id_faktur', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_so_rute_summary($filter = [])
    {
        $where = "WHERE COALESCE(NULLIF(so.kd_rute, ''), '') <> '' AND so.status = 'open'";
        $params = [];

        if (!empty($filter['create_by'])) {
            $where .= " AND so.create_by = ?";
            $params[] = $filter['create_by'];
        }

        $sql = "
            SELECT
                r.kd_rute,
                COALESCE(NULLIF(r.keterangan, ''), r.kd_rute) AS nama_rute,
                COALESCE(p.total_so, 0) AS total_so,
                COALESCE(p.total_tonase, 0) AS total_tonase,
                COALESCE(p.total_kubikasi, 0) AS total_kubikasi,
                COALESCE(p.total_qty_order, 0) AS total_qty_order,
                COALESCE(p.total_qty_faktur, 0) AS total_qty_faktur,
                COALESCE(p.total_qty_outstanding, 0) AS total_qty_outstanding
            FROM tb_rutecs r
            LEFT JOIN (
                SELECT
                    x.kd_rute,
                    COUNT(*) AS total_so,
                    ROUND(COALESCE(SUM(x.total_tonase), 0), 3) AS total_tonase,
                    ROUND(COALESCE(SUM(x.total_kubikasi), 0), 4) AS total_kubikasi,
                    ROUND(COALESCE(SUM(x.total_qty_order), 0), 2) AS total_qty_order,
                    ROUND(COALESCE(SUM(x.total_qty_faktur), 0), 2) AS total_qty_faktur,
                    ROUND(COALESCE(SUM(x.total_qty_outstanding), 0), 2) AS total_qty_outstanding
                FROM (
                    SELECT
                        NULLIF(so.kd_rute, '') AS kd_rute,
                        so.id_so,
                        COALESCE(so.total_tonase, 0) AS total_tonase,
                        COALESCE(so.total_kubikasi, 0) AS total_kubikasi,
                        COALESCE(d.total_qty_order, 0) AS total_qty_order,
                        COALESCE(d.total_qty_faktur, 0) AS total_qty_faktur,
                        COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding
                    FROM tbso_sales_order so
                    LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
                    LEFT JOIN (
                        SELECT
                            id_so,
                            SUM(qty) AS total_qty_order,
                            SUM(COALESCE(qty_faktur, 0)) AS total_qty_faktur,
                            SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding
                        FROM tbso_sales_order_detail
                        GROUP BY id_so
                    ) d ON d.id_so = so.id_so
                    {$where}
                ) x
                GROUP BY x.kd_rute
            ) p ON p.kd_rute = r.kd_rute
            ORDER BY
                COALESCE(p.total_so, 0) DESC,
                COALESCE(p.total_tonase, 0) DESC,
                COALESCE(p.total_kubikasi, 0) DESC,
                r.kd_rute ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    public function get_so_by_rute($kd_rute = '', $filter = [])
    {
        $kd_rute = trim((string)$kd_rute);
        if ($kd_rute === '') {
            return [];
        }

        $params = [$kd_rute];
        $where = "WHERE so.kd_rute = ? AND so.status IN ('open', 'partial')";

        if (!empty($filter['create_by'])) {
            $where .= " AND so.create_by = ?";
            $params[] = $filter['create_by'];
        }

        $sql = "
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.status,
                so.customer_name,
                so.create_by,
                so.total_tonase,
                so.total_kubikasi,
                c.nama_customer,
                c.nama_kios,
                c.regional,
                so.kd_rute AS so_kd_rute,
                so.kd_rute AS kd_rute,
                c.kd_rute AS customer_kd_rute,
                COALESCE(r.keterangan, so.kd_rute) AS nama_rute,
                COALESCE(d.jumlah_item, 0) AS jumlah_item,
                COALESCE(d.jumlah_item_diterima, 0) AS jumlah_item_diterima,
                COALESCE(d.total_qty_order, 0) AS total_qty_order,
                COALESCE(d.total_qty_faktur, 0) AS total_qty_faktur,
                COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding,
                COALESCE(d.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim,
                COALESCE(d.verifikasi_loading_notes, '') AS verifikasi_loading_notes
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = so.kd_rute
            LEFT JOIN (
                SELECT
                    id_so,
                    COUNT(id) AS jumlah_item,
                    SUM(CASE WHEN (qty - COALESCE(qty_faktur, 0)) <= 0 THEN 1 ELSE 0 END) AS jumlah_item_diterima,
                    SUM(qty) AS total_qty_order,
                    SUM(COALESCE(qty_faktur, 0)) AS total_qty_faktur,
                    SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding,
                    SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim,
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(verifikasi_loading_note), '')
                        ORDER BY id ASC
                        SEPARATOR '\n'
                    ) AS verifikasi_loading_notes
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            {$where}
            ORDER BY so.tanggal_transaksi DESC, so.no_so DESC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    public function get_open_so_for_routing($filter = [])
    {
        $where = "WHERE (
            (so.status = 'open' AND COALESCE(so.kd_rute, '') = '')
            OR (so.status IN ('siap_faktur', 'partial') AND COALESCE(d.total_qty_tidak_terkirim, 0) > 0)
        )";
        $params = [];

        if (!empty($filter['create_by'])) {
            $where .= " AND so.create_by = ?";
            $params[] = $filter['create_by'];
        }
        if (!empty($filter['customer_kd_rute'])) {
            $where .= " AND c.kd_rute = ?";
            $params[] = $filter['customer_kd_rute'];
        }

        $sql = "
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.status,
                so.customer_name,
                so.create_by,
                so.total_tonase,
                so.total_kubikasi,
                c.nama_customer,
                c.nama_kios,
                c.regional,
                so.kd_rute AS so_kd_rute,
                so.kd_rute AS kd_rute,
                c.kd_rute AS customer_kd_rute,
                COALESCE(r.keterangan, so.kd_rute) AS nama_rute,
                COALESCE(d.jumlah_item, 0) AS jumlah_item,
                COALESCE(d.jumlah_item_diterima, 0) AS jumlah_item_diterima,
                COALESCE(d.total_qty_order, 0) AS total_qty_order,
                COALESCE(d.total_qty_faktur, 0) AS total_qty_faktur,
                COALESCE(d.total_qty_outstanding, 0) AS total_qty_outstanding,
                COALESCE(d.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim,
                COALESCE(d.verifikasi_loading_notes, '') AS verifikasi_loading_notes
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = so.kd_rute
            LEFT JOIN (
                SELECT
                    id_so,
                    COUNT(id) AS jumlah_item,
                    SUM(CASE WHEN (qty - COALESCE(qty_faktur, 0)) <= 0 THEN 1 ELSE 0 END) AS jumlah_item_diterima,
                    SUM(qty) AS total_qty_order,
                    SUM(COALESCE(qty_faktur, 0)) AS total_qty_faktur,
                    SUM(GREATEST(qty - COALESCE(qty_faktur, 0), 0)) AS total_qty_outstanding,
                    SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim,
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(verifikasi_loading_note), '')
                        ORDER BY id ASC
                        SEPARATOR '\n'
                    ) AS verifikasi_loading_notes
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            {$where}
            ORDER BY
                CASE WHEN so.status = 'open' THEN 0 ELSE 1 END ASC,
                so.tanggal_transaksi DESC,
                so.no_so DESC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    public function count_open_so_for_routing($filter = [])
    {
        $where = "WHERE (
            (so.status = 'open' AND COALESCE(so.kd_rute, '') = '')
            OR (so.status = 'partial' AND COALESCE(so.kd_rute, '') = '')
            OR (so.status IN ('siap_faktur', 'partial') AND COALESCE(d.total_qty_tidak_terkirim, 0) > 0)
        )";
        $params = [];
        if (!empty($filter['create_by'])) {
            $where .= " AND so.create_by = ?";
            $params[] = $filter['create_by'];
        }
        if (!empty($filter['customer_kd_rute'])) {
            $where .= " AND c.kd_rute = ?";
            $params[] = $filter['customer_kd_rute'];
        }

        $row = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN (
                SELECT id_so, SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            {$where}
        ", $params)->row_array();

        return (int)($row['total'] ?? 0);
    }

    public function get_open_so_customer_route_options($filter = [])
    {
        $where = "WHERE (
            (so.status = 'open' AND COALESCE(so.kd_rute, '') = '')
            OR (so.status = 'partial' AND COALESCE(so.kd_rute, '') = '')
            OR (so.status IN ('siap_faktur', 'partial') AND COALESCE(d.total_qty_tidak_terkirim, 0) > 0)
        )";
        $params = [];

        if (!empty($filter['create_by'])) {
            $where .= " AND so.create_by = ?";
            $params[] = $filter['create_by'];
        }

        $sql = "
            SELECT
                c.kd_rute,
                COALESCE(NULLIF(r.keterangan, ''), c.kd_rute) AS nama_rute,
                COUNT(*) AS total_so,
                ROUND(COALESCE(SUM(so.total_tonase), 0), 3) AS total_tonase,
                ROUND(COALESCE(SUM(so.total_kubikasi), 0), 4) AS total_kubikasi
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = c.kd_rute
            LEFT JOIN (
                SELECT id_so, SUM(COALESCE(qty_tidak_terkirim, 0)) AS total_qty_tidak_terkirim
                FROM tbso_sales_order_detail
                GROUP BY id_so
            ) d ON d.id_so = so.id_so
            {$where}
            GROUP BY c.kd_rute, r.keterangan
            ORDER BY c.kd_rute ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    public function update_so_rute($id_so, $kd_rute, $update_by)
    {
        $this->db->where('id_so', $id_so);
        return $this->db->update('tbso_sales_order', [
            'kd_rute'   => $kd_rute,
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function clear_so_rute($id_so, $update_by)
    {
        $this->db->where('id_so', $id_so);
        return $this->db->update('tbso_sales_order', [
            'kd_rute'   => null,
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update_so_detail_harga($id_so, $id_so_detail, $harga, $update_by = '')
    {
        $id_so = (int)$id_so;
        $id_so_detail = (int)$id_so_detail;
        $harga = (float)$harga;

        if ($id_so <= 0 || $id_so_detail <= 0 || $harga <= 0) {
            return false;
        }

        $detail = $this->db
            ->where('id', $id_so_detail)
            ->where('id_so', $id_so)
            ->limit(1)
            ->get('tbso_sales_order_detail')
            ->row_array();

        if (!$detail) {
            return false;
        }

        $disc = (float)($detail['disc'] ?? 0);
        $pajak = (float)($detail['pajak'] ?? 0);

        $this->db->where('id', $id_so_detail);
        $this->db->where('id_so', $id_so);
        $this->db->set('hrg_satuan', $harga);
        $this->db->set('subtotal_before_disc', 'qty * ' . $harga, false);
        $this->db->set('subtotal_after_disc', '(qty * ' . $harga . ') * (1 - (' . $disc . ' / 100))', false);
        $this->db->set('total_harga', '((qty * ' . $harga . ') * (1 - (' . $disc . ' / 100))) * (1 + (' . $pajak . ' / 100))', false);
        if ($this->db->field_exists('update_by', 'tbso_sales_order_detail')) {
            $this->db->set('update_by', $update_by);
        }
        if ($this->db->field_exists('update_at', 'tbso_sales_order_detail')) {
            $this->db->set('update_at', date('Y-m-d H:i:s'));
        }
        return $this->db->update('tbso_sales_order_detail');
    }

    public function rute_exists($kd_rute)
    {
        return $this->db
            ->where('kd_rute', $kd_rute)
            ->limit(1)
            ->get('tb_rutecs')
            ->num_rows() > 0;
    }

    public function get_so($id_so)
    {
        $this->db->select('
            so.*,
            c.nama_customer,
            c.regional,
            c.kd_rute AS customer_kd_rute
        ');
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
            $row['qty_outstanding'] = max(0, (float)$row['qty'] - (float)$row['qty_faktur']);
            $row['qty_siap_faktur'] = array_key_exists('qty_siap_faktur', $row) && $row['qty_siap_faktur'] !== null
                ? (float)$row['qty_siap_faktur']
                : (float)$row['qty'];
            $row['qty_tidak_terkirim'] = (float)($row['qty_tidak_terkirim']
                ?? max(0, $row['qty_outstanding'] - $row['qty_siap_faktur']));
            $row['qty_available_faktur'] = max(0, min(
                $row['qty_outstanding'],
                $row['qty_siap_faktur'] - $row['qty_faktur']
            ));

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
        if ($this->db->field_exists('is_faktur_z', 'tbso_sales_order')) {
            $so_data['is_faktur_z'] = !empty($header['is_faktur_z']) ? 1 : 0;
        }

        $this->db->insert('tbso_sales_order', $so_data);
        $id_so    = $this->db->insert_id();
        $no_so    = $header['no_so'];

        foreach ($details as $d) {
            $d['id_so']          = $id_so;
            $d['no_so']          = $no_so;
            $d['qty_faktur']     = 0;   // belum ada faktur
            // qty_outstanding = generated column di DB, tidak perlu diisi
            $d = $this->_prepareSalesOrderDetailData($d);
            $this->db->insert('tbso_sales_order_detail', $d);

            $reserved = $this->_reservasi_stok(
                $no_so,
                $this->db->insert_id(),
                $d['kd_barang'],
                $d['expired_date'],
                $d['no_lot'] ?? null,
                $header['gudang_id'],
                $d['qty']
            );

            if (!$reserved) {
                $this->db->trans_rollback();
                return false;
            }
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

        $this->db->trans_start();

        // Draft lama sebelum reservasi-at-create belum punya stok terkunci.
        $reservation = $this->db->query("
            SELECT COALESCE(SUM(
                CASE
                    WHEN tipe = 'RESERVE' THEN qty
                    WHEN tipe = 'RELEASE' THEN -qty
                    ELSE 0
                END
            ), 0) AS active_qty
            FROM tberp_stock_ledger
            WHERE ref_no = ?
            AND ref_type IN ('SALES_ORDER', 'SALES_ORDER_CANCEL')
        ", [$so['no_so']])->row_array();

        if ((float)($reservation['active_qty'] ?? 0) <= 0) {
            $details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
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
        $so_update = [
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
        ];
        if ($this->db->field_exists('is_faktur_z', 'tbso_sales_order')) {
            $so_update['is_faktur_z'] = !empty($header['is_faktur_z']) ? 1 : 0;
        }
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', $so_update);

        // Lepas reservasi draft lama sebelum mengganti detail SO.
        $old_details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        foreach ($old_details as $old) {
            $outstanding = (float)$old['qty'] - (float)($old['qty_faktur'] ?? 0);
            if ($outstanding <= 0) continue;

            $this->_kurangi_reserved_batch(
                $no_so,
                $old['kd_barang'],
                $old['expired_date'],
                $old['no_lot'] ?? null,
                $so['gudang_id'],
                $outstanding
            );
        }

        // Hapus detail lama
        $this->db->delete('tbso_sales_order_detail', ['id_so' => $id_so]);

        // Insert detail baru dan reservasi ulang stok draft.
        foreach ($details as $d) {
            $d['id_so']      = $id_so;
            $d['no_so']      = $no_so;
            $d['qty_faktur'] = 0;
            $d = $this->_prepareSalesOrderDetailData($d);
            $this->db->insert('tbso_sales_order_detail', $d);

            $reserved = $this->_reservasi_stok(
                $no_so,
                $this->db->insert_id(),
                $d['kd_barang'],
                $d['expired_date'],
                $d['no_lot'] ?? null,
                $gudang_id,
                $d['qty']
            );

            if (!$reserved) {
                $this->db->trans_rollback();
                return false;
            }
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
        $this->db->select('f.*, c.nama_customer, c.kd_rute AS customer_kd_rute');
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
        $whereRoute = $routeFilter ? " AND COALESCE(NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') = ? " : "";

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
                COALESCE(NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
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
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN tb_master_barang_all mb ON mb.kd_barang = fd.kd_barang
            WHERE f.status = 'confirmed'
            AND NOT EXISTS (
                SELECT 1 FROM tb_detail_do d
                WHERE d.kd_faktur = f.no_faktur
                AND d.kd_customer = f.kd_customer
            )
            AND NOT EXISTS (
                SELECT 1 FROM tb_tmp_detaildo t
                WHERE t.kd_faktur = f.no_faktur
                AND t.kd_customer = f.kd_customer
            )
            {$whereRoute}
            GROUP BY
                f.id_faktur, f.no_faktur, f.no_so, f.kd_customer,
                f.customer_name, f.tanggal_faktur, f.status,
                f.total_tonase, f.total_kubikasi, so.id_so,
                c.nama_customer, c.nama_kios, c.alamat_kios,
                c.regional, c.kd_rute, so.kd_rute, r.keterangan
        ";
    }

    public function get_pending_faktur_rute_summary()
    {
        $sql = "
            SELECT
                r.kd_rute,
                COALESCE(NULLIF(r.keterangan, ''), r.kd_rute) AS nama_rute,
                COALESCE(p.total_faktur, 0) AS total_faktur,
                COALESCE(p.total_tonase, 0) AS total_tonase,
                COALESCE(p.total_kubikasi, 0) AS total_kubikasi
            FROM tb_rutecs r
            LEFT JOIN (
                SELECT
                    x.kd_rute,
                    COUNT(*) AS total_faktur,
                    ROUND(COALESCE(SUM(x.total_tonase), 0), 3) AS total_tonase,
                    ROUND(COALESCE(SUM(x.total_kubikasi), 0), 4) AS total_kubikasi
                FROM (
                    " . $this->_pending_faktur_rute_sql(false) . "
                ) x
                GROUP BY x.kd_rute
            ) p ON p.kd_rute = r.kd_rute
            WHERE COALESCE(p.total_faktur, 0) > 0
            ORDER BY
                COALESCE(p.total_tonase, 0) DESC,
                COALESCE(p.total_kubikasi, 0) DESC,
                COALESCE(p.total_faktur, 0) DESC,
                r.kd_rute ASC
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

    private function _today_delivery_faktur_rute_sql($routeFilter = false)
    {
        $whereRoute = $routeFilter ? " AND COALESCE(NULLIF(h.regional, ''), NULLIF(d.kd_rute, ''), NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') = ? " : "";

        return "
            SELECT
                f.id_faktur,
                f.no_faktur,
                f.no_so,
                f.kd_customer,
                COALESCE(f.customer_name, c.nama_customer) AS customer_name,
                f.tanggal_faktur,
                f.status,
                h.kd_do,
                h.tgl_pengiriman,
                od_log.create_at AS tanggal_on_delivery,
                h.status AS status_do,
                so.id_so,
                c.nama_kios,
                c.alamat_kios,
                c.regional,
                c.kd_rute AS kd_rute_customer,
                COALESCE(NULLIF(h.regional, ''), NULLIF(d.kd_rute, ''), NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(h.regional, ''), NULLIF(d.kd_rute, ''), NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
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
            JOIN (
                SELECT DISTINCT kd_do, kd_faktur, kd_rute, kd_customer
                FROM tb_detail_do
            ) d ON d.kd_faktur = f.no_faktur
            JOIN tb_do h ON h.kd_do = d.kd_do
            JOIN (
                SELECT kd_do, MAX(create_at) AS create_at
                FROM tb_log_do
                WHERE keterangan = 'REKAM ORDER - ON DELIVERY'
                GROUP BY kd_do
            ) od_log ON od_log.kd_do = h.kd_do
            LEFT JOIN tbso_sales_order so ON so.id_so = f.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(h.regional, ''), NULLIF(d.kd_rute, ''), NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN tb_master_barang_all mb ON mb.kd_barang = fd.kd_barang
            WHERE f.status = 'selesai_do'
            AND DATE(od_log.create_at) = CURDATE()
            AND h.status = 5
            {$whereRoute}
            GROUP BY
                f.id_faktur, f.no_faktur, f.no_so, f.kd_customer,
                f.customer_name, f.tanggal_faktur, f.status,
                h.kd_do, h.tgl_pengiriman, od_log.create_at, h.status, so.id_so,
                c.nama_customer, c.nama_kios, c.alamat_kios,
                c.regional, c.kd_rute, so.kd_rute, d.kd_rute, h.regional, r.keterangan
        ";
    }

    public function get_today_delivery_faktur_rute_summary()
    {
        $sql = "
            SELECT
                x.kd_rute,
                COALESCE(MAX(NULLIF(x.nama_rute, '')), x.kd_rute) AS nama_rute,
                COUNT(DISTINCT x.id_faktur) AS total_faktur,
                ROUND(COALESCE(SUM(x.total_tonase), 0), 3) AS total_tonase,
                ROUND(COALESCE(SUM(x.total_kubikasi), 0), 4) AS total_kubikasi
            FROM (
                " . $this->_today_delivery_faktur_rute_sql(false) . "
            ) x
            GROUP BY x.kd_rute
            ORDER BY
                COALESCE(SUM(x.total_tonase), 0) DESC,
                COALESCE(SUM(x.total_kubikasi), 0) DESC,
                COUNT(DISTINCT x.id_faktur) DESC,
                x.kd_rute ASC
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_today_delivery_faktur_by_rute($kd_rute = '')
    {
        $kd_rute = trim((string)$kd_rute);
        $params = [];
        $sql = $this->_today_delivery_faktur_rute_sql($kd_rute !== '');

        if ($kd_rute !== '') {
            $params[] = $kd_rute;
        }

        $sql .= " ORDER BY tanggal_on_delivery DESC, kd_do DESC, tanggal_faktur DESC, no_faktur DESC";
        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Buat Faktur Penjualan dari SO yang sudah berstatus 'siap_faktur' atau 'partial'.
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
        if (!$so || !in_array($so['status'], ['siap_faktur', 'partial'], true)) return false;

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
                $stock_batch_id = $this->_stockBatchIdForMovement(
                    $item['kd_barang'],
                    $exp_normalized,
                    $item['no_lot'] ?? null,
                    $gudang_id,
                    $qty_item,
                    'invoice'
                );
                if ($stock_batch_id <= 0) {
                    $this->db->trans_rollback();
                    return false;
                }

                $this->db->where('id', $stock_batch_id);
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

            // 3. Tambah qty_faktur dan sinkronkan harga yang dipakai faktur ke detail SO.
            $this->db->where('id', $item['id_so_detail']);
            $this->db->set('qty_faktur', 'qty_faktur + ' . $qty_item, false);
            $this->db->set('hrg_satuan', (float)($item['hrg_satuan'] ?? 0));
            $this->db->set('disc', (float)($item['disc'] ?? 0));
            $this->db->set('pajak', (float)($item['pajak'] ?? 0));
            $this->db->set('subtotal_before_disc', 'qty * ' . (float)($item['hrg_satuan'] ?? 0), false);
            $this->db->set('subtotal_after_disc', '(qty * ' . (float)($item['hrg_satuan'] ?? 0) . ') * (1 - (' . (float)($item['disc'] ?? 0) . ' / 100))', false);
            $this->db->set('total_harga', '((qty * ' . (float)($item['hrg_satuan'] ?? 0) . ') * (1 - (' . (float)($item['disc'] ?? 0) . ' / 100))) * (1 + (' . (float)($item['pajak'] ?? 0) . ' / 100))', false);
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

            $outstanding = max(0, (float)$sd['qty'] - (float)$sd['qty_faktur']);
            $qty_siap = array_key_exists('qty_siap_faktur', $sd) && $sd['qty_siap_faktur'] !== null
                ? (float)$sd['qty_siap_faktur']
                : (float)$sd['qty'];
            $available_faktur = max(0, min($outstanding, $qty_siap - (float)$sd['qty_faktur']));
            $diminta     = (float)$item['qty'];

            if ($diminta <= 0) {
                $errors[] = "Qty faktur untuk <b>{$sd['nama_barang']}</b> harus lebih dari 0.";
            } elseif ($diminta > $available_faktur + 0.001) {
                $errors[] = "Qty faktur untuk <b>{$sd['nama_barang']}</b> melebihi qty yang lolos verifikasi. "
                    . "Siap faktur: {$available_faktur} pcs, Diminta: {$diminta} pcs.";
            }
        }
        return $errors;
    }

    /**
     * Periksa status pemenuhan SO setelah faktur dibuat.
     * Completed jika semua outstanding habis, partial jika sudah ada faktur namun masih ada sisa.
     */
    private function _cek_dan_complete_so($id_so)
    {
        $rows = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        $all_done   = true;
        $has_faktur = false;
        $has_outstanding_available = false;

        foreach ($rows as $r) {
            $qty_faktur  = (float)$r['qty_faktur'];
            $outstanding = (float)$r['qty'] - $qty_faktur;

            if ($qty_faktur > 0.001) {
                $has_faktur = true;
            }
            if ($outstanding > 0.001) {
                $all_done = false;
                // Cek apakah item ini masih bisa difakturkan (ada qty_available_faktur)
                $available = (float)($r['qty_available_faktur'] ?? $outstanding);
                if ($available > 0.001) {
                    $has_outstanding_available = true;
                }
            }
        }

        if ($all_done) {
            // Semua item sudah terfakturkan penuh → completed
            $this->db->where('id_so', $id_so);
            $this->db->update('tbso_sales_order', [
                'status'    => 'completed',
                'update_at' => date('Y-m-d H:i:s'),
            ]);
        } elseif ($has_faktur && $has_outstanding_available) {
            // Ada faktur tapi masih ada item yang belum/sebagian terfakturkan → partial
            // Status tetap siap_faktur/partial tergantung asal, set ke partial supaya tetap
            // muncul di Admin SC dan bisa difakturkan sisa itemnya
            $this->db->where('id_so', $id_so);
            $this->db->update('tbso_sales_order', [
                'status'    => 'partial',
                'update_at' => date('Y-m-d H:i:s'),
            ]);
        }
        // Jika has_faktur tapi !has_outstanding_available: semua item yang ada sudah
        // difakturkan (meski ada qty tidak terkirim), tidak perlu ubah status di sini
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
            $stock_batch_id = $this->_stockBatchIdForMovement(
                $kd_barang,
                $exp_normalized,
                $no_lot,
                $gudang_id,
                $qty,
                'reserve'
            );
            if ($stock_batch_id <= 0) return false;

            $this->db->where('id', $stock_batch_id);
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
            $stock_batch_id = $this->_stockBatchIdForMovement(
                $kd_barang,
                $exp_normalized,
                $no_lot,
                $gudang_id,
                $qty,
                'release'
            );
            if ($stock_batch_id > 0) {
                $this->db->where('id', $stock_batch_id);
            } else {
                $this->db->where('kd_barang', $kd_barang);
                $this->db->where('gudang_id', $gudang_id);
                if (!empty($no_lot))         $this->db->where('no_lot', $no_lot);
                if (!empty($exp_normalized)) $this->db->where('expired_date', $exp_normalized);
            }
            $this->db->set('qty_reserved', 'GREATEST(0, qty_reserved - ' . (float)$qty . ')', false);
            $this->db->set('update_at', date('Y-m-d H:i:s'));
            $this->db->update('tberp_stock_batch');

            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd_barang,
                'gudang_id'    => $gudang_id,
                'no_lot'       => $no_lot,
                'expired_date' => $exp_normalized,
                'qty'          => $qty,
                'tipe'         => 'RELEASE',
                'ref_no'       => $no_so,
                'ref_type'     => 'SALES_ORDER_CANCEL',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) { /* ignore */ }
    }

    // ================================================================
    // VALIDASI STOK
    // ================================================================

    private function _reserved_qty_for_so_batch($id_so, $kd_barang, $exp_date, $no_lot)
    {
        $exp_normalized = $this->_normalizeDate($exp_date);
        $lot = trim((string)$no_lot);

        $this->db->select('COALESCE(SUM(qty - COALESCE(qty_faktur, 0)), 0) AS qty_reserved', false);
        $this->db->from('tbso_sales_order_detail');
        $this->db->where('id_so', $id_so);
        $this->db->where('kd_barang', $kd_barang);
        if (!empty($exp_normalized)) $this->db->where('expired_date', $exp_normalized);
        $this->db->where("COALESCE(no_lot, '') = " . $this->db->escape($lot), null, false);

        $row = $this->db->get()->row_array();
        return (float)($row['qty_reserved'] ?? 0);
    }

    /**
     * Validasi stok saat membuat/update SO.
     * $exclude_id_so: lewati reserved milik SO ini sendiri (saat edit).
     */
    public function validasi_stok($details, $gudang_id, $exclude_id_so = null)
    {
        $errors = [];
        $exclude_so = $exclude_id_so
            ? $this->db->get_where('tbso_sales_order', ['id_so' => $exclude_id_so])->row_array()
            : null;

        foreach ($details as $d) {
            $stock     = $this->cek_stock(
                $d['kd_barang'],
                $d['expired_date'],
                $gudang_id,
                $d['no_lot'] ?? null
            );
            $available = $stock ? (float)$stock['available_stock'] : 0;

            if ($exclude_so && (string)$exclude_so['gudang_id'] === (string)$gudang_id) {
                $available += $this->_reserved_qty_for_so_batch(
                    $exclude_id_so,
                    $d['kd_barang'],
                    $d['expired_date'],
                    $d['no_lot'] ?? null
                );
            }

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
                lcs.action                 AS sales_confirm_status,
                lcs.confirm_by             AS sales_confirm_by,
                lcs.confirm_at             AS sales_confirm_at,
                lcs.note                   AS sales_confirm_note,
                (SELECT COUNT(DISTINCT kd_barang) FROM tb_detail_do WHERE kd_do = a.kd_do) AS totalbarang,
                (SELECT COUNT(DISTINCT kd_faktur)  FROM tb_detail_do WHERE kd_do = a.kd_do) AS totalfaktur
            FROM tb_do a
            LEFT JOIN tb_log_confirm_sales lcs
                ON lcs.id = (
                    SELECT l2.id
                    FROM tb_log_confirm_sales l2
                    WHERE l2.kd_do = a.kd_do
                    ORDER BY l2.confirm_at DESC, l2.id DESC
                    LIMIT 1
                )
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
            'status' => ($action === 'siap') ? 3 : 2,
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

    public function repost_item_faktur($id_faktur, array $id_fd_list, $repost_by)
    {
        if (empty($id_fd_list)) return ['errors' => ['Tidak ada item yang dipilih.']];

        $faktur = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$faktur) return ['errors' => ['Faktur tidak ditemukan.']];
        if ($faktur['status'] === 'cancelled') return ['errors' => ['Faktur sudah dibatalkan.']];

        // Cek faktur belum masuk DO
        $in_do = $this->db->get_where('tb_detail_do', ['kd_faktur' => $faktur['no_faktur']])->num_rows();
        if ($in_do > 0) return ['errors' => ['Faktur sudah masuk Delivery Order, tidak bisa direpost.']];

        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $faktur['id_so']])->row_array();
        if (!$so) return ['errors' => ['SO tidak ditemukan.']];

        // Ambil baris faktur detail yang dipilih
        $fd_rows = $this->db
            ->where('id_faktur', $id_faktur)
            ->where_in('id', $id_fd_list)
            ->get('tbso_faktur_detail')
            ->result_array();

        if (empty($fd_rows)) return ['errors' => ['Item faktur tidak ditemukan.']];

        $this->db->trans_start();
        $qty_col = $this->_stockQtyColumn();

        foreach ($fd_rows as $fd) {
            $qty          = (float)$fd['qty'];
            $exp          = $this->_normalizeDate($fd['expired_date'] ?? '');
            $gudang_id    = $fd['gudang_id'] ?: $so['gudang_id'];
            $kd_barang    = $fd['kd_barang'];
            $no_lot       = $fd['no_lot'] ?? null;
            $id_so_detail = (int)$fd['id_so_detail'];

            // 1. Hapus baris faktur detail
            $this->db->delete('tbso_faktur_detail', ['id' => $fd['id']]);

            // 2. Kembalikan stok fisik (IN) + tambah kembali qty_reserved
            $this->db->select('id');
            $this->db->from('tberp_stock_batch');
            $this->db->where('kd_barang', $kd_barang);
            $this->db->where('gudang_id', $gudang_id);
            if (!empty($exp))    $this->db->where('expired_date', $exp);
            if (!empty($no_lot)) $this->db->where('no_lot', $no_lot);
            $this->db->limit(1);
            $batch = $this->db->get()->row_array();

            if ($batch) {
                $this->db->where('id', $batch['id']);
                $this->db->set($qty_col,       $qty_col . ' + ' . $qty, false);
                $this->db->set('qty_reserved', 'qty_reserved + ' . $qty, false);
                $this->db->set('update_at',    date('Y-m-d H:i:s'));
                $this->db->update('tberp_stock_batch');
            }

            // Ledger: IN (stok kembali)
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd_barang,
                'gudang_id'    => $gudang_id,
                'no_lot'       => $no_lot,
                'expired_date' => $exp,
                'qty'          => $qty,
                'tipe'         => 'IN',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'REPOST_FAKTUR',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // Ledger: RESERVE (lock kembali untuk SO)
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd_barang,
                'gudang_id'    => $gudang_id,
                'no_lot'       => $no_lot,
                'expired_date' => $exp,
                'qty'          => $qty,
                'tipe'         => 'RESERVE',
                'ref_no'       => $so['no_so'],
                'ref_type'     => 'REPOST_FAKTUR_RESERVE',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            // 3. Kurangi qty_faktur di SO detail
            $this->db->where('id', $id_so_detail);
            $this->db->set('qty_faktur', 'GREATEST(0, qty_faktur - ' . $qty . ')', false);
            $this->db->update('tbso_sales_order_detail');

            // 4. Reset checker_loaded ke 0 (item harus dipilih ulang oleh checker)
            $this->db->where('id', $id_so_detail);
            $this->db->update('tbso_sales_order_detail', ['checker_loaded' => 0]);
        }

        // 5. Cek sisa detail faktur — jika kosong, cancel faktur
        $sisa_detail = $this->db->where('id_faktur', $id_faktur)->count_all_results('tbso_faktur_detail');
        if ($sisa_detail === 0) {
            $this->db->where('id_faktur', $id_faktur);
            $this->db->update('tbso_faktur_penjualan', [
                'status'    => 'cancelled',
                'update_by' => $repost_by,
                'update_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            // Recalculate total tonase/kubikasi faktur yang tersisa
            $this->db->select('SUM(qty * berat_gram / 1000000) AS t, SUM(qty * kubikasi_m3) AS k', false);
            $this->db->where('id_faktur', $id_faktur);
            $sums = $this->db->get('tbso_faktur_detail')->row_array();
            $this->db->where('id_faktur', $id_faktur);
            $this->db->update('tbso_faktur_penjualan', [
                'total_tonase'   => round((float)($sums['t'] ?? 0), 6),
                'total_kubikasi' => round((float)($sums['k'] ?? 0), 6),
                'update_by'      => $repost_by,
                'update_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        // 6. Update status SO
        $this->_cek_dan_repost_so($faktur['id_so']);

        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            return ['errors' => ['Terjadi kesalahan database saat repost.']];
        }
        return ['success' => true, 'sisa_detail' => $sisa_detail];
    }

    /**
     * Setelah repost, tentukan status SO:
     * - Jika ada qty_faktur > 0 di salah satu item → partial
     * - Jika semua qty_faktur = 0 → kembali ke siap_faktur
     */
    private function _cek_dan_repost_so($id_so)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so) return;

        $rows = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        $has_faktur = false;
        foreach ($rows as $r) {
            if ((float)$r['qty_faktur'] > 0.001) {
                $has_faktur = true;
                break;
            }
        }

        $new_status = $has_faktur ? 'partial' : 'siap_faktur';
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => $new_status,
            'update_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function proses_split_faktur($parent_faktur, $parent_details, $splits, $username)
    {
        $this->db->trans_start();

        // 1. Update parent faktur: tandai telah dipecah
        $this->db->where('id_faktur', $parent_faktur['id_faktur']);
        $this->db->update('tbso_faktur_penjualan', [
            'is_split_parent' => 1,
            'update_by'       => $username,
            'update_at'       => date('Y-m-d H:i:s')
        ]);

        $this->load->model('M_ActivityLog');
        
        $this->M_ActivityLog->log(
            $parent_faktur['no_so'], 
            $parent_faktur['no_faktur'], 
            'SPLIT_FAKTUR',
            'Faktur Z ' . $parent_faktur['no_faktur'] . ' dipecah oleh ' . $username,
            $username,
            'Faktur dipecah menjadi ' . count($splits) . ' faktur turunan.'
        );

        $generated_numbers = [];

        // 2. Buat child faktur
        foreach ($splits as $idx => $s) {
            $kd_cust = $s['kd_customer'];
            $cust = $this->db->get_where('tb_customer', ['kd_customer' => $kd_cust])->row_array();
            $customer_name = $cust ? $cust['nama_customer'] : 'Unknown Customer';

            $no_faktur_child = $this->_generate_and_track_no_faktur('Z', $generated_numbers);

            $fh = [
                'no_faktur'           => $no_faktur_child,
                'id_so'               => $parent_faktur['id_so'],
                'no_so'               => $parent_faktur['no_so'],
                'kd_customer'         => $kd_cust,
                'customer_name'       => $customer_name,
                'gudang_id'           => $parent_faktur['gudang_id'],
                'tanggal_faktur'      => $parent_faktur['tanggal_faktur'],
                'tanggal_jatuh_tempo' => $parent_faktur['tanggal_jatuh_tempo'],
                'salesman'            => $parent_faktur['salesman'],
                'cara_pembayaran'     => $parent_faktur['cara_pembayaran'],
                'jtempo'              => $parent_faktur['jtempo'],
                'tempo'               => $parent_faktur['tempo'],
                'catatan'             => 'Pecahan dari Faktur Z ' . $parent_faktur['no_faktur'] . "\n" . ($parent_faktur['catatan'] ?? ''),
                'status'              => 'confirmed',
                'parent_id_faktur'    => $parent_faktur['id_faktur'],
                'is_split_parent'     => 0,
                'create_by'           => $username,
                'create_at'           => date('Y-m-d H:i:s'),
                'total_tonase'        => 0,
                'total_kubikasi'      => 0
            ];

            $this->db->insert('tbso_faktur_penjualan', $fh);
            $child_id_faktur = $this->db->insert_id();

            $items = $s['items'] ?? [];
            $child_details_logged = [];

            foreach ($parent_details as $pd) {
                $itemId = $pd['id'];
                $qty_allocated = isset($items[$itemId]) ? (float)$items[$itemId] : 0.0;

                if ($qty_allocated > 0) {
                    $isi = max(1, (int)($pd['isi_per_box'] ?? 1));
                    $qty_box = floor($qty_allocated / $isi);
                    $qty_satuan = fmod($qty_allocated, $isi);

                    $subtotal_before_disc = $qty_allocated * (float)$pd['hrg_satuan'];
                    $subtotal_after_disc  = $subtotal_before_disc * (1 - ((float)($pd['disc'] ?? 0) / 100));
                    $tax_rate             = (float)($pd['pajak'] ?? 0);
                    $tax_value            = $subtotal_after_disc * ($tax_rate / 100);
                    $total_harga          = $subtotal_after_disc + $tax_value;

                    $fd = [
                        'id_faktur'            => $child_id_faktur,
                        'no_faktur'            => $no_faktur_child,
                        'id_so'                => $pd['id_so'],
                        'id_so_detail'         => $pd['id_so_detail'],
                        'kd_barang'            => $pd['kd_barang'],
                        'nama_barang'          => $pd['nama_barang'],
                        'no_lot'               => $pd['no_lot'],
                        'expired_date'         => $pd['expired_date'],
                        'qty'                  => $qty_allocated,
                        'qty_box'              => $qty_box,
                        'qty_satuan'           => $qty_satuan,
                        'isi_per_box'          => $pd['isi_per_box'],
                        'satuan'               => $pd['satuan'],
                        'hrg_satuan'           => $pd['hrg_satuan'],
                        'hrg_pokok'            => $pd['hrg_pokok'],
                        'disc'                 => $pd['disc'],
                        'pajak'                => $pd['pajak'],
                        'subtotal_before_disc' => $subtotal_before_disc,
                        'subtotal_after_disc'  => $subtotal_after_disc,
                        'total_harga'          => $total_harga,
                        'berat_gram'           => $pd['berat_gram'],
                        'kubikasi_m3'          => $pd['kubikasi_m3'],
                        'gudang_id'            => $pd['gudang_id'],
                        'create_by'            => $username
                    ];

                    $this->db->insert('tbso_faktur_detail', $fd);
                    $child_details_logged[] = $pd['nama_barang'] . " (" . $qty_allocated . " " . $pd['satuan'] . ")";
                }
            }

            $this->db->select('SUM(qty * berat_gram / 1000000) AS t, SUM(qty * kubikasi_m3) AS k', false);
            $this->db->where('id_faktur', $child_id_faktur);
            $sums = $this->db->get('tbso_faktur_detail')->row_array();

            $this->db->where('id_faktur', $child_id_faktur);
            $this->db->update('tbso_faktur_penjualan', [
                'total_tonase'   => round((float)($sums['t'] ?? 0), 6),
                'total_kubikasi' => round((float)($sums['k'] ?? 0), 6)
            ]);

            $this->M_ActivityLog->log(
                $parent_faktur['no_so'],
                $no_faktur_child,
                'BUAT_FAKTUR_TURUNAN',
                'Faktur turunan ' . $no_faktur_child . ' dibuat untuk customer ' . $customer_name . ' (' . $kd_cust . ') dari induk ' . $parent_faktur['no_faktur'],
                $username,
                "Item:\n" . implode("\n", $child_details_logged)
            );
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function _generate_and_track_no_faktur($prefix, &$generated_numbers)
    {
        $no_faktur = $this->generate_no_faktur($prefix);
        while (in_array($no_faktur, $generated_numbers, true)) {
            $base = substr($no_faktur, 0, -4);
            $last = (int)substr($no_faktur, -4);
            $no_faktur = $base . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        $generated_numbers[] = $no_faktur;
        return $no_faktur;
    }
}
