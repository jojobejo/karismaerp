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
        return ((int)$this->session->userdata('lv') === 3)
            ? (int)$this->session->userdata('wilayah')
            : null;
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
            'page_title'        => 'Data Operasional KMT CORN',
            'list'         => $list,
            'total_biaya'  => $total_biaya,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/operasional/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH
    // ----------------------------------------------------------------
    public function tambah() {

        $data = [
            'page_title'        => 'Tambah Biaya Operasional',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'lv'     => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/operasional/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {

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
        redirect('kmt/operasional');
    }

    // ----------------------------------------------------------------
    // EDIT
    // ----------------------------------------------------------------
    public function edit($id) {
        $row = $this->M_Kmt->get_operasional_by_id($id);
        if (!$row) { show_404(); return; }

        // ABM hanya bisa edit data wilayah sendiri
        $lv = (int)$this->session->userdata('lv');
        if ($lv === 3 && $row['id_wilayah'] != $this->session->userdata('wilayah')) {
            $this->session->set_flashdata('error', 'Anda tidak bisa mengedit data wilayah lain.');
            redirect('kmt/operasional');
        }

        $data = [
            'page_title'           => 'Edit Biaya Operasional',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => $lv,
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);;
        $this->load->view('content/kmt/operasional/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id) {

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
        redirect('kmt/operasional');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        if ($this->M_Kmt->delete_operasional($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/operasional');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_operasional_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Operasional');

        $headers = ['No','Tanggal','Wilayah','Nama','Hotel','Per Diem','Entertainment',
                    'Communication','ATK','Gasoline','Sparepart','Toll/Parkir',
                    'Transportasi','Pos/Paket','Tambah Angin','Tambal Ban',
                    'Indekost','Lain-lain','Total'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        $sheet->getStyle('A1:S1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $fields = ['hotel','per_diem','entertainment','communication','atk','gasoline',
                'sparepart_service','retribusi_toll_parkir','transportasi','pos_paket',
                'tambah_angin','tambal_ban','indekost','lain_lain'];

        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1,  $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2,  $r, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValueByColumnAndRow(3,  $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4,  $r, $row['nama']);
            $col = 5;
            foreach ($fields as $f) {
                $sheet->setCellValueByColumnAndRow($col++, $r, $row[$f] ?? 0);
            }
            $sheet->setCellValueByColumnAndRow($col, $r, $row['total_biaya']);
        }

        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Operasional_KMT_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
