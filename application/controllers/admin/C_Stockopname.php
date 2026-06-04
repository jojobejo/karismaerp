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
        $this->stockopname->ensure_master_code_columns();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_master_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function master_barang_widgets()
    {
        $this->stockopname->ensure_master_code_columns();
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
        $this->stockopname->ensure_master_code_columns();
        $this->raw_json($this->stockopname->get_master_barang_datatable($this->post()));
    }

    public function ajax_master_barang_detail()
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

    public function ajax_generate_qrcode()
    {
        $this->generate_asset('qrcode');
    }

    public function ajax_generate_all_qrcode()
    {
        @set_time_limit(0);
        $this->stockopname->ensure_master_code_columns();
        $rows = $this->stockopname->get_all_master_barang_for_qrcode();

        $total = count($rows);
        $generated = 0;
        $updated = 0;
        $skipped = 0;
        $failed = [];

        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $kodeBarang = trim((string)($row['kode_barang'] ?? ''));
            if ($id <= 0 || $kodeBarang === '') {
                $skipped++;
                $failed[] = [
                    'id' => $id,
                    'message' => 'Kode barang kosong atau ID tidak valid.',
                ];
                continue;
            }

            $relativePath = $this->asset_relative_path($row, 'qrcode');
            $targetFile = FCPATH . $relativePath;

            try {
                $created = $this->karisma_code_generator->qrcode((string)$id, $targetFile);
            } catch (Exception $e) {
                $skipped++;
                $failed[] = [
                    'id' => $id,
                    'message' => $e->getMessage(),
                ];
                continue;
            }

            if (!$created || !is_file($targetFile)) {
                $skipped++;
                $failed[] = [
                    'id' => $id,
                    'message' => 'Gagal membuat file QRCode.',
                ];
                continue;
            }

            if (!$this->stockopname->update_asset_master_barang($id, ['qrcode' => $relativePath])) {
                $skipped++;
                $failed[] = [
                    'id' => $id,
                    'message' => 'File dibuat, tetapi path gagal diupdate.',
                ];
                continue;
            }

            $generated++;
            $updated++;
        }

        $this->json($generated > 0 || $total === 0, 'Generate QRCode semua barang selesai.', [
            'total' => $total,
            'generated' => $generated,
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => array_slice($failed, 0, 10),
        ]);
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

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id);
        if (!$row) {
            return $this->json(false, 'Data barang tidak ditemukan.');
        }

        $kodeBarang = trim((string)($row['kode_barang'] ?? ''));
        if ($kodeBarang === '') {
            return $this->json(false, 'Kode barang wajib diisi sebelum generate asset.');
        }

        $field = $type === 'barcode' ? 'barcode' : 'qrcode';
        if (!$regenerate && trim((string)($row[$field] ?? '')) !== '') {
            return $this->json(false, ucfirst($field) . ' sudah ada. Gunakan mode regenerate jika perlu membuat ulang.');
        }

        $scanValue = (string)(int)$row['id'];
        $relativePath = $this->asset_relative_path($row, $type);
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

        if (!$this->stockopname->update_asset_master_barang((int)$id, [$field => $relativePath])) {
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
            'value' => (string)(int)$row['id'],
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
            'scan_value' => (string)(int)$row['id'],
        ];

        $this->load->view('content/admin/stockopname_print_qrcode.php', $data);
    }
}
