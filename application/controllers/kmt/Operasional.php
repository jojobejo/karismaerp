<?php
// ================================================================
// controllers/content/kmt/Operasional.php — VERSI + VERIFIKASI
// Tabel user : tb_karyawan
// Kolom nama : nm_karyawan
// Kolom level: akses_lv
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Operasional extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    // ── Helper level ─────────────────────────────────────────────
    private function get_lv()     { return (int)$this->session->userdata('lv'); }
    private function get_id_user(){ return (int)$this->session->userdata('id_user'); }
    private function is_admkeu()  { return $this->get_lv() === 2; }
    private function is_abm()     { return $this->get_lv() === 3; }
    private function is_super()   { return $this->get_lv() === 1; }

    private function get_id_wilayah_filter() {
        return $this->is_abm()
            ? (int)$this->session->userdata('wilayah') : null;
    }

    // ── Daftar field biaya (dipakai berulang di simpan/update) ───
    private $fields = [
        'hotel','per_diem','entertainment','communication','atk','gasoline',
        'sparepart_service','retribusi_toll_parkir','transportasi','pos_paket',
        'tambah_angin','tambal_ban','indekost','sewa_kendaraan','lain_lain',
    ];

    // ================================================================
    // INDEX
    // ================================================================
    public function index() {
        $tahun             = $this->input->get('tahun')             ?? date('Y');
        $bulan             = $this->input->get('bulan')             ?? '';
        $id_wilayah        = $this->input->get('id_wilayah')        ?? $this->get_id_wilayah_filter();
        $status_verifikasi = $this->input->get('status_verifikasi') ?? '';

        $filter = ['tahun' => $tahun];
        if ($bulan)                    $filter['bulan']             = $bulan;
        if ($id_wilayah)               $filter['id_wilayah']        = $id_wilayah;
        if ($status_verifikasi !== '') $filter['status_verifikasi'] = (int)$status_verifikasi;

        $list        = $this->M_Kmt->get_operasional_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));

        $jml_belum = count(array_filter($list, fn($r) => (int)$r['status_verifikasi'] === 0));
        $jml_sudah = count(array_filter($list, fn($r) => (int)$r['status_verifikasi'] === 1));

        $data = [
            'page_title'        => 'Data Operasional KMT CORN',
            'list'              => $list,
            'total_biaya'       => $total_biaya,
            'wilayah_list'      => $this->M_Kmt->get_wilayah(),
            'tahun'             => $tahun,
            'bulan'             => $bulan,
            'id_wilayah'        => $id_wilayah,
            'status_verifikasi' => $status_verifikasi,
            'jml_belum'         => $jml_belum,
            'jml_sudah'         => $jml_sudah,
            'nama_bulan'        => ['','Januari','Februari','Maret','April','Mei','Juni',
                                    'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'                => $this->get_lv(),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/operasional/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // TAMBAH
    // ================================================================
    public function tambah() {
        // Adm Keu tidak bisa tambah data
        if ($this->is_admkeu()) {
            $this->session->set_flashdata('error', 'Adm Keuangan tidak dapat menambah data.');
            redirect('kmt/operasional');
        }

        $data = [
            'page_title'      => 'Tambah Biaya Operasional',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => $this->get_lv(),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/operasional/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // SIMPAN
    // ================================================================
    public function simpan() {
        $this->form_validation->set_rules('tanggal',    'Tanggal', 'required');
        $this->form_validation->set_rules('nama',       'Nama',    'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tanggal     = $this->input->post('tanggal');
        $total_biaya = 0;

        $insert = [
            'id_wilayah'       => (int)$this->input->post('id_wilayah'),
            'bulan'            => (int)date('m', strtotime($tanggal)),
            'tahun'            => (int)date('Y', strtotime($tanggal)),
            'tanggal'          => $tanggal,
            'nama'             => $this->input->post('nama'),
            'nama_mdo'         => $this->input->post('nama_mdo'),
            'status_verifikasi'=> 0,
            'created_by'       => $this->get_id_user(),
        ];

        foreach ($this->fields as $f) {
            $val         = $this->input->post($f);
            $angka       = $val ? (float)str_replace(['.','Rp ','Rp'], ['','',''], $val) : 0;
            $insert[$f]  = $angka;
            $total_biaya += $angka;
        }

        $um             = (float)str_replace('.', '', $this->input->post('um') ?? 0);
        $insert['um']   = $um;
        $insert['refund']= max(0, $um - $total_biaya);

        if ($this->M_Kmt->insert_operasional($insert)) {
            $this->session->set_flashdata('success', 'Data operasional berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/operasional');
    }

    // ================================================================
    // EDIT
    // ================================================================
    public function edit($id) {
        $row = $this->M_Kmt->get_operasional_by_id($id);
        if (!$row) { show_404(); return; }

        $lv          = $this->get_lv();
        $is_verified = (int)($row['status_verifikasi'] ?? 0) === 1;

        // ABM hanya bisa edit data wilayahnya sendiri
        if ($lv === 3 && $row['id_wilayah'] != $this->session->userdata('wilayah')) {
            $this->session->set_flashdata('error', 'Anda tidak bisa mengakses data wilayah lain.');
            redirect('kmt/operasional');
            return;
        }

        // ABM tidak bisa edit jika sudah terverifikasi (hanya view)
        // Adm Keu & Super tetap bisa edit meski sudah terverifikasi
        $data = [
            'page_title'      => $is_verified && $lv === 3
                                    ? 'Lihat Biaya Operasional'
                                    : 'Edit Biaya Operasional',
            'row'             => $row,
            'log_verifikasi'  => $this->M_Kmt->get_log_verif_op($id),
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'              => $lv,
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/operasional/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // UPDATE
    // ================================================================
    public function update($id) {
        $lv = $this->get_lv();

        // Guard: ABM tidak bisa update data yang sudah terverifikasi
        if ($lv === 3 && $this->M_Kmt->is_operasional_verified($id)) {
            $this->session->set_flashdata('error', 'Data sudah diverifikasi dan tidak dapat diubah.');
            redirect('kmt/operasional');
            return;
        }

        // Jika adm keu / super edit data yg sudah diverifikasi → reset status
        $was_verified = $this->M_Kmt->is_operasional_verified($id);
        $reset_verif  = ($was_verified && $lv <= 2);

        $tanggal     = $this->input->post('tanggal');
        $total_biaya = 0;

        $update = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'bulan'      => (int)date('m', strtotime($tanggal)),
            'tahun'      => (int)date('Y', strtotime($tanggal)),
            'tanggal'    => $tanggal,
            'nama'       => $this->input->post('nama'),
            'nama_mdo'   => $this->input->post('nama_mdo'),
        ];

        foreach ($this->fields as $f) {
            $val         = $this->input->post($f);
            $angka       = $val ? (float)str_replace(['.','Rp ','Rp'], ['','',''], $val) : 0;
            $update[$f]  = $angka;
            $total_biaya += $angka;
        }

        $um              = (float)str_replace('.', '', $this->input->post('um') ?? 0);
        $update['um']    = $um;
        $update['refund']= max(0, $um - $total_biaya);

        // Reset verifikasi jika diedit oleh level ≤ 2 setelah terverifikasi
        if ($reset_verif) {
            $update['status_verifikasi'] = 0;
            $update['verified_by']       = null;
            $update['verified_at']       = null;
            $update['verified_notes']    = null;
            $this->M_Kmt->_log_verif_op_public(
                $id, 'reset_oleh_edit', $this->get_id_user(),
                'Data diedit oleh ' . $this->session->userdata('nama')
            );
        }

        if ($this->M_Kmt->update_operasional($id, $update)) {
            $msg = $reset_verif
                ? 'Data diperbarui. Status verifikasi direset — perlu diverifikasi ulang.'
                : 'Data berhasil diperbarui.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/operasional');
    }

    // ================================================================
    // HAPUS
    // ================================================================
    public function hapus($id) {
        // Data terverifikasi tidak bisa dihapus (kecuali super)
        if (!$this->is_super() && $this->M_Kmt->is_operasional_verified($id)) {
            $this->session->set_flashdata('error',
                'Data sudah diverifikasi dan tidak dapat dihapus. Hubungi Admin.');
            redirect('kmt/operasional');
            return;
        }

        if ($this->M_Kmt->delete_operasional($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/operasional');
    }

    // ================================================================
    // AJAX VERIFIKASI
    // ================================================================
    public function ajax_verifikasi() {
        if (!$this->is_admkeu() && !$this->is_super()) {
            echo json_encode(['status' => 'error', 'msg' => 'Akses ditolak']);
            return;
        }

        $id     = (int)$this->input->post('id');
        $aksi   = $this->input->post('aksi');   // 'verifikasi' | 'batal'
        $catatan= trim($this->input->post('catatan') ?? '');

        if (!$id) {
            echo json_encode(['status' => 'error', 'msg' => 'ID tidak valid']);
            return;
        }

        if ($aksi === 'verifikasi') {
            $ok = $this->M_Kmt->verifikasi_operasional($id, $this->get_id_user(), $catatan);
        } else {
            $ok = $this->M_Kmt->batal_verifikasi_operasional($id, $this->get_id_user(), $catatan);
        }

        if ($ok) {
            $row = $this->M_Kmt->get_operasional_by_id($id);
            echo json_encode([
                'status'            => 'ok',
                'status_verifikasi' => (int)$row['status_verifikasi'],
                'verified_at'       => $row['verified_at'],
                'nama_verifikator'  => $row['nama_verifikator'] ?? '-',
                'msg'               => $aksi === 'verifikasi'
                                        ? 'Data berhasil diverifikasi.'
                                        : 'Verifikasi berhasil dibatalkan.',
            ]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Operasi gagal. Coba lagi.']);
        }
    }

    // ================================================================
    // EXPORT EXCEL
    // ================================================================
    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_operasional_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Operasional');

        $headers = [
            'No','Tanggal','Tanggal Input','Wilayah','Nama','MDO',
            'Hotel','Per Diem','Entertainment','Communication','ATK','Gasoline',
            'Sparepart','Toll/Parkir','Transportasi','Pos/Paket',
            'Tambah Angin','Tambal Ban','Indekost','Sewa Kendaraan','Lain-lain',
            'Total','Status Verifikasi',
        ];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $sheet->getStyle('A1:W1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $r, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValueByColumnAndRow(3, $r, !empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-');
            $sheet->setCellValueByColumnAndRow(4, $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $r, $row['nama']);
            $sheet->setCellValueByColumnAndRow(6, $r, $row['nama_mdo'] ?? '-');
            $col = 7;
            foreach ($this->fields as $f) {
                $sheet->setCellValueByColumnAndRow($col++, $r, $row[$f] ?? 0);
            }
            $sheet->setCellValueByColumnAndRow($col++, $r, $row['total_biaya']);
            $sheet->setCellValueByColumnAndRow($col,   $r,
                (int)$row['status_verifikasi'] === 1
                    ? '✓ Terverifikasi (' . ($row['nama_verifikator'] ?? '-') . ')'
                    : 'Belum Diverifikasi'
            );
        }

        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Operasional_KMT_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
