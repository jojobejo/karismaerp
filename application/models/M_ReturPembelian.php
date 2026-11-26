<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_ReturPembelian extends CI_Model
{
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_PURCHASING_VERIFIED = 'PURCHASING_VERIFIED';
    const STATUS_ACCOUNTING_VERIFIED = 'ACCOUNTING_VERIFIED';
    const STATUS_POSTED = 'POSTED';
    const STATUS_POSTING_EXCEPTION = 'POSTING_EXCEPTION';
    const STATUS_VOID = 'VOID';

    const PERSIAPAN_BELUM = 'BELUM_DISIAPKAN';
    const PERSIAPAN_SEDANG = 'SEDANG_DISIAPKAN';
    const PERSIAPAN_SUDAH = 'SUDAH_DISIAPKAN';
    const PERSIAPAN_SELESAI = 'SELESAI';

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_retur_pembelian` (
            `id_retur_pembelian` INT(11) NOT NULL AUTO_INCREMENT,
            `no_retur_pembelian` VARCHAR(50) NOT NULL,
            `id_lpb` INT(11) NOT NULL,
            `kd_po` VARCHAR(100) DEFAULT NULL,
            `no_po` VARCHAR(100) DEFAULT NULL,
            `kd_supplier` VARCHAR(100) DEFAULT NULL,
            `tanggal_retur` DATE NOT NULL,
            `gudang_id` VARCHAR(30) DEFAULT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'DRAFT',
            `status_persiapan` VARCHAR(30) NOT NULL DEFAULT 'BELUM_DISIAPKAN',
            `jenis_penyelesaian` VARCHAR(40) NOT NULL DEFAULT 'POTONG_HUTANG',
            `alasan_retur` TEXT DEFAULT NULL,
            `catatan_purchasing` TEXT DEFAULT NULL,
            `catatan_accounting` TEXT DEFAULT NULL,
            `catatan_persiapan` TEXT DEFAULT NULL,
            `total_dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total_ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `grand_total` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `id_jurnal` INT(11) DEFAULT NULL,
            `id_jurnal_reversal` INT(11) DEFAULT NULL,
            `created_by` VARCHAR(100) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_by` VARCHAR(100) DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            `submitted_by` VARCHAR(100) DEFAULT NULL,
            `submitted_at` DATETIME DEFAULT NULL,
            `purchasing_verified_by` VARCHAR(100) DEFAULT NULL,
            `purchasing_verified_at` DATETIME DEFAULT NULL,
            `accounting_verified_by` VARCHAR(100) DEFAULT NULL,
            `accounting_verified_at` DATETIME DEFAULT NULL,
            `posted_by` VARCHAR(100) DEFAULT NULL,
            `posted_at` DATETIME DEFAULT NULL,
            `reversed_by` VARCHAR(100) DEFAULT NULL,
            `reversed_at` DATETIME DEFAULT NULL,
            `disiapkan_oleh` VARCHAR(100) DEFAULT NULL,
            `disiapkan_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id_retur_pembelian`),
            UNIQUE KEY `uk_no_retur_pembelian` (`no_retur_pembelian`),
            KEY `idx_lpb` (`id_lpb`),
            KEY `idx_status` (`status`),
            KEY `idx_status_persiapan` (`status_persiapan`),
            KEY `idx_jurnal` (`id_jurnal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $fields = $this->db->list_fields('tb_retur_pembelian');
        if (!in_array('status_persiapan', $fields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian` ADD COLUMN `status_persiapan` VARCHAR(30) NOT NULL DEFAULT 'BELUM_DISIAPKAN' AFTER `status`");
            $this->db->query("ALTER TABLE `tb_retur_pembelian` ADD KEY `idx_status_persiapan` (`status_persiapan`)");
        }
        if (!in_array('catatan_persiapan', $fields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian` ADD COLUMN `catatan_persiapan` TEXT NULL AFTER `catatan_accounting`");
        }
        if (!in_array('disiapkan_oleh', $fields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian` ADD COLUMN `disiapkan_oleh` VARCHAR(100) NULL AFTER `reversed_at`");
        }
        if (!in_array('disiapkan_at', $fields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian` ADD COLUMN `disiapkan_at` DATETIME NULL AFTER `disiapkan_oleh`");
        }

        $detailFields = $this->db->list_fields('tb_retur_pembelian_detail');
        if (!in_array('is_disiapkan', $detailFields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian_detail` ADD COLUMN `is_disiapkan` TINYINT(1) NOT NULL DEFAULT 0 AFTER `alasan_retur`");
            $this->db->query("ALTER TABLE `tb_retur_pembelian_detail` ADD KEY `idx_detail_is_disiapkan` (`is_disiapkan`)");
        }
        if (!in_array('disiapkan_at', $detailFields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian_detail` ADD COLUMN `disiapkan_at` DATETIME NULL AFTER `is_disiapkan`");
        }
        if (!in_array('disiapkan_oleh', $detailFields, true)) {
            $this->db->query("ALTER TABLE `tb_retur_pembelian_detail` ADD COLUMN `disiapkan_oleh` VARCHAR(100) NULL AFTER `disiapkan_at`");
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_retur_pembelian_detail` (
            `id_detail_retur_pembelian` INT(11) NOT NULL AUTO_INCREMENT,
            `id_retur_pembelian` INT(11) NOT NULL,
            `id_detail_lpb` INT(11) NOT NULL,
            `kd_barang` VARCHAR(100) NOT NULL,
            `no_lot` VARCHAR(50) DEFAULT NULL,
            `expired_date` DATE DEFAULT NULL,
            `qty_retur` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `harga_satuan` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `kelompok_dagang` VARCHAR(10) DEFAULT NULL,
            `alasan_retur` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_detail_retur_pembelian`),
            KEY `idx_retur` (`id_retur_pembelian`),
            KEY `idx_detail_lpb` (`id_detail_lpb`),
            KEY `idx_barang_batch` (`kd_barang`, `no_lot`, `expired_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_retur_pembelian_log` (
            `id_log` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `id_retur_pembelian` INT(11) DEFAULT NULL,
            `no_retur_pembelian` VARCHAR(50) DEFAULT NULL,
            `action_type` VARCHAR(40) NOT NULL,
            `status_before` VARCHAR(30) DEFAULT NULL,
            `status_after` VARCHAR(30) DEFAULT NULL,
            `data_before` LONGTEXT DEFAULT NULL,
            `data_after` LONGTEXT DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `dilakukan_oleh` VARCHAR(100) DEFAULT NULL,
            `dilakukan_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_log`),
            KEY `idx_retur_log` (`id_retur_pembelian`),
            KEY `idx_action` (`action_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_tmp_retur_pembelian` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `kd_supplier` VARCHAR(100) NOT NULL,
            `nama_supplier` VARCHAR(255) DEFAULT NULL,
            `gudang_id` VARCHAR(30) NOT NULL,
            `kd_barang` VARCHAR(100) NOT NULL,
            `nama_barang` VARCHAR(255) DEFAULT NULL,
            `satuan` VARCHAR(50) DEFAULT 'PCS',
            `no_lot` VARCHAR(50) DEFAULT NULL,
            `expired_date` DATE DEFAULT NULL,
            `qty_retur` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `user_inputer` VARCHAR(100) NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_tmp_user` (`user_inputer`),
            KEY `idx_tmp_supplier` (`kd_supplier`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->ensure_gudang_retur_pembelian();
    }

    /**
     * Memastikan master gudang 'Gdg. Retur Pembelian' tersedia di tb_gudang.
     * Mengembalikan objek baris gudang tersebut.
     */
    public function ensure_gudang_retur_pembelian()
    {
        if (!$this->db->table_exists('tb_gudang')) {
            return null;
        }

        $row = $this->db
            ->where('LOWER(TRIM(nama_gudang))', 'gdg. retur pembelian')
            ->or_where('LOWER(TRIM(nama_gudang))', 'gudang retur pembelian')
            ->order_by('id_gudang', 'ASC')
            ->limit(1)
            ->get('tb_gudang')
            ->row_array();

        if ($row) {
            if ((int)($row['is_active'] ?? 1) !== 1) {
                $this->db->where('id_gudang', (int)$row['id_gudang'])->update('tb_gudang', ['is_active' => 1]);
                $row['is_active'] = 1;
            }
            return $row;
        }

        $insertData = [
            'nama_gudang' => 'Gdg. Retur Pembelian',
            'tipe'        => 'INDUK',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tb_gudang', $insertData);
        $newId = (int)$this->db->insert_id();

        return $this->db->where('id_gudang', $newId)->get('tb_gudang')->row_array();
    }

    public function get_gudang_retur_pembelian_id()
    {
        $gudang = $this->ensure_gudang_retur_pembelian();
        return $gudang ? (int)$gudang['id_gudang'] : 0;
    }

    public function header_rows($limit = 100)
    {
        $this->ensure_schema();
        $sql = "SELECT r.*, 
                    MAX(l.nomor_lpb) AS nomor_lpb, 
                    MAX(s.nama_suplier) AS nama_suplier,
                    MAX(g.nama_gudang) AS nama_gudang,
                    COUNT(d.id_detail_retur_pembelian) AS total_item,
                    COALESCE(SUM(d.qty_retur), 0) AS total_qty_retur
                FROM tb_retur_pembelian r
                LEFT JOIN tb_lpb l ON l.id_lpb = r.id_lpb
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = r.kd_supplier
                LEFT JOIN tb_gudang g ON CAST(g.id_gudang AS CHAR) = CAST(r.gudang_id AS CHAR)
                LEFT JOIN tb_retur_pembelian_detail d ON d.id_retur_pembelian = r.id_retur_pembelian
                GROUP BY r.id_retur_pembelian
                ORDER BY r.created_at DESC, r.id_retur_pembelian DESC
                LIMIT ?";
        return $this->db->query($sql, [(int)$limit])->result_array();
    }

    public function monitoring_rows($limit = 100, $filters = [])
    {
        $this->ensure_schema();
        $where = [];
        $params = [];

        if (!empty($filters['status_persiapan'])) {
            $where[] = "r.status_persiapan = ?";
            $params[] = $filters['status_persiapan'];
        }
        if (!empty($filters['status_dokumen'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status_dokumen'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "r.tanggal_retur >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "r.tanggal_retur <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['search'])) {
            $like = '%' . trim((string)$filters['search']) . '%';
            $where[] = "(r.no_retur_pembelian LIKE ? OR l.nomor_lpb LIKE ? OR r.no_po LIKE ? OR s.nama_suplier LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $params[] = (int)$limit;

        $sql = "SELECT r.*, 
                    MAX(l.nomor_lpb) AS nomor_lpb, 
                    MAX(s.nama_suplier) AS nama_suplier,
                    COUNT(d.id_detail_retur_pembelian) AS total_item,
                    COALESCE(SUM(d.qty_retur), 0) AS total_qty_retur,
                    COALESCE(SUM(CASE WHEN d.is_disiapkan = 1 THEN 1 ELSE 0 END), 0) AS total_item_disiapkan,
                    GROUP_CONCAT(DISTINCT COALESCE(b.nama_barang, d.kd_barang) SEPARATOR ', ') AS ringkasan_barang
                FROM tb_retur_pembelian r
                LEFT JOIN tb_lpb l ON l.id_lpb = r.id_lpb
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = r.kd_supplier
                LEFT JOIN tb_retur_pembelian_detail d ON d.id_retur_pembelian = r.id_retur_pembelian
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                {$whereClause}
                GROUP BY r.id_retur_pembelian
                ORDER BY r.created_at DESC, r.id_retur_pembelian DESC
                LIMIT ?";
        return $this->db->query($sql, $params)->result_array();
    }

    public function retur_items_detail($idRetur)
    {
        $sql = "SELECT d.*, 
                    COALESCE(b.nama_barang, d.kd_barang) AS nama_barang,
                    COALESCE(b.satuan, 'PCS') AS satuan
                FROM tb_retur_pembelian_detail d
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                WHERE d.id_retur_pembelian = ?
                ORDER BY d.id_detail_retur_pembelian ASC";
        return $this->db->query($sql, [(int)$idRetur])->result_array();
    }

    public function update_status_persiapan($idRetur, $statusPersiapan, $catatan, $user, $itemsDisiapkan = null)
    {
        $this->ensure_schema();
        $allowedStatus = [self::PERSIAPAN_BELUM, self::PERSIAPAN_SEDANG, self::PERSIAPAN_SUDAH, self::PERSIAPAN_SELESAI];
        $statusPersiapan = strtoupper(trim((string)$statusPersiapan));
        if (!in_array($statusPersiapan, $allowedStatus, true)) {
            return $this->fail('Status persiapan barang tidak valid.', ['INVALID_STATUS_PERSIAPAN']);
        }

        $header = $this->header((int)$idRetur);
        if (!$header) {
            return $this->fail('Dokumen retur pembelian tidak ditemukan.', ['RETUR_NOT_FOUND']);
        }

        $updateData = [
            'status_persiapan' => $statusPersiapan,
            'catatan_persiapan' => trim((string)$catatan),
            'disiapkan_oleh' => $user,
            'disiapkan_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_begin();
        $this->db->where('id_retur_pembelian', (int)$idRetur)->update('tb_retur_pembelian', $updateData);

        // Jika ada checklist item yang dikirim
        if (is_array($itemsDisiapkan)) {
            $allDetails = $this->details((int)$idRetur);
            foreach ($allDetails as $det) {
                $detId = (int)$det['id_detail_retur_pembelian'];
                $isSiap = in_array($detId, $itemsDisiapkan, false) || in_array((string)$detId, $itemsDisiapkan, true);
                $this->db->where('id_detail_retur_pembelian', $detId)->update('tb_retur_pembelian_detail', [
                    'is_disiapkan' => $isSiap ? 1 : 0,
                    'disiapkan_at' => $isSiap ? (!empty($det['disiapkan_at']) ? $det['disiapkan_at'] : date('Y-m-d H:i:s')) : null,
                    'disiapkan_oleh' => $isSiap ? (!empty($det['disiapkan_oleh']) ? $det['disiapkan_oleh'] : $user) : null,
                ]);
            }
        }

        $after = $this->header((int)$idRetur);
        $this->write_log(
            (int)$idRetur,
            'UPDATE_PERSIAPAN',
            $header['status_persiapan'] ?? self::PERSIAPAN_BELUM,
            $statusPersiapan,
            $header,
            $after,
            'Update status persiapan barang oleh ' . $user . ': ' . $statusPersiapan . ($catatan !== '' ? ' (' . $catatan . ')' : ''),
            $user
        );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Gagal memperbarui status persiapan barang.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();

        $detailSummary = $this->db->query(
            "SELECT COUNT(id_detail_retur_pembelian) AS total_item, 
                    COALESCE(SUM(CASE WHEN is_disiapkan = 1 THEN 1 ELSE 0 END), 0) AS total_item_disiapkan
             FROM tb_retur_pembelian_detail
             WHERE id_retur_pembelian = ?",
            [(int)$idRetur]
        )->row_array();

        return $this->ok('Status persiapan barang berhasil diperbarui.', [
            'id_retur_pembelian'   => (int)$idRetur,
            'status_persiapan'     => $statusPersiapan,
            'catatan_persiapan'    => trim((string)$catatan),
            'disiapkan_oleh'       => $user,
            'disiapkan_at'         => $updateData['disiapkan_at'],
            'total_item'           => (int)($detailSummary['total_item'] ?? 0),
            'total_item_disiapkan' => (int)($detailSummary['total_item_disiapkan'] ?? 0),
        ]);
    }

    public function toggle_item_persiapan($idDetail, $isDisiapkan, $user)
    {
        $this->ensure_schema();
        $detail = $this->db->where('id_detail_retur_pembelian', (int)$idDetail)->get('tb_retur_pembelian_detail')->row_array();
        if (!$detail) {
            return $this->fail('Detail item retur tidak ditemukan.', ['DETAIL_NOT_FOUND']);
        }
        $idRetur = (int)$detail['id_retur_pembelian'];
        $isDisiapkan = ((int)$isDisiapkan === 1) ? 1 : 0;

        $this->db->trans_begin();
        $this->db->where('id_detail_retur_pembelian', (int)$idDetail)->update('tb_retur_pembelian_detail', [
            'is_disiapkan' => $isDisiapkan,
            'disiapkan_at' => $isDisiapkan ? date('Y-m-d H:i:s') : null,
            'disiapkan_oleh' => $isDisiapkan ? $user : null,
        ]);

        $detailSummary = $this->db->query(
            "SELECT COUNT(id_detail_retur_pembelian) AS total_item, 
                    COALESCE(SUM(CASE WHEN is_disiapkan = 1 THEN 1 ELSE 0 END), 0) AS total_item_disiapkan
             FROM tb_retur_pembelian_detail
             WHERE id_retur_pembelian = ?",
            [$idRetur]
        )->row_array();

        $total = (int)($detailSummary['total_item'] ?? 0);
        $siap = (int)($detailSummary['total_item_disiapkan'] ?? 0);

        $header = $this->header($idRetur);
        $newStatus = $header['status_persiapan'];
        if ($header['status_persiapan'] !== self::PERSIAPAN_SELESAI) {
            if ($siap === 0) {
                $newStatus = self::PERSIAPAN_BELUM;
            } elseif ($siap === $total) {
                $newStatus = self::PERSIAPAN_SUDAH;
            } else {
                $newStatus = self::PERSIAPAN_SEDANG;
            }
            if ($newStatus !== $header['status_persiapan']) {
                $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', [
                    'status_persiapan' => $newStatus,
                    'disiapkan_oleh'   => $user,
                    'disiapkan_at'     => date('Y-m-d H:i:s'),
                    'updated_by'       => $user,
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Gagal memperbarui status item.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();

        return $this->ok('Status persiapan item berhasil diperbarui.', [
            'id_retur'             => $idRetur,
            'id_detail'            => (int)$idDetail,
            'is_disiapkan'         => $isDisiapkan,
            'total_item'           => $total,
            'total_item_disiapkan' => $siap,
            'status_persiapan'     => $newStatus
        ]);
    }

    public function lpb_options($search = '')
    {
        $search = trim((string)$search);
        $params = [];
        $where = "WHERE COALESCE(NULLIF(TRIM(l.nomor_lpb), ''), '') <> ''";

        if ($search !== '') {
            $where .= " AND (l.nomor_lpb LIKE ? OR l.no_po LIKE ? OR l.kd_po LIKE ? OR s.nama_suplier LIKE ?)";
            $like = '%' . $search . '%';
            $params = [$like, $like, $like, $like];
        }

        $sql = "SELECT l.id_lpb, l.nomor_lpb, l.kd_po, l.no_po, l.gudang_id,
                    p.kd_suplier, COALESCE(s.nama_suplier, '') AS nama_suplier
                FROM tb_lpb l
                LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
                {$where}
                ORDER BY l.input_at DESC, l.id_lpb DESC
                LIMIT 30";
        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Mengambil daftar supplier untuk dropdown Select2 pada halaman Retur Pembelian.
     */
    public function supplier_options($search = '')
    {
        $search = trim((string)$search);
        $this->db->select('kd_suplier, nama_suplier, alamat_suplier')
            ->from('tbpo_suplier')
            ->where("COALESCE(NULLIF(TRIM(kd_suplier), ''), '') <> ''", null, false);

        if ($search !== '') {
            $this->db->group_start()
                ->like('kd_suplier', $search)
                ->or_like('nama_suplier', $search)
                ->group_end();
        }

        $this->db->order_by('nama_suplier', 'ASC')->limit(30);
        return $this->db->get()->result_array();
    }

    /**
     * Mencari daftar nama barang yang memiliki stok tersedia di Gudang Retur Pembelian.
     */
    public function barang_gudang_retur_options($gudangId, $search = '')
    {
        $this->ensure_schema();
        $gudangId = trim((string)$gudangId);
        if ($gudangId === '') {
            $gudangId = (string)$this->get_gudang_retur_pembelian_id();
        }

        $params = [$gudangId];
        $whereSearch = '';
        $search = trim((string)$search);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $whereSearch = " AND (sb.kd_barang LIKE ? OR b.nama_barang LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "SELECT 
                    sb.kd_barang,
                    COALESCE(NULLIF(TRIM(MAX(b.nama_barang)), ''), sb.kd_barang) AS nama_barang,
                    COALESCE(NULLIF(TRIM(MAX(b.satuan)), ''), 'PCS') AS satuan_kecil,
                    COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS qty_tersedia
                FROM tberp_stock_batch sb
                LEFT JOIN tbpo_barang b ON b.kode_barang = sb.kd_barang
                WHERE CAST(sb.gudang_id AS CHAR) = ?
                    {$whereSearch}
                GROUP BY sb.kd_barang
                HAVING qty_tersedia > 0
                ORDER BY nama_barang ASC
                LIMIT 40";
        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Mengambil daftar No Lot berdasarkan Nama/Kode Barang yang dipilih dan Gudang Retur Pembelian.
     */
    public function lot_gudang_retur_options($gudangId, $kdBarang, $search = '')
    {
        $this->ensure_schema();
        $gudangId = trim((string)$gudangId);
        $kdBarang = trim((string)$kdBarang);
        if ($gudangId === '' || $kdBarang === '') {
            return [];
        }

        $params = [$gudangId, $kdBarang];
        $whereSearch = '';
        $search = trim((string)$search);
        if ($search !== '') {
            $whereSearch = " AND COALESCE(NULLIF(TRIM(sb.no_lot), ''), '-') LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql = "SELECT 
                    COALESCE(NULLIF(TRIM(sb.no_lot), ''), '-') AS no_lot,
                    COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS qty_tersedia
                FROM tberp_stock_batch sb
                WHERE CAST(sb.gudang_id AS CHAR) = ?
                    AND sb.kd_barang = ?
                    {$whereSearch}
                GROUP BY COALESCE(NULLIF(TRIM(sb.no_lot), ''), '-')
                HAVING qty_tersedia > 0
                ORDER BY no_lot ASC
                LIMIT 40";
        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Mengambil daftar Expired Date yang menyesuaikan dengan No Lot, Barang, dan Gudang Retur Pembelian.
     */
    public function exp_gudang_retur_options($gudangId, $kdBarang, $noLot, $search = '')
    {
        $this->ensure_schema();
        $gudangId = trim((string)$gudangId);
        $kdBarang = trim((string)$kdBarang);
        $noLot = trim((string)$noLot);
        if ($gudangId === '' || $kdBarang === '' || $noLot === '') {
            return [];
        }

        $params = [$gudangId, $kdBarang, $noLot];
        $whereSearch = '';
        $search = trim((string)$search);
        if ($search !== '') {
            $whereSearch = " AND COALESCE(CAST(sb.expired_date AS CHAR), '-') LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql = "SELECT 
                    COALESCE(CAST(sb.expired_date AS CHAR), '-') AS expired_date,
                    COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS qty_tersedia,
                    COALESCE(NULLIF(TRIM(MAX(b.satuan)), ''), 'PCS') AS satuan_kecil
                FROM tberp_stock_batch sb
                LEFT JOIN tbpo_barang b ON b.kode_barang = sb.kd_barang
                WHERE CAST(sb.gudang_id AS CHAR) = ?
                    AND sb.kd_barang = ?
                    AND COALESCE(NULLIF(TRIM(sb.no_lot), ''), '-') = ?
                    {$whereSearch}
                GROUP BY sb.expired_date
                HAVING qty_tersedia > 0
                ORDER BY sb.expired_date ASC
                LIMIT 40";
        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Mengambil stok tersedia spesifik untuk gudang, barang, no_lot, dan expired_date.
     */
    public function get_batch_available_qty($gudangId, $kdBarang, $noLot, $expiredDate)
    {
        $gudangId = trim((string)$gudangId);
        $kdBarang = trim((string)$kdBarang);
        $noLot = trim((string)$noLot);
        $noLot = ($noLot === '') ? '-' : $noLot;
        $normExp = $this->normalize_date($expiredDate);

        $params = [$gudangId, $kdBarang, $noLot];
        $whereExp = $normExp !== '' ? "AND sb.expired_date = ?" : "AND (sb.expired_date IS NULL OR CAST(sb.expired_date AS CHAR) IN ('', '0000-00-00'))";
        if ($normExp !== '') {
            $params[] = $normExp;
        }

        $row = $this->db->query(
            "SELECT COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS qty_tersedia
             FROM tberp_stock_batch sb
             WHERE CAST(sb.gudang_id AS CHAR) = ?
               AND sb.kd_barang = ?
               AND COALESCE(NULLIF(TRIM(sb.no_lot), ''), '-') = ?
               {$whereExp}",
            $params
        )->row_array();

        return (float)($row['qty_tersedia'] ?? 0);
    }

    /**
     * Mengambil daftar item draft temporary retur pembelian berdasarkan user aktif.
     */
    public function get_tmp_draft_rows($user)
    {
        $this->ensure_schema();
        $user = trim((string)$user);
        $sql = "SELECT t.*,
                    COALESCE(NULLIF(TRIM(s.nama_suplier), ''), t.nama_supplier, t.kd_supplier) AS supplier_label,
                    COALESCE(NULLIF(TRIM(b.nama_barang), ''), t.nama_barang, t.kd_barang) AS barang_label,
                    COALESCE(NULLIF(TRIM(b.satuan), ''), t.satuan, 'PCS') AS satuan_kecil
                FROM tb_tmp_retur_pembelian t
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = t.kd_supplier
                LEFT JOIN tbpo_barang b ON b.kode_barang = t.kd_barang
                WHERE t.user_inputer = ?
                ORDER BY t.id ASC";
        return $this->db->query($sql, [$user])->result_array();
    }

    /**
     * Menambahkan item ke tabel draft temporary (tb_tmp_retur_pembelian).
     */
    public function add_tmp_draft_item($payload, $user)
    {
        $this->ensure_schema();
        $user = trim((string)$user);
        $kdSupplier = trim((string)($payload['kd_supplier'] ?? ''));
        $gudangId = trim((string)($payload['gudang_id'] ?? ''));
        if ($gudangId === '') {
            $gudangId = (string)$this->get_gudang_retur_pembelian_id();
        }
        $kdBarang = trim((string)($payload['kd_barang'] ?? ''));
        $noLot = trim((string)($payload['no_lot'] ?? ''));
        $noLot = ($noLot === '') ? '-' : $noLot;
        $expiredDateRaw = trim((string)($payload['expired_date'] ?? ''));
        $expiredDate = ($expiredDateRaw !== '' && $expiredDateRaw !== '-') ? $this->normalize_date($expiredDateRaw) : null;
        $qtyRetur = (float)($payload['qty_retur'] ?? 0);

        if ($kdSupplier === '' || $kdBarang === '' || $noLot === '' || $qtyRetur <= 0) {
            return $this->fail('Supplier, Nama Barang, No Lot, Expired Date, dan Qty wajib diisi dengan benar.', ['INVALID_DRAFT_INPUT']);
        }

        // Pastikan supplier konsisten dalam 1 draft user
        $existingRows = $this->get_tmp_draft_rows($user);
        if (!empty($existingRows)) {
            $firstSupplier = trim((string)$existingRows[0]['kd_supplier']);
            if ($firstSupplier !== '' && $firstSupplier !== $kdSupplier) {
                return $this->fail(
                    'Draft aktif saat ini menggunakan supplier ' . ($existingRows[0]['supplier_label'] ?? $firstSupplier) . '. Selesaikan/posting atau hapus draft terlebih dahulu sebelum mengganti supplier.',
                    ['SUPPLIER_MISMATCH']
                );
            }
        }

        $availableQty = $this->get_batch_available_qty($gudangId, $kdBarang, $noLot, $expiredDate ?? '');
        if ($availableQty <= 0) {
            return $this->fail('Stok batch barang tersebut tidak tersedia di Gudang Retur Pembelian.', ['BATCH_EMPTY']);
        }

        // Cek apakah batch yang sama sudah ada di draft user
        $existingBatch = null;
        foreach ($existingRows as $r) {
            $rLot = trim((string)($r['no_lot'] ?: '-'));
            $rExp = $r['expired_date'] ? $this->normalize_date($r['expired_date']) : null;
            if ($r['kd_barang'] === $kdBarang && $rLot === $noLot && $rExp === $expiredDate && (string)$r['gudang_id'] === $gudangId) {
                $existingBatch = $r;
                break;
            }
        }

        $newTotalQty = $qtyRetur + ($existingBatch ? (float)$existingBatch['qty_retur'] : 0);
        if ($newTotalQty > $availableQty + 0.0001) {
            return $this->fail(
                'Total Qty retur (' . number_format($newTotalQty, 2, ',', '.') . ') melebihi stok tersedia di Gudang Retur Pembelian (' . number_format($availableQty, 2, ',', '.') . ').',
                ['QTY_EXCEEDS_STOCK']
            );
        }

        $barangRow = $this->db->select('nama_barang, satuan')->where('kode_barang', $kdBarang)->get('tbpo_barang')->row_array();
        $supplierRow = $this->db->select('nama_suplier')->where('kd_suplier', $kdSupplier)->get('tbpo_suplier')->row_array();

        if ($existingBatch) {
            $this->db->where('id', (int)$existingBatch['id'])->update('tb_tmp_retur_pembelian', [
                'qty_retur' => $newTotalQty,
            ]);
        } else {
            $this->db->insert('tb_tmp_retur_pembelian', [
                'kd_supplier'   => $kdSupplier,
                'nama_supplier' => $supplierRow['nama_suplier'] ?? $kdSupplier,
                'gudang_id'     => $gudangId,
                'kd_barang'     => $kdBarang,
                'nama_barang'   => $barangRow['nama_barang'] ?? $kdBarang,
                'satuan'        => !empty($barangRow['satuan']) ? $barangRow['satuan'] : 'PCS',
                'no_lot'        => $noLot,
                'expired_date'  => $expiredDate ?: null,
                'qty_retur'     => $qtyRetur,
                'user_inputer'  => $user,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->ok('Item berhasil ditambahkan ke draft retur pembelian.', [
            'rows' => $this->get_tmp_draft_rows($user),
        ]);
    }

    /**
     * Menghapus satu baris item dari draft temporary.
     */
    public function delete_tmp_draft_item($id, $user)
    {
        $this->ensure_schema();
        $id = (int)$id;
        $user = trim((string)$user);
        if ($id <= 0) {
            return $this->fail('ID item draft tidak valid.', ['INVALID_ID']);
        }

        $this->db->where('id', $id)->where('user_inputer', $user)->delete('tb_tmp_retur_pembelian');
        return $this->ok('Item draft berhasil dihapus.', [
            'rows' => $this->get_tmp_draft_rows($user),
        ]);
    }

    /**
     * Mengosongkan seluruh draft temporary milik user.
     */
    public function clear_tmp_draft($user)
    {
        $this->ensure_schema();
        $user = trim((string)$user);
        $this->db->where('user_inputer', $user)->delete('tb_tmp_retur_pembelian');
        return $this->ok('Draft retur pembelian berhasil dikosongkan.', ['rows' => []]);
    }

    /**
     * Memposting data draft retur pembelian dari Gudang Retur Pembelian:
     * - Menyimpan dokumen ke tb_retur_pembelian & tb_retur_pembelian_detail
     * - Menginput Jenis Penyelesaian & Alasan Retur setelah pembuatan draft
     * - Mengurangi stok fisik di Gudang Retur Pembelian dan mencatat ke tberp_stock_ledger (RBELI)
     */
    public function post_from_tmp_draft($payload, $user)
    {
        $this->ensure_schema();
        $user = trim((string)$user);
        $draftRows = $this->get_tmp_draft_rows($user);
        if (empty($draftRows)) {
            return $this->fail('Draft item retur pembelian masih kosong. Tambahkan minimal 1 barang terlebih dahulu.', ['DRAFT_EMPTY']);
        }

        $tanggal = $this->normalize_date($payload['tanggal_retur'] ?? date('Y-m-d'));
        $jenis = strtoupper(trim((string)($payload['jenis_penyelesaian'] ?? '')));
        $alasan = trim((string)($payload['alasan_retur'] ?? ''));
        $kdSupplier = trim((string)($payload['kd_supplier'] ?? ($draftRows[0]['kd_supplier'] ?? '')));
        $gudangId = trim((string)($draftRows[0]['gudang_id'] ?? $this->get_gudang_retur_pembelian_id()));

        if ($tanggal === '') {
            return $this->fail('Tanggal retur wajib diisi.', ['DATE_REQUIRED']);
        }
        if ($jenis === '') {
            return $this->fail('Jenis penyelesaian wajib dipilih.', ['SETTLEMENT_REQUIRED']);
        }
        if ($alasan === '') {
            return $this->fail('Alasan retur wajib diisi sebelum memposting data.', ['REASON_REQUIRED']);
        }

        // Validasi stok fisik setiap baris draft terhadap Gudang Retur Pembelian
        foreach ($draftRows as $d) {
            $avail = $this->get_batch_available_qty($gudangId, $d['kd_barang'], $d['no_lot'], $d['expired_date'] ?? '');
            if ((float)$d['qty_retur'] > $avail + 0.0001) {
                return $this->fail(
                    'Stok fisik barang ' . ($d['barang_label'] ?? $d['kd_barang']) . ' (Lot: ' . ($d['no_lot'] ?: '-') . ') tidak mencukupi di Gudang Retur Pembelian. Tersedia: ' . number_format($avail, 2, ',', '.'),
                    ['STOCK_NOT_ENOUGH']
                );
            }
        }

        $this->db->trans_begin();
        $noRetur = $this->generate_number();
        $now = date('Y-m-d H:i:s');

        $headerData = [
            'no_retur_pembelian' => $noRetur,
            'id_lpb'             => 0,
            'kd_po'              => null,
            'no_po'              => null,
            'kd_supplier'        => $kdSupplier,
            'tanggal_retur'      => $tanggal,
            'gudang_id'          => (string)$gudangId,
            'status'             => self::STATUS_POSTED,
            'status_persiapan'   => self::PERSIAPAN_BELUM,
            'jenis_penyelesaian' => $jenis,
            'alasan_retur'       => $alasan,
            'total_dpp'          => '0.0000',
            'total_ppn'          => '0.0000',
            'grand_total'        => '0.0000',
            'created_by'         => $user,
            'created_at'         => $now,
            'updated_by'         => $user,
            'updated_at'         => $now,
            'submitted_by'       => $user,
            'submitted_at'       => $now,
            'posted_by'          => $user,
            'posted_at'          => $now,
        ];

        $this->db->insert('tb_retur_pembelian', $headerData);
        $idRetur = (int)$this->db->insert_id();

        $totals = ['dpp' => '0.0000', 'ppn' => '0.0000', 'grand' => '0.0000'];

        foreach ($draftRows as $d) {
            $resolved = $this->resolve_latest_purchase_info($d['kd_barang'], $d['no_lot'], $d['expired_date'], $kdSupplier);
            $hargaSatuan = $this->money($resolved['harga_satuan'] ?? '0');
            $kelompok = (string)($resolved['kelompok_dagang'] ?? '');
            $qty = (float)$d['qty_retur'];
            $dpp = $this->money(bcmul((string)$qty, $hargaSatuan, 4));
            $ppn = ($kelompok === '2') ? $this->money(bcdiv(bcmul($dpp, '11', 4), '100', 4)) : '0.0000';
            $total = bcadd($dpp, $ppn, 4);

            $detailRow = [
                'id_retur_pembelian' => $idRetur,
                'id_detail_lpb'      => (int)($resolved['id_detail_lpb'] ?? 0),
                'kd_barang'          => $d['kd_barang'],
                'no_lot'             => $d['no_lot'],
                'expired_date'       => $d['expired_date'] ?: null,
                'qty_retur'          => $qty,
                'harga_satuan'       => $hargaSatuan,
                'dpp'                => $dpp,
                'ppn'                => $ppn,
                'total'              => $total,
                'kelompok_dagang'    => $kelompok,
                'alasan_retur'       => $alasan,
                'created_at'         => $now,
            ];

            $this->db->insert('tb_retur_pembelian_detail', $detailRow);

            // Kurangi stok fisik dari Gudang Retur Pembelian dan catat di stock ledger
            $this->decrease_stock($headerData, $detailRow);

            $totals['dpp'] = bcadd($totals['dpp'], $dpp, 4);
            $totals['ppn'] = bcadd($totals['ppn'], $ppn, 4);
            $totals['grand'] = bcadd($totals['grand'], $total, 4);
        }

        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', [
            'total_dpp'   => $totals['dpp'],
            'total_ppn'   => $totals['ppn'],
            'grand_total' => $totals['grand'],
        ]);

        // Kosongkan draft temporary milik user
        $this->db->where('user_inputer', $user)->delete('tb_tmp_retur_pembelian');

        $after = $this->header($idRetur);
        $this->write_log(
            $idRetur,
            'POST_GUDANG_RETUR',
            self::STATUS_DRAFT,
            self::STATUS_POSTED,
            null,
            $after,
            'Retur pembelian diposting dari Gudang Retur Pembelian (' . $jenis . ').',
            $user
        );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Gagal memposting data retur pembelian.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Data Retur Pembelian (' . $noRetur . ') berhasil diposting dan stok Gudang Retur Pembelian telah dikurangi.', [
            'id_retur_pembelian' => $idRetur,
            'no_retur_pembelian' => $noRetur,
        ]);
    }

    /**
     * Mencari referensi harga satuan & kelompok dagang terakhir dari LPB/PO jika tersedia (opsional).
     */
    private function resolve_latest_purchase_info($kdBarang, $noLot, $expiredDate, $kdSupplier = '')
    {
        $barang = $this->db->select('kelompok_dagang, kelompok_barang')
            ->where('kode_barang', $kdBarang)
            ->get('tbpo_barang')
            ->row_array();

        $kelompok = trim((string)($barang['kelompok_dagang'] ?? ($barang['kelompok_barang'] ?? '')));

        $sql = "SELECT d.id_detail_lpb, d.id_lpb,
                    COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                LEFT JOIN tbpo_detail_po pp ON pp.kd_po = l.kd_po AND pp.no_po = l.no_po AND pp.kd_barang = d.kd_barang
                WHERE d.kd_barang = ?
                ORDER BY (CASE WHEN COALESCE(d.no_lot, '-') = COALESCE(?, '-') THEN 0 ELSE 1 END) ASC,
                         d.id_detail_lpb DESC
                LIMIT 1";
        $lpbRow = $this->db->query($sql, [$kdBarang, $noLot])->row_array();

        return [
            'id_detail_lpb'   => (int)($lpbRow['id_detail_lpb'] ?? 0),
            'harga_satuan'    => (float)($lpbRow['harga_satuan'] ?? 0),
            'kelompok_dagang' => $kelompok,
        ];
    }

    public function lpb_detail_rows($idLpb)
    {
        $sql = "SELECT d.id_detail_lpb, d.id_lpb, d.kd_barang,
                    COALESCE(m.nama_barang, b.nama_barang, d.kd_barang) AS nama_barang,
                    d.no_lot, d.expired_date, COALESCE(d.qty_diterima, 0) AS qty_diterima,
                    COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan,
                    COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') AS kelompok_dagang,
                    COALESCE((
                        SELECT SUM(rd.qty_retur)
                        FROM tb_retur_pembelian_detail rd
                        INNER JOIN tb_retur_pembelian rh ON rh.id_retur_pembelian = rd.id_retur_pembelian
                        WHERE rd.id_detail_lpb = d.id_detail_lpb
                            AND rh.status NOT IN ('VOID')
                    ), 0) AS qty_retur_sebelumnya,
                    COALESCE(sb.qty_on_hand, 0) AS qty_on_hand
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                LEFT JOIN tbpo_detail_po pp ON pp.kd_po = l.kd_po AND pp.no_po = l.no_po AND pp.kd_barang = d.kd_barang
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                LEFT JOIN tbpo_barang m ON m.kode_barang = d.kd_barang
                LEFT JOIN tberp_stock_batch sb ON sb.kd_barang = d.kd_barang
                    AND sb.gudang_id = CAST(l.gudang_id AS CHAR)
                    AND COALESCE(sb.no_lot, '') = COALESCE(d.no_lot, '')
                    AND (sb.expired_date <=> d.expired_date)
                WHERE d.id_lpb = ?
                ORDER BY d.id_detail_lpb ASC";
        return $this->db->query($sql, [(int)$idLpb])->result_array();
    }

    public function create_draft($payload, $details, $user)
    {
        $this->ensure_schema();
        $idLpb = (int)($payload['id_lpb'] ?? 0);
        $tanggal = $this->normalize_date($payload['tanggal_retur'] ?? date('Y-m-d'));
        $jenis = strtoupper(trim((string)($payload['jenis_penyelesaian'] ?? 'POTONG_HUTANG')));
        $alasan = trim((string)($payload['alasan_retur'] ?? ''));

        if ($idLpb <= 0 || $tanggal === '' || empty($details)) {
            return $this->fail('LPB, tanggal retur, dan detail barang wajib diisi.', ['RETUR_DATA_REQUIRED']);
        }

        $normalizedDetails = [];
        foreach ($details as $detail) {
            $idDetailLpb = (int)($detail['id_detail_lpb'] ?? 0);
            $qtyRetur = (float)($detail['qty_retur'] ?? 0);
            if ($idDetailLpb <= 0 || $qtyRetur <= 0) {
                continue;
            }
            if (!isset($normalizedDetails[$idDetailLpb])) {
                $normalizedDetails[$idDetailLpb] = [
                    'id_detail_lpb' => $idDetailLpb,
                    'qty_retur' => 0,
                    'alasan_retur' => trim((string)($detail['alasan_retur'] ?? '')),
                ];
            }
            $normalizedDetails[$idDetailLpb]['qty_retur'] += $qtyRetur;
            if ($normalizedDetails[$idDetailLpb]['alasan_retur'] === '') {
                $normalizedDetails[$idDetailLpb]['alasan_retur'] = trim((string)($detail['alasan_retur'] ?? ''));
            }
        }

        if (empty($normalizedDetails)) {
            return $this->fail('Minimal satu qty retur harus lebih dari 0.', ['RETUR_DETAIL_EMPTY']);
        }

        $lpb = $this->lpb_header($idLpb);
        if (!$lpb || trim((string)$lpb['nomor_lpb']) === '') {
            return $this->fail('LPB final dengan nomor LPB tidak ditemukan.', ['LPB_NUMBER_REQUIRED']);
        }

        $this->db->trans_begin();
        $noRetur = $this->generate_number();
        $header = [
            'no_retur_pembelian' => $noRetur,
            'id_lpb' => $idLpb,
            'kd_po' => $lpb['kd_po'],
            'no_po' => $lpb['no_po'],
            'kd_supplier' => $lpb['kd_suplier'],
            'tanggal_retur' => $tanggal,
            'gudang_id' => (string)$lpb['gudang_id'],
            'status' => self::STATUS_DRAFT,
            'jenis_penyelesaian' => $jenis !== '' ? $jenis : 'POTONG_HUTANG',
            'alasan_retur' => $alasan,
            'created_by' => $user,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tb_retur_pembelian', $header);
        $idRetur = (int)$this->db->insert_id();

        $totals = ['dpp' => '0.0000', 'ppn' => '0.0000', 'grand' => '0.0000'];
        foreach ($normalizedDetails as $detail) {
            $valid = $this->validate_line((int)($detail['id_detail_lpb'] ?? 0), (float)($detail['qty_retur'] ?? 0), $idLpb);
            if (!$valid['success']) {
                $this->db->trans_rollback();
                return $valid;
            }

            $row = $valid['data'];
            $line = $this->calculate_line($row, (float)$detail['qty_retur'], trim((string)($detail['alasan_retur'] ?? $alasan)));
            $line['id_retur_pembelian'] = $idRetur;
            $this->db->insert('tb_retur_pembelian_detail', $line);

            $totals['dpp'] = bcadd($totals['dpp'], $this->money($line['dpp']), 4);
            $totals['ppn'] = bcadd($totals['ppn'], $this->money($line['ppn']), 4);
            $totals['grand'] = bcadd($totals['grand'], $this->money($line['total']), 4);
        }

        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', [
            'total_dpp' => $totals['dpp'],
            'total_ppn' => $totals['ppn'],
            'grand_total' => $totals['grand'],
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $after = $this->header($idRetur);
        $this->write_log($idRetur, 'CREATE_DRAFT', null, self::STATUS_DRAFT, null, $after, 'Draft retur pembelian dibuat.', $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Draft retur pembelian gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Draft retur pembelian berhasil dibuat.', ['id_retur_pembelian' => $idRetur, 'no_retur_pembelian' => $noRetur]);
    }

    public function submit($idRetur, $user)
    {
        return $this->change_status((int)$idRetur, [self::STATUS_DRAFT], self::STATUS_SUBMITTED, 'SUBMIT', [
            'submitted_by' => $user,
            'submitted_at' => date('Y-m-d H:i:s'),
        ], 'Draft retur pembelian diajukan.', $user);
    }

    public function verify_purchasing($idRetur, $catatan, $user)
    {
        return $this->change_status((int)$idRetur, [self::STATUS_SUBMITTED, self::STATUS_DRAFT], self::STATUS_PURCHASING_VERIFIED, 'VERIFY_PURCHASING', [
            'catatan_purchasing' => trim((string)$catatan),
            'purchasing_verified_by' => $user,
            'purchasing_verified_at' => date('Y-m-d H:i:s'),
        ], 'Purchasing memverifikasi harga, supplier, alasan retur, dan penyelesaian.', $user);
    }

    public function verify_accounting($idRetur, $catatan, $user)
    {
        $result = $this->change_status((int)$idRetur, [self::STATUS_PURCHASING_VERIFIED], self::STATUS_ACCOUNTING_VERIFIED, 'VERIFY_ACCOUNTING', [
            'catatan_accounting' => trim((string)$catatan),
            'accounting_verified_by' => $user,
            'accounting_verified_at' => date('Y-m-d H:i:s'),
        ], 'Accounting memverifikasi dampak hutang dan PPN.', $user);

        if ($result['success']) {
            $after = $this->header((int)$idRetur);
            $this->write_log((int)$idRetur, 'APPROVE', self::STATUS_ACCOUNTING_VERIFIED, self::STATUS_ACCOUNTING_VERIFIED, $after, $after, 'Retur pembelian disetujui untuk posting.', $user);
        }

        return $result;
    }

    public function post($idRetur, $user)
    {
        $this->ensure_schema();
        $this->load->library('Accounting_service');
        $this->db->trans_begin();

        $header = $this->locked_header((int)$idRetur);
        if (!$header) {
            $this->db->trans_rollback();
            return $this->fail('Retur pembelian tidak ditemukan.', ['RETUR_NOT_FOUND']);
        }
        if (!in_array($header['status'], [self::STATUS_ACCOUNTING_VERIFIED, self::STATUS_POSTING_EXCEPTION], true)) {
            $this->db->trans_rollback();
            return $this->fail('Retur hanya bisa diposting setelah verifikasi Accounting.', ['RETUR_NOT_APPROVED']);
        }
        if ($header['jenis_penyelesaian'] !== 'POTONG_HUTANG') {
            return $this->posting_exception($header, 'Jenis penyelesaian retur belum memiliki mapping akun final selain POTONG_HUTANG.', ['PURCHASE_RETURN_SETTLEMENT_UNMAPPED'], $user);
        }

        $details = $this->details((int)$idRetur);
        if (empty($details)) {
            $this->db->trans_rollback();
            return $this->fail('Detail retur pembelian kosong.', ['RETUR_DETAIL_EMPTY']);
        }

        foreach ($details as $detail) {
            $kelompok = trim((string)$detail['kelompok_dagang']);
            if (!in_array($kelompok, ['2', '3'], true)) {
                return $this->posting_exception($header, 'Kelompok dagang ' . ($kelompok !== '' ? $kelompok : '-') . ' belum aman untuk posting otomatis retur pembelian.', ['PURCHASE_RETURN_GROUP_UNSUPPORTED'], $user);
            }
            $valid = $this->validate_line((int)$detail['id_detail_lpb'], (float)$detail['qty_retur'], (int)$header['id_lpb'], (int)$detail['id_detail_retur_pembelian']);
            if (!$valid['success']) {
                $this->db->trans_rollback();
                return $valid;
            }
        }

        foreach ($details as $detail) {
            $this->decrease_stock($header, $detail);
        }

        $journal = $this->post_purchase_return_journal($header, $details, $user);
        if (!$journal['success']) {
            $this->db->trans_rollback();
            return $journal;
        }

        $before = $header;
        $this->db->where('id_retur_pembelian', (int)$idRetur)->update('tb_retur_pembelian', [
            'status' => self::STATUS_POSTED,
            'id_jurnal' => (int)$journal['data']['id_jurnal'],
            'posted_by' => $user,
            'posted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $after = $this->header((int)$idRetur);
        $this->write_log((int)$idRetur, 'POST', $before['status'], self::STATUS_POSTED, $before, $after, 'Retur pembelian diposting ke stock ledger RBELI dan jurnal PURCHASE_RETURN.', $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Posting retur pembelian gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Retur pembelian berhasil diposting.', ['id_jurnal' => (int)$journal['data']['id_jurnal']]);
    }

    public function void_posted($idRetur, $reason, $user)
    {
        $reason = trim((string)$reason);
        if ($reason === '') {
            return $this->fail('Alasan void/reversal wajib diisi.', ['VOID_REASON_REQUIRED']);
        }

        $this->ensure_schema();
        $this->load->library('Accounting_service');
        $this->db->trans_begin();

        $header = $this->locked_header((int)$idRetur);
        if (!$header || $header['status'] !== self::STATUS_POSTED) {
            $this->db->trans_rollback();
            return $this->fail('Hanya retur POSTED yang dapat di-void.', ['RETUR_NOT_POSTED']);
        }

        $idJurnalReversal = null;
        if ((int)$header['id_jurnal'] > 0) {
            $reversal = $this->accounting_service->reversal_journal((int)$header['id_jurnal'], 'Void retur pembelian ' . $header['no_retur_pembelian'] . ': ' . $reason, $user);
            if (!$reversal['success']) {
                $this->db->trans_rollback();
                return $reversal;
            }
            $idJurnalReversal = (int)$reversal['data']['id_jurnal'];
            $this->write_log((int)$idRetur, 'REVERSE', self::STATUS_POSTED, self::STATUS_POSTED, $header, $header, 'Jurnal reversal dibuat untuk void retur pembelian.', $user);
        }

        foreach ($this->details((int)$idRetur) as $detail) {
            $this->reverse_stock($header, $detail);
        }

        $this->db->where('id_retur_pembelian', (int)$idRetur)->update('tb_retur_pembelian', [
            'status' => self::STATUS_VOID,
            'id_jurnal_reversal' => $idJurnalReversal,
            'reversed_by' => $user,
            'reversed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $after = $this->header((int)$idRetur);
        $this->write_log((int)$idRetur, 'VOID', self::STATUS_POSTED, self::STATUS_VOID, $header, $after, $reason, $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Void retur pembelian gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Retur pembelian berhasil di-void dan stok telah dikembalikan.', ['id_jurnal_reversal' => $idJurnalReversal]);
    }

    private function post_purchase_return_journal($header, $details, $user)
    {
        $idSupplier = $this->supplier_id($header['kd_supplier']);
        $idGudang = (int)$header['gudang_id'];
        $amountBkp = '0.0000';
        $vatBkp = '0.0000';
        $amountBkps = '0.0000';

        foreach ($details as $detail) {
            if ((string)$detail['kelompok_dagang'] === '2') {
                $amountBkp = bcadd($amountBkp, $this->money($detail['dpp']), 4);
                $vatBkp = bcadd($vatBkp, $this->money($detail['ppn']), 4);
            } elseif ((string)$detail['kelompok_dagang'] === '3') {
                $amountBkps = bcadd($amountBkps, $this->money($detail['dpp']), 4);
            }
        }

        $lines = [];
        if (bccomp($amountBkp, '0.0000', 4) === 1) {
            $ruleCode = 'RBELI-PH-BKP';
            $lines[] = $this->journal_line('13013', $ruleCode . ' - Piutang Non Dagang Retur Pembelian Belum Dipotong', bcadd($amountBkp, $vatBkp, 4), '0.0000', $idSupplier, $idGudang, $header['no_retur_pembelian']);
            $lines[] = $this->journal_line('14010', $ruleCode . ' - Persediaan # 1', '0.0000', $amountBkp, $idSupplier, $idGudang, $header['no_retur_pembelian']);
            $lines[] = $this->journal_line('13017', $ruleCode . ' - PPN Masukan / PPN M Ymh Diterima', '0.0000', $vatBkp, $idSupplier, $idGudang, $header['no_retur_pembelian']);
        }
        if (bccomp($amountBkps, '0.0000', 4) === 1) {
            $ruleCode = 'RBELI-PH-BKPS';
            $lines[] = $this->journal_line('13013', $ruleCode . ' - Piutang Non Dagang Retur Pembelian Belum Dipotong', $amountBkps, '0.0000', $idSupplier, $idGudang, $header['no_retur_pembelian']);
            $lines[] = $this->journal_line('14011', $ruleCode . ' - Persediaan Brg Dagangan BKPS', '0.0000', $amountBkps, $idSupplier, $idGudang, $header['no_retur_pembelian']);
        }

        foreach ($lines as $line) {
            if ((int)$line['id_akun'] <= 0) {
                return $this->fail('Akun retur pembelian belum valid di COA.', ['PURCHASE_RETURN_ACCOUNT_NOT_FOUND']);
            }
        }

        return $this->accounting_service->post_retur([
            'retur_type' => 'PURCHASE_RETURN',
            'journal_type' => 'PJ',
            'tanggal_transaksi' => $header['tanggal_retur'],
            'keterangan' => 'Retur pembelian ' . $header['no_retur_pembelian'],
            'source_module' => 'LOGISTIK',
            'source_type' => 'RETUR_PEMBELIAN',
            'source_id' => (string)$header['id_retur_pembelian'],
            'source_no' => $header['no_retur_pembelian'],
            'idempotency_key' => 'PURCHASE_RETURN-' . $header['id_retur_pembelian'],
            'scope_type' => 'WAREHOUSE',
            'scope_key' => (string)$header['gudang_id'],
            'amount' => bcadd($amountBkp, $amountBkps, 4),
            'tax' => $vatBkp,
            'cogs' => '0.0000',
            'lines' => $lines,
        ], $user);
    }

    private function posting_exception($header, $message, $errors, $user)
    {
        $this->load->library('Accounting_service');
        $this->accounting_service->capture_posting_exception('PURCHASE_RETURN', [
            'source_module' => 'LOGISTIK',
            'source_type' => 'RETUR_PEMBELIAN',
            'source_id' => (string)$header['id_retur_pembelian'],
            'source_no' => $header['no_retur_pembelian'],
            'idempotency_key' => 'PURCHASE_RETURN-' . $header['id_retur_pembelian'],
            'amount' => $header['total_dpp'],
            'tax' => $header['total_ppn'],
        ], $message, $errors);

        $this->db->where('id_retur_pembelian', (int)$header['id_retur_pembelian'])->update('tb_retur_pembelian', [
            'status' => self::STATUS_POSTING_EXCEPTION,
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $after = $this->header((int)$header['id_retur_pembelian']);
        $this->write_log((int)$header['id_retur_pembelian'], 'POST', $header['status'], self::STATUS_POSTING_EXCEPTION, $header, $after, $message, $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Posting exception gagal dicatat.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->fail($message, $errors);
    }

    private function validate_line($idDetailLpb, $qtyRetur, $idLpb, $excludeReturDetailId = 0)
    {
        if ($idDetailLpb <= 0 || $qtyRetur <= 0) {
            return $this->fail('Qty retur harus lebih dari 0.', ['RETUR_QTY_INVALID']);
        }

        $sql = "SELECT d.*, l.nomor_lpb, l.gudang_id, l.kd_po, l.no_po,
                    COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan_final,
                    COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') AS kelompok_dagang,
                    COALESCE(lb.qty, 0) AS qty_batch,
                    COALESCE(sb.qty_on_hand, 0) AS qty_on_hand,
                    COALESCE((
                        SELECT SUM(rd.qty_retur)
                        FROM tb_retur_pembelian_detail rd
                        INNER JOIN tb_retur_pembelian rh ON rh.id_retur_pembelian = rd.id_retur_pembelian
                        WHERE rd.id_detail_lpb = d.id_detail_lpb
                            AND rh.status NOT IN ('VOID')
                            AND rd.id_detail_retur_pembelian <> ?
                    ), 0) AS qty_retur_sebelumnya
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                LEFT JOIN tb_lpb_batch lb ON lb.id_detail_lpb = d.id_detail_lpb
                    AND COALESCE(lb.no_lot, '') = COALESCE(d.no_lot, '')
                    AND (lb.expired_date <=> d.expired_date)
                LEFT JOIN tbpo_detail_po pp ON pp.kd_po = l.kd_po AND pp.no_po = l.no_po AND pp.kd_barang = d.kd_barang
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                LEFT JOIN tberp_stock_batch sb ON sb.kd_barang = d.kd_barang
                    AND sb.gudang_id = CAST(l.gudang_id AS CHAR)
                    AND COALESCE(sb.no_lot, '') = COALESCE(d.no_lot, '')
                    AND (sb.expired_date <=> d.expired_date)
                WHERE d.id_detail_lpb = ? AND d.id_lpb = ?
                LIMIT 1";
        $row = $this->db->query($sql, [(int)$excludeReturDetailId, (int)$idDetailLpb, (int)$idLpb])->row_array();

        if (!$row || trim((string)$row['nomor_lpb']) === '') {
            return $this->fail('LPB/detail final tidak valid atau nomor LPB belum tersedia.', ['LPB_DETAIL_INVALID']);
        }
        if ((float)$row['qty_batch'] <= 0) {
            return $this->fail('Lot/expired tidak cocok dengan tb_lpb_batch.', ['LPB_BATCH_NOT_FOUND']);
        }

        $availableFromLpb = (float)$row['qty_diterima'] - (float)$row['qty_retur_sebelumnya'];
        if ($qtyRetur > $availableFromLpb + 0.0001) {
            return $this->fail('Qty retur melebihi qty diterima dikurangi retur sebelumnya.', ['RETUR_QTY_EXCEEDS_LPB']);
        }
        if ($qtyRetur > (float)$row['qty_on_hand'] + 0.0001) {
            return $this->fail('Qty stok fisik gudang tidak cukup untuk retur.', ['STOCK_NOT_ENOUGH']);
        }
        if ((float)$row['harga_satuan_final'] <= 0) {
            return $this->fail('Harga satuan LPB belum valid untuk retur.', ['LPB_PRICE_INVALID']);
        }

        return $this->ok('Valid.', $row);
    }

    private function calculate_line($row, $qty, $alasan)
    {
        $harga = $this->money($row['harga_satuan_final']);
        $dpp = $this->money(bcmul((string)$qty, $harga, 4));
        $ppn = ((string)$row['kelompok_dagang'] === '2') ? $this->money(bcdiv(bcmul($dpp, '11', 4), '100', 4)) : '0.0000';

        return [
            'id_detail_lpb' => (int)$row['id_detail_lpb'],
            'kd_barang' => $row['kd_barang'],
            'no_lot' => $row['no_lot'],
            'expired_date' => $row['expired_date'],
            'qty_retur' => $qty,
            'harga_satuan' => $harga,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'total' => bcadd($dpp, $ppn, 4),
            'kelompok_dagang' => (string)$row['kelompok_dagang'],
            'alasan_retur' => $alasan,
        ];
    }

    private function apply_batch_where($kdBarang, $gudangId, $noLot, $expiredDate)
    {
        $this->db->where('kd_barang', $kdBarang);
        $this->db->where('gudang_id', (string)$gudangId);
        $lot = trim((string)$noLot);
        if ($lot === '' || $lot === '-') {
            $this->db->group_start()
                ->where('no_lot', '-')
                ->or_where('no_lot', '')
                ->or_where('no_lot IS NULL', null, false)
                ->group_end();
        } else {
            $this->db->where('no_lot', $lot);
        }

        if ($expiredDate !== null && $expiredDate !== '' && $expiredDate !== '-' && $expiredDate !== '0000-00-00') {
            $this->db->where('expired_date', $expiredDate);
        } else {
            $this->db->group_start()
                ->where('expired_date IS NULL', null, false)
                ->or_where("CAST(expired_date AS CHAR) IN ('', '0000-00-00')", null, false)
                ->group_end();
        }
    }

    private function decrease_stock($header, $detail)
    {
        $this->apply_batch_where($detail['kd_barang'], $header['gudang_id'], $detail['no_lot'], $detail['expired_date']);
        $this->db->set('qty_on_hand', 'qty_on_hand - ' . (float)$detail['qty_retur'], false);
        $this->db->set('update_at', date('Y-m-d H:i:s'));
        $this->db->update('tberp_stock_batch');

        $this->db->insert('tberp_stock_ledger', [
            'kd_barang' => $detail['kd_barang'],
            'gudang_id' => (string)$header['gudang_id'],
            'no_lot' => $detail['no_lot'],
            'expired_date' => $detail['expired_date'],
            'qty' => (float)$detail['qty_retur'],
            'tipe' => 'RBELI',
            'ref_no' => $header['no_retur_pembelian'],
            'ref_type' => 'RETUR_PEMBELIAN',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function reverse_stock($header, $detail)
    {
        $this->apply_batch_where($detail['kd_barang'], $header['gudang_id'], $detail['no_lot'], $detail['expired_date']);
        $this->db->set('qty_on_hand', 'qty_on_hand + ' . (float)$detail['qty_retur'], false);
        $this->db->set('update_at', date('Y-m-d H:i:s'));
        $this->db->update('tberp_stock_batch');

        $this->db->insert('tberp_stock_ledger', [
            'kd_barang' => $detail['kd_barang'],
            'gudang_id' => (string)$header['gudang_id'],
            'no_lot' => $detail['no_lot'],
            'expired_date' => $detail['expired_date'],
            'qty' => 0 - (float)$detail['qty_retur'],
            'tipe' => 'RBELI',
            'ref_no' => $header['no_retur_pembelian'],
            'ref_type' => 'RETUR_PEMBELIAN_VOID',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function change_status($idRetur, $allowed, $status, $action, $updates, $note, $user)
    {
        $this->ensure_schema();
        $this->db->trans_begin();
        $before = $this->locked_header($idRetur);
        if (!$before || !in_array($before['status'], $allowed, true)) {
            $this->db->trans_rollback();
            return $this->fail('Status retur pembelian tidak valid untuk aksi ini.', ['INVALID_RETUR_STATUS']);
        }

        $updates['status'] = $status;
        $updates['updated_by'] = $user;
        $updates['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', $updates);
        $after = $this->header($idRetur);
        $this->write_log($idRetur, $action, $before['status'], $status, $before, $after, $note, $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Perubahan status retur gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Status retur pembelian diperbarui.', ['status' => $status]);
    }

    private function lpb_header($idLpb)
    {
        $sql = "SELECT l.*, p.kd_suplier
                FROM tb_lpb l
                LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
                WHERE l.id_lpb = ?
                LIMIT 1";
        return $this->db->query($sql, [(int)$idLpb])->row_array();
    }

    private function header($idRetur)
    {
        return $this->db->where('id_retur_pembelian', (int)$idRetur)->get('tb_retur_pembelian')->row_array();
    }

    private function locked_header($idRetur)
    {
        return $this->db->query('SELECT * FROM tb_retur_pembelian WHERE id_retur_pembelian = ? FOR UPDATE', [(int)$idRetur])->row_array();
    }

    private function details($idRetur)
    {
        return $this->db->where('id_retur_pembelian', (int)$idRetur)->order_by('id_detail_retur_pembelian', 'ASC')->get('tb_retur_pembelian_detail')->result_array();
    }

    private function generate_number()
    {
        $prefix = 'RBELI-' . date('Ymd') . '-';
        $row = $this->db->query(
            "SELECT no_retur_pembelian FROM tb_retur_pembelian WHERE no_retur_pembelian LIKE ? ORDER BY no_retur_pembelian DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        )->row_array();
        $next = $row ? ((int)substr($row['no_retur_pembelian'], -4) + 1) : 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    private function supplier_id($kdSupplier)
    {
        $row = $this->db->select('id_suplier')->where('kd_suplier', $kdSupplier)->get('tbpo_suplier')->row_array();
        return $row ? (int)$row['id_suplier'] : 0;
    }

    private function journal_line($kodeAkun, $label, $debit, $kredit, $idSupplier, $idGudang, $nomorDokumen)
    {
        return [
            'id_akun' => $this->account_id($kodeAkun),
            'keterangan' => $label,
            'debit' => $this->money($debit),
            'kredit' => $this->money($kredit),
            'id_customer' => 0,
            'id_supplier' => $idSupplier,
            'id_barang' => 0,
            'id_gudang' => $idGudang,
            'id_departemen' => 0,
            'tanggal_jatuh_tempo' => '',
            'nomor_dokumen' => $nomorDokumen,
        ];
    }

    private function account_id($kodeAkun)
    {
        $row = $this->db->select('id_akun')->where('kode_akun', $kodeAkun)->get('tbkeu_akun')->row_array();
        return $row ? (int)$row['id_akun'] : 0;
    }

    private function write_log($idRetur, $action, $beforeStatus, $afterStatus, $before, $after, $note, $user)
    {
        $this->db->insert('tb_retur_pembelian_log', [
            'id_retur_pembelian' => $idRetur,
            'no_retur_pembelian' => is_array($after) ? ($after['no_retur_pembelian'] ?? null) : (is_array($before) ? ($before['no_retur_pembelian'] ?? null) : null),
            'action_type' => $action,
            'status_before' => $beforeStatus,
            'status_after' => $afterStatus,
            'data_before' => $before !== null ? json_encode($before) : null,
            'data_after' => $after !== null ? json_encode($after) : null,
            'keterangan' => $note,
            'dilakukan_oleh' => $user,
            'dilakukan_pada' => date('Y-m-d H:i:s'),
        ]);
    }

    private function normalize_date($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }
        $time = strtotime($raw);
        return $time ? date('Y-m-d', $time) : '';
    }

    private function money($value)
    {
        $value = str_replace(',', '.', trim((string)$value));
        if ($value === '' || !preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            return '0.0000';
        }
        return bcadd($value, '0', 4);
    }

    private function ok($message, $data = [])
    {
        return ['success' => true, 'message' => $message, 'data' => $data, 'errors' => []];
    }

    private function fail($message, $errors = [])
    {
        return ['success' => false, 'message' => $message, 'data' => null, 'errors' => $errors];
    }
}
