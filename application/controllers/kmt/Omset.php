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
            'kontak_person'    => $this->input->post('kontak_person') ?: null,
            'alamat'           => $this->input->post('alamat')        ?: null,
            'golongan'         => $this->input->post('golongan')      ?: null,
            'point'            => (float)$this->input->post('point'),
            'fokus'            => $this->input->post('fokus')         ?: null,
            'kode_produk'      => $this->input->post('kode_produk')   ?: null,
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
            'kontak_person'    => $this->input->post('kontak_person') ?: null,
            'alamat'           => $this->input->post('alamat')        ?: null,
            'golongan'         => $this->input->post('golongan')      ?: null,
            'point'            => (float)$this->input->post('point'),
            'fokus'            => $this->input->post('fokus')         ?: null,
            'kode_produk'      => $this->input->post('kode_produk')   ?: null,
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

        $tgl           = $this->input->post('tanggal_retur');
        $nilai_retur   = (float)str_replace('.', '', $this->input->post('nilai_retur') ?? 0);
        $kurangi_target = (int)$this->input->post('kurangi_target');

        $insert = [
            'id_omset'       => $id_omset,
            'id_wilayah'     => $omset['id_wilayah'],
            'bulan'          => (int)date('m', strtotime($tgl)),
            'tahun'          => (int)date('Y', strtotime($tgl)),
            'tanggal_retur'  => $tgl,
            'no_retur'       => $this->input->post('no_retur'),
            'nama_toko'      => $omset['nama_toko'],
            'produk'         => $omset['produk'],
            'sc'             => $omset['sc'],           // ← field baru dari omset
            'kota'           => $omset['kota'],         // ← field baru
            'harga_dpp'      => $omset['harga_inc_ppn'], // ← ambil dari omset
            'quantity'       => (float)$this->input->post('quantity'),
            'unit'           => $this->input->post('unit'),
            'nilai_retur'    => $nilai_retur,
            'kurangi_target' => $kurangi_target,
            'kategori'       => $this->input->post('kategori'),
            'keterangan'     => $this->input->post('keterangan'),
            'keterangan_detail' => $this->input->post('keterangan_detail'),
            'created_by'     => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_retur($insert)) {
            // ← BARU: jika kurangi target, update nilai omset
            if ($kurangi_target === 1) {
                $this->M_Kmt->adjust_omset_nilai($id_omset, $nilai_retur, true);
            }
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

        // ← BARU: kembalikan nilai omset jika retur ini tadinya kurangi target
        if ($retur && $retur['kurangi_target'] == 1 && $id_omset) {
            $this->M_Kmt->adjust_omset_nilai($id_omset, (float)$retur['nilai_retur'], false);
        }

        if ($this->M_Kmt->delete_retur($id)) {
            $this->session->set_flashdata('success', 'Retur berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus retur.');
        }
        redirect($id_omset ? 'kmt/omset/retur/' . $id_omset : 'kmt/omset');
    }

    public function export()
    {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();
 
        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;
 
        $list = $this->M_Kmt->get_omset_list($filter);
 
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Omset');
 
        // ── Definisi kolom: [header label => key dari $row] ──
        $columns = [
            'No'                  => null,           // nomor urut manual
            'No Urut'             => 'no_urut',
            'Kode'                => 'kode',
            'Tanggal'             => 'tanggal',
            'Bulan'               => 'bulan',
            'Tahun'               => 'tahun',
            'Nomor Faktur'        => 'nomor',
            'Inputer'             => 'inputer',
            'No Retur'            => 'no_retur',
            'Tgl Retur'           => 'tgl_retur',
            'Sales SO'            => 'sales_so',
            'SC'                  => 'sc',
            'SE'                  => 'se',
            'Wilayah SE'          => 'wilayah_se',
            'Wilayah'             => 'nama_wilayah',
            'Nama Toko'           => 'nama_toko',
            'Kontak Person'       => 'kontak_person',
            'Alamat'              => 'alamat',
            'Kota'                => 'kota',
            'Merk'                => 'merk',
            'Jenis'               => 'jenis',
            'Golongan'            => 'golongan',
            'Point'               => 'point',
            'Fokus'               => 'fokus',
            'Kode Produk'         => 'kode_produk',
            'Produk'              => 'produk',
            'Quantity'            => 'quantity',
            'Unit'                => 'unit',
            'Box'                 => 'box',
            'Ltr/Kg'              => 'ltr_kg',
            'Harga Inc. PPN'      => 'harga_inc_ppn',
            'Penj DPP Neto'       => 'penj_dpp_neto',
            'Penj Inc PPN Neto'   => 'penj_inc_ppn_neto',
            'Keterangan'          => 'keterangan',
            'Tgl Kirim'           => 'tgl_kirim',
        ];
 
        $headers    = array_keys($columns);
        $field_keys = array_values($columns);
        $total_col  = count($headers);
 
        // ── Tulis header baris 1 ──
        foreach ($headers as $i => $label) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $label);
        }
 
        // ── Style header ──
        $last_col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_col);
        $sheet->getStyle("A1:{$last_col_letter}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => 'solid',
                'startColor' => ['rgb' => '1F3864'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);
 
        // ── Tulis data ──
        $total_omset = 0;
 
        foreach ($list as $i => $row) {
            $r = $i + 2;
 
            foreach ($field_keys as $col_idx => $key) {
                $col_num = $col_idx + 1;
 
                if ($key === null) {
                    // Kolom "No" → nomor urut
                    $sheet->setCellValueByColumnAndRow($col_num, $r, $i + 1);
                    continue;
                }
 
                $val = $row[$key] ?? null;
 
                // Format tanggal
                if (in_array($key, ['tanggal', 'tgl_retur', 'tgl_kirim']) && !empty($val)) {
                    $val = date('d/m/Y', strtotime($val));
                }
 
                $sheet->setCellValueByColumnAndRow($col_num, $r, $val);
            }
 
            $total_omset += (float)($row['penj_inc_ppn_neto'] ?? 0);
        }
 
        // ── Baris TOTAL di bawah data ──
        $total_row = count($list) + 2;
 
        // Cari index kolom Penj Inc PPN Neto
        $neto_col_idx   = array_search('penj_inc_ppn_neto', $field_keys);
        $neto_col_num   = $neto_col_idx + 1;
        $neto_col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($neto_col_num);
 
        // Label TOTAL
        $sheet->setCellValueByColumnAndRow($neto_col_num - 1, $total_row, 'TOTAL:');
        $sheet->getStyleByColumnAndRow($neto_col_num - 1, $total_row)
              ->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow($neto_col_num - 1, $total_row)
              ->getAlignment()->setHorizontal('right');
 
        // Nilai total
        $sheet->setCellValueByColumnAndRow($neto_col_num, $total_row, $total_omset);
        $sheet->getStyleByColumnAndRow($neto_col_num, $total_row)
              ->getFont()->setBold(true);
 
        // ── Format angka untuk kolom numerik ──
        $numeric_keys = ['quantity','box','ltr_kg','harga_inc_ppn','penj_dpp_neto','penj_inc_ppn_neto','point'];
        foreach ($field_keys as $col_idx => $key) {
            if (!in_array($key, $numeric_keys)) continue;
            $col_num = $col_idx + 1;
            $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_num);
 
            // Format ribuan untuk kolom uang
            if (in_array($key, ['harga_inc_ppn','penj_dpp_neto','penj_inc_ppn_neto'])) {
                $fmt = '#,##0';
            } else {
                $fmt = '#,##0.##';
            }
 
            if (count($list) > 0) {
                $sheet->getStyle("{$col_letter}2:{$col_letter}" . (count($list) + 1))
                      ->getNumberFormat()->setFormatCode($fmt);
            }
            // Format total row juga
            $sheet->getStyleByColumnAndRow($col_num, $total_row)
                  ->getNumberFormat()->setFormatCode($fmt);
        }
 
        // ── Style zebra stripe (baris genap sedikit berbeda) ──
        for ($r = 2; $r <= count($list) + 1; $r++) {
            $style_arr = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color'       => ['rgb' => 'D0D0D0'],
                    ],
                ],
            ];
            if ($r % 2 === 0) {
                $style_arr['fill'] = [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => 'F5F8FF'],
                ];
            }
            $sheet->getStyle("A{$r}:{$last_col_letter}{$r}")->applyFromArray($style_arr);
        }
 
        // ── Auto width semua kolom ──
        foreach (range(1, $total_col) as $col_num) {
            $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_num);
            $sheet->getColumnDimension($col_letter)->setAutoSize(true);
        }
 
        // ── Freeze header ──
        $sheet->freezePane('A2');
 
        // ── Nama file ──
        $nama_bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $suffix_bulan = $bulan ? '_' . $nama_bulan[(int)$bulan] : '';
        $filename = "Omset_KMT_{$tahun}{$suffix_bulan}.xlsx";
 
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
            'Z'  => 'kontak_person','AA' => 'alamat',       'AB' => 'golongan',
            'AC' => 'point',        'AD' => 'fokus',        'AE' => 'kode_produk',
        ];
 
        // ------- Baris 2: label ramah untuk pengguna -------
        $labels = [
            'A' => 'Tanggal (YYYY-MM-DD)*', 'B' => 'ID Wilayah*',      'C' => 'Nama Toko*',
            'D' => 'Kota',                  'E' => 'Produk*',          'F' => 'Sales SO',
            'G' => 'Quantity*',             'H' => 'Unit',             'I' => 'Harga Inc PPN',
            'J' => 'Penj DPP Neto',         'K' => 'Penj Inc PPN Neto*',
            'L' => 'Nomor Faktur',          'M' => 'No Urut',          'N' => 'Kode',
            'O' => 'SC',                    'P' => 'SE',               'Q' => 'Wilayah SE',
            'R' => 'Merk',                  'S' => 'Jenis',            'T' => 'Box',
            'U' => 'Ltr/Kg',                'V' => 'Keterangan',
            'W' => 'Tgl Kirim (YYYY-MM-DD)','X' => 'No Retur',         'Y' => 'Tgl Retur (YYYY-MM-DD)',
            'Z' => 'Kontak Person',         'AA' => 'Alamat Toko',     'AB' => 'Golongan',
            'AC' => 'Point',                'AD' => 'Fokus',           'AE' => 'Kode Produk',
        ];
 
        foreach ($fields as $col => $field) {
            $sheet->setCellValue("{$col}1", $field);
            $sheet->setCellValue("{$col}2", $labels[$col]);
        }
 
        // Style baris 1 (biru tua)
        $sheet->getStyle('A1:AE1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);
 
        // Style baris 2 (tanpa warna)
        $sheet->getStyle('A2:Y2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'wrapText' => true],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(30);
 
        // Contoh data baris 3
        $example = [
            'A' => date('Y-m-d'), 'B' => '1',               'C' => 'Toko Maju Jaya',
            'D' => 'Semarang',    'E' => 'CORN A 1KG',      'F' => 'Budi Santoso',
            'G' => '10',          'H' => 'SAK',             'I' => '55000',
            'J' => '500000',      'K' => '550000',          'L' => 'INV/2025/001',
            'M' => '1',           'N' => 'KMT-001',         'O' => '',
            'P' => '',            'Q' => '',                'R' => 'KARISMA',
            'S' => 'CORN',        'T' => '2',               'U' => '10',
            'V' => 'Contoh data - hapus baris ini sebelum import',
            'W' => '',            'X' => '',                'Y' => '',
            'Z'  => '',           'AA' => 'Jl. Contoh No. 1, Semarang',
            'AB' => 'A',          'AC' => '0',             'AD' => 'CORN',
            'AE' => 'PRD-001',
        ];
 
        foreach ($example as $col => $val) {
            $sheet->setCellValue("{$col}3", $val);
        }
 
        $sheet->getStyle('A3:Y3')->applyFromArray([
            'font' => ['italic' => true],
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
 
        // Salin file ke folder sementara yang bisa diakses
        $tmp_dir  = FCPATH . 'assets/uploads/tmp/';
        if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
        $tmp_path = $tmp_dir . uniqid('omset_') . '.' . $ext;
 
        if (!move_uploaded_file($file['tmp_name'], $tmp_path)) {
            $this->session->set_flashdata('error', 'Gagal memindahkan file upload.');
            redirect('kmt/omset');
        }
 
        try {
            $result = $this->_baca_excel_omset($tmp_path, $ext);
 
            if (!empty($result['data'])) {
                $this->M_Kmt->import_batch_omset($result['data']);
            }
 
            $inserted = count($result['data']);
            $msg = "Import selesai (<em>{$result['format']}</em>). <strong>{$inserted}</strong> data berhasil diimpor.";
            if ($result['skipped'] > 0) $msg .= " <strong>{$result['skipped']}</strong> baris kosong dilewati.";
 
            if (!empty($result['errors'])) {
                $msg .= "<br><strong>" . count($result['errors']) . " baris gagal/warning:</strong><ul>";
                foreach ($result['errors'] as $e) $msg .= "<li>{$e}</li>";
                $msg .= "</ul>";
                $this->session->set_flashdata('warning', $msg);
            } else {
                $this->session->set_flashdata('success', $msg);
            }
 
        } catch (\Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file: ' . $e->getMessage());
        } finally {
            // Hapus file sementara
            if (file_exists($tmp_path)) @unlink($tmp_path);
        }
 
        redirect('kmt/omset');
    }
 
    // ================================================================
    // Core reader — pakai ReadFilter untuk hemat memori
    // ================================================================
    private function _baca_excel_omset(string $path, string $ext): array
    {
        // ---- ReadFilter: hanya kolom A s/d AJ (kolom 1-36) ----
        $filter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            public function readCell($columnAddress, $row, $worksheetName = '') {
                // Izinkan semua baris, tapi kolom hanya s/d AJ (kolom ke-36)
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnAddress);
                return $col <= 36;
            }
        };
 
        if ($ext === 'xls') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
 
        $reader->setReadFilter($filter);
        $reader->setReadDataOnly(false); // false agar tanggal terbaca
        $reader->setLoadSheetsOnly(['OMSET']); // hanya load sheet OMSET kalau ada
 
        try {
            $spreadsheet = $reader->load($path);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            // Kalau sheet OMSET tidak ada, load semua sheet lalu ambil aktif
            if ($ext === 'xls') {
                $reader2 = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $reader2 = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $reader2->setReadFilter($filter);
            $reader2->setReadDataOnly(false);
            $spreadsheet = $reader2->load($path);
        }
 
        $sheet = $spreadsheet->getActiveSheet();
 
        // Baca baris 1 dan 2 untuk deteksi format
        $row1_vals = [];
        $row2_vals = [];
        foreach ($sheet->getRowIterator(1, 2) as $rowIdx => $rowObj) {
            $cells = [];
            foreach ($rowObj->getCellIterator('A', 'AJ') as $cell) {
                $cells[] = $cell->getFormattedValue();
            }
            if ($rowIdx === 1) $row1_vals = $cells;
            if ($rowIdx === 2) $row2_vals = $cells;
        }
 
        // Deteksi format: template sistem atau file perusahaan
        $is_template = !empty($row1_vals)
            && in_array('tanggal', array_map('strtolower', array_map('trim', $row1_vals)));
 
        if ($is_template) {
            return $this->_proses_format_template($sheet, $row1_vals);
        } else {
            return $this->_proses_format_perusahaan($sheet);
        }
    }
 
    // ================================================================
    // FORMAT A — Template sistem (field names di baris 1)
    // ================================================================
    private function _proses_format_template($sheet, array $field_names): array
    {
        $insert_batch = [];
        $errors       = [];
        $skipped      = 0;
 
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
 
            $err = [];
            if (empty($data['tanggal']))    $err[] = 'tanggal wajib';
            if (empty($data['id_wilayah'])) $err[] = 'id_wilayah wajib';
            if (empty($data['nama_toko']))  $err[] = 'nama_toko wajib';
            if (empty($data['produk']))     $err[] = 'produk wajib';
            if (!empty($err)) { $errors[] = "Baris {$line}: " . implode(', ', $err); continue; }
 
            $tanggal   = $this->_parse_tgl($data['tanggal']);
            $tgl_kirim = $this->_parse_tgl($data['tgl_kirim'] ?? '');
            $tgl_retur = $this->_parse_tgl($data['tgl_retur'] ?? '');
            if (!$tanggal) { $errors[] = "Baris {$line}: format tanggal tidak valid"; continue; }
 
            $insert_batch[] = $this->_build_row_template($data, $tanggal, $tgl_kirim, $tgl_retur);
        }
 
        return ['data' => $insert_batch, 'errors' => $errors, 'skipped' => $skipped, 'format' => 'Template Sistem'];
    }
 
    // ================================================================
    // FORMAT B — File Excel asli perusahaan
    //
    // Pemetaan kolom (0-based) dari header row 2:
    //  0=No Urut  1=Kode     4=Tanggal       5=Nomor    6=Inputer
    //  7=No Retur 8=Tgl Retur 9=Sales SO     10=SC      11=SE
    //  12=Wilayah SE  13=Wilayah ABM  15=Nama Toko  18=Kota
    //  19=Merk    20=Jenis   26=Nama Barang  27=Quantity 28=Unit
    //  29=Box     30=Ltr/Kg  31=Harga        32=Penj DPP 33=Penj Inc PPN
    //  34=Keterangan  35=Tgl Kirim
    // ================================================================
    private function _proses_format_perusahaan($sheet): array
    {
        $wilayah_list = $this->M_Kmt->get_wilayah();
        $wilayah_map  = [];
        foreach ($wilayah_list as $w) {
            $wilayah_map[strtoupper(trim($w['nama_wilayah']))] = (int)$w['id'];
        }
 
        $insert_batch = [];
        $errors       = [];
        $skipped      = 0;
 
        foreach ($sheet->getRowIterator(3) as $rowObj) {
            $line  = $rowObj->getRowIndex();
            $cells = [];
            foreach ($rowObj->getCellIterator('A', 'AJ') as $cell) {
                $cells[] = $cell->getValue();
            }
            // Pad sampai 36 kolom agar index aman
            $cells = array_pad(array_slice($cells, 0, 36), 36, null);
 
            // Skip baris kosong
            $meaningful = array_filter(
                array_slice($cells, 0, 36),
                fn($v) => $v !== null && trim((string)$v) !== ''
            );
            if (empty($meaningful)) { $skipped++; continue; }
 
            // ── Ambil nilai tiap kolom (index 0-based sesuai tabel di atas) ──
            $no_urut        = $this->_s($cells[0]);
            $kode           = $this->_s($cells[1]);
            // $cells[2] = No, $cells[3] = Bulan — tidak dipakai (dihitung ulang)
            $tanggal        = $this->_parse_excel_val($cells[4]);
            $nomor          = $this->_s($cells[5]);
            $inputer        = $this->_s($cells[6]);
            $no_retur       = $this->_s($cells[7]);
            $tgl_retur      = $this->_parse_excel_val($cells[8]);
            $sales_so       = $this->_s($cells[9]);
            $sc             = $this->_s($cells[10]);
            $se             = $this->_s($cells[11]);
            $wilayah_se     = $this->_s($cells[12]);
            $wilayah_abm    = strtoupper(trim($this->_s($cells[13])));
            // $cells[14] = Kode toko
            $nama_toko      = $this->_s($cells[15]);
            $kontak_person  = $this->_s($cells[16]);   // ✅ FIX: was cells[36]
            $alamat         = $this->_s($cells[17]);   // ✅ FIX: was cells[37]
            $kota           = $this->_s($cells[18]);
            $merk           = $this->_s($cells[19]);
            $jenis          = $this->_s($cells[20]);
            $golongan       = $this->_s($cells[21]);   // ✅ FIX: was cells[38] — kolom "Gol"
            // $cells[22] = Prod (kategori produk)
            $point          = is_numeric($cells[23]) ? (float)$cells[23] : 0; // ✅ FIX: was cells[39]
            $fokus          = $this->_s($cells[24]);   // ✅ FIX: was cells[40]
            $kode_produk    = $this->_s($cells[25]);   // ✅ FIX: was cells[41] — kolom "Kode" barang
            $produk         = $this->_s($cells[26]);   // Nama Barang
            $quantity       = is_numeric($cells[27]) ? (float)$cells[27] : 0;
            $unit           = $this->_s($cells[28]);
            $box            = is_numeric($cells[29]) ? (float)$cells[29] : 0;
            $ltr_kg         = is_numeric($cells[30]) ? (float)$cells[30] : 0;
            $harga          = is_numeric($cells[31]) ? (float)$cells[31] : 0;
            $penj_dpp       = is_numeric($cells[32]) ? (float)$cells[32] : 0;
            $penj_neto      = is_numeric($cells[33]) ? (float)$cells[33] : 0;
            $keterangan     = $this->_s($cells[34]);
            $tgl_kirim      = $this->_parse_excel_val($cells[35]);
 
            // ── Validasi ──
            $err = [];
            if (!$tanggal)         $err[] = 'tanggal tidak valid';
            if (empty($nama_toko)) $err[] = 'nama_toko kosong';
            if (empty($produk))    $err[] = 'produk/nama_barang kosong';
            if (!empty($err)) { $errors[] = "Baris {$line}: " . implode(', ', $err); continue; }
 
            // ── Mapping wilayah (exact match dulu, lalu partial) ──
            $id_wilayah = $wilayah_map[$wilayah_abm] ?? null;
            if (!$id_wilayah) {
                foreach ($wilayah_map as $nm => $id) {
                    if (strpos($wilayah_abm, $nm) !== false || strpos($nm, $wilayah_abm) !== false) {
                        $id_wilayah = $id; break;
                    }
                }
            }
            if (!$id_wilayah) {
                $id_wilayah = !empty($wilayah_list) ? (int)$wilayah_list[0]['id'] : 1;
                $errors[]   = "Baris {$line}: wilayah '{$wilayah_abm}' tidak dikenali → diassign ke id_wilayah={$id_wilayah}";
            }
 
            $insert_batch[] = [
                'no_urut'           => $no_urut        ?: null,
                'kode'              => $kode           ?: null,
                'bulan'             => (int)date('m', strtotime($tanggal)),
                'tahun'             => (int)date('Y', strtotime($tanggal)),
                'tanggal'           => $tanggal,
                'nomor'             => $nomor          ?: null,
                'inputer'           => $inputer        ?: $this->session->userdata('nama'),
                'no_retur'          => $no_retur       ?: null,
                'tgl_retur'         => $tgl_retur,
                'sales_so'          => $sales_so       ?: null,
                'sc'                => $sc             ?: null,
                'se'                => $se             ?: null,
                'wilayah_se'        => $wilayah_se     ?: null,
                'id_wilayah'        => $id_wilayah,
                'nama_toko'         => $nama_toko,
                'kontak_person'     => $kontak_person  ?: null,  // ✅ index [16]
                'alamat'            => $alamat         ?: null,  // ✅ index [17]
                'kota'              => $kota           ?: null,
                'merk'              => $merk           ?: null,
                'jenis'             => $jenis          ?: null,
                'golongan'          => $golongan       ?: null,  // ✅ index [21]
                'point'             => $point,                   // ✅ index [23]
                'fokus'             => $fokus          ?: null,  // ✅ index [24]
                'kode_produk'       => $kode_produk    ?: null,  // ✅ index [25]
                'produk'            => $produk,
                'quantity'          => $quantity,
                'unit'              => $unit           ?: null,
                'box'               => $box,
                'ltr_kg'            => $ltr_kg,
                'harga_inc_ppn'     => $harga,
                'penj_dpp_neto'     => $penj_dpp,
                'penj_inc_ppn_neto' => $penj_neto,
                'keterangan'        => $keterangan     ?: null,
                'tgl_kirim'         => $tgl_kirim,
                'created_by'        => $this->session->userdata('id_user'),
                'created_at'        => date('Y-m-d H:i:s'),
            ];
        }
 
        return [
            'data'    => $insert_batch,
            'errors'  => $errors,
            'skipped' => $skipped,
            'format'  => 'File Excel Perusahaan',
        ];
    }
 
    // ================================================================
    // HELPERS
    // ================================================================
 
    /** Parse nilai sel Excel jadi string tanggal Y-m-d */
    private function _parse_excel_val($val): ?string
    {
        if ($val === null || $val === '') return null;
 
        // DateTime object (dari PhpSpreadsheet)
        if ($val instanceof \DateTime) return $val->format('Y-m-d');
 
        // Serial number Excel (float)
        if (is_float($val) || (is_int($val) && $val > 10000 && $val < 100000)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {}
        }
 
        return $this->_parse_tgl((string)$val);
    }
 
    /** Parse string tanggal berbagai format → Y-m-d */
    private function _parse_tgl(string $str): ?string
    {
        $str = trim($str);
        if (empty($str)) return null;
 
        // YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) return $str;
 
        // DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
 
        $ts = strtotime($str);
        return $ts ? date('Y-m-d', $ts) : null;
    }
 
    /** Safe string dari nilai sel (null-safe) */
    private function _s($val): string
    {
        if ($val === null) return '';
        if ($val instanceof \DateTime) return $val->format('Y-m-d');
        return trim((string)$val);
    }
 
    private function _build_row_template(array $data, string $tanggal, ?string $tgl_kirim, ?string $tgl_retur): array
    {
        return [
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
            'id_wilayah'        => (int)($data['id_wilayah'] ?? 1),
            'nama_toko'         => $data['nama_toko'],
            'kota'              => $data['kota']       ?: null,
            'merk'              => $data['merk']       ?: null,
            'jenis'             => $data['jenis']      ?: null,
            'produk'            => $data['produk'],
            'kontak_person'     => $data['kontak_person'] ?: null, //
            'alamat'            => $data['alamat']        ?: null,
            'golongan'          => $data['golongan']      ?: null,  
            'point'             => (float)($data['point'] ?? 0),
            'fokus'             => $data['fokus']         ?: null,
            'kode_produk'       => $data['kode_produk']   ?: null,
            'quantity'          => (float)str_replace(',', '.', $data['quantity'] ?? 0),
            'unit'              => $data['unit']       ?: null,
            'box'               => (float)($data['box']    ?? 0),
            'ltr_kg'            => (float)($data['ltr_kg'] ?? 0),
            'harga_inc_ppn'     => (float)str_replace(['.', ','], ['', '.'], $data['harga_inc_ppn'] ?? 0),
            'penj_dpp_neto'     => (float)str_replace(['.', ','], ['', '.'], $data['penj_dpp_neto'] ?? 0),
            'penj_inc_ppn_neto' => (float)str_replace(['.', ','], ['', '.'], $data['penj_inc_ppn_neto'] ?? 0),
            'keterangan'        => $data['keterangan'] ?: null,
            'tgl_kirim'         => $tgl_kirim,
            'created_by'        => $this->session->userdata('id_user'),
            'created_at'        => date('Y-m-d H:i:s'),
        ];
    }
}
