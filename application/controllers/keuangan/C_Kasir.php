<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Kasir
 * Mengelola transaksi kas masuk & kas keluar kasir harian,
 * saldo kasir berdasarkan akun jurnal, dan pilihan kategori transaksi.
 */
class C_Kasir extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->load->model('keuangan/M_Kasir');
        $this->load->helper(['url', 'form']);
    }

    // =====================================================
    // Halaman Utama Kasir
    // =====================================================
    public function index()
    {
        $bulan = $this->input->get('bulan') ?: date('Y-m');
        list($tahun, $bln) = explode('-', $bulan);

        $data['page_title']     = 'Kasir - Kas Masuk & Kas Keluar';
        $data['bulan']          = $bulan;
        $data['tahun']          = $tahun;
        $data['bln']            = $bln;

        // Saldo kasir aktif
        $data['saldo_kasir']    = $this->M_Kasir->get_saldo_kasir_aktif();

        // Hitung saldo aktual dari jurnal
        $data['saldo_aktual']   = 0;
        if ($data['saldo_kasir']) {
            $data['saldo_aktual'] = $this->M_Kasir->hitung_saldo_akun($data['saldo_kasir']->id_akun);
        }

        // Total bulan ini
        $data['total_masuk']    = $this->M_Kasir->total_bulan($bulan, 'kas_masuk');
        $data['total_keluar']   = $this->M_Kasir->total_bulan($bulan, 'kas_keluar');

        // List transaksi bulan ini
        $data['transaksi']      = $this->M_Kasir->get_transaksi_bulan($bulan);

        // Akun kas untuk pilihan saldo
        $data['akun_kas']       = $this->M_Kasir->get_akun_kas();

        // Pilihan kategori transaksi
        $data['pilihan_list']   = $this->M_Kasir->get_all_pilihan();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kasir/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // =====================================================
    // AJAX: Simpan Transaksi Kas Masuk / Kas Keluar
    // =====================================================
    public function simpan_transaksi()
    {
        $jenis     = $this->input->post('jenis_transaksi');
        $pilihan   = trim((string)$this->input->post('pilihan'));
        $nominal   = (float)str_replace(['.', ','], ['', '.'], (string)$this->input->post('nominal'));
        $keterangan = trim((string)$this->input->post('keterangan'));
        $tanggal   = $this->input->post('tanggal') ?: date('Y-m-d');

        if (!in_array($jenis, ['kas_masuk', 'kas_keluar']) || $nominal <= 0 || empty($pilihan)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau nominal tidak valid.']);
            return;
        }

        $userId   = (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0);
        $namaUser = (string)($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'Kasir');

        // Generate nomor transaksi
        $prefix   = $jenis === 'kas_masuk' ? 'KMK' : 'KKR';
        $no_transaksi = $this->M_Kasir->generate_no_transaksi($prefix, $tanggal);

        // Cek jika pilihan baru (belum ada di tbkeu_kasir_pilihan), simpan dulu
        $id_saldo = $this->M_Kasir->get_saldo_kasir_aktif();
        $id_saldo_kasir = $id_saldo ? $id_saldo->id : null;

        $data = [
            'no_transaksi'    => $no_transaksi,
            'tanggal'         => $tanggal,
            'jenis_transaksi' => $jenis,
            'pilihan'         => $pilihan,
            'nominal'         => $nominal,
            'keterangan'      => $keterangan,
            'id_user'         => $userId ?: null,
            'id_saldo_kasir'  => $id_saldo_kasir,
            'nama_user'       => $namaUser,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $ok = $this->M_Kasir->simpan_transaksi($data);

        if ($ok) {
            echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil disimpan.', 'no_transaksi' => $no_transaksi]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan transaksi.']);
        }
    }

    // =====================================================
    // AJAX: Hapus Transaksi
    // =====================================================
    public function hapus_transaksi()
    {
        $id = (int)$this->input->post('id');
        $ok = $id > 0 ? $this->M_Kasir->hapus_transaksi($id) : false;
        echo json_encode(['status' => $ok ? 'success' : 'error']);
    }

    // =====================================================
    // AJAX: Get List Transaksi (untuk filter bulan)
    // =====================================================
    public function get_transaksi()
    {
        $bulan = $this->input->get('bulan') ?: date('Y-m');
        $jenis = $this->input->get('jenis') ?: '';

        $rows = $this->M_Kasir->get_transaksi_bulan($bulan, $jenis);
        echo json_encode(['status' => 'success', 'data' => $rows]);
    }

    // =====================================================
    // AJAX: Set Saldo Kasir (pilih akun jurnal)
    // =====================================================
    public function set_saldo()
    {
        $id_akun = (int)$this->input->post('id_akun');
        if ($id_akun <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Akun tidak valid.']);
            return;
        }

        $akun = $this->M_Kasir->get_akun_by_id($id_akun);
        if (!$akun) {
            echo json_encode(['status' => 'error', 'message' => 'Akun tidak ditemukan.']);
            return;
        }

        $userId = (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0);

        $ok = $this->M_Kasir->set_saldo_kasir($id_akun, $akun->kode_akun, $akun->nama_akun, $userId);
        if ($ok) {
            $saldo = $this->M_Kasir->hitung_saldo_akun($id_akun);
            echo json_encode([
                'status'      => 'success',
                'message'     => 'Saldo kasir berhasil diatur.',
                'nama_akun'   => $akun->nama_akun,
                'kode_akun'   => $akun->kode_akun,
                'saldo'       => $saldo,
                'saldo_fmt'   => 'Rp ' . number_format($saldo, 0, ',', '.'),
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengatur saldo kasir.']);
        }
    }

    // =====================================================
    // AJAX: Get Saldo Aktual
    // =====================================================
    public function get_saldo()
    {
        $saldo_kasir = $this->M_Kasir->get_saldo_kasir_aktif();
        if (!$saldo_kasir) {
            echo json_encode(['status' => 'ok', 'saldo' => 0, 'saldo_fmt' => 'Rp 0', 'akun' => null]);
            return;
        }
        $saldo = $this->M_Kasir->hitung_saldo_akun($saldo_kasir->id_akun);
        echo json_encode([
            'status'    => 'ok',
            'saldo'     => $saldo,
            'saldo_fmt' => 'Rp ' . number_format($saldo, 0, ',', '.'),
            'akun'      => $saldo_kasir->kode_akun . ' - ' . $saldo_kasir->nama_akun,
        ]);
    }

    // =====================================================
    // AJAX: Tambah Pilihan Baru
    // =====================================================
    public function tambah_pilihan()
    {
        $nama = trim((string)$this->input->post('nama_pilihan'));
        if (empty($nama)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama pilihan tidak boleh kosong.']);
            return;
        }

        // Cek duplikat
        $cek = $this->db->where('nama_pilihan', $nama)->count_all_results('tbkeu_kasir_pilihan');
        if ($cek > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Pilihan sudah ada.']);
            return;
        }

        $this->db->insert('tbkeu_kasir_pilihan', [
            'nama_pilihan' => $nama,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $new_id = $this->db->insert_id();
        echo json_encode(['status' => 'success', 'id' => $new_id, 'nama_pilihan' => $nama]);
    }

    // =====================================================
    // AJAX: Hapus Pilihan
    // =====================================================
    public function hapus_pilihan()
    {
        $id = (int)$this->input->post('id');
        if ($id > 0) {
            $this->db->where('id', $id)->delete('tbkeu_kasir_pilihan');
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
}
