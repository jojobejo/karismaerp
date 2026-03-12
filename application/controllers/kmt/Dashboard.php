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
        $lv    = (int)$this->session->userdata('lv');
        $tahun = $this->input->get('tahun') ?? date('Y');

        // ABM (lv 3) paksa wilayah dari session, tidak bisa diubah via GET
        if ($lv === 3) {
            $id_wilayah = (int)$this->session->userdata('wilayah');
        } else {
            $id_wilayah = $this->input->get('id_wilayah');
            $id_wilayah = $id_wilayah ? (int)$id_wilayah : null;
        }

        $data['title']            = 'Dashboard KMT CORN';
        $data['tahun']            = $tahun;
        $data['id_wilayah']       = $id_wilayah;
        $data['lv']               = $lv;
        $data['wilayah_list']     = $this->M_Kmt->get_wilayah();
        $data['ytd']              = $this->M_Kmt->get_ytd($tahun, $id_wilayah);
        $data['summary']          = $this->M_Kmt->get_summary_cards($tahun, $id_wilayah);

        // ABM hanya lihat wilayahnya, selain ABM lihat semua
        $data['cost_per_wilayah'] = $this->M_Kmt->get_cost_per_hasil_wilayah($tahun, $id_wilayah);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/index', $data);
        $this->load->view('partial/main/footer.php');
    }
}