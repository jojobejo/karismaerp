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
    const LIMIT_KONTAK_PERSON_FAKTUR_H = 250000000; // Maksimal akumulasi nominal Faktur H per Kontak Person (Rp 250.000.000)

    public function __construct()
    {
        parent::__construct();
        $this->_ensure_columns();
    }

    /**
     * Memastikan kolom pembeda so_source dan tanggal_selesai_do tersedia di tabel SO dan Faktur.
     */
    private function _ensure_columns()
    {
        if ($this->db->table_exists('tbso_sales_order')) {
            if (!$this->db->field_exists('so_source', 'tbso_sales_order')) {
                $this->db->query("ALTER TABLE `tbso_sales_order` ADD COLUMN `so_source` VARCHAR(20) NOT NULL DEFAULT 'SALES' AFTER `status`");
            }
        }

        if ($this->db->table_exists('tbso_faktur_penjualan')) {
            if (!$this->db->field_exists('so_source', 'tbso_faktur_penjualan')) {
                $this->db->query("ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `so_source` VARCHAR(20) NOT NULL DEFAULT 'SALES' AFTER `status`");
            }
            if (!$this->db->field_exists('tanggal_selesai_do', 'tbso_faktur_penjualan')) {
                $this->db->query("ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `tanggal_selesai_do` DATE NULL DEFAULT NULL AFTER `tanggal_faktur`");
            }
        }
    }

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
                SELECT no_faktur AS nomor
                FROM tbso_faktur_z_pecah
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
        ", [$prefix . '%', $prefix . '%', $prefix . '%', $prefix . '%'])->row();

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
            $names = [strtolower($nama_sales)];

            // Cari mapping nama karyawan dan username dari tb_karyawan
            $karyawan = $this->db->select('nm_karyawan, username')
                ->where('username', $nama_sales)
                ->or_where('nm_karyawan', $nama_sales)
                ->get('tb_karyawan')
                ->row();

            if ($karyawan) {
                if (!empty($karyawan->nm_karyawan)) $names[] = strtolower(trim((string)$karyawan->nm_karyawan));
                if (!empty($karyawan->username))    $names[] = strtolower(trim((string)$karyawan->username));
            }
            $names = array_values(array_unique(array_filter($names)));

            $this->db->group_start();
            foreach ($names as $idx => $n) {
                if ($idx === 0) {
                    $this->db->where('LOWER(TRIM(nama_sales)) = ' . $this->db->escape($n), null, false);
                } else {
                    $this->db->or_where('LOWER(TRIM(nama_sales)) = ' . $this->db->escape($n), null, false);
                }
            }
            $this->db->group_end();
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

        $names = [strtolower($nama_sales)];
        $karyawan = $this->db->select('nm_karyawan, username')
            ->where('username', $nama_sales)
            ->or_where('nm_karyawan', $nama_sales)
            ->get('tb_karyawan')
            ->row();

        if ($karyawan) {
            if (!empty($karyawan->nm_karyawan)) $names[] = strtolower(trim((string)$karyawan->nm_karyawan));
            if (!empty($karyawan->username))    $names[] = strtolower(trim((string)$karyawan->username));
        }
        $names = array_values(array_unique(array_filter($names)));

        $this->db->where('kd_customer', $kd_customer);
        $this->db->group_start();
        foreach ($names as $idx => $n) {
            if ($idx === 0) {
                $this->db->where('LOWER(TRIM(nama_sales)) = ' . $this->db->escape($n), null, false);
            } else {
                $this->db->or_where('LOWER(TRIM(nama_sales)) = ' . $this->db->escape($n), null, false);
            }
        }
        $this->db->group_end();

        return $this->db->count_all_results('tb_customer') > 0;
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
        $row = $this->db->get_where('tbpo_barang', ['kode_barang' => $kd_barang])->row_array();
        if (!$row) return null;
        return $this->_normalize_barang($row);
    }

    private function _get_master_bulk(array $kd_list)
    {
        if (empty($kd_list)) return [];
        $unique_kds = array_values(array_unique(array_filter($kd_list)));
        if (empty($unique_kds)) return [];

        $rows = $this->db->where_in('kode_barang', $unique_kds)
            ->get('tbpo_barang')
            ->result_array();

        // Fetch HPP from tb_lpb_detail for these items
        $hpp_map = [];
        $lpb_rows = $this->db->query("
            SELECT d.kd_barang, d.harga_satuan
            FROM tb_lpb_detail d
            JOIN (
                SELECT kd_barang, MAX(id_detail_lpb) AS max_id
                FROM tb_lpb_detail
                WHERE kd_barang IN ? AND COALESCE(harga_satuan, 0) > 0
                GROUP BY kd_barang
            ) latest ON d.id_detail_lpb = latest.max_id
        ", [$unique_kds])->result_array();

        foreach ($lpb_rows as $lr) {
            $hpp_map[$lr['kd_barang']] = (float)$lr['harga_satuan'];
        }

        $map = [];
        foreach ($rows as $r) {
            $kd = $r['kode_barang'];
            $normalized = $this->_normalize_barang($r);
            if (isset($hpp_map[$kd]) && $hpp_map[$kd] > 0) {
                $normalized['hpp'] = $hpp_map[$kd];
            }
            $map[$kd] = $normalized;
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
        $this->db->join('tbpo_barang mb', 'mb.kode_barang = sb.kd_barang', 'left');

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

        $this->db->group_by(['so.id_so', 'c.nama_customer', 'c.regional', 'c.kd_rute']);
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
            SUM(CASE WHEN COALESCE(sd.checker_loaded, 0) = 2 THEN 1 ELSE 0 END) AS jumlah_item_ditolak,
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
        $this->db->where('so.status', 'siap_faktur');
        $this->db->where("(so.so_source IS NULL OR so.so_source != 'LOBY')", null, false);

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

        $this->db->group_by(['so.id_so', 'c.nama_customer', 'c.nama_kios', 'c.regional', 'c.kd_rute']);
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
                COALESCE(SUM(total_harga / (1 + COALESCE(pajak, 0) / 100)), 0) AS total_nilai_faktur,
                COALESCE(SUM(total_harga - (total_harga / (1 + COALESCE(pajak, 0) / 100))), 0) AS total_pajak,
                COALESCE(SUM(total_harga), 0) AS grand_total
            FROM tbso_faktur_detail
            GROUP BY id_faktur
        ";

        // Subquery: hitung item ditolak (checker_loaded=2) per SO
        $ditolak_summary = "
            SELECT id_so,
                   SUM(CASE WHEN COALESCE(checker_loaded, 0) = 2 THEN 1 ELSE 0 END) AS jumlah_item_ditolak,
                   COALESCE(SUM(COALESCE(qty_tidak_terkirim, 0)), 0) AS total_qty_tidak_terkirim
            FROM tbso_sales_order_detail
            GROUP BY id_so
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
            COALESCE(fs.grand_total, 0) AS grand_total,
            COALESCE(ds.jumlah_item_ditolak, 0) AS jumlah_item_ditolak,
            COALESCE(ds.total_qty_tidak_terkirim, 0) AS total_qty_tidak_terkirim
        ');
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tbso_sales_order so', 'so.id_so = f.id_so', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->join('(' . $detail_summary . ') fs', 'fs.id_faktur = f.id_faktur', 'left');
        $this->db->join('(' . $ditolak_summary . ') ds', 'ds.id_so = f.id_so', 'left');
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
        $where = "WHERE COALESCE(NULLIF(so.kd_rute, ''), '') <> '' AND so.status IN ('open', 'partial')";
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
        $where = "WHERE (so.so_source IS NULL OR so.so_source != 'LOBY') AND so.kd_rute = ? AND so.status IN ('open', 'partial')";

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
        $where = "WHERE (so.so_source IS NULL OR so.so_source != 'LOBY') AND (
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
        $where = "WHERE (so.so_source IS NULL OR so.so_source != 'LOBY') AND (
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
        $where = "WHERE (so.so_source IS NULL OR so.so_source != 'LOBY') AND (
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
        $this->db->set('total_harga', '((qty * ' . $harga . ') * (1 - (' . $disc . ' / 100)))', false);
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
            ->select('d.id AS id_so_detail, d.*, b.kelompok_dagang, g.DESKRIPSI AS kelompok_dagang_deskripsi')
            ->from('tbso_sales_order_detail d')
            ->join('tbpo_barang b', 'd.kd_barang = b.kode_barang', 'left')
            ->join('tbkeu_kelompok_dagang g', 'b.kelompok_dagang = g.NOINDEX', 'left')
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
            if ((int)($row['checker_loaded'] ?? 0) === 2) {
                $row['qty_available_faktur'] = 0;
            } else {
                $row['qty_available_faktur'] = max(0, min(
                    $row['qty_outstanding'],
                    $row['qty_siap_faktur'] - $row['qty_faktur']
                ));
            }

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
            'cara_pembayaran'   => $header['cara_pembayaran'] ?? 'cash',
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
            'cara_pembayaran'   => $header['cara_pembayaran'] ?? 'cash',
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
        // Support lookup by numeric id_faktur OR by no_faktur string (e.g. from buku besar drilldown)
        if (is_numeric($id_faktur)) {
            $this->db->where('f.id_faktur', (int)$id_faktur);
        } else {
            $this->db->where('f.no_faktur', $id_faktur);
        }
        return $this->db->get()->row_array();
    }

    /**
     * Ambil detail baris faktur.
     */
    public function get_faktur_detail($id_faktur)
    {
        return $this->db
            ->select('fd.*, sod.checker_loaded, sod.qty_tidak_terkirim')
            ->from('tbso_faktur_detail fd')
            ->join('tbso_sales_order_detail sod', 'sod.id = fd.id_so_detail', 'left')
            ->where('fd.id_faktur', $id_faktur)
            ->get()
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
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = fd.kd_barang
            WHERE f.status = 'confirmed'
            AND (f.so_source IS NULL OR f.so_source != 'LOBY')
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
            LEFT JOIN tbpo_barang mb ON mb.kode_barang = fd.kd_barang
            WHERE f.status IN ('selesai', 'selesai_do')
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
            $this->db->set('total_harga', '((qty * ' . (float)($item['hrg_satuan'] ?? 0) . ') * (1 - (' . (float)($item['disc'] ?? 0) . ' / 100)))', false);
            $this->db->update('tbso_sales_order_detail');
        }

        // ── Perhitungan Jurnal & Simpan ke Database ────────────────
        $total_nilai_pesanan = 0;
        foreach ($faktur_items as $item) {
            $total_nilai_pesanan += (float)($item['subtotal_after_disc'] ?? 0);
        }
        $tax_rate = (float)($faktur_items[0]['pajak'] ?? 0);
        $div_factor = 1 + ($tax_rate / 100);
        
        $jurnal_piutang = round($total_nilai_pesanan);
        $jurnal_penjualan = round($jurnal_piutang / $div_factor);
        $jurnal_ppn_keluar = $jurnal_piutang - $jurnal_penjualan;

        $fj = [
            'id_faktur'      => $id_faktur,
            'no_faktur'      => $no_faktur,
            'piutang_dagang' => $jurnal_piutang,
            'penjualan'      => $jurnal_penjualan,
            'ppn_keluar'     => $jurnal_ppn_keluar,
            'created_at'     => date('Y-m-d H:i:s')
        ];
        if ($this->db->table_exists('tbso_faktur_jurnal')) {
            $this->db->insert('tbso_faktur_jurnal', $fj);
        }

        if ($this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
            $this->load->library('Accounting_source_service');
            $journal = $this->accounting_source_service->post_sales_invoice(
                $no_faktur,
                '',
                (int)($faktur_header['created_by_id'] ?? 0) ?: null,
                true
            );

            if (empty($journal['success'])) {
                $this->db->trans_rollback();
                return [
                    'errors' => [
                        'Faktur batal disimpan karena jurnal otomatis gagal: '
                        . ($journal['message'] ?? 'Posting jurnal gagal.')
                    ],
                ];
            }
        } else {
            $this->db->trans_rollback();
            return [
                'errors' => [
                    'Faktur batal disimpan karena schema jurnal accounting belum tersedia.'
                ],
            ];
        }

        // ── Cek apakah semua outstanding = 0 → Completed ────────────
        $this->_cek_dan_complete_so($id_so);

        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            return false;
        }

        return [
            'id_faktur' => $id_faktur,
            'journal' => $journal['data'] ?? null,
        ];
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
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        $current_status = $so['status'] ?? '';
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
            // Ada faktur tapi masih ada item yang belum/sebagian terfakturkan.
            // Jika SO asalnya siap_faktur, pertahankan statusnya agar tetap muncul
            // di Admin SC untuk faktur berikutnya (contoh: pisah pajak/non-pajak).
            $next_status = $current_status === 'siap_faktur' ? 'siap_faktur' : 'partial';

            $this->db->where('id_so', $id_so);
            $this->db->update('tbso_sales_order', [
                'status'    => $next_status,
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

            // 4. Reset checker_loaded ke 0 HANYA jika bukan 2 (tidak dimuat)
            // Jika checker_loaded = 2 (tidak dimuat), pertahankan untuk matching logic
            $current_detail = $this->db->get_where('tbso_sales_order_detail', ['id' => $id_so_detail])->row_array();
            if ($current_detail && (int)($current_detail['checker_loaded'] ?? 0) !== 2) {
                $this->db->where('id', $id_so_detail);
                $this->db->update('tbso_sales_order_detail', ['checker_loaded' => 0]);
            }
        }

        // A. Hapus jurnal lama terlebih dahulu (baik untuk cancel maupun recalculate)
        $no_faktur = $faktur['no_faktur'];
        $q1 = $this->db->select('id_jurnal')
            ->where_in('idempotency_key', [
                'SALES_INVOICE-FAKTUR-' . $no_faktur,
                'GOODS_ISSUE-FAKTUR-' . $no_faktur
            ])
            ->get('tbkeu_jurnal')
            ->result_array();
            
        $q2 = $this->db->select('id_jurnal')
            ->where('source_type', 'PROMOSI_PENJUALAN')
            ->where('source_id', $id_faktur)
            ->get('tbkeu_jurnal')
            ->result_array();
            
        $old_journals = array_merge($q1, $q2);

        if (!empty($old_journals)) {
            $old_ids = array_column($old_journals, 'id_jurnal');
            $this->db->where_in('id_jurnal', $old_ids)->delete('tbkeu_jurnal_detail');
            if ($this->db->table_exists('tbkeu_jurnal_log')) {
                $this->db->where_in('id_jurnal', $old_ids)->delete('tbkeu_jurnal_log');
            }
            $this->db->where_in('id_jurnal', $old_ids)->delete('tbkeu_jurnal');
        }
        $this->db->delete('tbso_faktur_jurnal', ['id_faktur' => $id_faktur]);

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

            // B. Hitung ulang dan posting jurnal baru yang terupdate
            $this->db->select('SUM(subtotal_after_disc) AS total_after', false);
            $this->db->where('id_faktur', $id_faktur);
            $remaining_sums = $this->db->get('tbso_faktur_detail')->row_array();
            $total_nilai_pesanan = (float)($remaining_sums['total_after'] ?? 0);

            $tax_rate = 0.0;
            $first_item = $this->db->limit(1)->get_where('tbso_faktur_detail', ['id_faktur' => $id_faktur])->row_array();
            if ($first_item) {
                $tax_rate = (float)($first_item['pajak'] ?? 0);
            }
            $div_factor = 1 + ($tax_rate / 100);

            $jurnal_piutang = round($total_nilai_pesanan);
            $jurnal_penjualan = round($jurnal_piutang / $div_factor);
            $jurnal_ppn_keluar = $jurnal_piutang - $jurnal_penjualan;

            $fj = [
                'id_faktur'      => $id_faktur,
                'no_faktur'      => $faktur['no_faktur'],
                'piutang_dagang' => $jurnal_piutang,
                'penjualan'      => $jurnal_penjualan,
                'ppn_keluar'     => $jurnal_ppn_keluar,
                'created_at'     => date('Y-m-d H:i:s')
            ];
            if ($this->db->table_exists('tbso_faktur_jurnal')) {
                $this->db->insert('tbso_faktur_jurnal', $fj);
            }

            if ($this->db->table_exists('tbkeu_jurnal') && $this->db->table_exists('tbkeu_jurnal_detail')) {
                $this->load->library('Accounting_source_service');
                $current_user_id = (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0);
                
                $journal = $this->accounting_source_service->post_sales_invoice(
                    $faktur['no_faktur'],
                    '',
                    $current_user_id ?: null,
                    true
                );

                if (empty($journal['success'])) {
                    $this->db->trans_rollback();
                    return [
                        'errors' => [
                            'Repost gagal karena posting jurnal baru tidak berhasil: '
                            . ($journal['message'] ?? 'Posting jurnal gagal.')
                        ],
                    ];
                }
            }
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
     * Setelah repost, set status SO kembali ke siap_faktur
     * agar SO tetap muncul di halaman Admin SC untuk difakturkan ulang
     */
    private function _cek_dan_repost_so($id_so)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so) return;

        // Setelah repost, SO selalu kembali ke status siap_faktur
        // agar bisa difakturkan ulang di halaman Admin SC
        $new_status = 'siap_faktur';
        
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => $new_status,
            'update_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil seluruh faktur yang berawalan Z (atau SO is_faktur_z = 1)
     * untuk modul Pecah Faktur Z.
     */
    public function get_faktur_z_list($filter = [])
    {
        $detail_summary = "
            SELECT
                id_faktur,
                COUNT(*) AS total_barang,
                COALESCE(SUM(qty), 0) AS total_qty,
                COALESCE(SUM(total_harga / (1 + COALESCE(pajak, 0) / 100)), 0) AS total_nilai_faktur,
                COALESCE(SUM(total_harga - (total_harga / (1 + COALESCE(pajak, 0) / 100))), 0) AS total_pajak,
                COALESCE(SUM(total_harga), 0) AS grand_total
            FROM tbso_faktur_detail
            GROUP BY id_faktur
        ";

        $this->db->select('
            f.*,
            so.id_so,
            so.is_faktur_z,
            so.kd_rute AS so_kd_rute,
            c.nama_customer,
            c.nama_kios,
            c.regional,
            c.kd_rute AS customer_kd_rute,
            COALESCE(f.customer_name, c.nama_customer) AS display_customer_name,
            p.no_faktur AS parent_no_faktur,
            COALESCE(p.customer_name, pc.nama_customer) AS parent_customer_name,
            COALESCE(fs.total_barang, 0) AS total_barang,
            COALESCE(fs.total_qty, 0) AS total_qty,
            COALESCE(fs.total_nilai_faktur, 0) AS total_nilai_faktur,
            COALESCE(fs.total_pajak, 0) AS total_pajak,
            COALESCE(fs.grand_total, 0) AS grand_total
        ');
        $this->db->from('tbso_faktur_penjualan f');
        $this->db->join('tbso_sales_order so', 'so.id_so = f.id_so', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = f.kd_customer', 'left');
        $this->db->join('tbso_faktur_penjualan p', 'p.id_faktur = f.parent_id_faktur', 'left');
        $this->db->join('tb_customer pc', 'pc.kd_customer = p.kd_customer', 'left');
        $this->db->join('(' . $detail_summary . ') fs', 'fs.id_faktur = f.id_faktur', 'left');

        // Kriteria utama: Faktur yang diawali Z atau SO merupakan Faktur Z
        $this->db->group_start();
        $this->db->like('f.no_faktur', 'Z', 'after');
        $this->db->or_where('so.is_faktur_z', 1);
        $this->db->group_end();

        if (!empty($filter['date1'])) {
            $this->db->where('f.tanggal_faktur >=', $filter['date1']);
        }
        if (!empty($filter['date2'])) {
            $this->db->where('f.tanggal_faktur <=', $filter['date2']);
        }
        if (!empty($filter['status_faktur']) && $filter['status_faktur'] !== 'all') {
            $this->db->where('f.status', $filter['status_faktur']);
        }
        if (!empty($filter['customer_id'])) {
            $this->db->where('c.id', $filter['customer_id']);
        }
        if (!empty($filter['create_by'])) {
            $this->db->where('f.create_by', $filter['create_by']);
        }
        if (!empty($filter['search'])) {
            $q = trim((string)$filter['search']);
            $this->db->group_start();
            $this->db->like('f.no_faktur', $q);
            $this->db->or_like('f.no_so', $q);
            $this->db->or_like('f.customer_name', $q);
            $this->db->or_like('c.nama_customer', $q);
            $this->db->group_end();
        }

        if (!empty($filter['status_pecah'])) {
            if ($filter['status_pecah'] === 'belum_dipecah') {
                $this->db->where('f.parent_id_faktur IS NULL', null, false);
                $this->db->group_start();
                $this->db->where('f.is_split_parent', 0);
                $this->db->or_where('f.is_split_parent IS NULL', null, false);
                $this->db->group_end();
            } elseif ($filter['status_pecah'] === 'sudah_dipecah') {
                $this->db->where('f.parent_id_faktur IS NULL', null, false);
                $this->db->where('f.is_split_parent', 1);
            } elseif ($filter['status_pecah'] === 'turunan') {
                $this->db->where('f.parent_id_faktur IS NOT NULL', null, false);
            }
        }

        $this->db->order_by('f.id_faktur', 'DESC');
        $rows = $this->db->get()->result_array();

        // Ambil data child faktur untuk semua parent faktur yang ada di list dari tabel terpisah tbso_faktur_z_pecah
        $parent_ids = [];
        foreach ($rows as $r) {
            if (empty($r['parent_id_faktur'])) {
                $parent_ids[] = (int)$r['id_faktur'];
            }
        }

        $child_map = [];
        $allocated_map = [];
        if (!empty($parent_ids)) {
            $children = $this->db->select('p.id_pecah, p.no_faktur, COALESCE(pd.parent_id_faktur, p.parent_id_faktur) AS parent_id_faktur, p.customer_name, p.status, p.tanggal_faktur, COALESCE(SUM(pd.total_harga), 0) AS grand_total')
                ->from('tbso_faktur_z_pecah p')
                ->join('tbso_faktur_z_pecah_detail pd', 'pd.id_pecah = p.id_pecah', 'left')
                ->where('(pd.parent_id_faktur IN (' . implode(',', $parent_ids) . ') OR p.parent_id_faktur IN (' . implode(',', $parent_ids) . '))', null, false)
                ->where('p.status !=', 'cancelled')
                ->group_by('p.id_pecah, p.no_faktur, COALESCE(pd.parent_id_faktur, p.parent_id_faktur), p.customer_name, p.status, p.tanggal_faktur')
                ->order_by('p.id_pecah', 'ASC')
                ->get()
                ->result_array();

            foreach ($children as $c) {
                $pid = (int)$c['parent_id_faktur'];
                if (!isset($child_map[$pid])) {
                    $child_map[$pid] = [];
                }
                $child_map[$pid][] = $c;
            }

            // Alokasi qty per parent dari tbso_faktur_z_pecah_detail
            $child_allocations = $this->db->select('parent_id_faktur, id_so_detail, kd_barang, no_lot, expired_date, SUM(qty) as qty_allocated')
                ->from('tbso_faktur_z_pecah_detail')
                ->where_in('parent_id_faktur', $parent_ids)
                ->group_by('parent_id_faktur, id_so_detail, kd_barang, no_lot, expired_date')
                ->get()
                ->result_array();

            foreach ($child_allocations as $ca) {
                $pid = (int)$ca['parent_id_faktur'];
                $key = implode('|', [
                    $ca['id_so_detail'],
                    $ca['kd_barang'],
                    (string)$ca['no_lot'],
                    $ca['expired_date']
                ]);
                $allocated_map[$pid][$key] = (float)$ca['qty_allocated'];
            }
        }

        foreach ($rows as &$r) {
            $fid = (int)$r['id_faktur'];
            $r['child_fakturs'] = $child_map[$fid] ?? [];
            $r['child_count']   = count($r['child_fakturs']);

            if (!empty($r['parent_id_faktur'])) {
                $r['tipe_faktur'] = 'turunan';
                $r['can_split']   = false;
                $r['remaining_split_qty'] = 0;
            } else {
                // Faktur Induk
                if (in_array($r['status'], ['cancelled', 'draft'], true)) {
                    $r['tipe_faktur'] = empty($r['is_split_parent']) ? 'belum_dipecah' : 'sudah_dipecah';
                    $r['can_split']   = false;
                    $r['remaining_split_qty'] = 0;
                } else {
                    // Cek sisa kuantitas riil berdasarkan alokasi child pecahan yang sudah ada
                    $parent_alloc = $allocated_map[$fid] ?? [];
                    $has_existing_split = !empty($parent_alloc) || !empty($r['is_split_parent']) || !empty($r['child_fakturs']);

                    if ($has_existing_split) {
                        $details = $this->get_faktur_detail($fid);
                        $total_remaining = 0;
                        $has_any_allocation = false;
                        foreach ($details as $d) {
                            $key = implode('|', [
                                $d['id_so_detail'],
                                $d['kd_barang'],
                                (string)$d['no_lot'],
                                $d['expired_date']
                            ]);
                            $allocated = $parent_alloc[$key] ?? 0.0;
                            if ($allocated > 0) {
                                $has_any_allocation = true;
                            }
                            $remaining = max(0.0, (float)$d['qty'] - $allocated);
                            $total_remaining += $remaining;
                        }
                        $r['remaining_split_qty'] = $total_remaining;

                        if ($total_remaining <= 0.001) {
                            $r['tipe_faktur'] = 'sudah_dipecah';
                            $r['can_split']   = false;
                            // Sinkronkan flag is_split_parent di database jika belum 1
                            if (empty($r['is_split_parent'])) {
                                $this->db->where('id_faktur', $fid)->update('tbso_faktur_penjualan', ['is_split_parent' => 1]);
                            }
                        } elseif ($has_any_allocation) {
                            $r['tipe_faktur'] = 'dipecah_sebagian';
                            $r['can_split']   = true;
                        } else {
                            $r['tipe_faktur'] = 'belum_dipecah';
                            $r['can_split']   = true;
                            $r['remaining_split_qty'] = (float)$r['total_qty'];
                        }
                    } else {
                        $r['tipe_faktur'] = 'belum_dipecah';
                        $r['can_split']   = true;
                        $r['remaining_split_qty'] = (float)$r['total_qty'];
                    }
                }
            }
        }
        unset($r);

        return $rows;
    }

    public function proses_split_faktur($parent_faktur, $parent_details, $splits, $username)
    {
        $this->ensure_faktur_z_pecah_tables();
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
            'Faktur dipecah menjadi ' . count($splits) . ' faktur pecahan (Kode H).'
        );

        $generated_numbers = [];

        // 2. Buat child faktur di tabel terpisah tbso_faktur_z_pecah (Kode Awalan H)
        foreach ($splits as $idx => $s) {
            $kd_cust = trim((string)($s['kd_customer'] ?? ''));
            $cust_acak = $this->db->get_where('tb_customer_acak', ['kd_customer' => $kd_cust])->row_array();
            if ($cust_acak) {
                $customer_name = $cust_acak['kontak_person'] . ' (' . $cust_acak['nama_toko'] . ')';
            } else {
                $cust = $this->db->get_where('tb_customer', ['kd_customer' => $kd_cust])->row_array();
                $customer_name = $cust ? $cust['nama_customer'] : 'Unknown Customer';
            }

            // Hasil pecahan menggunakan kode awalan H
            $no_faktur_child = $this->_generate_and_track_no_faktur('H', $generated_numbers);

            $fh = [
                'no_faktur'           => $no_faktur_child,
                'parent_id_faktur'    => $parent_faktur['id_faktur'],
                'parent_no_faktur'    => $parent_faktur['no_faktur'],
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
                'create_by'           => $username,
                'create_at'           => date('Y-m-d H:i:s'),
                'total_tonase'        => 0,
                'total_kubikasi'      => 0
            ];

            $this->db->insert('tbso_faktur_z_pecah', $fh);
            $child_id_pecah = $this->db->insert_id();

            $items = $s['items'] ?? [];
            $child_details_logged = [];

            foreach ($parent_details as $pd) {
                $itemId = $pd['id'];
                $qty_allocated = isset($items[$itemId]) ? (float)$items[$itemId] : 0.0;

                if ($qty_allocated > 0) {
                    $isi = max(1, (int)($pd['isi_per_box'] ?? 1));
                    $qty_box = floor($qty_allocated / $isi);
                    $qty_satuan = fmod($qty_allocated, $isi);

                    // Harga satuan pada Faktur Pecahan H terpotong 20% dari harga Faktur Z induk
                    $hrg_satuan_pecah     = round((float)$pd['hrg_satuan'] * 0.8, 2);
                    $subtotal_before_disc = $qty_allocated * $hrg_satuan_pecah;
                    $subtotal_after_disc  = $subtotal_before_disc * (1 - ((float)($pd['disc'] ?? 0) / 100));
                    $tax_rate             = (float)($pd['pajak'] ?? 0);
                    $total_harga          = $subtotal_after_disc;

                    $fd = [
                        'id_pecah'                => $child_id_pecah,
                        'no_faktur'               => $no_faktur_child,
                        'parent_id_faktur'        => $parent_faktur['id_faktur'],
                        'parent_no_faktur'        => $parent_faktur['no_faktur'],
                        'id_faktur_detail_parent' => $pd['id'],
                        'id_so'                   => $pd['id_so'],
                        'id_so_detail'            => $pd['id_so_detail'],
                        'kd_barang'               => $pd['kd_barang'],
                        'nama_barang'             => $pd['nama_barang'],
                        'no_lot'                  => $pd['no_lot'],
                        'expired_date'            => $pd['expired_date'],
                        'qty'                     => $qty_allocated,
                        'qty_box'                 => $qty_box,
                        'qty_satuan'              => $qty_satuan,
                        'isi_per_box'             => $pd['isi_per_box'] ?? 1,
                        'satuan'                  => $pd['satuan'] ?? 'PCS',
                        'hrg_satuan'              => $hrg_satuan_pecah,
                        'hrg_pokok'               => $pd['hrg_pokok'] ?? 0,
                        'disc'                    => $pd['disc'] ?? 0,
                        'pajak'                   => $tax_rate,
                        'subtotal_before_disc'    => $subtotal_before_disc,
                        'subtotal_after_disc'     => $subtotal_after_disc,
                        'total_harga'             => $total_harga,
                        'berat_gram'              => $pd['berat_gram'] ?? 0,
                        'kubikasi_m3'             => $pd['kubikasi_m3'] ?? 0,
                        'gudang_id'               => $pd['gudang_id'] ?? $parent_faktur['gudang_id'],
                        'create_by'               => $username,
                        'create_at'               => date('Y-m-d H:i:s')
                    ];

                    $this->db->insert('tbso_faktur_z_pecah_detail', $fd);
                    $child_details_logged[] = $pd['nama_barang'] . " (" . $qty_allocated . " " . ($pd['satuan'] ?? 'PCS') . ")";
                }
            }

            $this->db->select('SUM(qty * berat_gram / 1000000) AS t, SUM(qty * kubikasi_m3) AS k', false);
            $this->db->where('id_pecah', $child_id_pecah);
            $sums = $this->db->get('tbso_faktur_z_pecah_detail')->row_array();

            $this->db->where('id_pecah', $child_id_pecah);
            $this->db->update('tbso_faktur_z_pecah', [
                'total_tonase'   => round((float)($sums['t'] ?? 0), 6),
                'total_kubikasi' => round((float)($sums['k'] ?? 0), 6)
            ]);

            $this->M_ActivityLog->log(
                $parent_faktur['no_so'],
                $no_faktur_child,
                'BUAT_FAKTUR_PECAHAN_H',
                'Faktur pecahan ' . $no_faktur_child . ' dibuat untuk customer ' . $customer_name . ' (' . $kd_cust . ') dari induk ' . $parent_faktur['no_faktur'],
                $username,
                "Item:\n" . implode("\n", $child_details_logged)
            );
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Memproses pemecahan beberapa Faktur Z sekaligus (Batch Split) menjadi N faktur pecahan (Kode H).
     *
     * @param array $parent_fakturs Array of Faktur Z induk yang dipilih (key by id_faktur)
     * @param array $parent_details Array of detail barang Faktur Z (key by tbso_faktur_detail.id)
     * @param array $splits Array of slot pecahan dari form
     * @param string $username Username pemroses
     * @return array Status proses [success => bool, total_created => int, message => string]
     */
    public function proses_split_faktur_batch($parent_fakturs, $parent_details, $splits, $username)
    {
        $this->ensure_faktur_z_pecah_tables();
        $this->db->trans_start();

        $this->load->model('M_ActivityLog');
        $generated_numbers = [];
        $created_fakturs = [];
        $affected_parent_ids = [];

        // Kumpulkan parent nomor faktur untuk catatan
        $parent_no_list = array_values(array_unique(array_column($parent_fakturs, 'no_faktur')));
        $parent_no_str = implode(', ', $parent_no_list);

        foreach ($splits as $split_idx => $s) {
            $kd_cust = trim((string)($s['kd_customer'] ?? ''));
            if (empty($kd_cust)) {
                continue; // Lewati jika customer belum dipilih
            }

            $items = $s['items'] ?? [];
            $has_qty = false;
            foreach ($items as $it) {
                if ((float)($it['qty'] ?? 0) > 0.0001) {
                    $has_qty = true;
                    break;
                }
            }
            if (!$has_qty) {
                continue; // Lewati jika tidak ada barang yang dialokasikan
            }

            $cust_acak = $this->db->get_where('tb_customer_acak', ['kd_customer' => $kd_cust])->row_array();
            if ($cust_acak) {
                $customer_name = $cust_acak['kontak_person'] . ' (' . $cust_acak['nama_toko'] . ')';
            } else {
                $cust = $this->db->get_where('tb_customer', ['kd_customer' => $kd_cust])->row_array();
                $customer_name = $cust ? $cust['nama_customer'] : ($s['customer_name'] ?? 'Unknown Customer');
            }

            // Nomor faktur pecahan berawalan kode H
            $no_faktur_child = $this->_generate_and_track_no_faktur('H', $generated_numbers);

            // Identifikasi parent faktur dari item-item yang dialokasikan pada slot ini
            $slot_parent_ids = [];
            foreach ($items as $detail_id => $it) {
                if ((float)($it['qty'] ?? 0) > 0.0001 && isset($parent_details[$detail_id])) {
                    $pid = (int)$parent_details[$detail_id]['id_faktur'];
                    $slot_parent_ids[$pid] = true;
                }
            }

            $primary_pid = !empty($slot_parent_ids) ? array_keys($slot_parent_ids)[0] : 0;
            $slot_parent = $parent_fakturs[$primary_pid] ?? reset($parent_fakturs);
            $parent_id_header = (int)($slot_parent['id_faktur'] ?? 0);

            // Jika item berasal dari Faktur Z tertentu, gunakan nomor Faktur Z tersebut
            $slot_parent_nos = [];
            foreach (array_keys($slot_parent_ids) as $pid_in_slot) {
                if (isset($parent_fakturs[$pid_in_slot]['no_faktur'])) {
                    $slot_parent_nos[] = $parent_fakturs[$pid_in_slot]['no_faktur'];
                }
            }
            $parent_no_header = !empty($slot_parent_nos) ? implode(', ', array_unique($slot_parent_nos)) : (string)($slot_parent['no_faktur'] ?? '');

            $tgl_faktur = !empty($s['tanggal_faktur']) ? $s['tanggal_faktur'] : ($slot_parent['tanggal_faktur'] ?? date('Y-m-d'));
            $tgl_tempo  = !empty($s['tanggal_jatuh_tempo']) ? $s['tanggal_jatuh_tempo'] : ($slot_parent['tanggal_jatuh_tempo'] ?? null);

            $fh = [
                'no_faktur'           => $no_faktur_child,
                'parent_id_faktur'    => $parent_id_header,
                'parent_no_faktur'    => $parent_no_header,
                'id_so'               => (int)($slot_parent['id_so'] ?? 0),
                'no_so'               => (string)($slot_parent['no_so'] ?? ''),
                'kd_customer'         => $kd_cust,
                'customer_name'       => $customer_name,
                'gudang_id'           => $slot_parent['gudang_id'] ?? null,
                'tanggal_faktur'      => $tgl_faktur,
                'tanggal_jatuh_tempo' => $tgl_tempo,
                'salesman'            => $slot_parent['salesman'] ?? null,
                'cara_pembayaran'     => $slot_parent['cara_pembayaran'] ?? 'tempo',
                'jtempo'              => (int)($slot_parent['jtempo'] ?? 0),
                'tempo'               => (int)($slot_parent['tempo'] ?? 0),
                'catatan'             => 'Pecahan Massal dari Faktur Z: ' . $parent_no_header . "\n" . trim((string)($s['catatan'] ?? '')),
                'status'              => 'confirmed',
                'create_by'           => $username,
                'create_at'           => date('Y-m-d H:i:s'),
                'total_tonase'        => 0,
                'total_kubikasi'      => 0
            ];

            $this->db->insert('tbso_faktur_z_pecah', $fh);
            $child_id_pecah = $this->db->insert_id();

            $child_details_logged = [];

            foreach ($items as $detail_id => $it) {
                $qty_allocated = (float)($it['qty'] ?? 0);
                if ($qty_allocated <= 0.0001) {
                    continue;
                }

                if (!isset($parent_details[$detail_id])) {
                    continue;
                }

                $pd = $parent_details[$detail_id];
                $pid = (int)$pd['id_faktur'];
                $affected_parent_ids[$pid] = true;

                $isi = max(1, (int)($pd['isi_per_box'] ?? 1));
                $qty_box = floor($qty_allocated / $isi);
                $qty_satuan = fmod($qty_allocated, $isi);

                // Harga satuan pada faktur pecahan H terpotong 20% dari harga Faktur Z induk
                $hrg_satuan = isset($it['hrg_satuan']) && is_numeric($it['hrg_satuan'])
                    ? (float)$it['hrg_satuan']
                    : round((float)$pd['hrg_satuan'] * 0.8, 2);

                $disc = isset($it['disc']) && is_numeric($it['disc'])
                    ? (float)$it['disc']
                    : (float)($pd['disc'] ?? 0);

                $pajak = isset($it['pajak']) && is_numeric($it['pajak'])
                    ? (float)$it['pajak']
                    : (float)($pd['pajak'] ?? 0);

                $subtotal_before = $qty_allocated * $hrg_satuan;
                $subtotal_after  = $subtotal_before * (1 - ($disc / 100));
                $total_harga     = $subtotal_after;

                $parent_item_faktur_no = $parent_fakturs[$pid]['no_faktur'] ?? $pd['no_faktur'];

                $fd = [
                    'id_pecah'                => $child_id_pecah,
                    'no_faktur'               => $no_faktur_child,
                    'parent_id_faktur'        => $pid,
                    'parent_no_faktur'        => $parent_item_faktur_no,
                    'id_faktur_detail_parent' => $pd['id'],
                    'id_so'                   => $pd['id_so'],
                    'id_so_detail'            => $pd['id_so_detail'],
                    'kd_barang'               => $pd['kd_barang'],
                    'nama_barang'             => $pd['nama_barang'],
                    'no_lot'                  => $pd['no_lot'],
                    'expired_date'            => $pd['expired_date'],
                    'qty'                     => $qty_allocated,
                    'qty_box'                 => $qty_box,
                    'qty_satuan'              => $qty_satuan,
                    'isi_per_box'             => $isi,
                    'satuan'                  => $pd['satuan'] ?? 'PCS',
                    'hrg_satuan'              => $hrg_satuan,
                    'hrg_pokok'               => (float)($pd['hrg_pokok'] ?? 0),
                    'disc'                    => $disc,
                    'pajak'                   => $pajak,
                    'subtotal_before_disc'    => $subtotal_before,
                    'subtotal_after_disc'     => $subtotal_after,
                    'total_harga'             => $total_harga,
                    'berat_gram'              => (float)($pd['berat_gram'] ?? 0),
                    'kubikasi_m3'             => (float)($pd['kubikasi_m3'] ?? 0),
                    'gudang_id'               => $pd['gudang_id'] ?? $first_parent['gudang_id'],
                    'create_by'               => $username,
                    'create_at'               => date('Y-m-d H:i:s')
                ];

                $this->db->insert('tbso_faktur_z_pecah_detail', $fd);
                $child_details_logged[] = $pd['nama_barang'] . " (" . $qty_allocated . " " . ($pd['satuan'] ?? 'PCS') . " @ Rp " . number_format($hrg_satuan, 0, ',', '.') . ")";
            }

            // Update total tonase dan kubikasi
            $this->db->select('SUM(qty * berat_gram / 1000000) AS t, SUM(qty * kubikasi_m3) AS k', false);
            $this->db->where('id_pecah', $child_id_pecah);
            $sums = $this->db->get('tbso_faktur_z_pecah_detail')->row_array();

            $this->db->where('id_pecah', $child_id_pecah);
            $this->db->update('tbso_faktur_z_pecah', [
                'total_tonase'   => round((float)($sums['t'] ?? 0), 6),
                'total_kubikasi' => round((float)($sums['k'] ?? 0), 6)
            ]);

            $this->M_ActivityLog->log(
                $slot_parent['no_so'] ?? '-',
                $no_faktur_child,
                'BUAT_FAKTUR_PECAHAN_H_BATCH',
                'Faktur pecahan massal ' . $no_faktur_child . ' dibuat untuk customer ' . $customer_name . ' (' . $kd_cust . ') dari Faktur Z: ' . $parent_no_header,
                $username,
                "Item:\n" . implode("\n", $child_details_logged)
            );

            $created_fakturs[] = $no_faktur_child;
        }

        // Tandai parent faktur yang terpengaruh sebagai telah dipecah
        if (!empty($affected_parent_ids)) {
            $this->db->where_in('id_faktur', array_keys($affected_parent_ids));
            $this->db->update('tbso_faktur_penjualan', [
                'is_split_parent' => 1,
                'update_by'       => $username,
                'update_at'       => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->trans_complete();

        return [
            'success'       => $this->db->trans_status() && !empty($created_fakturs),
            'total_created' => count($created_fakturs),
            'created_list'  => $created_fakturs
        ];
    }

    /**
     * Memproses pemecahan otomatis seluruh Faktur Z milik satu kios sekaligus (Auto-Split per Kios).
     * Aturan:
     * 1. Maksimal nilai nominal per Faktur H adalah $max_nominal_per_faktur (default Rp 25.000.000).
     * 2. Faktur Z dengan nilai <= 25 juta hanya dipecah menjadi 1 Faktur H.
     * 3. Faktur Z dengan nilai > 25 juta dipecah proporsional menjadi multiple Faktur H.
     * 4. Setiap Faktur Z terisolasi (barang tidak bercampur antar Faktur Z).
     * 5. Customer acak dialokasikan secara merata dari master customer acak kios tersebut.
     *
     * @param string $kd_customer Kode customer induk / kios
     * @param float $max_nominal_per_faktur Batas maksimal nilai per Faktur H (default 25.000.000)
     * @param string $username Username pemroses
     * @return array Hasil pemrosesan
     */
    public function auto_split_faktur_kios($kd_customer, $max_nominal_per_faktur = 25000000, $username = 'system')
    {
        $this->ensure_customer_acak_table();
        $this->ensure_faktur_z_pecah_tables();

        $max_nominal_per_faktur = (float)$max_nominal_per_faktur;
        if ($max_nominal_per_faktur <= 0) {
            $max_nominal_per_faktur = 25000000;
        }

        // 1. Ambil data customer induk
        $customer = $this->db->get_where('tb_customer', ['kd_customer' => $kd_customer])->row_array();
        $nama_kios = !empty($customer['nama_kios']) ? $customer['nama_kios'] : ($customer['nama_customer'] ?? '');

        // 2. Ambil kontak acak kios ini (hanya yang belum mencapai limit 250 juta)
        $all_contacts = $this->get_customers_acak_by_kios($nama_kios, $kd_customer);
        $contacts = [];
        if (!empty($all_contacts)) {
            foreach ($all_contacts as $c) {
                if (empty($c['is_limit_reached']) && (float)($c['total_nominal_pecah'] ?? 0) < self::LIMIT_KONTAK_PERSON_FAKTUR_H) {
                    $contacts[] = $c;
                }
            }

            if (empty($contacts)) {
                return [
                    'success' => false,
                    'message' => 'Semua kontak person customer acak untuk kios <b>' . htmlspecialchars($nama_kios) . '</b> telah mencapai batas maksimal limit transaksi Rp 250.000.000. Silakan tambahkan kontak person baru.'
                ];
            }
        }

        // 3. Ambil seluruh Faktur Z milik kios ini yang belum dipecah
        $this->db->select('*');
        $this->db->from('tbso_faktur_penjualan');
        $this->db->where('kd_customer', $kd_customer);
        $this->db->like('no_faktur', 'Z', 'after');
        $this->db->where('is_split_parent', 0);
        $this->db->where_in('status', ['confirmed', 'selesai', 'selesai_do', 'proses_do']);
        $this->db->order_by('id_faktur', 'ASC');
        $fakturs = $this->db->get()->result_array();

        if (empty($fakturs)) {
            return [
                'success' => false,
                'message' => 'Tidak ada Faktur Z yang siap dipecah untuk kios ini (semua sudah dipecah atau belum confirmed).'
            ];
        }

        $this->db->trans_start();
        $this->load->model('M_ActivityLog');

        $generated_numbers        = [];
        $created_fakturs          = [];
        $processed_parent_fakturs = [];
        $contact_idx              = 0;

        // 4. Proses masing-masing Faktur Z secara TERISOLASI
        foreach ($fakturs as $parent_faktur) {
            $details = $this->get_faktur_detail($parent_faktur['id_faktur']);
            if (empty($details)) {
                continue;
            }

            // Kumpulkan bucket pecahan H HANYA untuk Faktur Z ini
            $h_buckets = [];
            $current_bucket = [
                'items'         => [],
                'total_nominal' => 0.0,
                'total_qty'     => 0.0
            ];

            foreach ($details as $pd) {
                $qty_remaining = (float)($pd['qty'] ?? 0);
                if ($qty_remaining <= 0.0001) {
                    continue;
                }

                // Harga satuan pada faktur pecahan H terpotong 20% dari harga Faktur Z induk
                $hrg_satuan_pecah     = round((float)$pd['hrg_satuan'] * 0.8, 2);
                $disc                 = (float)($pd['disc'] ?? 0);
                $effective_unit_price = round($hrg_satuan_pecah * (1 - ($disc / 100)), 2);

                while ($qty_remaining > 0.0001) {
                    $remaining_budget = $max_nominal_per_faktur - $current_bucket['total_nominal'];

                    // Jika budget pada bucket berjalan sudah penuh / habis
                    if ($remaining_budget <= 0.01 && !empty($current_bucket['items'])) {
                        $h_buckets[] = $current_bucket;
                        $current_bucket = ['items' => [], 'total_nominal' => 0.0, 'total_qty' => 0.0];
                        $remaining_budget = $max_nominal_per_faktur;
                    }

                    // Hitung jumlah kuantitas yang muat dalam sisa plafon
                    if ($effective_unit_price <= 0.01) {
                        $fit_qty = $qty_remaining;
                    } else {
                        $fit_qty = floor($remaining_budget / $effective_unit_price);
                    }

                    if ($fit_qty >= $qty_remaining) {
                        // Seluruh sisa qty muat pada bucket saat ini
                        $alloc_qty = $qty_remaining;
                        $subtotal  = round($alloc_qty * $effective_unit_price, 2);

                        $current_bucket['items'][] = [
                            'detail'      => $pd,
                            'qty'         => $alloc_qty,
                            'hrg_satuan'  => $hrg_satuan_pecah,
                            'total_harga' => $subtotal
                        ];
                        $current_bucket['total_nominal'] += $subtotal;
                        $current_bucket['total_qty']     += $alloc_qty;
                        $qty_remaining = 0;
                    } elseif ($fit_qty > 0) {
                        // Sebagian qty muat pada bucket saat ini
                        $alloc_qty = $fit_qty;
                        $subtotal  = round($alloc_qty * $effective_unit_price, 2);

                        $current_bucket['items'][] = [
                            'detail'      => $pd,
                            'qty'         => $alloc_qty,
                            'hrg_satuan'  => $hrg_satuan_pecah,
                            'total_harga' => $subtotal
                        ];
                        $current_bucket['total_nominal'] += $subtotal;
                        $current_bucket['total_qty']     += $alloc_qty;
                        $qty_remaining -= $alloc_qty;

                        // Tutup bucket saat ini karena sudah penuh
                        $h_buckets[] = $current_bucket;
                        $current_bucket = ['items' => [], 'total_nominal' => 0.0, 'total_qty' => 0.0];
                    } else {
                        // fit_qty == 0: sisa plafon tidak cukup untuk 1 pcs
                        if (!empty($current_bucket['items'])) {
                            // Tutup bucket yang sedang berjalan dan buka bucket baru
                            $h_buckets[] = $current_bucket;
                            $current_bucket = ['items' => [], 'total_nominal' => 0.0, 'total_qty' => 0.0];
                        } else {
                            // Bucket kosong tetapi harga 1 pcs barang melebihi $max_nominal_per_faktur
                            $alloc_qty = min(1.0, $qty_remaining);
                            $subtotal  = round($alloc_qty * $effective_unit_price, 2);

                            $current_bucket['items'][] = [
                                'detail'      => $pd,
                                'qty'         => $alloc_qty,
                                'hrg_satuan'  => $hrg_satuan_pecah,
                                'total_harga' => $subtotal
                            ];
                            $current_bucket['total_nominal'] += $subtotal;
                            $current_bucket['total_qty']     += $alloc_qty;
                            $qty_remaining -= $alloc_qty;

                            $h_buckets[] = $current_bucket;
                            $current_bucket = ['items' => [], 'total_nominal' => 0.0, 'total_qty' => 0.0];
                        }
                    }
                }
            }

            // Simpan bucket terakhir jika masih ada item
            if (!empty($current_bucket['items'])) {
                $h_buckets[] = $current_bucket;
            }

            // 5. Buat Faktur H dari masing-masing bucket untuk Faktur Z ini
            foreach ($h_buckets as $bucket) {
                if (empty($bucket['items'])) {
                    continue;
                }

                // Tentukan kontak acak penerima
                if (!empty($contacts)) {
                    $selected_contact = $contacts[$contact_idx % count($contacts)];
                    $contact_idx++;
                    $kd_cust_h       = $selected_contact['kd_customer'];
                    $customer_name_h = $selected_contact['kontak_person'] . ' (' . $selected_contact['nama_toko'] . ')';
                } else {
                    $kd_cust_h       = $parent_faktur['kd_customer'];
                    $customer_name_h = !empty($customer['nama_customer']) ? $customer['nama_customer'] : ($parent_faktur['nama_customer'] ?? 'Customer Induk');
                }

                $no_faktur_child = $this->_generate_and_track_no_faktur('H', $generated_numbers);

                $fh = [
                    'no_faktur'           => $no_faktur_child,
                    'parent_id_faktur'    => $parent_faktur['id_faktur'],
                    'parent_no_faktur'    => $parent_faktur['no_faktur'],
                    'id_so'               => $parent_faktur['id_so'],
                    'no_so'               => $parent_faktur['no_so'],
                    'kd_customer'         => $kd_cust_h,
                    'customer_name'       => $customer_name_h,
                    'gudang_id'           => $parent_faktur['gudang_id'],
                    'tanggal_faktur'      => $parent_faktur['tanggal_faktur'],
                    'tanggal_jatuh_tempo' => $parent_faktur['tanggal_jatuh_tempo'],
                    'salesman'            => $parent_faktur['salesman'],
                    'cara_pembayaran'     => $parent_faktur['cara_pembayaran'],
                    'jtempo'              => $parent_faktur['jtempo'],
                    'tempo'               => $parent_faktur['tempo'],
                    'catatan'             => 'Pecahan Otomatis dari Faktur Z ' . $parent_faktur['no_faktur'] . "\n" . ($parent_faktur['catatan'] ?? ''),
                    'status'              => 'confirmed',
                    'create_by'           => $username,
                    'create_at'           => date('Y-m-d H:i:s'),
                    'total_tonase'        => 0,
                    'total_kubikasi'      => 0
                ];

                $this->db->insert('tbso_faktur_z_pecah', $fh);
                $child_id_pecah = $this->db->insert_id();

                $child_details_logged = [];

                foreach ($bucket['items'] as $b_item) {
                    $pd        = $b_item['detail'];
                    $alloc_qty = (float)$b_item['qty'];
                    $isi       = max(1, (int)($pd['isi_per_box'] ?? 1));
                    $qty_box   = floor($alloc_qty / $isi);
                    $qty_satuan = fmod($alloc_qty, $isi);

                    $subtotal_before = round($alloc_qty * $b_item['hrg_satuan'], 2);
                    $subtotal_after  = round($subtotal_before * (1 - ((float)($pd['disc'] ?? 0) / 100)), 2);
                    $total_harga     = $subtotal_after;

                    $fd = [
                        'id_pecah'                => $child_id_pecah,
                        'no_faktur'               => $no_faktur_child,
                        'parent_id_faktur'        => $parent_faktur['id_faktur'],
                        'parent_no_faktur'        => $parent_faktur['no_faktur'],
                        'id_faktur_detail_parent' => $pd['id'],
                        'id_so'                   => $pd['id_so'],
                        'id_so_detail'            => $pd['id_so_detail'],
                        'kd_barang'               => $pd['kd_barang'],
                        'nama_barang'             => $pd['nama_barang'],
                        'no_lot'                  => $pd['no_lot'],
                        'expired_date'            => $pd['expired_date'],
                        'qty'                     => $alloc_qty,
                        'qty_box'                 => $qty_box,
                        'qty_satuan'              => $qty_satuan,
                        'isi_per_box'             => $isi,
                        'satuan'                  => $pd['satuan'] ?? 'PCS',
                        'hrg_satuan'              => $b_item['hrg_satuan'],
                        'hrg_pokok'               => $pd['hrg_pokok'] ?? 0,
                        'disc'                    => $pd['disc'] ?? 0,
                        'pajak'                   => $pd['pajak'] ?? 0,
                        'subtotal_before_disc'    => $subtotal_before,
                        'subtotal_after_disc'     => $subtotal_after,
                        'total_harga'             => $total_harga,
                        'berat_gram'              => $pd['berat_gram'] ?? 0,
                        'kubikasi_m3'             => $pd['kubikasi_m3'] ?? 0,
                        'gudang_id'               => $pd['gudang_id'] ?? $parent_faktur['gudang_id'],
                        'create_by'               => $username,
                        'create_at'               => date('Y-m-d H:i:s')
                    ];

                    $this->db->insert('tbso_faktur_z_pecah_detail', $fd);
                    $child_details_logged[] = $pd['nama_barang'] . " (" . $alloc_qty . " " . ($pd['satuan'] ?? 'PCS') . " @ Rp " . number_format($b_item['hrg_satuan'], 0, ',', '.') . ")";
                }

                // Update total tonase dan kubikasi
                $this->db->select('SUM(qty * berat_gram / 1000000) AS t, SUM(qty * kubikasi_m3) AS k', false);
                $this->db->where('id_pecah', $child_id_pecah);
                $sums = $this->db->get('tbso_faktur_z_pecah_detail')->row_array();

                $this->db->where('id_pecah', $child_id_pecah);
                $this->db->update('tbso_faktur_z_pecah', [
                    'total_tonase'   => round((float)($sums['t'] ?? 0), 6),
                    'total_kubikasi' => round((float)($sums['k'] ?? 0), 6)
                ]);

                // Catat log aktivitas
                $this->M_ActivityLog->log(
                    $parent_faktur['no_so'],
                    $no_faktur_child,
                    'AUTO_SPLIT_FAKTUR_H',
                    'Faktur pecahan otomatis ' . $no_faktur_child . ' dibuat untuk customer ' . $customer_name_h . ' dari Faktur Z: ' . $parent_faktur['no_faktur'] . ' (Total Netto: Rp ' . number_format($bucket['total_nominal'], 0, ',', '.') . ')',
                    $username,
                    "Item:\n" . implode("\n", $child_details_logged)
                );

                $created_fakturs[] = $no_faktur_child;
            }

            // Tandai Faktur Z induk ini sebagai telah dipecah
            $this->db->where('id_faktur', $parent_faktur['id_faktur']);
            $this->db->update('tbso_faktur_penjualan', [
                'is_split_parent' => 1,
                'update_by'       => $username,
                'update_at'       => date('Y-m-d H:i:s')
            ]);

            $processed_parent_fakturs[] = $parent_faktur['no_faktur'];
        }

        $this->db->trans_complete();

        return [
            'success'                => $this->db->trans_status() && !empty($created_fakturs),
            'total_parent_processed' => count($processed_parent_fakturs),
            'total_created'          => count($created_fakturs),
            'created_list'           => $created_fakturs,
            'processed_parents'      => $processed_parent_fakturs
        ];
    }

    public function ensure_faktur_z_pecah_tables()
    {
        if (!$this->db->table_exists('tbso_faktur_z_pecah')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tbso_faktur_z_pecah` (
                    `id_pecah` INT AUTO_INCREMENT PRIMARY KEY,
                    `no_faktur` VARCHAR(50) NOT NULL COMMENT 'Kode Faktur pecahan diawali H',
                    `parent_id_faktur` INT NOT NULL COMMENT 'ID Faktur Z Induk',
                    `parent_no_faktur` VARCHAR(50) NOT NULL COMMENT 'No Faktur Z Induk',
                    `id_so` INT NOT NULL,
                    `no_so` VARCHAR(50) NOT NULL,
                    `kd_customer` VARCHAR(50) NOT NULL COMMENT 'Customer Penerima Pecahan',
                    `customer_name` VARCHAR(255) NOT NULL,
                    `gudang_id` VARCHAR(50) DEFAULT NULL,
                    `tanggal_faktur` DATE NOT NULL,
                    `tanggal_jatuh_tempo` DATE DEFAULT NULL,
                    `salesman` VARCHAR(100) DEFAULT NULL,
                    `cara_pembayaran` VARCHAR(20) DEFAULT NULL,
                    `jtempo` INT DEFAULT 0,
                    `tempo` INT DEFAULT 0,
                    `catatan` TEXT DEFAULT NULL,
                    `status` VARCHAR(20) NOT NULL DEFAULT 'confirmed',
                    `total_tonase` DECIMAL(12,4) DEFAULT 0,
                    `total_kubikasi` DECIMAL(12,6) DEFAULT 0,
                    `create_by` VARCHAR(100) NOT NULL,
                    `create_at` DATETIME NOT NULL,
                    `update_by` VARCHAR(100) DEFAULT NULL,
                    `update_at` DATETIME DEFAULT NULL,
                    INDEX `idx_no_faktur` (`no_faktur`),
                    INDEX `idx_parent_id_faktur` (`parent_id_faktur`),
                    INDEX `idx_id_so` (`id_so`),
                    INDEX `idx_kd_customer` (`kd_customer`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->db->table_exists('tbso_faktur_z_pecah_detail')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tbso_faktur_z_pecah_detail` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_pecah` INT NOT NULL COMMENT 'Relasi ke tbso_faktur_z_pecah.id_pecah',
                    `no_faktur` VARCHAR(50) NOT NULL COMMENT 'Kode Faktur pecahan H',
                    `parent_id_faktur` INT NOT NULL,
                    `parent_no_faktur` VARCHAR(50) NOT NULL,
                    `id_faktur_detail_parent` INT DEFAULT NULL COMMENT 'ID baris tbso_faktur_detail induk',
                    `id_so` INT NOT NULL,
                    `id_so_detail` INT NOT NULL,
                    `kd_barang` VARCHAR(50) NOT NULL,
                    `nama_barang` VARCHAR(255) NOT NULL,
                    `no_lot` VARCHAR(50) DEFAULT NULL,
                    `expired_date` DATE DEFAULT NULL,
                    `qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
                    `qty_box` DECIMAL(15,3) DEFAULT 0,
                    `qty_satuan` DECIMAL(15,3) DEFAULT 0,
                    `isi_per_box` INT DEFAULT 1,
                    `satuan` VARCHAR(20) DEFAULT NULL,
                    `hrg_satuan` DECIMAL(18,2) DEFAULT 0,
                    `hrg_pokok` DECIMAL(18,2) DEFAULT 0,
                    `disc` DECIMAL(5,2) DEFAULT 0,
                    `pajak` DECIMAL(5,2) DEFAULT 0,
                    `subtotal_before_disc` DECIMAL(15,2) DEFAULT 0,
                    `subtotal_after_disc` DECIMAL(15,2) DEFAULT 0,
                    `total_harga` DECIMAL(18,2) DEFAULT 0,
                    `berat_gram` DECIMAL(12,4) DEFAULT 0,
                    `kubikasi_m3` DECIMAL(12,6) DEFAULT 0,
                    `gudang_id` VARCHAR(50) DEFAULT NULL,
                    `create_by` VARCHAR(100) NOT NULL,
                    `create_at` DATETIME NOT NULL,
                    INDEX `idx_id_pecah` (`id_pecah`),
                    INDEX `idx_no_faktur` (`no_faktur`),
                    INDEX `idx_parent_id_faktur` (`parent_id_faktur`),
                    INDEX `idx_kd_barang` (`kd_barang`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }

    public function get_faktur_pecah_h($id_pecah)
    {
        $this->ensure_faktur_z_pecah_tables();
        $this->ensure_customer_acak_table();
        $this->db->select('p.*, 
            COALESCE(ca.kontak_person, c.nama_customer) AS full_customer_name, 
            ca.nama_toko, ca.alamat AS ca_alamat, ca.kota AS ca_kota, ca.nik AS ca_nik, ca.npwp AS ca_npwp,
            c.kd_rute AS customer_kd_rute');
        $this->db->from('tbso_faktur_z_pecah p');
        $this->db->join('tb_customer_acak ca', 'ca.kd_customer = p.kd_customer', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = p.kd_customer OR c.kd_customer = ca.kd_customer_induk', 'left');
        if (is_numeric($id_pecah)) {
            $this->db->where('p.id_pecah', (int)$id_pecah);
        } else {
            $this->db->where('p.no_faktur', $id_pecah);
        }
        return $this->db->get()->row_array();
    }

    public function get_faktur_pecah_h_detail($id_pecah)
    {
        $this->ensure_faktur_z_pecah_tables();
        return $this->db->get_where('tbso_faktur_z_pecah_detail', ['id_pecah' => $id_pecah])->result_array();
    }

    public function get_all_faktur_pecah_h($filter = [])
    {
        $this->ensure_faktur_z_pecah_tables();
        $this->ensure_customer_acak_table();
        $this->db->select('p.*, 
            COALESCE(MAX(ca.kontak_person), MAX(c.nama_customer)) AS nama_customer, 
            MAX(ca.nama_toko) AS nama_toko, 
            MAX(ca.kota) AS customer_kota,
            MAX(c.kd_rute) AS customer_kd_rute,
            COALESCE(SUM(pd.qty), 0) AS total_qty,
            COALESCE(SUM(pd.total_harga), 0) AS grand_total,
            COUNT(pd.id) AS total_barang');
        $this->db->from('tbso_faktur_z_pecah p');
        $this->db->join('tb_customer_acak ca', 'ca.kd_customer = p.kd_customer', 'left');
        $this->db->join('tb_customer c', 'c.kd_customer = p.kd_customer OR c.kd_customer = ca.kd_customer_induk', 'left');
        $this->db->join('tbso_faktur_z_pecah_detail pd', 'pd.id_pecah = p.id_pecah', 'left');
        if (!empty($filter['date1'])) $this->db->where('p.tanggal_faktur >=', $filter['date1']);
        if (!empty($filter['date2'])) $this->db->where('p.tanggal_faktur <=', $filter['date2']);
        if (!empty($filter['create_by'])) $this->db->where('p.create_by', $filter['create_by']);
        if (!empty($filter['search'])) {
            $q = trim((string)$filter['search']);
            $this->db->group_start();
            $this->db->like('p.no_faktur', $q);
            $this->db->or_like('p.parent_no_faktur', $q);
            $this->db->or_like('p.no_so', $q);
            $this->db->or_like('p.customer_name', $q);
            $this->db->or_like('ca.kontak_person', $q);
            $this->db->or_like('ca.nama_toko', $q);
            $this->db->or_like('c.nama_customer', $q);
            $this->db->group_end();
        }
        $this->db->group_by('p.id_pecah');
        $this->db->order_by('p.id_pecah', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Memastikan tabel master customer acak tersedia
     */
    public function ensure_customer_acak_table()
    {
        if (!$this->db->table_exists('tb_customer_acak')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_customer_acak` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `kd_customer` VARCHAR(50) NOT NULL,
                    `nama_toko` VARCHAR(255) NOT NULL,
                    `kd_customer_induk` VARCHAR(50) DEFAULT NULL,
                    `kontak_person` VARCHAR(255) NOT NULL,
                    `alamat` TEXT DEFAULT NULL,
                    `kota` VARCHAR(100) DEFAULT NULL,
                    `nik` VARCHAR(50) DEFAULT NULL,
                    `npwp` VARCHAR(50) DEFAULT NULL,
                    `created_at` DATETIME NOT NULL,
                    `updated_at` DATETIME DEFAULT NULL,
                    UNIQUE KEY `uk_kd_customer` (`kd_customer`),
                    INDEX `idx_nama_toko` (`nama_toko`),
                    INDEX `idx_kd_customer_induk` (`kd_customer_induk`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
    }

    /**
     * Ambil data customer acak berdasarkan kode acak
     */
    public function get_customer_acak_by_kode($kd_customer)
    {
        $this->ensure_customer_acak_table();
        return $this->db->get_where('tb_customer_acak', ['kd_customer' => $kd_customer])->row_array();
    }

    /**
     * Ambil list customer acak berdasarkan kios induk / kode induk
     * Pastikan tidak tertukar dengan kios lain, dan diurutkan berdasarkan
     * beban transaksi historis terendah (yang paling kosong / belum pernah diprioritaskan di awal)
     */
    public function get_customers_acak_by_kios($nama_kios = '', $kd_customer_induk = '')
    {
        $this->ensure_customer_acak_table();
        $this->ensure_faktur_z_pecah_tables();

        $limit_nominal = self::LIMIT_KONTAK_PERSON_FAKTUR_H;

        $this->db->select("ca.*, 
            COALESCE(COUNT(DISTINCT p.id_pecah), 0) AS total_faktur_pecah,
            COALESCE(SUM(pd.total_harga), 0) AS total_nominal_pecah,
            CASE WHEN COALESCE(SUM(pd.total_harga), 0) >= {$limit_nominal} THEN 1 ELSE 0 END AS is_limit_reached,
            GREATEST(0, {$limit_nominal} - COALESCE(SUM(pd.total_harga), 0)) AS sisa_limit_h");
        $this->db->from('tb_customer_acak ca');
        $this->db->join('tbso_faktur_z_pecah p', "p.kd_customer = ca.kd_customer AND p.status != 'cancelled'", 'left');
        $this->db->join('tbso_faktur_z_pecah_detail pd', 'pd.id_pecah = p.id_pecah', 'left');

        $has_condition = false;
        $this->db->group_start();

        if (!empty($kd_customer_induk)) {
            $this->db->where('ca.kd_customer_induk', $kd_customer_induk);
            $has_condition = true;
        }

        if (!empty($nama_kios)) {
            $nama_clean = trim($nama_kios);
            if ($has_condition) {
                $this->db->or_where('ca.nama_toko', $nama_clean);
                $this->db->or_like('ca.nama_toko', $nama_clean);
            } else {
                $this->db->where('ca.nama_toko', $nama_clean);
                $this->db->or_like('ca.nama_toko', $nama_clean);
                $has_condition = true;
            }
        }

        $this->db->group_end();

        // Jika tidak ada kriteria sama sekali, jangan kembalikan semua data untuk menjaga isolasi per kios
        if (!$has_condition) {
            return [];
        }

        $this->db->group_by('ca.id');
        // Prioritaskan: 
        // 1. Yang belum mencapai limit 250 juta di awal
        // 2. Yang akumulasi nominalnya paling sedikit / 0 (paling kosong)
        // 3. Yang jumlah fakturnya paling sedikit
        // 4. Urutkan berdasarkan kode customer acak
        $this->db->order_by('is_limit_reached', 'ASC');
        $this->db->order_by('total_nominal_pecah', 'ASC');
        $this->db->order_by('total_faktur_pecah', 'ASC');
        $this->db->order_by('ca.kd_customer', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Ambil list semua customer acak dengan filter fleksibel beserta akumulasi nominal Faktur H
     */
    public function get_all_customers_acak($filter = [])
    {
        $this->ensure_customer_acak_table();
        $this->ensure_faktur_z_pecah_tables();

        $limit_nominal = self::LIMIT_KONTAK_PERSON_FAKTUR_H;

        $subquery = "(
            SELECT p.kd_customer, 
                   COUNT(DISTINCT p.id_pecah) AS total_faktur_h,
                   SUM(pd.total_harga) AS total_nominal_h
            FROM tbso_faktur_z_pecah p
            JOIN tbso_faktur_z_pecah_detail pd ON pd.id_pecah = p.id_pecah
            WHERE p.status != 'cancelled'
            GROUP BY p.kd_customer
        ) sub";

        $this->db->select("ca.*, 
            c.nama_customer as induk_nama_customer, 
            c.nama_kios as induk_nama_kios,
            COALESCE(sub.total_nominal_h, 0) AS total_nominal_h,
            COALESCE(sub.total_faktur_h, 0) AS total_faktur_h,
            CASE WHEN COALESCE(sub.total_nominal_h, 0) >= {$limit_nominal} THEN 1 ELSE 0 END AS is_limit_reached,
            GREATEST(0, {$limit_nominal} - COALESCE(sub.total_nominal_h, 0)) AS sisa_limit_h");
        $this->db->from('tb_customer_acak ca');
        $this->db->join('tb_customer c', 'c.kd_customer = ca.kd_customer_induk', 'left');
        $this->db->join($subquery, 'sub.kd_customer = ca.kd_customer', 'left');

        if (!empty($filter['nama_toko'])) {
            $this->db->where('ca.nama_toko', $filter['nama_toko']);
        }
        if (!empty($filter['kd_customer_induk'])) {
            $this->db->where('ca.kd_customer_induk', $filter['kd_customer_induk']);
        }
        if (!empty($filter['search'])) {
            $s = trim($filter['search']);
            $this->db->group_start();
            $this->db->like('ca.kd_customer', $s);
            $this->db->or_like('ca.kontak_person', $s);
            $this->db->or_like('ca.nama_toko', $s);
            $this->db->or_like('ca.alamat', $s);
            $this->db->or_like('ca.kota', $s);
            $this->db->or_like('ca.nik', $s);
            $this->db->group_end();
        }

        $this->db->order_by('ca.nama_toko', 'ASC');
        $this->db->order_by('ca.kd_customer', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Cek apakah kontak person customer acak sudah mencapai limit atau penambahan nominal baru akan melebihi limit
     * 
     * @param string $kd_customer
     * @param float $nominal_tambahan
     * @return array
     */
    public function check_kontak_person_limit($kd_customer, $nominal_tambahan = 0)
    {
        $this->ensure_customer_acak_table();
        $this->ensure_faktur_z_pecah_tables();

        $cust = $this->db->get_where('tb_customer_acak', ['kd_customer' => $kd_customer])->row_array();
        if (!$cust) {
            return [
                'is_allowed'    => true,
                'current_total' => 0,
                'new_total'     => (float)$nominal_tambahan,
                'limit'         => self::LIMIT_KONTAK_PERSON_FAKTUR_H,
                'sisa'          => self::LIMIT_KONTAK_PERSON_FAKTUR_H,
                'kontak_person' => '',
                'nama_toko'     => '',
                'message'       => ''
            ];
        }

        $row = $this->db->select('COALESCE(SUM(pd.total_harga), 0) AS total')
            ->from('tbso_faktur_z_pecah p')
            ->join('tbso_faktur_z_pecah_detail pd', 'pd.id_pecah = p.id_pecah', 'inner')
            ->where('p.kd_customer', $kd_customer)
            ->where('p.status !=', 'cancelled')
            ->get()
            ->row_array();

        $current_total = (float)($row['total'] ?? 0);
        $limit         = (float)self::LIMIT_KONTAK_PERSON_FAKTUR_H;
        $new_total     = $current_total + (float)$nominal_tambahan;
        $sisa          = max(0.0, $limit - $current_total);

        $is_allowed = ($current_total < $limit) && ($new_total <= ($limit + 0.01));

        $msg = '';
        if (!$is_allowed) {
            if ($current_total >= $limit) {
                $msg = "Kontak person <b>" . htmlspecialchars($cust['kontak_person']) . "</b> (" . htmlspecialchars($cust['kd_customer']) . " - " . htmlspecialchars($cust['nama_toko']) . ") telah mencapai batas maksimal transaksi Rp " . number_format($limit, 0, ',', '.') . " (Total akumulasi: Rp " . number_format($current_total, 0, ',', '.') . "). Kontak person ini tidak dapat digunakan lagi.";
            } else {
                $msg = "Alokasi Faktur H untuk kontak person <b>" . htmlspecialchars($cust['kontak_person']) . "</b> (" . htmlspecialchars($cust['kd_customer']) . ") sebesar Rp " . number_format($nominal_tambahan, 0, ',', '.') . " melebihi sisa kuota limit (Sisa Kuota: Rp " . number_format($sisa, 0, ',', '.') . " dari batas maksimal Rp " . number_format($limit, 0, ',', '.') . ").";
            }
        }

        return [
            'is_allowed'    => $is_allowed,
            'current_total' => $current_total,
            'new_total'     => $new_total,
            'limit'         => $limit,
            'sisa'          => $sisa,
            'kontak_person' => $cust['kontak_person'],
            'nama_toko'     => $cust['nama_toko'],
            'message'       => $msg
        ];
    }

    /**
     * Ambil daftar nama toko unik di master customer acak
     */
    public function get_unique_tokos_customer_acak()
    {
        $this->ensure_customer_acak_table();
        return $this->db->select('nama_toko, MAX(kd_customer_induk) as kd_customer_induk, COUNT(*) as total_kontak')
            ->from('tb_customer_acak')
            ->group_by('nama_toko')
            ->order_by('nama_toko', 'ASC')
            ->get()
            ->result_array();
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

    public function kembalikan_so_ke_sales($id_so, $update_by)
    {
        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $id_so])->row_array();
        if (!$so) return ['errors' => ['SO tidak ditemukan.']];
        if (!in_array($so['status'], ['siap_faktur', 'partial'], true)) {
            return ['errors' => ['SO tidak dalam status yang bisa dikembalikan (harus siap_faktur atau partial).']];
        }

        // Cek apakah SO sudah punya faktur aktif
        $jumlah_faktur = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_faktur_penjualan
            WHERE id_so = ? AND status NOT IN ('cancelled')
        ", [$id_so])->row_array();

        $has_faktur = (int)($jumlah_faktur['total'] ?? 0) > 0;

        // Jika ada faktur yang sudah masuk DO, tidak bisa dikembalikan
        $in_do = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_faktur_penjualan fp
            JOIN tb_detail_do dd ON dd.kd_faktur = fp.no_faktur
            WHERE fp.id_so = ?
        ", [$id_so])->row_array();

        if ((int)($in_do['total'] ?? 0) > 0) {
            return ['errors' => ['SO tidak dapat dikembalikan karena fakturnya sudah masuk Delivery Order.']];
        }

        // ══════════════════════════════════════════════════════════════════
        // LOGIKA MATCHING: Barang Tidak Terfaktur vs Barang Tidak Dimuat
        // ══════════════════════════════════════════════════════════════════
        // Jika barang yang tidak terfaktur sama dengan barang yang tidak dimuat (checker_loaded=2),
        // maka DO bisa dibuat untuk barang yang sudah difakturkan dan dimuat (checker_loaded=1)
        
        $can_create_do = false;
        $kd_rute = trim((string)($so['kd_rute'] ?? ''));
        
        if ($has_faktur && $kd_rute !== '') {
            // Ambil barang yang tidak terfaktur (outstanding)
            $barang_tidak_terfaktur = $this->db->query("
                SELECT 
                    sd.id AS id_detail,
                    sd.kd_barang,
                    sd.no_lot,
                    sd.expired_date,
                    COALESCE(sd.qty_siap_faktur, sd.qty) AS qty_siap,
                    COALESCE(sd.qty_faktur, 0) AS qty_faktur,
                    GREATEST(COALESCE(sd.qty_siap_faktur, sd.qty) - COALESCE(sd.qty_faktur, 0), 0) AS qty_outstanding
                FROM tbso_sales_order_detail sd
                WHERE sd.id_so = ?
                AND GREATEST(COALESCE(sd.qty_siap_faktur, sd.qty) - COALESCE(sd.qty_faktur, 0), 0) > 0
                ORDER BY sd.kd_barang, sd.no_lot, sd.expired_date
            ", [$id_so])->result_array();

            // Ambil barang yang tidak dimuat (checker_loaded=2)
            $barang_tidak_dimuat = $this->db->query("
                SELECT 
                    sd.id AS id_detail,
                    sd.kd_barang,
                    sd.no_lot,
                    sd.expired_date,
                    COALESCE(sd.qty_siap_faktur, sd.qty) AS qty_siap
                FROM tbso_sales_order_detail sd
                WHERE sd.id_so = ?
                AND sd.checker_loaded = 2
                AND COALESCE(sd.qty_siap_faktur, sd.qty) > 0
                ORDER BY sd.kd_barang, sd.no_lot, sd.expired_date
            ", [$id_so])->result_array();

            // Bandingkan: apakah barang tidak terfaktur = barang tidak dimuat
            if (!empty($barang_tidak_terfaktur) && !empty($barang_tidak_dimuat)) {
                $match_count = 0;
                foreach ($barang_tidak_terfaktur as $btf) {
                    foreach ($barang_tidak_dimuat as $btd) {
                        if (
                            $btf['kd_barang'] === $btd['kd_barang'] &&
                            $btf['no_lot'] === $btd['no_lot'] &&
                            $btf['expired_date'] === $btd['expired_date'] &&
                            abs((float)$btf['qty_outstanding'] - (float)$btd['qty_siap']) < 0.01
                        ) {
                            $match_count++;
                            break;
                        }
                    }
                }

                // Jika semua barang tidak terfaktur cocok dengan barang tidak dimuat
                if ($match_count === count($barang_tidak_terfaktur) && $match_count === count($barang_tidak_dimuat)) {
                    $can_create_do = true;
                }
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // AUTO CREATE DO jika matching (SEBELUM reset checker_loaded & update status)
        // ══════════════════════════════════════════════════════════════════
        $do_created = null;
        if ($can_create_do && $kd_rute !== '') {
            // Panggil check_and_auto_create_do dengan $bypass_checks=true karena
            // matching antara barang tidak terfaktur vs barang tidak dimuat sudah
            // terkonfirmasi di atas. Bypass diperlukan agar has_remaining_so_*
            // tidak memblokir pembuatan DO (item checker_loaded=2 masih ada di DB
            // dan akan menggagalkan pengecekan jika tidak di-bypass).
            $this->load->model('M_Logistik');
            $auto_do = $this->M_Logistik->check_and_auto_create_do($kd_rute, $update_by, true);
            if (!empty($auto_do['kd_do'])) {
                $do_created = $auto_do['kd_do'];
            }
        }

        // SO yang dikembalikan dari Admin SC harus masuk antrian Sales sebagai partial,
        // agar tidak lagi diperlakukan sebagai SO siap faktur penuh.
        $new_status = 'partial';

        $this->db->trans_start();

        // Update status SO
        $this->db->where('id_so', $id_so);
        $this->db->update('tbso_sales_order', [
            'status'    => $new_status,
            'update_by' => $update_by,
            'update_at' => date('Y-m-d H:i:s'),
        ]);

        // Reset checker_loaded ke 0 HANYA untuk item yang belum terfakturkan
        // (outstanding qty > 0, yaitu item yang dikembalikan ke SC untuk difakturkan ulang).
        // Item yang sudah terfakturkan penuh (qty_faktur >= qty_siap_faktur) TIDAK di-reset,
        // agar rute tidak muncul kembali di halaman so_loading setelah DO dibuat.
        $this->db->query("
            UPDATE tbso_sales_order_detail
            SET checker_loaded = 0
            WHERE id_so = ?
            AND GREATEST(COALESCE(qty_siap_faktur, qty) - COALESCE(qty_faktur, 0), 0) > 0.001
        ", [$id_so]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['errors' => ['Gagal mengembalikan SO ke Sales.']];
        }

        return [
            'success'      => true,
            'new_status'   => $new_status,
            'has_faktur'   => $has_faktur,
            'do_created'   => $do_created,
            'can_create_do' => $can_create_do,
        ];
    }

    public function submit_cancel_partial_request($id_so, $details, $user)
    {
        $this->db->trans_start();
        foreach ($details as $detail) {
            $data = [
                'id_so'        => $id_so,
                'id_so_detail' => $detail['id_so_detail'],
                'qty_cancel'   => $detail['qty_cancel'],
                'status'       => 'pending',
                'request_by'   => $user,
                'request_at'   => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbso_cancel_partial_request', $data);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_pending_cancel_requests($id_so = null)
    {
        $this->db->select('cr.*, sd.kd_barang, sd.nama_barang, sd.hrg_satuan, so.no_so, so.tanggal_transaksi, c.nama_customer')
                 ->from('tbso_cancel_partial_request cr')
                 ->join('tbso_sales_order_detail sd', 'sd.id = cr.id_so_detail')
                 ->join('tbso_sales_order so', 'so.id_so = cr.id_so')
                 ->join('tb_customer c', 'c.kd_customer = so.kd_customer', 'left')
                 ->where('cr.status', 'pending');
        if ($id_so) {
            $this->db->where('cr.id_so', $id_so);
        }
        return $this->db->get()->result_array();
    }

    public function get_request_by_id($req_id)
    {
        return $this->db->get_where('tbso_cancel_partial_request', ['id' => $req_id])->row_array();
    }

    public function approve_cancel_partial($request_ids, $user)
    {
        $this->db->trans_start();

        foreach ($request_ids as $req_id) {
            $req = $this->db->get_where('tbso_cancel_partial_request', ['id' => $req_id, 'status' => 'pending'])->row_array();
            if ($req) {
                $so = $this->db->select('no_so, gudang_id')->get_where('tbso_sales_order', ['id_so' => $req['id_so']])->row_array();
                $sd = $this->db->select('kd_barang, expired_date, no_lot, hrg_satuan')
                               ->get_where('tbso_sales_order_detail', ['id' => $req['id_so_detail']])->row_array();

                $this->db->where('id', $req_id)
                         ->update('tbso_cancel_partial_request', [
                             'status'     => 'approved',
                             'approve_by' => $user,
                             'approve_at' => date('Y-m-d H:i:s')
                         ]);

                $this->db->set('qty', 'qty - ' . (float)$req['qty_cancel'], FALSE)
                         ->where('id', $req['id_so_detail'])
                         ->update('tbso_sales_order_detail');

                if ($so && $sd) {
                    // Update total_harga
                    $this->db->set('total_harga', 'qty * ' . (float)$sd['hrg_satuan'], FALSE)
                             ->where('id', $req['id_so_detail'])
                             ->update('tbso_sales_order_detail');

                    // Release reserved stock di tberp_stock_batch + catat di ledger
                    $this->_kurangi_reserved_batch(
                        $so['no_so'],
                        $sd['kd_barang'],
                        $sd['expired_date'],
                        $sd['no_lot'],
                        $so['gudang_id'],
                        (float)$req['qty_cancel']
                    );
                }
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    
    public function reject_cancel_partial($request_ids, $user)
    {
        $this->db->trans_start();
        $this->db->where_in('id', $request_ids)
                 ->where('status', 'pending')
                 ->update('tbso_cancel_partial_request', [
                     'status'     => 'rejected',
                     'approve_by' => $user,
                     'approve_at' => date('Y-m-d H:i:s')
                 ]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    
    public function check_and_update_completed_so($id_so)
    {
        $details = $this->db->get_where('tbso_sales_order_detail', ['id_so' => $id_so])->result_array();
        $all_completed = true;
        foreach ($details as $d) {
            if (round((float)$d['qty'] - (float)$d['qty_faktur'], 3) > 0) {
                $all_completed = false;
                break;
            }
        }
        
        if ($all_completed) {
            $this->db->where('id_so', $id_so)->update('tbso_sales_order', ['status' => 'completed']);
            return true;
        }
        return false;
    }
}
