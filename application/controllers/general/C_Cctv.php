<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_Cctv extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_General');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
        // Tambahkan cek login sesuai sistem autentikasi Karisma ERP
        // if (!$this->session->userdata('logged_in')) redirect('auth/login');
    }

    // -------------------------------------------------------
    // Halaman utama daftar CCTV
    // -------------------------------------------------------
    public function index() {
        $filter = [
            'tgl_awal'       => $this->input->get('tgl_awal'),
            'tgl_akhir'      => $this->input->get('tgl_akhir'),
            'lokasi'         => $this->input->get('lokasi'),
            'status'         => $this->input->get('status'),
            'status_rekaman' => $this->input->get('status_rekaman'),
        ];

        $data['title']        = 'Tracking CCTV';
        $data['summary']      = $this->M_General->get_summary();
        $data['cctv_list']    = $this->M_General->get_all($filter);
        $data['lokasi_list']  = $this->M_General->get_lokasi_list();
        $data['filter']       = $filter;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/general/cctv/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // -------------------------------------------------------
    // Form tambah kamera
    // -------------------------------------------------------
    public function tambah() {
        $data['title'] = 'Tambah Kamera';
        
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/general/cctv/form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // -------------------------------------------------------
    // Simpan data baru
    // -------------------------------------------------------
    public function simpan() {
        $this->form_validation->set_rules('tgl',            'Tanggal',      'required');
        $this->form_validation->set_rules('lokasi',         'Lokasi',       'required|max_length[100]');
        $this->form_validation->set_rules('nama_kamera',    'Nama Kamera',  'required|max_length[100]');
        $this->form_validation->set_rules('ip_kamera',      'IP Kamera',    'required|max_length[45]');
        $this->form_validation->set_rules('status',         'Status',       'required|in_list[Online,Offline]');
        $this->form_validation->set_rules('status_rekaman', 'Rek.',         'required|in_list[Terekam,Tidak]');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah();
            return;
        }

        $insert = [
            'tgl'            => $this->input->post('tgl'),
            'lokasi'         => $this->input->post('lokasi'),
            'nama_kamera'    => $this->input->post('nama_kamera'),
            'ip_kamera'      => $this->input->post('ip_kamera'),
            'status'         => $this->input->post('status'),
            'status_rekaman' => $this->input->post('status_rekaman'),
            'keterangan'     => $this->input->post('keterangan'),
        ];

        if ($this->M_General->insert($insert)) {
            $this->session->set_flashdata('success', 'Data kamera berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('cctv');
    }

    // -------------------------------------------------------
    // Form edit
    // -------------------------------------------------------
    public function edit($id) {
        $data['title']  = 'Edit Kamera';
        $data['kamera'] = $this->M_General->get_by_id($id);

        if (!$data['kamera']) {
            show_404();
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/general/cctv/form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // -------------------------------------------------------
    // Update data
    // -------------------------------------------------------
    public function update($id) {
        $this->form_validation->set_rules('tgl',            'Tanggal',      'required');
        $this->form_validation->set_rules('lokasi',         'Lokasi',       'required|max_length[100]');
        $this->form_validation->set_rules('nama_kamera',    'Nama Kamera',  'required|max_length[100]');
        $this->form_validation->set_rules('ip_kamera',      'IP Kamera',    'required|max_length[45]');
        $this->form_validation->set_rules('status',         'Status',       'required|in_list[Online,Offline]');
        $this->form_validation->set_rules('status_rekaman', 'Rek.',         'required|in_list[Terekam,Tidak]');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $update = [
            'tgl'            => $this->input->post('tgl'),
            'lokasi'         => $this->input->post('lokasi'),
            'nama_kamera'    => $this->input->post('nama_kamera'),
            'ip_kamera'      => $this->input->post('ip_kamera'),
            'status'         => $this->input->post('status'),
            'status_rekaman' => $this->input->post('status_rekaman'),
            'keterangan'     => $this->input->post('keterangan'),
        ];

        if ($this->M_General->update($id, $update)) {
            $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('cctv');
    }

    // -------------------------------------------------------
    // Hapus data
    // -------------------------------------------------------
    public function hapus($id) {
        if ($this->M_General->delete($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('cctv');
    }

    // -------------------------------------------------------
    // AJAX: Refresh status kamera (periodik / real-time)
    // -------------------------------------------------------
    public function refresh_status() {
        // Endpoint ini dipanggil secara periodik dari JS (setInterval)
        // Di sini bisa diintegrasikan dengan API ping kamera IP
        // Contoh sederhana: kembalikan semua status terkini sebagai JSON
        $result = $this->M_General->get_all();
        $out    = [];
        foreach ($result as $r) {
            $out[] = [
                'id'             => $r->id,
                'status'         => $r->status,
                'status_rekaman' => $r->status_rekaman,
            ];
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($out));
    }
}