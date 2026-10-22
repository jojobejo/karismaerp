<?php
// ================================================================
// controllers/content/kmt/Dca.php  — VERSI LENGKAP + VERIFIKASI
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Dca extends CI_Controller {
 
    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }
 
    // ── Level helper ──────────────────────────────────────────────
    private function get_lv()              { return (int)$this->session->userdata('lv'); }
    private function get_id_user()         { return (int)$this->session->userdata('id_user'); }
    private function is_admkeu()           { return $this->get_lv() === 2; }   // adm keu
    private function is_abm()             { return $this->get_lv() === 3; }   // ABM / field
    private function is_super()           { return $this->get_lv() === 1; }   // super admin
 
    private function get_id_wilayah_filter() {
        return $this->is_abm()
            ? (int)$this->session->userdata('wilayah') : null;
    }

    private function get_tgl_kasbon_filter($default = null) {
        $value = $this->input->post_get('tgl_kasbon');
        if (!$value) return $default ?: date('Y-m-d');

        $date = \DateTime::createFromFormat('!Y-m-d', $value);
        return ($date && $date->format('Y-m-d') === $value)
            ? $value
            : ($default ?: date('Y-m-d'));
    }

    private function get_dca_product_key($uraian) {
        $text = strtolower((string)$uraian);
        if (strpos($text, 'cpboration') !== false || preg_match('/\bcp\b/i', $text)) return 'cp';
        if (strpos($text, 'q235') !== false || strpos($text, 'q-235') !== false || strpos($text, 'qiuboration') !== false) return 'q235';
        if (strpos($text, 'bisi') !== false || strpos($text, 'bisiboration') !== false) return 'bisi';
        return 'dca';
    }

    private function get_dca_activity_code($nama_kegiatan) {
        $text = strtoupper(trim((string)$nama_kegiatan));
        foreach (['MBFM', 'BFM', 'FM', 'ODP', 'FFD'] as $code) {
            if (preg_match('/\b'.preg_quote($code, '/').'\b/', $text)) return $code;
        }
        return null;
    }

    private function get_dca_um_label($abm_nama, $data_abm) {
        $product_counts = $data_abm['product_counts'] ?? [];
        arsort($product_counts);
        $product_key = $product_counts ? key($product_counts) : 'dca';
        $product_labels = [
            'bisi' => 'Bisiboration Jag.Bisi 959',
            'q235' => 'Qiuboration Jag.Q235',
            'cp'   => 'CPBoration',
            'dca'  => 'DCA',
        ];
        $activity_order = ['MBFM', 'BFM', 'FM', 'ODP', 'FFD'];
        $activities = [];
        foreach ($activity_order as $code) {
            if (!empty($data_abm['activity_codes'][$code])) $activities[] = $code;
        }
        $activity_label = $activities ? ' ('.implode(',', $activities).')' : '';
        $mdo_count = count($data_abm['mdo_names'] ?? []);
        return 'UM '.$product_labels[$product_key].$activity_label
            .' ('.$mdo_count.' MDO) u/Bp.'.$abm_nama;
    }
 
    // ── Index ─────────────────────────────────────────────────────
    public function index() {
        $tahun             = $this->input->get('tahun')             ?? date('Y');
        $bulan             = $this->input->get('bulan')             ?? '';
        $id_wilayah        = $this->input->get('id_wilayah')        ?? $this->get_id_wilayah_filter();
        $status_verifikasi = $this->input->get('status_verifikasi') ?? '';
 
        $filter = ['tahun' => $tahun];
        if ($bulan)                        $filter['bulan']             = $bulan;
        if ($id_wilayah)                   $filter['id_wilayah']        = $id_wilayah;
        if ($status_verifikasi !== '')     $filter['status_verifikasi'] = (int)$status_verifikasi;
 
        $list        = $this->M_Kmt->get_dca_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));
 
        // Hitung badge ringkasan
        $jml_belum = count(array_filter($list, fn($r) => (int)$r['status_verifikasi'] === 0));
        $jml_sudah = count(array_filter($list, fn($r) => (int)$r['status_verifikasi'] === 1));
 
        $data = [
            'page_title'        => 'Data DCA KMT CORN',
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
        $this->load->view('content/kmt/dca/index', $data);
        $this->load->view('partial/main/footer.php');
    }
 
    // ── Tambah ────────────────────────────────────────────────────
    public function tambah() {
        $data = [
            'page_title'      => 'Tambah Data DCA',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'kegiatan_list'   => $this->M_Kmt->get_dca_kegiatan(),
            'lv'              => $this->get_lv(),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }
 
    // ── Simpan ────────────────────────────────────────────────────
    public function simpan() {
        $this->form_validation->set_rules('tanggal_dca', 'Tanggal', 'required');
        $this->form_validation->set_rules('id_wilayah',  'Wilayah', 'required|integer');
        $this->form_validation->set_rules('abm', 'ABM', 'trim|required|max_length[100]');
 
        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }
 
        $tgl       = $this->input->post('tanggal_dca');
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
 
        $total_real = 0;
        foreach ($real_arr as $real) {
            $total_real += (float)str_replace('.', '', $real ?? 0);
        }
        $refund_total = max(0, $um_header - $total_real);
 
        $insert_dca = [
            'tanggal_dca'      => $tgl,
            'bulan'            => (int)date('m', strtotime($tgl)),
            'tahun'            => (int)date('Y', strtotime($tgl)),
            'id_wilayah'       => (int)$this->input->post('id_wilayah'),
            'nama_mdo'         => $this->input->post('nama_mdo'),
            'abm'              => trim((string)$this->input->post('abm')),
            'uraian'           => $this->input->post('uraian') ?: 'Multi Kegiatan',
            'um'               => $um_header,
            'refund'           => $refund_total,
            'real_biaya'       => $total_real,
            'total_biaya'      => $total_real,
            'status_verifikasi'=> 0,          // default belum diverifikasi
            'created_by'       => $this->get_id_user(),
        ];
 
        if ($this->M_Kmt->insert_dca($insert_dca)) {
            $id_dca = $this->db->insert_id();
 
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
 
    // ── Edit ─────────────────────────────────────────────────────
    public function edit($id) {
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }
    
        $lv          = $this->get_lv();
        $is_verified = (int)($row['status_verifikasi'] ?? 0) === 1;
    
        // ABM hanya bisa akses data wilayahnya sendiri
        if ($lv === 3 && $row['id_wilayah'] != $this->session->userdata('wilayah')) {
            $this->session->set_flashdata('error', 'Anda tidak bisa mengakses data wilayah lain.');
            redirect('kmt/dca');
            return;
        }
    
        $data = [
            'page_title'      => ($lv === 3 && $is_verified)
                                    ? 'Lihat Data DCA'
                                    : 'Edit Data DCA',
            'row'             => $row,
            'detail'          => $this->M_Kmt->get_dca_detail($id),
            'kegiatan_list'   => $this->M_Kmt->get_dca_kegiatan(),
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'log_verifikasi'  => $this->M_Kmt->get_log_verifikasi($id),
            'lv'              => $lv,
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];
    
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }
 
    // ── Update ────────────────────────────────────────────────────
    public function update($id) {
        // Guard: ABM tidak boleh update data yang sudah diverifikasi
        if ($this->is_abm() && $this->M_Kmt->is_dca_verified($id)) {
            $this->session->set_flashdata('error',
                'Data sudah diverifikasi. Anda tidak memiliki izin untuk mengubahnya.');
            redirect('kmt/dca');
            return;
        }
 
        // Jika super admin / adm keu mengedit data yg sudah diverifikasi,
        // reset status verifikasi agar perlu diverifikasi ulang
        $was_verified = $this->M_Kmt->is_dca_verified($id);
        $reset_verif  = ($was_verified && !$this->is_abm());
 
        $tgl       = $this->input->post('tanggal_dca');
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
 
        $total_real = 0;
        foreach ($real_arr as $real) {
            $total_real += (float)str_replace('.', '', $real ?? 0);
        }
        $refund_total = max(0, $um_header - $total_real);
 
        $update = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'nama_mdo'    => $this->input->post('nama_mdo'),
            'abm'         => trim((string)$this->input->post('abm')),
            'uraian'      => $this->input->post('uraian') ?: 'Multi Kegiatan',
            'um'          => $um_header,
            'refund'      => $refund_total,
            'real_biaya'  => $total_real,
            'total_biaya' => $total_real,
        ];
 
        // Reset verifikasi jika data yg sudah diverifikasi diedit oleh level ≥ 2
        if ($reset_verif) {
            $update['status_verifikasi'] = 0;
            $update['verified_by']       = null;
            $update['verified_at']       = null;
            $update['verified_notes']    = null;
            $this->M_Kmt->_log_verifikasi_public($id, 'reset_oleh_edit', $this->get_id_user(),
                'Data diedit oleh ' . $this->session->userdata('nama'));
        }
 
        if ($this->M_Kmt->update_dca($id, $update)) {
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
 
            $msg = $reset_verif
                ? 'Data DCA diperbarui. Status verifikasi direset — perlu diverifikasi ulang.'
                : 'Data DCA berhasil diperbarui.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/dca');
    }
 
    // ================================================================
    // VERIFIKASI — hanya level 2 (adm keu) dan level 1 (super)
    // ================================================================
 
    /**
     * POST /kmt/dca/verifikasi/{id}
     * Adm Keu menekan tombol "Verifikasi"
     */
    public function verifikasi($id) {
        if (!$this->is_admkeu() && !$this->is_super()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk memverifikasi data.');
            redirect('kmt/dca');
            return;
        }
 
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }
 
        if ((int)$row['status_verifikasi'] === 1) {
            $this->session->set_flashdata('warning', 'Data ini sudah diverifikasi sebelumnya.');
            redirect('kmt/dca');
            return;
        }
 
        $catatan = trim($this->input->post('catatan_verifikasi') ?? '');
 
        if ($this->M_Kmt->verifikasi_dca($id, $this->get_id_user(), $catatan)) {
            $this->session->set_flashdata('success',
                'Data DCA <strong>' . htmlspecialchars($row['uraian']) . '</strong> berhasil diverifikasi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal melakukan verifikasi. Silakan coba lagi.');
        }
 
        // Kembali ke halaman index dengan filter yang sama
        $back = $this->input->post('redirect_back') ?: 'kmt/dca';
        redirect($back);
    }
 
    /**
     * POST /kmt/dca/batal_verifikasi/{id}
     * Adm Keu atau Super membatalkan verifikasi
     */
    public function batal_verifikasi($id) {
        if (!$this->is_admkeu() && !$this->is_super()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk membatalkan verifikasi.');
            redirect('kmt/dca');
            return;
        }
 
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }
 
        $catatan = trim($this->input->post('catatan_batal') ?? '');
 
        if ($this->M_Kmt->batal_verifikasi_dca($id, $this->get_id_user(), $catatan)) {
            $this->session->set_flashdata('success', 'Verifikasi DCA berhasil dibatalkan. Data dapat diedit kembali.');
        } else {
            $this->session->set_flashdata('error', 'Gagal membatalkan verifikasi.');
        }
 
        $back = $this->input->post('redirect_back') ?: 'kmt/dca';
        redirect($back);
    }
 
    /**
     * AJAX: verifikasi cepat dari tabel index (tanpa catatan)
     * POST /kmt/dca/ajax_verifikasi
     */
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
            $ok = $this->M_Kmt->verifikasi_dca($id, $this->get_id_user(), $catatan);
        } else {
            $ok = $this->M_Kmt->batal_verifikasi_dca($id, $this->get_id_user(), $catatan);
        }
 
        if ($ok) {
            $row = $this->M_Kmt->get_dca_by_id($id);
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
 
    // ── Hapus ─────────────────────────────────────────────────────
    public function hapus($id) {
        // Tidak boleh hapus data yang sudah diverifikasi (kecuali super)
        if (!$this->is_super() && $this->M_Kmt->is_dca_verified($id)) {
            $this->session->set_flashdata('error',
                'Data sudah diverifikasi dan tidak dapat dihapus. Hubungi Admin.');
            redirect('kmt/dca');
            return;
        }
 
        if ($this->M_Kmt->delete_dca($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/dca');
    }
 
    // ── Tambah kegiatan (AJAX) ────────────────────────────────────
    public function tambah_kegiatan() {
        $nama = trim($this->input->post('nama_kegiatan'));
        if (empty($nama)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nama kegiatan tidak boleh kosong']);
            return;
        }
        $cek = $this->db->get_where('tbkmt_dca_kegiatan', ['nama_kegiatan' => $nama])->row();
        if ($cek) {
            echo json_encode(['status' => 'exists', 'id' => $cek->id, 'nama' => $cek->nama_kegiatan]);
            return;
        }
        $this->M_Kmt->insert_dca_kegiatan($nama, $this->get_id_user());
        $new_id = $this->db->insert_id();
        echo json_encode(['status' => 'ok', 'id' => $new_id, 'nama' => $nama]);
    }
 
    // ── Export & Rekap (tidak berubah, copy dari versi sebelumnya) ─
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
 
        $headers = ['No','Tanggal','Tanggal Input','Wilayah','ABM','Uraian','UM','Refund','Real Biaya','Total','Status Verifikasi'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);
 
        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1,  $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2,  $r, date('d/m/Y', strtotime($row['tanggal_dca'])));
            $sheet->setCellValueByColumnAndRow(3,  $r, !empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-');
            $sheet->setCellValueByColumnAndRow(4,  $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5,  $r, $row['abm'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6,  $r, $row['uraian']);
            $sheet->setCellValueByColumnAndRow(7,  $r, $row['um']);
            $sheet->setCellValueByColumnAndRow(8,  $r, $row['refund']);
            $sheet->setCellValueByColumnAndRow(9,  $r, $row['real_biaya']);
            $sheet->setCellValueByColumnAndRow(10, $r, $row['total_biaya']);
            $sheet->setCellValueByColumnAndRow(11, $r,
                (int)$row['status_verifikasi'] === 1
                    ? '✓ Terverifikasi (' . ($row['nama_verifikator'] ?? '-') . ')'
                    : 'Belum Diverifikasi'
            );
        }

        foreach (range('A', 'K') as $col) {
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

    public function export_rincian() {
        $tahun             = $this->input->get('tahun')             ?? date('Y');
        $bulan             = $this->input->get('bulan')             ?? '';
        $id_wilayah        = $this->input->get('id_wilayah')        ?? $this->get_id_wilayah_filter();
        $status_verifikasi = $this->input->get('status_verifikasi') ?? '';

        $filter = ['tahun' => $tahun];
        if ($bulan)                        $filter['bulan']             = $bulan;
        if ($id_wilayah)                   $filter['id_wilayah']        = $id_wilayah;
        if ($status_verifikasi !== '')     $filter['status_verifikasi'] = (int)$status_verifikasi;

        $list = $this->M_Kmt->get_dca_with_details($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DCA');

        $headers = [
            'A' => 'No',
            'B' => 'Tanggal',
            'C' => 'Tanggal Input',
            'D' => 'Wilayah',
            'E' => 'ABM',
            'F' => 'MDO',
            'G' => 'Tanggal kasbon',
            'H' => 'Tanggal DCA',
            'I' => 'Uraian',
            'J' => 'UM',
            'K' => 'Refund',
            'L' => 'nama kegiatan',
            'M' => 'daftar peserta',
            'N' => 'Qty Bisi',
            'O' => 'Qty 235',
            'P' => 'Real Biaya (rincian)',
            'Q' => 'Real Biaya',
            'R' => 'Total',
            'S' => 'Status Verifikasi',
            'T' => 'Note sebelum verifikasi'
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
        }

        $sheet->getStyle('A1:T1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri', 'size' => 11],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $r = 2;
        $no = 1;
        foreach ($list as $row) {
            $details = $row['detail'] ?? [];
            $status_verif = ((int)$row['status_verifikasi'] === 1)
                ? '✓ Terverifikasi (' . (!empty($row['nama_verifikator']) ? $row['nama_verifikator'] : '-') . ')'
                : 'Belum Diverifikasi';

            $tgl_dca_fmt   = !empty($row['tanggal_dca']) ? date('d/m/Y', strtotime($row['tanggal_dca'])) : '-';
            $tgl_input_fmt = !empty($row['created_at'])  ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-';

            if (empty($details)) {
                $sheet->setCellValue('A' . $r, $no);
                $sheet->setCellValue('B' . $r, $tgl_dca_fmt);
                $sheet->setCellValue('C' . $r, $tgl_input_fmt);
                $sheet->setCellValue('D' . $r, $row['nama_wilayah'] ?? '-');
                $sheet->setCellValue('E' . $r, $row['abm'] ?? '-');
                $sheet->setCellValue('F' . $r, $row['nama_mdo'] ?? '');
                $sheet->setCellValue('G' . $r, '');
                $sheet->setCellValue('H' . $r, '');
                $sheet->setCellValue('I' . $r, $row['uraian'] ?? '');
                $sheet->setCellValue('J' . $r, (float)$row['um']);
                $sheet->setCellValue('K' . $r, (float)$row['refund']);
                $sheet->setCellValue('L' . $r, '');
                $sheet->setCellValue('M' . $r, '');
                $sheet->setCellValue('N' . $r, '');
                $sheet->setCellValue('O' . $r, '');
                $sheet->setCellValue('P' . $r, '');
                $sheet->setCellValue('Q' . $r, (float)$row['real_biaya']);
                $sheet->setCellValue('R' . $r, (float)$row['total_biaya']);
                $sheet->setCellValue('S' . $r, $status_verif);
                $sheet->setCellValue('T' . $r, $row['verified_notes'] ?? '');
                $r++;
            } else {
                foreach ($details as $det) {
                    $sheet->setCellValue('A' . $r, $no);
                    $sheet->setCellValue('B' . $r, $tgl_dca_fmt);
                    $sheet->setCellValue('C' . $r, $tgl_input_fmt);
                    $sheet->setCellValue('D' . $r, $row['nama_wilayah'] ?? '-');
                    $sheet->setCellValue('E' . $r, $row['abm'] ?? '-');
                    $sheet->setCellValue('F' . $r, $row['nama_mdo'] ?? '');
                    $sheet->setCellValue('G' . $r, !empty($det['tgl_kasbon']) && $det['tgl_kasbon'] != '0000-00-00' ? date('d/m/Y', strtotime($det['tgl_kasbon'])) : '');
                    $sheet->setCellValue('H' . $r, !empty($det['tgl_kegiatan']) && $det['tgl_kegiatan'] != '0000-00-00' ? date('d/m/Y', strtotime($det['tgl_kegiatan'])) : $tgl_dca_fmt);
                    $sheet->setCellValue('I' . $r, $row['uraian'] ?? '');
                    $sheet->setCellValue('J' . $r, (float)$row['um']);
                    $sheet->setCellValue('K' . $r, (float)$row['refund']);
                    $sheet->setCellValue('L' . $r, $det['nama_kegiatan'] ?? '');
                    $sheet->setCellValue('M' . $r, !empty($det['jml_peserta']) ? (int)$det['jml_peserta'] : '');
                    $sheet->setCellValue('N' . $r, !empty($det['qty_bisi']) && (float)$det['qty_bisi'] > 0 ? (float)$det['qty_bisi'] : '');
                    $sheet->setCellValue('O' . $r, !empty($det['qty_q235']) && (float)$det['qty_q235'] > 0 ? (float)$det['qty_q235'] : '');
                    $sheet->setCellValue('P' . $r, (float)($det['real_biaya'] ?? 0));
                    $sheet->setCellValue('Q' . $r, (float)$row['real_biaya']);
                    $sheet->setCellValue('R' . $r, (float)$row['total_biaya']);
                    $sheet->setCellValue('S' . $r, $status_verif);
                    $sheet->setCellValue('T' . $r, $row['verified_notes'] ?? '');
                    $r++;
                }
            }
            $no++;
        }

        $lastRow = $r - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:T{$lastRow}")->applyFromArray([
                'font'      => ['name' => 'Calibri', 'size' => 11],
                'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
            ]);
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M2:O{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->getStyle("J2:K{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("P2:R{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'DCA_KMT_Detail_Rincian_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
 
    public function rekap() {
        $tgl_kasbon_list = $this->M_Kmt->get_dca_tgl_kasbon_list();
        $tgl_kasbon_values = array_column($tgl_kasbon_list, 'tgl_kasbon');
        $tgl_kasbon_default = $tgl_kasbon_values[0] ?? date('Y-m-d');
        $tgl_kasbon = $this->get_tgl_kasbon_filter($tgl_kasbon_default);
        if ($tgl_kasbon_values && !in_array($tgl_kasbon, $tgl_kasbon_values, true)) {
            $tgl_kasbon = $tgl_kasbon_default;
        }
        $filter = ['tgl_kasbon' => $tgl_kasbon];
 
        $rekap_data = $this->M_Kmt->get_dca_rekap($filter);
 
        $grouped = [];
        foreach ($rekap_data as $dca) {
            $abm_key = trim((string)$dca['abm']) ?: '-';
            $mdo_key = trim((string)$dca['nama_mdo']) ?: '-';
            if (!isset($grouped[$abm_key])) {
                $grouped[$abm_key] = [
                    'um' => 0,
                    'wilayah' => $dca['nama_wilayah'] ?? '-',
                    'uraian' => [],
                    'tanggal_dca' => [],
                    'tanggal_kasbon' => [],
                    'mdo_names' => [],
                    'product_counts' => [],
                    'activity_codes' => [],
                    'kegiatan' => [],
                ];
            }
            $grouped[$abm_key]['um'] += (float)$dca['um'];
            if ($mdo_key !== '-') $grouped[$abm_key]['mdo_names'][strtoupper($mdo_key)] = $mdo_key;
            $product_key = $this->get_dca_product_key($dca['uraian'] ?? '');
            if (!isset($grouped[$abm_key]['product_counts'][$product_key])) {
                $grouped[$abm_key]['product_counts'][$product_key] = 0;
            }
            $grouped[$abm_key]['product_counts'][$product_key]++;
            if (!empty($dca['uraian']) && $dca['uraian'] !== 'Multi Kegiatan') {
                $grouped[$abm_key]['uraian'][$dca['uraian']] = $dca['uraian'];
            }
            if (!empty($dca['tanggal_dca']) && $dca['tanggal_dca'] !== '0000-00-00') {
                $grouped[$abm_key]['tanggal_dca'][] = $dca['tanggal_dca'];
            }
            foreach ($dca['detail'] as $det) {
                $kg_key = trim((string)$det['nama_kegiatan']) ?: '-';
                $activity_code = $this->get_dca_activity_code($kg_key);
                if ($activity_code) $grouped[$abm_key]['activity_codes'][$activity_code] = true;
                if (!isset($grouped[$abm_key]['kegiatan'][$kg_key])) {
                    $grouped[$abm_key]['kegiatan'][$kg_key] = [
                        'rows' => [], 'total_bisi' => 0, 'total_q235' => 0,
                        'total_peserta' => 0, 'total_biaya' => 0,
                    ];
                }
                if (!empty($det['tgl_kasbon']) && $det['tgl_kasbon'] !== '0000-00-00') {
                    $grouped[$abm_key]['tanggal_kasbon'][] = $det['tgl_kasbon'];
                }
                $mdo_row_key = mb_strtoupper(preg_replace('/\s+/', ' ', trim($mdo_key)));
                if (!isset($grouped[$abm_key]['kegiatan'][$kg_key]['rows'][$mdo_row_key])) {
                    $grouped[$abm_key]['kegiatan'][$kg_key]['rows'][$mdo_row_key] = [
                        'nama_mdo' => $mdo_key, 'qty_bisi' => 0, 'qty_q235' => 0,
                        'jml_peserta' => 0, 'real_biaya' => 0,
                    ];
                }
                $mdo_row = &$grouped[$abm_key]['kegiatan'][$kg_key]['rows'][$mdo_row_key];
                $mdo_row['qty_bisi']    += (float)$det['qty_bisi'];
                $mdo_row['qty_q235']    += (float)$det['qty_q235'];
                $mdo_row['jml_peserta'] += (int)$det['jml_peserta'];
                $mdo_row['real_biaya']  += (float)$det['real_biaya'];
                unset($mdo_row);
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_bisi']     += (float)$det['qty_bisi'];
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_q235']    += (float)$det['qty_q235'];
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_peserta'] += (int)$det['jml_peserta'];
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_biaya']   += (float)$det['real_biaya'];
            }
        }
        foreach ($grouped as $abm_nama => &$abm_data) {
            $abm_data['um_label'] = $this->get_dca_um_label($abm_nama, $abm_data);
        }
        unset($abm_data);
 
        $data = [
            'page_title'   => 'Rekapitulasi DCA',
            'grouped'      => $grouped,
            'rekap_data'   => $rekap_data,
            'tgl_kasbon'   => $tgl_kasbon,
            'tgl_kasbon_list' => $tgl_kasbon_list,
            'lv'           => $this->get_lv(),
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
        ];
 
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/rekap', $data);
        $this->load->view('partial/main/footer.php');
    }
    
    public function export_rekap() {
        $tgl_kasbon = $this->get_tgl_kasbon_filter();
        $filter = ['tgl_kasbon' => $tgl_kasbon];
        $no_bukti_values = $this->input->post('no_bukti');
        if (!is_array($no_bukti_values)) $no_bukti_values = [];
        $um_dca_values = $this->input->post('um_dca');
        if (!is_array($um_dca_values)) $um_dca_values = [];
        $note_values = $this->input->post('note');
        if (!is_array($note_values)) $note_values = [];

        $rekap_data = $this->M_Kmt->get_dca_rekap($filter);
        $grouped = [];

        // Format contoh mengelompokkan data: ABM -> kegiatan -> petugas/MDO.
        foreach ($rekap_data as $dca) {
            $abm_key = trim((string)$dca['abm']) ?: '-';
            $mdo_key = trim((string)$dca['nama_mdo']) ?: '-';
            if (!isset($grouped[$abm_key])) {
                $grouped[$abm_key] = [
                    'um' => 0, 'wilayah' => $dca['nama_wilayah'] ?? '-',
                    'uraian' => [], 'tanggal_dca' => [], 'tanggal_kasbon' => [],
                    'mdo_names' => [], 'product_counts' => [], 'activity_codes' => [],
                    'kegiatan' => [],
                ];
            }

            $grouped[$abm_key]['um'] += (float)$dca['um'];
            if ($mdo_key !== '-') $grouped[$abm_key]['mdo_names'][strtoupper($mdo_key)] = $mdo_key;
            $product_key = $this->get_dca_product_key($dca['uraian'] ?? '');
            if (!isset($grouped[$abm_key]['product_counts'][$product_key])) {
                $grouped[$abm_key]['product_counts'][$product_key] = 0;
            }
            $grouped[$abm_key]['product_counts'][$product_key]++;
            if (!empty($dca['uraian']) && $dca['uraian'] !== 'Multi Kegiatan') {
                $grouped[$abm_key]['uraian'][$dca['uraian']] = $dca['uraian'];
            }
            if (!empty($dca['tanggal_dca']) && $dca['tanggal_dca'] !== '0000-00-00') {
                $grouped[$abm_key]['tanggal_dca'][] = $dca['tanggal_dca'];
            }

            foreach ($dca['detail'] as $det) {
                $kg_key = trim((string)$det['nama_kegiatan']) ?: '-';
                $activity_code = $this->get_dca_activity_code($kg_key);
                if ($activity_code) $grouped[$abm_key]['activity_codes'][$activity_code] = true;
                if (!isset($grouped[$abm_key]['kegiatan'][$kg_key])) {
                    $grouped[$abm_key]['kegiatan'][$kg_key] = [
                        'rows' => [], 'total_bisi' => 0, 'total_q235' => 0,
                        'total_peserta' => 0, 'total_biaya' => 0,
                    ];
                }
                if (!empty($det['tgl_kasbon']) && $det['tgl_kasbon'] !== '0000-00-00') {
                    $grouped[$abm_key]['tanggal_kasbon'][] = $det['tgl_kasbon'];
                }
                $mdo_row_key = mb_strtoupper(preg_replace('/\s+/', ' ', trim($mdo_key)));
                if (!isset($grouped[$abm_key]['kegiatan'][$kg_key]['rows'][$mdo_row_key])) {
                    $grouped[$abm_key]['kegiatan'][$kg_key]['rows'][$mdo_row_key] = [
                        'nama_mdo' => $mdo_key, 'qty_bisi' => 0, 'qty_q235' => 0,
                        'jml_peserta' => 0, 'real_biaya' => 0,
                    ];
                }
                $mdo_row = &$grouped[$abm_key]['kegiatan'][$kg_key]['rows'][$mdo_row_key];
                $mdo_row['qty_bisi']    += (float)$det['qty_bisi'];
                $mdo_row['qty_q235']    += (float)$det['qty_q235'];
                $mdo_row['jml_peserta'] += (int)$det['jml_peserta'];
                $mdo_row['real_biaya']  += (float)$det['real_biaya'];
                unset($mdo_row);
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_bisi']     += (float)$det['qty_bisi'];
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_q235']    += (float)$det['qty_q235'];
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_peserta'] += (int)$det['jml_peserta'];
                $grouped[$abm_key]['kegiatan'][$kg_key]['total_biaya']   += (float)$det['real_biaya'];
            }
        }

        $nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet_index = 0;

        foreach ($grouped as $abm_nama => $data_abm) {
            $sheet = $sheet_index++ === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();
            $safe_name = mb_substr(preg_replace('/[\\[\\]\\:\\*\\?\\/\\\\]/', '', $abm_nama), 0, 28);
            $base_name = $safe_name ?: 'Sheet'.$sheet_index;
            $suffix = 1;
            while ($spreadsheet->getSheetByName($safe_name ?: $base_name)) {
                $safe_name = mb_substr($base_name, 0, 27).'-'.$suffix++;
            }
            $sheet->setTitle($safe_name ?: 'Sheet'.$sheet_index);
            $sheet->setShowGridLines(false);
            $sheet->getSheetView()->setZoomScale(85);

            $border = ['allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '7F7F7F'],
            ]];
            $base_style = [
                'font' => ['name' => 'Arial', 'size' => 8, 'color' => ['rgb' => '000000']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                           'startColor' => ['rgb' => 'FFFFFF']],
                'borders' => $border,
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ];
            $bold_style = $base_style;
            $bold_style['font']['bold'] = true;
            $number_format = '#,##0;[Red]-#,##0;-';

            foreach ([['A',37.43],['B',14.57],['C',11.71],['D',11.71],
                      ['E',9.14],['F',9.43],['G',12.29]] as $column) {
                $sheet->getColumnDimension($column[0])->setWidth($column[1]);
            }

            $sheet->mergeCells('A1:G1')->setCellValue('A1', 'REKAPITULASI OPERASIONAL');
            $sheet->getStyle('A1:G1')->applyFromArray($bold_style);
            $sheet->getStyle('A1')->getFont()->setSize(12);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getRowDimension(1)->setRowHeight(23.25);

            $sheet->mergeCells('A2:G2')->setCellValue('A2', 'DCA JAGUNG BISI 959, Q 235 (CLING)');
            $sheet->getStyle('A2:G2')->applyFromArray($bold_style);
            $sheet->getStyle('A2')->getFont()->setItalic(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->getRowDimension(2)->setRowHeight(19.5);

            $total_biaya = 0;
            foreach ($data_abm['kegiatan'] as $kg) $total_biaya += $kg['total_biaya'];

            if (isset($um_dca_values[$sheet_index - 1]) && $um_dca_values[$sheet_index - 1] !== '') {
                $clean_um = preg_replace('/[^\d]/', '', (string)$um_dca_values[$sheet_index - 1]);
                if ($clean_um !== '') {
                    $data_abm['um'] = (float)$clean_um;
                }
            }

            $sisa_dana = $data_abm['um'] - $total_biaya;
            $time_kasbon = strtotime($tgl_kasbon);
            $periode = $nama_bulan[(int)date('n', $time_kasbon)].' '.date('Y', $time_kasbon);
            $uraian = array_values($data_abm['uraian']);
            $no_bukti = mb_substr(trim((string)($no_bukti_values[$sheet_index - 1] ?? '')), 0, 100);
            $note_value = trim((string)($note_values[$sheet_index - 1] ?? ''));
            $uraian_um = $this->get_dca_um_label($abm_nama, $data_abm);

            $tanggal_list = $data_abm['tanggal_kasbon'] ?: $data_abm['tanggal_dca'];
            $tanggal_um = '';
            if ($tanggal_list) {
                sort($tanggal_list);
                $time_um = strtotime($tanggal_list[0]);
                $tanggal_um = date('d', $time_um).' '.$nama_bulan[(int)date('n', $time_um)].' '.date('Y', $time_um);
            }

            foreach ([3 => $abm_nama, 4 => 'AREA BUSINESS MANAGER (ABM)',
                      5 => $data_abm['wilayah'], 6 => 'Periode '.$periode] as $row => $value) {
                $sheet->setCellValue("A{$row}", $value);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
            }
            $sheet->setCellValue('B3', 'No Bukti')->mergeCells('C3:G3');
            $sheet->setCellValueExplicit('C3', $no_bukti, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B4', 'Total Biaya')->mergeCells('C4:D4')->mergeCells('E4:G4')->setCellValue('E4', $total_biaya);
            $sheet->setCellValue('B5', 'UM DCA')->mergeCells('C5:D5')->setCellValue('C5', $tanggal_um)
                  ->mergeCells('E5:G5')->setCellValue('E5', $data_abm['um']);
            $sheet->setCellValue('B6', 'Sisa Dana')->mergeCells('C6:D6')->mergeCells('E6:G6')->setCellValue('E6', $sisa_dana);
            $sheet->setCellValue('B7', 'Sisa Bulan Lalu')->mergeCells('C7:D7')->mergeCells('E7:G7');
            $sheet->getStyle('A3:G7')->applyFromArray($base_style);
            $sheet->getStyle('A3:B7')->getFont()->setBold(true);
            $sheet->getStyle('E4:G6')->getFont()->setBold(true);
            $sheet->getStyle('E4:G6')->getAlignment()->setHorizontal('right');
            $sheet->getStyle('E4:G6')->getNumberFormat()->setFormatCode($number_format);
            $sheet->getStyle('E6:G6')->getFont()->getColor()->setRGB('FF0000');
            foreach ([3 => 18.75, 4 => 15.75, 5 => 15.75, 6 => 19.5, 7 => 15.75, 8 => 6.75, 9 => 47.25] as $row => $height) {
                $sheet->getRowDimension($row)->setRowHeight($height);
            }

            $headers = [
                'A' => 'Nama Petugas & Jenis Kegiatan', 'B' => ' DCA Kas Bon',
                'C' => 'DS Bisi 959 (20 x 1Kg)', 'D' => 'DS Q-235 CLING (10 x 1Kg)',
                'E' => 'DS CP Produk', 'F' => ' Jml Peserta', 'G' => 'Tot Biaya',
            ];
            foreach ($headers as $column => $header) $sheet->setCellValue($column.'9', $header);
            $sheet->getStyle('A9:G9')->applyFromArray($bold_style);
            $sheet->getStyle('A9:G9')->getAlignment()->setHorizontal('center')->setWrapText(true);

            $row = 10;
            $intro_rows = [
                [$abm_nama, null, null], ['AREA BUSINESS MANAGER (ABM)', null, null],
                [$uraian_um, null, null], [$abm_nama, $data_abm['um'], 0],
                [$uraian_um.' Total', $data_abm['um'], 0],
                ['AREA BUSINESS MANAGER (ABM) Total', $data_abm['um'], 0],
                ['MARKET DEVELOPMENT OFFICER (MDO)', null, null],
            ];
            foreach ($intro_rows as $intro) {
                $sheet->setCellValue("A{$row}", $intro[0]);
                if ($intro[1] !== null) $sheet->setCellValue("B{$row}", $intro[1]);
                if ($intro[2] !== null) $sheet->setCellValue("G{$row}", $intro[2]);
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($bold_style);
                $sheet->getStyle("B{$row}:G{$row}")->getNumberFormat()->setFormatCode($number_format);
                $sheet->getStyle("B{$row}:G{$row}")->getAlignment()->setHorizontal('right');
                $sheet->getRowDimension($row++)->setRowHeight(19.5);
            }

            foreach ($data_abm['kegiatan'] as $nama_kegiatan => $kegiatan) {
                $sheet->setCellValue("A{$row}", $nama_kegiatan);
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($bold_style);
                $sheet->getRowDimension($row++)->setRowHeight(19.5);

                foreach ($kegiatan['rows'] as $detail) {
                    $sheet->setCellValue("A{$row}", $detail['nama_mdo']);
                    if ($detail['qty_bisi'])     $sheet->setCellValue("C{$row}", $detail['qty_bisi']);
                    if ($detail['qty_q235'])     $sheet->setCellValue("D{$row}", $detail['qty_q235']);
                    // E (DS CP Produk) kosong karena modul DCA belum mempunyai field sumber CP Produk.
                    if ($detail['jml_peserta'])  $sheet->setCellValue("F{$row}", $detail['jml_peserta']);
                    if ($detail['real_biaya'])   $sheet->setCellValue("G{$row}", $detail['real_biaya']);
                    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($base_style);
                    $sheet->getStyle("B{$row}:G{$row}")->getAlignment()->setHorizontal('right');
                    $sheet->getStyle("B{$row}:G{$row}")->getNumberFormat()->setFormatCode($number_format);
                    $sheet->getRowDimension($row++)->setRowHeight(19.5);
                }

                $sheet->setCellValue("A{$row}", $nama_kegiatan.' Total');
                if ($kegiatan['total_bisi'])     $sheet->setCellValue("C{$row}", $kegiatan['total_bisi']);
                if ($kegiatan['total_q235'])     $sheet->setCellValue("D{$row}", $kegiatan['total_q235']);
                if ($kegiatan['total_peserta'])  $sheet->setCellValue("F{$row}", $kegiatan['total_peserta']);
                if ($kegiatan['total_biaya'])    $sheet->setCellValue("G{$row}", $kegiatan['total_biaya']);
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($bold_style);
                $sheet->getStyle("B{$row}:G{$row}")->getAlignment()->setHorizontal('right');
                $sheet->getStyle("B{$row}:G{$row}")->getNumberFormat()->setFormatCode($number_format);
                $sheet->getRowDimension($row++)->setRowHeight(19.5);
            }

            $total_bisi = $total_q235 = $total_peserta = 0;
            foreach ($data_abm['kegiatan'] as $kegiatan) {
                $total_bisi     += $kegiatan['total_bisi'];
                $total_q235     += $kegiatan['total_q235'];
                $total_peserta  += $kegiatan['total_peserta'];
            }
            $grand_total_biaya = min($total_biaya, $data_abm['um']);
            $total_rows = [
                ['MARKET DEVELOPMENT OFFICER (MDO) Total', null, $total_biaya],
                [$abm_nama.' Total', $data_abm['um'], $total_biaya],
                ['Grand Total', $data_abm['um'], $grand_total_biaya],
            ];
            foreach ($total_rows as $total_row) {
                $sheet->setCellValue("A{$row}", $total_row[0]);
                if ($total_row[1] !== null) $sheet->setCellValue("B{$row}", $total_row[1]);
                if ($total_bisi)    $sheet->setCellValue("C{$row}", $total_bisi);
                if ($total_q235)    $sheet->setCellValue("D{$row}", $total_q235);
                if ($total_peserta) $sheet->setCellValue("F{$row}", $total_peserta);
                if ($total_row[2])  $sheet->setCellValue("G{$row}", $total_row[2]);
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($bold_style);
                $sheet->getStyle("B{$row}:G{$row}")->getAlignment()->setHorizontal('right');
                $sheet->getStyle("B{$row}:G{$row}")->getNumberFormat()->setFormatCode($number_format);
                $sheet->getRowDimension($row++)->setRowHeight(19.5);
            }

            $table_data_end_row = $row - 1;

            if (!empty($note_value)) {
                $row++; // Spacing row
                $sheet->setCellValue("A{$row}", "NOTE / CATATAN:");
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 8, 'bold' => true, 'color' => ['rgb' => '000000']],
                ]);
                $sheet->getRowDimension($row++)->setRowHeight(16);

                $sheet->setCellValue("A{$row}", $note_value);
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 8, 'italic' => true, 'color' => ['rgb' => '333333']],
                    'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '7F7F7F'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(36);
            }

            $last_row = !empty($note_value) ? $row : $table_data_end_row;
            $sheet->getStyle("A9:A{$last_row}")->getAlignment()->setWrapText(true);
            $sheet->freezePane('A10');
            $sheet->setAutoFilter("A9:G{$table_data_end_row}");
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
            $sheet->getPageSetup()->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 9);
            $sheet->getPageSetup()->setPrintArea("A1:G{$last_row}");
            $sheet->getPageMargins()->setTop(0.25)->setRight(0.25)->setLeft(0.25)->setBottom(0.25);
        }

        if (empty($grouped)) {
            $spreadsheet->getActiveSheet()->setTitle('Rekap DCA')
                ->setCellValue('A1', 'Tidak ada data DCA untuk filter yang dipilih.');
        }

        $filename = 'Rekap_DCA_KMT_' . date('d-m-Y', strtotime($tgl_kasbon)) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Format export rekap sebelum penyesuaian workbook operasional.
     * Dipertahankan sementara sebagai referensi internal.
     */
    private function export_rekap_lama() {
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
    
            $WHITE = 'FFFFFF';
            $BLACK = '000000';
            $num_fmt = '#,##0';
    
            $boldWhite = new \PhpOffice\PhpSpreadsheet\Style\Font();
            $boldWhite->setBold(true)->setColor(
                (new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB($WHITE)
            )->setName('Arial')->setSize(10);
    
            $borderThin = ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => $BLACK]]];
            $borderMedium = ['allBorders' => ['borderStyle' => 'medium', 'color' => ['rgb' => $BLACK]]];

            $styleBase = [
                'font'    => ['name' => 'Arial', 'size' => 10, 'bold' => false, 'color' => ['rgb' => $BLACK]],
                'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $WHITE]],
                'borders' => $borderThin,
            ];
            $styleBold = array_merge($styleBase, [
                'font' => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['rgb' => $BLACK]],
            ]);
            $styleHeader = array_merge($styleBold, [
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);
            $styleLeft    = array_merge($styleBase,  ['alignment' => ['horizontal' => 'left',  'vertical' => 'center']]);
            $styleBoldLeft = array_merge($styleBold, ['alignment' => ['horizontal' => 'left',  'vertical' => 'center']]);
            $styleRight   = array_merge($styleBase,  ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']]);

            // Semua level pakai style yang sama — hanya bold yang membedakan
            $styleUM         = $styleBoldLeft;
            $styleKegiatan   = $styleBoldLeft;
            $styleSubtotal   = $styleBoldLeft;
            $styleMDO        = $styleBoldLeft;
            $styleDetail     = array_merge($styleLeft, ['font' => ['name'=>'Arial','size'=>9,'bold'=>false,'color'=>['rgb'=>$BLACK]]]);
            $styleGrandTotal = array_merge($styleBoldLeft, ['borders' => $borderMedium]);
    
            // ── Column widths ───────────────────────────────────────
            foreach ([['A',44],['B',15],['C',16],['D',16],['E',12],['F',12],['G',16]] as [$c,$w]) {
                $sheet->getColumnDimension($c)->setWidth($w);
            }
    
            // ── Row 1-2: Judul ──────────────────────────────────────────
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'REKAPITULASI DCA KMT CORN');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold'=>true,'color'=>['rgb'=>$BLACK],'size'=>14,'name'=>'Arial'],
                'fill'      => ['fillType'=>'solid','startColor'=>['rgb'=>$WHITE]],
                'alignment' => ['horizontal'=>'center','vertical'=>'center'],
                'borders'   => $borderMedium,
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

            $sheet->mergeCells('A2:G2');
            $sheet->setCellValue('A2', 'DCA JAGUNG BISI 959 & Q-235 CLING');
            $sheet->getStyle('A2')->applyFromArray([
                'font'      => ['bold'=>true,'color'=>['rgb'=>$BLACK],'size'=>11,'name'=>'Arial'],
                'fill'      => ['fillType'=>'solid','startColor'=>['rgb'=>$WHITE]],
                'alignment' => ['horizontal'=>'center','vertical'=>'center'],
                'borders'   => $borderThin,
            ]);
            $sheet->getRowDimension(2)->setRowHeight(22);

            // ── Rows 3-6: Info ──────────────────────────────────────────
            $um_total  = $abm_data['um'];
            $tot_biaya = 0;
            foreach ($abm_data['mdo'] as $mdo_nm => $mdo_data) {
                foreach ($mdo_data['kegiatan'] as $kg) $tot_biaya += $kg['total_biaya'];
            }
            $sisa_dana = $um_total - $tot_biaya;
            $periode   = ($bulan ? $nama_bulan[(int)$bulan].' ' : '') . $tahun;

            $info = [
                [3, 'ABM',     $abm_nama],
                [4, 'MDO',     implode(', ', array_keys($abm_data['mdo']))],
                [5, 'Wilayah', $abm_data['wilayah'] ?? '-'],
                [6, 'Periode', $periode],
            ];
            foreach ($info as [$r, $lbl, $val]) {
                $sheet->getRowDimension($r)->setRowHeight(18);
                $sheet->setCellValue("A{$r}", $lbl);
                $sheet->getStyle("A{$r}")->applyFromArray($styleBoldLeft);
                $sheet->mergeCells("B{$r}:D{$r}");
                $sheet->setCellValue("B{$r}", $val);
                $sheet->getStyle("B{$r}")->applyFromArray($styleLeft);
            }

            // Info kanan
            $info_r = [
                [3, 'Total Biaya', $tot_biaya],
                [4, 'UM DCA',      $um_total],
                [5, 'Sisa Dana',   $sisa_dana],
            ];
            foreach ($info_r as [$r, $lbl, $val]) {
                $sheet->mergeCells("E{$r}:F{$r}");
                $sheet->setCellValue("E{$r}", $lbl);
                $sheet->getStyle("E{$r}")->applyFromArray($styleBoldLeft);
                $sheet->setCellValue("G{$r}", $val);
                $sheet->getStyle("G{$r}")->applyFromArray($styleRight);
                $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                $sheet->getStyle("G{$r}")->getFont()->setBold(true);
            }

            // ── Row 7: spacer, Row 8: thead ─────────────────────────────
            $sheet->getRowDimension(7)->setRowHeight(5);
            $sheet->getRowDimension(8)->setRowHeight(30);
            foreach ([
                ['A','Nama Petugas & Jenis Kegiatan'],
                ['B','DCA Kas Bon'],
                ['C','DS Bisi 959 (20x1Kg)'],
                ['D','DS Q-235 CLING (10x1Kg)'],
                ['E','Jml Peserta'],
                ['F','Qty Terjual'],
                ['G','Total Biaya'],
            ] as [$c, $h]) {
                $sheet->setCellValue("{$c}8", $h);
                $sheet->getStyle("{$c}8")->applyFromArray($styleHeader);
                $sheet->getStyle("{$c}8")->getFont()->setBold(true);
            }

            // ── Data rows ────────────────────────────────────────────────
            $r = 9;

            foreach ($abm_data['mdo'] as $mdo_nm => $mdo_data) {
                // UM row
                $sheet->setCellValue("A{$r}", "UM {$mdo_nm} (BFM,FM,FFD,ODP)");
                $sheet->setCellValue("B{$r}", $um_total);
                $sheet->setCellValue("G{$r}", 0);
                $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleUM);
                foreach (['B','G'] as $nc) {
                    $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                    $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                }
                $sheet->getRowDimension($r)->setRowHeight(18);
                $r++;

                foreach ($mdo_data['kegiatan'] as $kg_nm => $kg_data) {
                    // Kegiatan header
                    $sheet->setCellValue("A{$r}", $kg_nm);
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleKegiatan);
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $r++;

                    // Detail rows
                    foreach ($kg_data['rows'] as $det) {
                        $qty_terjual = ($det['qty_bisi'] ?? 0) + ($det['qty_q235'] ?? 0);
                        $sheet->setCellValue("A{$r}", '  '.$det['nama_mdo']);
                        $sheet->setCellValue("C{$r}", $det['qty_bisi']    ?? 0);
                        $sheet->setCellValue("D{$r}", $det['qty_q235']    ?? 0);
                        $sheet->setCellValue("E{$r}", $det['jml_peserta'] ?? 0);
                        $sheet->setCellValue("F{$r}", $qty_terjual);
                        $sheet->setCellValue("G{$r}", $det['real_biaya']  ?? 0);
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleDetail);
                        foreach (['C','D','E','F','G'] as $nc) {
                            $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                            $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                        }
                        $sheet->getRowDimension($r)->setRowHeight(15);
                        $r++;
                    }

                    // Subtotal kegiatan
                    $total_qty = ($kg_data['total_bisi'] ?? 0) + ($kg_data['total_q235'] ?? 0);
                    $sheet->setCellValue("A{$r}", "{$kg_nm} Total");
                    $sheet->setCellValue("C{$r}", $kg_data['total_bisi']    ?? 0);
                    $sheet->setCellValue("D{$r}", $kg_data['total_q235']    ?? 0);
                    $sheet->setCellValue("E{$r}", $kg_data['total_peserta'] ?? 0);
                    $sheet->setCellValue("F{$r}", $total_qty);
                    $sheet->setCellValue("G{$r}", $kg_data['total_biaya']   ?? 0);
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleSubtotal);
                    foreach (['C','D','E','F','G'] as $nc) {
                        $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                        $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                    }
                    $sheet->getRowDimension($r)->setRowHeight(18);
                    $r++;
                }

                // Subtotal MDO
                $mdo_bisi = $mdo_q235 = $mdo_peserta = $mdo_biaya = 0;
                foreach ($mdo_data['kegiatan'] as $kg) {
                    $mdo_bisi    += $kg['total_bisi']    ?? 0;
                    $mdo_q235    += $kg['total_q235']    ?? 0;
                    $mdo_peserta += $kg['total_peserta'] ?? 0;
                    $mdo_biaya   += $kg['total_biaya']   ?? 0;
                }
                $sheet->setCellValue("A{$r}", "MARKET DEVELOPMENT OFFICER (MDO) Total");
                $sheet->setCellValue("C{$r}", $mdo_bisi);
                $sheet->setCellValue("D{$r}", $mdo_q235);
                $sheet->setCellValue("E{$r}", $mdo_peserta);
                $sheet->setCellValue("F{$r}", $mdo_bisi + $mdo_q235);
                $sheet->setCellValue("G{$r}", $mdo_biaya);
                $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleMDO);
                foreach (['C','D','E','F','G'] as $nc) {
                    $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                    $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                }
                $sheet->getRowDimension($r)->setRowHeight(18);
                $r++;
            }

            // Grand Total
            $grand_bisi = $grand_q235 = $grand_peserta = 0;
            foreach ($abm_data['mdo'] as $mdo_data) {
                foreach ($mdo_data['kegiatan'] as $kg) {
                    $grand_bisi    += $kg['total_bisi']    ?? 0;
                    $grand_q235    += $kg['total_q235']    ?? 0;
                    $grand_peserta += $kg['total_peserta'] ?? 0;
                }
            }
            $sheet->setCellValue("A{$r}", "{$abm_nama} Total / Grand Total");
            $sheet->setCellValue("B{$r}", $um_total);
            $sheet->setCellValue("C{$r}", $grand_bisi);
            $sheet->setCellValue("D{$r}", $grand_q235);
            $sheet->setCellValue("E{$r}", $grand_peserta);
            $sheet->setCellValue("F{$r}", $grand_bisi + $grand_q235);
            $sheet->setCellValue("G{$r}", $tot_biaya);
            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray($styleGrandTotal);
            foreach (['B','C','D','E','F','G'] as $nc) {
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

    public function export_detail() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();
        $abm        = $this->input->get('abm')        ?? '';
    
        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;
        if ($abm)        $filter['abm']        = $abm;
    
        // Ambil data rekap (header + detail sudah digabung)
        $rekap_data = $this->M_Kmt->get_dca_rekap($filter);
    
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Kegiatan DCA');
    
        // ── Gaya umum ────────────────────────────────────────────────
        $BLACK    = '000000';
        $WHITE    = 'FFFFFF';
        $BLUE     = '1F3864';
        $LBLUE    = 'BDD7EE';  // biru muda untuk header group
        $YELLOW   = 'FFF2CC';  // kuning muda untuk baris header DCA
        $GREEN    = 'E2EFDA';  // hijau muda untuk subtotal
        $num_fmt  = '#,##0';
        $date_fmt = 'DD/MM/YYYY';
    
        $styleHeaderMain = [
            'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE], 'name' => 'Arial', 'size' => 10],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $BLUE]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => $BLACK]]],
        ];
        $styleHeaderDca = [
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 9],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $YELLOW]],
            'alignment' => ['vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => $BLACK]]],
        ];
        $styleDetail = [
            'font'      => ['name' => 'Arial', 'size' => 9],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $WHITE]],
            'alignment' => ['vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => $BLACK]]],
        ];
        $styleSubtotal = [
            'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 9],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $GREEN]],
            'alignment' => ['vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => $BLACK]]],
        ];
        $styleVerifBadge = [
            'font'  => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'  => ['fillType' => 'solid', 'startColor' => ['rgb' => '28A745']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];
        $styleBelumVerif = [
            'font'  => ['bold' => true, 'color' => ['rgb' => $BLACK], 'size' => 9],
            'fill'  => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFC107']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];
    
        // ── Judul ────────────────────────────────────────────────────
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'DETAIL KEGIATAN DCA KMT CORN');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
    
        $nama_bulan_map = ['','Januari','Februari','Maret','April','Mei','Juni',
                        'Juli','Agustus','September','Oktober','November','Desember'];
        $periode_str = ($bulan ? $nama_bulan_map[(int)$bulan] . ' ' : '') . $tahun;
    
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Periode: ' . $periode_str
            . ($id_wilayah ? '' : ' | Semua Wilayah')
            . ($abm        ? ' | ABM: ' . $abm : ''));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'name' => 'Arial'],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(16);
    
        // ── Header kolom (row 4) ─────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Tanggal DCA',
            'C' => 'Tanggal Input',
            'D' => 'Wilayah',
            'E' => 'ABM',
            'F' => 'MDO',
            'G' => 'Nama Kegiatan',
            'H' => 'Tgl Kegiatan',
            'I' => 'Tgl Kasbon',
            'J' => 'Jml Peserta',
            'K' => 'Qty Bisi 959\n(20x1Kg)',
            'L' => 'Qty Q-235\n(10x1Kg)',
            'M' => 'Qty Total\nTerjual',
            'N' => 'Real Biaya (Rp)',
            'O' => 'Status',
        ];
    
        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->applyFromArray($styleHeaderMain);
        }
        $sheet->getRowDimension(4)->setRowHeight(36);
    
        // ── Lebar kolom ──────────────────────────────────────────────
        $col_widths = [
            'A' =>  5, 'B' => 13, 'C' => 18, 'D' => 16, 'E' => 18,
            'F' => 18, 'G' => 30, 'H' => 13, 'I' => 13, 'J' =>  9,
            'K' => 15, 'L' => 15, 'M' => 15, 'N' => 16, 'O' => 18,
        ];
        foreach ($col_widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    
        // ── Isi data ─────────────────────────────────────────────────
        $r      = 5;
        $no_urut = 1;
    
        foreach ($rekap_data as $dca) {
            $verified     = (int)($dca['status_verifikasi'] ?? 0) === 1;
            $tgl_dca      = date('d/m/Y', strtotime($dca['tanggal_dca']));
            $tgl_input    = !empty($dca['created_at']) ? date('d/m/Y H:i', strtotime($dca['created_at'])) : '-';
            $detail_list  = $dca['detail'] ?? [];
    
            if (empty($detail_list)) {
                // DCA tanpa detail — tetap tampilkan 1 baris kosong
                $sheet->setCellValue('A' . $r, $no_urut++);
                $sheet->setCellValue('B' . $r, $tgl_dca);
                $sheet->setCellValue('C' . $r, $tgl_input);
                $sheet->setCellValue('D' . $r, $dca['nama_wilayah'] ?? '-');
                $sheet->setCellValue('E' . $r, $dca['abm'] ?? '-');
                $sheet->setCellValue('F' . $r, $dca['nama_mdo'] ?? '-');
                $sheet->setCellValue('G' . $r, '(tidak ada detail)');
                $sheet->setCellValue('O' . $r, $verified ? 'Terverifikasi' : 'Belum Verifikasi');
                $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($styleDetail);
                $sheet->getStyle("O{$r}")->applyFromArray($verified ? $styleVerifBadge : $styleBelumVerif);
                $sheet->getRowDimension($r)->setRowHeight(16);
                $r++;
                continue;
            }
    
            // Baris header DCA (warna kuning muda) — tampilkan info header
            $sheet->mergeCells("A{$r}:F{$r}");
            $sheet->setCellValue("A{$r}",
                'DCA ' . $tgl_dca
                . ' | ' . ($dca['nama_wilayah'] ?? '-')
                . ' | ABM: ' . ($dca['abm'] ?? '-')
                . ' | MDO: ' . ($dca['nama_mdo'] ?? '-')
                . ' | UM: Rp ' . number_format($dca['um'] ?? 0, 0, '.', '.')
            );
            $sheet->mergeCells("G{$r}:M{$r}");
            $sheet->setCellValue("G{$r}", 'Uraian: ' . ($dca['uraian'] ?? '-') . ' | Input: ' . $tgl_input);
            $sheet->setCellValue("N{$r}", $dca['total_biaya'] ?? 0);
            $sheet->setCellValue("O{$r}", $verified ? 'Terverifikasi' : 'Belum Verifikasi');
    
            $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($styleHeaderDca);
            $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode($num_fmt);
            $sheet->getStyle("N{$r}")->getAlignment()->setHorizontal('right');
            $sheet->getStyle("O{$r}")->applyFromArray($verified ? $styleVerifBadge : $styleBelumVerif);
            $sheet->getRowDimension($r)->setRowHeight(18);
            $r++;
    
            // Baris detail per kegiatan
            $sub_bisi = $sub_q235 = $sub_peserta = $sub_biaya = 0;
    
            foreach ($detail_list as $det) {
                $qty_total = ($det['qty_bisi'] ?? 0) + ($det['qty_q235'] ?? 0);
    
                $sheet->setCellValue("A{$r}", $no_urut++);
                $sheet->setCellValue("B{$r}", $tgl_dca);
                $sheet->setCellValue("C{$r}", $tgl_input);
                $sheet->setCellValue("D{$r}", $dca['nama_wilayah'] ?? '-');
                $sheet->setCellValue("E{$r}", $dca['abm'] ?? '-');
                $sheet->setCellValue("F{$r}", $dca['nama_mdo'] ?? '-');
                $sheet->setCellValue("G{$r}", $det['nama_kegiatan'] ?? '-');
    
                // Tanggal kegiatan
                if (!empty($det['tgl_kegiatan'])) {
                    $sheet->setCellValue("H{$r}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                        strtotime($det['tgl_kegiatan'])
                    ));
                    $sheet->getStyle("H{$r}")->getNumberFormat()->setFormatCode($date_fmt);
                } else {
                    $sheet->setCellValue("H{$r}", '-');
                }
    
                // Tanggal kasbon
                if (!empty($det['tgl_kasbon'])) {
                    $sheet->setCellValue("I{$r}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                        strtotime($det['tgl_kasbon'])
                    ));
                    $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode($date_fmt);
                } else {
                    $sheet->setCellValue("I{$r}", '-');
                }
    
                $sheet->setCellValue("J{$r}", (int)($det['jml_peserta'] ?? 0));
                $sheet->setCellValue("K{$r}", (float)($det['qty_bisi']  ?? 0));
                $sheet->setCellValue("L{$r}", (float)($det['qty_q235']  ?? 0));
                $sheet->setCellValue("M{$r}", $qty_total);
                $sheet->setCellValue("N{$r}", (float)($det['real_biaya'] ?? 0));
                $sheet->setCellValue("O{$r}", $verified ? 'Terverifikasi' : 'Belum Verifikasi');
    
                $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($styleDetail);
    
                // Format angka kolom numerik
                foreach (['J','K','L','M'] as $nc) {
                    $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                    $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
                }
                $sheet->getStyle("N{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                $sheet->getStyle("N{$r}")->getAlignment()->setHorizontal('right');
                $sheet->getStyle("O{$r}")->applyFromArray($verified ? $styleVerifBadge : $styleBelumVerif);
    
                // Keterangan di kolom F jika ada
                if (!empty($det['keterangan'])) {
                    $sheet->getComment("G{$r}")->getText()->createTextRun($det['keterangan']);
                }
    
                $sheet->getRowDimension($r)->setRowHeight(15);
    
                $sub_bisi    += (float)($det['qty_bisi']  ?? 0);
                $sub_q235    += (float)($det['qty_q235']  ?? 0);
                $sub_peserta += (int)($det['jml_peserta'] ?? 0);
                $sub_biaya   += (float)($det['real_biaya'] ?? 0);
                $r++;
            }
    
            // Baris subtotal per DCA (hijau muda)
            $sheet->mergeCells("A{$r}:F{$r}");
            $sheet->setCellValue("A{$r}", 'SUBTOTAL DCA ' . $tgl_dca
                . ' — ' . ($dca['nama_mdo'] ?? '-'));
            $sheet->setCellValue("J{$r}", $sub_peserta);
            $sheet->setCellValue("K{$r}", $sub_bisi);
            $sheet->setCellValue("L{$r}", $sub_q235);
            $sheet->setCellValue("M{$r}", $sub_bisi + $sub_q235);
            $sheet->setCellValue("N{$r}", $sub_biaya);
            $sheet->setCellValue("O{$r}", '');
    
            $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($styleSubtotal);
            foreach (['J','K','L','M','N'] as $nc) {
                $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
                $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
            }
            $sheet->getRowDimension($r)->setRowHeight(18);
            $r++;
        }
    
        // ── Grand Total ───────────────────────────────────────────────
        // Hitung ulang dari $rekap_data
        $gt_bisi = $gt_q235 = $gt_peserta = $gt_biaya = 0;
        foreach ($rekap_data as $dca) {
            foreach ($dca['detail'] ?? [] as $det) {
                $gt_bisi    += (float)($det['qty_bisi']   ?? 0);
                $gt_q235    += (float)($det['qty_q235']   ?? 0);
                $gt_peserta += (int)($det['jml_peserta']  ?? 0);
                $gt_biaya   += (float)($det['real_biaya'] ?? 0);
            }
        }
    
        $sheet->mergeCells("A{$r}:I{$r}");
        $sheet->setCellValue("A{$r}", 'GRAND TOTAL');
        $sheet->setCellValue("J{$r}", $gt_peserta);
        $sheet->setCellValue("K{$r}", $gt_bisi);
        $sheet->setCellValue("L{$r}", $gt_q235);
        $sheet->setCellValue("M{$r}", $gt_bisi + $gt_q235);
        $sheet->setCellValue("N{$r}", $gt_biaya);
    
        $styleGrandTotal = [
            'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE], 'name' => 'Arial', 'size' => 10],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $BLUE]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'medium', 'color' => ['rgb' => $BLACK]]],
        ];
        $sheet->getStyle("A{$r}:O{$r}")->applyFromArray($styleGrandTotal);
        foreach (['J','K','L','M','N'] as $nc) {
            $sheet->getStyle("{$nc}{$r}")->getNumberFormat()->setFormatCode($num_fmt);
            $sheet->getStyle("{$nc}{$r}")->getAlignment()->setHorizontal('right');
        }
        $sheet->getRowDimension($r)->setRowHeight(22);
    
        // ── Freeze & Page setup ───────────────────────────────────────
        $sheet->freezePane('A5');
        $sheet->getPageSetup()->setOrientation('landscape');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
    
        // Header print
        $sheet->getHeaderFooter()->setOddHeader(
            '&C&B&14DETAIL KEGIATAN DCA KMT CORN — ' . $periode_str
        );
        $sheet->getHeaderFooter()->setOddFooter(
            '&LDicetak: ' . date('d/m/Y H:i') . '&RHalaman &P dari &N'
        );
    
        // ── Output file ───────────────────────────────────────────────
        $periode_file = ($bulan ? 'Bln' . $bulan . '_' : '') . $tahun;
        $filename     = 'Detail_Kegiatan_DCA_KMT_' . $periode_file . '.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
