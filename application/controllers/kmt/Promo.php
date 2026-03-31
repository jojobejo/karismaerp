<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promo extends CI_Controller {

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

        $list        = $this->M_Kmt->get_promo_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));

        $data = [
            'page_title'        => 'Promo Material / Peralatan',
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
        $this->load->view('content/kmt/promo/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'page_title'           => 'Tambah Promo Material / Peralatan',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
            'kategori_list'   => ['Banner','Spanduk','Alat Promosi','Peralatan Event','Lainnya'],
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/promo/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('tanggal',    'Tanggal',    'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah',    'required|integer');
        $this->form_validation->set_rules('nama_item',  'Nama Item',  'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl     = $this->input->post('tanggal');
        $qty     = (int)$this->input->post('qty') ?: 1;
        $harga   = (float)str_replace('.','', $this->input->post('harga_satuan') ?? 0);
        $total   = $qty * $harga;

        $insert = [
            'id_wilayah'   => (int)$this->input->post('id_wilayah'),
            'bulan'        => (int)date('m', strtotime($tgl)),
            'tahun'        => (int)date('Y', strtotime($tgl)),
            'tanggal'      => $tgl,
            'nama_item'    => $this->input->post('nama_item'),
            'kategori'     => $this->input->post('kategori'),
            'qty'          => $qty,
            'satuan'       => $this->input->post('satuan'),
            'harga_satuan' => $harga,
            'total_biaya'  => $total,
            'keterangan'   => $this->input->post('keterangan'),
            'created_by'   => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_promo($insert)) {
            $this->session->set_flashdata('success', 'Data promo/peralatan berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/promo');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_promo_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'page_title'           => 'Edit Promo Material / Peralatan',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
            'kategori_list'   => ['Banner','Spanduk','Alat Promosi','Peralatan Event','Lainnya'],
        ];
        $this->load->view('content/kmt/promo/form', $data);
    }

    public function update($id) {
        $tgl   = $this->input->post('tanggal');
        $qty   = (int)$this->input->post('qty') ?: 1;
        $harga = (float)str_replace('.','', $this->input->post('harga_satuan') ?? 0);

        $update = [
            'id_wilayah'   => (int)$this->input->post('id_wilayah'),
            'bulan'        => (int)date('m', strtotime($tgl)),
            'tahun'        => (int)date('Y', strtotime($tgl)),
            'tanggal'      => $tgl,
            'nama_item'    => $this->input->post('nama_item'),
            'kategori'     => $this->input->post('kategori'),
            'qty'          => $qty,
            'satuan'       => $this->input->post('satuan'),
            'harga_satuan' => $harga,
            'total_biaya'  => $qty * $harga,
            'keterangan'   => $this->input->post('keterangan'),
        ];

        if ($this->M_Kmt->update_promo($id, $update)) {
            $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/promo');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_promo($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/promo');
    }

    public function export()
    {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_promo_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Promo');

        // Header
        $headers = [
            'No','Tanggal','Wilayah','Nama Item','Kategori',
            'Qty','Satuan','Harga Satuan','Total Biaya','Keterangan'
        ];

        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        // Style header
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Isi data
        foreach ($list as $i => $row) {
            $r = $i + 2;

            $sheet->setCellValueByColumnAndRow(1,  $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2,  $r, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValueByColumnAndRow(3,  $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4,  $r, $row['nama_item']);
            $sheet->setCellValueByColumnAndRow(5,  $r, $row['kategori'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6,  $r, $row['qty']);
            $sheet->setCellValueByColumnAndRow(7,  $r, $row['satuan'] ?? '-');
            $sheet->setCellValueByColumnAndRow(8,  $r, $row['harga_satuan']);
            $sheet->setCellValueByColumnAndRow(9,  $r, $row['total_biaya']);
            $sheet->setCellValueByColumnAndRow(10, $r, $row['keterangan'] ?? '-');
        }

        // Auto size
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Promo_KMT_' . $tahun . ($bulan ? '_' . $bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
