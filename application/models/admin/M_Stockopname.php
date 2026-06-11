<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Stockopname extends CI_Model
{
    private $masterTable = 'stockopname_master_item';
    private $opnameTable = 'stockopname_opname';
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
        return "
            SELECT
                kode_barang,
                MAX(nama_barang) AS nama_barang,
                SUM(COALESCE(qty, 0)) AS qty_buku,
                SUM(COALESCE(qty_box, 0)) AS box_buku,
                SUM(COALESCE(qty_pcs, 0)) AS pcs_buku
            FROM {$this->masterTable}
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
            ) x
        ";
    }

    private function monitoring_master_lot_subquery()
    {
        $expKey = $this->exp_key('expired_date');
        $lotKey = $this->lot_key('no_lot');

        return "
            SELECT
                kode_barang,
                MAX(nama_barang) AS nama_barang,
                {$expKey} AS exp_key,
                {$lotKey} AS lot_key,
                MAX(expired_date) AS expired_date,
                MAX(no_lot) AS no_lot,
                SUM(COALESCE(qty, 0)) AS qty_buku,
                SUM(COALESCE(qty_box, 0)) AS box_buku,
                SUM(COALESCE(qty_pcs, 0)) AS pcs_buku
            FROM {$this->masterTable}
            GROUP BY kode_barang, exp_key, lot_key
        ";
    }

    private function monitoring_opname_lot_subquery()
    {
        $expKey = $this->exp_key('expired_date');
        $lotKey = $this->lot_key('no_lot');
        $createdColumn = $this->opname_created_column();

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
                MAX({$createdColumn}) AS last_input
            FROM {$this->opnameTable}
            GROUP BY kode_barang, exp_key, lot_key
        ";
    }

    private function monitoring_compare_lot_base()
    {
        $master = $this->monitoring_master_lot_subquery();
        $opname = $this->monitoring_opname_lot_subquery();

        return "
            SELECT
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
                    m.expired_date,
                    m.no_lot,
                    COALESCE(m.qty_buku, 0) AS qty_buku,
                    COALESCE(o.qty_tim_1, 0) AS qty_tim_1,
                    COALESCE(o.qty_tim_2, 0) AS qty_tim_2,
                    COALESCE(o.input_tim_1, 0) AS input_tim_1,
                    COALESCE(o.input_tim_2, 0) AS input_tim_2,
                    COALESCE(o.inputers, '-') AS inputers,
                    COALESCE(o.wilayah, '-') AS wilayah,
                    o.last_input
                FROM ({$master}) m
                LEFT JOIN ({$opname}) o
                    ON o.kode_barang = m.kode_barang
                    AND o.exp_key = m.exp_key
                    AND o.lot_key = m.lot_key
                UNION ALL
                SELECT
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
                    o.last_input
                FROM ({$opname}) o
                LEFT JOIN ({$master}) m
                    ON m.kode_barang = o.kode_barang
                    AND m.exp_key = o.exp_key
                    AND m.lot_key = o.lot_key
                WHERE m.kode_barang IS NULL
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
                'source_table' => $this->masterTable . ' / ' . $this->opnameTable,
            ];
        }

        return [
            'team_1' => $this->monitoring_team_result_summary(1),
            'team_2' => $this->monitoring_team_result_summary(2),
            'source_table' => $this->masterTable . ' / ' . $this->opnameTable,
        ];
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

        return $this->db
            ->select("id,kode_barang,nama_barang,expired_date,{$noLotColumn},qty,qty_pcs,qty_box", false)
            ->from($this->masterTable)
            ->where('kode_barang', $kodeBarang)
            ->order_by('expired_date', 'ASC')
            ->order_by('no_lot', 'ASC')
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

    private function ensure_opname_log_table()
    {
        if ($this->db->table_exists('stockopname_opname_log')) {
            return true;
        }

        return $this->db->query("
            CREATE TABLE IF NOT EXISTS `stockopname_opname_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `opname_id` INT(11) NOT NULL,
                `kode_barang` VARCHAR(50) NOT NULL,
                `action` VARCHAR(30) NOT NULL DEFAULT 'UPDATE',
                `changed_fields` TEXT NULL,
                `old_data` LONGTEXT NULL,
                `new_data` LONGTEXT NULL,
                `changed_by` VARCHAR(100) NOT NULL,
                `changed_at` DATETIME NOT NULL,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` VARCHAR(255) NULL,
                PRIMARY KEY (`id`),
                KEY `idx_stockopname_opname_log_barang` (`kode_barang`),
                KEY `idx_stockopname_opname_log_opname` (`opname_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
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

    private function opname_dimensi_from_row($row)
    {
        $sourceId = (int)($row['source_id'] ?? 0);
        if ($sourceId > 0 && $this->db->table_exists($this->masterTable)) {
            $dimensi = $this->db->field_exists('dimensi', $this->masterTable)
                ? 'COALESCE(dimensi, 0) AS dimensi'
                : 'CASE WHEN COALESCE(qty_box, 0) > 0 THEN FLOOR((COALESCE(qty, 0) - COALESCE(qty_pcs, 0)) / qty_box) ELSE 0 END AS dimensi';
            $master = $this->db
                ->select($dimensi, false)
                ->from($this->masterTable)
                ->where('id', $sourceId)
                ->limit(1)
                ->get()
                ->row_array();
            if ((int)($master['dimensi'] ?? 0) > 0) {
                return (int)$master['dimensi'];
            }
        }

        $qtyBox = (int)($row['qty_box'] ?? 0);
        if ($qtyBox <= 0) {
            return 0;
        }

        return max(0, (int)floor(((int)($row['qty'] ?? 0) - (int)($row['qty_pcs'] ?? 0)) / $qtyBox));
    }

    private function insert_opname_edit_log($oldRow, $newRow, $changedFields, $actor)
    {
        if (!$this->ensure_opname_log_table()) {
            return false;
        }

        return $this->db->insert('stockopname_opname_log', [
            'opname_id' => (int)($oldRow['id'] ?? 0),
            'kode_barang' => (string)($oldRow['kode_barang'] ?? $newRow['kode_barang'] ?? ''),
            'action' => 'UPDATE',
            'changed_fields' => json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE),
            'old_data' => json_encode($this->normalize_opname_snapshot($oldRow), JSON_UNESCAPED_UNICODE),
            'new_data' => json_encode($this->normalize_opname_snapshot($newRow), JSON_UNESCAPED_UNICODE),
            'changed_by' => (string)$actor,
            'changed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string)$this->input->user_agent(), 0, 255),
        ]);
    }

    public function update_input_opname($id, $kodeBarang, $payload, $actor)
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
        $this->insert_opname_edit_log($oldRow, $newRow ?: array_merge($oldRow, $after), $changedFields, $actor);
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

    public function opname_edit_logs_by_kode_barang($kodeBarang, $limit = 20)
    {
        $kodeBarang = trim((string)$kodeBarang);
        if ($kodeBarang === '' || !$this->ensure_opname_log_table()) {
            return [];
        }

        return $this->db
            ->from('stockopname_opname_log')
            ->where('kode_barang', $kodeBarang)
            ->order_by('changed_at', 'DESC')
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

        $sql = "
            SELECT
                SUM(CASE WHEN COALESCE(o.qty_fisik, 0) = m.qty_buku THEN 1 ELSE 0 END) AS match_item,
                SUM(CASE WHEN COALESCE(o.qty_fisik, 0) = m.qty_buku THEN 0 ELSE 1 END) AS not_match_item
            FROM (
                SELECT
                    kode_barang,
                    SUM(COALESCE(qty, 0)) AS qty_buku
                FROM {$this->masterTable}
                WHERE COALESCE(qty, 0) <> 0
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
                WHERE COALESCE(qty, 0) <> 0
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
