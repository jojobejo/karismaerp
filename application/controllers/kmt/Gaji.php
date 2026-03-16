<?php
// ================================================================
// controllers/content/kmt/Gaji.php
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Gaji extends CI_Controller {

    private $bulan_cols = [
        'gaji_jan','gaji_feb','gaji_mar','gaji_apr','gaji_mei','gaji_jun',
        'gaji_jul','gaji_agu','gaji_sep','gaji_okt','gaji_nov','gaji_des'
    ];

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        // Hanya KADEP yang bisa akses gaji
        if ((int)$this->session->userdata('lv') > 1) {
            $this->session->set_flashdata('error', 'Hanya KADEP yang dapat mengakses menu Gaji.');
            redirect('kmt/dashboard');
        }
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $id_wilayah = $this->input->get('id_wilayah') ?? null;

        $filter = ['tahun' => $tahun];
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list    = $this->M_Kmt->get_gaji_list($filter);
        $summary = $this->M_Kmt->get_total_gaji_per_bulan_wilayah($tahun, $id_wilayah ?: null);

        // Hitung total per karyawan
        foreach ($list as &$row) {
            $total = 0;
            foreach ($this->bulan_cols as $col) $total += (float)($row[$col] ?? 0);
            $row['total_gaji'] = $total;
        }

        $data = [
            'page_title'        => 'Data Gaji KMT CORN',
            'list'         => $list,
            'summary'      => $summary,
            'bulan_cols'   => $this->bulan_cols,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'id_wilayah'   => $id_wilayah,
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/gaji/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'page_title'        => 'Tambah Data Gaji',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'bulan_cols'   => $this->bulan_cols,
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/gaji/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('nama',       'Nama',    'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah', 'required|integer');
        $this->form_validation->set_rules('tahun',      'Tahun',   'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $insert = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'nama'       => $this->input->post('nama'),
            'posisi'     => $this->input->post('posisi'),
            'status'     => $this->input->post('status'),
            'tgl_mulai'  => $this->input->post('tgl_mulai') ?: null,
            'tgl_resign' => $this->input->post('tgl_resign') ?: null,
            'tahun'      => (int)$this->input->post('tahun'),
            'created_by' => $this->session->userdata('id_user'),
        ];

        foreach ($this->bulan_cols as $col) {
            $val = $this->input->post($col);
            $insert[$col] = $val ? (float)str_replace('.','', $val) : 0;
        }

        if ($this->M_Kmt->insert_gaji($insert)) {
            $this->session->set_flashdata('success', 'Data gaji berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/gaji');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_gaji_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'page_title'        => 'Edit Data Gaji',
            'row'          => $row,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'bulan_cols'   => $this->bulan_cols,
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/gaji/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id) {
        $update = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'nama'       => $this->input->post('nama'),
            'posisi'     => $this->input->post('posisi'),
            'status'     => $this->input->post('status'),
            'tgl_mulai'  => $this->input->post('tgl_mulai') ?: null,
            'tgl_resign' => $this->input->post('tgl_resign') ?: null,
            'tahun'      => (int)$this->input->post('tahun'),
        ];

        foreach ($this->bulan_cols as $col) {
            $val = $this->input->post($col);
            $update[$col] = $val ? (float)str_replace('.','', $val) : 0;
        }

        if ($this->M_Kmt->update_gaji($id, $update)) {
            $this->session->set_flashdata('success', 'Data gaji berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/gaji');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_gaji($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/gaji');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $id_wilayah = $this->input->get('id_wilayah') ?? null;

        $filter = ['tahun' => $tahun];
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_gaji_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Gaji');

        $headers = ['No','Wilayah','Nama','Posisi','Status','Tgl Mulai','Tgl Resign',
                    'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des','Total'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        $sheet->getStyle('A1:T1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $bulan_cols = ['gaji_jan','gaji_feb','gaji_mar','gaji_apr','gaji_mei','gaji_jun',
                    'gaji_jul','gaji_agu','gaji_sep','gaji_okt','gaji_nov','gaji_des'];

        foreach ($list as $i => $row) {
            $r = $i + 2;
            $total = 0;
            foreach ($bulan_cols as $col) $total += (float)($row[$col] ?? 0);

            $sheet->setCellValueByColumnAndRow(1,  $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2,  $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(3,  $r, $row['nama']);
            $sheet->setCellValueByColumnAndRow(4,  $r, $row['posisi'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5,  $r, $row['status'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6,  $r, $row['tgl_mulai'] ?? '-');
            $sheet->setCellValueByColumnAndRow(7,  $r, $row['tgl_resign'] ?? '-');
            $col = 8;
            foreach ($bulan_cols as $bc) {
                $sheet->setCellValueByColumnAndRow($col++, $r, $row[$bc] ?? 0);
            }
            $sheet->setCellValueByColumnAndRow($col, $r, $total);
        }

        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Gaji_KMT_' . $tahun . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
