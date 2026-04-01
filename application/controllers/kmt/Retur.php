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
    // SIMPAN
    // ----------------------------------------------------------------
    public function simpan() {
        $this->form_validation->set_rules('tanggal_retur', 'Tanggal Retur', 'required');
        $this->form_validation->set_rules('id_wilayah',   'Wilayah',       'required|integer');
        $this->form_validation->set_rules('nama_toko',    'Nama Toko',     'required');
        $this->form_validation->set_rules('produk',       'Produk',        'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah();
            return;
        }

        $tgl    = $this->input->post('tanggal_retur');
        $insert = $this->_build_row_from_post($tgl);

        if ($this->M_Kmt->insert_retur($insert)) {
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
        $tgl    = $this->input->post('tanggal_retur');
        $update = $this->_build_row_from_post($tgl);

        if ($this->M_Kmt->update_retur($id, $update)) {
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
        $sheet->setTitle('RETUR');

        // Definisi kolom [label => key]
        $columns = [
            'No'                 => null,
            'Bln'                => 'bulan',
            'Tanggal'            => 'tanggal_retur',
            'No LPB'             => 'no_lpb',
            'No Retur'           => 'no_retur',
            'Tgl Fak Retur'      => 'tgl_fak_retur',
            'No. Faktur'         => 'no_faktur',
            'SC'                 => 'sc',
            'Wilayah (ABM)'      => 'nama_wilayah',
            'Kode Toko'          => 'kode_toko',
            'Nama Toko'          => 'nama_toko',
            'Kontak Person'      => 'kontak_person',
            'Alamat'             => 'alamat',
            'Kota'               => 'kota',
            'Merk'               => 'merk',
            'Jenis'              => 'jenis',
            'Gol'                => 'golongan',
            'Prod'               => 'prod',
            'Point'              => 'point',
            'Fokus'              => 'fokus',
            'Kode Produk'        => 'kode_produk',
            'Nama Barang'        => 'produk',
            'Quantity'           => 'quantity',
            'Unit'               => 'unit',
            'Harga DPP'          => 'harga_dpp',
            'Jumlah Retur'       => 'nilai_retur',
            'Ket'                => 'keterangan',
            'Keterangan Detail'  => 'keterangan_detail',
            'Kategori'           => 'kategori',
        ];

        $headers    = array_keys($columns);
        $field_keys = array_values($columns);
        $total_col  = count($headers);

        foreach ($headers as $i => $label) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $label);
        }

        $last_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_col);
        $sheet->getStyle("A1:{$last_col}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['rgb' => 'FFFFFF'],
            ]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);

        $nama_bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $total_retur = 0;

        foreach ($list as $i => $row) {
            $r = $i + 2;
            foreach ($field_keys as $col_idx => $key) {
                $col_num = $col_idx + 1;
                if ($key === null) {
                    $sheet->setCellValueByColumnAndRow($col_num, $r, $i + 1);
                    continue;
                }
                $val = $row[$key] ?? null;
                if ($key === 'bulan') {
                    $val = $nama_bln[(int)$val] ?? '-';
                } elseif (in_array($key, ['tanggal_retur','tgl_fak_retur']) && !empty($val)) {
                    $val = date('d/m/Y', strtotime($val));
                }
                $sheet->setCellValueByColumnAndRow($col_num, $r, $val);
            }
            $total_retur += (float)($row['nilai_retur'] ?? 0);
        }

        // Baris TOTAL
        $total_row     = count($list) + 2;
        $neto_col_idx  = array_search('nilai_retur', $field_keys);
        $neto_col_num  = $neto_col_idx + 1;
        $sheet->setCellValueByColumnAndRow($neto_col_num - 1, $total_row, 'TOTAL:');
        $sheet->getStyleByColumnAndRow($neto_col_num - 1, $total_row)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow($neto_col_num - 1, $total_row)->getAlignment()->setHorizontal('right');
        $sheet->setCellValueByColumnAndRow($neto_col_num, $total_row, $total_retur);
        $sheet->getStyleByColumnAndRow($neto_col_num, $total_row)->getFont()->setBold(true);

        // Format angka
        $numeric_keys = ['quantity','harga_dpp','nilai_retur','point'];
        foreach ($field_keys as $col_idx => $key) {
            if (!in_array($key, $numeric_keys)) continue;
            $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx + 1);
            $fmt = in_array($key, ['harga_dpp','nilai_retur']) ? '#,##0' : '#,##0.##';
            if (count($list) > 0) {
                $sheet->getStyle("{$col_letter}2:{$col_letter}" . (count($list) + 1))
                      ->getNumberFormat()->setFormatCode($fmt);
            }
            $sheet->getStyleByColumnAndRow($col_idx + 1, $total_row)
                  ->getNumberFormat()->setFormatCode($fmt);
        }

        // Zebra stripe
        for ($r = 2; $r <= count($list) + 1; $r++) {
            $style_arr = ['borders' => ['allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['rgb' => 'D0D0D0'],
            ]]];
            if ($r % 2 === 0) {
                $style_arr['fill'] = ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF5F5']];
            }
            $sheet->getStyle("A{$r}:{$last_col}{$r}")->applyFromArray($style_arr);
        }

        foreach (range(1, $total_col) as $col) {
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col)
            )->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        $nama_bulan_suffix = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $suffix = $bulan ? '_' . $nama_bulan_suffix[(int)$bulan] : '';
        $filename = "Retur_KMT_{$tahun}{$suffix}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ----------------------------------------------------------------
    // TEMPLATE IMPORT RETUR
    // ----------------------------------------------------------------
    public function template_retur() {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Retur');

        // Baris 1: nama field (key untuk mapping — jangan diubah)
        $fields = [
            'A' => 'tanggal_retur',    'B' => 'id_wilayah',      'C' => 'no_lpb',
            'D' => 'no_retur',         'E' => 'tgl_fak_retur',   'F' => 'no_faktur',
            'G' => 'sc',               'H' => 'kode_toko',       'I' => 'nama_toko',
            'J' => 'kontak_person',    'K' => 'alamat',          'L' => 'kota',
            'M' => 'merk',             'N' => 'jenis',           'O' => 'golongan',
            'P' => 'prod',             'Q' => 'point',           'R' => 'fokus',
            'S' => 'kode_produk',      'T' => 'produk',          'U' => 'quantity',
            'V' => 'unit',             'W' => 'harga_dpp',       'X' => 'nilai_retur',
            'Y' => 'keterangan',       'Z' => 'keterangan_detail','AA'=> 'kategori',
        ];

        // Baris 2: label ramah pengguna
        $labels = [
            'A' => 'Tanggal Retur (YYYY-MM-DD)*', 'B' => 'ID Wilayah*',       'C' => 'No LPB',
            'D' => 'No Retur',                     'E' => 'Tgl Fak Retur (YYYY-MM-DD)', 'F' => 'No. Faktur',
            'G' => 'SC',                           'H' => 'Kode Toko',        'I' => 'Nama Toko*',
            'J' => 'Kontak Person',                'K' => 'Alamat',           'L' => 'Kota',
            'M' => 'Merk',                         'N' => 'Jenis',            'O' => 'Golongan',
            'P' => 'Prod',                         'Q' => 'Point',            'R' => 'Fokus',
            'S' => 'Kode Produk',                  'T' => 'Nama Barang*',     'U' => 'Quantity',
            'V' => 'Unit',                         'W' => 'Harga DPP',        'X' => 'Jumlah Retur*',
            'Y' => 'Ket (Retur/Replacement)',      'Z' => 'Keterangan Detail','AA'=> 'Kategori',
        ];

        foreach ($fields as $col => $field) {
            $sheet->setCellValue("{$col}1", $field);
            $sheet->setCellValue("{$col}2", $labels[$col]);
        }

        // Style baris 1 (biru tua — sama dengan template omset)
        $sheet->getStyle('A1:AA1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Style baris 2 (label)
        $sheet->getStyle('A2:AA2')->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F0FE']],
            'alignment' => ['horizontal' => 'center', 'wrapText' => true],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(30);

        // Contoh data baris 3
        $example = [
            'A' => date('Y-m-d'),  'B' => '1',
            'C' => 'R25000033B',   'D' => 'RJ25000062',
            'E' => date('Y-m-d'),  'F' => 'D25006217',
            'G' => 'Reni',         'H' => 'SURY08',
            'I' => 'CV. Surya Agro Pradhana',
            'J' => 'Surya Agro Pradhana, CV',
            'K' => 'Jl. Raya Segodorejo No.1, Jombang',
            'L' => 'Jombang',      'M' => 'Jagung Q-235',
            'N' => 'Benih Jagung', 'O' => 'Benih',
            'P' => 'Crindo Satria Agro, CV',
            'Q' => '0',            'R' => 'A',
            'S' => 'QJAGU76',      'T' => 'Jagung Q-235 10 X 1 Kg*',
            'U' => '20',           'V' => 'Pack',
            'W' => '99000',        'X' => '1980000',
            'Y' => 'Retur',
            'Z' => 'Barang Bermasalah Retur ke Pabrik — hapus baris ini sebelum import',
            'AA'=> 'Barang bermasalah',
        ];

        foreach ($example as $col => $val) {
            $sheet->setCellValue("{$col}3", $val);
        }
        $sheet->getStyle('A3:AA3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '888888']],
        ]);

        // Auto width & freeze
        foreach (array_keys($fields) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A3');

        // Sheet Petunjuk
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Petunjuk');
        $rows = [
            1  => ['PETUNJUK PENGISIAN TEMPLATE IMPORT RETUR', true,  14],
            3  => ['1. Kolom bertanda (*) wajib diisi, kolom lain opsional.', false, 11],
            4  => ['2. Format tanggal: YYYY-MM-DD  (contoh: 2025-06-04)', false, 11],
            5  => ['3. Baris 1 (biru tua) = nama field untuk sistem. JANGAN diubah/dihapus.', false, 11],
            6  => ['4. Baris 2 (biru muda) = label keterangan. JANGAN diubah/dihapus.', false, 11],
            7  => ['5. Isi data mulai baris ke-3. Hapus baris contoh sebelum upload.', false, 11],
            8  => ['6. id_wilayah: sesuaikan dengan ID wilayah pada sistem.', false, 11],
            9  => ['7. Kolom Ket: isi dengan "Retur" atau "Replacement".', false, 11],
            10 => ['8. Angka (quantity, harga, jumlah): tulis angka murni tanpa titik/koma ribuan.', false, 11],
            12 => ['CATATAN: File Excel perusahaan (format asli) juga dapat diimport langsung', true,  11],
            13 => ['tanpa menggunakan template ini. Sistem akan mendeteksi format secara otomatis.', false, 11],
        ];
        foreach ($rows as $r => [$txt, $bold, $size]) {
            $guide->setCellValue("A{$r}", $txt);
            $guide->getStyle("A{$r}")->getFont()->setBold($bold)->setSize($size);
        }
        $guide->getColumnDimension('A')->setWidth(85);
        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Retur.xlsx"');
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ----------------------------------------------------------------
    // IMPORT EXCEL — deteksi otomatis 2 format
    // ----------------------------------------------------------------
    public function import() {
        $file = $_FILES['file_excel'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File tidak ditemukan atau gagal diupload.');
            redirect('kmt/retur');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls');
            redirect('kmt/retur');
        }

        $tmp_dir  = FCPATH . 'assets/uploads/tmp/';
        if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
        $tmp_path = $tmp_dir . uniqid('retur_') . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $tmp_path)) {
            $this->session->set_flashdata('error', 'Gagal memindahkan file upload.');
            redirect('kmt/retur');
        }

        try {
            $result = $this->_baca_excel_retur($tmp_path, $ext);

            if (!empty($result['data'])) {
                foreach (array_chunk($result['data'], 200) as $chunk) {
                    $this->db->insert_batch('tbkmt_retur', $chunk);
                }
            }

            $inserted = count($result['data']);
            $msg = "Import selesai (<em>{$result['format']}</em>). "
                 . "<strong>{$inserted}</strong> data berhasil diimpor.";
            if ($result['skipped'] > 0) {
                $msg .= " <strong>{$result['skipped']}</strong> baris kosong dilewati.";
            }
            if (!empty($result['errors'])) {
                $msg .= '<br><strong>' . count($result['errors']) . ' baris warning:</strong><ul>';
                foreach ($result['errors'] as $e) $msg .= "<li>{$e}</li>";
                $msg .= '</ul>';
                $this->session->set_flashdata('warning', $msg);
            } else {
                $this->session->set_flashdata('success', $msg);
            }

        } catch (\Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file: ' . $e->getMessage());
        } finally {
            if (file_exists($tmp_path)) @unlink($tmp_path);
        }

        redirect('kmt/retur');
    }

    // ================================================================
    // CORE READER — deteksi format otomatis
    // ================================================================
    private function _baca_excel_retur(string $path, string $ext): array {
        if ($ext === 'xls') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $reader->setReadDataOnly(false);

        // Coba load sheet RETUR, fallback ke sheet aktif
        try {
            $reader->setLoadSheetsOnly(['RETUR', 'Import Retur']);
            $spreadsheet = $reader->load($path);
        } catch (\Exception $e) {
            $r2 = ($ext === 'xls')
                ? new \PhpOffice\PhpSpreadsheet\Reader\Xls()
                : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $r2->setReadDataOnly(false);
            $spreadsheet = $r2->load($path);
        }

        $sheet = $spreadsheet->getActiveSheet();

        // Baca baris 1 untuk deteksi format
        $row1_vals = [];
        foreach ($sheet->getRowIterator(1, 1) as $rowObj) {
            foreach ($rowObj->getCellIterator('A', 'AJ') as $cell) {
                $row1_vals[] = strtolower(trim((string)$cell->getFormattedValue()));
            }
        }

        // FORMAT TEMPLATE: baris 1 berisi nama field (ada 'tanggal_retur' atau 'nilai_retur')
        $is_template = in_array('tanggal_retur', $row1_vals)
                    || in_array('nilai_retur', $row1_vals);

        if ($is_template) {
            return $this->_proses_format_template_retur($sheet, $row1_vals);
        } else {
            // FORMAT PERUSAHAAN: baris 1 = judul "RETUR PENJUALAN", baris 2 = header
            return $this->_proses_format_perusahaan_retur($sheet);
        }
    }

    // ================================================================
    // FORMAT A — Template sistem (field names di baris 1, data mulai baris 3)
    // ================================================================
    private function _proses_format_template_retur($sheet, array $field_names): array {
        $wilayah_list = $this->M_Kmt->get_wilayah();
        $wilayah_map  = [];
        foreach ($wilayah_list as $w) {
            $wilayah_map[(int)$w['id']] = (int)$w['id']; // by ID langsung
        }

        $insert_batch = [];
        $errors       = [];
        $skipped      = 0;

        // Data mulai baris 3 (baris 2 = label)
        foreach ($sheet->getRowIterator(3) as $rowObj) {
            $line  = $rowObj->getRowIndex();
            $cells = [];
            foreach ($rowObj->getCellIterator('A', 'AJ') as $cell) {
                $cells[] = $cell->getValue();
            }

            $data = [];
            foreach ($field_names as $i => $field) {
                $data[trim($field)] = isset($cells[$i]) ? trim((string)$cells[$i]) : '';
            }

            if (empty(array_filter($data))) { $skipped++; continue; }

            // Validasi wajib
            $err = [];
            if (empty($data['tanggal_retur'])) $err[] = 'tanggal_retur wajib';
            if (empty($data['id_wilayah']))    $err[] = 'id_wilayah wajib';
            if (empty($data['nama_toko']))     $err[] = 'nama_toko wajib';
            if (empty($data['produk']))        $err[] = 'produk wajib';
            if (!empty($err)) { $errors[] = "Baris {$line}: " . implode(', ', $err); continue; }

            $tanggal       = $this->_parse_tgl($data['tanggal_retur']);
            $tgl_fak_retur = $this->_parse_tgl($data['tgl_fak_retur'] ?? '');
            if (!$tanggal) { $errors[] = "Baris {$line}: format tanggal tidak valid"; continue; }

            $insert_batch[] = [
                'id_wilayah'        => (int)($data['id_wilayah'] ?? 1),
                'bulan'             => (int)date('m', strtotime($tanggal)),
                'tahun'             => (int)date('Y', strtotime($tanggal)),
                'tanggal_retur'     => $tanggal,
                'no_lpb'            => $data['no_lpb']            ?: null,
                'no_retur'          => $data['no_retur']          ?: null,
                'tgl_fak_retur'     => $tgl_fak_retur,
                'no_faktur'         => $data['no_faktur']         ?: null,
                'sc'                => $data['sc']                ?: null,
                'kode_toko'         => $data['kode_toko']         ?: null,
                'nama_toko'         => $data['nama_toko'],
                'kontak_person'     => $data['kontak_person']     ?: null,
                'alamat'            => $data['alamat']            ?: null,
                'kota'              => $data['kota']              ?: null,
                'merk'              => $data['merk']              ?: null,
                'jenis'             => $data['jenis']             ?: null,
                'golongan'          => $data['golongan']          ?: null,
                'prod'              => $data['prod']              ?: null,
                'point'             => (float)($data['point']     ?? 0),
                'fokus'             => $data['fokus']             ?: null,
                'kode_produk'       => $data['kode_produk']       ?: null,
                'produk'            => $data['produk'],
                'quantity'          => (float)str_replace(',', '.', $data['quantity'] ?? 0),
                'unit'              => $data['unit']              ?: null,
                'harga_dpp'         => (float)str_replace(['.', ','], ['', '.'], $data['harga_dpp'] ?? 0),
                'nilai_retur'       => (float)str_replace(['.', ','], ['', '.'], $data['nilai_retur'] ?? 0),
                'keterangan'        => $data['keterangan']        ?: null,
                'keterangan_detail' => $data['keterangan_detail'] ?: null,
                'kategori'          => $data['kategori']          ?: null,
                'created_by'        => $this->session->userdata('id_user'),
                'created_at'        => date('Y-m-d H:i:s'),
            ];
        }

        return ['data' => $insert_batch, 'errors' => $errors, 'skipped' => $skipped,
                'format' => 'Template Sistem'];
    }

    // ================================================================
    // FORMAT B — File Excel perusahaan
    // Baris 1 = judul "RETUR PENJUALAN", baris 2 = header, data mulai baris 3
    //
    // Pemetaan kolom (0-based):
    //  0=No   1=Bln   2=Tanggal   3=No LPB   4=No Retur   5=Tgl Fak Retur
    //  6=No. Faktur   7=SC   8=ABM   9=Kode Toko   10=Nama Toko
    //  11=Kontak Person   12=Alamat   13=Kota   14=Merk   15=Jenis
    //  16=Gol   17=Prod   18=Point   19=Fokus   20=Kode Produk
    //  21=Nama Barang   22=Quantity   23=Unit   24=Harga DPP
    //  25=Harga PPN   26=Jumlah DPP   27=Jumlah Retur
    //  28=Kategori(1)   29=Tgl Nota   30=Nota   31=Value Nota
    //  32=Ket   33=Keterangan Detail   34=Kategori(2)
    // ================================================================
    private function _proses_format_perusahaan_retur($sheet): array {
        $wilayah_list = $this->M_Kmt->get_wilayah();
        $wilayah_map  = [];
        foreach ($wilayah_list as $w) {
            $wilayah_map[strtoupper(trim($w['nama_wilayah']))] = (int)$w['id'];
        }

        $insert_batch = [];
        $errors       = [];
        $skipped      = 0;

        // Data mulai baris 3 (baris 1=judul, baris 2=header)
        foreach ($sheet->getRowIterator(3) as $rowObj) {
            $line  = $rowObj->getRowIndex();
            $cells = [];
            foreach ($rowObj->getCellIterator('A', 'AJ') as $cell) {
                $cells[] = $cell->getValue();
            }
            $cells = array_pad(array_slice($cells, 0, 35), 35, null);

            $meaningful = array_filter(
                array_slice($cells, 0, 28),
                fn($v) => $v !== null && trim((string)$v) !== ''
            );
            if (empty($meaningful)) { $skipped++; continue; }

            // Pemetaan kolom
            $tanggal       = $this->_parse_excel_val($cells[2]);
            $no_lpb        = $this->_s($cells[3]);
            $no_retur      = $this->_s($cells[4]);
            $tgl_fak_retur = $this->_parse_excel_val($cells[5]);
            $no_faktur     = $this->_s($cells[6]);
            $sc            = $this->_s($cells[7]);
            $abm           = strtoupper(trim($this->_s($cells[8])));
            $kode_toko     = $this->_s($cells[9]);
            $nama_toko     = $this->_s($cells[10]);
            $kontak_person = $this->_s($cells[11]);
            $alamat        = $this->_s($cells[12]);
            $kota          = $this->_s($cells[13]);
            $merk          = $this->_s($cells[14]);
            $jenis         = $this->_s($cells[15]);
            $golongan      = $this->_s($cells[16]);
            $prod          = $this->_s($cells[17]);
            $point         = is_numeric($cells[18]) ? (float)$cells[18] : 0;
            $fokus         = $this->_s($cells[19]);
            $kode_produk   = $this->_s($cells[20]);
            $produk        = $this->_s($cells[21]);
            $quantity      = is_numeric($cells[22]) ? (float)$cells[22] : 0;
            $unit          = $this->_s($cells[23]);
            $harga_dpp     = is_numeric($cells[24]) ? (float)$cells[24] : 0;
            // col 25=Harga PPN, col 26=Jumlah DPP — tidak dipakai
            $nilai_retur       = is_numeric($cells[27]) ? (float)$cells[27] : 0;
            // col 28-31 = Kategori/Nota Pengganti — tidak dipakai
            $keterangan        = $this->_s($cells[32]);
            $keterangan_detail = $this->_s($cells[33]);
            $kategori          = $this->_s($cells[34]);

            // Validasi
            $err = [];
            if (!$tanggal)         $err[] = 'tanggal tidak valid';
            if (empty($nama_toko)) $err[] = 'nama_toko kosong';
            if (empty($produk))    $err[] = 'produk kosong';
            if (!empty($err)) { $errors[] = "Baris {$line}: " . implode(', ', $err); continue; }

            // Mapping wilayah: exact → partial → default
            $id_wilayah = $wilayah_map[$abm] ?? null;
            if (!$id_wilayah && $abm !== '') {
                foreach ($wilayah_map as $nm => $id) {
                    if (strpos($abm, $nm) !== false || strpos($nm, $abm) !== false) {
                        $id_wilayah = $id;
                        break;
                    }
                }
            }
            if (!$id_wilayah) {
                $id_wilayah = !empty($wilayah_list) ? (int)$wilayah_list[0]['id'] : 1;
                if ($abm !== '') {
                    $errors[] = "Baris {$line}: wilayah '{$abm}' tidak dikenali → id_wilayah={$id_wilayah}";
                }
            }

            $insert_batch[] = [
                'id_wilayah'        => $id_wilayah,
                'bulan'             => (int)date('m', strtotime($tanggal)),
                'tahun'             => (int)date('Y', strtotime($tanggal)),
                'tanggal_retur'     => $tanggal,
                'no_lpb'            => $no_lpb        ?: null,
                'no_retur'          => $no_retur       ?: null,
                'tgl_fak_retur'     => $tgl_fak_retur,
                'no_faktur'         => $no_faktur      ?: null,
                'sc'                => $sc             ?: null,
                'kode_toko'         => $kode_toko      ?: null,
                'nama_toko'         => $nama_toko,
                'kontak_person'     => $kontak_person  ?: null,
                'alamat'            => $alamat         ?: null,
                'kota'              => $kota           ?: null,
                'merk'              => $merk           ?: null,
                'jenis'             => $jenis          ?: null,
                'golongan'          => $golongan       ?: null,
                'prod'              => $prod           ?: null,
                'point'             => $point,
                'fokus'             => $fokus          ?: null,
                'kode_produk'       => $kode_produk    ?: null,
                'produk'            => $produk,
                'quantity'          => $quantity,
                'unit'              => $unit           ?: null,
                'harga_dpp'         => $harga_dpp,
                'nilai_retur'       => $nilai_retur,
                'keterangan'        => $keterangan     ?: null,
                'keterangan_detail' => $keterangan_detail ?: null,
                'kategori'          => $kategori       ?: null,
                'created_by'        => $this->session->userdata('id_user'),
                'created_at'        => date('Y-m-d H:i:s'),
            ];
        }

        return ['data' => $insert_batch, 'errors' => $errors, 'skipped' => $skipped,
                'format' => 'File Excel Perusahaan'];
    }

    // ================================================================
    // HELPERS
    // ================================================================
    private function _parse_excel_val($val): ?string {
        if ($val === null || $val === '') return null;
        if ($val instanceof \DateTime) return $val->format('Y-m-d');
        if (is_float($val) || (is_int($val) && $val > 10000 && $val < 100000)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        return $this->_parse_tgl((string)$val);
    }

    private function _parse_tgl(string $str): ?string {
        $str = trim($str);
        if (empty($str)) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) return $str;
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        $ts = strtotime($str);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function _s($val): string {
        if ($val === null) return '';
        if ($val instanceof \DateTime) return $val->format('Y-m-d');
        return trim((string)$val);
    }

    private function _build_row_from_post(string $tgl): array {
        return [
            'id_wilayah'        => (int)$this->input->post('id_wilayah'),
            'bulan'             => (int)date('m', strtotime($tgl)),
            'tahun'             => (int)date('Y', strtotime($tgl)),
            'tanggal_retur'     => $tgl,
            'no_lpb'            => $this->input->post('no_lpb'),
            'no_retur'          => $this->input->post('no_retur'),
            'tgl_fak_retur'     => $this->input->post('tgl_fak_retur') ?: null,
            'no_faktur'         => $this->input->post('no_faktur'),
            'sc'                => $this->input->post('sc'),
            'kode_toko'         => $this->input->post('kode_toko'),
            'nama_toko'         => $this->input->post('nama_toko'),
            'kontak_person'     => $this->input->post('kontak_person'),
            'alamat'            => $this->input->post('alamat'),
            'kota'              => $this->input->post('kota'),
            'merk'              => $this->input->post('merk'),
            'jenis'             => $this->input->post('jenis'),
            'golongan'          => $this->input->post('golongan'),
            'prod'              => $this->input->post('prod'),
            'point'             => (float)$this->input->post('point'),
            'fokus'             => $this->input->post('fokus'),
            'kode_produk'       => $this->input->post('kode_produk'),
            'produk'            => $this->input->post('produk'),
            'quantity'          => (float)$this->input->post('quantity'),
            'unit'              => $this->input->post('unit'),
            'harga_dpp'         => (float)str_replace('.', '', $this->input->post('harga_dpp') ?? 0),
            'nilai_retur'       => (float)str_replace('.', '', $this->input->post('nilai_retur') ?? 0),
            'keterangan'        => $this->input->post('keterangan'),
            'keterangan_detail' => $this->input->post('keterangan_detail'),
            'kategori'          => $this->input->post('kategori'),
            'created_by'        => $this->session->userdata('id_user'),
        ];
    }
}