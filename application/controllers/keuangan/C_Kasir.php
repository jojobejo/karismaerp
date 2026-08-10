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
        $jenis = $this->input->get('jenis') ?: '';

        // Sanitasi jenis agar hanya nilai yang valid diterima
        if (!in_array($jenis, ['kas_masuk', 'kas_keluar', 'penyelesaian_um', ''])) {
            $jenis = '';
        }

        list($tahun, $bln) = explode('-', $bulan);

        $data['page_title']     = 'Kasir - Kas Masuk & Kas Keluar';
        $data['bulan']          = $bulan;
        $data['tahun']          = $tahun;
        $data['bln']            = $bln;
        $data['filter_jenis']   = $jenis;

        // Saldo kasir aktif
        $data['saldo_kasir']    = $this->M_Kasir->get_saldo_kasir_aktif();

        // Hitung saldo aktual dari jurnal
        $data['saldo_aktual']   = 0;
        if ($data['saldo_kasir']) {
            $data['saldo_aktual'] = $this->M_Kasir->hitung_saldo_akun($data['saldo_kasir']->id_akun);
        }

        // Total bulan ini (selalu tampilkan semua termasuk penyelesaian_um)
        $data['total_masuk']    = $this->M_Kasir->total_bulan($bulan, 'kas_masuk');
        $data['total_keluar']   = $this->M_Kasir->total_bulan($bulan, 'kas_keluar');

        // List transaksi bulan ini (dengan filter jenis jika ada)
        $data['transaksi']      = $this->M_Kasir->get_transaksi_bulan($bulan, $jenis);

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
        $nominal_kembali = 0;
        
        if ($jenis === 'penyelesaian_um') {
            $nominal_kembali = (float)str_replace(['.', ','], ['', '.'], (string)$this->input->post('nominal_kembali'));
        }
        
        $keterangan = trim((string)$this->input->post('keterangan'));
        $tanggal   = $this->input->post('tanggal') ?: date('Y-m-d');

        if (!in_array($jenis, ['kas_masuk', 'kas_keluar', 'penyelesaian_um']) || $nominal <= 0 || empty($pilihan)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau nominal tidak valid.']);
            return;
        }

        $userId   = (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0);
        $namaUser = (string)($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'Kasir');

        // Generate nomor transaksi
        $prefix   = 'PUM';
        if ($jenis === 'kas_masuk') $prefix = 'KMK';
        else if ($jenis === 'kas_keluar') $prefix = 'KKR';
        
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
            'nominal_kembali' => $nominal_kembali,
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
    // AJAX: Simpan Kas Masuk dari Kas Keluar yang sudah ada
    // =====================================================
    public function selesaikan_um()
    {
        $id_ref     = (int)$this->input->post('id_ref');
        $nominalRaw = $this->input->post('nominal') ?: $this->input->post('nominal_kembali');
        $nominal    = (float)str_replace(['.', ','], ['', '.'], (string)$nominalRaw);
        $keterangan = trim((string)$this->input->post('keterangan'));
        $tanggal    = $this->input->post('tanggal') ?: date('Y-m-d');

        if ($id_ref <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Transaksi referensi tidak valid.']);
            return;
        }

        if ($nominal <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Nominal Kas Masuk tidak valid.']);
            return;
        }

        // Ambil data transaksi referensi (harus kas_keluar dan belum settled)
        $ref = $this->M_Kasir->get_transaksi_by_id($id_ref);

        if (!$ref) {
            echo json_encode(['status' => 'error', 'message' => 'Transaksi Kas Keluar tidak ditemukan.']);
            return;
        }
        if ($ref['jenis_transaksi'] !== 'kas_keluar') {
            echo json_encode(['status' => 'error', 'message' => 'Hanya transaksi Kas Keluar yang dapat diinput kas masuk kembali.']);
            return;
        }
        if ($ref['is_settled'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Transaksi ini sudah pernah diinput kas masuk pengembaliannya.']);
            return;
        }

        $userId   = (int)($this->session->userdata('id_karyawan') ?: $this->session->userdata('id') ?: 0);
        $namaUser = (string)($this->session->userdata('nama') ?: $this->session->userdata('username') ?: 'Kasir');

        $id_saldo = $this->M_Kasir->get_saldo_kasir_aktif();
        $id_saldo_kasir = $id_saldo ? $id_saldo->id : null;

        $no_transaksi = $this->M_Kasir->generate_no_transaksi('KMK', $tanggal);

        // Simpan record kas_masuk dengan id_ref
        $data = [
            'no_transaksi'    => $no_transaksi,
            'tanggal'         => $tanggal,
            'jenis_transaksi' => 'kas_masuk',
            'pilihan'         => $ref['pilihan'],   // Sama dengan pilihan transaksi Kas Keluar
            'nominal'         => $nominal,          // Nominal Kas Masuk (debit)
            'nominal_kembali' => 0,
            'keterangan'      => $keterangan ?: ('Terima ' . $ref['pilihan'] . ' (' . $ref['no_transaksi'] . ')'),
            'id_user'         => $userId ?: null,
            'id_saldo_kasir'  => $id_saldo_kasir,
            'id_ref'          => $id_ref,
            'nama_user'       => $namaUser,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $ok = $this->M_Kasir->selesaikan_um($data, $id_ref);

        if ($ok) {
            echo json_encode([
                'status'       => 'success',
                'message'      => 'Kas Masuk pengembalian berhasil disimpan.',
                'no_transaksi' => $no_transaksi,
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan Kas Masuk.']);
        }
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

    // =====================================================
    // Halaman Report Mutasi Kasir
    // =====================================================
    public function report_mutasi()
    {
        // Ambil parameter filter tanggal dari GET
        $tanggal_awal  = $this->input->get('tanggal_awal')  ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-d');

        // Pastikan format valid
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal))  $tanggal_awal  = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir)) $tanggal_akhir = date('Y-m-d');

        // Info akun kasir aktif
        $saldo_kasir = $this->M_Kasir->get_saldo_kasir_aktif();
        $id_saldo_kasir = $saldo_kasir ? $saldo_kasir->id : null;

        // Saldo awal sebelum periode yang dipilih
        $saldo_awal = $this->M_Kasir->get_saldo_awal_periode($tanggal_awal, $id_saldo_kasir);

        // Ambil data transaksi dalam periode
        $transaksi = $this->M_Kasir->get_mutasi_kasir($tanggal_awal, $tanggal_akhir, $id_saldo_kasir);

        // Hitung total periode
        $total_periode = $this->M_Kasir->get_total_periode($tanggal_awal, $tanggal_akhir, $id_saldo_kasir);

        // Hitung running balance per baris
        $saldo_berjalan = $saldo_awal;
        foreach ($transaksi as &$row) {
            $saldo_berjalan += (float)$row['debit'] - (float)$row['kredit'];
            $row['saldo_berjalan'] = $saldo_berjalan;
        }
        unset($row);

        $saldo_akhir = $saldo_awal + $total_periode['total_debit'] - $total_periode['total_kredit'];

        $data = [
            'page_title'     => 'Report Mutasi Kasir',
            'tanggal_awal'   => $tanggal_awal,
            'tanggal_akhir'  => $tanggal_akhir,
            'saldo_kasir'    => $saldo_kasir,
            'saldo_awal'     => $saldo_awal,
            'transaksi'      => $transaksi,
            'total_debit'    => $total_periode['total_debit'],
            'total_kredit'   => $total_periode['total_kredit'],
            'saldo_akhir'    => $saldo_akhir,
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/kasir/report_mutasi.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // =====================================================
    // Halaman Print Mutasi Kasir (tanpa layout)
    // =====================================================
    public function print_mutasi()
    {
        $tanggal_awal  = $this->input->get('tanggal_awal')  ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal))  $tanggal_awal  = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir)) $tanggal_akhir = date('Y-m-d');

        $saldo_kasir    = $this->M_Kasir->get_saldo_kasir_aktif();
        $id_saldo_kasir = $saldo_kasir ? $saldo_kasir->id : null;

        $saldo_awal    = $this->M_Kasir->get_saldo_awal_periode($tanggal_awal, $id_saldo_kasir);
        $transaksi     = $this->M_Kasir->get_mutasi_kasir($tanggal_awal, $tanggal_akhir, $id_saldo_kasir);
        $total_periode = $this->M_Kasir->get_total_periode($tanggal_awal, $tanggal_akhir, $id_saldo_kasir);

        // Hitung running balance per baris
        $saldo_berjalan = $saldo_awal;
        foreach ($transaksi as &$row) {
            $saldo_berjalan += (float)$row['debit'] - (float)$row['kredit'];
            $row['saldo_berjalan'] = $saldo_berjalan;
        }
        unset($row);

        $saldo_akhir = $saldo_awal + $total_periode['total_debit'] - $total_periode['total_kredit'];

        $data = [
            'page_title'    => 'Print Mutasi Kasir',
            'tanggal_awal'  => $tanggal_awal,
            'tanggal_akhir' => $tanggal_akhir,
            'saldo_kasir'   => $saldo_kasir,
            'saldo_awal'    => $saldo_awal,
            'transaksi'     => $transaksi,
            'total_debit'   => $total_periode['total_debit'],
            'total_kredit'  => $total_periode['total_kredit'],
            'saldo_akhir'   => $saldo_akhir,
        ];

        $this->load->view('content/keuangan/kasir/print_mutasi.php', $data);
    }
}
