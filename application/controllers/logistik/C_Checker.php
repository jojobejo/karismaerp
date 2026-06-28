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
        return in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK]);
    }

    private function isMCK()
    {
        return $this->role() === self::ROLE_MANAGERCK;
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
        $this->load->library('session');
        $this->load->helper('url');
        date_default_timezone_set('Asia/Jakarta');
        if (!$this->session->userdata('nik')) redirect('Auth');
    }

    private function role()
    {
        $jobdesk = strtoupper((string) $this->session->userdata('jobdesk'));

        if (in_array($jobdesk, ['SALES', 'SALESMAN', 'SALESORDER', 'SALES_ORDER', 'SC'], true)) {
            return self::ROLE_SALES;
        }

        if (in_array($jobdesk, ['LOGISTIK', 'ADMLOGISTIK', 'ADMIN LOGISTIK', 'ADMIN_LOGISTIK', 'DISTRIBUSI'], true)) {
            return self::ROLE_ADMLOG;
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
        $data['list_checker'] = $this->isMCK()
                              ? $this->M_Checker->get_list_checker()
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
    
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // BONGKARAN
    // ================================================================
    public function store()
    {
        if ($this->role() !== self::ROLE_MANAGER_WH) {
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
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_ADMLOG])) {
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
        if ($this->role() !== self::ROLE_MANAGER_WH) {
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
        if ($this->role() !== self::ROLE_MANAGER_WH) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_kk_by_id($id);

        // ← PERBAIKAN: cek status BARANG_SIAP bukan SIAP_LOADING
        if (!$row || $row['status'] !== 'BARANG_SIAP') {
            echo json_encode(['status' => false, 'msg' => 'Status harus BARANG SIAP sebelum bisa start loading']); return;
        }

        // Checker & pintu diambil dari data existing (sudah diset saat siapkan barang)
        $nik_ck  = $row['nik_checker'];
        $nama_ck = $row['nm_checker'];
        $pintu   = $row['pintu'];

        // Override jika MANAGERCK kirim data baru
        if ($this->isMCK()) {
            if ($this->input->post('nik_checker')) $nik_ck  = $this->input->post('nik_checker', true);
            if ($this->input->post('nm_checker'))  $nama_ck = $this->input->post('nm_checker',  true);
            if ($this->input->post('pintu'))       $pintu   = (int)$this->input->post('pintu');
        }

        $ok = $this->M_Checker->start_kk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK dimulai' : 'Gagal']);
    }

    public function update_progres_kk()
    {
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_ADMLOG])) {
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

        // Checker & pintu diambil dari data existing
        $nik_ck  = $row['nik_checker'];
        $nama_ck = $row['nm_checker'];
        $pintu   = $row['pintu'];

        // Override jika MANAGERCK kirim data baru
        if ($this->isMCK()) {
            if ($this->input->post('nik_checker')) $nik_ck  = $this->input->post('nik_checker', true);
            if ($this->input->post('nm_checker'))  $nama_ck = $this->input->post('nm_checker',  true);
            if ($this->input->post('pintu'))       $pintu   = (int)$this->input->post('pintu');
        }

        $ok = $this->M_Checker->start_lk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK dimulai' : 'Gagal']);
    }

    public function update_progres_lk()
    {
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_ADMLOG])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id    = (int)$this->input->post('id');
        $pintu = (int)$this->input->post('pintu') ?: null;
        $row   = $this->M_Checker->get_kk_by_id($id);
        if (!$row || $row['status'] !== 'DO_SELESAI') {
            echo json_encode(['status' => false, 'msg' => 'Status harus DO SELESAI sebelum bisa siapkan barang']); return;
        }
        if ($this->isMCK()) {
            $nik_ck  = $this->input->post('nik_checker', true) ?: $this->session->userdata('nik');
            $nama_ck = $this->input->post('nm_checker',  true) ?: $this->nama();
        } else {
            $nik_ck = $this->session->userdata('nik');
            if ($this->M_Checker->get_active_id_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya bongkaran aktif']); return;
            }
            if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading/penyiapan aktif']); return;
            }
            $nama_ck = $this->nama();
        }
        $ok = $this->M_Checker->start_siapkan_kk($id, $nik_ck, $nama_ck, $pintu);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Penyiapan barang KK dimulai' : 'Gagal']);
    }

    public function update_progres_siapkan_kk()
    {
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
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
        if ($this->isMCK()) {
            $nik_ck  = $this->input->post('nik_checker', true) ?: $this->session->userdata('nik');
            $nama_ck = $this->input->post('nm_checker',  true) ?: $this->nama();
        } else {
            $nik_ck = $this->session->userdata('nik');
            if ($this->M_Checker->get_active_id_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya bongkaran aktif']); return;
            }
            if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading/penyiapan aktif']); return;
            }
            $nama_ck = $this->nama();
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
    // GANTI CHECKER — LOADING KK
    // ================================================================
    public function ganti_checker_kk()
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
        $row = $this->M_Checker->get_kk_by_id($id);
        if (!$row || !in_array($row['status'], ['PENYIAPAN_BARANG', 'SIAP_LOADING', 'PROSES_LOADING'])) {
            echo json_encode(['status' => false, 'msg' => 'Status tidak memungkinkan untuk ganti checker']); return;
        }
        $ok = $this->M_Checker->ganti_checker_kk($id, $nik_ck, $nama_ck);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Checker KK berhasil diganti' : 'Gagal mengganti checker']);
    }

    // ================================================================
    // GANTI CHECKER — LOADING LK
    // ================================================================
    public function ganti_checker_lk()
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
        $row = $this->M_Checker->get_lk_by_id($id);
        if (!$row || !in_array($row['status'], ['PENYIAPAN_BARANG', 'SIAP_LOADING', 'PROSES_LOADING'])) {
            echo json_encode(['status' => false, 'msg' => 'Status tidak memungkinkan untuk ganti checker']); return;
        }
        $ok = $this->M_Checker->ganti_checker_lk($id, $nik_ck, $nama_ck);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Checker LK berhasil diganti' : 'Gagal mengganti checker']);
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
        // 1. SO belum masuk DO (tidak ada di tb_detail_do via faktur)
        // 2. Masih ada item yang belum diverifikasi checker (checker_loaded = 0/NULL/2)
        // 3. Status SO siap_faktur, partial, ATAU completed
        //    - completed bisa terjadi saat Admin SC sudah memfakturkan seluruh item
        //      tetapi checker belum melakukan verifikasi loading (muat/tidak muat)
        // Rute baru hilang dari halaman ini setelah DO dibuat.
        $routes = $this->db->query("
            SELECT
                COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') AS kd_rute,
                COALESCE(r.keterangan, NULLIF(so.kd_rute, ''), NULLIF(c.kd_rute, ''), 'Tanpa Rute') AS nama_rute,
                COUNT(DISTINCT so.id_so) AS total_so
            FROM tbso_sales_order so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_rutecs r ON r.kd_rute = COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute)
            WHERE so.status IN ('siap_faktur', 'partial', 'completed')
            AND NOT EXISTS (
                SELECT 1
                FROM tbso_faktur_penjualan fp
                JOIN tb_detail_do dd ON dd.kd_faktur = fp.no_faktur
                WHERE fp.id_so = so.id_so
            )
            AND EXISTS (
                SELECT 1
                FROM tbso_sales_order_detail sod
                WHERE sod.id_so = so.id_so
                    AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
                    AND (sod.checker_loaded IS NULL OR sod.checker_loaded = 0 OR sod.checker_loaded = 2)
            )
            GROUP BY COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE')
        ")->result_array();

        $data['page_title'] = 'Checker Loading SO - Pilih Rute';
        $data['routes'] = $routes;
        $data['role'] = $this->role();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/so_loading.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function so_loading_detail($kd_rute)
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $this->load->model('M_Logistik');
        $kd_rute = rawurldecode($kd_rute);

        // Tampilkan item yang belum masuk DO dan masih perlu diverifikasi checker
        // Status SO: siap_faktur, partial, ATAU completed
        // checker_loaded: NULL/0 = belum dipilih, 1 = dimuat, 2 = tidak dimuat
        // Item dengan checker_loaded = 1 tidak ditampilkan karena sudah siap untuk DO
        $items = $this->db->query("
            SELECT 
                sod.*, 
                so.no_so, 
                so.customer_name, 
                c.nama_kios,
                b.nama_barang, b.satuan, (b.p * b.l * b.t) AS isi_per_box
            FROM tbso_sales_order_detail sod
            JOIN tbso_sales_order so ON so.id_so = sod.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            LEFT JOIN tb_master_barang_all b ON b.kd_barang = sod.kd_barang
            WHERE so.status IN ('siap_faktur', 'partial', 'completed')
              AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
              AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
              AND (sod.checker_loaded IS NULL OR sod.checker_loaded = 0 OR sod.checker_loaded = 2)
              AND NOT EXISTS (
                  SELECT 1 FROM tb_detail_do dd
                  JOIN tbso_faktur_penjualan fp ON fp.no_faktur = dd.kd_faktur
                  WHERE fp.id_so = so.id_so
              )
            ORDER BY so.no_so ASC, sod.id ASC
        ", [$kd_rute])->result_array();

        $data['page_title'] = 'Checker Loading SO - Detail Rute ' . $kd_rute;
        $data['kd_rute'] = $kd_rute;
        $data['items'] = $items;
        $data['role'] = $this->role();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/so_loading_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function toggle_so_item_loaded()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Method tidak valid']);
            exit;
        }

        $id_detail = (int)$this->input->post('id_detail');
        $loaded = (int)$this->input->post('loaded');
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

        $this->db->where('id', $id_detail);
        $this->db->update('tbso_sales_order_detail', ['checker_loaded' => $loaded]);

        $so = $this->db->get_where('tbso_sales_order', ['id_so' => $detail['id_so']])->row_array();
        $c = $this->db->get_where('tb_customer', ['kd_customer' => $so['kd_customer']])->row_array();
        $kd_rute = trim((string)(($so['kd_rute'] ?? '') ?: ($c['kd_rute'] ?? '')));

        $this->load->model('M_Logistik');
        $username = $this->session->userdata('username') ?? $this->session->userdata('nama') ?? 'system';
        $created_do = $this->M_Logistik->check_and_auto_create_do($kd_rute, $username);

        echo json_encode([
            'status' => true,
            'message' => 'Status muat berhasil diperbarui',
            'created_do' => $created_do ? $created_do['kd_do'] : null
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

        // Cek item belum dipilih sama sekali (0/null)
        $belum = $this->db->query("
            SELECT COUNT(*) AS total
            FROM tbso_sales_order_detail sod
            JOIN tbso_sales_order so ON so.id_so = sod.id_so
            LEFT JOIN tb_customer c ON c.kd_customer = so.kd_customer
            WHERE so.status IN ('siap_faktur', 'partial')
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
            AND NOT EXISTS (
                SELECT 1 FROM tbso_faktur_penjualan fp
                JOIN tb_detail_do dd ON dd.kd_faktur = fp.no_faktur
                WHERE fp.id_so = so.id_so
            )
            AND (sod.checker_loaded IS NULL OR sod.checker_loaded = 0)
        ", [$kd_rute])->row_array();

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
            WHERE so.status IN ('siap_faktur', 'partial')
            AND COALESCE(NULLIF(so.kd_rute, ''), c.kd_rute, 'TANPA_RUTE') = ?
            AND COALESCE(sod.qty_siap_faktur, sod.qty) > 0
            AND NOT EXISTS (
                SELECT 1 FROM tbso_faktur_penjualan fp
                JOIN tb_detail_do dd ON dd.kd_faktur = fp.no_faktur
                WHERE fp.id_so = so.id_so
            )
            AND sod.checker_loaded = 2
        ", [$kd_rute])->row_array();

        $this->load->model('M_Logistik');
        $username = $this->session->userdata('username') ?? $this->session->userdata('nama') ?? 'system';

        if ((int)$ada_ditolak['total'] > 0) {
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
        $created_do = $this->M_Logistik->check_and_auto_create_do($kd_rute, $username);

        echo json_encode([
            'status'      => true,
            'created_do'  => $created_do ? $created_do['kd_do'] : null,
            'ada_ditolak' => false,
            'message'     => $created_do ? 'DO berhasil dibuat.' : 'Loading selesai. Menunggu faktur Admin SC sebelum DO dapat dibuat.'
        ]);
        exit;
    }
}
