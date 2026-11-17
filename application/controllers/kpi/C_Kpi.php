<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Kpi extends CI_Controller
{


    function __construct()
    {
        parent::__construct();
        $this->require_authenticated_session();
        $this->load->model('M_Kpi');
    }

    /**
     * Memastikan setiap route modul KPI hanya diakses oleh session lokal yang terotorisasi.
     */
    private function require_authenticated_session()
    {
        $isLoggedIn = $this->session->userdata('status') === 'is_login'
            && (int) $this->session->userdata('id') > 0
            && trim((string) $this->session->userdata('username')) !== ''
            && in_array((int) $this->session->userdata('akses_lv'), [1, 2, 3, 4, 5], true);

        if ($isLoggedIn) {
            return;
        }

        $this->session->set_flashdata('gagal', 'Silakan login terlebih dahulu.');
        redirect('Auth');
        exit;
    }

    public function index()
    {
        $data['page_title'] = 'KPI DIGITAL';
        $kd_user = $this->session->userdata('kduser');
        $data['allkpi'] = $this->M_Kpi->getkpi($kd_user)->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kpi/dashboard_kpi.php', $data);
        $this->load->view('partial/main/footer.php');
    }
    public function what()
    {
        $data['page_title'] = 'KPI DIGITAL';
        $kd_user = $this->session->userdata('kduser');
        $data['allkpi'] = $this->M_Kpi->getkpi($kd_user)->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kpi/dashboard_kpiwhat.php', $data);
        $this->load->view('partial/main/footer.php');
    }
    public function addkpi()
    {
        $kduser = $this->input->post('kd_isi');
        $subdep = $this->input->post('sub_isi');
        $dep    = $this->input->post('dep_isi');
        $tgl    = $this->input->post('date_isi');
        $bln    = $this->input->post('date_isi1');

        $datakpi = array(
            'kd_user'           => $kduser,
            'sub_departemen'    => $subdep,
            'departmen'        => $dep,
            'tgl_pembuatan'     => $tgl,
            'bln_pembuatan'     => $bln,
        );
        $this->M_Kpi->addkpi($datakpi);

        redirect('dashboardkpi');
    }
    public function detail_kpi($kduser, $bln)
    {
        $data['page_title'] = 'KPI DIGITAL';
        $data['tujuan'] = $this->M_Kpi->get_tujuan_kpi($kduser, $bln);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kpi/detail_kpi.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
