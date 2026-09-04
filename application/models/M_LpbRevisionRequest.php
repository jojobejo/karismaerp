<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_LpbRevisionRequest extends CI_Model
{
    const STATUS_REQUESTED = 'REQUESTED';
    const STATUS_PROCESS = 'ACCOUNTING_PROCESS';
    const STATUS_READY = 'READY_LPB_UNPOST';
    const STATUS_LPB_UNPOSTED = 'LPB_UNPOSTED';
    const STATUS_DONE = 'REVISION_DONE';
    const STATUS_CANCELLED = 'CANCELLED';

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_lpb_revision_request` (
            `id_request` INT(11) NOT NULL AUTO_INCREMENT,
            `no_request` VARCHAR(50) NOT NULL,
            `id_lpb` INT(11) NOT NULL,
            `nomor_lpb` VARCHAR(50) DEFAULT NULL,
            `kd_po` VARCHAR(100) DEFAULT NULL,
            `no_po` VARCHAR(100) DEFAULT NULL,
            `kd_supplier` VARCHAR(100) DEFAULT NULL,
            `nama_supplier` VARCHAR(255) DEFAULT NULL,
            `gudang_id` VARCHAR(30) DEFAULT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'REQUESTED',
            `alasan_revisi` TEXT DEFAULT NULL,
            `total_faktur` INT(11) NOT NULL DEFAULT 0,
            `total_item` INT(11) NOT NULL DEFAULT 0,
            `total_qty_terjual` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `requested_by` VARCHAR(100) DEFAULT NULL,
            `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `accounting_by` VARCHAR(100) DEFAULT NULL,
            `accounting_at` DATETIME DEFAULT NULL,
            `purchasing_by` VARCHAR(100) DEFAULT NULL,
            `purchasing_at` DATETIME DEFAULT NULL,
            `completed_by` VARCHAR(100) DEFAULT NULL,
            `completed_at` DATETIME DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id_request`),
            UNIQUE KEY `uk_no_request` (`no_request`),
            KEY `idx_lpb_revision_lpb` (`id_lpb`),
            KEY `idx_lpb_revision_status` (`status`),
            KEY `idx_lpb_revision_requested_at` (`requested_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_lpb_revision_request_detail` (
            `id_detail` INT(11) NOT NULL AUTO_INCREMENT,
            `id_request` INT(11) NOT NULL,
            `id_lpb` INT(11) NOT NULL,
            `id_detail_lpb` INT(11) DEFAULT NULL,
            `source_table` VARCHAR(50) NOT NULL,
            `source_pk` VARCHAR(50) DEFAULT NULL,
            `id_faktur` INT(11) DEFAULT NULL,
            `id_faktur_detail` INT(11) DEFAULT NULL,
            `no_faktur` VARCHAR(50) NOT NULL,
            `tanggal_faktur` DATE DEFAULT NULL,
            `status_faktur_before` VARCHAR(30) DEFAULT NULL,
            `kd_barang` VARCHAR(100) NOT NULL,
            `nama_barang` VARCHAR(255) DEFAULT NULL,
            `no_lot` VARCHAR(100) DEFAULT NULL,
            `expired_date` DATE DEFAULT NULL,
            `qty_lpb` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `qty_terjual` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `status` VARCHAR(30) NOT NULL DEFAULT 'REQUESTED',
            `unpost_by` VARCHAR(100) DEFAULT NULL,
            `unpost_at` DATETIME DEFAULT NULL,
            `catatan_accounting` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_detail`),
            KEY `idx_lpb_revision_detail_request` (`id_request`),
            KEY `idx_lpb_revision_detail_faktur` (`no_faktur`),
            KEY `idx_lpb_revision_detail_source` (`source_table`, `source_pk`),
            KEY `idx_lpb_revision_detail_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_lpb_revision_request_log` (
            `id_log` INT(11) NOT NULL AUTO_INCREMENT,
            `id_request` INT(11) NOT NULL,
            `action_type` VARCHAR(50) NOT NULL,
            `status_before` VARCHAR(30) DEFAULT NULL,
            `status_after` VARCHAR(30) DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `data_before` LONGTEXT DEFAULT NULL,
            `data_after` LONGTEXT DEFAULT NULL,
            `dilakukan_oleh` VARCHAR(100) DEFAULT NULL,
            `dilakukan_pada` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_log`),
            KEY `idx_lpb_revision_log_request` (`id_request`),
            KEY `idx_lpb_revision_log_action` (`action_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function open_count()
    {
        $this->ensure_schema();
        return (int) $this->db
            ->where_in('status', [self::STATUS_REQUESTED, self::STATUS_PROCESS, self::STATUS_READY, self::STATUS_LPB_UNPOSTED])
            ->count_all_results('tb_lpb_revision_request');
    }

    public function sold_lpb_candidates($limit = 100)
    {
        $this->ensure_schema();
        $this->load->model('M_Logistik');

        $rows = $this->db->query("
            SELECT
                l.id_lpb,
                l.nomor_lpb,
                l.kd_po,
                l.no_po,
                l.no_invoice,
                l.gudang_id,
                l.status_lpb,
                l.input_at,
                COALESCE(NULLIF(TRIM(MAX(p.kd_suplier)), ''), '') AS kd_supplier,
                COALESCE(NULLIF(TRIM(MAX(s.nama_suplier)), ''), MAX(p.kd_suplier), '-') AS nama_supplier,
                COUNT(d.id_detail_lpb) AS total_detail,
                SUM(COALESCE(d.qty_diterima, 0)) AS total_qty_lpb
            FROM tb_lpb l
            INNER JOIN tb_lpb_detail d ON d.id_lpb = l.id_lpb
            LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
            LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
            LEFT JOIN tb_lpb_revision_request rr
                ON rr.id_lpb = l.id_lpb
                AND rr.status IN ('REQUESTED','ACCOUNTING_PROCESS','READY_LPB_UNPOST','LPB_UNPOSTED')
            WHERE COALESCE(l.status_lpb, 1) = 1
                AND rr.id_request IS NULL
            GROUP BY l.id_lpb
            ORDER BY l.input_at DESC, l.id_lpb DESC
            LIMIT ?
        ", [(int) $limit])->result_array();

        $candidates = [];
        foreach ($rows as $row) {
            $blockers = $this->M_Logistik->get_lpb_sales_unpost_blockers((int) $row['id_lpb']);
            if (empty($blockers)) {
                continue;
            }

            $fakturMap = [];
            $qty = 0;
            foreach ($blockers as $blocker) {
                $noFaktur = trim((string) ($blocker['no_faktur'] ?? ''));
                if ($noFaktur !== '') {
                    $fakturMap[$noFaktur] = $noFaktur;
                }
                $qty += (float) ($blocker['qty_terjual'] ?? 0);
            }

            $row['total_faktur_terjual'] = count($fakturMap);
            $row['total_item_terjual'] = count($blockers);
            $row['total_qty_terjual'] = $qty;
            $row['sample_faktur'] = implode(', ', array_slice(array_values($fakturMap), 0, 5));
            $candidates[] = $row;
        }

        return $candidates;
    }

    public function rows($limit = 100)
    {
        $this->ensure_schema();
        return $this->db->query("
            SELECT
                r.*,
                SUM(CASE WHEN d.status = 'UNPOSTED' THEN 1 ELSE 0 END) AS total_item_unposted,
                COUNT(d.id_detail) AS total_detail_record,
                SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT d.no_faktur ORDER BY d.tanggal_faktur DESC, d.no_faktur ASC SEPARATOR ', '), ', ', 5) AS sample_faktur
            FROM tb_lpb_revision_request r
            LEFT JOIN tb_lpb_revision_request_detail d ON d.id_request = r.id_request
            GROUP BY r.id_request
            ORDER BY r.requested_at DESC, r.id_request DESC
            LIMIT ?
        ", [(int) $limit])->result_array();
    }

    public function detail($idRequest)
    {
        $this->ensure_schema();
        $request = $this->db
            ->where('id_request', (int) $idRequest)
            ->limit(1)
            ->get('tb_lpb_revision_request')
            ->row_array();

        if (!$request) {
            return null;
        }

        $details = $this->db
            ->where('id_request', (int) $idRequest)
            ->order_by('no_faktur', 'ASC')
            ->order_by('kd_barang', 'ASC')
            ->get('tb_lpb_revision_request_detail')
            ->result_array();

        $logs = $this->db
            ->where('id_request', (int) $idRequest)
            ->order_by('dilakukan_pada', 'DESC')
            ->order_by('id_log', 'DESC')
            ->get('tb_lpb_revision_request_log')
            ->result_array();

        return [
            'request' => $request,
            'details' => $details,
            'logs' => $logs,
        ];
    }

    public function create_request($idLpb, $alasan, $user)
    {
        $this->ensure_schema();
        $this->load->model('M_Logistik');

        $idLpb = (int) $idLpb;
        $alasan = trim((string) $alasan);
        if ($idLpb <= 0) {
            return $this->fail('LPB tidak valid.');
        }
        if ($alasan === '') {
            return $this->fail('Alasan revisi harga LPB wajib diisi.');
        }

        $active = $this->db
            ->where('id_lpb', $idLpb)
            ->where_in('status', [self::STATUS_REQUESTED, self::STATUS_PROCESS, self::STATUS_READY, self::STATUS_LPB_UNPOSTED])
            ->limit(1)
            ->get('tb_lpb_revision_request')
            ->row_array();
        if ($active) {
            return $this->fail('LPB ini sudah memiliki request revisi aktif: ' . $active['no_request']);
        }

        $header = $this->lpb_header($idLpb);
        if (!$header) {
            return $this->fail('Data LPB tidak ditemukan.');
        }
        if ((string) ($header['status_lpb'] ?? '1') !== '1') {
            return $this->fail('Request hanya dapat dibuat untuk LPB berstatus POST.');
        }

        $blockers = $this->M_Logistik->get_lpb_sales_unpost_blockers($idLpb);
        if (empty($blockers)) {
            return $this->fail('LPB ini belum memiliki faktur penjualan aktif berdasarkan barang/lot/expired.');
        }

        $detailRows = $this->prepare_detail_rows($idLpb, $blockers);
        if (empty($detailRows)) {
            return $this->fail('Detail faktur penjualan untuk request revisi tidak ditemukan.');
        }

        $fakturMap = [];
        $qty = 0;
        foreach ($detailRows as $detail) {
            $fakturMap[$detail['no_faktur']] = $detail['no_faktur'];
            $qty += (float) $detail['qty_terjual'];
        }

        $this->db->trans_begin();
        $noRequest = $this->generate_request_number();
        $this->db->insert('tb_lpb_revision_request', [
            'no_request' => $noRequest,
            'id_lpb' => $idLpb,
            'nomor_lpb' => $header['nomor_lpb'],
            'kd_po' => $header['kd_po'],
            'no_po' => $header['no_po'],
            'kd_supplier' => $header['kd_supplier'],
            'nama_supplier' => $header['nama_supplier'],
            'gudang_id' => (string) $header['gudang_id'],
            'status' => self::STATUS_REQUESTED,
            'alasan_revisi' => $alasan,
            'total_faktur' => count($fakturMap),
            'total_item' => count($detailRows),
            'total_qty_terjual' => $qty,
            'requested_by' => $user,
            'requested_at' => date('Y-m-d H:i:s'),
        ]);

        $idRequest = (int) $this->db->insert_id();
        foreach ($detailRows as &$detail) {
            $detail['id_request'] = $idRequest;
        }
        unset($detail);

        if ($idRequest <= 0 || !$this->db->insert_batch('tb_lpb_revision_request_detail', $detailRows)) {
            $this->db->trans_rollback();
            return $this->fail('Request revisi gagal disimpan.');
        }

        $this->insert_log($idRequest, 'CREATE_REQUEST', null, self::STATUS_REQUESTED, 'Purchasing membuat request revisi harga LPB.', null, [
            'id_lpb' => $idLpb,
            'total_faktur' => count($fakturMap),
            'total_item' => count($detailRows),
        ], $user);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->fail('Request revisi gagal disimpan.');
        }

        $this->db->trans_commit();
        return ['success' => true, 'message' => 'Request revisi harga LPB berhasil dibuat.', 'id_request' => $idRequest, 'no_request' => $noRequest];
    }

    public function unpost_sales_invoice($idRequest, $noFaktur, $user)
    {
        $this->ensure_schema();
        $this->load->model('M_SalesOrder');

        $idRequest = (int) $idRequest;
        $noFaktur = trim((string) $noFaktur);
        $request = $this->request_row($idRequest);
        if (!$request) {
            return $this->fail('Request revisi tidak ditemukan.');
        }
        if (!in_array($request['status'], [self::STATUS_REQUESTED, self::STATUS_PROCESS], true)) {
            return $this->fail('Request revisi tidak berada pada status unpost accounting.');
        }

        $details = $this->db
            ->where('id_request', $idRequest)
            ->where('no_faktur', $noFaktur)
            ->where('status', self::STATUS_REQUESTED)
            ->get('tb_lpb_revision_request_detail')
            ->result_array();
        if (empty($details)) {
            return $this->fail('Tidak ada detail faktur yang masih menunggu unpost.');
        }

        $this->db->trans_begin();
        $beforeStatus = $request['status'];
        $modernMap = [];
        $legacyIds = [];
        foreach ($details as $detail) {
            if ($detail['source_table'] === 'tbso_faktur_detail' && (int) $detail['id_faktur'] > 0 && (int) $detail['id_faktur_detail'] > 0) {
                $modernMap[(int) $detail['id_faktur']][] = (int) $detail['id_faktur_detail'];
            } elseif ($detail['source_table'] === 'tb_detail_do' && (int) $detail['source_pk'] > 0) {
                $legacyIds[] = (int) $detail['source_pk'];
            }
        }

        foreach ($modernMap as $idFaktur => $idDetails) {
            $result = $this->M_SalesOrder->repost_item_faktur($idFaktur, array_values(array_unique($idDetails)), $user);
            if (!empty($result['errors'])) {
                $this->db->trans_rollback();
                return $this->fail(implode(' ', $result['errors']));
            }
        }

        if (!empty($legacyIds) && $this->db->table_exists('tb_detail_do')) {
            $this->db->where_in('id', array_values(array_unique($legacyIds)));
            $this->db->update('tb_detail_do', ['status' => 2]);
        }

        $this->db
            ->where('id_request', $idRequest)
            ->where('no_faktur', $noFaktur)
            ->where('status', self::STATUS_REQUESTED)
            ->update('tb_lpb_revision_request_detail', [
                'status' => 'UNPOSTED',
                'unpost_by' => $user,
                'unpost_at' => date('Y-m-d H:i:s'),
                'catatan_accounting' => 'Faktur/item penjualan di-unpost dari request revisi harga LPB.',
            ]);

        $newStatus = $this->remaining_requested_detail($idRequest) > 0 ? self::STATUS_PROCESS : self::STATUS_READY;
        $this->update_request_status($idRequest, $newStatus, [
            'accounting_by' => $user,
            'accounting_at' => date('Y-m-d H:i:s'),
        ]);

        $this->insert_log($idRequest, 'UNPOST_SALES_INVOICE', $beforeStatus, $newStatus, 'Accounting meng-unpost faktur penjualan ' . $noFaktur . '.', ['no_faktur' => $noFaktur], null, $user);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->fail('Unpost faktur penjualan gagal disimpan.');
        }

        $this->db->trans_commit();
        $message = $newStatus === self::STATUS_READY
            ? 'Seluruh faktur penjualan pada request sudah di-unpost. Purchasing sudah dapat unpost LPB.'
            : 'Faktur penjualan berhasil di-unpost.';

        return ['success' => true, 'message' => $message, 'status' => $newStatus];
    }

    public function mark_lpb_unposted($idRequest, $user)
    {
        $this->ensure_schema();
        $request = $this->request_row((int) $idRequest);
        if (!$request) {
            return $this->fail('Request revisi tidak ditemukan.');
        }
        if ($request['status'] !== self::STATUS_READY) {
            return $this->fail('LPB hanya dapat di-unpost setelah semua faktur penjualan request selesai di-unpost.');
        }

        $before = $request['status'];
        $this->update_request_status((int) $idRequest, self::STATUS_LPB_UNPOSTED, [
            'purchasing_by' => $user,
            'purchasing_at' => date('Y-m-d H:i:s'),
        ]);
        $this->insert_log((int) $idRequest, 'UNPOST_LPB', $before, self::STATUS_LPB_UNPOSTED, 'Purchasing meng-unpost LPB untuk proses revisi harga.', null, null, $user);

        return ['success' => true, 'message' => 'Status request diperbarui menjadi LPB_UNPOSTED.'];
    }

    public function finish_revision($idRequest, $user)
    {
        $this->ensure_schema();
        $request = $this->request_row((int) $idRequest);
        if (!$request) {
            return $this->fail('Request revisi tidak ditemukan.');
        }
        if (!in_array($request['status'], [self::STATUS_LPB_UNPOSTED, self::STATUS_READY], true)) {
            return $this->fail('Request belum siap ditandai selesai revisi.');
        }

        $before = $request['status'];
        $this->update_request_status((int) $idRequest, self::STATUS_DONE, [
            'completed_by' => $user,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->insert_log((int) $idRequest, 'FINISH_REVISION', $before, self::STATUS_DONE, 'Purchasing menandai revisi harga LPB selesai.', null, null, $user);

        return ['success' => true, 'message' => 'Request revisi harga LPB selesai.'];
    }

    public function request_row($idRequest)
    {
        return $this->db
            ->where('id_request', (int) $idRequest)
            ->limit(1)
            ->get('tb_lpb_revision_request')
            ->row_array();
    }

    private function lpb_header($idLpb)
    {
        return $this->db->query("
            SELECT
                l.id_lpb,
                l.nomor_lpb,
                l.kd_po,
                l.no_po,
                l.no_invoice,
                l.gudang_id,
                l.status_lpb,
                COALESCE(NULLIF(TRIM(p.kd_suplier), ''), '') AS kd_supplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), p.kd_suplier, '-') AS nama_supplier
            FROM tb_lpb l
            LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
            LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
            WHERE l.id_lpb = ?
            LIMIT 1
        ", [(int) $idLpb])->row_array();
    }

    private function prepare_detail_rows($idLpb, array $blockers)
    {
        $rows = [];
        $seen = [];
        foreach ($blockers as $blocker) {
            $sourceTable = trim((string) ($blocker['source_table'] ?? ''));
            $sourcePk = (string) ($blocker['source_pk'] ?? ($blocker['id_faktur_detail'] ?? ''));
            $noFaktur = trim((string) ($blocker['no_faktur'] ?? ''));
            $kdBarang = trim((string) ($blocker['kd_barang'] ?? ''));
            if ($sourceTable === '' || $sourcePk === '' || $noFaktur === '' || $kdBarang === '') {
                continue;
            }

            $key = $sourceTable . '|' . $sourcePk . '|' . $kdBarang;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $rows[] = [
                'id_lpb' => (int) $idLpb,
                'id_detail_lpb' => (int) ($blocker['id_detail_lpb'] ?? 0) ?: null,
                'source_table' => $sourceTable,
                'source_pk' => $sourcePk,
                'id_faktur' => (int) ($blocker['id_faktur'] ?? 0) ?: null,
                'id_faktur_detail' => (int) ($blocker['id_faktur_detail'] ?? 0) ?: null,
                'no_faktur' => $noFaktur,
                'tanggal_faktur' => $this->normalize_date($blocker['tanggal_faktur'] ?? null),
                'status_faktur_before' => (string) ($blocker['status_faktur'] ?? ''),
                'kd_barang' => $kdBarang,
                'nama_barang' => (string) ($blocker['nama_barang'] ?? $kdBarang),
                'no_lot' => (string) ($blocker['no_lot'] ?? ''),
                'expired_date' => $this->normalize_date($blocker['expired_date'] ?? null),
                'qty_lpb' => (float) ($blocker['qty_lpb'] ?? 0),
                'qty_terjual' => (float) ($blocker['qty_terjual'] ?? 0),
                'status' => self::STATUS_REQUESTED,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        return $rows;
    }

    private function remaining_requested_detail($idRequest)
    {
        return (int) $this->db
            ->where('id_request', (int) $idRequest)
            ->where('status', self::STATUS_REQUESTED)
            ->count_all_results('tb_lpb_revision_request_detail');
    }

    private function update_request_status($idRequest, $status, array $extra = [])
    {
        $data = array_merge($extra, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db
            ->where('id_request', (int) $idRequest)
            ->update('tb_lpb_revision_request', $data);
    }

    private function insert_log($idRequest, $actionType, $statusBefore, $statusAfter, $keterangan, $before = null, $after = null, $user = null)
    {
        return $this->db->insert('tb_lpb_revision_request_log', [
            'id_request' => (int) $idRequest,
            'action_type' => $actionType,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
            'keterangan' => $keterangan,
            'data_before' => $before !== null ? json_encode($before) : null,
            'data_after' => $after !== null ? json_encode($after) : null,
            'dilakukan_oleh' => $user,
            'dilakukan_pada' => date('Y-m-d H:i:s'),
        ]);
    }

    private function generate_request_number()
    {
        $prefix = 'RLPB-' . date('Ymd') . '-';
        $row = $this->db->query("
            SELECT no_request
            FROM tb_lpb_revision_request
            WHERE no_request LIKE ?
            ORDER BY no_request DESC
            LIMIT 1
            FOR UPDATE
        ", [$prefix . '%'])->row_array();

        $next = 1;
        if ($row && !empty($row['no_request'])) {
            $last = (int) substr($row['no_request'], -4);
            $next = $last + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function normalize_date($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function fail($message)
    {
        return ['success' => false, 'message' => $message];
    }
}
