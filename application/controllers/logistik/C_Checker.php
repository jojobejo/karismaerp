<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Checker extends CI_Controller
{
    const ROLE_CHECKER    = 'CHECKER';
    const ROLE_MANAGERCK  = 'MANAGERCK';
    const ROLE_ADMLOG     = 'ADMLOG';
    const ROLE_MANAGER_WH = 'MANAGERWH';
    const ROLE_SALES      = 'SALESCK';
    const ROLE_DIREKTUR   = 'DIREKTURCK';

    private function isDoer()
    {
        return in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK, self::ROLE_MANAGER_WH]);
    }

    private function canOperateSoLoading()
    {
        return in_array($this->role(), [
            self::ROLE_CHECKER,
            self::ROLE_MANAGERCK,
            self::ROLE_MANAGER_WH,
            self::ROLE_ADMLOG,
        ], true);
    }

    private function isMCK()
    {
        return in_array($this->role(), [self::ROLE_MANAGERCK, self::ROLE_MANAGER_WH]);
    }

    // Ahmad Fauzan (fauzan / QIU2502266) dan Admin berhak menentukan & mengubah daftar checker
    private function isCheckerCoordinator()
    {
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $nik      = strtoupper(trim((string)$this->session->userdata('nik')));
        $jobdesk  = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $lv       = (int)$this->session->userdata('lv');
        return ($username === 'fauzan' || $nik === 'QIU2502266' || $jobdesk === 'ADMIN' || $lv >= 99);
    }

    // Validasi apakah user saat ini berhak mengeksekusi penyiapan/loading pada baris rute tertentu
    private function _canExecuteAssignedRoute($row)
    {
        if ($this->isCheckerCoordinator()) {
            return ['ok' => true, 'msg' => ''];
        }
        $nik = trim((string)$this->session->userdata('nik'));
        if (!$this->M_Checker->is_registered_checker($nik)) {
            return ['ok' => false, 'msg' => 'Akun Anda tidak terdaftar dalam daftar checker aktif.'];
        }
        $assignedNik = trim((string)($row['nik_checker'] ?? ''));
        if ($assignedNik === '') {
            return ['ok' => false, 'msg' => 'Checker untuk rute ini belum ditentukan oleh Ahmad Fauzan.'];
        }
        if ($assignedNik !== $nik) {
            return ['ok' => false, 'msg' => 'Loading hanya dapat dilakukan oleh checker yang ditugaskan (' . ($row['nm_checker'] ?? $assignedNik) . ').'];
        }
        return ['ok' => true, 'msg' => ''];
    }

    private function canView()
    {
        return in_array($this->role(), [
            self::ROLE_CHECKER, self::ROLE_MANAGERCK, self::ROLE_ADMLOG,
            self::ROLE_MANAGER_WH, self::ROLE_SALES, self::ROLE_DIREKTUR,
        ]);
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Checker');
        $this->load->model('M_DeliveryTrip');
        $this->load->library('session');
        $this->load->helper('url');
        date_default_timezone_set('Asia/Jakarta');
        if (!$this->session->userdata('nik')) redirect('Auth');
    }

    private function role()
    {
        $jobdesk = strtoupper((string) $this->session->userdata('jobdesk'));
        $lv      = (int) $this->session->userdata('lv');

        if ($jobdesk === 'ADMIN' || $lv >= 99) {
            return self::ROLE_MANAGER_WH;
        }

        if (in_array($jobdesk, ['CHECKER', 'MT CHECKER'], true)) {
            return self::ROLE_CHECKER;
        }

        // User departemen LOGISTIC yang masuk daftar checker aktif oleh Ahmad Fauzan otomatis berperan sebagai CHECKER
        $nik = trim((string)$this->session->userdata('nik'));
        if (!$this->isCheckerCoordinator() && $nik !== '' && $this->M_Checker->is_registered_checker($nik)) {
            return self::ROLE_CHECKER;
        }

        if (in_array($jobdesk, ['MANAGERCK', 'MT MANAGER WAREHOUSE'], true)) {
            return self::ROLE_MANAGERCK;
        }

        if (in_array($jobdesk, ['MANAGERWH', 'MANAGER WAREHOUSE', 'ADMIN DISTRIBUSI & MANAGER WAREHOUSE'], true)) {
            return self::ROLE_MANAGER_WH;
        }

        if (in_array($jobdesk, ['SALES', 'SALESMAN', 'SALESORDER', 'SALES_ORDER', 'SC', 'SALES COUNTER', 'MT SALES COUNTER', 'ADMIN SALES COUNTER'], true)) {
            return self::ROLE_SALES;
        }

        if (in_array($jobdesk, ['LOGISTIK', 'ADMLOGISTIK', 'ADMIN LOGISTIK', 'ADMIN_LOGISTIK', 'MT ADMIN LOGISTIK', 'MT KADEP LOGISTIK', 'DISTRIBUSI', 'MT MANAGER DISTRIBUSI', 'ADMIN GUDANG'], true)) {
            return self::ROLE_ADMLOG;
        }

        if (in_array($jobdesk, ['DIREKTURCK', 'DIREKSI', 'DIREKTUR', 'MT DIREKTUR OPERASIONAL', 'DIREKTUR UTAMA'], true)) {
            return self::ROLE_DIREKTUR;
        }

        return $jobdesk;
    }

    private function nama()
    {
        return $this->session->userdata('nama');
    }

    // ----------------------------------------------------------------
    // HALAMAN
    // ----------------------------------------------------------------
    public function index()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $nik = $this->session->userdata('nik');
        $data['page_title']   = 'KARISMA - Aktivitas Warehouse';
        $data['bongkaran']    = $this->M_Checker->get_list();
        $data['list_kk']      = $this->M_Checker->get_list_kk();
        $data['list_lk']      = $this->M_Checker->get_list_lk();
        $data['role']         = $this->role();
        $data['nik']          = $nik;
        $data['kode_baru']    = $this->M_Checker->generate_kode();
        $data['my_active_id'] = ($this->role() === self::ROLE_CHECKER)
                              ? $this->M_Checker->get_active_id_by_checker($nik)
                              : null;
        $data['is_checker_coordinator'] = $this->isCheckerCoordinator();
        $data['is_registered_checker']  = $this->M_Checker->is_registered_checker($nik);
        $data['list_checker']           = $this->M_Checker->get_list_checker();
        $data['logistic_candidates']    = $this->isCheckerCoordinator()
                              ? $this->M_Checker->get_logistic_candidates()
                              : [];
        $data['kode_baru_kk'] = $this->M_Checker->generate_kode_kk();
        $data['kode_baru_lk'] = $this->M_Checker->generate_kode_lk();
        $data['rute_kk']      = $this->M_Checker->get_rute_by_type('KK');
        $data['rute_lk']      = $this->M_Checker->get_rute_by_type('LK');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function arsip()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $data['page_title']    = 'KARISMA - Arsip Warehouse';
        $data['arsip_bongkar'] = $this->M_Checker->get_arsip_bongkaran();
        $data['arsip_kk']      = $this->M_Checker->get_arsip_kk();
        $data['arsip_lk']      = $this->M_Checker->get_arsip_lk();
        $data['role']          = $this->role();
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/arsip.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function dashboard()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $data['page_title'] = 'KARISMA - Dashboard Warehouse';
        $data['role']       = $this->role();
        $data['bongkaran']  = $this->M_Checker->get_list();
        $data['list_lk']    = $this->M_Checker->get_list_lk();
        $data['list_kk']    = $this->M_Checker->get_list_kk();
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/dashboard.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_lk($id)
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $id  = (int)$id;
        $row = $this->M_Checker->get_lk_by_id($id);
        if (!$row) { show_404(); }
    
        $data['page_title'] = 'Detail Loading LK — ' . ($row['kode'] ?? $id);
        $data['row']        = $row;
        $data['type']       = 'lk';
        $data['role']       = $this->role();
        $data['so_loading_items'] = $this->get_so_loading_items_by_route($row['keterangan']);
    
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_kk($id)
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $id  = (int)$id;
        $row = $this->M_Checker->get_kk_by_id($id);
        if (!$row) { show_404(); }
    
        $data['page_title'] = 'Detail Loading KK — ' . ($row['kode'] ?? $id);
        $data['row']        = $row;
        $data['type']       = 'kk';
        $data['role']       = $this->role();
        $data['so_loading_items'] = $this->get_so_loading_items_by_route($row['keterangan']);
    
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    private function get_so_loading_items_by_route($kd_rute)
    {
        return $this->db->query("
            SELECT
                sod.id,
                so.no_so,
                so.tanggal_transaksi,
                so.customer_name,
                c.nama_kios,
                sod.kd_barang,
                sod.nama_barang,
                sod.no_lot,
                sod.expired_date,
                COALESCE(sod.qty_siap_faktur, sod.qty) AS qty_siap,
                sod.satuan,
                COALESCE(sod.checker_loaded, 0) AS checker_loaded
            FROM tbso_sales_order_detail sod
            JOIN tbso_sales_order so ON so.id_so = sod.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
              AND so.status IN ('siap_faktur', 'partial', 'completed')
              AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
              AND DATE(so.tanggal_transaksi) = (
                  SELECT MAX(DATE(so2.tanggal_transaksi))
                  FROM tbso_sales_order so2
                  LEFT JOIN tb_customer c2 ON c2.kd_customer = so2.kd_customer
                  WHERE COALESCE(NULLIF(so2.kd_rute, ''), c2.kd_rute, 'TANPA_RUTE') = ?
                    AND so2.status IN ('siap_faktur', 'partial', 'completed')
              )
            ORDER BY so.tanggal_transaksi DESC, so.no_so ASC, sod.id ASC
        ", [$kd_rute, $kd_rute])->result_array();
    }

    // ================================================================
    // BONGKARAN
    // ================================================================
    public function store()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_MANAGERCK])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->create([
            'kode_bongkar' => $this->M_Checker->generate_kode(),
            'keterangan'   => $this->input->post('keterangan', true),
            'status'       => 'MENUNGGU',
            'created_by'   => $this->nama(),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran berhasil dibuat' : 'Gagal']);
    }

    public function start()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id    = (int)$this->input->post('id');
        $pintu = (int)$this->input->post('pintu') ?: null;

        if ($this->M_Checker->is_taken($id)) {
            echo json_encode(['status' => false, 'msg' => 'Bongkaran sudah diambil checker lain']); return;
        }
        if ($this->isMCK()) {
            $nik_ck  = $this->input->post('nik_checker', true) ?: $this->session->userdata('nik');
            $nama_ck = $this->input->post('nm_checker',  true) ?: $this->nama();
        } else {
            $nik_ck = $this->session->userdata('nik');
            if ($this->M_Checker->get_active_id_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih memiliki bongkaran aktif']); return;
            }
            if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading aktif yang belum selesai']); return;
            }
            $nama_ck = $this->nama();
        }
        $ok = $this->M_Checker->start($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Start berhasil' : 'Gagal start']);
    }

    public function update_progres()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id      = (int)$this->input->post('id');
        $progres = (int)$this->input->post('progres');
        if (!$this->isMCK()) {
            $checker = $this->M_Checker->get_checker($id);
            if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->update_progres($id, $progres);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal']);
    }

    public function done()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $checker = $this->M_Checker->get_checker($id);
            if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->checker_done($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran selesai!' : 'Gagal']);
    }

    public function update_status_bongkaran()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id     = (int)$this->input->post('id');
        $status = $this->input->post('status', true);
        $ok     = $this->M_Checker->update_status($id, $status);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Status diperbarui' : 'Gagal']);
    }

    public function archive_all_today()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_MANAGERCK, self::ROLE_ADMLOG])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $result = $this->M_Checker->archive_all_done($this->nama());
        $total  = $result['bongkaran'] + $result['kk'] + $result['lk'];
        if ($total === 0) {
            echo json_encode(['status' => false, 'msg' => 'Tidak ada data DONE yang belum diarsipkan']); return;
        }
        echo json_encode([
            'status' => true,
            'msg'    => "Berhasil diarsipkan — Bongkaran: {$result['bongkaran']}, KK: {$result['kk']}, LK: {$result['lk']} data",
        ]);
    }

    public function edit_bongkaran()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_MANAGERCK])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $ket = $this->input->post('keterangan', true);
        if (empty(trim($ket))) {
            echo json_encode(['status' => false, 'msg' => 'Keterangan tidak boleh kosong']); return;
        }
        $row = $this->M_Checker->get_by_id($id);
        if (!$row || $row['status'] !== 'MENUNGGU') {
            echo json_encode(['status' => false, 'msg' => 'Hanya bongkaran berstatus MENUNGGU yang bisa diedit']); return;
        }
        $ok = $this->M_Checker->edit_bongkaran($id, $ket);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran berhasil diupdate' : 'Gagal update']);
    }

    public function hapus_bongkaran()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_MANAGERCK])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_by_id($id);
        if (!$row || $row['status'] !== 'MENUNGGU') {
            echo json_encode(['status' => false, 'msg' => 'Hanya bongkaran berstatus MENUNGGU yang bisa dihapus']); return;
        }
        $ok = $this->M_Checker->hapus_bongkaran($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran berhasil dihapus' : 'Gagal hapus']);
    }

    // ================================================================
    // LOADING KK
    // ================================================================
    public function store_kk()
    {
        if (!in_array($this->role(), [self::ROLE_ADMLOG, self::ROLE_SALES])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->create_kk([
            'kode'       => $this->M_Checker->generate_kode_kk(),
            'tgl'        => date('Y-m-d'),
            'keterangan' => $this->input->post('keterangan', true),
            'status'     => 'MENUNGGU',
            'created_by' => $this->nama(),
        ]);
        $id = $ok ? $this->db->insert_id() : null;
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Data KK ditambahkan' : 'Gagal', 'id' => $id]);
    }

    public function siap_loading_kk()
    {
        if ($this->role() !== self::ROLE_SALES) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        $ok = $this->M_Checker->set_siap_loading_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Status diubah menjadi SIAP LOADING' : 'Gagal']);
    }

    public function update_kk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id   = (int)$this->input->post('id');
        $data = [];
        if ($this->input->post('keterangan') !== null) 
            $data['keterangan'] = $this->input->post('keterangan', true);
        
        if ($this->input->post('status') !== null) {
            $status    = $this->input->post('status', true);
            $row       = $this->M_Checker->get_kk_by_id($id);
            $status_skr = $row['status'] ?? '';

            // Validasi: DO_SELESAI hanya bisa jika sudah CETAK_DO
            if ($status === 'DO_SELESAI' && $status_skr !== 'CETAK_DO') {
                echo json_encode(['status' => false, 'msg' => 'Status harus CETAK DO terlebih dahulu sebelum DO SELESAI']); return;
            }

            $data['status'] = $status;

            if ($status === 'CETAK_DO') {
                $data['waktu_cetak_do'] = date('Y-m-d H:i:s'); 
            } elseif ($status === 'SIAP_LOADING') {
                $data['waktu_cetak_do'] = null;
            }
        }
        $ok = $this->M_Checker->update_kk($id, $data);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'KK diperbarui' : 'Gagal']);
    }

    public function edit_kk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $ket = $this->input->post('keterangan', true);
        if (empty(trim($ket))) {
            echo json_encode(['status' => false, 'msg' => 'Keterangan tidak boleh kosong']); return;
        }
        $row = $this->M_Checker->get_kk_by_id($id);
        if (!$row || !in_array($row['status'], ['MENUNGGU','SIAP_LOADING','CETAK_DO','DO_SELESAI'])) {
            echo json_encode(['status' => false, 'msg' => 'KK sudah diproses, tidak bisa diedit']); return;
        }
        $ok = $this->M_Checker->edit_kk($id, $ket);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK berhasil diupdate' : 'Gagal update']);
    }

    public function hapus_kk()
    {
        if (!in_array($this->role(), [self::ROLE_ADMLOG, self::ROLE_SALES])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_kk_by_id($id);

        $allowed = ($this->role() === self::ROLE_SALES)
            ? ['MENUNGGU', 'SIAP_LOADING']
            : ['MENUNGGU', 'SIAP_LOADING', 'CETAK_DO', 'DO_SELESAI'];

        if (!$row || !in_array($row['status'], $allowed)) {
            echo json_encode(['status' => false, 'msg' => 'Status ini tidak bisa dihapus']); return;
        }
        $ok = $this->M_Checker->hapus_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK berhasil dihapus' : 'Gagal hapus']);
    }

    public function start_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_kk_by_id($id);

        // ← PERBAIKAN: cek status BARANG_SIAP bukan SIAP_LOADING
        if (!$row || $row['status'] !== 'BARANG_SIAP') {
            echo json_encode(['status' => false, 'msg' => 'Status harus BARANG SIAP sebelum bisa start loading']); return;
        }

        $auth = $this->_canExecuteAssignedRoute($row);
        if (!$auth['ok']) {
            echo json_encode(['status' => false, 'msg' => $auth['msg']]); return;
        }

        // Checker & pintu diambil dari data existing (sudah diset saat siapkan barang)
        $nik_ck  = $row['nik_checker'];
        $nama_ck = $row['nm_checker'];
        $pintu   = $row['pintu'];

        // Override hanya jika koordinator (fauzan) mengirim checker valid dari daftar checker aktif
        if ($this->isCheckerCoordinator() && $this->input->post('nik_checker')) {
            $reqNik = trim((string)$this->input->post('nik_checker', true));
            $ck = $this->M_Checker->get_registered_checker($reqNik);
            if (!$ck) {
                echo json_encode(['status' => false, 'msg' => 'Checker tidak terdaftar dalam daftar checker aktif.']); return;
            }
            $nik_ck  = $ck['nik'];
            $nama_ck = $ck['nama_lngkp'];
        }
        if ($this->isCheckerCoordinator() && $this->input->post('pintu')) {
            $pintu = (int)$this->input->post('pintu');
        }
        if (empty($nik_ck)) {
            echo json_encode(['status' => false, 'msg' => 'Checker untuk rute ini belum ditentukan oleh Ahmad Fauzan.']); return;
        }

        $ok = $this->M_Checker->start_kk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK dimulai' : 'Gagal']);
    }

    public function update_progres_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->update_progres_kk($id, (int)$this->input->post('progres'));
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal']);
    }

    public function done_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->done_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK selesai!' : 'Gagal']);
    }

    public function archive_kk()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_MANAGERCK, self::ROLE_ADMLOG])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->archive_kk((int)$this->input->post('id'), $this->nama());
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'KK diarsipkan' : 'Gagal']);
    }

    // ================================================================
    // LOADING LK
    // ================================================================
    public function store_lk()
    {
        if (!in_array($this->role(), [self::ROLE_ADMLOG, self::ROLE_SALES])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->create_lk([
            'kode'       => $this->M_Checker->generate_kode_lk(),
            'tgl'        => date('Y-m-d'),
            'keterangan' => $this->input->post('keterangan', true),
            'status'     => 'MENUNGGU',
            'created_by' => $this->nama(),
        ]);
        $id = $ok ? $this->db->insert_id() : null;
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Data LK ditambahkan' : 'Gagal', 'id' => $id]);
    }

    public function siap_loading_lk()
    {
        if ($this->role() !== self::ROLE_SALES) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        $ok = $this->M_Checker->set_siap_loading_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Status diubah menjadi SIAP LOADING' : 'Gagal']);
    }

    public function update_lk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id   = (int)$this->input->post('id');
        $data = [];
        if ($this->input->post('keterangan') !== null)
            $data['keterangan'] = $this->input->post('keterangan', true);

        if ($this->input->post('status') !== null) {
            $status     = $this->input->post('status', true);
            $row        = $this->M_Checker->get_lk_by_id($id);
            $status_skr = $row['status'] ?? '';

            // Validasi: DO_SELESAI hanya bisa jika sudah CETAK_DO
            if ($status === 'DO_SELESAI' && $status_skr !== 'CETAK_DO') {
                echo json_encode(['status' => false, 'msg' => 'Status harus CETAK DO terlebih dahulu sebelum DO SELESAI']); return;
            }

            $data['status'] = $status;

            if ($status === 'CETAK_DO') {
                $data['waktu_cetak_do'] = date('Y-m-d H:i:s'); // ← ganti nama kolom
            } elseif ($status === 'SIAP_LOADING') {
                $data['waktu_cetak_do'] = null; // ← reset jika mundur
            }
        }
        $ok = $this->M_Checker->update_lk($id, $data);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'LK diperbarui' : 'Gagal']);
    }

    public function edit_lk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $ket = $this->input->post('keterangan', true);
        if (empty(trim($ket))) {
            echo json_encode(['status' => false, 'msg' => 'Keterangan tidak boleh kosong']); return;
        }
        $row = $this->M_Checker->get_lk_by_id($id);
        if (!$row || !in_array($row['status'], ['MENUNGGU','SIAP_LOADING','CETAK_DO','DO_SELESAI'])) {
            echo json_encode(['status' => false, 'msg' => 'LK sudah diproses, tidak bisa diedit']); return;
        }
        $ok = $this->M_Checker->edit_lk($id, $ket);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK berhasil diupdate' : 'Gagal update']);
    }

    public function hapus_lk()
    {
        if (!in_array($this->role(), [self::ROLE_ADMLOG, self::ROLE_SALES])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_lk_by_id($id);

        $allowed = ($this->role() === self::ROLE_SALES)
            ? ['MENUNGGU', 'SIAP_LOADING']
            : ['MENUNGGU', 'SIAP_LOADING', 'CETAK_DO', 'DO_SELESAI'];

        if (!$row || !in_array($row['status'], $allowed)) {
            echo json_encode(['status' => false, 'msg' => 'Status ini tidak bisa dihapus']); return;
        }
        $ok = $this->M_Checker->hapus_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK berhasil dihapus' : 'Gagal hapus']);
    }

    public function start_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_lk_by_id($id);

        // ← PERBAIKAN: cek status BARANG_SIAP bukan SIAP_LOADING
        if (!$row || $row['status'] !== 'BARANG_SIAP') {
            echo json_encode(['status' => false, 'msg' => 'Status harus BARANG SIAP sebelum bisa start loading']); return;
        }

        $auth = $this->_canExecuteAssignedRoute($row);
        if (!$auth['ok']) {
            echo json_encode(['status' => false, 'msg' => $auth['msg']]); return;
        }

        // Checker & pintu diambil dari data existing
        $nik_ck  = $row['nik_checker'];
        $nama_ck = $row['nm_checker'];
        $pintu   = $row['pintu'];

        // Override hanya jika koordinator (fauzan) mengirim checker valid dari daftar checker aktif
        if ($this->isCheckerCoordinator() && $this->input->post('nik_checker')) {
            $reqNik = trim((string)$this->input->post('nik_checker', true));
            $ck = $this->M_Checker->get_registered_checker($reqNik);
            if (!$ck) {
                echo json_encode(['status' => false, 'msg' => 'Checker tidak terdaftar dalam daftar checker aktif.']); return;
            }
            $nik_ck  = $ck['nik'];
            $nama_ck = $ck['nama_lngkp'];
        }
        if ($this->isCheckerCoordinator() && $this->input->post('pintu')) {
            $pintu = (int)$this->input->post('pintu');
        }
        if (empty($nik_ck)) {
            echo json_encode(['status' => false, 'msg' => 'Checker untuk rute ini belum ditentukan oleh Ahmad Fauzan.']); return;
        }

        $ok = $this->M_Checker->start_lk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK dimulai' : 'Gagal']);
    }

    public function update_progres_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->update_progres_lk($id, (int)$this->input->post('progres'));
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal']);
    }

    public function done_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->done_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK selesai!' : 'Gagal']);
    }

    public function archive_lk()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_MANAGERCK, self::ROLE_ADMLOG])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->archive_lk((int)$this->input->post('id'), $this->nama());
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'LK diarsipkan' : 'Gagal']);
    }

    // ================================================================
    // PAUSE / RESUME — BONGKARAN
    // ================================================================
 
    public function pause()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $checker = $this->M_Checker->get_checker($id);
            if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->pause_bongkaran($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran di-pause' : 'Gagal pause (mungkin sudah di-pause)']);
    }
 
    public function resume()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $checker = $this->M_Checker->get_checker($id);
            if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->resume_bongkaran($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran dilanjutkan' : 'Gagal resume']);
    }
 
    // ================================================================
    // PAUSE / RESUME — LOADING KK
    // ================================================================
 
    public function pause_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->pause_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK di-pause' : 'Gagal pause']);
    }
 
    public function resume_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->resume_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK dilanjutkan' : 'Gagal resume']);
    }
 
    // ================================================================
    // PAUSE / RESUME — LOADING LK
    // ================================================================
 
    public function pause_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->pause_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK di-pause' : 'Gagal pause']);
    }
 
    public function resume_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->resume_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK dilanjutkan' : 'Gagal resume']);
    }

    // ================================================================
    // PENYIAPAN BARANG — LOADING KK
    // ================================================================

    public function start_siapkan_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id    = (int)$this->input->post('id');
        $pintu = (int)$this->input->post('pintu') ?: null;
        $row   = $this->M_Checker->get_kk_by_id($id);
        if (!$row || $row['status'] !== 'DO_SELESAI') {
            echo json_encode(['status' => false, 'msg' => 'Status harus DO SELESAI sebelum bisa siapkan barang']); return;
        }
        $auth = $this->_canExecuteAssignedRoute($row);
        if (!$auth['ok']) {
            echo json_encode(['status' => false, 'msg' => $auth['msg']]); return;
        }

        if ($this->isCheckerCoordinator() && $this->input->post('nik_checker')) {
            $reqNik = trim((string)$this->input->post('nik_checker', true));
            $ck = $this->M_Checker->get_registered_checker($reqNik);
            if (!$ck) {
                echo json_encode(['status' => false, 'msg' => 'Checker tidak terdaftar dalam daftar checker aktif.']); return;
            }
            $nik_ck  = $ck['nik'];
            $nama_ck = $ck['nama_lngkp'];
        } else {
            $nik_ck  = trim((string)($row['nik_checker'] ?? ''));
            $nama_ck = trim((string)($row['nm_checker'] ?? ''));
            if ($nik_ck === '') {
                echo json_encode(['status' => false, 'msg' => 'Checker untuk rute ini belum ditentukan oleh Ahmad Fauzan.']); return;
            }
            if (!$this->isCheckerCoordinator()) {
                if ($this->M_Checker->get_active_id_by_checker($nik_ck) !== null) {
                    echo json_encode(['status' => false, 'msg' => 'Anda masih punya bongkaran aktif']); return;
                }
                if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                    echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading/penyiapan aktif']); return;
                }
            }
        }
        $ok = $this->M_Checker->start_siapkan_kk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan barang KK dimulai' : 'Gagal']);
    }

    public function update_progres_siapkan_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->update_progres_siapkan_kk($id, (int)$this->input->post('progres'));
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal']);
    }

    public function done_siapkan_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->done_siapkan_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan selesai! Silakan start loading.' : 'Gagal']);
    }

    public function pause_siapkan_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->pause_siapkan_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan KK di-pause' : 'Gagal pause']);
    }

    public function resume_siapkan_kk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_kk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->resume_siapkan_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan KK dilanjutkan' : 'Gagal resume']);
    }

    // ================================================================
    // PENYIAPAN BARANG — LOADING LK
    // ================================================================

    public function start_siapkan_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id    = (int)$this->input->post('id');
        $pintu = (int)$this->input->post('pintu') ?: null;
        $row   = $this->M_Checker->get_lk_by_id($id);
        if (!$row || $row['status'] !== 'DO_SELESAI') {
            echo json_encode(['status' => false, 'msg' => 'Status harus DO SELESAI sebelum bisa siapkan barang']); return;
        }
        $auth = $this->_canExecuteAssignedRoute($row);
        if (!$auth['ok']) {
            echo json_encode(['status' => false, 'msg' => $auth['msg']]); return;
        }

        if ($this->isCheckerCoordinator() && $this->input->post('nik_checker')) {
            $reqNik = trim((string)$this->input->post('nik_checker', true));
            $ck = $this->M_Checker->get_registered_checker($reqNik);
            if (!$ck) {
                echo json_encode(['status' => false, 'msg' => 'Checker tidak terdaftar dalam daftar checker aktif.']); return;
            }
            $nik_ck  = $ck['nik'];
            $nama_ck = $ck['nama_lngkp'];
        } else {
            $nik_ck  = trim((string)($row['nik_checker'] ?? ''));
            $nama_ck = trim((string)($row['nm_checker'] ?? ''));
            if ($nik_ck === '') {
                echo json_encode(['status' => false, 'msg' => 'Checker untuk rute ini belum ditentukan oleh Ahmad Fauzan.']); return;
            }
            if (!$this->isCheckerCoordinator()) {
                if ($this->M_Checker->get_active_id_by_checker($nik_ck) !== null) {
                    echo json_encode(['status' => false, 'msg' => 'Anda masih punya bongkaran aktif']); return;
                }
                if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                    echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading/penyiapan aktif']); return;
                }
            }
        }
        $ok = $this->M_Checker->start_siapkan_lk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan barang LK dimulai' : 'Gagal']);
    }

    public function update_progres_siapkan_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->update_progres_siapkan_lk($id, (int)$this->input->post('progres'));
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal']);
    }

    public function done_siapkan_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->done_siapkan_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan selesai! Silakan start loading.' : 'Gagal']);
    }

    public function pause_siapkan_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->pause_siapkan_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan LK di-pause' : 'Gagal pause']);
    }

    public function resume_siapkan_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id = (int)$this->input->post('id');
        if (!$this->isMCK()) {
            $row = $this->M_Checker->get_lk_by_id($id);
            if (!$row || $row['nik_checker'] !== $this->session->userdata('nik')) {
                echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
            }
        }
        $ok = $this->M_Checker->resume_siapkan_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan LK dilanjutkan' : 'Gagal resume']);
    }

    // ================================================================
    // GANTI CHECKER — BONGKARAN
    // ================================================================
    public function ganti_checker()
    {
        if (!$this->isMCK()) {
            echo json_encode(['status' => false, 'msg' => 'Hanya Manager Checker yang bisa mengganti checker']); return;
        }
        $id      = (int)$this->input->post('id');
        $nik_ck  = $this->input->post('nik_checker', true);
        $nama_ck = $this->input->post('nm_checker',  true);
        if (!$nik_ck || !$nama_ck) {
            echo json_encode(['status' => false, 'msg' => 'Pilih checker terlebih dahulu']); return;
        }
        $row = $this->M_Checker->get_by_id($id);
        if (!$row || !in_array($row['status'], ['PROSES'])) {
            echo json_encode(['status' => false, 'msg' => 'Hanya bongkaran yang sedang PROSES yang bisa diganti checkernya']); return;
        }
        $ok = $this->M_Checker->ganti_checker_bongkaran($id, $nik_ck, $nama_ck);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Checker berhasil diganti' : 'Gagal mengganti checker']);
    }

    // ================================================================
    // GANTI & PENUGASAN CHECKER — LOADING KK & LK (HANYA AHMAD FAUZAN)
    // ================================================================
    public function ganti_checker_kk()
    {
        if (!$this->isCheckerCoordinator()) {
            echo json_encode(['status' => false, 'msg' => 'Hanya Ahmad Fauzan atau Admin yang dapat mengganti checker.']); return;
        }
        $id     = (int)$this->input->post('id');
        $nik_ck = trim((string)$this->input->post('nik_checker', true));
        $ck     = $this->M_Checker->get_registered_checker($nik_ck);
        if (!$ck) {
            echo json_encode(['status' => false, 'msg' => 'Checker harus berasal dari daftar checker aktif (departemen LOGISTIC).']); return;
        }
        $row = $this->M_Checker->get_kk_by_id($id);
        if (!$row || $row['status'] === 'DONE') {
            echo json_encode(['status' => false, 'msg' => 'Status tidak memungkinkan untuk ganti checker']); return;
        }
        $ok = $this->M_Checker->ganti_checker_kk($id, $ck['nik'], $ck['nama_lngkp']);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Checker KK berhasil ditetapkan ke ' . $ck['nama_lngkp'] : 'Gagal menetapkan checker']);
    }

    public function assign_checker_kk()
    {
        return $this->ganti_checker_kk();
    }

    public function ganti_checker_lk()
    {
        if (!$this->isCheckerCoordinator()) {
            echo json_encode(['status' => false, 'msg' => 'Hanya Ahmad Fauzan atau Admin yang dapat mengganti checker.']); return;
        }
        $id     = (int)$this->input->post('id');
        $nik_ck = trim((string)$this->input->post('nik_checker', true));
        $ck     = $this->M_Checker->get_registered_checker($nik_ck);
        if (!$ck) {
            echo json_encode(['status' => false, 'msg' => 'Checker harus berasal dari daftar checker aktif (departemen LOGISTIC).']); return;
        }
        $row = $this->M_Checker->get_lk_by_id($id);
        if (!$row || $row['status'] === 'DONE') {
            echo json_encode(['status' => false, 'msg' => 'Status tidak memungkinkan untuk ganti checker']); return;
        }
        $ok = $this->M_Checker->ganti_checker_lk($id, $ck['nik'], $ck['nama_lngkp']);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Checker LK berhasil ditetapkan ke ' . $ck['nama_lngkp'] : 'Gagal menetapkan checker']);
    }

    public function assign_checker_lk()
    {
        return $this->ganti_checker_lk();
    }

    // Kelola daftar checker (hanya Ahmad Fauzan, dan hanya user departemen LOGISTIC)
    public function add_checker_master()
    {
        if (!$this->isCheckerCoordinator()) {
            echo json_encode(['status' => false, 'msg' => 'Hanya Ahmad Fauzan atau Admin yang dapat mengubah daftar checker.']); return;
        }
        $nik = trim((string)$this->input->post('nik', true));
        $res = $this->M_Checker->add_checker_master($nik, (string)$this->session->userdata('username'));
        echo json_encode($res);
    }

    public function remove_checker_master()
    {
        if (!$this->isCheckerCoordinator()) {
            echo json_encode(['status' => false, 'msg' => 'Hanya Ahmad Fauzan atau Admin yang dapat mengubah daftar checker.']); return;
        }
        $nik = trim((string)$this->input->post('nik', true));
        $ok  = $this->M_Checker->remove_checker_master($nik);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Checker dihapus dari daftar aktif.' : 'Gagal menghapus checker.']);
    }

    // ================================================================
    // NOTIFIKASI
    // ================================================================
    public function push_notif()
    {
        if ($this->role() !== self::ROLE_SALES) {
            echo json_encode(['status' => false]); return;
        }
        $type = $this->input->post('type', true);
        $ket  = $this->input->post('keterangan', true);
        $ok   = $this->M_Checker->push_notif($type, $ket);
        echo json_encode(['status' => (bool)$ok]);
    }

    public function get_notif()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'data' => []]); return;
        }
        $data = $this->M_Checker->get_unread_notif();
        echo json_encode(['status' => true, 'data' => $data]);
    }

    public function read_notif()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false]); return;
        }
        $ok = $this->M_Checker->mark_notif_read();
        echo json_encode(['status' => (bool)$ok]);
    }
 //controller/c_checker.php
    public function so_loading()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $this->load->model('M_Logistik');
        
        // Tampilkan rute jika:
        // 1. Masih ada item SO yang belum masuk DO (belum selesai dikirim)
        // 2. Status SO siap_faktur, partial, ATAU completed
        // Rute baru hilang dari halaman ini setelah seluruh item dibuatkan DO.
        $routes = $this->db->query("
            SELECT
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                DATE(so.tanggal_transaksi) AS tgl_transaksi,
                MAX(COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute')) AS nama_rute,
                MAX(t.id_trip) AS id_trip,
                COUNT(DISTINCT so.id_so) AS total_so
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN tb_delivery_trip t ON t.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
                AND t.status IN ('DRAFT','VERIFIKASI','SIAP_LOADING','PROSES_LOADING','PROSES_FAKTUR','SIAP_BERANGKAT','MENUNGGU_TAMBAHAN','PROSES_TAMBAHAN')
            WHERE so.status IN ('siap_faktur', 'partial', 'completed')
            AND EXISTS (
                SELECT 1
                FROM tbso_sales_order_detail sod
                WHERE sod.id_so = so.id_so
                    AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
                    AND NOT EXISTS (
                        SELECT 1
                        FROM tbso_faktur_detail fd
                        JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
                        WHERE fd.id_so_detail = sod.id
                    )
            )
            GROUP BY COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE'), DATE(so.tanggal_transaksi)
            ORDER BY DATE(so.tanggal_transaksi) ASC, kd_rute ASC
        ")->result_array();

        foreach ($routes as &$route) {
            $route['activity'] = $this->M_Checker->get_route_loading_activity($route['kd_rute'], $route['id_trip'] ?? null);
        }
        unset($route);

        $data['page_title'] = 'Checker Loading SO - Pilih Rute';
        $data['routes'] = $routes;
        $data['role'] = $this->role();
        $data['nik'] = (string)$this->session->userdata('nik');
        $data['is_checker_coordinator'] = $this->isCheckerCoordinator();
        $data['list_checker'] = $this->M_Checker->get_list_checker();
        $data['logistic_candidates'] = $this->isCheckerCoordinator()
            ? $this->M_Checker->get_logistic_candidates()
            : [];
        $trip_rows = $this->db->where_in('status', ['SIAP_LOADING','PROSES_LOADING','PROSES_FAKTUR','SIAP_BERANGKAT','MENUNGGU_TAMBAHAN','PROSES_TAMBAHAN'])
            ->order_by('id_trip', 'DESC')->get('tb_delivery_trip')->result_array();
        $data['active_trips'] = [];
        foreach ($trip_rows as $trip_row) {
            $this->M_DeliveryTrip->sync_trip_status((int)$trip_row['id_trip']);
            $summary = $this->M_DeliveryTrip->capacity_summary($trip_row['id_trip']);
            if ($summary) {
                $summary['has_do'] = $this->M_DeliveryTrip->check_has_do($trip_row['id_trip']);
                $data['active_trips'][] = $summary;
            }
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/so_loading.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // Halaman riwayat Sales Order yang sudah diproses loading oleh checker
    public function so_loading_riwayat()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }

        $has_filter_param = $this->input->get('filter') !== null
            || $this->input->get('tanggal_dari') !== null
            || $this->input->get('tanggal_sampai') !== null
            || $this->input->get('kd_rute') !== null
            || $this->input->get('status_loading') !== null
            || $this->input->get('q') !== null;

        $tanggal_dari   = trim((string)$this->input->get('tanggal_dari', true));
        $tanggal_sampai = trim((string)$this->input->get('tanggal_sampai', true));
        $kd_rute        = trim((string)$this->input->get('kd_rute', true));
        $status_loading = trim((string)$this->input->get('status_loading', true));
        $q              = trim((string)$this->input->get('q', true));

        // Default tampilkan 30 hari terakhir saat halaman dibuka pertama kali
        if (!$has_filter_param) {
            $tanggal_dari   = date('Y-m-d', strtotime('-30 days'));
            $tanggal_sampai = date('Y-m-d');
        }

        $has_trip_table = $this->db->field_exists('id_trip', 'tbso_sales_order')
            && $this->db->table_exists('tb_delivery_trip');
        $trip_select = $has_trip_table
            ? "MAX(t.kode_trip) AS kode_trip, MAX(t.status) AS trip_status,"
            : "NULL AS kode_trip, NULL AS trip_status,";
        $trip_join = $has_trip_table
            ? "LEFT JOIN tb_delivery_trip t ON t.id_trip = so.id_trip"
            : "";

        $where_clauses = [
            "(sod.checker_loaded IN (1, 2) OR dd.kd_do IS NOT NULL)"
        ];
        $params = [];

        if ($tanggal_dari !== '') {
            $where_clauses[] = "DATE(so.tanggal_transaksi) >= ?";
            $params[] = $tanggal_dari;
        }
        if ($tanggal_sampai !== '') {
            $where_clauses[] = "DATE(so.tanggal_transaksi) <= ?";
            $params[] = $tanggal_sampai;
        }
        if ($kd_rute !== '') {
            $where_clauses[] = "COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?";
            $params[] = $kd_rute;
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            if ($has_trip_table) {
                $where_clauses[] = "(so.no_so LIKE ? OR so.customer_name LIKE ? OR c.nama_kios LIKE ? OR fd.no_faktur LIKE ? OR dd.kd_do LIKE ? OR t.kode_trip LIKE ?)";
                array_push($params, $like, $like, $like, $like, $like, $like);
            } else {
                $where_clauses[] = "(so.no_so LIKE ? OR so.customer_name LIKE ? OR c.nama_kios LIKE ? OR fd.no_faktur LIKE ? OR dd.kd_do LIKE ?)";
                array_push($params, $like, $like, $like, $like, $like);
            }
        }

        $where_sql = implode(' AND ', $where_clauses);

        $having_sql = "";
        if ($status_loading === 'sudah_do') {
            $having_sql = "HAVING COUNT(DISTINCT dd.kd_do) > 0";
        } elseif ($status_loading === 'menunggu_do') {
            $having_sql = "HAVING COUNT(DISTINCT dd.kd_do) = 0 AND SUM(CASE WHEN sod.checker_loaded = 2 THEN 1 ELSE 0 END) = 0";
        } elseif ($status_loading === 'ada_ditolak') {
            $having_sql = "HAVING SUM(CASE WHEN sod.checker_loaded = 2 THEN 1 ELSE 0 END) > 0";
        }

        $rows = $this->db->query("
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.customer_name,
                so.status AS so_status,
                MAX(c.nama_kios) AS nama_kios,
                COALESCE(NULLIF(so.kd_rute, ''), MAX(c.kd_rute), 'TANPA_RUTE') AS kd_rute,
                MAX(COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute')) AS nama_rute,
                {$trip_select}
                COUNT(DISTINCT sod.id) AS total_item,
                COUNT(DISTINCT CASE WHEN (sod.checker_loaded = 1 OR dd.kd_do IS NOT NULL) THEN sod.id END) AS item_dimuat,
                COUNT(DISTINCT CASE WHEN sod.checker_loaded = 2 THEN sod.id END) AS item_tidak_dimuat,
                GROUP_CONCAT(DISTINCT fd.no_faktur ORDER BY fd.no_faktur SEPARATOR ', ') AS no_faktur_list,
                GROUP_CONCAT(DISTINCT dd.kd_do ORDER BY dd.kd_do SEPARATOR ', ') AS kd_do_list
            FROM tbso_sales_order so
            JOIN tbso_sales_order_detail sod ON sod.id_so = so.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            {$trip_join}
            LEFT JOIN tbso_faktur_detail fd ON fd.id_so_detail = sod.id
            LEFT JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
            WHERE {$where_sql}
            GROUP BY so.id_so, so.no_so, so.tanggal_transaksi, so.customer_name, so.status, so.kd_rute
            {$having_sql}
            ORDER BY so.tanggal_transaksi DESC, so.id_so DESC
            LIMIT 500
        ", $params)->result_array();

        // Ambil daftar rute untuk dropdown filter
        $route_options = $this->db->query("
            SELECT DISTINCT
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                MAX(COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute')) AS nama_rute
            FROM tbso_sales_order so
            JOIN tbso_sales_order_detail sod ON sod.id_so = so.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            LEFT JOIN tbso_faktur_detail fd ON fd.id_so_detail = sod.id
            LEFT JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
            WHERE sod.checker_loaded IN (1, 2) OR dd.kd_do IS NOT NULL
            GROUP BY COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE')
            ORDER BY kd_rute ASC
        ")->result_array();

        // Hitung ringkasan statistik
        $summary = [
            'total_so'     => count($rows),
            'sudah_do'     => 0,
            'menunggu_do'  => 0,
            'ada_ditolak'  => 0,
        ];
        foreach ($rows as $row) {
            if ((int)$row['item_tidak_dimuat'] > 0) {
                $summary['ada_ditolak']++;
            } elseif (!empty($row['kd_do_list'])) {
                $summary['sudah_do']++;
            } else {
                $summary['menunggu_do']++;
            }
        }

        $data['page_title']     = 'Riwayat Loading SO - Checker';
        $data['rows']           = $rows;
        $data['route_options']  = $route_options;
        $data['summary']        = $summary;
        $data['role']           = $this->role();
        $data['filter']         = [
            'tanggal_dari'   => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
            'kd_rute'        => $kd_rute,
            'status_loading' => $status_loading,
            'q'              => $q,
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/so_loading_riwayat.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // Endpoint AJAX untuk mengambil detail item SO pada halaman riwayat loading
    public function so_loading_riwayat_detail($id_so)
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->canView()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $id_so = (int)$id_so;
        if ($id_so <= 0) {
            echo json_encode(['status' => false, 'message' => 'ID Sales Order tidak valid.']);
            exit;
        }

        $has_trip_table = $this->db->field_exists('id_trip', 'tbso_sales_order')
            && $this->db->table_exists('tb_delivery_trip');
        $trip_select = $has_trip_table ? "t.kode_trip," : "NULL AS kode_trip,";
        $trip_join   = $has_trip_table ? "LEFT JOIN tb_delivery_trip t ON t.id_trip = so.id_trip" : "";

        $header = $this->db->query("
            SELECT
                so.id_so,
                so.no_so,
                so.tanggal_transaksi,
                so.customer_name,
                so.status AS so_status,
                c.nama_kios,
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                {$trip_select}
                so.create_by
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            {$trip_join}
            WHERE so.id_so = ?
            LIMIT 1
        ", [$id_so])->row_array();

        if (!$header) {
            echo json_encode(['status' => false, 'message' => 'Sales Order tidak ditemukan.']);
            exit;
        }

        $items = $this->db->query("
            SELECT
                sod.id,
                sod.kd_barang,
                COALESCE(b.nama_barang, sod.kd_barang) AS nama_barang,
                COALESCE(b.satuan, 'PCS') AS satuan,
                COALESCE(sod.qty_siap_faktur, sod.qty, 0) AS qty_siap,
                COALESCE(sod.checker_loaded, 0) AS checker_loaded,
                sod.qty_checker_loaded,
                COALESCE(sod.qty_tidak_terkirim, 0) AS qty_tidak_terkirim,
                GROUP_CONCAT(DISTINCT fd.no_faktur ORDER BY fd.no_faktur SEPARATOR ', ') AS no_faktur_list,
                GROUP_CONCAT(DISTINCT dd.kd_do ORDER BY dd.kd_do SEPARATOR ', ') AS kd_do_list
            FROM tbso_sales_order_detail sod
            LEFT JOIN tbpo_barang b ON b.kode_barang = sod.kd_barang
            LEFT JOIN tbso_faktur_detail fd ON fd.id_so_detail = sod.id
            LEFT JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
            WHERE sod.id_so = ?
            GROUP BY sod.id, sod.kd_barang, b.nama_barang, b.satuan, sod.qty_siap_faktur, sod.qty, sod.checker_loaded, sod.qty_checker_loaded, sod.qty_tidak_terkirim
            ORDER BY sod.id ASC
        ", [$id_so])->result_array();

        echo json_encode([
            'status' => true,
            'header' => $header,
            'items'  => $items,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function so_loading_detail($kd_rute)
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $this->load->model('M_Logistik');
        $kd_rute = rawurldecode($kd_rute);
        $activity = $this->M_Checker->get_route_loading_activity($kd_rute);
        $show_history = $activity && ($activity['status'] ?? '') === 'DONE';

        // Tampilkan item yang belum masuk DO dan masih perlu diverifikasi checker
        // Status SO: siap_faktur, partial, ATAU completed
        // checker_loaded: NULL/0 = belum dipilih, 1 = dimuat, 2 = tidak dimuat
        // Item dengan checker_loaded = 1 tidak ditampilkan karena sudah siap untuk DO
        $tgl_transaksi = $this->input->get('date', true);
        $date_filter = "";
        $params = [$kd_rute];
        if (!empty($tgl_transaksi)) {
            $date_filter = "AND DATE(so.tanggal_transaksi) = ?";
            $params[] = $tgl_transaksi;
        }
        $do_filter = $show_history ? '' : "AND NOT EXISTS (
            SELECT 1
            FROM tbso_faktur_detail fd
            JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
            WHERE fd.id_so_detail = sod.id
        )";

        $items = $this->db->query("
            SELECT 
                sod.*, 
                so.no_so, 
                so.customer_name, 
                c.nama_kios,
                b.nama_barang, b.satuan,
                (CASE 
                    WHEN COALESCE(sod.isi_per_box, 0) > 0 THEN sod.isi_per_box
                    WHEN COALESCE(b.isi, 0) > 0 THEN b.isi
                    WHEN (COALESCE(b.panjang, 0) * COALESCE(b.lebar, 0) * COALESCE(b.tinggi, 0)) > 0 
                        THEN (COALESCE(b.panjang, 0) * COALESCE(b.lebar, 0) * COALESCE(b.tinggi, 0))
                    ELSE 1 
                END) AS isi_per_box
            FROM tbso_sales_order_detail sod
            JOIN tbso_sales_order so ON so.id_so = sod.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tbpo_barang b ON b.kode_barang = sod.kd_barang
            WHERE so.status IN ('siap_faktur', 'partial', 'completed')
              AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
              $date_filter
              AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
              $do_filter
            ORDER BY so.no_so ASC, sod.id ASC
        ", $params)->result_array();

        $data['page_title'] = 'Checker Loading SO - Detail Rute ' . $kd_rute;
        $data['kd_rute'] = $kd_rute;
        $data['items'] = $items;
        $data['role'] = $this->role();
        $data['activity'] = $activity;
        $id_trip = (int)($activity['id_trip'] ?? 0);
        if (!$id_trip) {
            $active_trip = $this->M_DeliveryTrip->get_active_by_route($kd_rute);
            $id_trip = (int)($active_trip['id_trip'] ?? 0);
        }
        $data['trip'] = $id_trip ? $this->M_DeliveryTrip->capacity_summary($id_trip) : null;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/so_loading_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function assign_checker_so_rute()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post' || !$this->isCheckerCoordinator()) {
            echo json_encode(['status' => false, 'message' => 'Hanya Ahmad Fauzan atau Admin yang dapat menentukan checker rute.']);
            exit;
        }

        $kd_rute = strtoupper(trim((string)$this->input->post('kd_rute', true)));
        $id_trip = (int)$this->input->post('id_trip');
        $nik_ck  = trim((string)$this->input->post('nik_checker', true));
        if ($kd_rute === '') {
            echo json_encode(['status' => false, 'message' => 'Kode rute tidak valid.']);
            exit;
        }

        $ck = $this->M_Checker->get_registered_checker($nik_ck);
        if (!$ck) {
            echo json_encode(['status' => false, 'message' => 'Checker harus berasal dari daftar checker aktif (departemen LOGISTIC).']);
            exit;
        }

        $activity = $this->M_Checker->ensure_route_loading_activity($kd_rute, $ck['nama_lngkp'], $id_trip ?: null);
        if (!$activity) {
            echo json_encode(['status' => false, 'message' => 'Rute tidak terdaftar sebagai LK/KK.']);
            exit;
        }
        if (($activity['status'] ?? '') === 'DONE') {
            echo json_encode(['status' => false, 'message' => 'Loading rute sudah selesai (DONE).']);
            exit;
        }

        $ok = ($activity['loading_type'] === 'kk')
            ? $this->M_Checker->assign_checker_kk((int)$activity['id'], $ck['nik'], $ck['nama_lngkp'])
            : $this->M_Checker->assign_checker_lk((int)$activity['id'], $ck['nik'], $ck['nama_lngkp']);

        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok ? 'Checker rute ' . $kd_rute . ' ditetapkan ke ' . $ck['nama_lngkp'] : 'Gagal menetapkan checker.'
        ]);
        exit;
    }

    public function start_so_loading_rute()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post' || !$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $kd_rute = strtoupper(trim((string)$this->input->post('kd_rute', true)));
        $id_trip = (int)$this->input->post('id_trip');
        if ($kd_rute === '') {
            echo json_encode(['status' => false, 'message' => 'Kode rute tidak valid.']);
            exit;
        }

        $nik = (string)$this->session->userdata('nik');
        $nama = (string)($this->nama() ?: $this->session->userdata('username'));
        $activity = $this->M_Checker->ensure_route_loading_activity($kd_rute, $nama, $id_trip ?: null);
        if (!$activity) {
            echo json_encode(['status' => false, 'message' => 'Rute tidak terdaftar sebagai LK/KK atau aktivitas loading gagal disiapkan.']);
            exit;
        }
        $auth = $this->_canExecuteAssignedRoute($activity);
        if (!$auth['ok']) {
            echo json_encode(['status' => false, 'message' => $auth['msg']]);
            exit;
        }
        if ($activity['status'] === 'PROSES_LOADING' && !empty($activity['waktu_mulai'])) {
            echo json_encode(['status' => true, 'message' => 'Loading sudah dimulai sebelumnya.']);
            exit;
        }

        $targetNik  = !empty($activity['nik_checker']) ? $activity['nik_checker'] : $nik;
        $targetNama = !empty($activity['nm_checker']) ? $activity['nm_checker'] : $nama;
        $ok = $this->M_Checker->start_route_loading($kd_rute, $targetNik, $targetNama, $id_trip ?: null);
        if ($ok && !empty($activity['id_trip'])) {
            $trip = $this->M_DeliveryTrip->get((int)$activity['id_trip']);
            $next_status = $trip && $trip['status'] === 'PROSES_TAMBAHAN' ? 'PROSES_TAMBAHAN' : 'PROSES_LOADING';
            $this->M_DeliveryTrip->set_status((int)$activity['id_trip'], $next_status);
        }

        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok ? 'Loading rute ' . $kd_rute . ' dimulai.' : 'Gagal memulai loading.'
        ]);
        exit;
    }

    public function cancel_so_loading_rute()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post' || !$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $kd_rute = strtoupper(trim((string)$this->input->post('kd_rute', true)));
        if ($kd_rute === '') {
            echo json_encode(['status' => false, 'message' => 'Kode rute tidak valid.']);
            exit;
        }

        $activity = $this->M_Checker->get_route_loading_activity($kd_rute);
        if (!$activity) {
            echo json_encode(['status' => false, 'message' => 'Aktivitas loading untuk rute ini tidak ditemukan.']);
            exit;
        }

        if ($activity['status'] === 'DONE') {
            echo json_encode(['status' => false, 'message' => 'Loading rute ini sudah selesai (DONE) dan tidak dapat dibatalkan.']);
            exit;
        }

        $ok = $this->M_Checker->cancel_route_loading($kd_rute);
        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok ? 'Start loading rute ' . $kd_rute . ' berhasil dibatalkan dan dikembalikan ke status Siap Loading.' : 'Gagal membatalkan loading.'
        ]);
        exit;
    }

    public function pause_so_loading_rute()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post' || !$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $kd_rute = strtoupper(trim((string)$this->input->post('kd_rute', true)));
        $action = strtolower(trim((string)$this->input->post('action', true)));
        if ($kd_rute === '' || !in_array($action, ['pause', 'resume'], true)) {
            echo json_encode(['status' => false, 'message' => 'Data pause tidak valid.']);
            exit;
        }

        $ok = $this->M_Checker->set_route_loading_pause($kd_rute, $action === 'pause');
        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok
                ? ($action === 'pause' ? 'Loading berhasil di-pause.' : 'Loading berhasil dilanjutkan.')
                : 'Gagal mengubah status pause loading.'
        ]);
        exit;
    }

    public function prepare_so_loading_rute()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post' || !$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $kd_rute = strtoupper(trim((string)$this->input->post('kd_rute', true)));
        $action = strtolower(trim((string)$this->input->post('action', true)));
        if ($kd_rute === '' || !in_array($action, ['start', 'finish'], true)) {
            echo json_encode(['status' => false, 'message' => 'Data penyiapan barang tidak valid.']);
            exit;
        }

        $ok = $this->M_Checker->set_route_loading_preparation($kd_rute, $action === 'start');
        echo json_encode([
            'status'  => (bool)$ok,
            'message' => $ok
                ? ($action === 'start'
                    ? 'Penyiapan barang dimulai.'
                    : 'Penyiapan barang selesai. Proses loading dapat dilanjutkan.')
                : 'Gagal mengubah proses penyiapan barang.'
        ]);
        exit;
    }

    public function toggle_so_item_loaded()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Method tidak valid']);
            exit;
        }
        if (!$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $id_detail = (int)$this->input->post('id_detail');
        $loaded = (int)$this->input->post('loaded');
        $qty_loaded_input = $this->input->post('qty_loaded', true);
        if (!in_array($loaded, [0, 1, 2])) $loaded = 0;

        if ($id_detail <= 0) {
            echo json_encode(['status' => false, 'message' => 'ID item tidak valid']);
            exit;
        }

        $detail = $this->db->get_where('tbso_sales_order_detail', ['id' => $id_detail])->row_array();
        if (!$detail) {
            echo json_encode(['status' => false, 'message' => 'Item tidak ditemukan']);
            exit;
        }

        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $detail['id_so']])->row_array();
        $c = $this->db->get_where('tb_customer', ['kd_customer' => $so['kd_customer']])->row_array();
        $kd_rute = trim((string)(($so['kd_rute'] ?? '') ?: ($c['kd_rute'] ?? '')));

        $activity = $this->M_Checker->get_route_loading_activity($kd_rute);
        if (!$activity || $activity['status'] !== 'PROSES_LOADING' || empty($activity['waktu_mulai'])) {
            echo json_encode(['status' => false, 'message' => 'Klik Start Loading terlebih dahulu.']);
            exit;
        }
        if (!empty($activity['is_paused'])) {
            echo json_encode(['status' => false, 'message' => 'Loading sedang di-pause. Klik Lanjutkan terlebih dahulu.']);
            exit;
        }

        $qty_siap = $detail['qty_siap_faktur'] !== null
            ? (float)$detail['qty_siap_faktur']
            : (float)$detail['qty'];
        $qty_faktur = (float)($detail['qty_faktur'] ?? 0);
        $qty_maks_muat = max(0, $qty_siap - $qty_faktur);
        $qty_loaded = $loaded === 1
            ? ($qty_loaded_input === null || $qty_loaded_input === '' ? $qty_maks_muat : (float)$qty_loaded_input)
            : 0;

        if ($loaded === 1 && ($qty_loaded <= 0 || $qty_loaded > $qty_maks_muat + 0.001)) {
            echo json_encode([
                'status' => false,
                'message' => 'Qty dimuat harus lebih dari 0 dan tidak boleh melebihi qty siap (' . $qty_maks_muat . ').'
            ]);
            exit;
        }

        $this->db->where('id', $id_detail);
        $this->db->update('tbso_sales_order_detail', [
            'checker_loaded' => $loaded,
            'qty_checker_loaded' => $loaded === 1 ? $qty_loaded : null,
            'qty_tidak_terkirim' => $loaded === 1 ? max(0, $qty_maks_muat - $qty_loaded) : $qty_maks_muat,
        ]);

        $username = $this->session->userdata('username') ?? $this->session->userdata('nama') ?? 'system';
        $progress = $this->M_Checker->update_route_loading_progress(
            $kd_rute,
            $so['tanggal_transaksi'] ?? null
        );

        echo json_encode([
            'status' => true,
            'message' => 'Status muat berhasil diperbarui',
            'qty_loaded' => $qty_loaded,
            'qty_remaining' => max(0, $qty_maks_muat - $qty_loaded),
            'progress' => $progress,
            'created_do' => null
        ]);
        exit;
    }

    public function selesai_loading_rute()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Method tidak valid']);
            exit;
        }

        $kd_rute = trim($this->input->post('kd_rute'));
        if (empty($kd_rute)) {
            echo json_encode(['status' => false, 'message' => 'Kode rute tidak valid']);
            exit;
        }
        if (!$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

        $activity = $this->M_Checker->get_route_loading_activity($kd_rute);
        if (!$activity || $activity['status'] !== 'PROSES_LOADING' || empty($activity['waktu_mulai'])) {
            echo json_encode(['status' => false, 'message' => 'Loading belum dimulai. Klik Start terlebih dahulu.']);
            exit;
        }
        if (!empty($activity['is_paused'])) {
            echo json_encode(['status' => false, 'message' => 'Loading sedang di-pause. Klik Lanjutkan terlebih dahulu.']);
            exit;
        }

        $tgl_transaksi = $this->input->post('date', true);
        $date_filter = "";
        $params = [$kd_rute];
        if (!empty($tgl_transaksi)) {
            $date_filter = "AND DATE(so.tanggal_transaksi) = ?";
            $params[] = $tgl_transaksi;
        }

        // Cek item belum dipilih sama sekali (0/null)
        $belum = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order_detail sod
            JOIN tbso_sales_order so ON so.id_so = sod.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE so.status IN ('siap_faktur', 'partial', 'completed')
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            $date_filter
            AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
            AND NOT EXISTS (
                SELECT 1
                FROM tbso_faktur_detail fd
                JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
                WHERE fd.id_so_detail = sod.id
            )
            AND (sod.checker_loaded IS NULL OR sod.checker_loaded = 0)
        ", $params)->row_array();

        if ((int)$belum['total'] > 0) {
            echo json_encode([
                'status'  => false,
                'message' => 'Masih ada ' . $belum['total'] . ' item yang belum dipilih (✅/❌). Harap pilih semua item terlebih dahulu.'
            ]);
            exit;
        }

        // Cek apakah ada item yang di-X (checker_loaded = 2)
        $ada_ditolak = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order_detail sod
            JOIN tbso_sales_order so ON so.id_so = sod.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE so.status IN ('siap_faktur', 'partial', 'completed')
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            $date_filter
            AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
            AND NOT EXISTS (
                SELECT 1
                FROM tbso_faktur_detail fd
                JOIN tb_detail_do dd ON dd.kd_faktur = fd.no_faktur
                WHERE fd.id_so_detail = sod.id
            )
            AND sod.checker_loaded = 2
        ", $params)->row_array();

        $this->load->model('M_Logistik');
        $username = $this->session->userdata('username') ?? $this->session->userdata('nama') ?? 'system';

        if ((int)$ada_ditolak['total'] > 0) {
            if (!$this->M_Checker->finish_route_loading($kd_rute)) {
                echo json_encode(['status' => false, 'message' => 'Gagal mencatat waktu selesai loading.']);
                exit;
            }
            // Ada item X — tandai selesai tapi jangan buat DO
            // Kembalikan response sukses dengan pesan instruksi untuk Admin SC
            echo json_encode([
                'status'      => true,
                'created_do'  => null,
                'ada_ditolak' => true,
                'message'     => (int)$ada_ditolak['total'] . ' item tidak termuat. Admin SC perlu melakukan repost faktur untuk item tersebut sebelum DO dapat dibuat.'
            ]);
            exit;
        }

        // Semua item dimuat (checker_loaded = 1) — coba buat DO
        $id_trip = (int)($activity['id_trip'] ?? 0);
        $created_do = $this->M_Logistik->check_and_auto_create_do($kd_rute, $username, false, $id_trip ?: null);

        if (!$this->M_Checker->finish_route_loading($kd_rute)) {
            echo json_encode(['status' => false, 'message' => 'Gagal mencatat waktu selesai loading.']);
            exit;
        }

        echo json_encode([
            'status'      => true,
            'created_do'  => $created_do ? $created_do['kd_do'] : null,
            'ada_ditolak' => false,
            'message'     => $created_do
                ? (!empty($created_do['merged'])
                    ? 'Muatan tambahan berhasil digabung ke DO ' . $created_do['kd_do'] . '.'
                    : 'DO berhasil dibuat.')
                : 'Loading selesai. Menunggu faktur Admin SC sebelum DO dapat dibuat.'
        ]);
        exit;
    }

    public function open_additional_load()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        if ($this->input->method() !== 'post' || !$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']); exit;
        }
        $id_trip = (int)$this->input->post('id_trip');
        $trip = $this->M_DeliveryTrip->capacity_summary($id_trip);
        if (!$trip) {
            echo json_encode(['status' => false, 'message' => 'Trip tidak ditemukan.']); exit;
        }
        $has_do = $this->M_DeliveryTrip->check_has_do($id_trip);
        if (!$has_do && ($trip['status'] ?? '') !== 'SIAP_BERANGKAT') {
            echo json_encode(['status' => false, 'message' => 'Tambahan muatan hanya dapat dibuka setelah DO awal terbentuk.']); exit;
        }
        if ($trip['remaining_tonase'] <= 0 || $trip['remaining_kubikasi'] <= 0) {
            echo json_encode(['status' => false, 'message' => 'Kapasitas trip sudah penuh.']); exit;
        }
        $by = $this->session->userdata('username') ?: $this->nama() ?: 'system';
        $ok = $this->M_DeliveryTrip->open_additional($id_trip, $by);
        echo json_encode(['status' => (bool)$ok, 'message' => $ok
            ? 'Permintaan tambahan muatan dibuka. Sales dapat menambahkan SO pada rute ini.'
            : 'Gagal membuka tambahan muatan.']);
        exit;
    }

    public function close_delivery_trip()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        if ($this->input->method() !== 'post' || !$this->canOperateSoLoading()) {
            echo json_encode(['status' => false, 'message' => 'Akses ditolak.']); exit;
        }
        $id_trip = (int)$this->input->post('id_trip');
        $total_so = $this->db->where('id_trip', $id_trip)->count_all_results('tbso_sales_order');
        $has_do = $this->M_DeliveryTrip->check_has_do($id_trip);
        if ($total_so > 0 && !$has_do && ($trip['status'] ?? '') !== 'SIAP_BERANGKAT') {
            echo json_encode(['status' => false, 'message' => 'Trip belum memiliki DO dan belum dapat ditutup sebagai keberangkatan.']); exit;
        }
        $by = $this->session->userdata('username') ?: $this->nama() ?: 'system';
        $ok = $this->M_DeliveryTrip->close($id_trip, $by);
        echo json_encode(['status' => (bool)$ok, 'message' => $ok ? 'Trip ditutup dan tidak menerima muatan tambahan.' : 'Gagal menutup trip.']);
        exit;
    }
}
