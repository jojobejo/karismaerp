<?php
// ================================================================
// controllers/content/kmt/Dca.php
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Dca extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('akses_lv') === 3)
            ? (int)$this->session->userdata('id_wilayah') : null;
    }

    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list        = $this->M_Kmt->get_dca_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));

        $data = [
            'title'        => 'Data DCA KMT CORN',
            'list'         => $list,
            'total_biaya'  => $total_biaya,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'akses_lv'     => (int)$this->session->userdata('akses_lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'title'        => 'Tambah Data DCA',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'akses_lv'     => (int)$this->session->userdata('akses_lv'),
            'id_wilayah_user' => $this->session->userdata('id_wilayah'),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('tanggal_dca','Tanggal', 'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah', 'required|integer');
        $this->form_validation->set_rules('uraian',     'Uraian',  'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl     = $this->input->post('tanggal_dca');
        $real    = (float)str_replace('.','', $this->input->post('real_biaya') ?? 0);
        $um      = (float)str_replace('.','', $this->input->post('um') ?? 0);
        $refund  = (float)str_replace('.','', $this->input->post('refund') ?? 0);

        $insert = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'abm'         => $this->input->post('abm'),
            'uraian'      => $this->input->post('uraian'),
            'um'          => $um,
            'refund'      => $refund,
            'real_biaya'  => $real,
            'total_biaya' => $real - $refund,
            'created_by'  => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_dca($insert)) {
            $this->session->set_flashdata('success', 'Data DCA berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('content/kmt/dca');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'title'           => 'Edit Data DCA',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'akses_lv'        => (int)$this->session->userdata('akses_lv'),
            'id_wilayah_user' => $this->session->userdata('id_wilayah'),
        ];
        $this->load->view('content/kmt/dca/form', $data);
    }

    public function update($id) {
        $tgl    = $this->input->post('tanggal_dca');
        $real   = (float)str_replace('.','', $this->input->post('real_biaya') ?? 0);
        $refund = (float)str_replace('.','', $this->input->post('refund') ?? 0);

        $update = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'abm'         => $this->input->post('abm'),
            'uraian'      => $this->input->post('uraian'),
            'um'          => (float)str_replace('.','', $this->input->post('um') ?? 0),
            'refund'      => $refund,
            'real_biaya'  => $real,
            'total_biaya' => $real - $refund,
        ];

        if ($this->M_Kmt->update_dca($id, $update)) {
            $this->session->set_flashdata('success', 'Data DCA berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('content/kmt/dca');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_dca($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('content/kmt/dca');
    }
}
