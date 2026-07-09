<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_ReturPenjualan.php
 *
 * Model untuk modul Surat Perintah Retur (SPR) / Surat Pengajuan Retur Barang.
 *
 * Alur:
 *   SC (buat SPR) → Koor SC (verifikasi) → Admin Stock (cek) → Kadep SC (setuju) → Logistik (proses)
 *
 * Tabel:
 *   - tb_spr_header : header SPR
 *   - tb_spr_detail : detail item barang per SPR
 */
class M_ReturPenjualan extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_ensureTables();
    }

    // ================================================================
    // AUTO-CREATE TABLES
    // ================================================================

    private function _ensureTables()
    {
        $this->_ensureHeaderTable();
        $this->_ensureDetailTable();
    }

    private function _ensureHeaderTable()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tb_spr_header` (
                `id_spr`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `no_spr`               VARCHAR(30)  NOT NULL,
                `tanggal`              DATE         NOT NULL,
                `kd_customer`          VARCHAR(30)  DEFAULT NULL,
                `nama_customer`        VARCHAR(200) DEFAULT NULL,
                `alamat`               VARCHAR(300) DEFAULT NULL,
                `nama_sales`           VARCHAR(150) DEFAULT NULL,
                `catatan`              TEXT         DEFAULT NULL,
                `status`               ENUM('draft','diajukan','diverifikasi_koor','dicek_admin_stock','disetujui_kadep','selesai','ditolak')
                                       NOT NULL DEFAULT 'draft',
                -- Koor SC
                `koor_sc_by`           VARCHAR(150) DEFAULT NULL,
                `koor_sc_at`           DATETIME     DEFAULT NULL,
                `koor_sc_catatan`      TEXT         DEFAULT NULL,
                -- Admin Stock
                `admin_stock_by`       VARCHAR(150) DEFAULT NULL,
                `admin_stock_at`       DATETIME     DEFAULT NULL,
                `admin_stock_catatan`  TEXT         DEFAULT NULL,
                -- Kadep SC
                `kadep_sc_by`          VARCHAR(150) DEFAULT NULL,
                `kadep_sc_at`          DATETIME     DEFAULT NULL,
                `kadep_sc_catatan`     TEXT         DEFAULT NULL,
                -- Logistik
                `logistik_by`          VARCHAR(150) DEFAULT NULL,
                `logistik_at`          DATETIME     DEFAULT NULL,
                `logistik_catatan`     TEXT         DEFAULT NULL,
                -- Audit
                `create_by`            VARCHAR(150) DEFAULT NULL,
                `create_at`            DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `update_by`            VARCHAR(150) DEFAULT NULL,
                `update_at`            DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_spr`),
                UNIQUE KEY `uq_no_spr` (`no_spr`),
                KEY `idx_status` (`status`),
                KEY `idx_create_by` (`create_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    private function _ensureDetailTable()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tb_spr_detail` (
                `id_spr_detail`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_spr`                      INT UNSIGNED NOT NULL,
                `no_urut`                     TINYINT UNSIGNED NOT NULL DEFAULT 1,
                `nama_barang`                 VARCHAR(250) DEFAULT NULL,
                `no_faktur`                   VARCHAR(80)  DEFAULT NULL,
                `no_batch`                    VARCHAR(80)  DEFAULT NULL,
                `qty`                         DECIMAL(12,3) DEFAULT 0,
                -- Alasan / Keterangan (sesuai form SPR)
                `alasan_brg_bermasalah`       TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_brg_bermasalah_opt`   ENUM('','replace','not_replace') NOT NULL DEFAULT '',
                `alasan_expired`              TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_expired_opt`          ENUM('','replace','not_replace') NOT NULL DEFAULT '',
                `alasan_tidak_laku`           TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_tes_market`           TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_bad_debt`             TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_harga_tidak_sesuai`   TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_spr_intern`           TINYINT(1) NOT NULL DEFAULT 0,
                `alasan_lainlain`             VARCHAR(300) DEFAULT NULL,
                PRIMARY KEY (`id_spr_detail`),
                KEY `idx_id_spr` (`id_spr`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    // ================================================================
    // GENERATE NOMOR SPR
    // Format: SPR/ddmmyy/0001
    // ================================================================

    public function generate_no_spr()
    {
        $prefix = 'SPR/' . date('dmy') . '/';

        $row = $this->db
            ->like('no_spr', $prefix, 'after')
            ->order_by('no_spr', 'DESC')
            ->limit(1)
            ->get('tb_spr_header')
            ->row();

        if ($row) {
            $last = (int) substr($row->no_spr, -4);
            return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    // ================================================================
    // MASTER DATA
    // ================================================================

    public function get_customers($nama_sales = null)
    {
        $nama_sales = trim((string) $nama_sales);
        if ($nama_sales !== '' && $this->db->field_exists('nama_sales', 'tb_customer')) {
            $this->db->where(
                'LOWER(TRIM(nama_sales)) = ' . $this->db->escape(strtolower($nama_sales)),
                null, false
            );
        }
        return $this->db
            ->order_by('nama_customer', 'ASC')
            ->get('tb_customer')
            ->result_array();
    }

    public function get_customer_by_kd($kd_customer)
    {
        return $this->db
            ->get_where('tb_customer', ['kd_customer' => $kd_customer])
            ->row_array();
    }

    // ================================================================
    // LIST / READ
    // ================================================================

    /**
     * Ambil semua SPR dengan filter opsional.
     *
     * Filter keys:
     *   status, date1, date2, create_by, kd_customer, id_spr
     */
    public function get_all_spr($filter = [])
    {
        $this->db->select('
            h.*,
            c.nama_customer AS nama_customer_master,
            c.alamat_kios   AS alamat_master,
            (SELECT COUNT(*) FROM tb_spr_detail d WHERE d.id_spr = h.id_spr) AS jumlah_item
        ');
        $this->db->from('tb_spr_header h');
        $this->db->join('tb_customer c', 'c.kd_customer = h.kd_customer', 'left');

        if (!empty($filter['status'])) {
            if (is_array($filter['status'])) {
                $this->db->where_in('h.status', $filter['status']);
            } else {
                $this->db->where('h.status', $filter['status']);
            }
        }
        if (!empty($filter['date1']))       $this->db->where('h.tanggal >=', $filter['date1']);
        if (!empty($filter['date2']))       $this->db->where('h.tanggal <=', $filter['date2']);
        if (!empty($filter['create_by']))   $this->db->where('h.create_by', $filter['create_by']);
        if (!empty($filter['kd_customer'])) $this->db->where('h.kd_customer', $filter['kd_customer']);

        $this->db->order_by('h.tanggal', 'DESC');
        $this->db->order_by('h.id_spr',  'DESC');

        return $this->db->get()->result_array();
    }

    public function get_spr($id_spr)
    {
        $this->db->select('h.*, c.nama_customer AS nama_customer_master, c.alamat_kios AS alamat_master, c.nama_sales AS sales_master');
        $this->db->from('tb_spr_header h');
        $this->db->join('tb_customer c', 'c.kd_customer = h.kd_customer', 'left');
        $this->db->where('h.id_spr', (int) $id_spr);
        return $this->db->get()->row_array();
    }

    public function get_spr_detail($id_spr)
    {
        return $this->db
            ->where('id_spr', (int) $id_spr)
            ->order_by('no_urut', 'ASC')
            ->get('tb_spr_detail')
            ->result_array();
    }

    // ================================================================
    // WRITE
    // ================================================================

    public function save_spr(array $data)
    {
        $this->db->insert('tb_spr_header', $data);
        return $this->db->insert_id();
    }

    public function save_spr_detail(array $rows)
    {
        if (empty($rows)) return;
        $this->db->insert_batch('tb_spr_detail', $rows);
    }

    public function delete_spr_detail($id_spr)
    {
        $this->db->delete('tb_spr_detail', ['id_spr' => (int) $id_spr]);
    }

    /**
     * Update status SPR beserta field audit dari masing-masing tahap.
     *
     * @param int    $id_spr
     * @param string $status  Nilai ENUM baru
     * @param array  $extra   Field tambahan, misal: ['koor_sc_by'=>...,'koor_sc_at'=>..., ...]
     */
    public function update_spr_status($id_spr, $status, array $extra = [])
    {
        $data = array_merge(['status' => $status], $extra);
        $this->db->where('id_spr', (int) $id_spr);
        $this->db->update('tb_spr_header', $data);
        return $this->db->affected_rows() > 0;
    }

    public function update_spr($id_spr, array $data)
    {
        $this->db->where('id_spr', (int) $id_spr);
        $this->db->update('tb_spr_header', $data);
        return $this->db->affected_rows() > 0;
    }

    // ================================================================
    // STATISTIK BADGE (untuk dashboard / sidebar)
    // ================================================================

    /**
     * Hitung jumlah SPR menunggu tindakan per status.
     * Berguna untuk badge notifikasi di menu.
     */
    public function count_pending($status)
    {
        return $this->db
            ->where('status', $status)
            ->count_all_results('tb_spr_header');
    }

    /**
     * Get history of SPR approvals and rejections processed by a specific user or role.
     */
    public function get_approval_history($username_or_name, $role, $filter = [])
    {
        $this->db->select('
            h.*,
            c.nama_customer AS nama_customer_master,
            c.alamat_kios   AS alamat_master,
            (SELECT COUNT(*) FROM tb_spr_detail d WHERE d.id_spr = h.id_spr) AS jumlah_item
        ');
        $this->db->from('tb_spr_header h');
        $this->db->join('tb_customer c', 'c.kd_customer = h.kd_customer', 'left');

        if ($role === 'admin') {
            // Admin sees all history (SPRs that are not draft/diajukan, meaning processed at least once)
            $this->db->where_in('h.status', ['diverifikasi_koor', 'dicek_admin_stock', 'disetujui_kadep', 'selesai', 'ditolak']);
        } else {
            $this->db->group_start();
            $this->db->where('h.koor_sc_by', $username_or_name);
            $this->db->or_where('h.admin_stock_by', $username_or_name);
            $this->db->or_where('h.kadep_sc_by', $username_or_name);
            $this->db->or_where('h.logistik_by', $username_or_name);
            $this->db->group_end();
        }

        if (!empty($filter['date1']))       $this->db->where('h.tanggal >=', $filter['date1']);
        if (!empty($filter['date2']))       $this->db->where('h.tanggal <=', $filter['date2']);
        if (!empty($filter['status']))      $this->db->where('h.status', $filter['status']);

        $this->db->order_by('h.tanggal', 'DESC');
        $this->db->order_by('h.id_spr',  'DESC');

        return $this->db->get()->result_array();
    }
}
