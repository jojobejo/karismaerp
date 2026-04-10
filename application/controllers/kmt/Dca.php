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

    // Ganti method tambah() — kirim kegiatan_list
    public function tambah() {
        $data = [
            'page_title'      => 'Tambah Data DCA',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'kegiatan_list'   => $this->M_Kmt->get_dca_kegiatan(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('tanggal_dca', 'Tanggal', 'required');
        $this->form_validation->set_rules('id_wilayah',  'Wilayah', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl = $this->input->post('tanggal_dca');

        // ── Ambil semua input POST ────────────────────────────────
        $um_header = (float)str_replace('.', '', $this->input->post('um_header') ?? 0);

        // Array detail
        $id_kegiatan      = $this->input->post('id_kegiatan')   ?? [];
        $nama_kegiatan    = $this->input->post('nama_kegiatan') ?? [];
        $real_arr         = $this->input->post('real_detail')   ?? [];
        $ket_arr          = $this->input->post('ket_detail')    ?? [];
        $tgl_kegiatan_arr = $this->input->post('tgl_kegiatan')  ?? [];
        $tgl_kasbon_arr   = $this->input->post('tgl_kasbon')    ?? [];
        $jml_peserta_arr  = $this->input->post('jml_peserta')   ?? [];
        $qty_bisi_arr     = $this->input->post('qty_bisi')      ?? [];
        $qty_q235_arr     = $this->input->post('qty_q235')      ?? [];

        // ── Hitung total real biaya ───────────────────────────────
        $total_real = 0;
        foreach ($real_arr as $real) {
            $total_real += (float)str_replace('.', '', $real ?? 0);
        }
        $refund_total = max(0, $um_header - $total_real);

        // ── Simpan header DCA ─────────────────────────────────────
        $insert_dca = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'nama_mdo'    => $this->input->post('nama_mdo'),
            'abm'         => $this->input->post('abm'),
            'uraian'      => $this->input->post('uraian') ?: 'Multi Kegiatan',
            'um'          => $um_header,
            'refund'      => $refund_total,
            'real_biaya'  => $total_real,
            'total_biaya' => $total_real,
            'created_by'  => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_dca($insert_dca)) {
            $id_dca = $this->db->insert_id();

            // ── Simpan detail ─────────────────────────────────────
            $detail_rows = [];
            foreach ($id_kegiatan as $i => $id_k) {
                $real = (float)str_replace('.', '', $real_arr[$i] ?? 0);
                if ($real <= 0) continue;

                $detail_rows[] = [
                    'id_dca'        => $id_dca,
                    'id_kegiatan'   => $id_k ?: null,
                    'nama_kegiatan' => $nama_kegiatan[$i] ?? '-',
                    'tgl_kegiatan'  => !empty($tgl_kegiatan_arr[$i]) ? $tgl_kegiatan_arr[$i] : null,
                    'tgl_kasbon'    => !empty($tgl_kasbon_arr[$i])   ? $tgl_kasbon_arr[$i]   : null,
                    'jml_peserta'   => (int)($jml_peserta_arr[$i] ?? 0),
                    'qty_bisi'      => (float)str_replace('.', '', $qty_bisi_arr[$i]  ?? 0),
                    'qty_q235'      => (float)str_replace('.', '', $qty_q235_arr[$i]  ?? 0),
                    'real_biaya'    => $real,
                    'total_biaya'   => $real,
                    'keterangan'    => $ket_arr[$i] ?? '',
                ];
            }

            if (!empty($detail_rows)) {
                $this->M_Kmt->insert_dca_detail($detail_rows);
            }

            $this->session->set_flashdata('success', 'Data DCA berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/dca');
    }

    // Ganti method edit()
    public function edit($id) {
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }

        $data = [
            'page_title'      => 'Edit Data DCA',
            'row'             => $row,
            'detail'          => $this->M_Kmt->get_dca_detail($id),
            'kegiatan_list'   => $this->M_Kmt->get_dca_kegiatan(),
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // Ganti method update()
    public function update($id) {
        $tgl = $this->input->post('tanggal_dca');

        // ── Ambil semua input POST ────────────────────────────────
        $um_header = (float)str_replace('.', '', $this->input->post('um_header') ?? 0);

        $id_kegiatan      = $this->input->post('id_kegiatan')   ?? [];
        $nama_kegiatan    = $this->input->post('nama_kegiatan') ?? [];
        $real_arr         = $this->input->post('real_detail')   ?? [];
        $ket_arr          = $this->input->post('ket_detail')    ?? [];
        $tgl_kegiatan_arr = $this->input->post('tgl_kegiatan')  ?? [];
        $tgl_kasbon_arr   = $this->input->post('tgl_kasbon')    ?? [];
        $jml_peserta_arr  = $this->input->post('jml_peserta')   ?? [];
        $qty_bisi_arr     = $this->input->post('qty_bisi')      ?? [];
        $qty_q235_arr     = $this->input->post('qty_q235')      ?? [];

        // ── Hitung total real biaya ───────────────────────────────
        $total_real = 0;
        foreach ($real_arr as $real) {
            $total_real += (float)str_replace('.', '', $real ?? 0);
        }
        $refund_total = max(0, $um_header - $total_real);

        // ── Update header DCA ─────────────────────────────────────
        $update = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'nama_mdo'    => $this->input->post('nama_mdo'),
            'abm'         => $this->input->post('abm'),
            'uraian'      => $this->input->post('uraian') ?: 'Multi Kegiatan',
            'um'          => $um_header,
            'refund'      => $refund_total,
            'real_biaya'  => $total_real,
            'total_biaya' => $total_real,
        ];

        if ($this->M_Kmt->update_dca($id, $update)) {
            // ── Hapus detail lama, insert baru ────────────────────
            $this->M_Kmt->delete_dca_detail($id);

            $detail_rows = [];
            foreach ($id_kegiatan as $i => $id_k) {
                $real = (float)str_replace('.', '', $real_arr[$i] ?? 0);
                if ($real <= 0) continue;

                $detail_rows[] = [
                    'id_dca'        => $id,
                    'id_kegiatan'   => $id_k ?: null,
                    'nama_kegiatan' => $nama_kegiatan[$i] ?? '-',
                    'tgl_kegiatan'  => !empty($tgl_kegiatan_arr[$i]) ? $tgl_kegiatan_arr[$i] : null,
                    'tgl_kasbon'    => !empty($tgl_kasbon_arr[$i])   ? $tgl_kasbon_arr[$i]   : null,
                    'jml_peserta'   => (int)($jml_peserta_arr[$i] ?? 0),
                    'qty_bisi'      => (float)str_replace('.', '', $qty_bisi_arr[$i]  ?? 0),
                    'qty_q235'      => (float)str_replace('.', '', $qty_q235_arr[$i]  ?? 0),
                    'real_biaya'    => $real,
                    'total_biaya'   => $real,
                    'keterangan'    => $ket_arr[$i] ?? '',
                ];
            }

            if (!empty($detail_rows)) {
                $this->M_Kmt->insert_dca_detail($detail_rows);
            }

            $this->session->set_flashdata('success', 'Data DCA berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/dca');
    }

    // Tambah method tambah_kegiatan() — AJAX add kegiatan custom
    public function tambah_kegiatan() {
        $nama = trim($this->input->post('nama_kegiatan'));
        if (empty($nama)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nama kegiatan tidak boleh kosong']);
            return;
        }

        // Cek duplikat
        $cek = $this->db->get_where('tbkmt_dca_kegiatan', ['nama_kegiatan' => $nama])->row();
        if ($cek) {
            echo json_encode(['status' => 'exists', 'id' => $cek->id, 'nama' => $cek->nama_kegiatan]);
            return;
        }

        $this->M_Kmt->insert_dca_kegiatan($nama, $this->session->userdata('id_user'));
        $new_id = $this->db->insert_id();

        echo json_encode(['status' => 'ok', 'id' => $new_id, 'nama' => $nama]);
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

    public function rekap() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();
        $abm        = $this->input->get('abm')        ?? '';
    
        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;
        if ($abm)        $filter['abm']        = $abm;
    
        $rekap_data = $this->M_Kmt->get_dca_rekap($filter);
    
        // Susun per ABM → per MDO → per kegiatan
        $grouped = [];
        foreach ($rekap_data as $dca) {
            $abm_key = $dca['abm'];
            $mdo_key = $dca['nama_mdo'];
    
            if (!isset($grouped[$abm_key])) {
                $grouped[$abm_key] = ['um' => 0, 'mdo' => []];
            }
            $grouped[$abm_key]['um'] += $dca['um'];
    
            if (!isset($grouped[$abm_key]['mdo'][$mdo_key])) {
                $grouped[$abm_key]['mdo'][$mdo_key] = ['kegiatan' => []];
            }
    
            foreach ($dca['detail'] as $det) {
                $kg_key = $det['nama_kegiatan'];
                if (!isset($grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key])) {
                    $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key] = [
                        'rows'        => [],
                        'total_bisi'  => 0,
                        'total_q235'  => 0,
                        'total_peserta' => 0,
                        'total_biaya' => 0,
                    ];
                }
    
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['rows'][] = [
                    'nama_mdo'    => $mdo_key,
                    'tgl_kegiatan'=> $det['tgl_kegiatan'],
                    'tgl_kasbon'  => $det['tgl_kasbon'],
                    'qty_bisi'    => $det['qty_bisi'],
                    'qty_q235'    => $det['qty_q235'],
                    'jml_peserta' => $det['jml_peserta'],
                    'real_biaya'  => $det['real_biaya'],
                ];
    
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_bisi']    += $det['qty_bisi'];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_q235']   += $det['qty_q235'];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_peserta'] += $det['jml_peserta'];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_biaya']   += $det['real_biaya'];
            }
        }
    
        $data = [
            'page_title'   => 'Rekapitulasi DCA',
            'grouped'      => $grouped,
            'rekap_data'   => $rekap_data,
            'abm_list'     => $this->M_Kmt->get_dca_abm_list(['tahun' => $tahun, 'id_wilayah' => $id_wilayah]),
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'abm'          => $abm,
            'lv'           => (int)$this->session->userdata('lv'),
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
        ];
    
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/rekap', $data);
        $this->load->view('partial/main/footer.php');
    }
    
    public function export_rekap() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();
        $abm        = $this->input->get('abm')        ?? '';
    
        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;
        if ($abm)        $filter['abm']        = $abm;
    
        $rekap_data = $this->M_Kmt->get_dca_rekap($filter);
    
        // Sama seperti rekap(), susun grouped
        $grouped = [];
        foreach ($rekap_data as $dca) {
            $abm_key = $dca['abm'];
            $mdo_key = $dca['nama_mdo'];
            if (!isset($grouped[$abm_key])) $grouped[$abm_key] = ['um' => 0, 'mdo' => [], 'wilayah' => $dca['nama_wilayah']];
            $grouped[$abm_key]['um'] += $dca['um'];
            if (!isset($grouped[$abm_key]['mdo'][$mdo_key])) $grouped[$abm_key]['mdo'][$mdo_key] = ['kegiatan' => []];
            foreach ($dca['detail'] as $det) {
                $kg_key = $det['nama_kegiatan'];
                if (!isset($grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key])) {
                    $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key] = [
                        'rows' => [], 'total_bisi' => 0, 'total_q235' => 0,
                        'total_peserta' => 0, 'total_biaya' => 0,
                    ];
                }
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['rows'][] = [
                    'nama_mdo' => $mdo_key, 'qty_bisi' => $det['qty_bisi'],
                    'qty_q235' => $det['qty_q235'], 'jml_peserta' => $det['jml_peserta'],
                    'real_biaya' => $det['real_biaya'],
                ];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_bisi']    += $det['qty_bisi'];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_q235']   += $det['qty_q235'];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_peserta'] += $det['jml_peserta'];
                $grouped[$abm_key]['mdo'][$mdo_key]['kegiatan'][$kg_key]['total_biaya']   += $det['real_biaya'];
            }
        }
    
        $nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];
    
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheetIndex  = 0;
    
        foreach ($grouped as $abm_nama => $abm_data) {
            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $sheetIndex++;
    
            $safe_name = substr(preg_replace('/[^\w\s]/', '', $abm_nama), 0, 28);
            $sheet->setTitle($safe_name ?: 'Sheet'.$sheetIndex);
            $sheet->setShowGridLines(false);
    
            // ── Helper styles ───────────────────────────────────────
            $DARK  = '1F3864'; $MID = '2E75B6'; $GOLD = 'FFC000';
            $LIGHT = 'DAEEF3'; $WHITE = 'FFFFFF'; $GRAY = 'F2F2F2';
    
            $boldWhite = new \PhpOffice\PhpSpreadsheet\Style\Font();
            $boldWhite->setBold(true)->setColor(
                (new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB($WHITE)
            )->setName('Arial')->setSize(10);
    
            $styleHeader = [
                'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE], 'name' => 'Arial', 'size' => 10],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $DARK]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
            ];
            $styleSubtotal = [
                'font'      => ['bold' => true, 'color' => ['rgb' => $DARK], 'name' => 'Arial', 'size' => 10],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $LIGHT]],
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
            ];
            $styleDetail = [
                'font'      => ['bold' => false, 'color' => ['rgb' => '000000'], 'name' => 'Arial', 'size' => 9],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $WHITE]],
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
            ];
    
            // ── Column widths ───────────────────────────────────────
            foreach ([['A',44],['B',15],['C',16],['D',16],['E',12],['F',12],['G',16]] as [$c,$w]) {
                $sheet->getColumnDimension($c)->setWidth($w);
            }
    
            // ── Row 1-2: Judul ──────────────────────────────────────
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'REKAPITULASI DCA KMT CORN');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>$WHITE],'size'=>14,'name'=>'Arial'],
                'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>$DARK]],
                'alignment' => ['horizontal'=>'center','vertical'=>'center'],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);
    
            $sheet->mergeCells('A2:G2');
            $sheet->setCellValue('A2', 'DCA JAGUNG BISI 959 & Q-235 CLING');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold'=>true,'color'=>['rgb'=>$WHITE],'size'=>11,'name'=>'Arial'],
                'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>$MID]],
                'alignment' => ['horizontal'=>'center','vertical'=>'center'],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(22);
    
            // ── Rows 3-6: Info ──────────────────────────────────────
            $um_total   = $abm_data['um'];
            $tot_biaya  = 0;
            foreach ($abm_data['mdo'] as $mdo_nm => $mdo_data) {
                foreach ($mdo_data['kegiatan'] as $kg) $tot_biaya += $kg['total_biaya'];
            }
            $sisa_dana  = $um_total - $tot_biaya;
            $periode    = ($bulan ? $nama_bulan[(int)$bulan].' ' : '') . $tahun;
    
            $info = [
                [3, 'ABM',     $abm_nama],
                [4, 'MDO',     implode(', ', array_keys($abm_data['mdo']))],
                [5, 'Wilayah', $abm_data['wilayah'] ?? '-'],
                [6, 'Periode', $periode],
            ];
            foreach ($info as [$r, $lbl, $val]) {
                $sheet->getRowDimension($r)->setRowHeight(18);
                $sheet->setCellValue("A{$r}", $lbl);
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font' => ['bold'=>true,'color'=>['rgb'=>$DARK],'name'=>'Arial','size'=>10],
                    'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>$GRAY]],
                    'borders'=>['allBorders'=>['borderStyle'=>'thin']],
                ]);
                $sheet->mergeCells("B{$r}:D{$r}");
                $sheet->setCellValue("B{$r}", $val);
                $sheet->getStyle("B{$r}")->applyFromArray([
                    'borders'=>['allBorders'=>['borderStyle'=>'thin']],
                ]);
            }
    
            // Info kanan
            $info_r = [
                [3,'Total Biaya', $tot_biaya, $GOLD,   $DARK],
                [4,'UM DCA',      $um_total,  '000000', $WHITE],
                [5,'Sisa Dana',   $sisa_dana, $DARK,    'FFF2CC'],
            ];
            foreach ($info_r as [$r, $lbl, $val, $vfg, $vbg]) {
                $sheet->mergeCells("E{$r}:F{$r}");
                $sheet->setCellValue("E{$r}", $lbl);
                $sheet->getStyle("E{$r}")->applyFromArray([
                    'font' => ['bold'=>true,'color'=>['rgb'=>$WHITE],'name'=>'Arial','size'=>10],
                    'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>$DARK]],
                    'alignment' => ['horizontal'=>'right'],
                    'borders'   => ['allBorders'=>['borderStyle'=>'thin']],
                ]);
                $sheet->setCellValue("G{$r}", $val);
                $sheet->getStyle("G{$r}")->applyFromArray([
                    'font'      => ['bold'=>true,'color'=>['rgb'=>$vfg],'name'=>'Arial','size'=>10],
                    'fill'      => ['fillType'=>'solid','startColor'=>['rgb'=>$vbg]],
                    'alignment' => ['horizontal'=>'right'],
                    'borders'   => ['allBorders'=>['borderStyle'=>'thin']],
                    'numberFormat' => ['formatCode'=>'#,##0'],
                ]);
            }
    
            // ── Row 7: spacer, Row 8: thead ─────────────────────────
            $sheet->getRowDimension(7)->setRowHeight(5);
            $sheet->getRowDimension(8)->setRowHeight(30);
            foreach ([['A','Nama Petugas & Jenis Kegiatan'],['B','DCA Kas Bon'],
                      ['C','DS Bisi 959\n(20x1Kg)'],['D','DS Q-235 CLING\n(10x1Kg)'],
                      ['E','Jml Peserta'],['F','Qty Terjual'],['G','Total Biaya']] as [$c,$h]) {
                $sheet->setCellValue("{$c}8", $h);
                $sheet->getStyle("{$c}8")->applyFromArray($styleHeader);
            }
    
            // ── Data rows ───────────────────────────────────────────
            $r = 9;
            $num_fmt = '#,##0';
    
            foreach ($abm_data['mdo'] as $mdo_nm => $mdo_data) {
                // UM row
                $sheet->setCellValue("A{$r}", "UM {$mdo_nm} (BFM,FM,FFD,ODP)");
                $sheet->setCellValue("B{$r}", $um_total);
                $sheet->setCellValue("G{$r}", 0);
                $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                    'font' => ['bold'=>true,'color'=>['rgb'=>'DAEEF3'],'name'=>'Arial','size'=>10],
                    'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>'344D7E']],
                    'alignment' => ['horizontal'=>'left','vertical'=>'center'],
                    'borders'   => ['allBorders'=>['borderStyle'=>'thin']],
                ]);
                foreach (['B','G'] as $nc) {
                    $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                    $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                }
                $sheet->getRowDimension($r)->setRowHeight(18);
                $r++;
    
                foreach ($mdo_data['kegiatan'] as $kg_nm => $kg_data) {
                    // Kegiatan header
                    $sheet->setCellValue("A{$r}", $kg_nm);
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                        'font' => ['bold'=>true,'color'=>['rgb'=>$WHITE],'name'=>'Arial','size'=>10],
                        'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>'4472C4']],
                        'alignment' => ['horizontal'=>'left','vertical'=>'center'],
                        'borders'   => ['allBorders'=>['borderStyle'=>'thin']],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $r++;
    
                    // Detail rows
                    foreach ($kg_data['rows'] as $det) {
                        $sheet->setCellValue("A{$r}", '  ' . $det['nama_mdo']);
                        $sheet->setCellValue("C{$r}", $det['qty_bisi']);
                        $sheet->setCellValue("D{$r}", $det['qty_q235']);
                        $sheet->setCellValue("E{$r}", $det['jml_peserta']);
                        $sheet->setCellValue("G{$r}", $det['real_biaya']);
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleDetail);
                        foreach (['C','D','E','G'] as $nc) {
                            $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                            $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                        }
                        $sheet->getRowDimension($r)->setRowHeight(15);
                        $r++;
                    }
    
                    // Subtotal kegiatan
                    $sheet->setCellValue("A{$r}", "{$kg_nm} Total");
                    $sheet->setCellValue("C{$r}", $kg_data['total_bisi']);
                    $sheet->setCellValue("D{$r}", $kg_data['total_q235']);
                    $sheet->setCellValue("E{$r}", $kg_data['total_peserta']);
                    $sheet->setCellValue("G{$r}", $kg_data['total_biaya']);
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleSubtotal);
                    foreach (['C','D','E','G'] as $nc) {
                        $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                        $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                    }
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $r++;
                }
    
                // Subtotal MDO
                $mdo_bisi = $mdo_peserta = $mdo_biaya = 0;
                foreach ($mdo_data['kegiatan'] as $kg) {
                    $mdo_bisi    += $kg['total_bisi'];
                    $mdo_peserta += $kg['total_peserta'];
                    $mdo_biaya   += $kg['total_biaya'];
                }
                $sheet->setCellValue("A{$r}", "MARKET DEVELOPMENT OFFICER (MDO) Total");
                $sheet->setCellValue("C{$r}", $mdo_bisi);
                $sheet->setCellValue("E{$r}", $mdo_peserta);
                $sheet->setCellValue("G{$r}", $mdo_biaya);
                $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                    'font' => ['bold'=>true,'color'=>['rgb'=>$DARK],'name'=>'Arial','size'=>10],
                    'fill' => ['fillType'=>'solid','startColor'=>['rgb'=>'9DC3E6']],
                    'alignment' => ['horizontal'=>'left','vertical'=>'center'],
                    'borders'   => ['allBorders'=>['borderStyle'=>'thin']],
                ]);
                foreach (['C','E','G'] as $nc) {
                    $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                    $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                }
                $sheet->getRowDimension($r)->setRowHeight(18);
                $r++;
            }
    
            // Grand Total
            $sheet->setCellValue("A{$r}", "{$abm_nama} Total / Grand Total");
            $sheet->setCellValue("B{$r}", $um_total);
            $sheet->setCellValue("G{$r}", $tot_biaya);
            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                'font'      => ['bold'=>true,'color'=>['rgb'=>$GOLD],'name'=>'Arial','size'=>11],
                'fill'      => ['fillType'=>'solid','startColor'=>['rgb'=>$DARK]],
                'alignment' => ['horizontal'=>'left','vertical'=>'center'],
                'borders'   => ['allBorders'=>['borderStyle'=>'thin']],
            ]);
            foreach (['B','G'] as $nc) {
                $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
            }
            $sheet->getRowDimension($r)->setRowHeight(22);
    
            $sheet->freezePane('A9');
            $sheet->getPageSetup()->setOrientation('landscape');
            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1);
        }
    
        $periode_str = ($bulan ? 'Bln'.$bulan.'_' : '') . $tahun;
        $filename = 'Rekap_DCA_KMT_' . $periode_str . '.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
