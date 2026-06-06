<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Stockopname extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/M_Stockopname', 'stockopname');
        $this->load->library('Karisma_code_generator');
        $this->guard();
    }

    private function guard()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }

        $lvuser = (int)$this->session->userdata('lv');
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $isAdminDashboard = (bool)$this->session->userdata('is_admin_dashboard') || $username === 'admin' || ($lvuser === 1 && $jobdesk === 'ADMIN');

        $method = $this->router->fetch_method();
        $inputMethods = ['input_opname', 'history_input', 'ajax_input_lookup', 'ajax_input_save'];
        $isStockopnameInputer = $jobdesk === 'STOCKOPNAME' && in_array($method, $inputMethods, true);

        if (!$isAdminDashboard && !$isStockopnameInputer) {
            show_error('Anda tidak memiliki akses ke dashboard admin stockopname.', 403, 'Akses Ditolak');
        }
    }

    private function json($status, $message, $data = [])
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => (bool)$status,
                'message' => $message,
                'data' => $data,
            ]));
    }

    private function post()
    {
        return $this->input->post(null, true) ?: [];
    }

    private function raw_json($data)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function numeric_value($value)
    {
        return str_replace(',', '.', trim((string)$value));
    }

    private function clean_asset_filename($value)
    {
        $value = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim((string)$value));
        $value = trim($value, '_') ?: 'barang';
        return substr($value, 0, 80);
    }

    private function qrcode_scan_value($row)
    {
        return 'OP|' . trim((string)($row['kode_barang'] ?? '')) . '|' . (int)($row['id'] ?? 0);
    }

    private function qrcode_relative_path($row)
    {
        $safeCode = $this->clean_asset_filename($row['kode_barang'] ?? '');
        return 'assets/qrcode/stockopname/OP_' . $safeCode . '_' . (int)($row['id'] ?? 0) . '.png';
    }

    private function qrcode_percent($total, $remaining)
    {
        $total = (int)$total;
        if ($total <= 0) {
            return 100;
        }

        return max(0, min(100, (int)round((($total - (int)$remaining) / $total) * 100)));
    }

    private function asset_payload($path)
    {
        $path = trim((string)$path);
        $exists = $path !== '' && is_file(FCPATH . $path);

        return [
            'path' => $path,
            'url' => $exists ? base_url($path) : '',
            'exists' => $exists,
        ];
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA ERP - Admin Stockopname';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_dashboard.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function monitoring()
    {
        $data['page_title'] = 'KARISMA ERP - Opname Monitoring';
        $data['summary'] = [
            'session' => 'SO-DEMO-2026-06',
            'warehouse' => 'Gudang Karisma Pusat',
            'cutoff' => '2026-06-03 17:00',
            'progress' => 68,
            'scanned_item' => 456,
            'target_item' => 670,
            'variance_item' => 38,
            'pending_approval' => 12,
        ];
        $data['modules'] = [
            ['name' => 'Session Control', 'status' => 'Ready', 'owner' => 'Admin', 'metric' => '1 sesi aktif', 'icon' => 'fa-calendar-check', 'note' => 'Buka, kunci, dan arsip sesi opname.'],
            ['name' => 'Team Assignment', 'status' => 'Demo', 'owner' => 'PIC Gudang', 'metric' => '4 tim', 'icon' => 'fa-users-cog', 'note' => 'Pembagian area, user input, dan target scan.'],
            ['name' => 'Scan Queue', 'status' => 'Ready', 'owner' => 'Opname', 'metric' => '27 antrian', 'icon' => 'fa-qrcode', 'note' => 'Monitoring QR/barcode yang masuk dari mobile.'],
            ['name' => 'Variance Review', 'status' => 'Needs Review', 'owner' => 'Supervisor', 'metric' => '38 selisih', 'icon' => 'fa-balance-scale', 'note' => 'Validasi selisih sebelum adjustment stok.'],
            ['name' => 'FEFO Audit', 'status' => 'Demo', 'owner' => 'QC Gudang', 'metric' => '82% match', 'icon' => 'fa-sort-amount-down', 'note' => 'Cek expired terdekat dan urutan pengeluaran barang.'],
            ['name' => 'Adjustment Approval', 'status' => 'Waiting', 'owner' => 'Manager', 'metric' => '12 approval', 'icon' => 'fa-user-check', 'note' => 'Alur persetujuan koreksi setelah rekonsiliasi.'],
            ['name' => 'Import Export', 'status' => 'Ready', 'owner' => 'Admin', 'metric' => 'CSV/XLS', 'icon' => 'fa-file-import', 'note' => 'Template import master, hasil scan, dan export hasil final.'],
            ['name' => 'Audit Trail', 'status' => 'Ready', 'owner' => 'System', 'metric' => '128 log', 'icon' => 'fa-history', 'note' => 'Jejak perubahan qty, lot, expired, user, dan waktu.'],
        ];
        $data['teams'] = [
            ['team' => 'Tim A', 'area' => 'Rak Pestisida A-C', 'progress' => 82, 'inputer' => 'opname1', 'last_sync' => '10:42'],
            ['team' => 'Tim B', 'area' => 'Rak Benih & Pupuk', 'progress' => 64, 'inputer' => 'opname2', 'last_sync' => '10:39'],
            ['team' => 'Tim C', 'area' => 'Gudang Transit', 'progress' => 57, 'inputer' => 'opname3', 'last_sync' => '10:31'],
            ['team' => 'Tim FEFO', 'area' => 'Expired Control', 'progress' => 73, 'inputer' => 'qcfefo', 'last_sync' => '10:28'],
        ];
        $data['exceptions'] = [
            ['kode' => 'BRG-002', 'barang' => 'Abacel 18 EC 20 X 500 ml', 'exp' => '2027-01-18', 'lot' => 'AB-27A', 'issue' => 'Qty fisik lebih 6 pcs', 'status' => 'Review'],
            ['kode' => 'BRG-014', 'barang' => 'Paclo 15 WP 16 X 5 X 100 gr', 'exp' => '2026-09-30', 'lot' => 'PC-0930', 'issue' => 'Lot ditemukan di area berbeda', 'status' => 'Investigasi'],
            ['kode' => 'BRG-021', 'barang' => 'Karissnail 6 PL 20 X 500 gr', 'exp' => '2026-12-01', 'lot' => '-', 'issue' => 'Belum discan tim opname', 'status' => 'Pending'],
        ];
        $data['timeline'] = [
            ['time' => '08:00', 'event' => 'Cutoff stok demo dibuka', 'type' => 'System'],
            ['time' => '08:20', 'event' => 'Tim A mulai scan area Pestisida', 'type' => 'Input'],
            ['time' => '09:15', 'event' => 'Variance pertama masuk ke review', 'type' => 'Review'],
            ['time' => '10:42', 'event' => 'Sinkronisasi terakhir Tim A', 'type' => 'Sync'],
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_monitoring.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function widgets()
    {
        $summary = $this->stockopname->summary();
        $this->json(true, 'Data dashboard stockopname berhasil dimuat.', [
            'summary' => $summary,
            'master_barang' => $this->stockopname->master_barang_summary(),
            'all_barang_result' => $this->stockopname->all_barang_result_summary($summary),
            'fefo_result' => $this->stockopname->fefo_result_summary(),
        ]);
    }

    public function list()
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->stockopname->datatable($this->post())));
    }

    public function demo_preview()
    {
        $input = $this->post();
        if (trim((string)($input['kode_barang'] ?? '')) === '' || trim((string)($input['nama_barang'] ?? '')) === '') {
            return $this->json(false, 'Kode barang dan nama barang wajib diisi.');
        }

        if (!is_numeric($input['qty_buku'] ?? null) || !is_numeric($input['qty_fisik'] ?? null)) {
            return $this->json(false, 'Qty sistem dan qty fisik harus berupa angka.');
        }

        $this->json(true, 'Simulasi stockopname berhasil dihitung.', $this->stockopname->demo_preview($input));
    }

    public function input_opname()
    {
        $data['page_title'] = 'KARISMA ERP - Input Stockopname';
        $this->stockopname->ensure_master_code_columns();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_input_mobile.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function history_input()
    {
        $inputBy = $this->session->userdata('nama') ?: $this->session->userdata('username') ?: $this->session->userdata('nik') ?: '';
        $data['page_title'] = 'KARISMA ERP - Histori Input Stockopname';
        $data['input_by'] = $inputBy;
        $data['histori'] = $this->stockopname->history_input_by($inputBy);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_history_input.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    private function clean_scan_value($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            return $value;
        }

        if (preg_match('/(?:^|[?&\/#=])(\d+)(?:$|[&\/#])/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    public function ajax_input_lookup()
    {
        $scanValue = $this->clean_scan_value($this->input->post('scan_value', true));
        if ($scanValue === '') {
            return $this->json(false, 'QRCode belum terbaca.');
        }

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->find_master_barang_for_opname($scanValue);
        if (!$row) {
            return $this->json(false, 'Data master dari QRCode tidak ditemukan.');
        }

        $this->json(true, 'Data barang berhasil diisi otomatis.', $row);
    }

    public function ajax_input_save()
    {
        $input = $this->post();
        $masterId = $input['master_id'] ?? '';

        if (!ctype_digit((string)$masterId)) {
            return $this->json(false, 'Scan QRCode barang terlebih dahulu.');
        }

        $qtyPcs = $this->numeric_value($input['qty_pcs'] ?? '0');
        $qtyBox = $this->numeric_value($input['qty_box'] ?? '0');
        if ($qtyPcs === '' || !ctype_digit((string)$qtyPcs)) {
            return $this->json(false, 'Qty pcs harus berupa angka bulat 0 atau lebih.');
        }
        if ($qtyBox === '' || !ctype_digit((string)$qtyBox)) {
            return $this->json(false, 'Qty box harus berupa angka bulat 0 atau lebih.');
        }

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$masterId);
        if (!$row) {
            return $this->json(false, 'Data master barang tidak ditemukan.');
        }

        $qtyPcs = (int)$qtyPcs;
        $qtyBox = (int)$qtyBox;
        if (($qtyPcs + $qtyBox) <= 0) {
            return $this->json(false, 'Isi qty pcs atau qty box terlebih dahulu.');
        }

        $saved = $this->stockopname->save_mobile_opname($row, [
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'input_by' => $this->session->userdata('nama') ?: $this->session->userdata('username') ?: $this->session->userdata('nik') ?: 'system',
            'wilayah' => $this->session->userdata('wilayah') ?: 0,
        ]);

        if (!$saved) {
            return $this->json(false, 'Gagal menyimpan data opname.');
        }

        $this->json(true, 'Data opname berhasil disimpan.', [
            'id' => $saved,
            'kode_barang' => $row['kode_barang'],
            'nama_barang' => $row['nama_barang'],
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
        ]);
    }

    public function master_barang()
    {
        $data['page_title'] = 'KARISMA ERP - Master Opname Stockopname';
        $this->stockopname->ensure_qrcode_columns();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_master_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function master_barang_widgets()
    {
        $this->stockopname->ensure_qrcode_columns();
        $this->json(true, 'Data master opname berhasil dimuat.', [
            'summary' => $this->stockopname->master_barang_summary(),
        ]);
    }

    public function master_barang_list()
    {
        return $this->ajax_master_barang_list();
    }

    public function ajax_master_barang_list()
    {
        $this->stockopname->ensure_qrcode_columns();
        $this->raw_json($this->stockopname->get_master_barang_datatable($this->post()));
    }

    public function ajax_master_barang_detail()
    {
        $id = $this->input->post('id', true);
        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        $this->stockopname->ensure_qrcode_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id);
        if (!$row) {
            return $this->json(false, 'Data barang tidak ditemukan.');
        }

        $this->json(true, 'Detail barang berhasil dimuat.', $row);
    }

    public function ajax_update_master_barang()
    {
        $input = $this->post();
        $id = $input['id'] ?? '';

        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        $kdBarang = trim((string)($input['kd_barang'] ?? ''));
        $namaBarang = trim((string)($input['nama_barang'] ?? ''));
        if ($kdBarang === '') {
            return $this->json(false, 'Kode barang wajib diisi.');
        }
        if ($namaBarang === '') {
            return $this->json(false, 'Nama barang wajib diisi.');
        }

        $numericFields = ['p', 'l', 't', 'berat'];
        foreach ($numericFields as $field) {
            $value = $this->numeric_value($input[$field] ?? '');
            if ($value === '' || !is_numeric($value)) {
                return $this->json(false, strtoupper($field) . ' harus berupa angka.');
            }
            $input[$field] = $value;
        }

        $kubikasi = $this->numeric_value($input['kubikasi'] ?? '0');
        if ($kubikasi !== '' && !is_numeric($kubikasi)) {
            return $this->json(false, 'Kubikasi harus berupa angka.');
        }

        $data = [
            'kd_barang' => $kdBarang,
            'nama_barang' => $namaBarang,
            'satuan' => trim((string)($input['satuan'] ?? '')),
            'p' => $input['p'] + 0,
            'l' => $input['l'] + 0,
            't' => $input['t'] + 0,
            'berat' => $input['berat'] + 0,
            'kubikasi' => $kubikasi === '' ? 0 : $kubikasi,
        ];

        if (!$this->stockopname->update_master_barang((int)$id, $data)) {
            return $this->json(false, 'Gagal update master barang.');
        }

        $this->json(true, 'Master barang berhasil diperbarui.', [
            'id' => (int)$id,
        ]);
    }

    private function ensure_qrcode_ready()
    {
        $ready = $this->stockopname->ensure_qrcode_columns();
        if (empty($ready['success'])) {
            $this->raw_json([
                'success' => false,
                'message' => $ready['message'] ?? 'Struktur database QRCode belum siap.',
                'total' => 0,
                'done' => 0,
                'pending' => 0,
                'failed' => 0,
            ]);
            return false;
        }

        return true;
    }

    public function qrcode_summary()
    {
        if (!$this->ensure_qrcode_ready()) {
            return;
        }

        $this->stockopname->reset_stale_qrcode_process(1);
        $summary = $this->stockopname->qrcode_summary();
        $this->raw_json([
            'success' => true,
            'total' => $summary['total'],
            'done' => $summary['done'],
            'pending' => $summary['pending'],
            'failed' => $summary['failed'],
        ]);
    }

    private function process_qrcode_row($row, $force = false)
    {
        $id = (int)($row['id'] ?? 0);
        $kodeBarang = trim((string)($row['kode_barang'] ?? ''));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID barang tidak valid.',
            ];
        }

        if ($kodeBarang === '') {
            $message = 'Kode barang kosong.';
            $this->stockopname->mark_qrcode_failed($id, $message);
            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $value = $this->qrcode_scan_value($row);
        $relativePath = $this->qrcode_relative_path($row);
        $targetFile = FCPATH . $relativePath;

        if (!$force && strtoupper((string)($row['qrcode_status'] ?? '')) === 'DONE' && is_file($targetFile)) {
            return [
                'success' => true,
                'skipped' => true,
                'value' => $value,
                'path' => $relativePath,
            ];
        }

        try {
            $this->stockopname->mark_qrcode_process($id);
            $created = $this->karisma_code_generator->qrcode($value, $targetFile);
            if (!$created || !is_file($targetFile)) {
                throw new Exception('Gagal membuat file QRCode.');
            }

            if (!$this->stockopname->mark_qrcode_success($id, $value, $relativePath)) {
                throw new Exception('File dibuat, tetapi path QRCode gagal diupdate ke database.');
            }

            return [
                'success' => true,
                'value' => $value,
                'path' => $relativePath,
            ];
        } catch (Throwable $e) {
            $this->stockopname->mark_qrcode_failed($id, $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function run_qrcode_batch($mode = 'normal')
    {
        if (!$this->ensure_qrcode_ready()) {
            return;
        }

        @set_time_limit(25);
        $startedAt = microtime(true);
        $maxRuntime = 20;

        $batchSize = (int)$this->input->post('batch_size', true);
        $batchSize = $batchSize > 0 ? min(100, $batchSize) : 100;
        $force = (string)$this->input->post('force', true) === '1';
        $mode = $mode === 'retry' ? 'retry' : 'normal';

        $this->stockopname->reset_stale_qrcode_process(0);
        $total = $this->stockopname->qrcode_total_count();
        $failedBefore = $this->stockopname->qrcode_failed_count();
        $rows = $this->stockopname->get_qrcode_batch($batchSize, $mode);
        $processed = 0;
        $successCount = 0;
        $failedCount = 0;
        $timeLimited = false;

        foreach ($rows as $row) {
            if ((microtime(true) - $startedAt) >= $maxRuntime) {
                $timeLimited = true;
                break;
            }

            $processed++;
            $result = $this->process_qrcode_row($row, $force);
            if (!empty($result['success'])) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        $summary = $this->stockopname->qrcode_summary();
        if ($mode === 'retry') {
            $remainingFailed = $this->stockopname->qrcode_failed_count();
            $retryTotal = max($failedBefore, $processed + $remainingFailed);
            $this->raw_json([
                'success' => true,
                'mode' => 'retry',
                'batch_size' => $batchSize,
                'processed' => $processed,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'remaining_failed' => $remainingFailed,
                'total_failed' => $retryTotal,
                'percent' => $this->qrcode_percent($retryTotal, $remainingFailed),
                'time_limited' => $timeLimited,
                'is_completed' => $remainingFailed <= 0,
            ]);
            return;
        }

        $remaining = $this->stockopname->qrcode_pending_count();
        $this->raw_json([
            'success' => true,
            'mode' => 'normal',
            'batch_size' => $batchSize,
            'processed' => $processed,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'remaining' => $remaining,
            'total' => $total,
            'done' => $summary['done'],
            'failed' => $summary['failed'],
            'percent' => $this->qrcode_percent($total, $remaining),
            'time_limited' => $timeLimited,
            'is_completed' => $remaining <= 0,
        ]);
    }

    public function qrcode_generate_batch()
    {
        $this->run_qrcode_batch('normal');
    }

    public function qrcode_retry_failed()
    {
        $this->run_qrcode_batch('retry');
    }

    public function qrcode_failed_list()
    {
        if (!$this->ensure_qrcode_ready()) {
            return;
        }

        $rows = $this->stockopname->failed_qrcode_list((int)$this->input->get('limit', true) ?: 100);
        $this->raw_json([
            'success' => true,
            'total' => count($rows),
            'data' => $rows,
        ]);
    }

    public function ajax_generate_qrcode()
    {
        $this->generate_asset('qrcode');
    }

    public function ajax_generate_all_qrcode()
    {
        $this->run_qrcode_batch('normal');
    }

    public function ajax_generate_barcode()
    {
        $this->generate_asset('barcode');
    }

    private function asset_relative_path($row, $type)
    {
        $safeCode = $this->clean_asset_filename(($row['kode_barang'] ?? '') . '_' . ($row['nama_barang'] ?? ''));
        $id = (int)($row['id'] ?? 0);

        if ($type === 'barcode') {
            return 'assets/codegenerator/barcode/barcode_' . $safeCode . '_' . $id . '.png';
        }

        return 'assets/codegenerator/qrcode/qr_' . $safeCode . '_' . $id . '.png';
    }

    private function generate_asset($type)
    {
        $id = $this->input->post('id', true);
        $regenerate = (string)$this->input->post('regenerate', true) === '1';

        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        if ($type === 'qrcode') {
            $ready = $this->stockopname->ensure_qrcode_columns();
            if (empty($ready['success'])) {
                return $this->json(false, $ready['message'] ?? 'Struktur database QRCode belum siap.');
            }
        } else {
            $this->stockopname->ensure_master_code_columns();
        }
        $row = $this->stockopname->get_master_barang_by_id((int)$id);
        if (!$row) {
            return $this->json(false, 'Data barang tidak ditemukan.');
        }

        $kodeBarang = trim((string)($row['kode_barang'] ?? ''));
        if ($kodeBarang === '') {
            return $this->json(false, 'Kode barang wajib diisi sebelum generate asset.');
        }

        $field = $type === 'barcode' ? 'barcode' : 'qrcode';
        if ($type === 'qrcode' && !$regenerate && strtoupper((string)($row['qrcode_status'] ?? '')) === 'DONE' && trim((string)($row['qrcode_file'] ?? $row['qrcode'] ?? '')) !== '') {
            return $this->json(false, 'QRCode sudah ada. Gunakan mode regenerate jika perlu membuat ulang.');
        }
        if ($type !== 'qrcode' && !$regenerate && trim((string)($row[$field] ?? '')) !== '') {
            return $this->json(false, ucfirst($field) . ' sudah ada. Gunakan mode regenerate jika perlu membuat ulang.');
        }

        $scanValue = $type === 'qrcode' ? $this->qrcode_scan_value($row) : (string)(int)$row['id'];
        $relativePath = $type === 'qrcode' ? $this->qrcode_relative_path($row) : $this->asset_relative_path($row, $type);
        $targetFile = FCPATH . $relativePath;

        try {
            if ($type === 'barcode') {
                $created = $this->karisma_code_generator->barcode($scanValue, $targetFile);
            } else {
                $created = $this->karisma_code_generator->qrcode($scanValue, $targetFile);
            }
        } catch (Exception $e) {
            return $this->json(false, $e->getMessage());
        }

        if (!$created || !is_file($targetFile)) {
            return $this->json(false, 'Gagal membuat file asset.');
        }

        $updated = $type === 'qrcode'
            ? $this->stockopname->mark_qrcode_success((int)$id, $scanValue, $relativePath)
            : $this->stockopname->update_asset_master_barang((int)$id, [$field => $relativePath]);

        if (!$updated) {
            return $this->json(false, 'Asset dibuat, tetapi gagal update path ke database.');
        }

        $this->json(true, ucfirst($field) . ' berhasil digenerate.', [
            'id' => (int)$id,
            'type' => $field,
            'value' => $scanValue,
            'path' => $relativePath,
            'url' => base_url($relativePath),
            'qrcode' => $this->asset_payload($field === 'qrcode' ? $relativePath : ($row['qrcode'] ?? '')),
            'barcode' => $this->asset_payload($field === 'barcode' ? $relativePath : ($row['barcode'] ?? '')),
        ]);
    }

    public function ajax_preview_asset()
    {
        $id = $this->input->post('id', true);
        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id);
        if (!$row) {
            return $this->json(false, 'Data barang tidak ditemukan.');
        }

        $this->json(true, 'Preview asset berhasil dimuat.', [
            'id' => (int)$row['id'],
            'kode_barang' => $row['kode_barang'],
            'nama_barang' => $row['nama_barang'],
            'expired_date' => $row['expired_date'],
            'no_lot' => $row['no_lot'],
            'value' => $this->qrcode_scan_value($row),
            'qrcode' => $this->asset_payload($row['qrcode'] ?? ''),
        ]);
    }

    public function print_qrcode($id = null)
    {
        if (!ctype_digit((string)$id)) {
            show_error('ID barang tidak valid.', 400, 'Data Tidak Valid');
        }

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id);
        if (!$row) {
            show_error('Data barang tidak ditemukan.', 404, 'Data Tidak Ditemukan');
        }

        $data = [
            'page_title' => 'Print QRCode - ' . $row['nama_barang'],
            'barang' => $row,
            'qrcode' => $this->asset_payload($row['qrcode'] ?? ''),
            'scan_value' => $this->qrcode_scan_value($row),
        ];

        $this->load->view('content/admin/stockopname_print_qrcode.php', $data);
    }
}
