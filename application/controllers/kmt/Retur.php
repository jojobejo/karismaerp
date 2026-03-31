<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur extends CI_Controller {

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

        $list        = $this->M_Kmt->get_retur_list($filter);
        $total_retur = array_sum(array_column($list, 'nilai_retur'));
        $summary     = $this->M_Kmt->get_summary_retur($tahun, $id_wilayah ?: null);

        $data = [
            'page_title'   => 'Data Retur KMT CORN',
            'list'         => $list,
            'total_retur'  => $total_retur,
            'summary'      => $summary,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'           => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/retur/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH
    // ----------------------------------------------------------------
    public function tambah() {
        $data = [
            'page_title'      => 'Tambah Data Retur',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/retur/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // SIMPAN (insert baru)
    // ----------------------------------------------------------------
    public function simpan() {
        $this->form_validation->set_rules('tanggal_retur', 'Tanggal Retur', 'required');
        $this->form_validation->set_rules('id_wilayah',   'Wilayah',       'required|integer');
        $this->form_validation->set_rules('nama_toko',    'Nama Toko',     'required');
        $this->form_validation->set_rules('produk',       'Produk',        'required');
        $this->form_validation->set_rules('kurangi_target','Target ABM',   'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah();
            return;
        }

        $tgl           = $this->input->post('tanggal_retur');
        $nilai_retur   = (float)str_replace('.', '', $this->input->post('nilai_retur') ?? 0);
        $kurangi_target = (int)$this->input->post('kurangi_target');

        $insert = $this->_build_insert($tgl, $nilai_retur, $kurangi_target);

        if ($this->M_Kmt->insert_retur($insert)) {
            // Jika retur standalone (tanpa id_omset) dan kurangi_target = 1,
            // tidak perlu adjust omset karena tidak linked ke transaksi tertentu.
            // Adjust hanya dilakukan jika ada id_omset.
            $this->session->set_flashdata('success', 'Data retur berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/retur');
    }

    // ----------------------------------------------------------------
    // EDIT
    // ----------------------------------------------------------------
    public function edit($id) {
        $row = $this->M_Kmt->get_retur_by_id($id);
        if (!$row) { show_404(); return; }

        $data = [
            'page_title'      => 'Edit Data Retur',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/retur/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // UPDATE
    // ----------------------------------------------------------------
    public function update($id) {
        // Ambil data lama sebelum diupdate
        $old = $this->M_Kmt->get_retur_by_id($id);
        if (!$old) { show_404(); return; }

        $tgl              = $this->input->post('tanggal_retur');
        $nilai_retur_baru = (float)str_replace('.', '', $this->input->post('nilai_retur') ?? 0);
        $kurangi_baru     = (int)$this->input->post('kurangi_target');
        $kurangi_lama     = (int)($old['kurangi_target'] ?? 0);
        $nilai_lama       = (float)($old['nilai_retur'] ?? 0);
        $id_omset         = $old['id_omset'] ?? null;

        $update = [
            'id_wilayah'        => (int)$this->input->post('id_wilayah'),
            'bulan'             => (int)date('m', strtotime($tgl)),
            'tahun'             => (int)date('Y', strtotime($tgl)),
            'tanggal_retur'     => $tgl,
            'no_retur'          => $this->input->post('no_retur'),
            'sc'                => $this->input->post('sc'),
            'nama_toko'         => $this->input->post('nama_toko'),
            'kota'              => $this->input->post('kota'),
            'produk'            => $this->input->post('produk'),
            'quantity'          => (float)$this->input->post('quantity'),
            'unit'              => $this->input->post('unit'),
            'harga_dpp'         => (float)str_replace('.', '', $this->input->post('harga_dpp') ?? 0),
            'nilai_retur'       => $nilai_retur_baru,
            'kurangi_target'    => $kurangi_baru,
            'kategori'          => $this->input->post('kategori'),
            'keterangan'        => $this->input->post('keterangan'),
            'keterangan_detail' => $this->input->post('keterangan_detail'),
        ];

        if ($this->M_Kmt->update_retur($id, $update)) {

            // ── Adjust nilai omset jika retur ini linked ke transaksi omset ──
            if ($id_omset) {
                // 1. Kembalikan efek lama dulu
                if ($kurangi_lama === 1) {
                    $this->M_Kmt->adjust_omset_nilai($id_omset, $nilai_lama, false); // tambah balik
                }
                // 2. Terapkan efek baru
                if ($kurangi_baru === 1) {
                    $this->M_Kmt->adjust_omset_nilai($id_omset, $nilai_retur_baru, true); // kurangi
                }
            }

            $this->session->set_flashdata('success', 'Data retur berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/retur');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        $retur    = $this->M_Kmt->get_retur_by_id($id);
        $id_omset = $retur['id_omset'] ?? null;

        // Kembalikan nilai omset jika retur ini tadinya mengurangi target
        if ($retur && (int)($retur['kurangi_target'] ?? 0) === 1 && $id_omset) {
            $this->M_Kmt->adjust_omset_nilai($id_omset, (float)$retur['nilai_retur'], false);
        }

        if ($this->M_Kmt->delete_retur($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/retur');
    }

    // ----------------------------------------------------------------
    // EXPORT EXCEL
    // ----------------------------------------------------------------
    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_retur_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Retur');

        $headers = [
            'No', 'Bln', 'Tanggal', 'No Retur', 'SC', 'Wilayah (ABM)',
            'Nama Toko', 'Kota', 'Nama Barang', 'Quantity', 'Unit',
            'Harga DPP', 'Jumlah Retur', 'Target ABM', 'Keterangan', 'Keterangan Detail', 'Kategori',
        ];

        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        $last_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$last_col}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $nama_bulan_short = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1,  $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2,  $r, $nama_bulan_short[(int)$row['bulan']] ?? '-');
            $sheet->setCellValueByColumnAndRow(3,  $r, date('d/m/Y', strtotime($row['tanggal_retur'])));
            $sheet->setCellValueByColumnAndRow(4,  $r, $row['no_retur']         ?? '-');
            $sheet->setCellValueByColumnAndRow(5,  $r, $row['sc']               ?? '-');
            $sheet->setCellValueByColumnAndRow(6,  $r, $row['nama_wilayah']     ?? '-');
            $sheet->setCellValueByColumnAndRow(7,  $r, $row['nama_toko']);
            $sheet->setCellValueByColumnAndRow(8,  $r, $row['kota']             ?? '-');
            $sheet->setCellValueByColumnAndRow(9,  $r, $row['produk']);
            $sheet->setCellValueByColumnAndRow(10, $r, $row['quantity']);
            $sheet->setCellValueByColumnAndRow(11, $r, $row['unit']             ?? '-');
            $sheet->setCellValueByColumnAndRow(12, $r, $row['harga_dpp']        ?? 0);
            $sheet->setCellValueByColumnAndRow(13, $r, $row['nilai_retur']);
            $sheet->setCellValueByColumnAndRow(14, $r, (int)($row['kurangi_target'] ?? 0) === 1 ? 'Retur' : 'Replacement');
            $sheet->setCellValueByColumnAndRow(15, $r, $row['keterangan']       ?? '-');
            $sheet->setCellValueByColumnAndRow(16, $r, $row['keterangan_detail'] ?? '-');
            $sheet->setCellValueByColumnAndRow(17, $r, $row['kategori']         ?? '-');
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)
            )->setAutoSize(true);
        }

        $filename = 'Retur_KMT_' . $tahun . ($bulan ? '_Bln' . $bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ================================================================
    // PRIVATE HELPER
    // ================================================================
    private function _build_insert(string $tgl, float $nilai_retur, int $kurangi_target): array {
        return [
            'id_wilayah'        => (int)$this->input->post('id_wilayah'),
            'bulan'             => (int)date('m', strtotime($tgl)),
            'tahun'             => (int)date('Y', strtotime($tgl)),
            'tanggal_retur'     => $tgl,
            'no_retur'          => $this->input->post('no_retur'),
            'sc'                => $this->input->post('sc'),
            'nama_toko'         => $this->input->post('nama_toko'),
            'kota'              => $this->input->post('kota'),
            'produk'            => $this->input->post('produk'),
            'quantity'          => (float)$this->input->post('quantity'),
            'unit'              => $this->input->post('unit'),
            'harga_dpp'         => (float)str_replace('.', '', $this->input->post('harga_dpp') ?? 0),
            'nilai_retur'       => $nilai_retur,
            'kurangi_target'    => $kurangi_target,
            'kategori'          => $this->input->post('kategori'),
            'keterangan'        => $this->input->post('keterangan'),
            'keterangan_detail' => $this->input->post('keterangan_detail'),
            'created_by'        => $this->session->userdata('id_user'),
        ];
    }
}