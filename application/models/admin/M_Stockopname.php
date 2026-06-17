<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Stockopname extends CI_Model
{
    private $masterTable = 'stockopname_master_item';
    private $opnameTable = 'stockopname_opname';
    private $manualOpnameTable = 'stockopname_opname_manual';
    private $manualMasterTable = 'stockopname_master_manual_item';
    private $masterBarangAllTable = 'tb_master_barang_all';

    public function ensure_master_code_columns()
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return false;
        }

        $timestampField = $this->db->field_exists('created_at', $this->masterTable) ? 'created_at' : ($this->db->field_exists('create_at', $this->masterTable) ? 'create_at' : 'kode_barang');
        if (!$this->db->field_exists('qrcode', $this->masterTable)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD `qrcode` VARCHAR(255) NULL DEFAULT NULL AFTER `{$timestampField}`");
        }
        if (!$this->db->field_exists('barcode', $this->masterTable)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD `barcode` VARCHAR(255) NULL DEFAULT NULL AFTER `qrcode`");
        }

        $indexes = $this->db->query("SHOW INDEX FROM {$this->masterTable}")->result_array();
        $indexNames = array_column($indexes, 'Key_name');
        $indexedColumns = array_column($indexes, 'Column_name');
        $qrcodeIndex = 'idx_' . $this->masterTable . '_qrcode';
        $barcodeIndex = 'idx_' . $this->masterTable . '_barcode';
        if (!in_array($qrcodeIndex, $indexNames, true) && !in_array('qrcode', $indexedColumns, true)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD KEY `{$qrcodeIndex}` (`qrcode`)");
        }
        if (!in_array($barcodeIndex, $indexNames, true) && !in_array('barcode', $indexedColumns, true)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD KEY `{$barcodeIndex}` (`barcode`)");
        }

        return true;
    }

    public function ensure_qrcode_columns()
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [
                'success' => false,
                'message' => 'Tabel stockopname_master_item belum tersedia.',
                'missing_table' => true,
            ];
        }

        foreach (['id', 'kode_barang'] as $field) {
            if (!$this->db->field_exists($field, $this->masterTable)) {
                return [
                    'success' => false,
                    'message' => 'Kolom wajib ' . $field . ' tidak tersedia di stockopname_master_item.',
                    'missing_column' => $field,
                ];
            }
        }

        $this->ensure_master_code_columns();

        $columns = [
            'qrcode_value' => "ALTER TABLE {$this->masterTable} ADD `qrcode_value` VARCHAR(255) NULL DEFAULT NULL AFTER `barcode`",
            'qrcode_file' => "ALTER TABLE {$this->masterTable} ADD `qrcode_file` VARCHAR(255) NULL DEFAULT NULL AFTER `qrcode_value`",
            'qrcode_status' => "ALTER TABLE {$this->masterTable} ADD `qrcode_status` ENUM('PENDING','PROCESS','DONE','FAILED') NOT NULL DEFAULT 'PENDING' AFTER `qrcode_file`",
            'qrcode_retry_flag' => "ALTER TABLE {$this->masterTable} ADD `qrcode_retry_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `qrcode_status`",
            'qrcode_attempt_count' => "ALTER TABLE {$this->masterTable} ADD `qrcode_attempt_count` INT NOT NULL DEFAULT 0 AFTER `qrcode_retry_flag`",
            'qrcode_error_message' => "ALTER TABLE {$this->masterTable} ADD `qrcode_error_message` TEXT NULL AFTER `qrcode_attempt_count`",
            'qrcode_generated_at' => "ALTER TABLE {$this->masterTable} ADD `qrcode_generated_at` DATETIME NULL DEFAULT NULL AFTER `qrcode_error_message`",
            'qrcode_updated_at' => "ALTER TABLE {$this->masterTable} ADD `qrcode_updated_at` DATETIME NULL DEFAULT NULL AFTER `qrcode_generated_at`",
        ];

        foreach ($columns as $field => $sql) {
            if (!$this->db->field_exists($field, $this->masterTable)) {
                $this->db->query($sql);
            }
        }

        $indexes = $this->db->query("SHOW INDEX FROM {$this->masterTable}")->result_array();
        $indexNames = array_column($indexes, 'Key_name');
        if (!in_array('idx_stockopname_qrcode_status', $indexNames, true)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD KEY `idx_stockopname_qrcode_status` (`qrcode_status`)");
        }
        if (!in_array('idx_stockopname_qrcode_retry', $indexNames, true)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD KEY `idx_stockopname_qrcode_retry` (`qrcode_status`, `qrcode_retry_flag`)");
        }

        return [
            'success' => true,
            'message' => 'Struktur QRCode stockopname siap.',
        ];
    }

    public function ensure_manual_tables()
    {
        if (!$this->db->table_exists($this->manualMasterTable)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->manualMasterTable}` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `source_id` INT(11) NOT NULL,
                    `kode_barang` VARCHAR(50) NOT NULL,
                    `nama_barang` TEXT NOT NULL,
                    `expired_date` DATE NOT NULL,
                    `no_lot` VARCHAR(100) NOT NULL,
                    `dimensi` INT(12) NOT NULL DEFAULT 0,
                    `qty` INT(12) NOT NULL DEFAULT 0,
                    `qty_pcs` INT(12) NOT NULL DEFAULT 0,
                    `qty_box` INT(12) NOT NULL DEFAULT 0,
                    `status` ENUM('PENDING','APPROVED','REJECTED','ADDED','DONE','Manual Input','Request Master Item') NOT NULL DEFAULT 'PENDING',
                    `requested_by` VARCHAR(100) NOT NULL,
                    `requested_at` DATETIME NOT NULL,
                    `wilayah` INT(2) NOT NULL DEFAULT 0,
                    `tim_opname` INT(2) NOT NULL DEFAULT 0,
                    `reviewed_by` VARCHAR(100) NULL DEFAULT NULL,
                    `reviewed_at` DATETIME NULL DEFAULT NULL,
                    `review_note` TEXT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_manual_master_source` (`source_id`),
                    KEY `idx_manual_master_barang` (`kode_barang`),
                    KEY `idx_manual_master_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if (!$this->db->table_exists($this->manualOpnameTable)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->manualOpnameTable}` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `manual_master_id` INT(11) NOT NULL,
                    `source_id` INT(11) NOT NULL,
                    `kode_barang` VARCHAR(50) NOT NULL,
                    `nama_barang` TEXT NOT NULL,
                    `expired_date` DATE NOT NULL,
                    `no_lot` VARCHAR(100) NOT NULL,
                    `qty` INT(12) NOT NULL DEFAULT 0,
                    `qty_pcs` INT(12) NOT NULL DEFAULT 0,
                    `qty_box` INT(12) NOT NULL DEFAULT 0,
                    `input_by` VARCHAR(100) NOT NULL,
                    `input_at` DATETIME NOT NULL,
                    `wilayah` INT(2) NOT NULL DEFAULT 0,
                    `tim_opname` INT(2) NOT NULL DEFAULT 0,
                    `input_source` VARCHAR(30) NOT NULL DEFAULT 'manual',
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_manual_opname_master` (`manual_master_id`),
                    KEY `idx_manual_opname_source` (`source_id`),
                    KEY `idx_manual_opname_barang` (`kode_barang`),
                    KEY `idx_manual_opname_source_type` (`input_source`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        if ($this->db->table_exists($this->manualOpnameTable) && !$this->db->field_exists('input_source', $this->manualOpnameTable)) {
            $this->db->query("ALTER TABLE {$this->manualOpnameTable} ADD `input_source` VARCHAR(30) NOT NULL DEFAULT 'manual' AFTER `tim_opname`");
        } elseif ($this->db->table_exists($this->manualOpnameTable)) {
            foreach ($this->db->field_data($this->manualOpnameTable) as $field) {
                if ($field->name === 'input_source' && stripos((string)$field->type, 'enum') !== false) {
                    $this->db->query("ALTER TABLE {$this->manualOpnameTable} MODIFY `input_source` VARCHAR(30) NOT NULL DEFAULT 'manual'");
                    break;
                }
            }
        }
        if ($this->db->table_exists($this->manualMasterTable)) {
            $manualMasterColumns = [
                'qty' => "ALTER TABLE {$this->manualMasterTable} ADD `qty` INT(12) NOT NULL DEFAULT 0 AFTER `dimensi`",
                'qty_pcs' => "ALTER TABLE {$this->manualMasterTable} ADD `qty_pcs` INT(12) NOT NULL DEFAULT 0 AFTER `qty`",
                'qty_box' => "ALTER TABLE {$this->manualMasterTable} ADD `qty_box` INT(12) NOT NULL DEFAULT 0 AFTER `qty_pcs`",
                'wilayah' => "ALTER TABLE {$this->manualMasterTable} ADD `wilayah` INT(2) NOT NULL DEFAULT 0 AFTER `requested_at`",
                'tim_opname' => "ALTER TABLE {$this->manualMasterTable} ADD `tim_opname` INT(2) NOT NULL DEFAULT 0 AFTER `wilayah`",
            ];
            foreach ($manualMasterColumns as $column => $sql) {
                if (!$this->db->field_exists($column, $this->manualMasterTable)) {
                    $this->db->query($sql);
                }
            }
            foreach ($this->db->field_data($this->manualMasterTable) as $field) {
                if ($field->name === 'status' && stripos((string)$field->type, 'enum') !== false) {
                    $this->db->query("ALTER TABLE {$this->manualMasterTable} MODIFY `status` ENUM('PENDING','APPROVED','REJECTED','ADDED','DONE','Manual Input','Request Master Item') NOT NULL DEFAULT 'PENDING'");
                    break;
                }
            }
        }

        return $this->db->table_exists($this->manualMasterTable) && $this->db->table_exists($this->manualOpnameTable);
    }

    private function ensure_opname_input_source_column()
    {
        if (!$this->db->table_exists($this->opnameTable)) {
            return false;
        }

        if (!$this->db->field_exists('input_source', $this->opnameTable)) {
            $after = $this->db->field_exists('scan_code', $this->opnameTable) ? 'scan_code' : 'tim_opname';
            $this->db->query("ALTER TABLE {$this->opnameTable} ADD `input_source` VARCHAR(50) NULL DEFAULT NULL AFTER `{$after}`");
        }

        return true;
    }

    private function ensure_master_input_status_columns()
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return false;
        }

        if (!$this->db->field_exists('input_source', $this->masterTable)) {
            $after = $this->db->field_exists('barcode', $this->masterTable) ? 'barcode' : 'no_lot';
            $this->db->query("ALTER TABLE {$this->masterTable} ADD `input_source` VARCHAR(50) NULL DEFAULT NULL AFTER `{$after}`");
        }
        if (!$this->db->field_exists('request_status', $this->masterTable)) {
            $after = $this->db->field_exists('input_source', $this->masterTable) ? 'input_source' : 'no_lot';
            $this->db->query("ALTER TABLE {$this->masterTable} ADD `request_status` VARCHAR(20) NULL DEFAULT NULL AFTER `{$after}`");
        }

        return true;
    }

    private function ready()
    {
        return $this->db->table_exists($this->masterTable) && $this->db->table_exists($this->opnameTable);
    }

    private function exp_key($field)
    {
        return "COALESCE(DATE_FORMAT({$field}, '%Y-%m-%d'), DATE_FORMAT(STR_TO_DATE({$field}, '%d/%m/%Y'), '%Y-%m-%d'), DATE_FORMAT(STR_TO_DATE({$field}, '%Y-%m-%d'), '%Y-%m-%d'))";
    }

    private function lot_key($field)
    {
        return "CASE WHEN TRIM(COALESCE({$field}, '')) IN ('', '-', '0') THEN '-' ELSE TRIM({$field}) END";
    }

    private function master_positive_sql($alias = '')
    {
        $prefix = $alias !== '' ? $alias . '.' : '';
        return "COALESCE({$prefix}qty, 0) > 0";
    }

    private function opname_subquery()
    {
        $expKey = $this->exp_key('expired_date');
        $lotKey = $this->lot_key('no_lot');

        return "
            SELECT
                kode_barang,
                {$expKey} AS exp_key,
                {$lotKey} AS lot_key,
                SUM(qty) AS qty_fisik,
                SUM(qty_box) AS qty_box,
                SUM(qty_pcs) AS qty_pcs,
                GROUP_CONCAT(DISTINCT input_by ORDER BY input_by SEPARATOR ', ') AS inputers,
                GROUP_CONCAT(DISTINCT wilayah ORDER BY wilayah SEPARATOR ', ') AS wilayah,
                MAX(created_at) AS last_input
            FROM {$this->opnameTable}
            GROUP BY kode_barang, exp_key, lot_key
        ";
    }

    private function base_query()
    {
        $masterExpKey = $this->exp_key('m.expired_date');
        $masterLotKey = $this->lot_key('m.no_lot');
        $masterPositiveWhere = $this->master_positive_sql('m');
        $opname = $this->opname_subquery();

        return "
            SELECT
                m.id,
                m.kode_barang,
                m.nama_barang,
                m.expired_date,
                m.no_lot,
                m.qty AS qty_buku,
                m.qty_box AS box_buku,
                m.qty_pcs AS pcs_buku,
                COALESCE(o.qty_fisik, 0) AS qty_fisik,
                COALESCE(o.qty_box, 0) AS box_fisik,
                COALESCE(o.qty_pcs, 0) AS pcs_fisik,
                COALESCE(o.inputers, '-') AS inputers,
                COALESCE(o.wilayah, '-') AS wilayah,
                o.last_input,
                COALESCE(o.qty_fisik, 0) - m.qty AS selisih,
                CASE
                    WHEN o.qty_fisik IS NULL THEN 'belum'
                    WHEN COALESCE(o.qty_fisik, 0) = m.qty THEN 'match'
                    ELSE 'selisih'
                END AS status_opname
            FROM {$this->masterTable} m
            LEFT JOIN ({$opname}) o
                ON o.kode_barang = m.kode_barang
                AND o.exp_key = {$masterExpKey}
                AND o.lot_key = {$masterLotKey}
            WHERE {$masterPositiveWhere}
        ";
    }

    private function demo_summary()
    {
        return [
            'total_item' => 4,
            'qty_buku' => 515,
            'qty_fisik' => 501,
            'match_item' => 1,
            'selisih_item' => 2,
            'belum_item' => 1,
            'qty_plus' => 6,
            'qty_minus' => 20,
            'progress' => 75,
            'last_input' => date('Y-m-d H:i:s'),
            'mode' => 'demo'
        ];
    }

    public function summary()
    {
        if (!$this->ready()) {
            return $this->demo_summary();
        }

        $sql = "
            SELECT
                COUNT(*) AS total_item,
                SUM(qty_buku) AS qty_buku,
                SUM(qty_fisik) AS qty_fisik,
                SUM(CASE WHEN status_opname = 'match' THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN status_opname = 'selisih' THEN 1 ELSE 0 END) AS selisih_item,
                SUM(CASE WHEN status_opname = 'belum' THEN 1 ELSE 0 END) AS belum_item,
                SUM(CASE WHEN selisih > 0 THEN selisih ELSE 0 END) AS qty_plus,
                SUM(CASE WHEN selisih < 0 THEN ABS(selisih) ELSE 0 END) AS qty_minus,
                MAX(last_input) AS last_input
            FROM ({$this->base_query()}) x
        ";
        $row = $this->db->query($sql)->row_array();
        $total = (int)($row['total_item'] ?? 0);
        $done = $total - (int)($row['belum_item'] ?? 0);

        return [
            'total_item' => $total,
            'qty_buku' => (int)($row['qty_buku'] ?? 0),
            'qty_fisik' => (int)($row['qty_fisik'] ?? 0),
            'match_item' => (int)($row['match_item'] ?? 0),
            'selisih_item' => (int)($row['selisih_item'] ?? 0),
            'belum_item' => (int)($row['belum_item'] ?? 0),
            'qty_plus' => (int)($row['qty_plus'] ?? 0),
            'qty_minus' => (int)($row['qty_minus'] ?? 0),
            'progress' => $total > 0 ? round(($done / $total) * 100, 2) : 0,
            'last_input' => $row['last_input'] ?: '-',
            'mode' => 'database'
        ];
    }

    private function filtered_where($post)
    {
        $where = [];
        $search = trim((string)($post['search']['value'] ?? $post['search'] ?? ''));
        if ($search !== '') {
            $like = $this->db->escape('%' . $this->db->escape_like_str($search) . '%');
            $where[] = "(nama_barang LIKE {$like} OR kode_barang LIKE {$like} OR expired_date LIKE {$like} OR no_lot LIKE {$like})";
        }

        $status = trim((string)($post['status'] ?? ''));
        if ($status !== '') {
            $where[] = 'status_opname = ' . $this->db->escape($status);
        }

        return empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
    }

    private function demo_rows()
    {
        return [
            ['id' => 1, 'kode_barang' => 'BRG-001', 'nama_barang' => 'A-PlusCal 20 X 250 gr', 'expired_date' => '01/01/1000', 'no_lot' => '-', 'qty_buku' => 120, 'qty_fisik' => 120, 'box_buku' => 4, 'box_fisik' => 4, 'pcs_buku' => 0, 'pcs_fisik' => 0, 'selisih' => 0, 'status_opname' => 'match', 'inputers' => 'opname1', 'wilayah' => '1', 'last_input' => date('Y-m-d H:i:s')],
            ['id' => 2, 'kode_barang' => 'BRG-002', 'nama_barang' => 'Abacel 18 EC 20 X 500 ml', 'expired_date' => '01/01/1000', 'no_lot' => '-', 'qty_buku' => 85, 'qty_fisik' => 91, 'box_buku' => 4, 'box_fisik' => 4, 'pcs_buku' => 5, 'pcs_fisik' => 11, 'selisih' => 6, 'status_opname' => 'selisih', 'inputers' => 'opname2', 'wilayah' => '2', 'last_input' => date('Y-m-d H:i:s')],
            ['id' => 3, 'kode_barang' => 'BRG-003', 'nama_barang' => 'Paclo 15 WP 16 X 5 X 100 gr', 'expired_date' => '01/01/1000', 'no_lot' => '-', 'qty_buku' => 210, 'qty_fisik' => 190, 'box_buku' => 13, 'box_fisik' => 11, 'pcs_buku' => 2, 'pcs_fisik' => 14, 'selisih' => -20, 'status_opname' => 'selisih', 'inputers' => 'opname3', 'wilayah' => '1', 'last_input' => date('Y-m-d H:i:s')],
            ['id' => 4, 'kode_barang' => 'BRG-004', 'nama_barang' => 'Karissnail 6 PL 20 X 500 gr', 'expired_date' => '01/01/1000', 'no_lot' => '-', 'qty_buku' => 100, 'qty_fisik' => 0, 'box_buku' => 5, 'box_fisik' => 0, 'pcs_buku' => 0, 'pcs_fisik' => 0, 'selisih' => -100, 'status_opname' => 'belum', 'inputers' => '-', 'wilayah' => '-', 'last_input' => null],
        ];
    }

    public function datatable($post)
    {
        if (!$this->ready()) {
            $rows = $this->demo_rows();
            return [
                'draw' => (int)($post['draw'] ?? 1),
                'recordsTotal' => count($rows),
                'recordsFiltered' => count($rows),
                'data' => $rows,
            ];
        }

        $base = $this->base_query();
        $where = $this->filtered_where($post);
        $length = (int)($post['length'] ?? 10);
        $start = (int)($post['start'] ?? 0);

        $total = (int)$this->db->query("SELECT COUNT(*) AS total FROM ({$base}) x")->row()->total;
        $filtered = (int)$this->db->query("SELECT COUNT(*) AS total FROM ({$base}) x {$where}")->row()->total;

        $columns = ['id', 'kode_barang', 'nama_barang', 'expired_date', 'no_lot', 'qty_buku', 'qty_fisik', 'selisih', 'status_opname', 'last_input'];
        $orderIndex = (int)($post['order'][0]['column'] ?? 9);
        $orderColumn = $columns[$orderIndex] ?? 'last_input';
        $orderDir = strtolower((string)($post['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $limit = $length > 0 ? ' LIMIT ' . (int)$start . ', ' . (int)$length : '';

        $rows = $this->db->query("SELECT * FROM ({$base}) x {$where} ORDER BY {$orderColumn} {$orderDir}{$limit}")->result_array();

        return [
            'draw' => (int)($post['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ];
    }

    public function recent_inputs($limit = 8)
    {
        if (!$this->ready()) {
            return array_slice($this->demo_rows(), 0, $limit);
        }

        return $this->db
            ->select('kode_barang,nama_barang,expired_date,no_lot,qty,qty_box,qty_pcs,input_by,wilayah,input_at,created_at AS create_at')
            ->from($this->opnameTable)
            ->order_by('created_at', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    public function top_variance($limit = 6)
    {
        if (!$this->ready()) {
            return array_slice($this->demo_rows(), 1, $limit);
        }

        $base = $this->base_query();
        return $this->db
            ->query("SELECT * FROM ({$base}) x WHERE selisih <> 0 ORDER BY ABS(selisih) DESC LIMIT " . (int)$limit)
            ->result_array();
    }

    public function progress_by_user()
    {
        if (!$this->ready()) {
            return [
                ['input_by' => 'opname1', 'total_item' => 1, 'total_qty' => 120],
                ['input_by' => 'opname2', 'total_item' => 1, 'total_qty' => 91],
                ['input_by' => 'opname3', 'total_item' => 1, 'total_qty' => 190],
            ];
        }

        return $this->db
            ->select('input_by, COUNT(*) AS total_item, SUM(qty) AS total_qty')
            ->from($this->opnameTable)
            ->group_by('input_by')
            ->order_by('total_item', 'DESC')
            ->limit(6)
            ->get()
            ->result_array();
    }

    private function empty_datatable($post)
    {
        return [
            'draw' => (int)($post['draw'] ?? 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ];
    }

    private function opname_created_column()
    {
        if ($this->db->field_exists('created_at', $this->opnameTable)) {
            return 'created_at';
        }

        if ($this->db->field_exists('create_at', $this->opnameTable)) {
            return 'create_at';
        }

        return 'input_at';
    }

    private function monitoring_master_all_subquery()
    {
        $masterPositiveWhere = $this->master_positive_sql();

        return "
            SELECT
                kode_barang,
                MAX(nama_barang) AS nama_barang,
                SUM(COALESCE(qty, 0)) AS qty_buku,
                SUM(COALESCE(qty_box, 0)) AS box_buku,
                SUM(COALESCE(qty_pcs, 0)) AS pcs_buku
            FROM {$this->masterTable}
            WHERE {$masterPositiveWhere}
            GROUP BY kode_barang
        ";
    }

    private function monitoring_opname_all_subquery()
    {
        $createdColumn = $this->opname_created_column();

        return "
            SELECT
                kode_barang,
                MAX(nama_barang) AS nama_barang,
                SUM(CASE WHEN tim_opname = 1 THEN COALESCE(qty, 0) ELSE 0 END) AS qty_tim_1,
                SUM(CASE WHEN tim_opname = 2 THEN COALESCE(qty, 0) ELSE 0 END) AS qty_tim_2,
                SUM(CASE WHEN tim_opname = 1 THEN 1 ELSE 0 END) AS input_tim_1,
                SUM(CASE WHEN tim_opname = 2 THEN 1 ELSE 0 END) AS input_tim_2,
                GROUP_CONCAT(DISTINCT input_by ORDER BY input_by SEPARATOR ', ') AS inputers,
                GROUP_CONCAT(DISTINCT wilayah ORDER BY wilayah SEPARATOR ', ') AS wilayah,
                MAX({$createdColumn}) AS last_input
            FROM {$this->opnameTable}
            GROUP BY kode_barang
        ";
    }

    private function monitoring_compare_all_base()
    {
        $master = $this->monitoring_master_all_subquery();
        $opname = $this->monitoring_opname_all_subquery();

        return "
            SELECT
                x.kode_barang,
                x.nama_barang,
                x.qty_buku,
                x.qty_tim_1,
                x.qty_tim_2,
                x.input_tim_1,
                x.input_tim_2,
                x.inputers,
                x.wilayah,
                x.last_input,
                CASE
                    WHEN x.qty_tim_1 = x.qty_buku AND x.qty_tim_2 = x.qty_buku THEN 'all_match'
                    WHEN x.qty_tim_1 = x.qty_buku AND x.qty_tim_2 <> x.qty_buku THEN 'tim_1'
                    WHEN x.qty_tim_2 = x.qty_buku AND x.qty_tim_1 <> x.qty_buku THEN 'tim_2'
                    ELSE 're_check'
                END AS status_opname,
                CASE WHEN x.qty_tim_1 = x.qty_buku THEN 1 ELSE 0 END AS tim_1_match,
                CASE WHEN x.qty_tim_2 = x.qty_buku THEN 1 ELSE 0 END AS tim_2_match
            FROM (
                SELECT
                    m.kode_barang,
                    m.nama_barang,
                    COALESCE(m.qty_buku, 0) AS qty_buku,
                    COALESCE(o.qty_tim_1, 0) AS qty_tim_1,
                    COALESCE(o.qty_tim_2, 0) AS qty_tim_2,
                    COALESCE(o.input_tim_1, 0) AS input_tim_1,
                    COALESCE(o.input_tim_2, 0) AS input_tim_2,
                    COALESCE(o.inputers, '-') AS inputers,
                    COALESCE(o.wilayah, '-') AS wilayah,
                    o.last_input
                FROM ({$master}) m
                LEFT JOIN ({$opname}) o ON o.kode_barang = m.kode_barang
                UNION ALL
                SELECT
                    o.kode_barang,
                    o.nama_barang,
                    0 AS qty_buku,
                    COALESCE(o.qty_tim_1, 0) AS qty_tim_1,
                    COALESCE(o.qty_tim_2, 0) AS qty_tim_2,
                    COALESCE(o.input_tim_1, 0) AS input_tim_1,
                    COALESCE(o.input_tim_2, 0) AS input_tim_2,
                    COALESCE(o.inputers, '-') AS inputers,
                    COALESCE(o.wilayah, '-') AS wilayah,
                    o.last_input
                FROM ({$opname}) o
                LEFT JOIN ({$master}) m ON m.kode_barang = o.kode_barang
                WHERE m.kode_barang IS NULL
                    AND NOT EXISTS (
                        SELECT 1
                        FROM {$this->masterTable} mz
                        WHERE mz.kode_barang = o.kode_barang
                    )
            ) x
        ";
    }

    private function monitoring_master_lot_subquery()
    {
        $expKey = $this->exp_key('expired_date');
        $lotKey = $this->lot_key('no_lot');
        $masterPositiveWhere = $this->master_positive_sql();

        return "
            SELECT
                kode_barang,
                MAX(nama_barang) AS nama_barang,
                {$expKey} AS exp_key,
                {$lotKey} AS lot_key,
                MAX(expired_date) AS expired_date,
                MAX(no_lot) AS no_lot,
                MIN(id) AS master_id,
                SUM(COALESCE(qty, 0)) AS qty_buku,
                SUM(COALESCE(qty_box, 0)) AS box_buku,
                SUM(COALESCE(qty_pcs, 0)) AS pcs_buku
            FROM {$this->masterTable}
            WHERE {$masterPositiveWhere}
            GROUP BY kode_barang, exp_key, lot_key
        ";
    }

    private function monitoring_opname_lot_subquery()
    {
        $expKey = $this->exp_key('expired_date');
        $lotKey = $this->lot_key('no_lot');
        $createdColumn = $this->opname_created_column();
        $inputSource = $this->db->field_exists('input_source', $this->opnameTable)
            ? 'GROUP_CONCAT(DISTINCT input_source ORDER BY input_source SEPARATOR \',\') AS input_sources'
            : "'' AS input_sources";

        return "
            SELECT
                kode_barang,
                MAX(nama_barang) AS nama_barang,
                {$expKey} AS exp_key,
                {$lotKey} AS lot_key,
                MAX(expired_date) AS expired_date,
                MAX(no_lot) AS no_lot,
                SUM(CASE WHEN tim_opname = 1 THEN COALESCE(qty, 0) ELSE 0 END) AS qty_tim_1,
                SUM(CASE WHEN tim_opname = 2 THEN COALESCE(qty, 0) ELSE 0 END) AS qty_tim_2,
                SUM(CASE WHEN tim_opname = 1 THEN 1 ELSE 0 END) AS input_tim_1,
                SUM(CASE WHEN tim_opname = 2 THEN 1 ELSE 0 END) AS input_tim_2,
                GROUP_CONCAT(DISTINCT input_by ORDER BY input_by SEPARATOR ', ') AS inputers,
                GROUP_CONCAT(DISTINCT wilayah ORDER BY wilayah SEPARATOR ', ') AS wilayah,
                {$inputSource},
                MAX({$createdColumn}) AS last_input
            FROM {$this->opnameTable}
            GROUP BY kode_barang, exp_key, lot_key
        ";
    }

    private function monitoring_compare_lot_base()
    {
        $master = $this->monitoring_master_lot_subquery();
        $opname = $this->monitoring_opname_lot_subquery();
        $masterZeroExpKey = $this->exp_key('mz.expired_date');
        $masterZeroLotKey = $this->lot_key('mz.no_lot');

        return "
            SELECT
                x.master_id,
                x.kode_barang,
                x.nama_barang,
                x.expired_date,
                x.no_lot,
                x.qty_buku,
                x.qty_tim_1,
                x.qty_tim_2,
                x.input_tim_1,
                x.input_tim_2,
                x.inputers,
                x.wilayah,
                x.input_sources,
                x.last_input,
                CASE
                    WHEN x.qty_tim_1 = x.qty_buku AND x.qty_tim_2 = x.qty_buku THEN 'all_match'
                    WHEN x.qty_tim_1 = x.qty_buku AND x.qty_tim_2 <> x.qty_buku THEN 'tim_1'
                    WHEN x.qty_tim_2 = x.qty_buku AND x.qty_tim_1 <> x.qty_buku THEN 'tim_2'
                    ELSE 're_check'
                END AS status_opname,
                CASE WHEN x.qty_tim_1 = x.qty_buku THEN 1 ELSE 0 END AS tim_1_match,
                CASE WHEN x.qty_tim_2 = x.qty_buku THEN 1 ELSE 0 END AS tim_2_match
            FROM (
                SELECT
                    m.master_id,
                    m.kode_barang,
                    m.nama_barang,
                    m.expired_date,
                    m.no_lot,
                    COALESCE(m.qty_buku, 0) AS qty_buku,
                    COALESCE(o.qty_tim_1, 0) AS qty_tim_1,
                    COALESCE(o.qty_tim_2, 0) AS qty_tim_2,
                    COALESCE(o.input_tim_1, 0) AS input_tim_1,
                    COALESCE(o.input_tim_2, 0) AS input_tim_2,
                    COALESCE(o.inputers, '-') AS inputers,
                    COALESCE(o.wilayah, '-') AS wilayah,
                    COALESCE(o.input_sources, '') AS input_sources,
                    o.last_input
                FROM ({$master}) m
                LEFT JOIN ({$opname}) o
                    ON o.kode_barang = m.kode_barang
                    AND o.exp_key = m.exp_key
                    AND o.lot_key = m.lot_key
                UNION ALL
                SELECT
                    0 AS master_id,
                    o.kode_barang,
                    o.nama_barang,
                    o.expired_date,
                    o.no_lot,
                    0 AS qty_buku,
                    COALESCE(o.qty_tim_1, 0) AS qty_tim_1,
                    COALESCE(o.qty_tim_2, 0) AS qty_tim_2,
                    COALESCE(o.input_tim_1, 0) AS input_tim_1,
                    COALESCE(o.input_tim_2, 0) AS input_tim_2,
                    COALESCE(o.inputers, '-') AS inputers,
                    COALESCE(o.wilayah, '-') AS wilayah,
                    COALESCE(o.input_sources, '') AS input_sources,
                    o.last_input
                FROM ({$opname}) o
                LEFT JOIN ({$master}) m
                    ON m.kode_barang = o.kode_barang
                    AND m.exp_key = o.exp_key
                    AND m.lot_key = o.lot_key
                WHERE m.kode_barang IS NULL
                    AND (
                        FIND_IN_SET('manual opname request', COALESCE(o.input_sources, '')) > 0
                        OR FIND_IN_SET('master data request opname', COALESCE(o.input_sources, '')) > 0
                        OR NOT EXISTS (
                        SELECT 1
                        FROM {$this->masterTable} mz
                        WHERE mz.kode_barang = o.kode_barang
                            AND {$masterZeroExpKey} = o.exp_key
                            AND {$masterZeroLotKey} = o.lot_key
                        )
                    )
            ) x
        ";
    }

    private function monitoring_filtered_where($post, $withLot = false)
    {
        $where = [];
        $search = trim((string)($post['search']['value'] ?? $post['search'] ?? ''));
        if ($search !== '') {
            $like = $this->db->escape('%' . $this->db->escape_like_str($search) . '%');
            $fields = ['kode_barang', 'nama_barang', 'inputers', 'wilayah'];
            if ($withLot) {
                $fields[] = 'expired_date';
                $fields[] = 'no_lot';
            }

            $likes = [];
            foreach ($fields as $field) {
                $likes[] = "{$field} LIKE {$like}";
            }
            $where[] = '(' . implode(' OR ', $likes) . ')';
        }

        $status = trim((string)($post['status'] ?? ''));
        if ($status !== '') {
            $where[] = 'status_opname = ' . $this->db->escape($status);
        }

        return empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
    }

    private function monitoring_datatable($post, $base, $columns, $withLot = false)
    {
        $where = $this->monitoring_filtered_where($post, $withLot);
        $length = (int)($post['length'] ?? 10);
        $start = max(0, (int)($post['start'] ?? 0));
        $total = (int)$this->db->query("SELECT COUNT(*) AS total FROM ({$base}) x")->row()->total;
        $filtered = (int)$this->db->query("SELECT COUNT(*) AS total FROM ({$base}) x {$where}")->row()->total;
        $orderIndex = (int)($post['order'][0]['column'] ?? 0);
        $orderColumn = $columns[$orderIndex] ?? 'kode_barang';
        $orderDir = strtolower((string)($post['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $limit = $length > 0 ? ' LIMIT ' . (int)$start . ', ' . (int)$length : '';
        $rows = $this->db->query("SELECT * FROM ({$base}) x {$where} ORDER BY {$orderColumn} {$orderDir}{$limit}")->result_array();

        return [
            'draw' => (int)($post['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ];
    }

    public function monitoring_compare_all_datatable($post)
    {
        if (!$this->ready()) {
            return $this->empty_datatable($post);
        }

        return $this->monitoring_datatable($post, $this->monitoring_compare_all_base(), [
            'kode_barang',
            'nama_barang',
            'qty_buku',
            'qty_tim_1',
            'qty_tim_2',
            'status_opname',
            'kode_barang',
        ]);
    }

    public function monitoring_compare_lot_datatable($post)
    {
        if (!$this->ready()) {
            return $this->empty_datatable($post);
        }

        return $this->monitoring_datatable($post, $this->monitoring_compare_lot_base(), [
            'kode_barang',
            'nama_barang',
            'expired_date',
            'no_lot',
            'qty_buku',
            'qty_tim_1',
            'qty_tim_2',
            'status_opname',
            'kode_barang',
        ], true);
    }

    private function monitoring_result_from_base($base, $mode = 'overall')
    {
        $matchExpression = 'status_opname = ' . $this->db->escape('all_match');
        if ($mode === 'team_1') {
            $matchExpression = 'tim_1_match = 1';
        } elseif ($mode === 'team_2') {
            $matchExpression = 'tim_2_match = 1';
        }

        $row = $this->db->query("
            SELECT
                SUM(CASE WHEN {$matchExpression} THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN {$matchExpression} THEN 0 ELSE 1 END) AS not_match_item
            FROM ({$base}) x
        ")->row_array();

        return $this->percentage_result(
            $row['match_item'] ?? 0,
            $row['not_match_item'] ?? 0
        );
    }

    private function progress_result($input, $total)
    {
        $input = (int)$input;
        $total = (int)$total;
        $notInput = max(0, $total - $input);

        return [
            'total' => $total,
            'input' => $input,
            'not_input' => $notInput,
            'persen_input' => $total > 0 ? round(($input / $total) * 100, 2) : 0,
        ];
    }

    private function monitoring_team_result_summary($team)
    {
        $team = (int)$team === 2 ? 2 : 1;
        $inputColumn = 'input_tim_' . $team;
        $matchColumn = 'tim_' . $team . '_match';

        $allRow = $this->db->query("
            SELECT
                COUNT(*) AS total_item,
                SUM(CASE WHEN {$inputColumn} > 0 THEN 1 ELSE 0 END) AS input_item,
                SUM(CASE WHEN {$matchColumn} = 1 THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN {$matchColumn} = 1 THEN 0 ELSE 1 END) AS not_match_item
            FROM ({$this->monitoring_compare_all_base()}) x
        ")->row_array();

        $lotRow = $this->db->query("
            SELECT
                SUM(CASE WHEN {$matchColumn} = 1 THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN {$matchColumn} = 1 THEN 0 ELSE 1 END) AS not_match_item
            FROM ({$this->monitoring_compare_lot_base()}) x
        ")->row_array();

        return [
            'progress_input' => $this->progress_result($allRow['input_item'] ?? 0, $allRow['total_item'] ?? 0),
            'compare_all' => $this->percentage_result($allRow['match_item'] ?? 0, $allRow['not_match_item'] ?? 0),
            'compare_lot' => $this->percentage_result($lotRow['match_item'] ?? 0, $lotRow['not_match_item'] ?? 0),
        ];
    }

    public function monitoring_summary()
    {
        if (!$this->ready()) {
            return [
                'team_1' => [
                    'progress_input' => $this->progress_result(0, 0),
                    'compare_all' => $this->percentage_result(0, 0),
                    'compare_lot' => $this->percentage_result(0, 0),
                ],
                'team_2' => [
                    'progress_input' => $this->progress_result(0, 0),
                    'compare_all' => $this->percentage_result(0, 0),
                    'compare_lot' => $this->percentage_result(0, 0),
                ],
                'input_source' => $this->monitoring_input_source_summary(),
                'source_table' => $this->masterTable . ' / ' . $this->opnameTable,
            ];
        }

        return [
            'team_1' => $this->monitoring_team_result_summary(1),
            'team_2' => $this->monitoring_team_result_summary(2),
            'input_source' => $this->monitoring_input_source_summary(),
            'source_table' => $this->masterTable . ' / ' . $this->opnameTable,
        ];
    }

    public function monitoring_input_source_summary()
    {
        $summary = [
            'manual' => ['total_input' => 0, 'total_user' => 0, 'total_qty' => 0, 'last_input' => '-'],
            'request' => ['total_input' => 0, 'total_user' => 0, 'total_qty' => 0, 'last_input' => '-'],
        ];

        if (!$this->ensure_manual_tables()) {
            return $summary;
        }

        $requestRow = $this->db->query("
            SELECT
                COUNT(*) AS total_input,
                COUNT(DISTINCT NULLIF(TRIM(requested_by), '')) AS total_user,
                MAX(requested_at) AS last_input
            FROM {$this->manualMasterTable}
        ")->row_array();

        $manualRow = $this->db->query("
            SELECT
                COUNT(*) AS total_input,
                COUNT(DISTINCT NULLIF(TRIM(input_by), '')) AS total_user,
                COALESCE(SUM(qty), 0) AS total_qty,
                MAX(input_at) AS last_input
            FROM {$this->manualOpnameTable}
        ")->row_array();

        $summary['request'] = [
            'total_input' => (int)($requestRow['total_input'] ?? 0),
            'total_user' => (int)($requestRow['total_user'] ?? 0),
            'total_qty' => (int)($requestRow['total_input'] ?? 0),
            'last_input' => $requestRow['last_input'] ?: '-',
        ];
        $summary['manual'] = [
            'total_input' => (int)($manualRow['total_input'] ?? 0),
            'total_user' => (int)($manualRow['total_user'] ?? 0),
            'total_qty' => (int)($manualRow['total_qty'] ?? 0),
            'last_input' => $manualRow['last_input'] ?: '-',
        ];

        return $summary;
    }

    public function monitoring_request_opname_rows($limit = 500)
    {
        if (!$this->ensure_manual_tables()) {
            return [];
        }

        return $this->db
            ->select('id,source_id,kode_barang,nama_barang,expired_date,no_lot,dimensi,status,requested_by,requested_at,reviewed_by,reviewed_at,review_note,created_at,updated_at')
            ->from($this->manualMasterTable)
            ->order_by('requested_at', 'DESC')
            ->order_by('id', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    public function monitoring_manual_opname_rows($limit = 500)
    {
        if (!$this->ensure_manual_tables()) {
            return [];
        }

        return $this->db
            ->select('id,manual_master_id,source_id,kode_barang,nama_barang,expired_date,no_lot,qty,qty_pcs,qty_box,input_by,input_at,wilayah,tim_opname,created_at,updated_at')
            ->from($this->manualOpnameTable)
            ->order_by('input_at', 'DESC')
            ->order_by('id', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    public function monitoring_activity($limit = 10)
    {
        if (!$this->ready()) {
            return [];
        }

        $createdColumn = $this->opname_created_column();
        return $this->db
            ->select("kode_barang,nama_barang,expired_date,no_lot,qty,qty_pcs,qty_box,input_by,wilayah,tim_opname,input_at,{$createdColumn} AS created_at", false)
            ->from($this->opnameTable)
            ->order_by($createdColumn, 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    public function monitoring_activity_wilayah_options()
    {
        if (!$this->ready()) {
            return [];
        }

        return $this->db
            ->select('wilayah')
            ->from($this->opnameTable)
            ->where("TRIM(COALESCE(wilayah, '')) <> ''", null, false)
            ->group_by('wilayah')
            ->order_by('wilayah', 'ASC')
            ->get()
            ->result_array();
    }

    public function monitoring_activity_log($wilayah = '', $limit = 300)
    {
        if (!$this->ready()) {
            return [];
        }

        $createdColumn = $this->opname_created_column();
        $noLot = $this->db->field_exists('no_lot', $this->opnameTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->opnameTable) ? 'nolot AS no_lot' : "'-' AS no_lot");

        $this->db
            ->select("id,kode_barang,nama_barang,expired_date,{$noLot},qty,qty_pcs,qty_box,input_by,wilayah,tim_opname,input_at,{$createdColumn} AS created_at", false)
            ->from($this->opnameTable);

        $wilayah = trim((string)$wilayah);
        if ($wilayah !== '') {
            $this->db->where('wilayah', $wilayah);
        }

        return $this->db
            ->order_by($createdColumn, 'DESC')
            ->order_by('id', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    public function monitoring_compare_all_detail($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if (!$this->ready() || $kodeBarang === '') {
            return null;
        }

        return $this->db
            ->query(
                "SELECT * FROM ({$this->monitoring_compare_all_base()}) x WHERE kode_barang = ? LIMIT 1",
                [$kodeBarang]
            )
            ->row_array();
    }

    public function lot_compare_by_kode_barang($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if (!$this->ready() || $kodeBarang === '') {
            return $this->master_items_by_kode_barang($kodeBarang);
        }

        return $this->db
            ->query(
                "
                SELECT
                    x.*,
                    (COALESCE(x.qty_tim_1, 0) - COALESCE(x.qty_buku, 0)) AS selisih_tim_1,
                    (COALESCE(x.qty_tim_2, 0) - COALESCE(x.qty_buku, 0)) AS selisih_tim_2
                FROM ({$this->monitoring_compare_lot_base()}) x
                WHERE x.kode_barang = ?
                ORDER BY x.expired_date ASC, x.no_lot ASC
                ",
                [$kodeBarang]
            )
            ->result_array();
    }

    public function master_items_by_kode_barang($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if (!$this->db->table_exists($this->masterTable) || $kodeBarang === '') {
            return [];
        }

        $noLotColumn = $this->db->field_exists('no_lot', $this->masterTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->masterTable) ? 'nolot AS no_lot' : "'-' AS no_lot");
        $dimensi = $this->db->field_exists('dimensi', $this->masterTable)
            ? 'COALESCE(dimensi, 0) AS dimensi'
            : 'CASE WHEN COALESCE(qty_box, 0) > 0 THEN FLOOR((COALESCE(qty, 0) - COALESCE(qty_pcs, 0)) / qty_box) ELSE 0 END AS dimensi';

        return $this->db
            ->select("id,kode_barang,nama_barang,expired_date,{$noLotColumn},qty,qty_pcs,qty_box,{$dimensi}", false)
            ->from($this->masterTable)
            ->where('kode_barang', $kodeBarang)
            ->order_by('expired_date', 'ASC')
            ->order_by('no_lot', 'ASC')
            ->get()
            ->result_array();
    }

    public function master_item_options_by_kode_barang($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $this->master_barang_select();
        return $this->db
            ->where('kode_barang', $kodeBarang)
            ->order_by('expired_date', 'ASC')
            ->order_by('no_lot', 'ASC')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function input_opname_by_kode_barang($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if (!$this->db->table_exists($this->opnameTable) || $kodeBarang === '') {
            return [];
        }

        $createdColumn = $this->opname_created_column();
        $sourceId = $this->db->field_exists('source_id', $this->opnameTable) ? 'source_id' : 'NULL AS source_id';
        $noLot = $this->db->field_exists('no_lot', $this->opnameTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->opnameTable) ? 'nolot AS no_lot' : "'-' AS no_lot");
        $timOpname = $this->db->field_exists('tim_opname', $this->opnameTable) ? 'tim_opname' : '0 AS tim_opname';
        $scanCode = $this->db->field_exists('scan_code', $this->opnameTable) ? 'scan_code' : 'NULL AS scan_code';
        $updatedAt = $this->db->field_exists('updated_at', $this->opnameTable) ? 'updated_at' : 'NULL AS updated_at';

        $rows = $this->db
            ->select("id,{$sourceId},kode_barang,nama_barang,expired_date,{$noLot},qty,qty_pcs,qty_box,input_by,input_at,wilayah,{$timOpname},{$scanCode},{$createdColumn} AS created_at,{$updatedAt}", false)
            ->from($this->opnameTable)
            ->where('kode_barang', $kodeBarang)
            ->order_by($createdColumn, 'DESC')
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $row['dimensi'] = $this->opname_dimensi_from_row($row);
        }
        unset($row);

        return $rows;
    }

    public function recycle_input_by_kode_barang($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->ensure_opname_recycle_table()) {
            return [];
        }

        return $this->db->from('stockopname_recyclebin_input')
            ->where('kode_barang', $kodeBarang)
            ->order_by('deleted_at', 'DESC')->order_by('id', 'DESC')
            ->get()->result_array();
    }

    public function request_item_by_kode_barang($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->ensure_manual_tables()) {
            return [];
        }

        return $this->db
            ->select("id AS manual_master_id,source_id,nama_barang,kode_barang,expired_date,no_lot,dimensi,qty,qty_pcs,qty_box,wilayah,tim_opname,requested_by AS input_by,status AS input_source,status,requested_at", false)
            ->from($this->manualMasterTable)
            ->where('kode_barang', $kodeBarang)
            ->where_in('status', ['Manual Input', 'Request Master Item'])
            ->order_by('requested_at', 'ASC')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    private function input_opname_row_by_id($id)
    {
        if (!$this->db->table_exists($this->opnameTable)) {
            return null;
        }

        $createdColumn = $this->opname_created_column();
        $sourceId = $this->db->field_exists('source_id', $this->opnameTable) ? 'source_id' : 'NULL AS source_id';
        $noLot = $this->db->field_exists('no_lot', $this->opnameTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->opnameTable) ? 'nolot AS no_lot' : "'-' AS no_lot");
        $timOpname = $this->db->field_exists('tim_opname', $this->opnameTable) ? 'tim_opname' : '0 AS tim_opname';
        $scanCode = $this->db->field_exists('scan_code', $this->opnameTable) ? 'scan_code' : 'NULL AS scan_code';
        $updatedAt = $this->db->field_exists('updated_at', $this->opnameTable) ? 'updated_at' : 'NULL AS updated_at';

        return $this->db
            ->select("id,{$sourceId},kode_barang,nama_barang,expired_date,{$noLot},qty,qty_pcs,qty_box,input_by,input_at,wilayah,{$timOpname},{$scanCode},{$createdColumn} AS created_at,{$updatedAt}", false)
            ->from($this->opnameTable)
            ->where('id', (int)$id)
            ->limit(1)
            ->get()
            ->row_array();
    }

    private function ensure_opname_recycle_table()
    {
        if ($this->db->table_exists('stockopname_recyclebin_input')) {
            return true;
        }

        return $this->db->query("
            CREATE TABLE IF NOT EXISTS `stockopname_recyclebin_input` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `source_id` INT(11) NOT NULL,
                `original_source_id` INT(11) NULL,
                `kode_barang` VARCHAR(50) NOT NULL,
                `nama_barang` TEXT NOT NULL,
                `expired_date` DATE NOT NULL,
                `no_lot` VARCHAR(100) NOT NULL,
                `qty` INT(12) NOT NULL DEFAULT 0,
                `qty_box` INT(12) NOT NULL DEFAULT 0,
                `qty_pcs` INT(12) NOT NULL DEFAULT 0,
                `input_by` VARCHAR(100) NOT NULL,
                `input_at` DATETIME NULL,
                `wilayah` INT(2) NOT NULL DEFAULT 0,
                `tim_opname` INT(2) NOT NULL DEFAULT 0,
                `scan_code` VARCHAR(255) NULL,
                `original_created_at` DATETIME NULL,
                `original_updated_at` DATETIME NULL,
                `deleted_by` VARCHAR(100) NOT NULL,
                `deleted_at` DATETIME NOT NULL,
                `delete_reason` TEXT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_recycle_source` (`source_id`),
                KEY `idx_recycle_barang` (`kode_barang`),
                KEY `idx_recycle_deleted_at` (`deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    private function ensure_opname_log_table()
    {
        if (!$this->db->table_exists('stockopname_opname_log')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `stockopname_opname_log` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `opname_id` INT(11) NULL,
                    `kode_barang` VARCHAR(50) NOT NULL,
                    `nama_barang` TEXT NULL,
                    `expired_date` DATE NULL,
                    `no_lot` VARCHAR(100) NULL,
                    `action_type` VARCHAR(30) NOT NULL,
                    `action` VARCHAR(30) NULL,
                    `changed_fields` TEXT NULL,
                    `old_data` LONGTEXT NULL,
                    `new_data` LONGTEXT NULL,
                    `old_value` LONGTEXT NULL,
                    `new_value` LONGTEXT NULL,
                    `description` TEXT NULL,
                    `created_by` VARCHAR(100) NOT NULL,
                    `created_at` DATETIME NOT NULL,
                    `changed_by` VARCHAR(100) NULL,
                    `changed_at` DATETIME NULL,
                    `ip_address` VARCHAR(45) NULL,
                    `user_agent` VARCHAR(255) NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_stockopname_opname_log_barang` (`kode_barang`),
                    KEY `idx_stockopname_opname_log_opname` (`opname_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        $columns = [
            'nama_barang' => 'TEXT NULL AFTER `kode_barang`',
            'expired_date' => 'DATE NULL AFTER `nama_barang`',
            'no_lot' => 'VARCHAR(100) NULL AFTER `expired_date`',
            'action_type' => "VARCHAR(30) NOT NULL DEFAULT 'EDIT_QTY' AFTER `no_lot`",
            'old_value' => 'LONGTEXT NULL AFTER `new_data`',
            'new_value' => 'LONGTEXT NULL AFTER `old_value`',
            'description' => 'TEXT NULL AFTER `new_value`',
            'created_by' => "VARCHAR(100) NOT NULL DEFAULT 'system' AFTER `description`",
            'created_at' => 'DATETIME NULL AFTER `created_by`',
            'before_qty' => 'INT(12) NULL AFTER `created_at`',
            'after_qty' => 'INT(12) NULL AFTER `before_qty`',
            'before_qty_box' => 'INT(12) NULL AFTER `after_qty`',
            'after_qty_box' => 'INT(12) NULL AFTER `before_qty_box`',
            'before_qty_pcs' => 'INT(12) NULL AFTER `after_qty_box`',
            'after_qty_pcs' => 'INT(12) NULL AFTER `before_qty_pcs`',
        ];
        foreach ($columns as $field => $definition) {
            if (!$this->db->field_exists($field, 'stockopname_opname_log')) {
                $this->db->query("ALTER TABLE stockopname_opname_log ADD `{$field}` {$definition}");
            }
        }

        return $this->db->table_exists('stockopname_opname_log');
    }

    private function normalize_opname_snapshot($row)
    {
        return [
            'expired_date' => (string)($row['expired_date'] ?? ''),
            'no_lot' => (string)($row['no_lot'] ?? '-'),
            'qty_box' => (int)($row['qty_box'] ?? 0),
            'qty_pcs' => (int)($row['qty_pcs'] ?? 0),
            'qty' => (int)($row['qty'] ?? 0),
            'wilayah' => (string)($row['wilayah'] ?? ''),
        ];
    }

    private function master_dimensi_select_sql()
    {
        return $this->db->field_exists('dimensi', $this->masterTable)
            ? 'COALESCE(dimensi, 0) AS dimensi'
            : 'CASE WHEN COALESCE(qty_box, 0) > 0 THEN FLOOR((COALESCE(qty, 0) - COALESCE(qty_pcs, 0)) / qty_box) ELSE 0 END AS dimensi';
    }

    private function dimensi_from_master_row($row)
    {
        $dimensi = (int)($row['dimensi'] ?? 0);
        if ($dimensi > 0) {
            return $dimensi;
        }

        $qtyBox = (int)($row['qty_box'] ?? 0);
        if ($qtyBox <= 0) {
            return 0;
        }

        return max(0, (int)floor(((int)($row['qty'] ?? 0) - (int)($row['qty_pcs'] ?? 0)) / $qtyBox));
    }

    private function master_dimensi_for_opname_row($row)
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return 0;
        }

        $sourceId = (int)($row['source_id'] ?? 0);
        $this->db
            ->select('qty,qty_box,qty_pcs,' . $this->master_dimensi_select_sql(), false)
            ->from($this->masterTable);

        if ($sourceId > 0) {
            $this->db->where('id', $sourceId);
        } else {
            $kodeBarang = trim((string)($row['kode_barang'] ?? ''));
            if ($kodeBarang === '') {
                return 0;
            }

            $this->db->where('kode_barang', $kodeBarang);
            $expiredDate = trim((string)($row['expired_date'] ?? ''));
            if ($expiredDate !== '') {
                $this->db->where($this->exp_key('expired_date') . ' = ' . $this->db->escape($expiredDate), null, false);
            }

            $noLot = trim((string)($row['no_lot'] ?? '-'));
            if ($noLot !== '') {
                $normalizedLot = $noLot === '-' ? ['', '-', '0'] : [$noLot];
                $this->db->group_start();
                foreach ($normalizedLot as $index => $lot) {
                    $condition = "TRIM(COALESCE(no_lot, '')) = " . $this->db->escape($lot);
                    if ($index === 0) {
                        $this->db->where($condition, null, false);
                    } else {
                        $this->db->or_where($condition, null, false);
                    }
                }
                $this->db->group_end();
            }

            $this->db->order_by('id', 'ASC');
        }

        $master = $this->db->limit(1)->get()->row_array();
        return $master ? $this->dimensi_from_master_row($master) : 0;
    }

    private function opname_dimensi_from_row($row)
    {
        $masterDimensi = $this->master_dimensi_for_opname_row($row);
        if ($masterDimensi > 0) {
            return $masterDimensi;
        }

        $qtyBox = (int)($row['qty_box'] ?? 0);
        if ($qtyBox <= 0) {
            return 0;
        }

        return max(0, (int)floor(((int)($row['qty'] ?? 0) - (int)($row['qty_pcs'] ?? 0)) / $qtyBox));
    }

    private function insert_opname_edit_log($oldRow, $newRow, $changedFields, $actor, $actionType, $description = '')
    {
        if (!$this->ensure_opname_log_table()) {
            return false;
        }

        $oldData = $this->normalize_opname_snapshot($oldRow);
        $newData = $this->normalize_opname_snapshot($newRow);
        $now = date('Y-m-d H:i:s');
        return $this->db->insert('stockopname_opname_log', [
            'opname_id' => (int)($oldRow['id'] ?? $newRow['id'] ?? 0),
            'kode_barang' => (string)($oldRow['kode_barang'] ?? $newRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($oldRow['nama_barang'] ?? $newRow['nama_barang'] ?? ''),
            'expired_date' => ($oldRow['expired_date'] ?? $newRow['expired_date'] ?? null) ?: null,
            'no_lot' => (string)($oldRow['no_lot'] ?? $newRow['no_lot'] ?? '-'),
            'action_type' => (string)$actionType,
            'action' => (string)$actionType,
            'changed_fields' => json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE),
            'old_data' => json_encode($oldData, JSON_UNESCAPED_UNICODE),
            'new_data' => json_encode($newData, JSON_UNESCAPED_UNICODE),
            'old_value' => json_encode($oldData, JSON_UNESCAPED_UNICODE),
            'new_value' => json_encode($newData, JSON_UNESCAPED_UNICODE),
            'description' => $description ?: $actionType . ' input opname',
            'created_by' => (string)$actor,
            'created_at' => $now,
            'before_qty' => $oldData['qty'],
            'after_qty' => $newData['qty'],
            'before_qty_box' => $oldData['qty_box'],
            'after_qty_box' => $newData['qty_box'],
            'before_qty_pcs' => $oldData['qty_pcs'],
            'after_qty_pcs' => $newData['qty_pcs'],
            'changed_by' => (string)$actor,
            'changed_at' => $now,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string)$this->input->user_agent(), 0, 255),
        ]);
    }

    private function insert_request_item_event($row, $actor, $inputSource)
    {
        if (!$this->ensure_manual_tables()) {
            return false;
        }

        return $this->db->insert($this->manualOpnameTable, [
            'manual_master_id' => 0,
            'source_id' => (int)($row['source_id'] ?? 0),
            'kode_barang' => (string)($row['kode_barang'] ?? ''),
            'nama_barang' => (string)($row['nama_barang'] ?? ''),
            'expired_date' => (string)($row['expired_date'] ?? ''),
            'no_lot' => (string)($row['no_lot'] ?? '-'),
            'qty' => (int)($row['qty'] ?? 0),
            'qty_pcs' => (int)($row['qty_pcs'] ?? 0),
            'qty_box' => (int)($row['qty_box'] ?? 0),
            'input_by' => (string)$actor,
            'input_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($row['wilayah'] ?? 0),
            'tim_opname' => (int)($row['tim_opname'] ?? 0),
            'input_source' => strtolower((string)$inputSource),
        ]);
    }

    public function update_input_opname($id, $kodeBarang, $payload, $actor, $actionType = 'EDIT_QTY')
    {
        $kodeBarang = trim((string)$kodeBarang);
        $oldRow = $this->input_opname_row_by_id($id);
        if (!$oldRow || (string)($oldRow['kode_barang'] ?? '') !== $kodeBarang) {
            return [
                'status' => false,
                'message' => 'Data input opname tidak ditemukan.',
            ];
        }

        $before = $this->normalize_opname_snapshot($oldRow);
        $dimensi = $this->opname_dimensi_from_row($oldRow);
        $after = [
            'expired_date' => $before['expired_date'],
            'no_lot' => $before['no_lot'],
            'qty_box' => (int)($payload['qty_box'] ?? $before['qty_box']),
            'qty_pcs' => (int)($payload['qty_pcs'] ?? $before['qty_pcs']),
            'qty' => ((int)($payload['qty_box'] ?? $before['qty_box']) * $dimensi) + (int)($payload['qty_pcs'] ?? $before['qty_pcs']),
            'wilayah' => $before['wilayah'],
        ];

        $changedFields = [];
        foreach ($after as $field => $value) {
            if ((string)$before[$field] !== (string)$value) {
                $changedFields[] = $field;
            }
        }

        if (empty($changedFields)) {
            return [
                'status' => false,
                'message' => 'Tidak ada perubahan data untuk disimpan.',
            ];
        }

        if (!$this->ensure_opname_log_table()) {
            return [
                'status' => false,
                'message' => 'Tabel log perubahan input opname tidak dapat disiapkan.',
            ];
        }

        $update = [
            'qty_box' => $after['qty_box'],
            'qty_pcs' => $after['qty_pcs'],
            'qty' => $after['qty'],
        ];

        if ($this->db->field_exists('updated_at', $this->opnameTable)) {
            $update['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->trans_start();
        $this->db
            ->where('id', (int)$id)
            ->where('kode_barang', $kodeBarang)
            ->update($this->opnameTable, $update);
        $newRow = $this->input_opname_row_by_id($id);
        $this->insert_opname_edit_log($oldRow, $newRow ?: array_merge($oldRow, $after), $changedFields, $actor, $actionType);
        if ($actionType === 'ADJUSTMENT') {
            $this->insert_request_item_event($newRow ?: array_merge($oldRow, $after), $actor, 'adjustment');
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return [
                'status' => false,
                'message' => 'Gagal menyimpan perubahan input opname.',
            ];
        }

        return [
            'status' => true,
            'data' => [
                'row' => $newRow,
                'changed_fields' => $changedFields,
            ],
        ];
    }

    public function delete_input_opname($id, $kodeBarang, $actor)
    {
        $oldRow = $this->input_opname_row_by_id($id);
        if (!$oldRow || (string)$oldRow['kode_barang'] !== trim((string)$kodeBarang)) {
            return ['status' => false, 'message' => 'Data input opname tidak ditemukan.'];
        }
        if (!$this->ensure_opname_log_table()) {
            return ['status' => false, 'message' => 'Tabel audit log belum siap.'];
        }

        $this->db->trans_start();
        $this->insert_opname_edit_log($oldRow, [], array_keys($this->normalize_opname_snapshot($oldRow)), $actor, 'DELETE', 'Input opname dihapus langsung dari database.');
        $this->db->where('id', (int)$id)->where('kode_barang', $kodeBarang)->delete($this->opnameTable);
        $this->db->trans_complete();

        return $this->db->trans_status()
            ? ['status' => true, 'message' => 'Input opname berhasil dihapus dari database.']
            : ['status' => false, 'message' => 'Gagal menghapus input opname. Transaksi dibatalkan.'];
    }

    public function repost_input_opname($recycleId, $kodeBarang, $actor)
    {
        if (!$this->ensure_opname_recycle_table() || !$this->ensure_opname_log_table()) {
            return ['status' => false, 'message' => 'Tabel recycle bin atau audit log belum siap.'];
        }
        $row = $this->db->from('stockopname_recyclebin_input')->where('id', (int)$recycleId)
            ->where('kode_barang', $kodeBarang)->limit(1)->get()->row_array();
        if (!$row) {
            return ['status' => false, 'message' => 'Data recycle bin tidak ditemukan.'];
        }

        $insert = [
            'source_id' => (int)($row['original_source_id'] ?? 0), 'kode_barang' => $row['kode_barang'],
            'nama_barang' => $row['nama_barang'], 'expired_date' => $row['expired_date'],
            'no_lot' => $row['no_lot'], 'qty' => (int)$row['qty'], 'qty_pcs' => (int)$row['qty_pcs'],
            'qty_box' => (int)$row['qty_box'], 'input_by' => $row['input_by'],
            'input_at' => $row['input_at'] ?: date('Y-m-d H:i:s'), 'wilayah' => (int)$row['wilayah'],
            'tim_opname' => (int)$row['tim_opname'], 'scan_code' => $row['scan_code'],
        ];
        if ($this->db->field_exists('created_at', $this->opnameTable)) {
            $insert['created_at'] = $row['original_created_at'] ?: date('Y-m-d H:i:s');
        }

        $this->db->trans_start();
        $this->db->insert($this->opnameTable, $insert);
        $newId = (int)$this->db->insert_id();
        $newRow = $this->input_opname_row_by_id($newId) ?: array_merge($insert, ['id' => $newId]);
        $logOld = array_merge($row, ['id' => (int)$row['source_id']]);
        $this->insert_opname_edit_log($logOld, $newRow, array_keys($this->normalize_opname_snapshot($newRow)), $actor, 'REPOST', 'Input opname dikembalikan dari recycle bin.');
        $this->insert_request_item_event($newRow, $actor, 'repost');
        $this->db->where('id', (int)$recycleId)->delete('stockopname_recyclebin_input');
        $this->db->trans_complete();

        return $this->db->trans_status()
            ? ['status' => true, 'message' => 'Input opname berhasil direpost.', 'data' => ['id' => $newId]]
            : ['status' => false, 'message' => 'Gagal repost input opname. Transaksi dibatalkan.'];
    }

    public function add_request_item_to_opname($kodeBarang, $expiredDate, $noLot, $payload, $actor)
    {
        if (!$this->ready() || !$this->ensure_manual_tables() || !$this->ensure_opname_log_table() || !$this->ensure_opname_input_source_column() || !$this->ensure_master_input_status_columns()) {
            return ['status' => false, 'message' => 'Tabel opname atau request item belum siap.'];
        }

        $manualMasterId = (int)($payload['manual_master_id'] ?? 0);
        $this->db
            ->select('id,source_id,kode_barang,nama_barang,expired_date,no_lot,dimensi,qty,qty_pcs,qty_box,status,wilayah,tim_opname')
            ->from($this->manualMasterTable)
            ->where_in('status', ['Manual Input', 'Request Master Item']);
        if ($manualMasterId > 0) {
            $this->db->where('id', $manualMasterId);
        } else {
            $this->db
                ->where('kode_barang', $kodeBarang)
                ->where('expired_date', $expiredDate)
                ->where('no_lot', $noLot);
        }
        $request = $this->db->order_by('id', 'DESC')->limit(1)->get()->row_array();
        if (!$request) {
            return ['status' => false, 'message' => 'Request item tidak ditemukan.'];
        }

        $sourceId = (int)($request['source_id'] ?? 0);
        $dimension = (int)($request['dimensi'] ?? 0);
        if ($dimension <= 0) {
            $dimension = $this->master_dimensi_for_opname_row($request);
        }

        $status = (string)($request['status'] ?? '');
        $inputSource = $status === 'Request Master Item' ? 'master data request opname' : 'manual input';
        $row = [
            'source_id' => $sourceId,
            'kode_barang' => (string)$request['kode_barang'],
            'nama_barang' => (string)$request['nama_barang'],
            'expired_date' => (string)$request['expired_date'],
            'no_lot' => (string)$request['no_lot'],
            'qty_box' => (int)$payload['qty_box'],
            'qty_pcs' => (int)$payload['qty_pcs'],
            'qty' => ((int)$payload['qty_box'] * $dimension) + (int)$payload['qty_pcs'],
            'input_by' => (string)$actor,
            'input_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($payload['wilayah'] ?: $request['wilayah']),
            'tim_opname' => (int)$payload['tim_opname'],
            'scan_code' => null,
        ];
        if ($this->db->field_exists('input_source', $this->opnameTable)) {
            $row['input_source'] = $inputSource;
        }

        $this->db->trans_begin();
        $masterRequestId = 0;
        if ($status === 'Request Master Item') {
            $masterRequestId = $this->insert_zero_master_request_item($request, [
                'expired_date' => $row['expired_date'],
                'no_lot' => $row['no_lot'],
            ], $inputSource);
            if ($masterRequestId <= 0) {
                $this->db->trans_rollback();
                return ['status' => false, 'message' => 'Gagal menyimpan Request Master Item ke stockopname_master_item.'];
            }
            $row['source_id'] = $masterRequestId;
        }
        $this->db->insert($this->opnameTable, $row);
        $newId = (int)$this->db->insert_id();
        $newRow = $this->input_opname_row_by_id($newId) ?: array_merge($row, ['id' => $newId]);
        $description = $status === 'Request Master Item'
            ? 'Request Master Item ditambahkan ke master item dan hasil input opname.'
            : 'Manual Input ditambahkan ke data hasil input opname.';
        $this->insert_opname_edit_log([], $newRow, ['qty', 'qty_box', 'qty_pcs'], $actor, 'ADJUSTMENT', $description);
        $this->mark_manual_master_done((int)$request['id'], $actor);
        if ($this->db->table_exists($this->manualOpnameTable) && $this->db->field_exists('manual_master_id', $this->manualOpnameTable)) {
            $this->db->where('manual_master_id', (int)$request['id'])->delete($this->manualOpnameTable);
        }
        if ($this->db->trans_status() === false || $newId <= 0) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Gagal menambahkan request item. Transaksi dibatalkan.'];
        }
        $this->db->trans_commit();

        return ['status' => true, 'message' => 'Request item berhasil diproses dan status menjadi DONE.', 'data' => ['id' => $newId, 'master_id' => $masterRequestId, 'source_status' => $status]];
    }

    public function opname_edit_logs_by_kode_barang($kodeBarang, $limit = 20)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->ensure_opname_log_table()) {
            return [];
        }

        return $this->db
            ->from('stockopname_opname_log')
            ->where('kode_barang', $kodeBarang)
            ->order_by('COALESCE(created_at, changed_at)', 'DESC', false)
            ->order_by('id', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    private function percentage_result($match, $notMatch)
    {
        $match = (int)$match;
        $notMatch = (int)$notMatch;
        $total = $match + $notMatch;

        return [
            'total' => $total,
            'match' => $match,
            'not_match' => $notMatch,
            'persen_match' => $total > 0 ? round(($match / $total) * 100, 2) : 0,
            'persen_not' => $total > 0 ? round(($notMatch / $total) * 100, 2) : 0,
        ];
    }

    public function stockopname_result_summary($summary = null)
    {
        $summary = $summary ?: $this->summary();

        return $this->percentage_result(
            $summary['match_item'] ?? 0,
            $summary['selisih_item'] ?? 0
        );
    }

    public function all_barang_result_summary($summary = null)
    {
        if (!$this->ready()) {
            return $this->percentage_result(1, 2);
        }

        $masterPositiveWhere = $this->master_positive_sql();
        $sql = "
            SELECT
                SUM(CASE WHEN COALESCE(o.qty_fisik, 0) = m.qty_buku THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN COALESCE(o.qty_fisik, 0) = m.qty_buku THEN 0 ELSE 1 END) AS not_match_item
            FROM (
                SELECT
                    kode_barang,
                    SUM(COALESCE(qty, 0)) AS qty_buku
                FROM {$this->masterTable}
                WHERE {$masterPositiveWhere}
                GROUP BY kode_barang
            ) m
            LEFT JOIN (
                SELECT
                    kode_barang,
                    SUM(COALESCE(qty, 0)) AS qty_fisik
                FROM {$this->opnameTable}
                GROUP BY kode_barang
            ) o ON o.kode_barang = m.kode_barang
        ";
        $row = $this->db->query($sql)->row_array();

        return $this->percentage_result(
            $row['match_item'] ?? 0,
            $row['not_match_item'] ?? 0
        );
    }

    public function expired_lot_result_summary()
    {
        if (!$this->ready()) {
            return $this->percentage_result(1, 2);
        }

        $masterLot = $this->db->field_exists('no_lot', $this->masterTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->masterTable) ? 'nolot' : "''");
        $opnameLot = $this->db->field_exists('no_lot', $this->opnameTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->opnameTable) ? 'nolot' : "''");
        $masterPositiveWhere = $this->master_positive_sql();

        $sql = "
            SELECT
                SUM(CASE WHEN COALESCE(o.qty_fisik, 0) = m.qty_buku THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN COALESCE(o.qty_fisik, 0) = m.qty_buku THEN 0 ELSE 1 END) AS not_match_item
            FROM (
                SELECT
                    kode_barang,
                    {$this->exp_key('expired_date')} AS exp_key,
                    {$this->lot_key($masterLot)} AS lot_key,
                    SUM(COALESCE(qty, 0)) AS qty_buku
                FROM {$this->masterTable}
                WHERE {$masterPositiveWhere}
                GROUP BY kode_barang, exp_key, lot_key
            ) m
            LEFT JOIN (
                SELECT
                    kode_barang,
                    {$this->exp_key('expired_date')} AS exp_key,
                    {$this->lot_key($opnameLot)} AS lot_key,
                    SUM(COALESCE(qty, 0)) AS qty_fisik
                FROM {$this->opnameTable}
                GROUP BY kode_barang, exp_key, lot_key
            ) o
                ON o.kode_barang = m.kode_barang
                AND o.exp_key = m.exp_key
                AND o.lot_key = m.lot_key
        ";
        $row = $this->db->query($sql)->row_array();

        return $this->percentage_result(
            $row['match_item'] ?? 0,
            $row['not_match_item'] ?? 0
        );
    }

    public function fefo_result_summary()
    {
        return $this->expired_lot_result_summary();
    }

    public function demo_preview($input)
    {
        $qtyBuku = (int)($input['qty_buku'] ?? 0);
        $qtyFisik = (int)($input['qty_fisik'] ?? 0);
        $selisih = $qtyFisik - $qtyBuku;

        return [
            'kode_barang' => trim((string)($input['kode_barang'] ?? 'DEMO')),
            'nama_barang' => trim((string)($input['nama_barang'] ?? 'Barang demo')),
            'qty_buku' => $qtyBuku,
            'qty_fisik' => $qtyFisik,
            'selisih' => $selisih,
            'status_opname' => $selisih === 0 ? 'match' : 'selisih',
            'message' => $selisih === 0 ? 'Qty fisik sudah cocok dengan sistem.' : 'Ada selisih yang perlu dicek sebelum finalisasi.'
        ];
    }

    public function master_barang_summary()
    {
        $qrSummary = $this->qrcode_summary();

        return [
            'total_item' => $this->count_all_master_barang(),
            'qty_zero_item' => $this->count_all_master_barang('zero'),
            'qrcode_generated_item' => $qrSummary['done'],
            'qrcode_pending_item' => $qrSummary['pending'],
            'qrcode_failed_item' => $qrSummary['failed'],
            'source_table' => $this->masterTable,
            'mode' => 'database'
        ];
    }

    public function count_all_master_barang($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return 0;
        }

        $where = $qtyMode === 'zero' ? 'COALESCE(qty, 0) = 0' : 'COALESCE(qty, 0) > 0';
        $row = $this->db
            ->query("SELECT COUNT(*) AS total FROM (SELECT 1 FROM {$this->masterTable} WHERE {$where} GROUP BY nama_barang, expired_date, no_lot) grouped_master")
            ->row_array();

        return (int)($row['total'] ?? 0);
    }

    public function count_master_barang_by_qrcode_status($status)
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return 0;
        }

        if ($this->db->field_exists('qrcode_status', $this->masterTable)) {
            if ($status === 'generated') {
                return $this->qrcode_done_count();
            }

            if ($status === 'pending') {
                return $this->qrcode_pending_count();
            }

            if ($status === 'failed') {
                return $this->qrcode_failed_count();
            }
        }

        if (!$this->db->field_exists('qrcode', $this->masterTable)) {
            return 0;
        }

        $this->db->from($this->masterTable);
        $this->master_barang_positive_qty_filter();
        $this->master_barang_qrcode_filter($status);
        return (int)$this->db->count_all_results();
    }

    private function master_barang_positive_qty_filter()
    {
        $this->db->where('COALESCE(qty, 0) > 0', null, false);
    }

    private function master_barang_zero_qty_filter()
    {
        $this->db->where('COALESCE(qty, 0) = 0', null, false);
    }

    private function master_barang_qty_filter($qtyMode = 'positive')
    {
        if ($qtyMode === 'zero') {
            $this->master_barang_zero_qty_filter();
            return;
        }

        if ($qtyMode === 'positive') {
            $this->master_barang_positive_qty_filter();
        }
    }

    private function master_barang_select()
    {
        $legacyQrcode = $this->db->field_exists('qrcode', $this->masterTable) ? 'qrcode' : "''";
        $qrcode = $this->db->field_exists('qrcode_file', $this->masterTable)
            ? "COALESCE(NULLIF(qrcode_file, ''), NULLIF({$legacyQrcode}, ''), '') AS qrcode"
            : ($this->db->field_exists('qrcode', $this->masterTable) ? 'qrcode' : 'NULL AS qrcode');
        $barcode = $this->db->field_exists('barcode', $this->masterTable) ? 'barcode' : 'NULL AS barcode';
        $qrcodeValue = $this->db->field_exists('qrcode_value', $this->masterTable) ? 'qrcode_value' : 'NULL AS qrcode_value';
        $qrcodeFile = $this->db->field_exists('qrcode_file', $this->masterTable) ? 'qrcode_file' : 'NULL AS qrcode_file';
        $qrcodeStatus = $this->db->field_exists('qrcode_status', $this->masterTable) ? 'qrcode_status' : 'NULL AS qrcode_status';
        $qrcodeError = $this->db->field_exists('qrcode_error_message', $this->masterTable) ? 'qrcode_error_message' : 'NULL AS qrcode_error_message';
        $dimensi = $this->db->field_exists('dimensi', $this->masterTable)
            ? 'COALESCE(dimensi, 0) AS dimensi'
            : 'CASE WHEN COALESCE(qty_box, 0) > 0 THEN FLOOR((COALESCE(qty, 0) - COALESCE(qty_pcs, 0)) / qty_box) ELSE 0 END AS dimensi';

        $this->db->select('
            id,
            kode_barang,
            nama_barang,
            expired_date,
            no_lot,
            COALESCE(qty, 0) AS qty,
            COALESCE(qty_pcs, 0) AS qty_pcs,
            COALESCE(qty_box, 0) AS qty_box,
            ' . $dimensi . ',
            ' . $qrcode . ',
            ' . $barcode . ',
            ' . $qrcodeValue . ',
            ' . $qrcodeFile . ',
            ' . $qrcodeStatus . ',
            ' . $qrcodeError . '
        ', false);
        $this->db->from($this->masterTable);
    }

    private function master_barang_search($search)
    {
        if ($search === '') {
            return;
        }

        $this->db->group_start();
        $this->db->like('kode_barang', $search);
        $this->db->or_like('nama_barang', $search);
        $this->db->or_like('expired_date', $search);
        $this->db->or_like('no_lot', $search);
        if ($this->db->field_exists('qrcode', $this->masterTable)) {
            $this->db->or_like('qrcode', $search);
        }
        if ($this->db->field_exists('qrcode_file', $this->masterTable)) {
            $this->db->or_like('qrcode_file', $search);
        }
        if ($this->db->field_exists('qrcode_value', $this->masterTable)) {
            $this->db->or_like('qrcode_value', $search);
        }
        if ($this->db->field_exists('barcode', $this->masterTable)) {
            $this->db->or_like('barcode', $search);
        }
        $this->db->group_end();
    }

    private function master_barang_qrcode_filter($status)
    {
        if ($this->db->field_exists('qrcode_status', $this->masterTable)) {
            if ($status === 'generated') {
                $this->db->where('qrcode_status', 'DONE');
                return;
            }

            if ($status === 'pending') {
                $this->db->group_start();
                $this->db->where('qrcode_status', 'PENDING');
                $this->db->or_where('qrcode_status IS NULL', null, false);
                $this->db->group_end();
                return;
            }

            if ($status === 'failed') {
                $this->db->group_start();
                $this->db->where('qrcode_status', 'FAILED');
                $this->db->or_where('qrcode_retry_flag', 1);
                $this->db->group_end();
                return;
            }
        }

        if (!$this->db->field_exists('qrcode', $this->masterTable)) {
            return;
        }

        if ($status === 'generated') {
            $this->db->where("TRIM(COALESCE(qrcode, '')) <> ''", null, false);
            return;
        }

        if ($status === 'pending') {
            $this->db->where("TRIM(COALESCE(qrcode, '')) = ''", null, false);
        }
    }

    private function count_filtered_master_barang($search, $qrcodeStatus = '', $qtyMode = 'positive')
    {
        $this->db->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        $this->master_barang_search($search);
        $this->master_barang_qrcode_filter($qrcodeStatus);
        return (int)$this->db->count_all_results();
    }

    public function get_master_barang_datatable($post, $qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [
                'draw' => (int)($post['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];
        }

        $search = trim((string)($post['search']['value'] ?? $post['search'] ?? ''));
        $qrcodeStatus = trim((string)($post['qrcode_status'] ?? ''));
        $length = (int)($post['length'] ?? 10);
        $start = max(0, (int)($post['start'] ?? 0));

        $columns = [
            'nama_barang',
            'expired_date',
            'no_lot',
            'qty',
            'qty_pcs',
            'qty_box',
            'id',
        ];
        $orderIndex = (int)($post['order'][0]['column'] ?? 1);
        $orderColumn = $columns[$orderIndex] ?? 'nama_barang';
        $orderDir = strtolower((string)($post['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $this->master_barang_select();
        $this->master_barang_qty_filter($qtyMode);
        $this->master_barang_search($search);
        $this->master_barang_qrcode_filter($qrcodeStatus);
        $this->db->order_by($orderColumn, $orderDir, false);
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result_array();
        foreach ($rows as &$row) {
            $qrcode = trim((string)($row['qrcode'] ?? ''));
            $barcode = trim((string)($row['barcode'] ?? ''));
            $row['qrcode_ready'] = $qrcode !== '' && is_file(FCPATH . $qrcode) ? 1 : 0;
            $row['barcode_ready'] = $barcode !== '' && is_file(FCPATH . $barcode) ? 1 : 0;
        }
        unset($row);

        return [
            'draw' => (int)($post['draw'] ?? 1),
            'recordsTotal' => $this->count_all_master_barang($qtyMode),
            'recordsFiltered' => $this->count_filtered_master_barang($search, $qrcodeStatus, $qtyMode),
            'data' => $rows,
        ];
    }

    public function master_barang_datatable($post)
    {
        return $this->get_master_barang_datatable($post);
    }

    public function get_master_barang_by_id($id, $positiveQtyOnly = false)
    {
        $this->master_barang_select();
        if ($positiveQtyOnly === true) {
            $this->master_barang_qty_filter('positive');
        } elseif (is_string($positiveQtyOnly)) {
            $this->master_barang_qty_filter($positiveQtyOnly);
        }

        return $this->db
            ->where('id', (int)$id)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function get_all_master_barang_for_qrcode()
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $this->master_barang_select();
        $this->master_barang_positive_qty_filter();
        return $this->db
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_master_barang_print_assets($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $this->master_barang_select();
        $this->master_barang_qty_filter($qtyMode);
        $this->master_barang_qrcode_filter('generated');
        $this->db
            ->order_by('nama_barang', 'ASC')
            ->order_by('expired_date', 'ASC')
            ->order_by('no_lot', 'ASC')
            ->order_by('id', 'ASC');

        $rows = $this->db->get()->result_array();
        $printable = [];
        foreach ($rows as $row) {
            $qrcode = trim((string)($row['qrcode'] ?? ''));
            if ($qrcode !== '' && is_file(FCPATH . $qrcode)) {
                $printable[] = $row;
            }
        }

        return $printable;
    }

    public function get_master_barang_by_id_range($startId, $endId)
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $startId = max(1, (int)$startId);
        $endId = max($startId, (int)$endId);

        $this->master_barang_select();
        return $this->db
            ->where('id >=', $startId)
            ->where('id <=', $endId)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_master_barang_ids_with_positive_qty_pcs($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $this->db->select('id');
        $this->master_barang_qty_filter($qtyMode);
        $rows = $this->db
            ->where('COALESCE(qty_pcs, 0) > 0', null, false)
            ->order_by('id', 'ASC')
            ->get($this->masterTable)
            ->result_array();

        return array_map('intval', array_column($rows, 'id'));
    }

    public function get_master_barang_by_ids(array $ids, $qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable) || empty($ids)) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
        if (empty($ids)) {
            return [];
        }

        $this->master_barang_select();
        $this->master_barang_qty_filter($qtyMode);
        $rows = $this->db
            ->where_in('id', $ids)
            ->get()
            ->result_array();

        $position = array_flip($ids);
        usort($rows, function ($left, $right) use ($position) {
            return ($position[(int)$left['id']] ?? PHP_INT_MAX) <=> ($position[(int)$right['id']] ?? PHP_INT_MAX);
        });

        return $rows;
    }

    public function find_master_barang_for_opname($scanValue)
    {
        $scanValue = trim((string)$scanValue);
        if ($scanValue === '' || !$this->db->table_exists($this->masterTable)) {
            return null;
        }

        $conditions = [];
        if (preg_match('/^OP\|(.+)\|(\d+)$/', $scanValue, $matches)) {
            $conditions[] = '(id = ' . (int)$matches[2] . ' AND kode_barang = ' . $this->db->escape($matches[1]) . ')';
        }

        if (ctype_digit($scanValue)) {
            $conditions[] = 'id = ' . (int)$scanValue;
        }
        $conditions[] = 'kode_barang = ' . $this->db->escape($scanValue);
        if ($this->db->field_exists('qrcode', $this->masterTable)) {
            $conditions[] = 'qrcode = ' . $this->db->escape($scanValue);
        }
        if ($this->db->field_exists('qrcode_file', $this->masterTable)) {
            $conditions[] = 'qrcode_file = ' . $this->db->escape($scanValue);
        }
        if ($this->db->field_exists('qrcode_value', $this->masterTable)) {
            $conditions[] = 'qrcode_value = ' . $this->db->escape($scanValue);
        }

        $this->master_barang_select();
        $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);

        return $this->db
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function get_first_master_barang_by_kode($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->db->table_exists($this->masterTable)) {
            return null;
        }

        $this->master_barang_select();
        return $this->db
            ->where('kode_barang', $kodeBarang)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function manual_barang_options($term = '', $page = 1, $limit = 20)
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [
                'results' => [],
                'pagination' => ['more' => false],
            ];
        }

        $term = trim((string)$term);
        $page = max(1, (int)$page);
        $limit = max(1, min(50, (int)$limit));
        $offset = ($page - 1) * $limit;

        $this->db
            ->select('MIN(id) AS first_id, nama_barang, COUNT(*) AS total_lot', false)
            ->from($this->masterTable);
        if ($term !== '') {
            $this->db->group_start();
            $this->db->like('nama_barang', $term);
            $this->db->or_like('kode_barang', $term);
            $this->db->group_end();
        }
        $this->db
            ->group_by('nama_barang')
            ->order_by('nama_barang', 'ASC')
            ->limit($limit + 1, $offset);

        $rows = $this->db->get()->result_array();
        $more = count($rows) > $limit;
        if ($more) {
            array_pop($rows);
        }

        $results = [];
        foreach ($rows as $row) {
            $firstId = (int)($row['first_id'] ?? 0);
            $firstRow = $firstId > 0 ? $this->get_master_barang_by_id($firstId) : null;
            $kodeBarang = (string)($firstRow['kode_barang'] ?? '');
            $namaBarang = (string)($row['nama_barang'] ?? '');
            $results[] = [
                'id' => $kodeBarang,
                'text' => $namaBarang,
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'dimensi' => (int)($firstRow['dimensi'] ?? 0),
                'total_lot' => (int)($row['total_lot'] ?? 0),
            ];
        }

        return [
            'results' => $results,
            'pagination' => ['more' => $more],
        ];
    }

    public function manual_lot_options($kodeBarang)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $rows = $this->db
            ->select("CASE WHEN TRIM(COALESCE(no_lot, '')) = '' THEN '-' ELSE TRIM(no_lot) END AS no_lot", false)
            ->from($this->masterTable)
            ->where('kode_barang', $kodeBarang)
            ->group_by("CASE WHEN TRIM(COALESCE(no_lot, '')) = '' THEN '-' ELSE TRIM(no_lot) END", false)
            ->order_by('no_lot', 'ASC')
            ->get()
            ->result_array();

        $options = [];
        foreach ($rows as $row) {
            $lot = (string)($row['no_lot'] ?? '-');
            $options[] = [
                'id' => $lot,
                'text' => $lot,
            ];
        }

        return $options;
    }

    public function manual_expired_options($kodeBarang, $noLot)
    {
        $kodeBarang = trim((string)$kodeBarang);
        $noLot = trim((string)$noLot);
        if ($kodeBarang === '' || $noLot === '' || !$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $normalizedLot = $noLot === '-' ? ['', '-', '0'] : [$noLot];
        $this->master_barang_select();
        $this->db->where('kode_barang', $kodeBarang);
        $this->db->group_start();
        foreach ($normalizedLot as $index => $lot) {
            $condition = "TRIM(COALESCE(no_lot, '')) = " . $this->db->escape($lot);
            if ($index === 0) {
                $this->db->where($condition, null, false);
            } else {
                $this->db->or_where($condition, null, false);
            }
        }
        $this->db->group_end();
        $this->db
            ->order_by('expired_date', 'ASC')
            ->order_by('id', 'ASC');

        $rows = $this->db->get()->result_array();
        $options = [];
        $seen = [];
        foreach ($rows as $row) {
            $expired = (string)($row['expired_date'] ?? '');
            $key = $expired . '|' . (string)($row['no_lot'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $options[] = [
                'id' => (int)($row['id'] ?? 0),
                'text' => $expired,
                'expired_date' => $expired,
                'no_lot' => (string)($row['no_lot'] ?? '-'),
                'dimensi' => (int)($row['dimensi'] ?? 0),
            ];
        }

        return $options;
    }

    public function save_mobile_opname($masterRow, $input)
    {
        if (!$this->db->table_exists($this->opnameTable)) {
            return false;
        }

        $qtyPcs = (int)($input['qty_pcs'] ?? 0);
        $qtyBox = (int)($input['qty_box'] ?? 0);
        $dimensi = (int)($masterRow['dimensi'] ?? 0);
        if ($dimensi <= 0) {
            $masterQtyBox = (int)($masterRow['qty_box'] ?? 0);
            $masterQtyPcs = (int)($masterRow['qty_pcs'] ?? 0);
            $masterQty = (int)($masterRow['qty'] ?? 0);
            $dimensi = $masterQtyBox > 0 ? (int)floor(($masterQty - $masterQtyPcs) / $masterQtyBox) : 0;
        }

        $data = [
            'source_id' => (int)($masterRow['id'] ?? 0),
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'expired_date' => (string)($masterRow['expired_date'] ?? ''),
            'qty' => ($qtyBox * $dimensi) + $qtyPcs,
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'no_lot' => (string)($masterRow['no_lot'] ?? '-'),
            'input_by' => (string)($input['input_by'] ?? 'system'),
            'input_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($input['wilayah'] ?? 0),
            'tim_opname' => (int)($input['tim_opname'] ?? 0),
            'scan_code' => (string)($masterRow['qrcode'] ?? $masterRow['barcode'] ?? $masterRow['kode_barang'] ?? ''),
        ];

        if (!$this->db->insert($this->opnameTable, $data)) {
            return false;
        }

        return (int)$this->db->insert_id();
    }

    public function save_manual_master_item_queue($masterRow, $input, $status)
    {
        if (!$this->ensure_manual_tables()) {
            return [
                'status' => false,
                'message' => 'Tabel request item manual belum siap.',
            ];
        }

        $status = (string)$status;
        if (!in_array($status, ['Manual Input', 'Request Master Item'], true)) {
            return [
                'status' => false,
                'message' => 'Status request item tidak valid.',
            ];
        }

        $qtyPcs = (int)($input['qty_pcs'] ?? 0);
        $qtyBox = (int)($input['qty_box'] ?? 0);
        $dimensi = (int)($masterRow['dimensi'] ?? 0);
        if ($dimensi <= 0) {
            $masterQtyBox = (int)($masterRow['qty_box'] ?? 0);
            $masterQtyPcs = (int)($masterRow['qty_pcs'] ?? 0);
            $masterQty = (int)($masterRow['qty'] ?? 0);
            $dimensi = $masterQtyBox > 0 ? (int)floor(($masterQty - $masterQtyPcs) / $masterQtyBox) : 0;
        }

        $data = [
            'source_id' => (int)($masterRow['id'] ?? 0),
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'expired_date' => (string)($input['expired_date'] ?? $masterRow['expired_date'] ?? ''),
            'no_lot' => (string)($input['no_lot'] ?? $masterRow['no_lot'] ?? '-'),
            'dimensi' => $dimensi,
            'qty' => ($qtyBox * $dimensi) + $qtyPcs,
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'status' => $status,
            'requested_by' => (string)($input['input_by'] ?? 'system'),
            'requested_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($input['wilayah'] ?? 0),
            'tim_opname' => (int)($input['tim_opname'] ?? 0),
        ];

        if (!$this->db->insert($this->manualMasterTable, $data)) {
            return [
                'status' => false,
                'message' => 'Gagal menyimpan data ke stockopname_master_manual_item.',
            ];
        }

        return [
            'status' => true,
            'message' => $status === 'Manual Input'
                ? 'Data input manual berhasil disimpan sebagai Manual Input.'
                : 'Data opname request berhasil disimpan sebagai Request Master Item.',
            'data' => [
                'id' => (int)$this->db->insert_id(),
                'kode_barang' => $data['kode_barang'],
                'nama_barang' => $data['nama_barang'],
                'expired_date' => $data['expired_date'],
                'no_lot' => $data['no_lot'],
                'qty' => $data['qty'],
                'qty_pcs' => $qtyPcs,
                'qty_box' => $qtyBox,
                'status' => $status,
            ],
        ];
    }

    public function update_dimensi_by_kode_barang($kodeBarang, $dimensi)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->db->table_exists($this->masterTable)) {
            return false;
        }

        if (!$this->db->field_exists('dimensi', $this->masterTable)) {
            $this->db->query("ALTER TABLE {$this->masterTable} ADD `dimensi` INT(12) NOT NULL DEFAULT 0 AFTER `qty_box`");
        }

        return $this->db
            ->where('kode_barang', $kodeBarang)
            ->update($this->masterTable, ['dimensi' => max(0, (int)$dimensi)]);
    }

    public function delete_master_item_by_lot($kodeBarang, $expiredDate, $noLot)
    {
        $kodeBarang = trim((string)$kodeBarang);
        $expiredDate = trim((string)$expiredDate);
        $noLot = trim((string)$noLot);
        if ($kodeBarang === '' || $expiredDate === '' || $noLot === '' || !$this->db->table_exists($this->masterTable)) {
            return ['status' => false, 'message' => 'Data stock buku tidak valid.'];
        }

        $noLotColumn = $this->db->field_exists('no_lot', $this->masterTable) ? 'no_lot' : ($this->db->field_exists('nolot', $this->masterTable) ? 'nolot' : '');
        if ($noLotColumn === '') {
            return ['status' => false, 'message' => 'Kolom no lot master item tidak tersedia.'];
        }

        $rows = $this->db
            ->select("id,kode_barang,nama_barang,expired_date,{$noLotColumn} AS no_lot,qty", false)
            ->from($this->masterTable)
            ->where('kode_barang', $kodeBarang)
            ->where('expired_date', $expiredDate)
            ->where($noLotColumn, $noLot)
            ->get()
            ->result_array();
        if (!$rows) {
            return ['status' => false, 'message' => 'Data stock buku tidak ditemukan.'];
        }

        $ids = array_map('intval', array_column($rows, 'id'));
        $deleted = [
            $this->manualMasterTable => 0,
            $this->masterTable => 0,
            $this->opnameTable => 0,
            $this->manualOpnameTable => 0,
        ];

        $this->db->trans_start();

        $deleted[$this->manualOpnameTable] = $this->delete_rows_by_barang_expired_lot($this->manualOpnameTable, $kodeBarang, $expiredDate, $noLot);
        $deleted[$this->opnameTable] = $this->delete_rows_by_barang_expired_lot($this->opnameTable, $kodeBarang, $expiredDate, $noLot);
        $deleted[$this->manualMasterTable] = $this->delete_rows_by_barang_expired_lot($this->manualMasterTable, $kodeBarang, $expiredDate, $noLot);
        $deleted[$this->masterTable] = $this->delete_rows_by_barang_expired_lot($this->masterTable, $kodeBarang, $expiredDate, $noLot);

        $this->db->trans_complete();

        if (!$this->db->trans_status() || (int)$deleted[$this->masterTable] <= 0) {
            return ['status' => false, 'message' => 'Gagal menghapus data stock buku. Transaksi dibatalkan.'];
        }

        return [
            'status' => true,
            'message' => 'Data stock buku dan data opname lot terkait berhasil dihapus.',
            'data' => [
                'deleted' => (int)array_sum($deleted),
                'deleted_by_table' => $deleted,
                'ids' => $ids,
                'kode_barang' => $kodeBarang,
                'expired_date' => $expiredDate,
                'no_lot' => $noLot,
            ],
        ];
    }

    private function delete_rows_by_barang_expired_lot($table, $kodeBarang, $expiredDate, $noLot)
    {
        if (!$this->db->table_exists($table)
            || !$this->db->field_exists('kode_barang', $table)
            || !$this->db->field_exists('expired_date', $table)
        ) {
            return 0;
        }

        $noLotColumn = $this->db->field_exists('no_lot', $table) ? 'no_lot' : ($this->db->field_exists('nolot', $table) ? 'nolot' : '');
        if ($noLotColumn === '') {
            return 0;
        }

        $this->db
            ->where('kode_barang', $kodeBarang)
            ->where('expired_date', $expiredDate)
            ->where($noLotColumn, $noLot)
            ->delete($table);

        return max(0, (int)$this->db->affected_rows());
    }

    private function manual_master_item_id($masterRow, $inputBy)
    {
        if (!$this->ensure_manual_tables()) {
            return 0;
        }

        $sourceId = (int)($masterRow['id'] ?? 0);
        $existing = $this->db
            ->select('id')
            ->from($this->manualMasterTable)
            ->where('source_id', $sourceId)
            ->where('expired_date', (string)($masterRow['expired_date'] ?? ''))
            ->where('no_lot', (string)($masterRow['no_lot'] ?? '-'))
            ->where('status', 'PENDING')
            ->limit(1)
            ->get()
            ->row_array();
        if ($existing) {
            return (int)$existing['id'];
        }

        $data = [
            'source_id' => $sourceId,
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'expired_date' => (string)($masterRow['expired_date'] ?? ''),
            'no_lot' => (string)($masterRow['no_lot'] ?? '-'),
            'dimensi' => (int)($masterRow['dimensi'] ?? 0),
            'status' => 'PENDING',
            'requested_by' => (string)$inputBy,
            'requested_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->db->insert($this->manualMasterTable, $data)) {
            return 0;
        }

        return (int)$this->db->insert_id();
    }

    private function opname_payload_from_master($masterRow, $input, $inputSource)
    {
        $qtyPcs = (int)($input['qty_pcs'] ?? 0);
        $qtyBox = (int)($input['qty_box'] ?? 0);
        $dimensi = (int)($masterRow['dimensi'] ?? 0);
        if ($dimensi <= 0) {
            $masterQtyBox = (int)($masterRow['qty_box'] ?? 0);
            $masterQtyPcs = (int)($masterRow['qty_pcs'] ?? 0);
            $masterQty = (int)($masterRow['qty'] ?? 0);
            $dimensi = $masterQtyBox > 0 ? (int)floor(($masterQty - $masterQtyPcs) / $masterQtyBox) : 0;
        }

        $data = [
            'source_id' => (int)($masterRow['id'] ?? 0),
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'expired_date' => (string)($masterRow['expired_date'] ?? ''),
            'no_lot' => (string)($masterRow['no_lot'] ?? '-'),
            'qty' => ($qtyBox * $dimensi) + $qtyPcs,
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'input_by' => (string)($input['input_by'] ?? 'system'),
            'input_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($input['wilayah'] ?? 0),
            'tim_opname' => (int)($input['tim_opname'] ?? 0),
            'scan_code' => (string)($masterRow['qrcode'] ?? $masterRow['barcode'] ?? $masterRow['kode_barang'] ?? ''),
        ];

        if ($this->db->field_exists('input_source', $this->opnameTable)) {
            $data['input_source'] = $inputSource;
        }

        return $data;
    }

    private function mark_manual_master_done($manualMasterId, $actor)
    {
        if ((int)$manualMasterId <= 0 || !$this->db->table_exists($this->manualMasterTable)) {
            return false;
        }

        $data = ['status' => 'DONE'];
        if ($this->db->field_exists('reviewed_by', $this->manualMasterTable)) {
            $data['reviewed_by'] = (string)$actor;
        }
        if ($this->db->field_exists('reviewed_at', $this->manualMasterTable)) {
            $data['reviewed_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('review_note', $this->manualMasterTable)) {
            $data['review_note'] = 'Data request sudah tersimpan ke stockopname_opname.';
        }

        return $this->db->where('id', (int)$manualMasterId)->update($this->manualMasterTable, $data);
    }

    public function save_manual_request_to_opname($masterRow, $input)
    {
        if (!$this->ensure_manual_tables() || !$this->ensure_opname_input_source_column()) {
            return ['status' => false, 'message' => 'Tabel input opname belum siap.'];
        }

        $inputBy = (string)($input['input_by'] ?? 'system');
        $inputSource = 'manual opname request';
        $this->db->trans_start();
        $manualMasterId = $this->manual_master_item_id($masterRow, $inputBy);
        if ($manualMasterId <= 0) {
            $this->db->trans_complete();
            return ['status' => false, 'message' => 'Gagal menyiapkan request manual.'];
        }

        $manualData = [
            'manual_master_id' => $manualMasterId,
            'source_id' => (int)($masterRow['id'] ?? 0),
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'expired_date' => (string)($masterRow['expired_date'] ?? ''),
            'no_lot' => (string)($masterRow['no_lot'] ?? '-'),
            'qty' => (int)$this->opname_payload_from_master($masterRow, $input, $inputSource)['qty'],
            'qty_pcs' => (int)($input['qty_pcs'] ?? 0),
            'qty_box' => (int)($input['qty_box'] ?? 0),
            'input_by' => $inputBy,
            'input_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($input['wilayah'] ?? 0),
            'tim_opname' => (int)($input['tim_opname'] ?? 0),
            'input_source' => $inputSource,
        ];
        $this->db->insert($this->manualOpnameTable, $manualData);

        $opnameData = $this->opname_payload_from_master($masterRow, $input, $inputSource);
        $this->db->insert($this->opnameTable, $opnameData);
        $opnameId = (int)$this->db->insert_id();
        $this->mark_manual_master_done($manualMasterId, $inputBy);
        $this->db->trans_complete();

        if (!$this->db->trans_status() || $opnameId <= 0) {
            return ['status' => false, 'message' => 'Gagal menyimpan request manual ke hasil opname.'];
        }

        return [
            'status' => true,
            'message' => 'Data manual opname request berhasil disimpan dan status menjadi DONE.',
            'data' => ['id' => $opnameId, 'input_source' => $inputSource],
        ];
    }

    public function save_manual_opname($masterRow, $input)
    {
        if (!$this->ensure_manual_tables()) {
            return [
                'status' => false,
                'message' => 'Tabel opname manual belum siap.',
            ];
        }

        $qtyPcs = (int)($input['qty_pcs'] ?? 0);
        $qtyBox = (int)($input['qty_box'] ?? 0);
        $dimensi = (int)($masterRow['dimensi'] ?? 0);
        if ($dimensi <= 0) {
            $masterQtyBox = (int)($masterRow['qty_box'] ?? 0);
            $masterQtyPcs = (int)($masterRow['qty_pcs'] ?? 0);
            $masterQty = (int)($masterRow['qty'] ?? 0);
            $dimensi = $masterQtyBox > 0 ? (int)floor(($masterQty - $masterQtyPcs) / $masterQtyBox) : 0;
        }

        $inputBy = (string)($input['input_by'] ?? 'system');
        $this->db->trans_start();
        $manualMasterId = $this->manual_master_item_id($masterRow, $inputBy);
        if ($manualMasterId <= 0) {
            $this->db->trans_complete();
            return [
                'status' => false,
                'message' => 'Gagal menyiapkan master item manual.',
            ];
        }

        $data = [
            'manual_master_id' => $manualMasterId,
            'source_id' => (int)($masterRow['id'] ?? 0),
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'expired_date' => (string)($masterRow['expired_date'] ?? ''),
            'no_lot' => (string)($masterRow['no_lot'] ?? '-'),
            'qty' => ($qtyBox * $dimensi) + $qtyPcs,
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'input_by' => $inputBy,
            'input_at' => date('Y-m-d H:i:s'),
            'wilayah' => (int)($input['wilayah'] ?? 0),
            'tim_opname' => (int)($input['tim_opname'] ?? 0),
            'input_source' => in_array((string)($input['input_source'] ?? 'manual'), ['manual', 'request', 'adjustment', 'repost', 'system'], true)
                ? (string)$input['input_source']
                : 'manual',
        ];

        $this->db->insert($this->manualOpnameTable, $data);
        $manualOpnameId = (int)$this->db->insert_id();
        $this->db->trans_complete();

        if (!$this->db->trans_status() || $manualOpnameId <= 0) {
            return [
                'status' => false,
                'message' => 'Gagal menyimpan data opname manual.',
            ];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $manualOpnameId,
                'manual_master_id' => $manualMasterId,
                'kode_barang' => $data['kode_barang'],
                'nama_barang' => $data['nama_barang'],
                'qty_pcs' => $qtyPcs,
                'qty_box' => $qtyBox,
                'input_source' => $data['input_source'],
            ],
        ];
    }

    public function save_request_opname($masterRow, $input)
    {
        $requestRow = $masterRow;
        $requestRow['expired_date'] = (string)($input['expired_date'] ?? $masterRow['expired_date'] ?? '');
        $requestRow['no_lot'] = (string)($input['no_lot'] ?? $masterRow['no_lot'] ?? '-');
        $input['input_source'] = 'request';
        return $this->save_manual_opname($requestRow, $input);
    }

    private function insert_zero_master_request_item($masterRow, $input, $inputSource)
    {
        if (!$this->ensure_master_input_status_columns()) {
            return 0;
        }

        $available = array_map(function ($field) {
            return $field->name;
        }, $this->db->field_data($this->masterTable));
        $allowed = array_flip($available);
        $data = [
            'kode_barang' => (string)($masterRow['kode_barang'] ?? ''),
            'nama_barang' => (string)($masterRow['nama_barang'] ?? ''),
            'qty' => 0,
            'qty_box' => 0,
            'qty_pcs' => 0,
            'dimensi' => (int)($masterRow['dimensi'] ?? 0),
            'expired_date' => (string)($input['expired_date'] ?? ''),
            'no_lot' => (string)($input['no_lot'] ?? '-'),
            'qrcode' => null,
            'barcode' => null,
            'input_source' => $inputSource,
            'request_status' => 'DONE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $insert = [];
        foreach ($data as $field => $value) {
            if (isset($allowed[$field])) {
                $insert[$field] = $value;
            }
        }

        if (!$this->db->insert($this->masterTable, $insert)) {
            return 0;
        }

        return (int)$this->db->insert_id();
    }

    public function save_master_request_to_opname($masterRow, $input)
    {
        if (!$this->ensure_opname_input_source_column() || !$this->ensure_master_input_status_columns()) {
            return ['status' => false, 'message' => 'Tabel master atau opname belum siap.'];
        }

        $inputSource = 'master data request opname';
        $requestRow = $masterRow;
        $requestRow['expired_date'] = (string)($input['expired_date'] ?? $masterRow['expired_date'] ?? '');
        $requestRow['no_lot'] = (string)($input['no_lot'] ?? $masterRow['no_lot'] ?? '-');

        $this->db->trans_start();
        $masterRequestId = $this->insert_zero_master_request_item($masterRow, [
            'expired_date' => $requestRow['expired_date'],
            'no_lot' => $requestRow['no_lot'],
        ], $inputSource);
        if ($masterRequestId > 0) {
            $requestRow['id'] = $masterRequestId;
        }

        $opnameData = $this->opname_payload_from_master($requestRow, $input, $inputSource);
        $this->db->insert($this->opnameTable, $opnameData);
        $opnameId = (int)$this->db->insert_id();
        $this->db->trans_complete();

        if (!$this->db->trans_status() || $masterRequestId <= 0 || $opnameId <= 0) {
            return ['status' => false, 'message' => 'Gagal menyimpan opname request ke master dan hasil opname.'];
        }

        return [
            'status' => true,
            'message' => 'Opname request berhasil disimpan dan status menjadi DONE.',
            'data' => ['id' => $opnameId, 'master_id' => $masterRequestId, 'input_source' => $inputSource],
        ];
    }

    public function delete_request_item_group($kodeBarang, $expiredDate, $noLot, $actor, $manualMasterId = 0)
    {
        $kodeBarang = trim((string)$kodeBarang);
        $expiredDate = trim((string)$expiredDate);
        $noLot = trim((string)$noLot);
        if ($kodeBarang === '' || $expiredDate === '' || $noLot === '' || !$this->ensure_manual_tables()) {
            return ['status' => false, 'message' => 'Data request item tidak valid.'];
        }

        $rows = $this->db
            ->select('id')
            ->from($this->manualMasterTable)
            ->where_in('status', ['Manual Input', 'Request Master Item']);
        if ((int)$manualMasterId > 0) {
            $this->db->where('id', (int)$manualMasterId);
        } else {
            $this->db
                ->where('kode_barang', $kodeBarang)
                ->where('expired_date', $expiredDate)
                ->where('no_lot', $noLot);
        }
        $rows = $this->db->get()->result_array();
        if (!$rows) {
            return ['status' => false, 'message' => 'Request item tidak ditemukan.'];
        }

        $ids = array_map('intval', array_column($rows, 'id'));

        $this->db->trans_start();
        $update = ['status' => 'REJECTED'];
        if ($this->db->field_exists('reviewed_by', $this->manualMasterTable)) {
            $update['reviewed_by'] = (string)$actor;
        }
        if ($this->db->field_exists('reviewed_at', $this->manualMasterTable)) {
            $update['reviewed_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('review_note', $this->manualMasterTable)) {
            $update['review_note'] = 'Request item dihapus dari detail input opname.';
        }
        $this->db->where_in('id', $ids)->update($this->manualMasterTable, $update);
        if ($this->db->table_exists($this->manualOpnameTable) && $this->db->field_exists('manual_master_id', $this->manualOpnameTable)) {
            $this->db->where_in('manual_master_id', $ids)->delete($this->manualOpnameTable);
        }
        $this->db->trans_complete();

        return $this->db->trans_status()
            ? ['status' => true, 'message' => 'Request item berhasil dihapus.', 'data' => ['deleted' => count($ids)]]
            : ['status' => false, 'message' => 'Gagal menghapus request item.'];
    }

    public function history_input_by($inputBy, $limit = 100)
    {
        $inputBy = trim((string)$inputBy);
        if ($inputBy === '' || !$this->db->table_exists($this->opnameTable)) {
            return [];
        }

        return $this->db
            ->select('id,kode_barang,nama_barang,expired_date,no_lot,qty,qty_pcs,qty_box,input_by,input_at,wilayah,created_at AS create_at')
            ->from($this->opnameTable)
            ->where('input_by', $inputBy)
            ->order_by('created_at', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();
    }

    public function update_master_barang($id, $data)
    {
        return $this->db
            ->where('id', (int)$id)
            ->update($this->masterTable, $data);
    }

    public function update_asset_master_barang($id, $data)
    {
        $allowed = [];
        if (isset($data['qrcode'])) {
            $allowed['qrcode'] = $data['qrcode'];
        }
        if (isset($data['barcode'])) {
            $allowed['barcode'] = $data['barcode'];
        }

        if (empty($allowed)) {
            return false;
        }

        return $this->db
            ->where('id', (int)$id)
            ->update($this->masterTable, $allowed);
    }

    public function qrcode_summary($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [
                'total' => 0,
                'done' => 0,
                'pending' => 0,
                'failed' => 0,
            ];
        }

        if (!$this->db->field_exists('qrcode_status', $this->masterTable)) {
            $total = $this->qrcode_total_count($qtyMode);
            return [
                'total' => $total,
                'done' => 0,
                'pending' => $total,
                'failed' => 0,
            ];
        }

        $this->db
            ->select("
                COUNT(*) AS total,
                SUM(CASE WHEN qrcode_status = 'DONE' THEN 1 ELSE 0 END) AS done,
                SUM(CASE WHEN qrcode_status = 'FAILED' OR qrcode_retry_flag = 1 THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN qrcode_status IS NULL OR qrcode_status IN ('PENDING','PROCESS') THEN 1 ELSE 0 END) AS pending
            ", false)
            ->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        $row = $this->db->get()->row_array();

        return [
            'total' => (int)($row['total'] ?? 0),
            'done' => (int)($row['done'] ?? 0),
            'pending' => (int)($row['pending'] ?? 0),
            'failed' => (int)($row['failed'] ?? 0),
        ];
    }

    public function qrcode_total_count($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return 0;
        }

        $this->db->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        return (int)$this->db->count_all_results();
    }

    public function qrcode_done_count($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable) || !$this->db->field_exists('qrcode_status', $this->masterTable)) {
            return 0;
        }

        $this->db->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        $this->db->where('qrcode_status', 'DONE');
        return (int)$this->db->count_all_results();
    }

    public function qrcode_pending_count($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return 0;
        }

        if (!$this->db->field_exists('qrcode_status', $this->masterTable)) {
            return $this->qrcode_total_count($qtyMode);
        }

        $this->db->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        $this->db->group_start();
        $this->db->where('qrcode_status', 'PENDING');
        $this->db->or_where('qrcode_status', 'PROCESS');
        $this->db->or_where('qrcode_status IS NULL', null, false);
        $this->db->group_end();
        return (int)$this->db->count_all_results();
    }

    public function qrcode_failed_count($qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable) || !$this->db->field_exists('qrcode_status', $this->masterTable)) {
            return 0;
        }

        $this->db->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        $this->db->group_start();
        $this->db->where('qrcode_status', 'FAILED');
        $this->db->or_where('qrcode_retry_flag', 1);
        $this->db->group_end();
        return (int)$this->db->count_all_results();
    }

    public function get_qrcode_batch($limit = 100, $mode = 'normal', $qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $this->master_barang_select();
        $this->master_barang_qty_filter($qtyMode);
        if ($mode === 'retry') {
            $this->db->where('qrcode_status', 'FAILED');
            $this->db->where('qrcode_retry_flag', 1);
        } else {
            $this->db->group_start();
            $this->db->where('qrcode_status', 'PENDING');
            $this->db->or_where('qrcode_status IS NULL', null, false);
            $this->db->group_end();
        }

        return $this->db
            ->order_by('id', 'ASC')
            ->limit(max(1, min(100, (int)$limit)))
            ->get()
            ->result_array();
    }

    public function reset_stale_qrcode_process($minutes = 2)
    {
        if (!$this->db->table_exists($this->masterTable) || !$this->db->field_exists('qrcode_status', $this->masterTable)) {
            return 0;
        }

        $minutes = (int)$minutes;
        $threshold = date('Y-m-d H:i:s', time() - (max(1, $minutes) * 60));
        $this->db
            ->set('qrcode_status', 'PENDING')
            ->set('qrcode_updated_at', date('Y-m-d H:i:s'))
            ->where('qrcode_status', 'PROCESS');

        if ($minutes > 0) {
            $this->db
                ->group_start()
                ->where('qrcode_updated_at IS NULL', null, false)
                ->or_where('qrcode_updated_at <', $threshold)
                ->group_end();
        }

        $this->db->update($this->masterTable);

        return (int)$this->db->affected_rows();
    }

    public function mark_qrcode_process($id)
    {
        return $this->db
            ->where('id', (int)$id)
            ->update($this->masterTable, [
                'qrcode_status' => 'PROCESS',
                'qrcode_updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function mark_qrcode_success($id, $value, $file)
    {
        $data = [
            'qrcode_value' => $value,
            'qrcode_file' => $file,
            'qrcode_status' => 'DONE',
            'qrcode_retry_flag' => 0,
            'qrcode_error_message' => null,
            'qrcode_generated_at' => date('Y-m-d H:i:s'),
            'qrcode_updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('qrcode', $this->masterTable)) {
            $data['qrcode'] = $file;
        }

        return $this->db
            ->where('id', (int)$id)
            ->update($this->masterTable, $data);
    }

    public function mark_qrcode_failed($id, $message)
    {
        return $this->db
            ->set('qrcode_status', 'FAILED')
            ->set('qrcode_retry_flag', 1)
            ->set('qrcode_attempt_count', 'COALESCE(qrcode_attempt_count, 0) + 1', false)
            ->set('qrcode_error_message', substr((string)$message, 0, 1000))
            ->set('qrcode_updated_at', date('Y-m-d H:i:s'))
            ->where('id', (int)$id)
            ->update($this->masterTable);
    }

    public function failed_qrcode_list($limit = 100, $qtyMode = 'positive')
    {
        if (!$this->db->table_exists($this->masterTable) || !$this->db->field_exists('qrcode_status', $this->masterTable)) {
            return [];
        }

        $this->db
            ->select('id,kode_barang,qrcode_error_message,qrcode_attempt_count,qrcode_updated_at')
            ->from($this->masterTable);
        $this->master_barang_qty_filter($qtyMode);
        return $this->db
            ->group_start()
            ->where('qrcode_status', 'FAILED')
            ->or_where('qrcode_retry_flag', 1)
            ->group_end()
            ->order_by('qrcode_updated_at', 'DESC')
            ->limit(max(1, min(500, (int)$limit)))
            ->get()
            ->result_array();
    }

    public function qrcode_file_paths_for_reset()
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [];
        }

        $fields = [];
        if ($this->db->field_exists('qrcode', $this->masterTable)) {
            $fields[] = 'qrcode';
        }
        if ($this->db->field_exists('qrcode_file', $this->masterTable)) {
            $fields[] = 'qrcode_file';
        }

        if (empty($fields)) {
            return [];
        }

        $this->db->select(implode(',', $fields));
        $rows = $this->db->get($this->masterTable)->result_array();
        $paths = [];

        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $path = trim((string)($row[$field] ?? ''));
                if ($path !== '' && $path !== '-') {
                    $paths[$path] = $path;
                }
            }
        }

        return array_values($paths);
    }

    public function reset_qrcode_opname_data()
    {
        if (!$this->db->table_exists($this->masterTable)) {
            return [
                'success' => false,
                'message' => 'Tabel stockopname_master_item belum tersedia.',
            ];
        }

        $opnameRows = $this->db->table_exists($this->opnameTable)
            ? (int)$this->db->count_all($this->opnameTable)
            : 0;

        $data = [];
        foreach (['qrcode', 'qrcode_value', 'qrcode_file', 'qrcode_error_message', 'qrcode_generated_at', 'qrcode_updated_at'] as $field) {
            if ($this->db->field_exists($field, $this->masterTable)) {
                $data[$field] = null;
            }
        }
        if ($this->db->field_exists('qrcode_status', $this->masterTable)) {
            $data['qrcode_status'] = 'PENDING';
        }
        if ($this->db->field_exists('qrcode_retry_flag', $this->masterTable)) {
            $data['qrcode_retry_flag'] = 0;
        }
        if ($this->db->field_exists('qrcode_attempt_count', $this->masterTable)) {
            $data['qrcode_attempt_count'] = 0;
        }

        $this->db->trans_begin();

        if ($this->db->table_exists($this->opnameTable)) {
            $this->db->empty_table($this->opnameTable);
            $this->db->query("ALTER TABLE {$this->opnameTable} AUTO_INCREMENT = 1");
        }

        if (!empty($data)) {
            $this->db->update($this->masterTable, $data);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'message' => 'Gagal reset data QRCode opname.',
            ];
        }

        $this->db->trans_commit();

        return [
            'success' => true,
            'opname_rows_deleted' => $opnameRows,
            'master_rows_reset' => $this->qrcode_total_count(),
        ];
    }

    public function is_asset_exists($id, $type)
    {
        $field = $type === 'barcode' ? 'barcode' : 'qrcode';
        $row = $this->db
            ->select($field)
            ->from($this->masterTable)
            ->where('id', (int)$id)
            ->limit(1)
            ->get()
            ->row_array();

        $path = trim((string)($row[$field] ?? ''));
        return $path !== '' && is_file(FCPATH . $path);
    }
}
