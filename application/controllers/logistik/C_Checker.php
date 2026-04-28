<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Checker extends CI_Controller
{
    const ROLE_CHECKER    = 'CHECKER';
    const ROLE_MANAGERCK  = 'MANAGERCK';
    const ROLE_ADMLOG     = 'ADMLOG';
    const ROLE_MANAGER_WH = 'MANAGERWH';
    const ROLE_SALES      = 'SALES';
    const ROLE_DIREKTUR   = 'DIREKTURCK';

    // Apakah role ini termasuk "doer" (bisa start/jalankan bongkaran & loading)
    private function isDoer()
    {
        return in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK]);
    }

    // Apakah role ini Manager Checker (bisa start banyak + input nama checker)
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
        date_default_timezone_set('Asia/Jakarta'); // WIB UTC+7
        if (!$this->session->userdata('nik')) redirect('Auth');
    }

    private function role()
    {
        return strtoupper($this->session->userdata('jobdesk'));
    }

    private function nama()
    {
        // Auth.php menyimpan dengan key 'nama' bukan 'nm_karyawan'
        return $this->session->userdata('nama');
    }

    // ----------------------------------------------------------------
    // HALAMAN UTAMA
    // ----------------------------------------------------------------
    public function index()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }
        $nik = $this->session->userdata('nik');
        $data['page_title']    = 'KARISMA - Aktivitas Warehouse';
        $data['bongkaran']     = $this->M_Checker->get_list();
        $data['list_kk']       = $this->M_Checker->get_list_kk();
        $data['list_lk']       = $this->M_Checker->get_list_lk();
        $data['role']          = $this->role();
        $data['nik']           = $nik;
        $data['kode_baru']     = $this->M_Checker->generate_kode();
        // CHECKER biasa: cek job aktif (hanya boleh 1)
        // MANAGERCK: bebas start banyak → null (tidak dibatasi)
        $data['my_active_id']  = ($this->role() === self::ROLE_CHECKER)
                               ? $this->M_Checker->get_active_id_by_checker($nik)
                               : null;
        // Daftar checker untuk dropdown input nama (dipakai MANAGERCK)
        $data['list_checker']  = $this->isMCK()
                               ? $this->M_Checker->get_list_checker()
                               : [];
        $data['kode_baru_kk']  = $this->M_Checker->generate_kode_kk();
        $data['kode_baru_lk']  = $this->M_Checker->generate_kode_lk();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // HALAMAN ARSIP (Manager WH only)
    // ----------------------------------------------------------------
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

    // ----------------------------------------------------------------
    // HALAMAN DASHBOARD
    // ----------------------------------------------------------------
    public function dashboard()
    {
        if (!$this->canView()) { show_error('Akses ditolak', 403); }

        $data['page_title'] = 'KARISMA - Dashboard Warehouse';
        $data['role']       = $this->role();

        // Ambil semua data aktif (belum diarsip)
        $data['bongkaran'] = $this->M_Checker->get_list();
        $data['list_lk']   = $this->M_Checker->get_list_lk();
        $data['list_kk']   = $this->M_Checker->get_list_kk();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/checker/dashboard.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // AJAX: BONGKARAN
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
        $pintu = (int)$this->input->post('pintu') ?: null;   // ← TAMBAH

        if ($this->M_Checker->is_taken($id)) {
            echo json_encode(['status' => false, 'msg' => 'Bongkaran sudah diambil checker lain']); return;
        }

        if ($this->isMCK()) {
            $nik_ck  = $this->input->post('nik_checker', true) ?: $this->session->userdata('nik');
            $nama_ck = $this->input->post('nm_checker',  true) ?: $this->nama();
        } else {
            $nik_ck = $this->session->userdata('nik');
            $active = $this->M_Checker->get_active_id_by_checker($nik_ck);
            if ($active !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih memiliki bongkaran aktif']); return;
            }
            if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading aktif yang belum selesai']); return;
            }
            $nama_ck = $this->nama();
        }

        $ok = $this->M_Checker->start($id, $nik_ck, $nama_ck, $pintu);  // ← $pintu
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Start berhasil' : 'Gagal start']);
    }

    public function update_progres()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id      = (int)$this->input->post('id');
        $progres = (int)$this->input->post('progres');
        // CHECKER biasa hanya bisa update job miliknya sendiri
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
            echo json_encode(['status' => false, 'msg' => 'Tidak ada data DONE yang belum diarsipkan']);
            return;
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
        // Pastikan masih status MENUNGGU
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
    // AJAX: LOADING KK  (ADMLOG yang input & update)
    // ================================================================

    public function store_kk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->create_kk([
            'kode'        => $this->M_Checker->generate_kode_kk(),
            'tgl'         => date('Y-m-d'),
            'keterangan'  => $this->input->post('keterangan', true),
            'status'      => 'CETAK_DO',
            'created_by'  => $this->nama(),
        ]);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Data KK ditambahkan' : 'Gagal']);
    }

    public function update_kk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id   = (int)$this->input->post('id');
        $data = [];
        if ($this->input->post('keterangan') !== null) $data['keterangan'] = $this->input->post('keterangan', true);
        if ($this->input->post('status')     !== null) $data['status']     = $this->input->post('status',     true);
        $ok = $this->M_Checker->update_kk($id, $data);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'KK diperbarui' : 'Gagal']);
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
    // AJAX: LOADING LK  (ADMLOG yang input & update)
    // ================================================================

    public function store_lk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->create_lk([
            'kode'       => $this->M_Checker->generate_kode_lk(),
            'tgl'        => date('Y-m-d'),
            'keterangan' => $this->input->post('keterangan', true),
            'status'     => 'CETAK_DO',
            'created_by' => $this->nama(),
        ]);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Data LK ditambahkan' : 'Gagal']);
    }

    public function update_lk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id   = (int)$this->input->post('id');
        $data = [];
        if ($this->input->post('keterangan') !== null) $data['keterangan'] = $this->input->post('keterangan', true);
        if ($this->input->post('status')     !== null) $data['status']     = $this->input->post('status',     true);
        $ok = $this->M_Checker->update_lk($id, $data);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'LK diperbarui' : 'Gagal']);
    }

    public function archive_lk()
    {
        if (!in_array($this->role(), [self::ROLE_MANAGER_WH, self::ROLE_ADMLOG])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->archive_lk((int)$this->input->post('id'), $this->nama());
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'LK diarsipkan' : 'Gagal']);
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
        if (!$row || $row['status'] !== 'MENUNGGU') {
            echo json_encode(['status' => false, 'msg' => 'Hanya LK berstatus MENUNGGU yang bisa diedit']); return;
        }
        $ok = $this->M_Checker->edit_lk($id, $ket);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK berhasil diupdate' : 'Gagal update']);
    }

    public function hapus_lk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_lk_by_id($id);
        if (!$row || $row['status'] !== 'MENUNGGU') {
            echo json_encode(['status' => false, 'msg' => 'Hanya LK berstatus MENUNGGU yang bisa dihapus']); return;
        }
        $ok = $this->M_Checker->hapus_lk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading LK berhasil dihapus' : 'Gagal hapus']);
    }
    // ================================================================
    // AJAX: LOADING KK — Start / Progres / Done (ADMLOG yang jalankan)
    // ================================================================

    public function start_kk()
    {
        if (!in_array($this->role(), [self::ROLE_CHECKER, self::ROLE_MANAGERCK])) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $pintu = (int)$this->input->post('pintu') ?: null;

        $row = $this->M_Checker->get_kk_by_id($id);
        if (!$row || $row['status'] !== 'DO_SELESAI') {
            echo json_encode(['status' => false, 'msg' => 'Status harus DO SELESAI sebelum bisa start loading']); return;
        }
        if ($this->isMCK()) {
            $nik_ck  = $this->input->post('nik_checker', true) ?: $this->session->userdata('nik');
            $nama_ck = $this->input->post('nm_checker',  true) ?: $this->nama();
        } else {
            $nik_ck = $this->session->userdata('nik');
            // Cek job aktif di bongkaran, KK, maupun LK
            if ($this->M_Checker->get_active_id_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya bongkaran aktif']); return;
            }
            if ($this->M_Checker->get_active_loading_by_checker($nik_ck) !== null) {
                echo json_encode(['status' => false, 'msg' => 'Anda masih punya loading aktif yang belum selesai']); return;
            }
            $nama_ck = $this->nama();
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
        if (!$row || $row['status'] !== 'MENUNGGU') {
            echo json_encode(['status' => false, 'msg' => 'Hanya KK berstatus MENUNGGU yang bisa diedit']); return;
        }
        $ok = $this->M_Checker->edit_kk($id, $ket);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK berhasil diupdate' : 'Gagal update']);
    }

    public function hapus_kk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $row = $this->M_Checker->get_kk_by_id($id);
        if (!$row || $row['status'] !== 'MENUNGGU') {
            echo json_encode(['status' => false, 'msg' => 'Hanya KK berstatus MENUNGGU yang bisa dihapus']); return;
        }
        $ok = $this->M_Checker->hapus_kk($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Loading KK berhasil dihapus' : 'Gagal hapus']);
    }
    // ================================================================
    // AJAX: LOADING LK — Start / Progres / Done (ADMLOG yang jalankan)
    // ================================================================

    public function start_lk()
    {
        if (!$this->isDoer()) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id  = (int)$this->input->post('id');
        $pintu = (int)$this->input->post('pintu') ?: null;

        $row = $this->M_Checker->get_lk_by_id($id);
        if (!$row || $row['status'] !== 'DO_SELESAI') {
            echo json_encode(['status' => false, 'msg' => 'Status harus DO SELESAI sebelum bisa start loading']); return;
        }
        if ($this->isMCK()) {
            $nik_ck  = $this->input->post('nik_checker', true) ?: $this->session->userdata('nik');
            $nama_ck = $this->input->post('nm_checker',  true) ?: $this->nama();
        } else {
            $nik_ck  = $this->session->userdata('nik');
            $nama_ck = $this->nama();
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
}