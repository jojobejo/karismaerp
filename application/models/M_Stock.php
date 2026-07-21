<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Stock extends CI_Model
{
    private function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        return $raw;
    }

    private function _jsonNumber($value, $decimals = 3)
    {
        return round((float)$value, $decimals);
    }

    private function _isiExpr()
    {
        return "CASE
            WHEN COALESCE(b.isi, 0) > 0 THEN b.isi
            WHEN (COALESCE(b.panjang, 0) * COALESCE(b.lebar, 0) * COALESCE(b.tinggi, 0)) > 0
                THEN (COALESCE(b.panjang, 0) * COALESCE(b.lebar, 0) * COALESCE(b.tinggi, 0))
            ELSE 1
        END";
    }

    private function _physicalQtyExpr($alias = 'l')
    {
        return "CASE
            WHEN {$alias}.tipe IN ('SALDO_AWAL','IN','RJUAL','ADJIN') THEN COALESCE({$alias}.qty, 0)
            WHEN {$alias}.tipe IN ('OUT','RBELI','ADJOUT') THEN -COALESCE({$alias}.qty, 0)
            ELSE 0
        END";
    }

    private function _reservedQtyExpr($alias = 'l')
    {
        return "CASE
            WHEN {$alias}.tipe = 'RESERVE' THEN COALESCE({$alias}.qty, 0)
            WHEN {$alias}.tipe = 'RELEASE' THEN -COALESCE({$alias}.qty, 0)
            ELSE 0
        END";
    }

    private function _splitBoxPcs($qty, $isi)
    {
        $qty = (float)$qty;
        $isi = max(1, (int)$isi);

        return [
            'qty_box' => (int)floor($qty / $isi),
            'qty_pcs' => (int)fmod($qty, $isi),
        ];
    }

    private function _ledgerSnapshotSql($whereSql = '')
    {
        $physicalExpr = $this->_physicalQtyExpr('l');
        $reservedExpr = $this->_reservedQtyExpr('l');

        return "
            SELECT
                l.kd_barang,
                l.gudang_id,
                l.no_lot,
                l.expired_date,
                SUM({$physicalExpr}) AS ledger_qty_on_hand,
                SUM({$reservedExpr}) AS ledger_qty_reserved,
                MAX(l.id) AS last_ledger_id,
                MAX(l.created_at) AS last_ledger_at
            FROM tberp_stock_ledger l
            {$whereSql}
            GROUP BY l.kd_barang, l.gudang_id, l.no_lot, l.expired_date
        ";
    }

    private function _ledgerSnapshotFilterSql($filters, &$params)
    {
        $where = [];

        if (!empty($filters['kd_barang'])) {
            $where[] = 'l.kd_barang = ?';
            $params[] = $filters['kd_barang'];
        }

        if (!empty($filters['gudang_id'])) {
            $where[] = 'l.gudang_id = ?';
            $params[] = $filters['gudang_id'];
        }

        return $where ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    private function _ledgerItemSql($filters, &$params, $forCount = false)
    {
        $snapshotParams = [];
        $snapshotSql = $this->_ledgerSnapshotSql($this->_ledgerSnapshotFilterSql($filters, $snapshotParams));
        $params = array_merge($params, $snapshotParams);
        $isiExpr = $this->_isiExpr();

        $where = ["(b.is_active = 'T' OR b.is_active IS NULL)"];
        if (!empty($filters['search'])) {
            $where[] = '(s.kd_barang LIKE ? OR b.nama_barang LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $select = $forCount ? 'COUNT(*) AS total' : "
            x.kd_barang,
            x.nama_barang,
            x.satuan,
            x.isi_per_box,
            x.stock_minimum,
            x.total_batch,
            x.qty,
            x.qty_reserved,
            (x.qty - GREATEST(x.qty_reserved, 0)) AS qty_available,
            x.nearest_expired_date,
            x.last_ledger_at
        ";

        return "
            SELECT {$select}
            FROM (
                SELECT
                    s.kd_barang,
                    COALESCE(b.nama_barang, s.kd_barang) AS nama_barang,
                    b.satuan,
                    {$isiExpr} AS isi_per_box,
                    COALESCE(b.stock_minimum, 0) AS stock_minimum,
                    COUNT(*) AS total_batch,
                    COALESCE(SUM(s.ledger_qty_on_hand), 0) AS qty,
                    COALESCE(SUM(s.ledger_qty_reserved), 0) AS qty_reserved,
                    MIN(CASE WHEN s.ledger_qty_on_hand > 0 THEN NULLIF(s.expired_date, '0000-00-00') ELSE NULL END) AS nearest_expired_date,
                    MAX(s.last_ledger_at) AS last_ledger_at
                FROM ({$snapshotSql}) s
                LEFT JOIN tbpo_barang b ON b.kode_barang = s.kd_barang
                {$whereSql}
                GROUP BY
                    s.kd_barang,
                    b.nama_barang,
                    b.satuan,
                    b.isi,
                    b.panjang,
                    b.lebar,
                    b.tinggi,
                    b.stock_minimum
            ) x
        ";
    }

    private function _applyFilters($filters, $batchAlias = 'sb', $barangAlias = 'b')
    {
        if (!empty($filters['kd_barang'])) {
            $this->db->where($batchAlias . '.kd_barang', $filters['kd_barang']);
        }

        if (!empty($filters['gudang_id'])) {
            $this->db->where($batchAlias . '.gudang_id', $filters['gudang_id']);
        }

        if (!empty($filters['expired_from'])) {
            $this->db->where($batchAlias . '.expired_date >=', $this->_normalizeDate($filters['expired_from']));
        }

        if (!empty($filters['expired_to'])) {
            $this->db->where($batchAlias . '.expired_date <=', $this->_normalizeDate($filters['expired_to']));
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like($batchAlias . '.kd_barang', $filters['search']);
            $this->db->or_like($barangAlias . '.nama_barang', $filters['search']);
            $this->db->or_like($batchAlias . '.no_lot', $filters['search']);
            $this->db->group_end();
        }

        if (!isset($filters['active_only']) || $filters['active_only']) {
            $this->db->group_start();
            $this->db->where($barangAlias . '.is_active', 'T');
            $this->db->or_where($barangAlias . '.is_active IS NULL', null, false);
            $this->db->group_end();
        }
    }

    private function _normalizeSalesRow($row)
    {
        $isi = max(1, (int)($row['isi_per_box'] ?? 1));
        $available = (float)($row['available_stock'] ?? 0);

        $row['kode_barang'] = $row['kd_barang'];
        $row['exp_date'] = $this->_normalizeDate($row['expired_date'] ?? '');
        $row['gudang'] = $row['gudang_id'] ?? '';
        $row['qty_on_hand'] = $this->_jsonNumber($row['qty_on_hand'] ?? 0);
        $row['qty_reserved'] = $this->_jsonNumber($row['qty_reserved'] ?? 0);
        $row['available_stock'] = $this->_jsonNumber($available);
        $row['available_box'] = (int)floor($available / $isi);
        $row['available_ecer'] = (int)fmod($available, $isi);
        $row['isi_per_box'] = $isi;
        $row['berat_gram'] = (float)($row['berat_gram'] ?? 0);
        $row['kubikasi_m3'] = (float)($row['kubikasi_m3'] ?? 0);
        $row['hpp'] = 0;
        $row['p'] = (float)($row['p'] ?? 0);
        $row['l'] = (float)($row['l'] ?? 0);
        $row['t'] = (float)($row['t'] ?? 0);

        return $row;
    }

    public function get_summary($filters = [])
    {
        $params = [];
        $snapshotSql = $this->_ledgerSnapshotSql($this->_ledgerSnapshotFilterSql($filters, $params));

        $where = ["(b.is_active = 'T' OR b.is_active IS NULL)"];
        if (!empty($filters['search'])) {
            $where[] = '(s.kd_barang LIKE ? OR b.nama_barang LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "
            SELECT
                COUNT(DISTINCT s.kd_barang) AS total_sku,
                COUNT(*) AS total_batch,
                COALESCE(SUM(s.ledger_qty_on_hand), 0) AS qty_on_hand,
                COALESCE(SUM(s.ledger_qty_reserved), 0) AS qty_reserved,
                COALESCE(SUM(s.ledger_qty_on_hand - GREATEST(s.ledger_qty_reserved, 0)), 0) AS qty_available,
                SUM(CASE WHEN s.ledger_qty_on_hand < 0 THEN 1 ELSE 0 END) AS negative_batch,
                SUM(CASE WHEN s.expired_date IS NOT NULL AND s.expired_date < CURDATE() AND s.ledger_qty_on_hand > 0 THEN 1 ELSE 0 END) AS expired_batch,
                SUM(CASE WHEN s.expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND s.ledger_qty_on_hand > 0 THEN 1 ELSE 0 END) AS near_expired_batch,
                SUM(CASE WHEN COALESCE(b.stock_minimum, 0) > 0 AND s.ledger_qty_on_hand <= b.stock_minimum THEN 1 ELSE 0 END) AS low_stock_row
            FROM ({$snapshotSql}) s
            LEFT JOIN tbpo_barang b ON b.kode_barang = s.kd_barang
            {$whereSql}
        ";

        $row = $this->db->query($sql, $params)->row_array();

        return [
            'total_sku' => (int)($row['total_sku'] ?? 0),
            'total_batch' => (int)($row['total_batch'] ?? 0),
            'qty_on_hand' => $this->_jsonNumber($row['qty_on_hand'] ?? 0),
            'qty_reserved' => $this->_jsonNumber($row['qty_reserved'] ?? 0),
            'qty_available' => $this->_jsonNumber($row['qty_available'] ?? 0),
            'negative_batch' => (int)($row['negative_batch'] ?? 0),
            'expired_batch' => (int)($row['expired_batch'] ?? 0),
            'near_expired_batch' => (int)($row['near_expired_batch'] ?? 0),
            'low_stock_row' => (int)($row['low_stock_row'] ?? 0),
        ];
    }

    public function get_gudang_summary($filters = [])
    {
        $params = [];
        $snapshotSql = $this->_ledgerSnapshotSql($this->_ledgerSnapshotFilterSql($filters, $params));

        $where = ["(b.is_active = 'T' OR b.is_active IS NULL)"];
        if (!empty($filters['search'])) {
            $where[] = '(s.kd_barang LIKE ? OR b.nama_barang LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "
            SELECT
                g.id_gudang AS gudang_id,
                g.nama_gudang,
                g.tipe AS tipe_gudang,
                COALESCE(x.total_sku, 0) AS total_sku,
                COALESCE(x.total_batch, 0) AS total_batch,
                COALESCE(x.qty_on_hand, 0) AS qty_on_hand,
                COALESCE(x.qty_reserved, 0) AS qty_reserved,
                COALESCE(x.qty_available, 0) AS qty_available,
                COALESCE(x.negative_batch, 0) AS negative_batch,
                COALESCE(x.expired_batch, 0) AS expired_batch,
                COALESCE(x.near_expired_batch, 0) AS near_expired_batch
            FROM tb_gudang g
            LEFT JOIN (
                SELECT
                    s.gudang_id,
                    COUNT(DISTINCT s.kd_barang) AS total_sku,
                    COUNT(*) AS total_batch,
                    SUM(s.ledger_qty_on_hand) AS qty_on_hand,
                    SUM(s.ledger_qty_reserved) AS qty_reserved,
                    SUM(s.ledger_qty_on_hand - GREATEST(s.ledger_qty_reserved, 0)) AS qty_available,
                    SUM(CASE WHEN s.ledger_qty_on_hand < 0 THEN 1 ELSE 0 END) AS negative_batch,
                    SUM(CASE WHEN s.expired_date IS NOT NULL AND s.expired_date < CURDATE() AND s.ledger_qty_on_hand > 0 THEN 1 ELSE 0 END) AS expired_batch,
                    SUM(CASE WHEN s.expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND s.ledger_qty_on_hand > 0 THEN 1 ELSE 0 END) AS near_expired_batch
                FROM ({$snapshotSql}) s
                LEFT JOIN tbpo_barang b ON b.kode_barang = s.kd_barang
                {$whereSql}
                GROUP BY s.gudang_id
            ) x ON x.gudang_id = g.id_gudang
            WHERE COALESCE(g.is_active, 1) = 1
            ORDER BY g.id_gudang ASC
        ";

        $rows = $this->db->query($sql, $params)->result_array();

        foreach ($rows as &$row) {
            $row['total_sku'] = (int)($row['total_sku'] ?? 0);
            $row['total_batch'] = (int)($row['total_batch'] ?? 0);
            $row['qty_on_hand'] = $this->_jsonNumber($row['qty_on_hand'] ?? 0);
            $row['qty_reserved'] = $this->_jsonNumber($row['qty_reserved'] ?? 0);
            $row['qty_available'] = $this->_jsonNumber($row['qty_available'] ?? 0);
            $row['negative_batch'] = (int)($row['negative_batch'] ?? 0);
            $row['expired_batch'] = (int)($row['expired_batch'] ?? 0);
            $row['near_expired_batch'] = (int)($row['near_expired_batch'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    public function get_available_for_sales($filters = [])
    {
        $isiExpr = $this->_isiExpr();
        $this->db->select("
            sb.id,
            sb.kd_barang,
            b.nama_barang,
            b.satuan,
            b.panjang AS p,
            b.lebar AS l,
            b.tinggi AS t,
            b.berat AS berat_gram,
            CASE
                WHEN b.panjang > 0 AND b.lebar > 0 AND b.tinggi > 0
                    THEN (b.panjang * b.lebar * b.tinggi) / 1000000
                ELSE 0
            END AS kubikasi_m3,
            {$isiExpr} AS isi_per_box,
            b.stock_minimum,
            b.is_lot,
            sb.gudang_id,
            sb.no_lot,
            sb.expired_date,
            sb.qty_on_hand,
            COALESCE(sb.qty_reserved, 0) AS qty_reserved,
            (sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)) AS available_stock,
            sb.update_at
        ", false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');
        $this->_applyFilters($filters);

        if (!isset($filters['include_zero']) || !$filters['include_zero']) {
            $this->db->where('(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)) >', 0, false);
        }

        $this->db->order_by('b.nama_barang', 'ASC');
        $this->db->order_by('sb.expired_date', 'ASC');
        $this->db->order_by('sb.no_lot', 'ASC');

        if (!empty($filters['limit'])) {
            $this->db->limit((int)$filters['limit']);
        }

        $rows = $this->db->get()->result_array();
        foreach ($rows as &$row) {
            $row = $this->_normalizeSalesRow($row);
        }
        unset($row);

        return $rows;
    }

    public function get_batch_rows($filters = [])
    {
        if (empty($filters['limit'])) {
            $filters['limit'] = 300;
        }

        $rows = $this->get_available_for_sales(array_merge($filters, ['include_zero' => true]));
        foreach ($rows as &$row) {
            $row['status_expired'] = 'OK';
            if (!empty($row['expired_date']) && $row['expired_date'] < date('Y-m-d')) {
                $row['status_expired'] = 'EXPIRED';
            } elseif (!empty($row['expired_date']) && $row['expired_date'] <= date('Y-m-d', strtotime('+90 days'))) {
                $row['status_expired'] = 'NEAR_EXPIRED';
            }
        }
        unset($row);
        return $rows;
    }

    public function get_item_rows($filters = [])
    {
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = (int)($filters['per_page'] ?? 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;
        $offset = ($page - 1) * $perPage;

        $countParams = [];
        $countSql = $this->_ledgerItemSql($filters, $countParams, true);
        $countRow = $this->db->query($countSql, $countParams)->row_array();
        $totalRows = (int)($countRow['total'] ?? 0);

        $params = [];
        $sql = $this->_ledgerItemSql($filters, $params, false);
        $sql .= " ORDER BY x.nama_barang ASC, x.kd_barang ASC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $perPage;

        $rows = $this->db->query($sql, $params)->result_array();
        foreach ($rows as &$row) {
            $isi = max(1, (int)($row['isi_per_box'] ?? 1));
            $qty = (float)($row['qty'] ?? 0);
            $split = $this->_splitBoxPcs($qty, $isi);
            $row['qty'] = $this->_jsonNumber($qty);
            $row['qty_on_hand'] = $row['qty'];
            $row['qty_reserved'] = $this->_jsonNumber($row['qty_reserved'] ?? 0);
            $row['available_stock'] = $this->_jsonNumber($row['qty_available'] ?? 0);
            $row['available_box'] = $split['qty_box'];
            $row['available_ecer'] = $split['qty_pcs'];
            $row['qty_box'] = $split['qty_box'];
            $row['qty_pcs'] = $split['qty_pcs'];
        }
        unset($row);

        return [
            'rows' => $rows,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => $perPage > 0 ? (int)ceil($totalRows / $perPage) : 1,
            ],
        ];
    }

    public function get_stock_item_detail($kdBarang, $gudangId = '')
    {
        $filters = ['kd_barang' => $kdBarang, 'per_page' => 1, 'page' => 1];
        if ($gudangId !== '') {
            $filters['gudang_id'] = $gudangId;
        }

        $itemResult = $this->get_item_rows($filters);
        $item = $itemResult['rows'][0] ?? null;

        $history = $this->get_ledger_rows([
            'kd_barang' => $kdBarang,
            'gudang_id' => $gudangId,
            'limit' => 200,
        ]);

        return [
            'item' => $item,
            'gudang_id' => $gudangId,
            'per_gudang' => $this->get_gudang_summary(['kd_barang' => $kdBarang]),
            'lot_rows' => $this->get_stock_lot_rows($kdBarang, $gudangId),
            'movement_summary' => $this->get_stock_movement_summary($kdBarang, $gudangId),
            'ledger_history' => $history,
            'adjustment_rows' => $this->get_ledger_rows([
                'kd_barang' => $kdBarang,
                'gudang_id' => $gudangId,
                'tipe' => 'ADJIN',
                'limit' => 100,
            ]),
            'adjustment_out_rows' => $this->get_ledger_rows([
                'kd_barang' => $kdBarang,
                'gudang_id' => $gudangId,
                'tipe' => 'ADJOUT',
                'limit' => 100,
            ]),
            'reservation_rows' => $this->get_stock_reservation_rows($kdBarang, $gudangId),
            'reconciliation_rows' => $this->get_reconciliation([
                'kd_barang' => $kdBarang,
                'gudang_id' => $gudangId,
                'limit' => 100,
            ]),
        ];
    }

    public function get_stock_lot_rows($kdBarang, $gudangId = '')
    {
        $params = [];
        $filters = ['kd_barang' => $kdBarang];
        if ($gudangId !== '') $filters['gudang_id'] = $gudangId;
        $snapshotSql = $this->_ledgerSnapshotSql($this->_ledgerSnapshotFilterSql($filters, $params));
        $isiExpr = $this->_isiExpr();

        $sql = "
            SELECT
                s.kd_barang,
                COALESCE(g.nama_gudang, CONCAT('Gudang ', s.gudang_id)) AS nama_gudang,
                s.gudang_id,
                s.no_lot,
                s.expired_date,
                {$isiExpr} AS isi_per_box,
                s.ledger_qty_on_hand AS qty,
                s.ledger_qty_reserved AS qty_reserved,
                s.last_ledger_at
            FROM ({$snapshotSql}) s
            LEFT JOIN tbpo_barang b ON b.kode_barang = s.kd_barang
            LEFT JOIN tb_gudang g ON g.id_gudang = s.gudang_id
            ORDER BY g.id_gudang ASC, s.expired_date ASC, s.no_lot ASC
        ";

        $rows = $this->db->query($sql, $params)->result_array();
        foreach ($rows as &$row) {
            $split = $this->_splitBoxPcs($row['qty'] ?? 0, $row['isi_per_box'] ?? 1);
            $row['qty'] = $this->_jsonNumber($row['qty'] ?? 0);
            $row['qty_reserved'] = $this->_jsonNumber($row['qty_reserved'] ?? 0);
            $row['qty_box'] = $split['qty_box'];
            $row['qty_pcs'] = $split['qty_pcs'];
        }
        unset($row);

        return $rows;
    }

    public function get_stock_movement_summary($kdBarang, $gudangId = '')
    {
        $physicalExpr = $this->_physicalQtyExpr('l');
        $reservedExpr = $this->_reservedQtyExpr('l');
        $params = [$kdBarang];
        $where = 'WHERE l.kd_barang = ?';
        if ($gudangId !== '') {
            $where .= ' AND l.gudang_id = ?';
            $params[] = $gudangId;
        }

        $sql = "
            SELECT
                l.tipe,
                COUNT(*) AS total_transaksi,
                COALESCE(SUM(l.qty), 0) AS total_qty,
                COALESCE(SUM({$physicalExpr}), 0) AS signed_physical_qty,
                COALESCE(SUM({$reservedExpr}), 0) AS signed_reserved_qty,
                MAX(l.created_at) AS last_at
            FROM tberp_stock_ledger l
            {$where}
            GROUP BY l.tipe
            ORDER BY FIELD(l.tipe, 'SALDO_AWAL','IN','OUT','RESERVE','RELEASE','ADJIN','ADJOUT','RJUAL','RBELI','MUTASI'), l.tipe
        ";

        $rows = $this->db->query($sql, $params)->result_array();
        foreach ($rows as &$row) {
            $row['total_transaksi'] = (int)($row['total_transaksi'] ?? 0);
            $row['total_qty'] = $this->_jsonNumber($row['total_qty'] ?? 0);
            $row['signed_physical_qty'] = $this->_jsonNumber($row['signed_physical_qty'] ?? 0);
            $row['signed_reserved_qty'] = $this->_jsonNumber($row['signed_reserved_qty'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    public function get_stock_reservation_rows($kdBarang, $gudangId = '')
    {
        $rows = [];
        foreach (['RESERVE', 'RELEASE'] as $tipe) {
            $rows = array_merge($rows, $this->get_ledger_rows([
                'kd_barang' => $kdBarang,
                'gudang_id' => $gudangId,
                'tipe' => $tipe,
                'limit' => 100,
            ]));
        }

        usort($rows, function($a, $b) {
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });

        return array_slice($rows, 0, 100);
    }

    public function get_ledger_rows($filters = [])
    {
        $this->db->select('l.*, b.nama_barang, b.satuan');
        $this->db->from('tberp_stock_ledger l');
        $this->db->join('tbpo_barang b', 'b.kode_barang = l.kd_barang', 'left');

        if (!empty($filters['kd_barang'])) $this->db->where('l.kd_barang', $filters['kd_barang']);
        if (!empty($filters['gudang_id'])) $this->db->where('l.gudang_id', $filters['gudang_id']);
        if (!empty($filters['date_from'])) $this->db->where('DATE(l.created_at) >=', $filters['date_from']);
        if (!empty($filters['date_to'])) $this->db->where('DATE(l.created_at) <=', $filters['date_to']);
        if (!empty($filters['tipe'])) $this->db->where('l.tipe', $filters['tipe']);
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('l.kd_barang', $filters['search']);
            $this->db->or_like('b.nama_barang', $filters['search']);
            $this->db->or_like('l.ref_no', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('l.created_at', 'DESC');
        $this->db->order_by('l.id', 'DESC');
        $this->db->limit(!empty($filters['limit']) ? (int)$filters['limit'] : 300);
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$row) {
            $qty = (float)($row['qty'] ?? 0);
            $row['signed_physical_qty'] = 0;
            $row['signed_reserved_qty'] = 0;
            if (in_array($row['tipe'], ['SALDO_AWAL','IN','RJUAL','ADJIN'])) {
                $row['signed_physical_qty'] = $qty;
            } elseif (in_array($row['tipe'], ['OUT','RBELI','ADJOUT'])) {
                $row['signed_physical_qty'] = -$qty;
            } elseif ($row['tipe'] === 'RESERVE') {
                $row['signed_reserved_qty'] = $qty;
            } elseif ($row['tipe'] === 'RELEASE') {
                $row['signed_reserved_qty'] = -$qty;
            }
        }
        unset($row);

        return $rows;
    }

    public function get_reconciliation($filters = [])
    {
        $where = [];
        $params = [];
        $batchWhere = [];
        $batchParams = [];
        if (!empty($filters['kd_barang'])) {
            $where[] = 'l.kd_barang = ?';
            $params[] = $filters['kd_barang'];
            $batchWhere[] = 'sb.kd_barang = ?';
            $batchParams[] = $filters['kd_barang'];
        }
        if (!empty($filters['gudang_id'])) {
            $where[] = 'l.gudang_id = ?';
            $params[] = $filters['gudang_id'];
            $batchWhere[] = 'sb.gudang_id = ?';
            $batchParams[] = $filters['gudang_id'];
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $batchWhereSql = $batchWhere ? implode(' AND ', $batchWhere) . ' AND ' : '';
        $ledgerSql = $this->_ledgerSnapshotSql($whereSql);

        $sql = "
            SELECT *
            FROM (
                SELECT
                    COALESCE(sb.kd_barang, lg.kd_barang) AS kd_barang,
                    b.nama_barang,
                    COALESCE(sb.gudang_id, lg.gudang_id) AS gudang_id,
                    COALESCE(sb.no_lot, lg.no_lot) AS no_lot,
                    COALESCE(sb.expired_date, lg.expired_date) AS expired_date,
                    COALESCE(sb.qty_on_hand, 0) AS batch_qty_on_hand,
                    COALESCE(sb.qty_reserved, 0) AS batch_qty_reserved,
                    COALESCE(lg.ledger_qty_on_hand, 0) AS ledger_qty_on_hand,
                    COALESCE(lg.ledger_qty_reserved, 0) AS ledger_qty_reserved,
                    (COALESCE(sb.qty_on_hand, 0) - COALESCE(lg.ledger_qty_on_hand, 0)) AS diff_on_hand,
                    (COALESCE(sb.qty_reserved, 0) - GREATEST(COALESCE(lg.ledger_qty_reserved, 0), 0)) AS diff_reserved,
                    lg.last_ledger_id,
                    lg.last_ledger_at
                FROM tberp_stock_batch sb
                LEFT JOIN ({$ledgerSql}) lg
                    ON lg.kd_barang = sb.kd_barang
                    AND COALESCE(lg.gudang_id, '') = COALESCE(sb.gudang_id, '')
                    AND COALESCE(lg.no_lot, '') = COALESCE(sb.no_lot, '')
                    AND COALESCE(lg.expired_date, '1000-01-01') = COALESCE(sb.expired_date, '1000-01-01')
                LEFT JOIN tbpo_barang b ON b.kode_barang = COALESCE(sb.kd_barang, lg.kd_barang)
                WHERE {$batchWhereSql}(
                    ABS(COALESCE(sb.qty_on_hand, 0) - COALESCE(lg.ledger_qty_on_hand, 0)) > 0.0009
                    OR ABS(COALESCE(sb.qty_reserved, 0) - GREATEST(COALESCE(lg.ledger_qty_reserved, 0), 0)) > 0.0009
                    OR lg.kd_barang IS NULL
                )

                UNION ALL

                SELECT
                    lg.kd_barang,
                    b.nama_barang,
                    lg.gudang_id,
                    lg.no_lot,
                    lg.expired_date,
                    0 AS batch_qty_on_hand,
                    0 AS batch_qty_reserved,
                    COALESCE(lg.ledger_qty_on_hand, 0) AS ledger_qty_on_hand,
                    COALESCE(lg.ledger_qty_reserved, 0) AS ledger_qty_reserved,
                    -COALESCE(lg.ledger_qty_on_hand, 0) AS diff_on_hand,
                    -GREATEST(COALESCE(lg.ledger_qty_reserved, 0), 0) AS diff_reserved,
                    lg.last_ledger_id,
                    lg.last_ledger_at
                FROM ({$ledgerSql}) lg
                LEFT JOIN tberp_stock_batch sb
                    ON sb.kd_barang = lg.kd_barang
                    AND COALESCE(sb.gudang_id, '') = COALESCE(lg.gudang_id, '')
                    AND COALESCE(sb.no_lot, '') = COALESCE(lg.no_lot, '')
                    AND COALESCE(sb.expired_date, '1000-01-01') = COALESCE(lg.expired_date, '1000-01-01')
                LEFT JOIN tbpo_barang b ON b.kode_barang = lg.kd_barang
                WHERE sb.id IS NULL
                    AND (
                        ABS(COALESCE(lg.ledger_qty_on_hand, 0)) > 0.0009
                        OR ABS(GREATEST(COALESCE(lg.ledger_qty_reserved, 0), 0)) > 0.0009
                    )
            ) x
            ORDER BY ABS(x.diff_on_hand) DESC
        ";

        $limit = array_key_exists('limit', $filters) ? (int)$filters['limit'] : 300;
        if ($limit > 0) {
            $sql .= "\nLIMIT " . $limit;
        }

        return $this->db->query($sql, array_merge($params, $batchParams, $params))->result_array();
    }

    public function count_reconciliation_mismatch($filters = [])
    {
        $filters['limit'] = 0;
        return count($this->get_reconciliation($filters));
    }

    public function sync_batch_from_ledger($filters = [], $dryRun = true)
    {
        $where = [];
        $params = [];
        if (!empty($filters['kd_barang'])) {
            $where[] = 'l.kd_barang = ?';
            $params[] = $filters['kd_barang'];
        }
        if (!empty($filters['gudang_id'])) {
            $where[] = 'l.gudang_id = ?';
            $params[] = $filters['gudang_id'];
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $rows = $this->db->query($this->_ledgerSnapshotSql($whereSql), $params)->result_array();

        $result = [
            'dry_run' => (bool)$dryRun,
            'ledger_groups' => count($rows),
            'to_insert' => 0,
            'to_update' => 0,
            'unchanged' => 0,
            'rows' => [],
        ];

        if (!$dryRun) $this->db->trans_begin();

        foreach ($rows as $row) {
            $reserved = max(0, (float)$row['ledger_qty_reserved']);
            $onHand = (float)$row['ledger_qty_on_hand'];

            $this->db->from('tberp_stock_batch');
            $this->db->where('kd_barang', $row['kd_barang']);
            $this->db->where('gudang_id', $row['gudang_id']);
            $row['no_lot'] === null ? $this->db->where('no_lot IS NULL', null, false) : $this->db->where('no_lot', $row['no_lot']);
            $row['expired_date'] === null ? $this->db->where('expired_date IS NULL', null, false) : $this->db->where('expired_date', $row['expired_date']);
            $existing = $this->db->get()->row_array();

            $action = 'unchanged';
            if (!$existing) {
                $action = 'insert';
                $result['to_insert']++;
                if (!$dryRun) {
                    $this->db->insert('tberp_stock_batch', [
                        'kd_barang' => $row['kd_barang'],
                        'gudang_id' => $row['gudang_id'],
                        'no_lot' => $row['no_lot'],
                        'expired_date' => $row['expired_date'],
                        'qty_on_hand' => $onHand,
                        'qty_reserved' => $reserved,
                        'created_at' => date('Y-m-d H:i:s'),
                        'update_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                $diffOnHand = abs((float)$existing['qty_on_hand'] - $onHand);
                $diffReserved = abs((float)$existing['qty_reserved'] - $reserved);
                if ($diffOnHand > 0.0009 || $diffReserved > 0.0009) {
                    $action = 'update';
                    $result['to_update']++;
                    if (!$dryRun) {
                        $this->db->where('id', $existing['id']);
                        $this->db->update('tberp_stock_batch', [
                            'qty_on_hand' => $onHand,
                            'qty_reserved' => $reserved,
                            'update_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                } else {
                    $result['unchanged']++;
                }
            }

            if (count($result['rows']) < 100) {
                $result['rows'][] = [
                    'action' => $action,
                    'kd_barang' => $row['kd_barang'],
                    'gudang_id' => $row['gudang_id'],
                    'no_lot' => $row['no_lot'],
                    'expired_date' => $row['expired_date'],
                    'qty_on_hand' => $this->_jsonNumber($onHand),
                    'qty_reserved' => $this->_jsonNumber($reserved),
                ];
            }
        }

        if (!$dryRun) {
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $result['status'] = false;
                $result['message'] = 'Sinkronisasi gagal, transaksi dibatalkan.';
            } else {
                $this->db->trans_commit();
                $result['status'] = true;
                $result['message'] = 'Sinkronisasi batch dari ledger selesai.';
            }
        } else {
            $result['status'] = true;
            $result['message'] = 'Preview sinkronisasi selesai. Belum ada data yang diubah.';
        }

        return $result;
    }
}
