<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Cek login
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('M_Kmt');
    }

    public function index() {
        // Ambil filter dari GET, default tahun sekarang
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $id_wilayah = $this->input->get('id_wilayah') ?? null;

        // ABM hanya bisa lihat wilayah sendiri
        if ((int)$this->session->userdata('akses_lv') === 3) {
            $id_wilayah = $this->session->userdata('id_wilayah');
        }

        // Pastikan integer atau null
        $id_wilayah = $id_wilayah ? (int)$id_wilayah : null;

        $data['title']             = 'Dashboard KMT CORN';
        $data['tahun']             = $tahun;
        $data['id_wilayah']        = $id_wilayah;
        $data['wilayah_list']      = $this->M_Kmt->get_wilayah();
        $data['ytd']               = $this->M_Kmt->get_ytd($tahun, $id_wilayah);
        $data['summary']           = $this->M_Kmt->get_summary_cards($tahun, $id_wilayah);
        $data['cost_per_wilayah']  = $this->M_Kmt->get_cost_per_hasil_wilayah($tahun);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/index', $data);
        $this->load->view('partial/main/footer.php');
    }
}