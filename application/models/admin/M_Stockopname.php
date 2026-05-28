<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Stockopname extends CI_Model
{
    private $masterTable = 'stockopname_master';
    private $opnameTable = 'stockopname_opname';
    private $masterBarangAllTable = 'tb_master_barang_all';

    private function ready()
    {
        return $this->db->table_exists($this->masterTable) && $this->db->table_exists($this->opnameTable);
    }

    private function exp_key($field)
    {
        return "DATE_FORMAT(STR_TO_DATE({$field}, '%d/%m/%Y'), '%Y-%m-%d')";
    }

    private function lot_key($field)
    {
        return "CASE WHEN TRIM(COALESCE({$field}, '')) IN ('', '-', '0') THEN '-' ELSE TRIM({$field}) END";
    }

    private function opname_subquery()
    {
        $expKey = $this->exp_key('expired_date');
        $lotKey = $this->lot_key('nolot');

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
                MAX(create_at) AS last_input
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
            ->select('kode_barang,nama_barang,expired_date,nolot AS no_lot,qty,qty_box,qty_pcs,input_by,wilayah,input_at,create_at')
            ->from($this->opnameTable)
            ->order_by('create_at', 'DESC')
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
        return [
            'total_item' => $this->count_all_master_barang(),
            'source_table' => 'tb_master_barang_all',
            'mode' => 'database'
        ];
    }

    public function count_all_master_barang()
    {
        if (!$this->db->table_exists($this->masterBarangAllTable)) {
            return 0;
        }

        return (int)$this->db->count_all($this->masterBarangAllTable);
    }

    private function master_barang_select()
    {
        $this->db->select('
            id,
            kd_barang,
            nama_barang,
            satuan,
            COALESCE(p, 0) AS p,
            COALESCE(l, 0) AS l,
            COALESCE(t, 0) AS t,
            COALESCE(berat, 0) AS berat,
            COALESCE(kubikasi, 0) AS kubikasi,
            qrcode,
            barcode,
            (COALESCE(p, 0) * COALESCE(l, 0) * COALESCE(t, 0)) AS dimensi
        ', false);
        $this->db->from($this->masterBarangAllTable);
    }

    private function master_barang_search($search)
    {
        if ($search === '') {
            return;
        }

        $this->db->group_start();
        $this->db->like('kd_barang', $search);
        $this->db->or_like('nama_barang', $search);
        $this->db->or_like('satuan', $search);
        $this->db->or_like('qrcode', $search);
        $this->db->or_like('barcode', $search);
        $this->db->group_end();
    }

    private function count_filtered_master_barang($search)
    {
        $this->db->from($this->masterBarangAllTable);
        $this->master_barang_search($search);
        return (int)$this->db->count_all_results();
    }

    public function get_master_barang_datatable($post)
    {
        if (!$this->db->table_exists($this->masterBarangAllTable)) {
            return [
                'draw' => (int)($post['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];
        }

        $search = trim((string)($post['search']['value'] ?? $post['search'] ?? ''));
        $length = (int)($post['length'] ?? 10);
        $start = max(0, (int)($post['start'] ?? 0));

        $columns = [
            'kd_barang',
            'nama_barang',
            'dimensi',
            'satuan',
            'berat',
            'kubikasi',
            'qrcode',
            'barcode',
            'id',
        ];
        $orderIndex = (int)($post['order'][0]['column'] ?? 1);
        $orderColumn = $columns[$orderIndex] ?? 'nama_barang';
        $orderDir = strtolower((string)($post['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $this->master_barang_select();
        $this->master_barang_search($search);
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
            'recordsTotal' => $this->count_all_master_barang(),
            'recordsFiltered' => $this->count_filtered_master_barang($search),
            'data' => $rows,
        ];
    }

    public function master_barang_datatable($post)
    {
        return $this->get_master_barang_datatable($post);
    }

    public function get_master_barang_by_id($id)
    {
        $this->master_barang_select();
        return $this->db
            ->where('id', (int)$id)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function update_master_barang($id, $data)
    {
        return $this->db
            ->where('id', (int)$id)
            ->update($this->masterBarangAllTable, $data);
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
            ->update($this->masterBarangAllTable, $allowed);
    }

    public function is_asset_exists($id, $type)
    {
        $field = $type === 'barcode' ? 'barcode' : 'qrcode';
        $row = $this->db
            ->select($field)
            ->from($this->masterBarangAllTable)
            ->where('id', (int)$id)
            ->limit(1)
            ->get()
            ->row_array();

        $path = trim((string)($row[$field] ?? ''));
        return $path !== '' && is_file(FCPATH . $path);
    }
}
