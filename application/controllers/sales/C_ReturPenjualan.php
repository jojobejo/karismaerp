<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * C_ReturPenjualan.php
 *
 * Controller untuk modul Surat Perintah Retur (SPR) / Surat Pengajuan Retur Barang.
 *
 * Alur approval:
 *   SC (buat & submit SPR)
 *     → Koor SC (verifikasi / tolak)
 *       → Admin Stock (cek fisik / tolak)
 *         → Kadep SC (setuju / tolak)
 *           → Logistik (proses / selesai)
 */
class C_ReturPenjualan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_ReturPenjualan');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);

        // Pastikan user sudah login
        if (!$this->session->userdata('username') && !$this->session->userdata('nm_karyawan')) {
            redirect('Auth');
        }
    }

    // ================================================================
    // HELPER — SESSION
    // ================================================================

    private function _getUser()
    {
        $nama = $this->session->userdata('nm_karyawan')
             ?? $this->session->userdata('nama')
             ?? $this->session->userdata('name')
             ?? $this->session->userdata('username')
             ?? 'system';

        $jobdesk = strtoupper((string) ($this->session->userdata('jobdesk') ?? ''));

        return [
            'nama'     => $nama,
            'jobdesk'  => $jobdesk,
            'username' => $this->session->userdata('username') ?? $nama,
        ];
    }

    private function _isJobdesk(array $allowed)
    {
        $jobdesk = strtoupper((string) ($this->session->userdata('jobdesk') ?? ''));
        $allowed = array_map('strtoupper', $allowed);
        return in_array($jobdesk, $allowed, true);
    }

    /** SC / Sales Counter — pembuat SPR */
    private function _isSC()
    {
        return $this->_isJobdesk(['SC', 'SALESCOUNTER']);
    }

    /** Koordinator SC */
    private function _isKoorSC()
    {
        return $this->_isJobdesk(['KOORSC', 'ADMINSC', 'ADMIN']);
    }

    /** Admin Stock */
    private function _isAdminStock()
    {
        return $this->_isJobdesk(['ADMINSTOCK', 'ADMIN', 'LOGISTIK']);
    }

    /** Kepala Departemen SC */
    private function _isKadepSC()
    {
        return $this->_isJobdesk(['KADEPSC', 'KADEP', 'ADMIN', 'MANAGER']);
    }

    /** Logistik */
    private function _isLogistik()
    {
        return $this->_isJobdesk(['LOGISTIK', 'ADMIN']);
    }

    /** Cek apakah boleh lihat semua SPR (bukan SC biasa) */
    private function _canSeeAll()
    {
        return !$this->_isSC() || $this->_isJobdesk(['ADMIN']);
    }

    private function _denyAccess($msg = 'Anda tidak memiliki akses ke halaman ini.')
    {
        $this->session->set_flashdata('error', $msg);
        redirect('retur_penjualan');
    }

    // ================================================================
    // INDEX — LIST SPR (SC melihat milik sendiri)
    // ================================================================

    public function index()
    {
        $user   = $this->_getUser();
        $filter = [
            'date1'       => $this->input->get('date1', true) ?: $this->input->post('date1'),
            'date2'       => $this->input->get('date2', true) ?: $this->input->post('date2'),
            'status'      => $this->input->get('status', true) ?: $this->input->post('status'),
            'kd_customer' => $this->input->get('kd_customer', true) ?: $this->input->post('kd_customer'),
        ];

        // SC hanya lihat milik sendiri
        if ($this->_isSC() && !$this->_isJobdesk(['ADMIN'])) {
            $filter['create_by'] = $user['nama'];
        }

        $data['page_title']  = 'KARISMA — Retur Penjualan (SPR)';
        $data['spr_list']    = $this->M_ReturPenjualan->get_all_spr($filter);
        $data['customers']   = $this->M_ReturPenjualan->get_customers();
        $data['filter']      = $filter;
        $data['user']        = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // CREATE — Form buat SPR baru (SC)
    // ================================================================

    public function create()
    {
        $user = $this->_getUser();

        // Cek akses: SC atau admin
        if (!$this->_isSC() && !$this->_isJobdesk(['ADMIN','KOORSC','ADMINSC'])) {
            $this->_denyAccess('Hanya SC yang dapat membuat SPR.');
            return;
        }

        $data['page_title']  = 'KARISMA — Buat SPR Baru';
        $data['customers']   = $this->M_ReturPenjualan->get_customers(
            $this->_isSC() ? $user['nama'] : null
        );
        $data['user']        = $user;
        $data['no_spr']      = $this->M_ReturPenjualan->generate_no_spr();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // STORE — Simpan SPR baru
    // ================================================================

    public function store()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/create');
            return;
        }

        $user = $this->_getUser();

        $tanggal      = $this->input->post('tanggal');
        $kd_customer  = $this->input->post('kd_customer');
        $nama_customer= $this->input->post('nama_customer');
        $alamat       = $this->input->post('alamat');
        $nama_sales   = $this->input->post('nama_sales');
        $catatan      = $this->input->post('catatan');
        $as_draft     = $this->input->post('as_draft'); // simpan sbg draft

        // Validasi minimal
        if (empty($tanggal)) {
            $this->session->set_flashdata('error', 'Tanggal wajib diisi.');
            redirect('retur_penjualan/create');
            return;
        }

        // ---- Header SPR ----
        $no_spr  = $this->M_ReturPenjualan->generate_no_spr();
        $status  = $as_draft ? 'draft' : 'diajukan';

        $header = [
            'no_spr'        => $no_spr,
            'tanggal'       => $tanggal,
            'kd_customer'   => $kd_customer,
            'nama_customer' => $nama_customer,
            'alamat'        => $alamat,
            'nama_sales'    => $nama_sales,
            'catatan'       => $catatan,
            'status'        => $status,
            'create_by'     => $user['nama'],
            'create_at'     => date('Y-m-d H:i:s'),
        ];

        $id_spr = $this->M_ReturPenjualan->save_spr($header);

        if (!$id_spr) {
            $this->session->set_flashdata('error', 'Gagal menyimpan SPR. Silakan coba lagi.');
            redirect('retur_penjualan/create');
            return;
        }

        // ---- Detail Barang ----
        $this->_saveDetail($id_spr);

        $msg = $as_draft
            ? "SPR <strong>{$no_spr}</strong> berhasil disimpan sebagai Draft."
            : "SPR <strong>{$no_spr}</strong> berhasil diajukan dan menunggu verifikasi Koor SC.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/detail/' . $id_spr);
    }

    /**
     * Simpan detail item dari POST array.
     * POST keys: nama_barang[], no_faktur[], no_batch[], qty[],
     *            alasan_brg_bermasalah[], alasan_brg_bermasalah_opt[], dst.
     */
    private function _saveDetail($id_spr)
    {
        $nama_barang    = $this->input->post('nama_barang')    ?: [];
        $no_faktur      = $this->input->post('no_faktur')      ?: [];
        $no_batch       = $this->input->post('no_batch')       ?: [];
        $qty            = $this->input->post('qty')            ?: [];

        // Data alasan retur sekarang dikirim sebagai input tunggal (global untuk 1 SPR)
        $alasan_brg         = $this->input->post('alasan_brg_bermasalah') ? 1 : 0;
        $alasan_brg_opt     = $this->input->post('alasan_brg_bermasalah_opt') ?: '';
        $alasan_exp         = $this->input->post('alasan_expired') ? 1 : 0;
        $alasan_exp_opt     = $this->input->post('alasan_expired_opt') ?: '';
        $alasan_tidak_laku  = $this->input->post('alasan_tidak_laku') ? 1 : 0;
        $alasan_tes         = $this->input->post('alasan_tes_market') ? 1 : 0;
        $alasan_bad_debt    = $this->input->post('alasan_bad_debt') ? 1 : 0;
        $alasan_harga       = $this->input->post('alasan_harga_tidak_sesuai') ? 1 : 0;
        $alasan_spr_intern  = $this->input->post('alasan_spr_intern') ? 1 : 0;
        $alasan_lainlain    = $this->input->post('alasan_lainlain') ?: '';

        $rows = [];
        foreach ($nama_barang as $i => $nb) {
            if (empty($nb)) continue;
            $rows[] = [
                'id_spr'                    => $id_spr,
                'no_urut'                   => $i + 1,
                'nama_barang'               => $nb,
                'no_faktur'                 => $no_faktur[$i] ?? '',
                'no_batch'                  => $no_batch[$i]  ?? '',
                'qty'                       => (float) ($qty[$i] ?? 0),
                'alasan_brg_bermasalah'     => $alasan_brg,
                'alasan_brg_bermasalah_opt' => $alasan_brg_opt,
                'alasan_expired'            => $alasan_exp,
                'alasan_expired_opt'        => $alasan_exp_opt,
                'alasan_tidak_laku'         => $alasan_tidak_laku,
                'alasan_tes_market'         => $alasan_tes,
                'alasan_bad_debt'           => $alasan_bad_debt,
                'alasan_harga_tidak_sesuai' => $alasan_harga,
                'alasan_spr_intern'         => $alasan_spr_intern,
                'alasan_lainlain'           => $alasan_lainlain,
            ];
        }

        if (!empty($rows)) {
            $this->M_ReturPenjualan->save_spr_detail($rows);
        }
    }

    // ================================================================
    // DETAIL — Lihat detail SPR
    // ================================================================

    public function detail($id_spr)
    {
        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr) {
            $this->session->set_flashdata('error', 'SPR tidak ditemukan.');
            redirect('retur_penjualan');
            return;
        }

        $user = $this->_getUser();

        $data['page_title']  = 'KARISMA — Detail SPR ' . $spr['no_spr'];
        $data['spr']         = $spr;
        $data['spr_detail']  = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']        = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // SUBMIT — SC submit SPR dari draft → diajukan
    // ================================================================

    public function submit($id_spr)
    {
        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'draft') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diajukan.');
            redirect('retur_penjualan');
            return;
        }

        $user = $this->_getUser();
        $this->M_ReturPenjualan->update_spr_status($id_spr, 'diajukan', [
            'update_by' => $user['nama'],
        ]);

        $this->session->set_flashdata('success', "SPR <strong>{$spr['no_spr']}</strong> berhasil diajukan ke Koor SC.");
        redirect('retur_penjualan/detail/' . $id_spr);
    }

    // ================================================================
    // KOOR SC — Antrian verifikasi
    // ================================================================

    public function koor_sc()
    {
        if (!$this->_isKoorSC()) {
            $this->_denyAccess('Anda tidak memiliki akses halaman Koor SC.');
            return;
        }

        $filter = [
            'status'  => 'diajukan',
            'date1'   => $this->input->get('date1', true),
            'date2'   => $this->input->get('date2', true),
        ];

        $data['page_title'] = 'KARISMA — Koor SC: Verifikasi SPR';
        $data['spr_list']   = $this->M_ReturPenjualan->get_all_spr($filter);
        $data['filter']     = $filter;
        $data['user']       = $this->_getUser();
        $data['role_label'] = 'Koor SC';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/koor_sc_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function koor_sc_verifikasi($id_spr)
    {
        if (!$this->_isKoorSC()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'diajukan') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan/koor_sc');
            return;
        }

        $data['page_title'] = 'KARISMA — Koor SC: Verifikasi ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'koor_sc';
        $data['back_url']   = base_url('retur_penjualan/koor_sc');
        $data['action_url'] = base_url('retur_penjualan/koor_sc/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function koor_sc_simpan($id_spr)
    {
        if (!$this->_isKoorSC() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/koor_sc');
            return;
        }

        $aksi    = $this->input->post('aksi'); // 'setuju' | 'tolak'
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'diajukan') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan/koor_sc');
            return;
        }

        $new_status = ($aksi === 'setuju') ? 'diverifikasi_koor' : 'ditolak';

        $this->M_ReturPenjualan->update_spr_status($id_spr, $new_status, [
            'koor_sc_by'      => $user['nama'],
            'koor_sc_at'      => date('Y-m-d H:i:s'),
            'koor_sc_catatan' => $catatan,
            'update_by'       => $user['nama'],
        ]);

        $msg = ($aksi === 'setuju')
            ? "SPR <strong>{$spr['no_spr']}</strong> disetujui, lanjut ke Admin Stock."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/koor_sc');
    }

    // ================================================================
    // ADMIN STOCK — Antrian cek fisik
    // ================================================================

    public function admin_stock()
    {
        if (!$this->_isAdminStock()) {
            $this->_denyAccess('Anda tidak memiliki akses halaman Admin Stock.');
            return;
        }

        $filter = [
            'status' => 'diverifikasi_koor',
            'date1'  => $this->input->get('date1', true),
            'date2'  => $this->input->get('date2', true),
        ];

        $data['page_title'] = 'KARISMA — Admin Stock: Cek SPR';
        $data['spr_list']   = $this->M_ReturPenjualan->get_all_spr($filter);
        $data['filter']     = $filter;
        $data['user']       = $this->_getUser();
        $data['role_label'] = 'Admin Stock';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/admin_stock_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_stock_cek($id_spr)
    {
        if (!$this->_isAdminStock()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan/admin_stock');
            return;
        }

        $data['page_title'] = 'KARISMA — Admin Stock: Cek ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'admin_stock';
        $data['back_url']   = base_url('retur_penjualan/admin_stock');
        $data['action_url'] = base_url('retur_penjualan/admin_stock/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_stock_simpan($id_spr)
    {
        if (!$this->_isAdminStock() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/admin_stock');
            return;
        }

        $aksi    = $this->input->post('aksi');
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan/admin_stock');
            return;
        }

        $new_status = ($aksi === 'setuju') ? 'dicek_admin_stock' : 'ditolak';

        $this->M_ReturPenjualan->update_spr_status($id_spr, $new_status, [
            'admin_stock_by'      => $user['nama'],
            'admin_stock_at'      => date('Y-m-d H:i:s'),
            'admin_stock_catatan' => $catatan,
            'update_by'           => $user['nama'],
        ]);

        $msg = ($aksi === 'setuju')
            ? "SPR <strong>{$spr['no_spr']}</strong> dicek Admin Stock, lanjut ke Kadep SC."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak oleh Admin Stock.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/admin_stock');
    }

    // ================================================================
    // KADEP SC — Antrian persetujuan Kepala Departemen
    // ================================================================

    public function kadep_sc()
    {
        if (!$this->_isKadepSC()) {
            $this->_denyAccess('Anda tidak memiliki akses halaman Kadep SC.');
            return;
        }

        $filter = [
            'status' => 'dicek_admin_stock',
            'date1'  => $this->input->get('date1', true),
            'date2'  => $this->input->get('date2', true),
        ];

        $data['page_title'] = 'KARISMA — Kadep SC: Persetujuan SPR';
        $data['spr_list']   = $this->M_ReturPenjualan->get_all_spr($filter);
        $data['filter']     = $filter;
        $data['user']       = $this->_getUser();
        $data['role_label'] = 'Kadep SC';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/kadep_sc_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function kadep_sc_approve($id_spr)
    {
        if (!$this->_isKadepSC()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'dicek_admin_stock') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan/kadep_sc');
            return;
        }

        $data['page_title'] = 'KARISMA — Kadep SC: Setujui ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'kadep_sc';
        $data['back_url']   = base_url('retur_penjualan/kadep_sc');
        $data['action_url'] = base_url('retur_penjualan/kadep_sc/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function kadep_sc_simpan($id_spr)
    {
        if (!$this->_isKadepSC() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/kadep_sc');
            return;
        }

        $aksi    = $this->input->post('aksi');
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'dicek_admin_stock') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan/kadep_sc');
            return;
        }

        $new_status = ($aksi === 'setuju') ? 'disetujui_kadep' : 'ditolak';

        $this->M_ReturPenjualan->update_spr_status($id_spr, $new_status, [
            'kadep_sc_by'      => $user['nama'],
            'kadep_sc_at'      => date('Y-m-d H:i:s'),
            'kadep_sc_catatan' => $catatan,
            'update_by'        => $user['nama'],
        ]);

        $msg = ($aksi === 'setuju')
            ? "SPR <strong>{$spr['no_spr']}</strong> disetujui Kadep SC, lanjut ke Logistik."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak oleh Kadep SC.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/kadep_sc');
    }

    // ================================================================
    // LOGISTIK — Antrian proses retur fisik
    // ================================================================

    public function logistik()
    {
        if (!$this->_isLogistik()) {
            $this->_denyAccess('Anda tidak memiliki akses halaman Logistik.');
            return;
        }

        $filter = [
            'status' => 'disetujui_kadep',
            'date1'  => $this->input->get('date1', true),
            'date2'  => $this->input->get('date2', true),
        ];

        $data['page_title'] = 'KARISMA — Logistik: Proses SPR';
        $data['spr_list']   = $this->M_ReturPenjualan->get_all_spr($filter);
        $data['filter']     = $filter;
        $data['user']       = $this->_getUser();
        $data['role_label'] = 'Logistik';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/logistik_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function logistik_proses($id_spr)
    {
        if (!$this->_isLogistik()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'disetujui_kadep') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan/logistik');
            return;
        }

        $data['page_title'] = 'KARISMA — Logistik: Proses ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'logistik';
        $data['back_url']   = base_url('retur_penjualan/logistik');
        $data['action_url'] = base_url('retur_penjualan/logistik/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function logistik_simpan($id_spr)
    {
        if (!$this->_isLogistik() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/logistik');
            return;
        }

        $aksi    = $this->input->post('aksi');
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'disetujui_kadep') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan/logistik');
            return;
        }

        $new_status = ($aksi === 'selesai') ? 'selesai' : 'ditolak';

        $this->M_ReturPenjualan->update_spr_status($id_spr, $new_status, [
            'logistik_by'      => $user['nama'],
            'logistik_at'      => date('Y-m-d H:i:s'),
            'logistik_catatan' => $catatan,
            'update_by'        => $user['nama'],
        ]);

        $msg = ($aksi === 'selesai')
            ? "SPR <strong>{$spr['no_spr']}</strong> telah selesai diproses oleh Logistik."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak oleh Logistik.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/logistik');
    }

    // ================================================================
    // PRINT SPR
    // ================================================================

    public function print_spr($id_spr)
    {
        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr) {
            $this->session->set_flashdata('error', 'SPR tidak ditemukan.');
            redirect('retur_penjualan');
            return;
        }

        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();

        // Load print view tanpa header/footer sidebar
        $this->load->view('content/sales/retur/spr_print.php', $data);
    }
}
