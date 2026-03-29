<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Omset extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    // Cek akses — ABM tidak bisa input omset
    private function cek_bukan_abm() {
        if ((int)$this->session->userdata('lv') === 3) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke menu ini.');
            redirect('kmt/dashboard');
        }
    }

    // Wilayah filter sesuai level
    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('lv') === 3)
            ? (int)$this->session->userdata('wilayah')
            : null;
    }

    // ----------------------------------------------------------------
    // INDEX - Daftar Omset
    // ----------------------------------------------------------------
    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_omset_list($filter);

        // Hitung total omset hasil filter
        $total_omset = array_sum(array_column($list, 'penj_inc_ppn_neto'));

        $data = [
            'page_title'        => 'Data Omset KMT CORN',
            'list'         => $list,
            'total_omset'  => $total_omset,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/omset/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH - Form + Simpan
    // ----------------------------------------------------------------
    public function tambah() {
        $this->cek_bukan_abm(); // ABM tidak bisa input omset

        $data = [
            'page_title'        => 'Tambah Data Omset',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/omset/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->cek_bukan_abm();

        $this->form_validation->set_rules('tanggal',   'Tanggal',   'required');
        $this->form_validation->set_rules('id_wilayah','Wilayah',   'required|integer');
        $this->form_validation->set_rules('nama_toko', 'Nama Toko', 'required');
        $this->form_validation->set_rules('produk',    'Produk',    'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah();
            return;
        }

        $tanggal = $this->input->post('tanggal');
        $qty     = (float)str_replace(',', '.', $this->input->post('quantity'));
        $harga   = (float)str_replace(['.','Rp ','Rp',',' ], ['','','','.'], $this->input->post('harga_inc_ppn'));
        $dpp     = (float)str_replace(['.','Rp ','Rp'],['' ,'',''],$this->input->post('penj_dpp_neto'));
        $ppn     = (float)str_replace(['.','Rp ','Rp'],['' ,'',''],$this->input->post('penj_inc_ppn_neto'));

        $insert = [
            'no_urut'          => $this->input->post('no_urut'),
            'kode'             => $this->input->post('kode'),
            'bulan'            => (int)date('m', strtotime($tanggal)),
            'tahun'            => (int)date('Y', strtotime($tanggal)),
            'tanggal'          => $tanggal,
            'nomor'            => $this->input->post('nomor'),
            'inputer'          => $this->session->userdata('nama'),
            'no_retur'         => $this->input->post('no_retur'),
            'tgl_retur'        => $this->input->post('tgl_retur') ?: null,
            'sales_so'         => $this->input->post('sales_so'),
            'sc'               => $this->input->post('sc'),
            'se'               => $this->input->post('se'),
            'wilayah_se'       => $this->input->post('wilayah_se'),
            'id_wilayah'       => (int)$this->input->post('id_wilayah'),
            'nama_toko'        => $this->input->post('nama_toko'),
            'kota'             => $this->input->post('kota'),
            'merk'             => $this->input->post('merk'),
            'jenis'            => $this->input->post('jenis'),
            'produk'           => $this->input->post('produk'),
            'quantity'         => $qty,
            'unit'             => $this->input->post('unit'),
            'box'              => (float)$this->input->post('box'),
            'ltr_kg'           => (float)$this->input->post('ltr_kg'),
            'harga_inc_ppn'    => $harga,
            'penj_dpp_neto'    => $dpp,
            'penj_inc_ppn_neto'=> $ppn,
            'keterangan'       => $this->input->post('keterangan'),
            'tgl_kirim'        => $this->input->post('tgl_kirim') ?: null,
            'created_by'       => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_omset($insert)) {
            $this->session->set_flashdata('success', 'Data omset berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/omset/');
    }

    // ----------------------------------------------------------------
    // EDIT - Form + Update
    // ----------------------------------------------------------------
    public function edit($id) {
        $this->cek_bukan_abm();

        $row = $this->M_Kmt->get_omset_by_id($id);
        if (!$row) { show_404(); return; }

        $data = [
            'page_title'        => 'Edit Data Omset',
            'row'          => $row,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/omset/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id) {
        $this->cek_bukan_abm();

        $tanggal = $this->input->post('tanggal');
        $update  = [
            'no_urut'          => $this->input->post('no_urut'),
            'kode'             => $this->input->post('kode'),
            'bulan'            => (int)date('m', strtotime($tanggal)),
            'tahun'            => (int)date('Y', strtotime($tanggal)),
            'tanggal'          => $tanggal,
            'nomor'            => $this->input->post('nomor'),
            'no_retur'         => $this->input->post('no_retur'),
            'tgl_retur'        => $this->input->post('tgl_retur') ?: null,
            'sales_so'         => $this->input->post('sales_so'),
            'sc'               => $this->input->post('sc'),
            'se'               => $this->input->post('se'),
            'wilayah_se'       => $this->input->post('wilayah_se'),
            'id_wilayah'       => (int)$this->input->post('id_wilayah'),
            'nama_toko'        => $this->input->post('nama_toko'),
            'kota'             => $this->input->post('kota'),
            'merk'             => $this->input->post('merk'),
            'jenis'            => $this->input->post('jenis'),
            'produk'           => $this->input->post('produk'),
            'quantity'         => (float)$this->input->post('quantity'),
            'unit'             => $this->input->post('unit'),
            'box'              => (float)$this->input->post('box'),
            'ltr_kg'           => (float)$this->input->post('ltr_kg'),
            'harga_inc_ppn'    => (float)str_replace('.','', $this->input->post('harga_inc_ppn')),
            'penj_dpp_neto'    => (float)str_replace('.','', $this->input->post('penj_dpp_neto')),
            'penj_inc_ppn_neto'=> (float)str_replace('.','', $this->input->post('penj_inc_ppn_neto')),
            'keterangan'       => $this->input->post('keterangan'),
            'tgl_kirim'        => $this->input->post('tgl_kirim') ?: null,
        ];

        if ($this->M_Kmt->update_omset($id, $update)) {
            $this->session->set_flashdata('success', 'Data omset berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/omset');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        $this->cek_bukan_abm();
        if ($this->M_Kmt->delete_omset($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/omset');
    }

    public function retur($id_omset) {
        $omset = $this->M_Kmt->get_omset_by_id($id_omset);
        if (!$omset) { show_404(); return; }

        $list_retur = $this->M_Kmt->get_retur_by_omset($id_omset);
        $summary    = $this->M_Kmt->get_summary_retur_omset($id_omset);

        $data = [
            'page_title'  => 'Retur - ' . $omset['nama_toko'],
            'omset'       => $omset,
            'list_retur'  => $list_retur,
            'summary'     => $summary,
            'lv'          => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/omset/retur', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // SIMPAN RETUR
    // ----------------------------------------------------------------
    public function simpan_retur() {
        $id_omset = (int)$this->input->post('id_omset');
        $omset    = $this->M_Kmt->get_omset_by_id($id_omset);
        if (!$omset) { show_404(); return; }

        $tgl = $this->input->post('tanggal_retur');
        $insert = [
            'id_omset'       => $id_omset,
            'id_wilayah'     => $omset['id_wilayah'],
            'bulan'          => (int)date('m', strtotime($tgl)),
            'tahun'          => (int)date('Y', strtotime($tgl)),
            'tanggal_retur'  => $tgl,
            'no_retur'       => $this->input->post('no_retur'),
            'nama_toko'      => $omset['nama_toko'],
            'produk'         => $omset['produk'],
            'quantity'       => (float)$this->input->post('quantity'),
            'nilai_retur'    => (float)str_replace('.', '', $this->input->post('nilai_retur') ?? 0),
            'kurangi_target' => (int)$this->input->post('kurangi_target'),
            'keterangan'     => $this->input->post('keterangan'),
            'created_by'     => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_retur($insert)) {
            $this->session->set_flashdata('success', 'Retur berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan retur.');
        }
        redirect('kmt/omset/retur/' . $id_omset);
    }

    // ----------------------------------------------------------------
    // HAPUS RETUR
    // ----------------------------------------------------------------
    public function hapus_retur($id) {
        $retur    = $this->M_Kmt->get_retur_by_id($id);
        $id_omset = $retur['id_omset'] ?? null;

        if ($this->M_Kmt->delete_retur($id)) {
            $this->session->set_flashdata('success', 'Retur berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus retur.');
        }
        redirect($id_omset ? 'kmt/omset/retur/' . $id_omset : 'kmt/omset');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_omset_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Omset');

        // Header
        $headers = ['No','Tanggal','Wilayah','Nama Toko','Kota','Produk',
                    'Sales SO','Qty','Penj Inc PPN Neto'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        // Style header
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Data
        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $r, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValueByColumnAndRow(3, $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $r, $row['nama_toko']);
            $sheet->setCellValueByColumnAndRow(5, $r, $row['kota'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6, $r, $row['produk']);
            $sheet->setCellValueByColumnAndRow(7, $r, $row['sales_so'] ?? '-');
            $sheet->setCellValueByColumnAndRow(8, $r, $row['quantity']);
            $sheet->setCellValueByColumnAndRow(9, $r, $row['penj_inc_ppn_neto']);
        }

        // Auto width kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Omset_KMT_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function template_omset()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Omset');
 
        // ------- Baris 1: nama field (KEY untuk mapping, jangan diubah) -------
        $fields = [
            'A' => 'tanggal',        'B' => 'id_wilayah',   'C' => 'nama_toko',
            'D' => 'kota',           'E' => 'produk',        'F' => 'sales_so',
            'G' => 'quantity',       'H' => 'unit',          'I' => 'harga_inc_ppn',
            'J' => 'penj_dpp_neto',  'K' => 'penj_inc_ppn_neto',
            'L' => 'nomor',          'M' => 'no_urut',       'N' => 'kode',
            'O' => 'sc',             'P' => 'se',            'Q' => 'wilayah_se',
            'R' => 'merk',           'S' => 'jenis',         'T' => 'box',
            'U' => 'ltr_kg',         'V' => 'keterangan',
            'W' => 'tgl_kirim',      'X' => 'no_retur',      'Y' => 'tgl_retur',
        ];
 
        // ------- Baris 2: label ramah untuk pengguna -------
        $labels = [
            'A' => 'Tanggal (YYYY-MM-DD)*', 'B' => 'ID Wilayah*',    'C' => 'Nama Toko*',
            'D' => 'Kota',                  'E' => 'Produk*',          'F' => 'Sales SO',
            'G' => 'Quantity*',             'H' => 'Unit',             'I' => 'Harga Inc PPN',
            'J' => 'Penj DPP Neto',         'K' => 'Penj Inc PPN Neto*',
            'L' => 'Nomor Faktur',          'M' => 'No Urut',         'N' => 'Kode',
            'O' => 'SC',                    'P' => 'SE',               'Q' => 'Wilayah SE',
            'R' => 'Merk',                  'S' => 'Jenis',            'T' => 'Box',
            'U' => 'Ltr/Kg',                'V' => 'Keterangan',
            'W' => 'Tgl Kirim (YYYY-MM-DD)','X' => 'No Retur',        'Y' => 'Tgl Retur (YYYY-MM-DD)',
        ];
 
        foreach ($fields as $col => $field) {
            $sheet->setCellValue("{$col}1", $field);
            $sheet->setCellValue("{$col}2", $labels[$col]);
        }
 
        // Style baris 1 (biru tua)
        $sheet->getStyle('A1:Y1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);
 
        // Style baris 2 (biru muda)
        $sheet->getStyle('A2:Y2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E75B6']],
            'alignment' => ['horizontal' => 'center', 'wrapText' => true],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(30);
 
        // Contoh data baris 3
        $example = [
            'A' => date('Y-m-d'), 'B' => '1',              'C' => 'Toko Maju Jaya',
            'D' => 'Semarang',    'E' => 'CORN A 1KG',      'F' => 'Budi Santoso',
            'G' => '10',          'H' => 'SAK',             'I' => '55000',
            'J' => '500000',      'K' => '550000',           'L' => 'INV/2025/001',
            'M' => '1',           'N' => 'KMT-001',         'O' => '',
            'P' => '',            'Q' => '',                 'R' => 'KARISMA',
            'S' => 'CORN',        'T' => '2',               'U' => '10',
            'V' => 'Contoh data - hapus baris ini sebelum import',
            'W' => '', 'X' => '', 'Y' => '',
        ];
 
        foreach ($example as $col => $val) {
            $sheet->setCellValue("{$col}3", $val);
        }
 
        $sheet->getStyle('A3:Y3')->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F4FD']],
            'font' => ['italic' => true, 'color' => ['rgb' => '777777']],
        ]);
 
        // Auto width & freeze header
        foreach (array_keys($fields) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A3');
 
        // Sheet Petunjuk
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Petunjuk');
        $rows = [
            1  => ['PETUNJUK PENGISIAN TEMPLATE IMPORT OMSET', true, 14],
            3  => ['1. Kolom bertanda (*) wajib diisi, kolom lain opsional.', false, 11],
            4  => ['2. Format tanggal: YYYY-MM-DD  (contoh: 2025-01-15)', false, 11],
            5  => ['3. Baris 1 (biru tua) = nama field untuk sistem. JANGAN diubah/dihapus.', false, 11],
            6  => ['4. Baris 2 (biru muda) = label keterangan. JANGAN diubah/dihapus.', false, 11],
            7  => ['5. Isi data mulai baris ke-3.', false, 11],
            8  => ['6. Hapus baris contoh (baris 3) sebelum upload jika tidak dipakai.', false, 11],
            9  => ['7. Angka (quantity, harga, dll): tulis angka murni tanpa titik/koma ribuan.', false, 11],
            10 => ['8. id_wilayah: sesuaikan dengan ID wilayah pada sistem.', false, 11],
            11 => ['9. Jika tidak ada retur, kolom no_retur & tgl_retur dikosongkan.', false, 11],
        ];
        foreach ($rows as $r => [$txt, $bold, $size]) {
            $guide->setCellValue("A{$r}", $txt);
            $guide->getStyle("A{$r}")->getFont()->setBold($bold)->setSize($size);
        }
        $guide->getColumnDimension('A')->setWidth(80);
 
        $spreadsheet->setActiveSheetIndex(0);
 
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Omset.xlsx"');
        header('Cache-Control: max-age=0');
 
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
 
    // ----------------------------------------------------------------
    // IMPORT OMSET DARI EXCEL
    // ----------------------------------------------------------------
    public function import()
    {
        $this->cek_bukan_abm();
 
        // Validasi file upload
        $file = $_FILES['file_excel'] ?? null;
 
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File tidak ditemukan atau gagal diupload.');
            redirect('kmt/omset');
        }
 
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls');
            redirect('kmt/omset');
        }
 
        try {
            // Baca file Excel
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file['tmp_name']);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file['tmp_name']);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false); // index 0-based
 
            if (count($rows) < 3) {
                $this->session->set_flashdata('error', 'File kosong atau tidak memiliki data (minimal 1 baris data setelah header).');
                redirect('kmt/omset');
            }
 
            // Baris 0 (index): nama field → mapping kolom
            $field_names = array_map('trim', $rows[0]); // baris ke-1 di Excel = index 0 di array
            // Baris 1 (index): label → skip
            // Data mulai baris 2 (index)
 
            $insert_batch = [];
            $errors       = [];
            $skipped      = 0;
            $inserted     = 0;
 
            for ($i = 2; $i < count($rows); $i++) {
                $row    = $rows[$i];
                $line   = $i + 1; // nomor baris di Excel (human-readable)
 
                // Buat array asosiatif field => nilai
                $data = [];
                foreach ($field_names as $col_idx => $field) {
                    $data[$field] = isset($row[$col_idx]) ? trim((string)$row[$col_idx]) : '';
                }
 
                // Skip baris kosong sepenuhnya
                if (empty(array_filter($data))) {
                    $skipped++;
                    continue;
                }
 
                // ---- Validasi wajib ----
                $err = [];
                if (empty($data['tanggal']))           $err[] = 'tanggal wajib diisi';
                if (empty($data['id_wilayah']))        $err[] = 'id_wilayah wajib diisi';
                if (empty($data['nama_toko']))         $err[] = 'nama_toko wajib diisi';
                if (empty($data['produk']))            $err[] = 'produk wajib diisi';
                if (!is_numeric($data['quantity'] ?? '')) $err[] = 'quantity harus angka';
                if (!is_numeric($data['penj_inc_ppn_neto'] ?? '')) $err[] = 'penj_inc_ppn_neto harus angka';
 
                if (!empty($err)) {
                    $errors[] = "Baris {$line}: " . implode(', ', $err);
                    continue;
                }
 
                // ---- Normalisasi tanggal ----
                // Support format YYYY-MM-DD dan DD/MM/YYYY
                $tanggal = $this->_parse_tanggal($data['tanggal']);
                if (!$tanggal) {
                    $errors[] = "Baris {$line}: format tanggal tidak valid ({$data['tanggal']})";
                    continue;
                }
 
                $tgl_kirim = !empty($data['tgl_kirim'])  ? $this->_parse_tanggal($data['tgl_kirim'])  : null;
                $tgl_retur = !empty($data['tgl_retur'])  ? $this->_parse_tanggal($data['tgl_retur'])  : null;
 
                $insert_batch[] = [
                    'no_urut'           => $data['no_urut']    ?: null,
                    'kode'              => $data['kode']       ?: null,
                    'bulan'             => (int)date('m', strtotime($tanggal)),
                    'tahun'             => (int)date('Y', strtotime($tanggal)),
                    'tanggal'           => $tanggal,
                    'nomor'             => $data['nomor']      ?: null,
                    'inputer'           => $this->session->userdata('nama'),
                    'no_retur'          => $data['no_retur']   ?: null,
                    'tgl_retur'         => $tgl_retur,
                    'sales_so'          => $data['sales_so']   ?: null,
                    'sc'                => $data['sc']         ?: null,
                    'se'                => $data['se']         ?: null,
                    'wilayah_se'        => $data['wilayah_se'] ?: null,
                    'id_wilayah'        => (int)$data['id_wilayah'],
                    'nama_toko'         => $data['nama_toko'],
                    'kota'              => $data['kota']       ?: null,
                    'merk'              => $data['merk']       ?: null,
                    'jenis'             => $data['jenis']      ?: null,
                    'produk'            => $data['produk'],
                    'quantity'          => (float)str_replace(',', '.', $data['quantity']),
                    'unit'              => $data['unit']       ?: null,
                    'box'               => (float)($data['box']     ?? 0),
                    'ltr_kg'            => (float)($data['ltr_kg']  ?? 0),
                    'harga_inc_ppn'     => (float)str_replace(['.', ','], ['', '.'], $data['harga_inc_ppn'] ?? 0),
                    'penj_dpp_neto'     => (float)str_replace(['.', ','], ['', '.'], $data['penj_dpp_neto'] ?? 0),
                    'penj_inc_ppn_neto' => (float)str_replace(['.', ','], ['', '.'], $data['penj_inc_ppn_neto']),
                    'keterangan'        => $data['keterangan'] ?: null,
                    'tgl_kirim'         => $tgl_kirim,
                    'created_by'        => $this->session->userdata('id_user'),
                    'created_at'        => date('Y-m-d H:i:s'),
                ];
                $inserted++;
            }
 
            // Simpan ke database
            if (!empty($insert_batch)) {
                $this->M_Kmt->import_batch_omset($insert_batch);
            }
 
            // Buat pesan hasil
            $msg  = "Import selesai. <strong>{$inserted}</strong> data berhasil diimpor.";
            if ($skipped > 0) $msg .= " <strong>{$skipped}</strong> baris kosong dilewati.";
            if (!empty($errors)) {
                $msg .= "<br><strong>" . count($errors) . " baris gagal:</strong><ul>";
                foreach ($errors as $e) $msg .= "<li>{$e}</li>";
                $msg .= "</ul>";
                $this->session->set_flashdata('warning', $msg);
            } else {
                $this->session->set_flashdata('success', $msg);
            }
 
        } catch (\Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file: ' . $e->getMessage());
        }
 
        redirect('kmt/omset');
    }
 
    // ---- Helper: parse berbagai format tanggal ----
    private function _parse_tanggal($str)
    {
        $str = trim($str);
        if (empty($str)) return null;
 
        // Format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return $str;
        }
        // Format DD/MM/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $str, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        // Format Excel serial number (angka bulat)
        if (is_numeric($str)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$str);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        // Fallback strtotime
        $ts = strtotime($str);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
