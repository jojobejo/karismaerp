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

    public function template_gaji()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Gaji');

        // Baris 1: nama field (KEY — jangan diubah)
        $fields = [
            'A' => 'wilayah', 'B' => 'nama',      'C' => 'posisi',
            'D' => 'status',     'E' => 'tgl_mulai',  'F' => 'tgl_resign',
            'G' => 'tahun',
            'H' => 'gaji_jan',   'I' => 'gaji_feb',   'J' => 'gaji_mar',
            'K' => 'gaji_apr',   'L' => 'gaji_mei',   'M' => 'gaji_jun',
            'N' => 'gaji_jul',   'O' => 'gaji_agu',   'P' => 'gaji_sep',
            'Q' => 'gaji_okt',   'R' => 'gaji_nov',   'S' => 'gaji_des',
        ];

        // Baris 2: label ramah
        $labels = [
            'A' => 'Wilayah* (Jatim Timur / Jatim Barat / NTB)',
            'B' => 'Nama Karyawan*',
            'C' => 'Posisi/Jabatan',             'D' => 'Status (Aktif/Resign)',
            'E' => 'Tgl Mulai (YYYY-MM-DD)',     'F' => 'Tgl Resign (YYYY-MM-DD)',
            'G' => 'Tahun*',
            'H' => 'Gaji Jan',                   'I' => 'Gaji Feb',
            'J' => 'Gaji Mar',                   'K' => 'Gaji Apr',
            'L' => 'Gaji Mei',                   'M' => 'Gaji Jun',
            'N' => 'Gaji Jul',                   'O' => 'Gaji Agu',
            'P' => 'Gaji Sep',                   'Q' => 'Gaji Okt',
            'R' => 'Gaji Nov',                   'S' => 'Gaji Des',
        ];

        foreach ($fields as $col => $field) {
            $sheet->setCellValue("{$col}1", $field);
            $sheet->setCellValue("{$col}2", $labels[$col]);
        }

        // Style baris 1 (biru tua)
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Style baris 2 (tanpa warna)
        $sheet->getStyle('A2:S2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'wrapText' => true],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(30);

        // Contoh data baris 3
        $example = [
            'A' => 'Jatim Timur',           'B' => 'Siti Rahayu',  'C' => 'Sales',
            'D' => 'Aktif',       'E' => '2023-01-01',   'F' => '',
            'G' => date('Y'),
            'H' => '3500000',     'I' => '3500000',      'J' => '3500000',
            'K' => '3500000',     'L' => '3500000',      'M' => '3500000',
            'N' => '3500000',     'O' => '3500000',      'P' => '3500000',
            'Q' => '3500000',     'R' => '3500000',      'S' => '3500000',
        ];

        foreach ($example as $col => $val) {
            $sheet->setCellValue("{$col}3", $val);
        }

        $sheet->getStyle('A3:S3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '777777']],
        ]);

        foreach (array_keys($fields) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A3');

        // Sheet Petunjuk
        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Petunjuk');
        $rows = [
            1  => ['PETUNJUK PENGISIAN TEMPLATE IMPORT GAJI', true, 14],
            3  => ['1. Kolom bertanda (*) wajib diisi, kolom lain opsional.', false, 11],
            4  => ['2. Format tanggal: YYYY-MM-DD  (contoh: 2023-01-01)', false, 11],
            5  => ['3. Baris 1 (biru tua) = nama field untuk sistem. JANGAN diubah/dihapus.', false, 11],
            6  => ['4. Baris 2 (ungu) = label keterangan. JANGAN diubah/dihapus.', false, 11],
            7  => ['5. Isi data mulai baris ke-3.', false, 11],
            8  => ['6. Kolom gaji diisi angka murni tanpa titik/koma ribuan (contoh: 3500000)', false, 11],
            9  => ['7. Status: tulis "Aktif" atau "Resign"', false, 11],
            10 => ['8. Jika karyawan masih aktif, kolom tgl_resign dikosongkan.', false, 11],
            11 => ['9. Satu baris = satu karyawan untuk satu tahun.', false, 11],
            12 => ['10. Kolom gaji yang tidak diisi / dikosongkan akan disimpan sebagai 0.', false, 11],
        ];
        foreach ($rows as $r => [$txt, $bold, $size]) {
            $guide->setCellValue("A{$r}", $txt);
            $guide->getStyle("A{$r}")->getFont()->setBold($bold)->setSize($size);
        }
        $guide->getColumnDimension('A')->setWidth(80);

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Gaji.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ----------------------------------------------------------------
    // IMPORT GAJI DARI EXCEL
    // ----------------------------------------------------------------
    public function import()
    {
        // Hanya KADEP (lv=1) yang boleh import
        $file = $_FILES['file_excel'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File tidak ditemukan atau gagal diupload.');
            redirect('kmt/gaji');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls');
            redirect('kmt/gaji');
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file['tmp_name']);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file['tmp_name']);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);

            if (count($rows) < 3) {
                $this->session->set_flashdata('error', 'File kosong atau tidak memiliki data.');
                redirect('kmt/gaji');
            }

            $field_names  = array_map('trim', $rows[0]); // baris 1 = nama field
            $bulan_cols   = $this->bulan_cols;

            $wilayah_map = [];
            $wilayah_list = $this->M_Kmt->get_wilayah();

            foreach ($wilayah_list as $w) {
                $wilayah_map[strtolower(trim($w['nama_wilayah']))] = $w['id'];
            }


            $insert_batch = [];
            $errors       = [];
            $skipped      = 0;
            $inserted     = 0;

            for ($i = 2; $i < count($rows); $i++) {
                $row  = $rows[$i];
                $line = $i + 1;

                // Map field => nilai
                $data = [];
                foreach ($field_names as $col_idx => $field) {
                    $data[$field] = isset($row[$col_idx]) ? trim((string)$row[$col_idx]) : '';
                }

                // Skip baris kosong
                if (empty(array_filter($data))) {
                    $skipped++;
                    continue;
                }

                // ---- Validasi wajib ----
                $err = [];
                $wilayah_nama = strtolower(trim($data['wilayah'] ?? ''));

                if (empty($wilayah_nama)) {
                    $err[] = 'wilayah wajib diisi';
                } elseif (!isset($wilayah_map[$wilayah_nama])) {
                    $err[] = 'wilayah tidak dikenal';
                } 

                if (empty($data['nama']))                                             $err[] = 'nama wajib diisi';
                if (empty($data['tahun'])   || !is_numeric($data['tahun']))          $err[] = 'tahun wajib diisi (angka)';

                if (!empty($err)) {
                    $errors[] = "Baris {$line}: " . implode(', ', $err);
                    continue;
                }

                // Normalisasi tanggal
                $tgl_mulai  = !empty($data['tgl_mulai'])  ? $this->_parse_tanggal_gaji($data['tgl_mulai'])  : null;
                $tgl_resign = !empty($data['tgl_resign'])  ? $this->_parse_tanggal_gaji($data['tgl_resign']) : null;

                $record = [
                    'id_wilayah'  => $wilayah_map[$wilayah_nama] ?? null,
                    'nama'        => $data['nama'],
                    'posisi'      => $data['posisi']  ?: null,
                    'status'      => $data['status']  ?: null,
                    'tgl_mulai'   => $tgl_mulai,
                    'tgl_resign'  => $tgl_resign,
                    'tahun'       => (int)$data['tahun'],
                    'created_by'  => $this->session->userdata('id_user'),
                    'created_at'  => date('Y-m-d H:i:s'),
                ];

                // Kolom gaji per bulan
                foreach ($bulan_cols as $col) {
                    $val = $data[$col] ?? 0;
                    // Bersihkan pemisah ribuan jika ada (titik)
                    $val = str_replace(['.', ','], ['', '.'], (string)$val);
                    $record[$col] = is_numeric($val) ? (float)$val : 0;
                }

                $insert_batch[] = $record;
                $inserted++;
            }

            if (!empty($insert_batch)) {
                $this->M_Kmt->import_batch_gaji($insert_batch);
            }

            $msg = "Import selesai. <strong>{$inserted}</strong> karyawan berhasil diimpor.";
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

        redirect('kmt/gaji');
    }

    // ---- Helper tanggal khusus Gaji ----
    private function _parse_tanggal_gaji($str)
    {
        $str = trim($str);
        if (empty($str)) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) return $str;
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $str, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
        if (is_numeric($str)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$str);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        $ts = strtotime($str);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
