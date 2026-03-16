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
        return ((int)$this->session->userdata('lv') === 3)
            ? (int)$this->session->userdata('wilayah') : null;
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
            'page_title'   => 'Data DCA KMT CORN',
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
        $this->load->view('content/kmt/dca/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'page_title'        => 'Tambah Data DCA',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'lv'     => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
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
        redirect('kmt/dca');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'page_title'      => 'Edit Data DCA',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
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
        redirect('kmt/dca');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_dca($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/dca');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_dca_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DCA');

        $headers = ['No','Tanggal','Wilayah','ABM','Uraian','UM','Refund','Real Biaya','Total'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $r, date('d/m/Y', strtotime($row['tanggal_dca'])));
            $sheet->setCellValueByColumnAndRow(3, $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $r, $row['abm'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $r, $row['uraian']);
            $sheet->setCellValueByColumnAndRow(6, $r, $row['um']);
            $sheet->setCellValueByColumnAndRow(7, $r, $row['refund']);
            $sheet->setCellValueByColumnAndRow(8, $r, $row['real_biaya']);
            $sheet->setCellValueByColumnAndRow(9, $r, $row['total_biaya']);
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'DCA_KMT_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
