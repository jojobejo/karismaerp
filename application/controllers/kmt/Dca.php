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
}
