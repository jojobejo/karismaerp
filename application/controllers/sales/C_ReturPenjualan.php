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
        return $this->_isJobdesk(['ADMSTOCK', 'ADMINSTOCK', 'ADMIN', 'LOGISTIK']);
    }

    /** Kepala Departemen SC */
    private function _isKadepSC()
    {
        return $this->_isJobdesk(['KADEPSC', 'KADEP', 'ADMIN', 'MANAGER']);
    }

    /** Logistik */
    private function _isLogistik()
    {
        // Toleran terhadap variasi ejaan jobdesk di DB
        return $this->_isJobdesk(['LOGISTIK', 'LOGISTIC', 'LOGISTICS', 'ADMIN']);
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

        // Filter based on active queue for each role (except Admin)
        if ($this->_isJobdesk(['ADMIN'])) {
            // Admin can see all
        } elseif ($this->_isSC()) {
            // SC only sees their own
            $filter['create_by'] = $user['nama'];
        } else {
            // Restrict statuses based on role active queues
            $allowed_statuses = [];
            if ($this->_isKoorSC()) {
                $allowed_statuses[] = 'diajukan';
            }
            if ($this->_isAdminStock()) {
                $allowed_statuses[] = 'diverifikasi_koor';
            }
            if ($this->_isKadepSC()) {
                $allowed_statuses[] = 'dicek_admin_stock';
            }
            if ($this->_isLogistik()) {
                $allowed_statuses[] = 'disetujui_kadep';
            }

            if (!empty($allowed_statuses)) {
                if (!empty($filter['status'])) {
                    if (is_array($filter['status'])) {
                        $filter['status'] = array_intersect($filter['status'], $allowed_statuses);
                    } elseif (!in_array($filter['status'], $allowed_statuses)) {
                        $filter['status'] = $allowed_statuses;
                    }
                } else {
                    $filter['status'] = $allowed_statuses;
                }
            } else {
                // Not in any allowed role, show nothing
                $filter['status'] = ['none'];
            }
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

    // ================================================================
    // EDIT & UPDATE (Admin Stock)
    // ================================================================

    public function edit($id_spr)
    {
        if (!$this->_isAdminStock()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau tidak dapat diedit saat ini.');
            redirect('retur_penjualan');
            return;
        }

        $data['page_title']  = 'KARISMA — Edit SPR ' . $spr['no_spr'];
        $data['spr']         = $spr;
        $data['no_spr']      = $spr['no_spr'];
        $data['spr_detail']  = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']        = $this->_getUser();
        $data['customers']   = $this->M_ReturPenjualan->get_customers();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id_spr)
    {
        if (!$this->_isAdminStock() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan');
            return;
        }

        $user = $this->_getUser();
        $spr  = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan');
            return;
        }

        $header = [
            'tanggal'       => $this->input->post('tanggal'),
            'kd_customer'   => $this->input->post('kd_customer'),
            'nama_customer' => $this->input->post('nama_customer'),
            'alamat'        => $this->input->post('alamat'),
            'nama_sales'    => $this->input->post('nama_sales'),
            'catatan'       => $this->input->post('catatan'),
            'update_by'     => $user['nama'],
        ];

        // Update header tanpa merubah status (tetap diverifikasi_koor)
        $this->M_ReturPenjualan->update_spr_status($id_spr, $spr['status'], $header);

        // Hapus detail lama dan ganti dengan yang baru
        $this->M_ReturPenjualan->delete_spr_detail($id_spr);
        $this->_saveDetail($id_spr);

        $this->session->set_flashdata('success', "Data SPR <strong>{$spr['no_spr']}</strong> berhasil diperbarui.");
        redirect('retur_penjualan/admin_stock_cek/' . $id_spr);
    }

    /**
     * Simpan detail item dari POST array.
     * POST keys: nama_barang[], no_faktur[], no_batch[], expired_date[], harga[], qty[],
     *            alasan_brg_bermasalah[], alasan_brg_bermasalah_opt[], dst.
     */
    private function _saveDetail($id_spr)
    {
        $nama_barang    = $this->input->post('nama_barang')    ?: [];
        $no_faktur      = $this->input->post('no_faktur')      ?: [];
        $no_batch       = $this->input->post('no_batch')       ?: [];
        $expired_date   = $this->input->post('expired_date')   ?: [];
        $harga          = $this->input->post('harga')          ?: [];
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
            $exp_val = !empty($expired_date[$i]) ? $expired_date[$i] : null;
            $rows[] = [
                'id_spr'                    => $id_spr,
                'no_urut'                   => $i + 1,
                'nama_barang'               => $nb,
                'no_faktur'                 => $no_faktur[$i] ?? '',
                'no_batch'                  => $no_batch[$i]  ?? '',
                'expired_date'              => $exp_val,
                'harga'                     => (float) str_replace(',', '', ($harga[$i] ?? 0)),
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
    // AJAX — Search Barang untuk Select2
    // ================================================================

    public function ajax_search_barang()
    {
        header('Content-Type: application/json');
        $q = $this->input->get('q', true);
        if (empty($q) || strlen(trim($q)) < 2) {
            echo json_encode(['results' => []]);
            return;
        }
        $this->db->select('kd_barang, nama_barang, satuan, hpp');
        $this->db->from('tb_master_barang_all');
        $this->db->like('nama_barang', $q);
        $this->db->order_by('nama_barang', 'ASC');
        $this->db->limit(30);
        $rows = $this->db->get()->result_array();

        $results = array_map(function($r) {
            return [
                'id'     => $r['nama_barang'],
                'text'   => $r['nama_barang'],
                'satuan' => $r['satuan'],
                'harga'  => (float) $r['hpp'],
            ];
        }, $rows);

        echo json_encode(['results' => $results]);
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

    public function koor_sc_verifikasi($id_spr)
    {
        if (!$this->_isKoorSC()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'diajukan') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan');
            return;
        }

        $data['page_title'] = 'KARISMA — Koor SC: Verifikasi ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'koor_sc';
        $data['back_url']   = base_url('retur_penjualan');
        $data['action_url'] = base_url('retur_penjualan/koor_sc/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function koor_sc_simpan($id_spr)
    {
        if (!$this->_isKoorSC() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan');
            return;
        }

        $aksi    = $this->input->post('aksi'); // 'setuju' | 'tolak'
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'diajukan') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan');
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
        redirect('retur_penjualan');
    }

    // ================================================================
    // ADMIN STOCK — Antrian cek fisik
    // ================================================================

    public function admin_stock_cek($id_spr)
    {
        if (!$this->_isAdminStock()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan');
            return;
        }

        $data['page_title'] = 'KARISMA — Admin Stock: Cek ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'admin_stock';
        $data['back_url']   = base_url('retur_penjualan');
        $data['action_url'] = base_url('retur_penjualan/admin_stock/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_stock_simpan($id_spr)
    {
        if (!$this->_isAdminStock() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan');
            return;
        }

        $aksi    = $this->input->post('aksi');
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan');
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
        redirect('retur_penjualan');
    }

    // ================================================================
    // KADEP SC — Antrian persetujuan Kepala Departemen
    // ================================================================

    public function kadep_sc_approve($id_spr)
    {
        if (!$this->_isKadepSC()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'dicek_admin_stock') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan');
            return;
        }

        $data['page_title'] = 'KARISMA — Kadep SC: Approve ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'kadep_sc';
        $data['back_url']   = base_url('retur_penjualan');
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
            ? "SPR <strong>{$spr['no_spr']}</strong> disetujui, lanjut ke Logistik."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak oleh Kadep SC.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan');
    }

    // ================================================================
    // PRINT SPR
    // ================================================================

    public function print_spr($id_spr)
    {
        // Restrict print to Logistik or Admin
        if (!$this->_isLogistik()) {
            $this->_denyAccess('Akses ditolak. Cetak SPR hanya diperbolehkan untuk Logistik.');
            return;
        }

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

    // ================================================================
    // APPROVAL HISTORY
    // ================================================================

    public function history()
    {
        $user = $this->_getUser();
        $role = '';

        if ($this->_isJobdesk(['ADMIN'])) {
            $role = 'admin';
            $role_label = 'Admin';
        } elseif ($this->_isKoorSC()) {
            $role = 'koor_sc';
            $role_label = 'Koor SC';
        } elseif ($this->_isAdminStock()) {
            $role = 'admin_stock';
            $role_label = 'Admin Stock';
        } elseif ($this->_isKadepSC()) {
            $role = 'kadep_sc';
            $role_label = 'Kadep SC';
        } elseif ($this->_isLogistik()) {
            $role = 'logistik';
            $role_label = 'Logistik';
        } else {
            $this->_denyAccess('Hanya user approval yang dapat melihat riwayat persetujuan.');
            return;
        }

        $filter = [
            'date1'   => $this->input->get('date1', true),
            'date2'   => $this->input->get('date2', true),
            'status'  => $this->input->get('status', true),
        ];

        $data['page_title'] = 'KARISMA — Riwayat Persetujuan SPR ' . $role_label;
        $data['spr_list']   = $this->M_ReturPenjualan->get_approval_history($user['nama'], $role, $filter);
        $data['filter']     = $filter;
        $data['user']       = $user;
        $data['role_label'] = $role_label;
        $data['role']       = $role;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_history.php', $data);
        $this->load->view('partial/main/footer.php');
    }
    // ================================================================
    // ADMLPB2 — Daftar SPR yang disetujui Kadep (buat Retur)
    // ================================================================

    private function _isAdmlpb2()
    {
        return $this->_isJobdesk(['ADMLPB2', 'ADMIN']);
    }

    private function _isCollection()
    {
        return $this->_isJobdesk(['COLLECTION', 'KOLEKTOR', 'ADMIN']);
    }

    private function _isKasir()
    {
        return $this->_isJobdesk(['KASIR', 'ADMIN']);
    }

    /** ADMLPB2: list SPR disetujui kadep yang siap dibuat Retur */
    public function admlpb2_index()
    {
        if (!$this->_isAdmlpb2()) {
            $this->_denyAccess('Akses ditolak.');
            return;
        }

        $user   = $this->_getUser();
        $filter = [
            'date1'  => $this->input->get('date1', true),
            'date2'  => $this->input->get('date2', true),
            'status' => $this->input->get('status', true) ?: 'disetujui_kadep',
        ];

        $data['page_title'] = 'KARISMA — ADMLPB2: SPR Siap Retur';
        $data['spr_list']   = $this->M_ReturPenjualan->get_all_spr($filter);
        $data['filter']     = $filter;
        $data['user']       = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/admlpb2_spr_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // RETUR PENJUALAN — Dibuat oleh ADMLPB2 dari SPR disetujui Kadep
    // ================================================================

    /** List Retur Penjualan */
    public function retur_list()
    {
        $user    = $this->_getUser();
        $jobdesk = $user['jobdesk'];

        $filter  = [
            'date1'  => $this->input->get('date1', true),
            'date2'  => $this->input->get('date2', true),
            'status' => $this->input->get('status', true),
        ];

        // Default status per role
        if ($this->_isJobdesk(['ADMIN'])) {
            // Admin lihat semua
        } elseif ($this->_isAdminStock() && !$this->_isCollection()) {
            if (empty($filter['status'])) $filter['status'] = 'menunggu_verifikasi';
        } elseif ($this->_isCollection()) {
            if (empty($filter['status'])) $filter['status'] = 'menunggu_collection';
        } elseif ($this->_isKasir()) {
            if (empty($filter['status'])) $filter['status'] = 'menunggu_kasir';
        }

        $data['page_title']  = 'KARISMA — Retur Penjualan';
        $data['retur_list']  = $this->M_ReturPenjualan->get_all_retur_penjualan($filter);
        $data['filter']      = $filter;
        $data['user']        = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_list.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** ADMLPB2: Form buat Retur Penjualan dari SPR */
    public function retur_buat($id_spr)
    {
        if (!$this->_isAdmlpb2()) {
            $this->_denyAccess('Hanya ADMLPB2 yang dapat membuat Retur Penjualan.');
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'disetujui_kadep') {
            $this->session->set_flashdata('error', 'SPR harus berstatus Disetujui Kadep untuk membuat Retur Penjualan.');
            redirect('retur_penjualan/admlpb2');
            return;
        }

        // Cek apakah sudah ada retur dari SPR ini
        $existing = $this->M_ReturPenjualan->get_retur_by_spr($id_spr);
        if ($existing) {
            $this->session->set_flashdata('error', 'Retur Penjualan untuk SPR ini sudah dibuat (No. Retur: ' . $existing['no_retur'] . ').');
            redirect('retur_penjualan/admlpb2');
            return;
        }

        $data['page_title'] = 'KARISMA — Buat Retur Penjualan dari ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['no_retur']   = $this->M_ReturPenjualan->generate_no_retur();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** ADMLPB2: Simpan Retur Penjualan baru */
    public function retur_simpan($id_spr)
    {
        if (!$this->_isAdmlpb2() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/admlpb2');
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'disetujui_kadep') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau statusnya bukan Disetujui Kadep.');
            redirect('retur_penjualan/admlpb2');
            return;
        }

        $existing = $this->M_ReturPenjualan->get_retur_by_spr($id_spr);
        if ($existing) {
            $this->session->set_flashdata('error', 'Retur Penjualan untuk SPR ini sudah ada.');
            redirect('retur_penjualan/admlpb2');
            return;
        }

        $user        = $this->_getUser();
        $no_retur    = $this->M_ReturPenjualan->generate_no_retur();
        $tanggal     = $this->input->post('tanggal_retur');
        $catatan_log = $this->input->post('catatan_admlpb2');

        $header = [
            'no_retur'          => $no_retur,
            'id_spr'            => (int)$id_spr,
            'no_spr'            => $spr['no_spr'],
            'tanggal_retur'     => $tanggal ?: date('Y-m-d'),
            'kd_customer'       => $spr['kd_customer'],
            'nama_customer'     => $spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? ''),
            'alamat'            => $spr['alamat'] ?: ($spr['alamat_master'] ?? ''),
            'nama_sales'        => $spr['nama_sales'],
            'catatan_logistik'  => $catatan_log,
            'status_retur'      => 'menunggu_verifikasi',
            'create_by_retur'   => $user['nama'],
            'create_at_retur'   => date('Y-m-d H:i:s'),
        ];

        $id_retur = $this->M_ReturPenjualan->save_retur_penjualan($header);
        if (!$id_retur) {
            $this->session->set_flashdata('error', 'Gagal menyimpan Retur Penjualan.');
            redirect('retur_penjualan/retur/buat/' . $id_spr);
            return;
        }

        // Simpan detail dari SPR
        $nama_barang   = $this->input->post('nama_barang') ?: [];
        $satuan_arr    = $this->input->post('satuan') ?: [];
        $no_faktur_arr = $this->input->post('no_faktur') ?: [];
        $no_batch_arr  = $this->input->post('no_batch') ?: [];
        $expired_arr   = $this->input->post('expired_date') ?: [];
        $id_spr_detail = $this->input->post('id_spr_detail') ?: [];
        $qty_retur     = $this->input->post('qty_retur') ?: [];
        $harga_satuan  = $this->input->post('harga_satuan') ?: [];

        $rows = [];
        foreach ($nama_barang as $i => $nb) {
            if (empty($nb)) continue;
            $rows[] = [
                'id_retur'      => $id_retur,
                'id_spr_detail' => (int)($id_spr_detail[$i] ?? 0),
                'no_urut'       => $i + 1,
                'nama_barang'   => $nb,
                'satuan'        => $satuan_arr[$i] ?? '',
                'no_faktur'     => $no_faktur_arr[$i] ?? '',
                'no_batch'      => $no_batch_arr[$i] ?? '',
                'expired_date'  => !empty($expired_arr[$i]) ? $expired_arr[$i] : null,
                'qty_retur'     => (float)($qty_retur[$i] ?? 0),
                'harga_satuan'  => (float)str_replace([',', '.'], ['', '.'], ($harga_satuan[$i] ?? 0)),
            ];
        }
        $this->M_ReturPenjualan->save_retur_penjualan_detail($rows);

        // Update SPR status jadi selesai
        $this->M_ReturPenjualan->update_spr_status($id_spr, 'selesai', [
            'logistik_by'      => $user['nama'],
            'logistik_at'      => date('Y-m-d H:i:s'),
            'logistik_catatan' => 'Retur Penjualan dibuat: ' . $no_retur,
            'update_by'        => $user['nama'],
        ]);

        $this->session->set_flashdata('success', "Retur Penjualan <strong>{$no_retur}</strong> berhasil dibuat dan menunggu verifikasi Admin Stock.");
        redirect('retur_penjualan/retur/detail/' . $id_retur);
    }

    /** Detail Retur Penjualan */
    public function retur_detail($id_retur)
    {
        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur) {
            $this->session->set_flashdata('error', 'Retur Penjualan tidak ditemukan.');
            redirect('retur_penjualan/retur');
            return;
        }

        $user    = $this->_getUser();
        $jobdesk = $user['jobdesk'];

        $data['page_title']   = 'KARISMA — Detail Retur ' . $retur['no_retur'];
        $data['retur']        = $retur;
        $data['retur_detail'] = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $data['user']         = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ================================================================
    // ADMIN STOCK — Verifikasi Retur Penjualan
    // ================================================================

    /** Admin Stock: Form verifikasi Retur */
    public function retur_verifikasi($id_retur)
    {
        if (!$this->_isAdminStock()) {
            $this->_denyAccess('Hanya Admin Stock yang dapat memverifikasi Retur Penjualan.');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'menunggu_verifikasi') {
            $this->session->set_flashdata('error', 'Retur tidak valid atau sudah diproses.');
            redirect('retur_penjualan/retur');
            return;
        }

        $data['page_title']   = 'KARISMA — Verifikasi Retur ' . $retur['no_retur'];
        $data['retur']        = $retur;
        $data['retur_detail'] = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $data['user']         = $this->_getUser();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_verifikasi.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** Admin Stock: Simpan keputusan verifikasi Retur */
    public function retur_verifikasi_simpan($id_retur)
    {
        if (!$this->_isAdminStock() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/retur');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'menunggu_verifikasi') {
            $this->session->set_flashdata('error', 'Retur tidak valid.');
            redirect('retur_penjualan/retur');
            return;
        }

        $aksi         = $this->input->post('aksi');
        $catatan      = $this->input->post('catatan_admin_stock');
        $user         = $this->_getUser();
        $id_retur_det = $this->input->post('id_retur_detail') ?: [];
        $qty_retur    = $this->input->post('qty_retur') ?: [];
        $harga_satuan = $this->input->post('harga_satuan') ?: [];

        foreach ($id_retur_det as $i => $idd) {
            $this->M_ReturPenjualan->update_retur_penjualan_detail_row((int)$idd, [
                'qty_retur'    => (float)($qty_retur[$i] ?? 0),
                'harga_satuan' => (float)str_replace(',', '', ($harga_satuan[$i] ?? 0)),
            ]);
        }

        $new_status = ($aksi === 'setuju') ? 'menunggu_collection' : 'ditolak';

        $this->M_ReturPenjualan->update_retur_penjualan_status($id_retur, $new_status, [
            'admin_stock_by_retur' => $user['nama'],
            'admin_stock_at_retur' => date('Y-m-d H:i:s'),
            'catatan_admin_stock'  => $catatan,
            'update_by_retur'      => $user['nama'],
        ]);

        $msg = ($aksi === 'setuju')
            ? "Retur <strong>{$retur['no_retur']}</strong> diverifikasi, lanjut ke Team Collection."
            : "Retur <strong>{$retur['no_retur']}</strong> ditolak oleh Admin Stock.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/retur');
    }

    // ================================================================
    // COLLECTION — Proses Retur Penjualan
    // ================================================================

    /** Collection: Form proses Retur */
    public function retur_collection($id_retur)
    {
        if (!$this->_isCollection()) {
            $this->_denyAccess('Hanya Team Collection yang dapat memproses tahap ini.');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'menunggu_collection') {
            $this->session->set_flashdata('error', 'Retur tidak valid atau belum diverifikasi Admin Stock.');
            redirect('retur_penjualan/retur');
            return;
        }

        $retur_detail = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $total_retur  = 0;
        foreach ($retur_detail as $d) $total_retur += (float)$d['qty_retur'] * (float)$d['harga_satuan'];

        $data['page_title']   = 'KARISMA — Collection: Proses Retur ' . $retur['no_retur'];
        $data['retur']        = $retur;
        $data['retur_detail'] = $retur_detail;
        $data['total_retur']  = $total_retur;
        $data['user']         = $this->_getUser();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_collection.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** Collection: Simpan keputusan */
    public function retur_collection_simpan($id_retur)
    {
        if (!$this->_isCollection() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/retur');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'menunggu_collection') {
            $this->session->set_flashdata('error', 'Retur tidak valid.');
            redirect('retur_penjualan/retur');
            return;
        }

        $no_faktur_potong    = $this->input->post('no_faktur_potong');
        $catatan_collection  = $this->input->post('catatan_collection');
        $user                = $this->_getUser();

        $this->M_ReturPenjualan->update_retur_penjualan_status($id_retur, 'menunggu_kasir', [
            'collection_by'      => $user['nama'],
            'collection_at'      => date('Y-m-d H:i:s'),
            'catatan_collection' => $catatan_collection,
            'no_faktur_potong'   => $no_faktur_potong,
            'update_by_retur'    => $user['nama'],
        ]);

        $this->session->set_flashdata('success', "Retur <strong>{$retur['no_retur']}</strong> selesai diproses Collection, lanjut ke Kasir.");
        redirect('retur_penjualan/retur/detail/' . $id_retur);
    }

    // ================================================================
    // KASIR — Selesaikan Retur Penjualan
    // ================================================================

    /** Kasir: Form selesaikan Retur */
    public function retur_kasir($id_retur)
    {
        if (!$this->_isKasir()) {
            $this->_denyAccess('Hanya Kasir yang dapat menyelesaikan Retur Penjualan.');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'menunggu_kasir') {
            $this->session->set_flashdata('error', 'Retur tidak valid atau belum diproses Collection.');
            redirect('retur_penjualan/retur');
            return;
        }

        $retur_detail = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $total_retur  = 0;
        foreach ($retur_detail as $d) $total_retur += (float)$d['qty_retur'] * (float)$d['harga_satuan'];

        $data['page_title']   = 'KARISMA — Kasir: Selesaikan Retur ' . $retur['no_retur'];
        $data['retur']        = $retur;
        $data['retur_detail'] = $retur_detail;
        $data['total_retur']  = $total_retur;
        $data['user']         = $this->_getUser();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_kasir.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** Kasir: Simpan & selesaikan Retur */
    public function retur_kasir_simpan($id_retur)
    {
        if (!$this->_isKasir() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/retur');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'menunggu_kasir') {
            $this->session->set_flashdata('error', 'Retur tidak valid.');
            redirect('retur_penjualan/retur');
            return;
        }

        $catatan_kasir = $this->input->post('catatan_kasir');
        $user          = $this->_getUser();

        $this->M_ReturPenjualan->update_retur_penjualan_status($id_retur, 'selesai', [
            'kasir_by'       => $user['nama'],
            'kasir_at'       => date('Y-m-d H:i:s'),
            'catatan_kasir'  => $catatan_kasir,
            'update_by_retur'=> $user['nama'],
        ]);

        $this->session->set_flashdata('success', "Retur Penjualan <strong>{$retur['no_retur']}</strong> selesai diproses oleh Kasir.");
        redirect('retur_penjualan/retur/detail/' . $id_retur);
    }

}


