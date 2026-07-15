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
        $this->db->select("
            COUNT(DISTINCT sb.kd_barang) AS total_sku,
            COUNT(sb.id) AS total_batch,
            COALESCE(SUM(sb.qty_on_hand), 0) AS qty_on_hand,
            COALESCE(SUM(sb.qty_reserved), 0) AS qty_reserved,
            COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS qty_available,
            SUM(CASE WHEN sb.qty_on_hand < 0 THEN 1 ELSE 0 END) AS negative_batch,
            SUM(CASE WHEN sb.expired_date IS NOT NULL AND sb.expired_date < CURDATE() AND sb.qty_on_hand > 0 THEN 1 ELSE 0 END) AS expired_batch,
            SUM(CASE WHEN sb.expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND sb.qty_on_hand > 0 THEN 1 ELSE 0 END) AS near_expired_batch,
            SUM(CASE WHEN b.stock_minimum > 0 AND sb.qty_on_hand <= b.stock_minimum THEN 1 ELSE 0 END) AS low_stock_row
        ", false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');
        $this->_applyFilters($filters);
        $row = $this->db->get()->row_array();

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
        $this->db->select("
            sb.gudang_id,
            COALESCE(g.nama_gudang, CONCAT('Gudang ', sb.gudang_id)) AS nama_gudang,
            COALESCE(g.tipe, '-') AS tipe_gudang,
            COUNT(DISTINCT sb.kd_barang) AS total_sku,
            COUNT(sb.id) AS total_batch,
            COALESCE(SUM(sb.qty_on_hand), 0) AS qty_on_hand,
            COALESCE(SUM(sb.qty_reserved), 0) AS qty_reserved,
            COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS qty_available,
            SUM(CASE WHEN sb.qty_on_hand < 0 THEN 1 ELSE 0 END) AS negative_batch,
            SUM(CASE WHEN sb.expired_date IS NOT NULL AND sb.expired_date < CURDATE() AND sb.qty_on_hand > 0 THEN 1 ELSE 0 END) AS expired_batch,
            SUM(CASE WHEN sb.expired_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND sb.qty_on_hand > 0 THEN 1 ELSE 0 END) AS near_expired_batch
        ", false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');
        $this->db->join('tb_gudang g', 'g.id_gudang = sb.gudang_id', 'left');
        $this->_applyFilters($filters);
        $this->db->group_by(['sb.gudang_id', 'g.nama_gudang', 'g.tipe']);
        $this->db->order_by('g.id_gudang', 'ASC');
        $rows = $this->db->get()->result_array();

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
        $isiExpr = $this->_isiExpr();
        $this->db->select("
            sb.kd_barang,
            b.nama_barang,
            b.satuan,
            {$isiExpr} AS isi_per_box,
            b.stock_minimum,
            COUNT(sb.id) AS total_batch,
            COALESCE(SUM(sb.qty_on_hand), 0) AS qty_on_hand,
            COALESCE(SUM(sb.qty_reserved), 0) AS qty_reserved,
            COALESCE(SUM(sb.qty_on_hand - COALESCE(sb.qty_reserved, 0)), 0) AS available_stock,
            MIN(NULLIF(sb.expired_date, '0000-00-00')) AS nearest_expired_date,
            MAX(sb.update_at) AS last_sync_at
        ", false);
        $this->db->from('tberp_stock_batch sb');
        $this->db->join('tbpo_barang b', 'b.kode_barang = sb.kd_barang', 'left');
        $this->_applyFilters($filters);
        $this->db->group_by([
            'sb.kd_barang',
            'b.nama_barang',
            'b.satuan',
            'b.isi',
            'b.panjang',
            'b.lebar',
            'b.tinggi',
            'b.stock_minimum',
        ]);
        $this->db->order_by('b.nama_barang', 'ASC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$row) {
            $isi = max(1, (int)($row['isi_per_box'] ?? 1));
            $available = (float)$row['available_stock'];
            $row['qty_on_hand'] = $this->_jsonNumber($row['qty_on_hand']);
            $row['qty_reserved'] = $this->_jsonNumber($row['qty_reserved']);
            $row['available_stock'] = $this->_jsonNumber($available);
            $row['available_box'] = (int)floor($available / $isi);
            $row['available_ecer'] = (int)fmod($available, $isi);
        }
        unset($row);

        return $rows;
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
