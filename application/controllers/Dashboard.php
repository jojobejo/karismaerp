<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{


    function __construct()
    {
        parent::__construct();
        $this->require_authenticated_session();
        $this->load->model('M_Logistik');
        $this->load->model('M_Hrd');
    }

    /**
     * Memastikan pengguna memiliki session lokal aktif dengan level akses yang sah.
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

        $acceptHeader = (string) $this->input->server('HTTP_ACCEPT');
        if ($this->input->is_ajax_request() || stripos($acceptHeader, 'application/json') !== false) {
            $this->output
                ->set_header('Cache-Control: no-store')
                ->set_status_header(401)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'unauthenticated',
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ]))
                ->_display();
            exit;
        }

        $this->session->set_flashdata('gagal', 'Silakan login terlebih dahulu.');
        redirect('Auth');
        exit;
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA';
        $data['logistik'] = $this->M_Logistik->get_all_do()->result();
        $data['tamu'] = $this->M_Hrd->getalltamulb()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/body.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function konfirm_tamu()
    {

        $id = $this->input->post('id');
        $tanggal = $this->input->post('tanggal');
        $nama = $this->input->post('nama');
        $perusahaan = $this->input->post('perusahaan');
        $alamat = $this->input->post('alamat');
        $jumlahpersonil = $this->input->post('jumlahpersonil');
        $tujuan = $this->input->post('tujuan');
        $jammasuk = $this->input->post('jammasuk');
        $jamkeluar = $this->input->post('jamkeluar');
        $keterangan = $this->input->post('keterangan');

        $data = array(
            'tanggal' => $tanggal,
            'nama' => $nama,
            'perusahaan' => $perusahaan,
            'alamat' => $alamat,
            'jumlahpersonil' => $jumlahpersonil,
            'tujuan' => $tujuan,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jamkeluar,
            'keterangan' => $keterangan,

        );
        $this->M_Hrd->konfirmtamulb($data);
        $this->M_Hrd->hapus_lap_tamu_lby($id);
        redirect('dashboard');
    }
}
