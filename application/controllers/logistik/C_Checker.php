<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Bongkaran extends CI_Controller
{
    // Mapping jobdesk -> role
    const ROLE_CHECKER    = 'CHECKER';
    const ROLE_ADMLOG     = 'LOGISTIK';
    const ROLE_MANAGER_WH = 'MANAGERWH';
    const ROLE_VIEW_ONLY  = ['DIREKTUR', 'SALES'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Checker');
        $this->load->library('session');
        $this->load->helper('url');

        // Pastikan user sudah login
        if (!$this->session->userdata('nik')) {
            redirect('auth/login');
        }
    }

    private function jobdesk()
    {
        return strtoupper($this->session->userdata('jobdesk'));
    }

    // ----------------------------------------------------------------
    // HALAMAN UTAMA
    // ----------------------------------------------------------------
    public function index()
    {
        $data['page_title'] = 'KARISMA - Bongkaran';
        $data['list']       = $this->M_Checker->get_list();
        $data['jobdesk']    = $this->jobdesk();
        $data['nik']        = $this->session->userdata('nik');
        $data['kode_baru']  = $this->M_Checker->generate_kode();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bongkaran/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // HALAMAN ARSIP (Manager WH only)
    // ----------------------------------------------------------------
    public function arsip()
    {
        if ($this->jobdesk() !== self::ROLE_MANAGER_WH) {
            show_error('Akses ditolak', 403);
        }

        $data['page_title'] = 'KARISMA - Arsip Bongkaran';
        $data['list']       = $this->M_Checker->get_arsip();
        $data['jobdesk']    = $this->jobdesk();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/bongkaran/arsip.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // MANAGER WH: buat bongkaran baru
    // ----------------------------------------------------------------
    public function store()
    {
        if ($this->jobdesk() !== self::ROLE_MANAGER_WH) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }

        $data = [
            'kode_bongkar' => $this->M_Checker->generate_kode(),
            'keterangan'   => $this->input->post('keterangan', true),
            'status'       => 'MENUNGGU',
            'created_by'   => $this->session->userdata('nm_karyawan'),
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        $ok = $this->M_Checker->create($data);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran berhasil dibuat' : 'Gagal membuat bongkaran']);
    }

    // ----------------------------------------------------------------
    // CHECKER: start bongkaran
    // ----------------------------------------------------------------
    public function start()
    {
        if ($this->jobdesk() !== self::ROLE_CHECKER) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }

        $id = (int) $this->input->post('id');

        if ($this->M_Checker->is_taken($id)) {
            echo json_encode(['status' => false, 'msg' => 'Bongkaran ini sudah diambil checker lain']);
            return;
        }

        $ok = $this->M_Checker->start(
            $id,
            $this->session->userdata('nik'),
            $this->session->userdata('nm_karyawan')
        );
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Berhasil start' : 'Gagal start']);
    }

    // ----------------------------------------------------------------
    // CHECKER: update progres
    // ----------------------------------------------------------------
    public function update_progres()
    {
        if ($this->jobdesk() !== self::ROLE_CHECKER) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }

        $id      = (int) $this->input->post('id');
        $progres = (int) $this->input->post('progres');

        // Pastikan hanya checker yang mengambil job ini yang bisa update
        $checker = $this->M_Checker->get_checker($id);
        if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
            echo json_encode(['status' => false, 'msg' => 'Anda bukan checker untuk bongkaran ini']);
            return;
        }

        $ok = $this->M_Checker->update_progres($id, $progres);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Progres diperbarui' : 'Gagal update progres']);
    }

    // ----------------------------------------------------------------
    // CHECKER: done
    // ----------------------------------------------------------------
    public function done()
    {
        if ($this->jobdesk() !== self::ROLE_CHECKER) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }

        $id      = (int) $this->input->post('id');
        $checker = $this->M_Checker->get_checker($id);

        if (!$checker || $checker['nik_checker'] !== $this->session->userdata('nik')) {
            echo json_encode(['status' => false, 'msg' => 'Anda bukan checker untuk bongkaran ini']);
            return;
        }

        $ok = $this->M_Checker->checker_done($id);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Bongkaran selesai' : 'Gagal']);
    }

    // ----------------------------------------------------------------
    // ADMLOG: update jalur & status
    // ----------------------------------------------------------------
    public function update_admlog()
    {
        if ($this->jobdesk() !== self::ROLE_ADMLOG) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }

        $id   = (int) $this->input->post('id');
        $data = [];

        if ($this->input->post('jalur_kk') !== null) $data['jalur_kk'] = $this->input->post('jalur_kk', true);
        if ($this->input->post('jalur_lk') !== null) $data['jalur_lk'] = $this->input->post('jalur_lk', true);
        if ($this->input->post('status')   !== null) $data['status']   = $this->input->post('status',   true);

        $ok = $this->M_Checker->update_admlog($id, $data);
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Data diperbarui' : 'Gagal update']);
    }

    // ----------------------------------------------------------------
    // MANAGER WH: archive
    // ----------------------------------------------------------------
    public function archive()
    {
        if ($this->jobdesk() !== self::ROLE_MANAGER_WH) {
            echo json_encode(['status' => false, 'msg' => 'Akses ditolak']); return;
        }

        $id = (int) $this->input->post('id');
        $bongkaran = $this->M_Checker->get_by_id($id);

        if (!$bongkaran || $bongkaran['status'] !== 'DONE') {
            echo json_encode(['status' => false, 'msg' => 'Hanya bongkaran berstatus DONE yang bisa diarsipkan']);
            return;
        }

        $ok = $this->M_Checker->archive($id, $this->session->userdata('nm_karyawan'));
        echo json_encode(['status' => (bool)$ok, 'msg' => $ok ? 'Data berhasil diarsipkan' : 'Gagal arsip']);
    }
}