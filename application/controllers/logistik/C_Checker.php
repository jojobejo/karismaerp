<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Checker extends CI_Controller
{
    const ROLE_CHECKER    = 'CHECKER';
    const ROLE_ADMLOG     = 'ADMLOG';
    const ROLE_MANAGER_WH = 'MANAGERWH';
    const ROLE_SALES      = 'SALESCK';
    const ROLE_DIREKTUR   = 'DIREKTURCK';

    private function canView()
    {
        return in_array($this->role(), [
            self::ROLE_CHECKER, self::ROLE_ADMLOG, self::ROLE_MANAGER_WH,
            self::ROLE_SALES, self::ROLE_DIREKTUR,
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
        $data['my_active_id']  = $this->M_Checker->get_active_id_by_checker($nik);

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
        if ($this->role() !== self::ROLE_CHECKER) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $nik = $this->session->userdata('nik');
        $id  = (int)$this->input->post('id');

        // Blokir jika checker ini masih punya job aktif yang belum done
        $active = $this->M_Checker->get_active_id_by_checker($nik);
        if ($active !== null) {
            echo json_encode(['status' => false, 'msg' => 'Anda masih memiliki bongkaran aktif yang belum selesai']); return;
        }
        // Blokir jika bongkaran ini sudah diambil checker lain
        if ($this->M_Checker->is_taken($id)) {
            echo json_encode(['status' => false, 'msg' => 'Bongkaran sudah diambil checker lain']); return;
        }
        $ok = $this->M_Checker->start($id, $nik, $this->nama());
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Start berhasil' : 'Gagal start']);
    }

    public function update_progres()
    {
        if ($this->role() !== self::ROLE_CHECKER) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id      = (int)$this->input->post('id');
        $progres = (int)$this->input->post('progres');
        $checker = $this->M_Checker->get_checker($id);
        if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
            echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
        }
        $ok = $this->M_Checker->update_progres($id, $progres);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal']);
    }

    public function done()
    {
        if ($this->role() !== self::ROLE_CHECKER) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $id      = (int)$this->input->post('id');
        $checker = $this->M_Checker->get_checker($id);
        if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
            echo json_encode(['status' => false, 'msg' => 'Bukan job Anda']); return;
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
        if ($this->role() !== self::ROLE_MANAGER_WH) {
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

    // ================================================================
    // AJAX: LOADING KK  (ADMLOG yang input & update)
    // ================================================================

    public function store_kk()
    {
        if ($this->role() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->create_kk([
            'tgl'         => date('Y-m-d'),
            'keterangan'  => $this->input->post('keterangan', true),
            'status'      => 'MENUNGGU',
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
        if ($this->role() !== self::ROLE_MANAGER_WH) {
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
            'tgl'        => date('Y-m-d'),
            'keterangan' => $this->input->post('keterangan', true),
            'status'     => 'MENUNGGU',
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
        if ($this->role() !== self::ROLE_MANAGER_WH) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }
        $ok = $this->M_Checker->archive_lk((int)$this->input->post('id'), $this->nama());
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'LK diarsipkan' : 'Gagal']);
    }
}