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
        $inputMethods = [
            'input_opname',
            'history_input',
            'ajax_input_lookup',
            'ajax_input_save',
            'ajax_manual_barang',
            'ajax_manual_lot',
            'ajax_manual_expired',
            'ajax_manual_save',
            'ajax_request_save',
        ];
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

    private function tanggal_indo($date = null)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $timestamp = $date ? strtotime((string)$date) : time();
        if (!$timestamp) {
            $timestamp = time();
        }

        return date('d', $timestamp) . ' ' . $months[(int)date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }

    private function delete_qrcode_files($paths)
    {
        $root = realpath(FCPATH);
        $deleted = 0;
        $failed = 0;

        foreach (array_unique($paths) as $path) {
            $path = trim((string)$path);
            if ($path === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $path)) {
                continue;
            }

            $candidate = $path[0] === DIRECTORY_SEPARATOR ? $path : FCPATH . $path;
            $realFile = realpath($candidate);
            if (!$root || !$realFile || !is_file($realFile)) {
                continue;
            }

            if (strpos($realFile, $root . DIRECTORY_SEPARATOR) !== 0) {
                $failed++;
                continue;
            }

            if (@unlink($realFile)) {
                $deleted++;
            } else {
                $failed++;
            }
        }

        return [
            'deleted' => $deleted,
            'failed' => $failed,
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
        $data['monitoring_summary'] = $this->stockopname->monitoring_summary();
        $data['activity_logs'] = $this->stockopname->monitoring_activity(5);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_monitoring.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function monitoring_activity_log()
    {
        $wilayah = trim((string)$this->input->get('wilayah', true));

        $data['page_title'] = 'KARISMA ERP - Log Aktifitas Stock Opname';
        $data['selected_wilayah'] = $wilayah;
        $data['wilayah_options'] = $this->stockopname->monitoring_activity_wilayah_options();
        $data['activity_logs'] = $this->stockopname->monitoring_activity_log($wilayah, 300);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function monitoring_request_opname()
    {
        $data['page_title'] = 'KARISMA ERP - Request Opname User';
        $data['request_logs'] = $this->stockopname->monitoring_request_opname_rows(500);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_request_opname.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function monitoring_manual_opname()
    {
        $data['page_title'] = 'KARISMA ERP - Input Manual Opname User';
        $data['manual_logs'] = $this->stockopname->monitoring_manual_opname_rows(500);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_manual_opname.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function monitoring_summary()
    {
        $this->json(true, 'Summary monitoring stockopname berhasil dimuat.', $this->stockopname->monitoring_summary());
    }

    public function monitoring_activity()
    {
        $this->json(true, 'Log aktifitas opname berhasil dimuat.', $this->stockopname->monitoring_activity(5));
    }

    public function monitoring_compare_all()
    {
        $this->raw_json($this->stockopname->monitoring_compare_all_datatable($this->post()));
    }

    public function monitoring_compare_lot()
    {
        $this->raw_json($this->stockopname->monitoring_compare_lot_datatable($this->post()));
    }

    public function detail_input_opname($kodeBarang = '')
    {
        $kodeBarang = $kodeBarang ?: $this->input->get('kode_barang', true);
        $kodeBarang = rawurldecode(trim((string)$kodeBarang));
        if ($kodeBarang === '') {
            show_404();
        }

        $data['page_title'] = 'KARISMA ERP - Detail Input Opname';
        $data['kode_barang'] = $kodeBarang;
        $data['compare'] = $this->stockopname->monitoring_compare_all_detail($kodeBarang);
        $data['master_items'] = $this->stockopname->lot_compare_by_kode_barang($kodeBarang);
        $data['input_rows'] = $this->stockopname->input_opname_by_kode_barang($kodeBarang);
        $data['edit_logs'] = $this->stockopname->opname_edit_logs_by_kode_barang($kodeBarang);

        if (empty($data['compare']) && empty($data['master_items']) && empty($data['input_rows'])) {
            show_error('Data input opname untuk kode barang ' . html_escape($kodeBarang) . ' tidak ditemukan.', 404, 'Data Tidak Ditemukan');
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_detail_input_opname.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function ajax_update_input_opname()
    {
        $input = $this->post();
        $id = $input['id'] ?? '';
        $kodeBarang = trim((string)($input['kode_barang'] ?? ''));

        if (!ctype_digit((string)$id) || (int)$id <= 0) {
            return $this->json(false, 'ID input opname tidak valid.');
        }

        if ($kodeBarang === '') {
            return $this->json(false, 'Kode barang tidak valid.');
        }

        $qtyBox = $this->numeric_value($input['qty_box'] ?? '0');
        $qtyPcs = $this->numeric_value($input['qty_pcs'] ?? '0');
        foreach (['Qty box' => $qtyBox, 'Qty pcs' => $qtyPcs] as $label => $value) {
            if ($value === '' || !ctype_digit((string)$value)) {
                return $this->json(false, $label . ' harus berupa angka bulat 0 atau lebih.');
            }
        }

        $payload = [
            'qty_box' => (int)$qtyBox,
            'qty_pcs' => (int)$qtyPcs,
        ];

        $actor = $this->session->userdata('nama') ?: $this->session->userdata('username') ?: $this->session->userdata('nik') ?: 'system';
        $updated = $this->stockopname->update_input_opname((int)$id, $kodeBarang, $payload, $actor);
        if (!$updated['status']) {
            return $this->json(false, $updated['message'] ?? 'Gagal update data input opname.');
        }

        $this->json(true, 'Data input opname berhasil diperbarui.', $updated['data'] ?? []);
    }

    public function widgets()
    {
        $summary = $this->stockopname->summary();
        $expiredLotResult = $this->stockopname->expired_lot_result_summary();
        $this->json(true, 'Data dashboard stockopname berhasil dimuat.', [
            'summary' => $summary,
            'master_barang' => $this->stockopname->master_barang_summary(),
            'all_barang_result' => $this->stockopname->all_barang_result_summary(),
            'expired_lot_result' => $expiredLotResult,
            'fefo_result' => $expiredLotResult,
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

    private function normalize_request_expired_date($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
            return checkdate((int)$matches[2], (int)$matches[1], (int)$matches[3])
                ? $matches[3] . '-' . $matches[2] . '-' . $matches[1]
                : '';
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]) ? $value : '';
        }

        return '';
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
        $qtyPcs = $qtyPcs === '' ? '0' : $qtyPcs;
        $qtyBox = $qtyBox === '' ? '0' : $qtyBox;
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
            'tim_opname' => $this->session->userdata('tim') ?: 0,
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

    public function ajax_manual_barang()
    {
        $term = trim((string)$this->input->get('q', true));
        if ($term === '') {
            $term = trim((string)$this->input->post('q', true));
        }

        $page = (int)($this->input->get('page', true) ?: 1);
        $this->stockopname->ensure_master_code_columns();
        $this->stockopname->ensure_manual_tables();
        $result = $this->stockopname->manual_barang_options($term, $page);
        $this->raw_json($result);
    }

    public function ajax_manual_lot()
    {
        $kodeBarang = trim((string)$this->input->post('kode_barang', true));
        if ($kodeBarang === '') {
            return $this->json(false, 'Pilih nama barang terlebih dahulu.');
        }

        $this->stockopname->ensure_master_code_columns();
        $this->stockopname->ensure_manual_tables();
        $this->json(true, 'Data no lot berhasil dimuat.', $this->stockopname->manual_lot_options($kodeBarang));
    }

    public function ajax_manual_expired()
    {
        $kodeBarang = trim((string)$this->input->post('kode_barang', true));
        $noLot = trim((string)$this->input->post('no_lot', true));
        if ($kodeBarang === '') {
            return $this->json(false, 'Pilih nama barang terlebih dahulu.');
        }
        if ($noLot === '') {
            return $this->json(false, 'Pilih no lot terlebih dahulu.');
        }

        $this->stockopname->ensure_master_code_columns();
        $this->stockopname->ensure_manual_tables();
        $this->json(true, 'Data expired date berhasil dimuat.', $this->stockopname->manual_expired_options($kodeBarang, $noLot));
    }

    public function ajax_manual_save()
    {
        $input = $this->post();
        $sourceId = $input['manual_source_id'] ?? '';
        if (!ctype_digit((string)$sourceId) || (int)$sourceId <= 0) {
            return $this->json(false, 'Lengkapi nama barang, no lot, dan expired date terlebih dahulu.');
        }

        $qtyPcs = $this->numeric_value($input['qty_pcs'] ?? '0');
        $qtyBox = $this->numeric_value($input['qty_box'] ?? '0');
        $qtyPcs = $qtyPcs === '' ? '0' : $qtyPcs;
        $qtyBox = $qtyBox === '' ? '0' : $qtyBox;
        if ($qtyPcs === '' || !ctype_digit((string)$qtyPcs)) {
            return $this->json(false, 'Qty pcs harus berupa angka bulat 0 atau lebih.');
        }
        if ($qtyBox === '' || !ctype_digit((string)$qtyBox)) {
            return $this->json(false, 'Qty box harus berupa angka bulat 0 atau lebih.');
        }

        $qtyPcs = (int)$qtyPcs;
        $qtyBox = (int)$qtyBox;
        if (($qtyPcs + $qtyBox) <= 0) {
            return $this->json(false, 'Isi qty pcs atau qty box terlebih dahulu.');
        }

        $this->stockopname->ensure_master_code_columns();
        $this->stockopname->ensure_manual_tables();
        $row = $this->stockopname->get_master_barang_by_id((int)$sourceId);
        if (!$row) {
            return $this->json(false, 'Data master barang manual tidak ditemukan.');
        }

        $saved = $this->stockopname->save_manual_opname($row, [
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'input_by' => $this->session->userdata('nama') ?: $this->session->userdata('username') ?: $this->session->userdata('nik') ?: 'system',
            'wilayah' => $this->session->userdata('wilayah') ?: 0,
            'tim_opname' => $this->session->userdata('tim') ?: 0,
        ]);

        if (empty($saved['status'])) {
            return $this->json(false, $saved['message'] ?? 'Gagal menyimpan data opname manual.');
        }

        $this->json(true, 'Data opname manual berhasil disimpan untuk review admin.', $saved['data'] ?? []);
    }

    public function ajax_request_save()
    {
        $input = $this->post();
        $kodeBarang = trim((string)($input['request_kode_barang'] ?? ''));
        $noLot = trim((string)($input['request_no_lot'] ?? ''));
        $expiredDate = $this->normalize_request_expired_date($input['request_expired_date'] ?? '');

        if ($kodeBarang === '') {
            return $this->json(false, 'Pilih nama barang terlebih dahulu.');
        }
        if ($noLot === '') {
            return $this->json(false, 'No lot wajib diisi.');
        }
        if ($expiredDate === '') {
            return $this->json(false, 'Expired date wajib format tanggal/bulan/tahun, contoh 15/06/2026.');
        }

        $qtyPcs = $this->numeric_value($input['qty_pcs'] ?? '0');
        $qtyBox = $this->numeric_value($input['qty_box'] ?? '0');
        $qtyPcs = $qtyPcs === '' ? '0' : $qtyPcs;
        $qtyBox = $qtyBox === '' ? '0' : $qtyBox;
        if ($qtyPcs === '' || !ctype_digit((string)$qtyPcs)) {
            return $this->json(false, 'Qty pcs harus berupa angka bulat 0 atau lebih.');
        }
        if ($qtyBox === '' || !ctype_digit((string)$qtyBox)) {
            return $this->json(false, 'Qty box harus berupa angka bulat 0 atau lebih.');
        }

        $qtyPcs = (int)$qtyPcs;
        $qtyBox = (int)$qtyBox;
        if (($qtyPcs + $qtyBox) <= 0) {
            return $this->json(false, 'Isi qty pcs atau qty box terlebih dahulu.');
        }

        $this->stockopname->ensure_master_code_columns();
        $this->stockopname->ensure_manual_tables();
        $row = $this->stockopname->get_first_master_barang_by_kode($kodeBarang);
        if (!$row) {
            return $this->json(false, 'Data master barang request tidak ditemukan.');
        }

        $saved = $this->stockopname->save_request_opname($row, [
            'no_lot' => $noLot,
            'expired_date' => $expiredDate,
            'qty_pcs' => $qtyPcs,
            'qty_box' => $qtyBox,
            'input_by' => $this->session->userdata('nama') ?: $this->session->userdata('username') ?: $this->session->userdata('nik') ?: 'system',
            'wilayah' => $this->session->userdata('wilayah') ?: 0,
            'tim_opname' => $this->session->userdata('tim') ?: 0,
        ]);

        if (empty($saved['status'])) {
            return $this->json(false, $saved['message'] ?? 'Gagal menyimpan opname request.');
        }

        $this->json(true, 'Opname request berhasil disimpan untuk review admin.', $saved['data'] ?? []);
    }

    public function master_barang()
    {
        $data['page_title'] = 'KARISMA ERP - Master Opname Stockopname';
        $data['page_heading'] = 'Master Opname';
        $data['table_title'] = 'Data Master Opname';
        $data['route_base'] = 'admin/stockopname/master_opname';
        $data['qrcode_route_base'] = 'admin/stockopname/qrcode';
        $data['qty_zero_count'] = $this->stockopname->count_all_master_barang('zero');
        $data['show_qty_zero_widget'] = true;
        $data['show_reset_qrcode'] = true;
        $this->stockopname->ensure_qrcode_columns();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/stockopname_master_barang.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function master_barang_qty_zero()
    {
        $data['page_title'] = 'KARISMA ERP - Master Opname Qty 0';
        $data['page_heading'] = 'Master Opname Qty 0';
        $data['table_title'] = 'Data Master Opname Qty 0';
        $data['route_base'] = 'admin/stockopname/master_opname/qty-zero';
        $data['qrcode_route_base'] = 'admin/stockopname/qrcode/qty-zero';
        $data['qty_zero_count'] = $this->stockopname->count_all_master_barang('zero');
        $data['show_qty_zero_widget'] = false;
        $data['show_reset_qrcode'] = false;
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

    public function ajax_master_barang_qty_zero_list()
    {
        $this->stockopname->ensure_qrcode_columns();
        $this->raw_json($this->stockopname->get_master_barang_datatable($this->post(), 'zero'));
    }

    public function ajax_master_barang_detail()
    {
        $id = $this->input->post('id', true);
        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        $this->stockopname->ensure_qrcode_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id, true);
        if (!$row) {
            return $this->json(false, 'Data barang tidak ditemukan.');
        }

        $this->json(true, 'Detail barang berhasil dimuat.', $row);
    }

    public function ajax_master_barang_qty_zero_detail()
    {
        $id = $this->input->post('id', true);
        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        $this->stockopname->ensure_qrcode_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id, 'zero');
        if (!$row) {
            return $this->json(false, 'Data barang qty 0 tidak ditemukan.');
        }

        $this->json(true, 'Detail barang qty 0 berhasil dimuat.', $row);
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

    private function qrcode_summary_response($qtyMode = 'positive')
    {
        if (!$this->ensure_qrcode_ready()) {
            return;
        }

        $this->stockopname->reset_stale_qrcode_process(1);
        $summary = $this->stockopname->qrcode_summary($qtyMode);
        $this->raw_json([
            'success' => true,
            'total' => $summary['total'],
            'done' => $summary['done'],
            'pending' => $summary['pending'],
            'failed' => $summary['failed'],
            'qty_zero_total' => $this->stockopname->count_all_master_barang('zero'),
        ]);
    }

    public function qrcode_summary()
    {
        $this->qrcode_summary_response('positive');
    }

    public function qrcode_qty_zero_summary()
    {
        $this->qrcode_summary_response('zero');
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

    private function run_qrcode_batch($mode = 'normal', $qtyMode = 'positive')
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
        $total = $this->stockopname->qrcode_total_count($qtyMode);
        $failedBefore = $this->stockopname->qrcode_failed_count($qtyMode);
        $rows = $this->stockopname->get_qrcode_batch($batchSize, $mode, $qtyMode);
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

        $summary = $this->stockopname->qrcode_summary($qtyMode);
        if ($mode === 'retry') {
            $remainingFailed = $this->stockopname->qrcode_failed_count($qtyMode);
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

        $remaining = $this->stockopname->qrcode_pending_count($qtyMode);
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

    public function qrcode_qty_zero_generate_batch()
    {
        $this->run_qrcode_batch('normal', 'zero');
    }

    public function qrcode_retry_failed()
    {
        $this->run_qrcode_batch('retry');
    }

    public function qrcode_qty_zero_retry_failed()
    {
        $this->run_qrcode_batch('retry', 'zero');
    }

    private function qrcode_failed_list_response($qtyMode = 'positive')
    {
        if (!$this->ensure_qrcode_ready()) {
            return;
        }

        $rows = $this->stockopname->failed_qrcode_list((int)$this->input->get('limit', true) ?: 100, $qtyMode);
        $this->raw_json([
            'success' => true,
            'total' => count($rows),
            'data' => $rows,
        ]);
    }

    public function qrcode_failed_list()
    {
        $this->qrcode_failed_list_response('positive');
    }

    public function qrcode_qty_zero_failed_list()
    {
        $this->qrcode_failed_list_response('zero');
    }

    public function qrcode_reset()
    {
        if (strtoupper((string)$this->input->method(true)) !== 'POST') {
            return $this->json(false, 'Request reset harus menggunakan POST.');
        }

        if (!$this->ensure_qrcode_ready()) {
            return;
        }

        $paths = $this->stockopname->qrcode_file_paths_for_reset();
        foreach (glob(FCPATH . 'assets/qrcode/stockopname/*') ?: [] as $file) {
            if (is_file($file)) {
                $paths[] = $file;
            }
        }

        $reset = $this->stockopname->reset_qrcode_opname_data();
        if (empty($reset['success'])) {
            return $this->json(false, $reset['message'] ?? 'Gagal reset QRCode opname.');
        }

        $files = $this->delete_qrcode_files($paths);
        $summary = $this->stockopname->qrcode_summary();

        $this->json(true, 'Reset QRCode opname berhasil diproses.', [
            'deleted_files' => $files['deleted'],
            'failed_files' => $files['failed'],
            'opname_rows_deleted' => (int)($reset['opname_rows_deleted'] ?? 0),
            'master_rows_reset' => (int)($reset['master_rows_reset'] ?? 0),
            'summary' => $summary,
        ]);
    }

    public function ajax_generate_qrcode()
    {
        $this->generate_asset('qrcode');
    }

    public function ajax_generate_qrcode_qty_zero()
    {
        $this->generate_asset('qrcode', 'zero');
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

    private function generate_asset($type, $qtyMode = 'positive')
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
        $row = $this->stockopname->get_master_barang_by_id((int)$id, $qtyMode);
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

    private function preview_asset_response($qtyMode = 'positive')
    {
        $id = $this->input->post('id', true);
        if (!ctype_digit((string)$id)) {
            return $this->json(false, 'ID barang tidak valid.');
        }

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id, $qtyMode);
        if (!$row) {
            return $this->json(false, 'Data tidak ditemukan');
        }

        $this->json(true, 'Preview asset berhasil dimuat.', [
            'id' => (int)$row['id'],
            'kode_barang' => $row['kode_barang'],
            'nama_barang' => $row['nama_barang'],
            'expired_date' => $row['expired_date'],
            'no_lot' => $row['no_lot'],
            'qty' => $row['qty'],
            'qty_pcs' => $row['qty_pcs'],
            'qty_box' => $row['qty_box'],
            'inventory_date' => $this->tanggal_indo(),
            'value' => $this->qrcode_scan_value($row),
            'qrcode' => $this->asset_payload($row['qrcode'] ?? ''),
            'barcode' => $this->asset_payload($row['barcode'] ?? ''),
        ]);
    }

    public function ajax_preview_asset()
    {
        $this->preview_asset_response('positive');
    }

    public function ajax_preview_asset_qty_zero()
    {
        $this->preview_asset_response('zero');
    }

    private function print_qrcode_response($id = null, $qtyMode = 'positive')
    {
        if (!ctype_digit((string)$id)) {
            show_error('ID barang tidak valid.', 400, 'Data Tidak Valid');
        }

        $this->stockopname->ensure_master_code_columns();
        $row = $this->stockopname->get_master_barang_by_id((int)$id, $qtyMode);
        if (!$row) {
            show_error('Data barang tidak ditemukan.', 404, 'Data Tidak Ditemukan');
        }

        $data = [
            'page_title' => 'Print Kartu Stock - ' . $row['nama_barang'],
            'barang' => $row,
            'qrcode' => $this->asset_payload($row['qrcode'] ?? ''),
            'scan_value' => $this->qrcode_scan_value($row),
            'inventory_date' => $this->tanggal_indo(),
        ];

        $this->load->view('content/admin/stockopname_print_qrcode.php', $data);
    }

    public function print_qrcode($id = null)
    {
        $this->print_qrcode_response($id, 'positive');
    }

    public function print_qrcode_qty_zero($id = null)
    {
        $this->print_qrcode_response($id, 'zero');
    }

    private function print_preview_asset_response($qtyMode = 'positive')
    {
        $this->stockopname->ensure_qrcode_columns();
        $rows = $this->stockopname->get_master_barang_print_assets($qtyMode);
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'barang' => $row,
                'qrcode' => $this->asset_payload($row['qrcode'] ?? ''),
                'scan_value' => $this->qrcode_scan_value($row),
            ];
        }

        $data = [
            'page_title' => 'Print Preview Asset Stockopname',
            'items' => $items,
            'inventory_date' => $this->tanggal_indo(),
        ];

        $this->load->view('content/admin/stockopname_print_preview_asset.php', $data);
    }

    public function print_preview_asset()
    {
        $this->print_preview_asset_response('positive');
    }

    public function print_preview_asset_qty_zero()
    {
        $this->print_preview_asset_response('zero');
    }
}
