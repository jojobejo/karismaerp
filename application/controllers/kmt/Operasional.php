<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Operasional extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('akses_lv') === 3)
            ? (int)$this->session->userdata('id_wilayah')
            : null;
    }

    // Cek akses: hanya ABM & KADEP yang bisa input operasional
    // ADMIN tidak bisa input
    private function cek_bisa_input() {
        $lv = (int)$this->session->userdata('akses_lv');
        if ($lv === 2) { // ADMIN
            $this->session->set_flashdata('error', 'Admin tidak dapat menginput data operasional.');
            redirect('content/kmt/operasional');
        }
    }

    // ----------------------------------------------------------------
    // INDEX
    // ----------------------------------------------------------------
    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list        = $this->M_Kmt->get_operasional_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));

        $data = [
            'title'        => 'Data Operasional KMT CORN',
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
        $this->load->view('content/kmt/operasional/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH
    // ----------------------------------------------------------------
    public function tambah() {
        $this->cek_bisa_input();
        $data = [
            'title'        => 'Tambah Biaya Operasional',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'akses_lv'     => (int)$this->session->userdata('akses_lv'),
            'id_wilayah_user' => $this->session->userdata('id_wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/operasional/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->cek_bisa_input();

        $this->form_validation->set_rules('tanggal',   'Tanggal', 'required');
        $this->form_validation->set_rules('nama',      'Nama',    'required');
        $this->form_validation->set_rules('id_wilayah','Wilayah', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah();
            return;
        }

        $fields = ['hotel','per_diem','entertainment','communication','atk','gasoline',
                   'sparepart_service','retribusi_toll_parkir','transportasi','pos_paket',
                   'tambah_angin','tambal_ban','indekost','lain_lain'];

        $tanggal = $this->input->post('tanggal');
        $insert  = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'bulan'      => (int)date('m', strtotime($tanggal)),
            'tahun'      => (int)date('Y', strtotime($tanggal)),
            'tanggal'    => $tanggal,
            'nama'       => $this->input->post('nama'),
            'created_by' => $this->session->userdata('id_user'),
        ];

        foreach ($fields as $f) {
            $val = $this->input->post($f);
            $insert[$f] = $val ? (float)str_replace(['.','Rp ','Rp'],['' ,'',''], $val) : 0;
        }

        if ($this->M_Kmt->insert_operasional($insert)) {
            $this->session->set_flashdata('success', 'Data operasional berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('content/kmt/operasional');
    }

    // ----------------------------------------------------------------
    // EDIT
    // ----------------------------------------------------------------
    public function edit($id) {
        $this->cek_bisa_input();
        $row = $this->M_Kmt->get_operasional_by_id($id);
        if (!$row) { show_404(); return; }

        // ABM hanya bisa edit data wilayah sendiri
        $lv = (int)$this->session->userdata('akses_lv');
        if ($lv === 3 && $row['id_wilayah'] != $this->session->userdata('id_wilayah')) {
            $this->session->set_flashdata('error', 'Anda tidak bisa mengedit data wilayah lain.');
            redirect('content/kmt/operasional');
        }

        $data = [
            'title'           => 'Edit Biaya Operasional',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'akses_lv'        => $lv,
            'id_wilayah_user' => $this->session->userdata('id_wilayah'),
        ];
        $this->load->view('content/kmt/operasional/form', $data);
    }

    public function update($id) {
        $this->cek_bisa_input();

        $fields = ['hotel','per_diem','entertainment','communication','atk','gasoline',
                   'sparepart_service','retribusi_toll_parkir','transportasi','pos_paket',
                   'tambah_angin','tambal_ban','indekost','lain_lain'];

        $tanggal = $this->input->post('tanggal');
        $update  = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'bulan'      => (int)date('m', strtotime($tanggal)),
            'tahun'      => (int)date('Y', strtotime($tanggal)),
            'tanggal'    => $tanggal,
            'nama'       => $this->input->post('nama'),
        ];

        foreach ($fields as $f) {
            $val = $this->input->post($f);
            $update[$f] = $val ? (float)str_replace(['.','Rp ','Rp'],['' ,'',''], $val) : 0;
        }

        if ($this->M_Kmt->update_operasional($id, $update)) {
            $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('content/kmt/operasional');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        $this->cek_bisa_input();
        if ($this->M_Kmt->delete_operasional($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('content/kmt/operasional');
    }
}
