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

        $list             = $this->M_Kmt->get_promo_list($filter);
        $total_biaya      = array_sum(array_column($list, 'total_biaya'));
        $total_promo      = array_sum(array_column($list, 'promo_material'));
        $total_peralatan  = array_sum(array_column($list, 'peralatan'));

        $data = [
            'page_title'     => 'Promo Material / Peralatan',
            'list'           => $list,
            'total_biaya'    => $total_biaya,
            'total_promo'    => $total_promo,
            'total_peralatan'=> $total_peralatan,
            'wilayah_list'   => $this->M_Kmt->get_wilayah(),
            'tahun'          => $tahun,
            'bulan'          => $bulan,
            'id_wilayah'     => $id_wilayah,
            'nama_bulan'     => ['','Januari','Februari','Maret','April','Mei','Juni',
                                 'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'             => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/promo/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH
    // ----------------------------------------------------------------
    public function tambah() {
        $data = [
            'page_title'      => 'Tambah Promo Material / Peralatan',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
            'row'             => null,
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/promo/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // SIMPAN
    // ----------------------------------------------------------------
    public function simpan() {
        $this->form_validation->set_rules('tanggal',    'Tanggal',    'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah',    'required|integer');
        $this->form_validation->set_rules('nama_item',  'Nama Barang','required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl      = $this->input->post('tanggal');
        $promo    = (float)str_replace('.', '', $this->input->post('promo_material') ?? 0);
        $peralatan= (float)str_replace('.', '', $this->input->post('peralatan')      ?? 0);
        $total    = $promo + $peralatan;

        // Jika total diisi manual, gunakan itu
        $total_manual = (float)str_replace('.', '', $this->input->post('total_biaya') ?? 0);
        if ($total_manual > 0) $total = $total_manual;

        $insert = [
            'id_wilayah'     => (int)$this->input->post('id_wilayah'),
            'bulan'          => (int)date('m', strtotime($tgl)),
            'tahun'          => (int)date('Y', strtotime($tgl)),
            'tanggal'        => $tgl,
            'supplier'       => $this->input->post('supplier')  ?: null,
            'nama_item'      => $this->input->post('nama_item'),
            'total_biaya'    => $total,
            'promo_material' => $promo,
            'peralatan'      => $peralatan,
            'keterangan'     => $this->input->post('keterangan') ?: null,
            'created_by'     => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_promo($insert)) {
            $this->session->set_flashdata('success', 'Data berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/promo');
    }

    // ----------------------------------------------------------------
    // EDIT
    // ----------------------------------------------------------------
    public function edit($id) {
        $row = $this->M_Kmt->get_promo_by_id($id);
        if (!$row) { show_404(); return; }

        $data = [
            'page_title'      => 'Edit Promo Material / Peralatan',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/promo/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // UPDATE
    // ----------------------------------------------------------------
    public function update($id) {
        $tgl       = $this->input->post('tanggal');
        $promo     = (float)str_replace('.', '', $this->input->post('promo_material') ?? 0);
        $peralatan = (float)str_replace('.', '', $this->input->post('peralatan')      ?? 0);
        $total     = $promo + $peralatan;

        $total_manual = (float)str_replace('.', '', $this->input->post('total_biaya') ?? 0);
        if ($total_manual > 0) $total = $total_manual;

        $update = [
            'id_wilayah'     => (int)$this->input->post('id_wilayah'),
            'bulan'          => (int)date('m', strtotime($tgl)),
            'tahun'          => (int)date('Y', strtotime($tgl)),
            'tanggal'        => $tgl,
            'supplier'       => $this->input->post('supplier')  ?: null,
            'nama_item'      => $this->input->post('nama_item'),
            'total_biaya'    => $total,
            'promo_material' => $promo,
            'peralatan'      => $peralatan,
            'keterangan'     => $this->input->post('keterangan') ?: null,
        ];

        if ($this->M_Kmt->update_promo($id, $update)) {
            $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/promo');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        if ($this->M_Kmt->delete_promo($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/promo');
    }

    // ----------------------------------------------------------------
    // EXPORT — format persis seperti file Excel perusahaan
    // ----------------------------------------------------------------
    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_promo_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PROMO MATERIAL');

        // ── Baris 1: Judul ──
        $sheet->setCellValue('A1', 'BIAYA PROMO & PERALATAN — ' . $tahun
            . ($bulan ? ' Bulan ' . $bulan : ''));
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Baris 2: Header ──
        $headers = ['No', 'Tgl TF', 'Wilayah', 'Supplier', 'Nama Barang',
                    'Total', 'Promo Material', 'Peralatan'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 2, $h);
        }
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C55A11']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['rgb' => 'FFFFFF'],
            ]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ── Data mulai baris 3 ──
        $total_biaya     = 0;
        $total_promo     = 0;
        $total_peralatan = 0;

        foreach ($list as $i => $row) {
            $r = $i + 3;

            $sheet->setCellValueByColumnAndRow(1, $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $r, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValueByColumnAndRow(3, $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $r, $row['supplier']     ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $r, $row['nama_item']);
            $sheet->setCellValueByColumnAndRow(6, $r, (float)$row['total_biaya']);
            $sheet->setCellValueByColumnAndRow(7, $r, (float)$row['promo_material']);
            $sheet->setCellValueByColumnAndRow(8, $r, (float)$row['peralatan']);

            $total_biaya     += (float)$row['total_biaya'];
            $total_promo     += (float)$row['promo_material'];
            $total_peralatan += (float)$row['peralatan'];

            // Zebra stripe
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:H{$r}")->getFill()
                      ->setFillType('solid')->getStartColor()->setRGB('FEF3EC');
            }
            $sheet->getStyle("A{$r}:H{$r}")->getBorders()
                  ->getAllBorders()
                  ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                  ->getColor()->setRGB('E0E0E0');
        }

        // ── Baris Grand Total ──
        $total_row = count($list) + 3;
        $sheet->setCellValueByColumnAndRow(4, $total_row, 'Grand Total');
        $sheet->setCellValueByColumnAndRow(6, $total_row, $total_biaya);
        $sheet->setCellValueByColumnAndRow(7, $total_row, $total_promo);
        $sheet->setCellValueByColumnAndRow(8, $total_row, $total_peralatan);

        $sheet->getStyle("D{$total_row}:H{$total_row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE4D6']],
        ]);
        $sheet->getStyleByColumnAndRow(4, $total_row)
              ->getAlignment()->setHorizontal('right');

        // ── Format angka untuk kolom F, G, H ──
        $last_data_row = count($list) + 2;
        if (count($list) > 0) {
            foreach (['F', 'G', 'H'] as $col) {
                $sheet->getStyle("{$col}3:{$col}{$total_row}")
                      ->getNumberFormat()->setFormatCode('#,##0');
            }
        }

        // ── Auto width & freeze ──
        foreach (['A','B','C','D','E','F','G','H'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A3');

        $nama_bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $suffix     = $bulan ? '_' . $nama_bulan[(int)$bulan] : '';
        $filename   = "Promo_Material_KMT_{$tahun}{$suffix}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ----------------------------------------------------------------
    // IMPORT — dari file Excel format perusahaan
    // ----------------------------------------------------------------
    public function import() {
        $file = $_FILES['file_excel'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('kmt/promo');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format harus .xlsx atau .xls');
            redirect('kmt/promo');
        }

        $tmp_dir  = FCPATH . 'assets/uploads/tmp/';
        if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
        $tmp_path = $tmp_dir . uniqid('promo_') . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $tmp_path)) {
            $this->session->set_flashdata('error', 'Gagal memindahkan file.');
            redirect('kmt/promo');
        }

        try {
            $result = $this->_baca_excel_promo($tmp_path, $ext);

            $inserted  = 0;
            $db_errors = [];
            if (!empty($result['data'])) {
                $default = [
                    'id_wilayah' => null, 'bulan' => null, 'tahun' => null,
                    'tanggal' => null, 'supplier' => null, 'nama_item' => null,
                    'total_biaya' => 0, 'promo_material' => 0, 'peralatan' => 0,
                    'keterangan' => null, 'created_by' => null, 'created_at' => null,
                ];
                $normalized = array_map(fn($r) => array_merge($default, $r), $result['data']);

                foreach (array_chunk($normalized, 100) as $chunk) {
                    if ($this->db->insert_batch('tbkmt_promo_material', $chunk)) {
                        $inserted += count($chunk);
                    } else {
                        foreach ($chunk as $row) {
                            if ($this->db->insert('tbkmt_promo_material', $row)) {
                                $inserted++;
                            } else {
                                $db_errors[] = 'DB error: ' . $this->db->error()['message'];
                            }
                        }
                    }
                }
            }

            $all_errors = array_merge($result['errors'], $db_errors);
            $msg = "Import selesai. <strong>{$inserted}</strong> data berhasil diimpor.";
            if ($result['skipped'] > 0) $msg .= " <strong>{$result['skipped']}</strong> baris dilewati.";
            if (!empty($all_errors)) {
                $msg .= '<br><ul>' . implode('', array_map(fn($e) => "<li>{$e}</li>", $all_errors)) . '</ul>';
                $this->session->set_flashdata('warning', $msg);
            } else {
                $this->session->set_flashdata('success', $msg);
            }

        } catch (\Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file: ' . $e->getMessage());
        } finally {
            if (file_exists($tmp_path)) @unlink($tmp_path);
        }

        redirect('kmt/promo');
    }

    // ================================================================
    // READER — baca file Excel format perusahaan
    // Struktur: Baris 1=Judul, Baris 2=Header, Data baris 3+
    // Kolom: [0]No [1]Tgl TF [2]Supplier [3]Nama Barang
    //        [4]Total [5]Promo Material [6]Peralatan
    // PERHATIAN: ada baris "Total Per Wilayah" di antara data → skip
    // ================================================================
    private function _baca_excel_promo(string $path, string $ext): array
    {
        $filter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            public function readCell($col, $row, $ws = ''): bool {
                return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col) <= 10;
            }
        };

        $reader = ($ext === 'xls')
            ? new \PhpOffice\PhpSpreadsheet\Reader\Xls()
            : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadFilter($filter);
        $reader->setReadDataOnly(false);

        try {
            $reader->setLoadSheetsOnly(['PROMO MATERIAL']);
            $spreadsheet = $reader->load($path);
        } catch (\Exception $e) {
            $r2 = ($ext === 'xls')
                ? new \PhpOffice\PhpSpreadsheet\Reader\Xls()
                : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $r2->setReadFilter($filter);
            $r2->setReadDataOnly(false);
            $spreadsheet = $r2->load($path);
        }

        $sheet = $spreadsheet->getActiveSheet();

        // Ambil id_wilayah dari GET (user memilih wilayah saat import)
        $id_wilayah = (int)($this->input->get('id_wilayah') ?: $this->get_id_wilayah_filter() ?: 1);

        $insert_batch = [];
        $errors       = [];
        $skipped      = 0;

        foreach ($sheet->getRowIterator(3) as $rowObj) {
            $line  = $rowObj->getRowIndex();
            $cells = [];
            foreach ($rowObj->getCellIterator('A', 'J') as $cell) {
                $cells[] = $cell->getValue();
            }
            $cells = array_pad(array_slice($cells, 0, 10), 10, null);

            // Skip baris kosong atau baris "Total Per Wilayah" / "Grand Total"
            $col3_str = strtolower(trim($this->_s($cells[3])));
            if (strpos($col3_str, 'total') !== false || strpos($col3_str, 'grand') !== false) {
                $skipped++; continue;
            }

            $meaningful = array_filter([$cells[1], $cells[3], $cells[4]],
                fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($meaningful)) { $skipped++; continue; }

            // Parse kolom
            $tanggal       = $this->_parse_excel_val($cells[1]);
            $supplier      = trim($this->_s($cells[2]));
            $nama_item     = trim($this->_s($cells[3]));
            $total_biaya   = is_numeric($cells[4]) ? (float)$cells[4] : 0;
            $promo_material= is_numeric($cells[5]) ? (float)$cells[5] : 0;
            $peralatan     = is_numeric($cells[6]) ? (float)$cells[6] : 0;

            // Validasi
            if (!$tanggal) { $errors[] = "Baris {$line}: tanggal tidak valid"; continue; }
            if (empty($nama_item)) { $errors[] = "Baris {$line}: nama barang kosong"; continue; }

            $insert_batch[] = [
                'id_wilayah'     => $id_wilayah,
                'bulan'          => (int)date('m', strtotime($tanggal)),
                'tahun'          => (int)date('Y', strtotime($tanggal)),
                'tanggal'        => $tanggal,
                'supplier'       => $supplier  ?: null,
                'nama_item'      => $nama_item,
                'total_biaya'    => $total_biaya,
                'promo_material' => $promo_material,
                'peralatan'      => $peralatan,
                'keterangan'     => null,
                'created_by'     => $this->session->userdata('id_user'),
                'created_at'     => date('Y-m-d H:i:s'),
            ];
        }

        return ['data' => $insert_batch, 'errors' => $errors, 'skipped' => $skipped];
    }

    // ── Helpers ──
    private function _parse_excel_val($val): ?string {
        if ($val === null || $val === '') return null;
        if ($val instanceof \DateTime) return $val->format('Y-m-d');
        if (is_float($val) || (is_int($val) && $val > 10000 && $val < 100000)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val)->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        $str = trim((string)$val);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) return $str;
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $m))
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        $ts = strtotime($str);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function _s($val): string {
        if ($val === null) return '';
        if ($val instanceof \DateTime) return $val->format('Y-m-d');
        return trim((string)$val);
    }
}