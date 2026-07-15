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

        // Batasi akses hanya ke jobdesk yang diperbolehkan di DB
        $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
        $allowed_jobdesks = ['SC', 'KOORSC', 'ADMSTOCK', 'KADEPSC', 'ADMLPB2', 'LOGISTIC', 'COLLECTION', 'KASIR', 'ADMIN', 'ADMPNJ', 'KADEPUB', 'MANAGERACC', 'MANAGERSE', 'DIREKTUROP', 'DIREKTURUTAMA'];
        if (!in_array($jobdesk, $allowed_jobdesks)) {
            show_error('Akses ditolak. Anda tidak memiliki izin untuk mengakses modul Retur Penjualan.', 403);
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
        return $this->_isJobdesk(['SC', 'ADMIN']);
    }

    /** Koordinator SC */
    private function _isKoorSC()
    {
        return $this->_isJobdesk(['KOORSC', 'ADMIN']);
    }

    /** Admin Stock */
    private function _isAdminStock()
    {
        return $this->_isJobdesk(['ADMSTOCK', 'ADMIN']);
    }

    /** Admin Penjualan */
    private function _isAdmpnj()
    {
        return $this->_isJobdesk(['ADMPNJ', 'ADMIN']);
    }

    /** Kepala Departemen SC */
    private function _isKadepSC()
    {
        return $this->_isJobdesk(['KADEPSC', 'KADEPUB', 'ADMIN']);
    }

    /** Kadep Unit Bisnis */
    private function _isKadepub()
    {
        return $this->_isJobdesk(['KADEPUB', 'ADMIN']);
    }

    /** Logistik */
    private function _isLogistik()
    {
        return $this->_isJobdesk(['ADMLPB2', 'LOGISTIC', 'ADMIN']);
    }

    /** Cek apakah boleh lihat semua SPR (bukan SC biasa) */
    private function _canSeeAll()
    {
        return !$this->_isSC() || $this->_isJobdesk(['ADMIN']);
    }

    private function _denyAccess($msg = 'Anda tidak memiliki akses ke halaman ini.')
    {
        $this->session->set_flashdata('error', $msg);
        $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
        if ($jobdesk === 'ADMSTOCK') {
            redirect('retur_penjualan/retur');
        } else {
            redirect('retur_penjualan');
        }
    }

    // ================================================================
    // INDEX — LIST SPR (SC melihat milik sendiri)
    // ================================================================

    public function index()
    {
        $user   = $this->_getUser();
        
        // ADMSTOCK is not allowed to see SPR list anymore (separated to ADMPNJ)
        if ($this->_isJobdesk(['ADMSTOCK']) && !$this->_isJobdesk(['ADMIN'])) {
            redirect('retur_penjualan/retur');
            return;
        }

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
            if ($this->_isKadepub()) {
                $allowed_statuses[] = 'menunggu_kadepub';
            }
            if ($this->_isAdmpnj()) {
                $allowed_statuses[] = 'diverifikasi_koor';
            }
            if ($this->_isKadepSC() || $this->_isKadepub()) {
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
        $tipe_retur = $this->input->post('tipe_retur') ?: 'biasa';
        $is_jagung  = $this->input->post('is_jagung') ? 1 : 0;

        $header = [
            'no_spr'        => $no_spr,
            'tipe_retur'    => $tipe_retur,
            'is_jagung'     => $is_jagung,
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

        // Record Log
        $this->M_ReturPenjualan->record_log($no_spr, 'spr', $as_draft ? 'create_draft' : 'create_submit', null, $status, $catatan, $user['nama']);

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
        if (!$this->_isAdmpnj()) {
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
        if (!$this->_isAdmpnj() || $this->input->server('REQUEST_METHOD') !== 'POST') {
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
            'tipe_retur'    => $this->input->post('tipe_retur') ?: 'biasa',
            'is_jagung'     => $this->input->post('is_jagung') ? 1 : 0,
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

        // Record Log
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', 'edit_stock', $spr['status'], $spr['status'], $header['catatan'], $user['nama']);

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

        // Record Log
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', 'submit', $spr['status'], 'diajukan', null, $user['nama']);

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

        if ($aksi === 'setuju') {
            $new_status = !empty($spr['is_jagung']) ? 'menunggu_kadepub' : 'diverifikasi_koor';
        } else {
            $new_status = 'ditolak';
        }

        $this->M_ReturPenjualan->update_spr_status($id_spr, $new_status, [
            'koor_sc_by'      => $user['nama'],
            'koor_sc_at'      => date('Y-m-d H:i:s'),
            'koor_sc_catatan' => $catatan,
            'update_by'       => $user['nama'],
        ]);

        // Record Log
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', $aksi === 'setuju' ? 'koor_verify' : 'koor_reject', $spr['status'], $new_status, $catatan, $user['nama']);

        $msg = ($aksi === 'setuju')
            ? (!empty($spr['is_jagung'])
                ? "SPR <strong>{$spr['no_spr']}</strong> disetujui Koor SC, lanjut ke Kadep Unit Bisnis (KADEPUB)."
                : "SPR <strong>{$spr['no_spr']}</strong> disetujui Koor SC, lanjut ke Admin Penjualan.")
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan');
    }

    // ================================================================
    // KADEPUB — Verifikasi SPR Jagung
    // ================================================================

    public function kadepub_verifikasi($id_spr)
    {
        if (!$this->_isKadepub()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'menunggu_kadepub') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan');
            return;
        }

        $data['page_title'] = 'KARISMA — Kadep Unit Bisnis: Verifikasi ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'kadepub';
        $data['back_url']   = base_url('retur_penjualan');
        $data['action_url'] = base_url('retur_penjualan/kadepub/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function kadepub_simpan($id_spr)
    {
        if (!$this->_isKadepub() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan');
            return;
        }

        $aksi    = $this->input->post('aksi'); // 'setuju' | 'tolak'
        $catatan = $this->input->post('catatan');
        $user    = $this->_getUser();
        $spr     = $this->M_ReturPenjualan->get_spr($id_spr);

        if (!$spr || $spr['status'] !== 'menunggu_kadepub') {
            $this->session->set_flashdata('error', 'SPR tidak valid.');
            redirect('retur_penjualan');
            return;
        }

        $new_status = ($aksi === 'setuju') ? 'diverifikasi_koor' : 'ditolak';

        $this->M_ReturPenjualan->update_spr_status($id_spr, $new_status, [
            'kadepub_by'      => $user['nama'],
            'kadepub_at'      => date('Y-m-d H:i:s'),
            'kadepub_catatan' => $catatan,
            'update_by'       => $user['nama'],
        ]);

        // Record Log
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', $aksi === 'setuju' ? 'kadepub_verify' : 'kadepub_reject', $spr['status'], $new_status, $catatan, $user['nama']);

        $msg = ($aksi === 'setuju')
            ? "SPR <strong>{$spr['no_spr']}</strong> disetujui Kadep Unit Bisnis, lanjut ke Admin Penjualan."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak oleh Kadep Unit Bisnis.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan');
    }

    // ================================================================
    // ADMIN STOCK — Antrian cek fisik
    // ================================================================

    public function admin_stock_cek($id_spr)
    {
        if (!$this->_isAdmpnj()) {
            $this->_denyAccess();
            return;
        }

        $spr = $this->M_ReturPenjualan->get_spr($id_spr);
        if (!$spr || $spr['status'] !== 'diverifikasi_koor') {
            $this->session->set_flashdata('error', 'SPR tidak valid atau sudah diproses.');
            redirect('retur_penjualan');
            return;
        }

        $data['page_title'] = 'KARISMA — Admin Penjualan: Cek ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['role']       = 'admin_stock'; // keep as admin_stock for view logic compatibility
        $data['back_url']   = base_url('retur_penjualan');
        $data['action_url'] = base_url('retur_penjualan/admin_stock/simpan/' . $id_spr);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/spr_approval.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function admin_stock_simpan($id_spr)
    {
        if (!$this->_isAdmpnj() || $this->input->server('REQUEST_METHOD') !== 'POST') {
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

        // Record Log
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', $aksi === 'setuju' ? 'admin_stock_check' : 'admin_stock_reject', $spr['status'], $new_status, $catatan, $user['nama']);

        $msg = ($aksi === 'setuju')
            ? "SPR <strong>{$spr['no_spr']}</strong> dicek Admin Penjualan, lanjut ke Kadep SC."
            : "SPR <strong>{$spr['no_spr']}</strong> ditolak oleh Admin Penjualan.";

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

        // Record Log
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', $aksi === 'setuju' ? 'kadep_approve' : 'kadep_reject', $spr['status'], $new_status, $catatan, $user['nama']);

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

    public function retur_print($id_retur)
    {
        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur) {
            $this->session->set_flashdata('error', 'Retur Penjualan tidak ditemukan.');
            redirect('retur_penjualan/retur');
            return;
        }

        $data['retur']        = $retur;
        $data['retur_detail'] = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $data['user']         = $this->_getUser();

        // Load print view tanpa header/footer sidebar
        $this->load->view('content/sales/retur/retur_print.php', $data);
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
        } elseif ($this->_isCollection()) {
            $role = 'collection';
            $role_label = 'Collection';
        } elseif ($this->_isKasir()) {
            $role = 'kasir';
            $role_label = 'Kasir';
        } else {
            $this->_denyAccess('Hanya user approval, collection, atau kasir yang dapat melihat riwayat.');
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
        return $this->_isJobdesk(['COLLECTION', 'ADMIN']);
    }

    private function _isKasir()
    {
        return $this->_isJobdesk(['KASIR', 'ADMIN']);
    }

    private function _isMngacc()
    {
        return $this->_isJobdesk(['MANAGERACC', 'ADMIN']);
    }

    private function _isMngse()
    {
        return $this->_isJobdesk(['MANAGERSE', 'ADMIN']);
    }

    private function _isDirop()
    {
        return $this->_isJobdesk(['DIREKTUROP', 'ADMIN']);
    }

    private function _isDirut()
    {
        return $this->_isJobdesk(['DIREKTURUTAMA', 'ADMIN']);
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

    public function retur_list()
    {
        $user    = $this->_getUser();
        
        // ADMPNJ is not allowed to see Retur list (separated to ADMSTOCK)
        if ($this->_isJobdesk(['ADMPNJ']) && !$this->_isJobdesk(['ADMIN'])) {
            redirect('retur_penjualan');
            return;
        }

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
        } elseif ($this->_isKadepub()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_kadepub';
        } elseif ($this->_isMngacc()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_mngacc';
        } elseif ($this->_isKoorSC()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_koorsc';
        } elseif ($this->_isMngse()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_mngse';
        } elseif ($this->_isKadepSC()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_kadepsc';
        } elseif ($this->_isDirop()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_dirop';
        } elseif ($this->_isDirut()) {
            if (empty($filter['status'])) $filter['status'] = 'retur_menunggu_dirut';
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

        $this->load->model('M_SalesOrder');
        $data['page_title'] = 'KARISMA — Buat Retur Penjualan dari ' . $spr['no_spr'];
        $data['spr']        = $spr;
        $data['spr_detail'] = $this->M_ReturPenjualan->get_spr_detail($id_spr);
        $data['user']       = $this->_getUser();
        $data['no_retur']   = $this->M_ReturPenjualan->generate_no_retur($spr['tipe_retur'] ?? 'biasa');
        $data['gudang_list'] = $this->db->where('is_active', 1)->order_by('nama_gudang', 'ASC')->get('tb_gudang')->result_array();

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
        $no_retur    = $this->M_ReturPenjualan->generate_no_retur($spr['tipe_retur'] ?? 'biasa');
        $tanggal     = $this->input->post('tanggal_retur');
        $catatan_log = $this->input->post('catatan_admlpb2');
        $gudang_id   = $this->input->post('gudang_id');

        $header = [
            'no_retur'          => $no_retur,
            'tipe_retur'        => $spr['tipe_retur'] ?? 'biasa',
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

        // simpan gudang_id ke header jika kolomnya ada, tambahkan jika belum ada.
        if ($this->db->field_exists('gudang_id', 'tbrp_retur_penjualan_header')) {
            $header['gudang_id'] = $gudang_id;
        } else {
            $this->db->query("ALTER TABLE `tbrp_retur_penjualan_header` ADD COLUMN `gudang_id` INT DEFAULT NULL AFTER `no_spr`");
            $header['gudang_id'] = $gudang_id;
        }

        $id_retur = $this->M_ReturPenjualan->save_retur_penjualan($header);
        if (!$id_retur) {
            $this->session->set_flashdata('error', 'Gagal menyimpan Retur Penjualan.');
            redirect('retur_penjualan/retur/buat/' . $id_spr);
            return;
        }

        // Simpan detail dari SPR
        $nama_barang   = $this->input->post('nama_barang') ?: [];
        $kd_barang_arr = $this->input->post('kd_barang') ?: [];
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

            $kb = !empty($kd_barang_arr[$i]) ? $kd_barang_arr[$i] : '';
            if (empty($kb)) {
                // cari kd_barang dari db jika inputnya kosong
                $mb = $this->db->get_where('tb_master_barang_all', ['nama_barang' => $nb])->row_array();
                $kb = $mb ? $mb['kd_barang'] : '';
            }

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

            // Insert / update ke tberp_stock_batch & tberp_stock_ledger
            $stockQty = (float)($qty_retur[$i] ?? 0);
            $stockNoLot = trim((string)($no_batch_arr[$i] ?? ''));
            $normalizedExpired = !empty($expired_arr[$i]) ? date('Y-m-d', strtotime($expired_arr[$i])) : null;

            if ($stockQty > 0 && !empty($kb)) {
                if ($this->db->table_exists('tberp_stock_batch')) {
                    $this->db->where('kd_barang', $kb);
                    $this->db->where('gudang_id', $gudang_id);
                    $this->db->where('no_lot', $stockNoLot);
                    if ($normalizedExpired !== null) {
                        $this->db->where('expired_date', $normalizedExpired);
                    } else {
                        $this->db->where('expired_date', null);
                    }
                    $existingStockBatch = $this->db->get('tberp_stock_batch')->row_array();

                    if ($existingStockBatch) {
                        $this->db->where('id', $existingStockBatch['id']);
                        $this->db->set('qty_on_hand', 'qty_on_hand + ' . $stockQty, FALSE);
                        $this->db->set('update_at', date('Y-m-d H:i:s'));
                        $this->db->update('tberp_stock_batch');
                    } else {
                        $this->db->insert('tberp_stock_batch', [
                            'kd_barang'    => $kb,
                            'gudang_id'    => $gudang_id,
                            'no_lot'       => $stockNoLot,
                            'expired_date' => $normalizedExpired,
                            'qty_on_hand'  => $stockQty,
                            'qty_reserved' => 0,
                        ]);
                    }
                }

                if ($this->db->table_exists('tberp_stock_ledger')) {
                    $this->db->insert('tberp_stock_ledger', [
                        'kd_barang'    => $kb,
                        'gudang_id'    => $gudang_id,
                        'no_lot'       => $stockNoLot,
                        'expired_date' => $normalizedExpired,
                        'qty'          => $stockQty,
                        'tipe'         => 'RJUAL',
                        'ref_no'       => $no_retur,
                        'ref_type'     => 'RETUR_PENJUALAN',
                        'created_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
        $this->M_ReturPenjualan->save_retur_penjualan_detail($rows);

        // Update SPR status jadi selesai
        $this->M_ReturPenjualan->update_spr_status($id_spr, 'selesai', [
            'logistik_by'      => $user['nama'],
            'logistik_at'      => date('Y-m-d H:i:s'),
            'logistik_catatan' => 'Retur Penjualan dibuat: ' . $no_retur,
            'update_by'        => $user['nama'],
        ]);

        // Record Log for SPR completed and Retur created
        $this->M_ReturPenjualan->record_log($spr['no_spr'], 'spr', 'logistik_process', $spr['status'], 'selesai', 'Retur Penjualan dibuat: ' . $no_retur, $user['nama']);
        $this->M_ReturPenjualan->record_log($no_retur, 'retur', 'retur_create', null, 'menunggu_verifikasi', $catatan_log, $user['nama']);

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

    /** Persetujuan/Approval Retur Penjualan */
    public function retur_approve($id_retur)
    {
        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur) {
            $this->session->set_flashdata('error', 'Retur Penjualan tidak ditemukan.');
            redirect('retur_penjualan/retur');
            return;
        }

        $user    = $this->_getUser();
        $jobdesk = strtoupper((string)$user['jobdesk']);

        // Check if this retur is waiting for the logged in user's role approval
        $st = $retur['status_retur'];
        $allowed = false;
        if ($st === 'retur_menunggu_mngacc' && in_array($jobdesk, ['MANAGERACC', 'ADMIN'])) {
            $allowed = true;
        } elseif ($st === 'retur_menunggu_koorsc' && in_array($jobdesk, ['KOORSC', 'ADMIN'])) {
            $allowed = true;
        } elseif ($st === 'retur_menunggu_kadepub' && in_array($jobdesk, ['KADEPUB', 'ADMIN'])) {
            $allowed = true;
        } elseif ($st === 'retur_menunggu_mngse' && in_array($jobdesk, ['MANAGERSE', 'ADMIN'])) {
            $allowed = true;
        } elseif ($st === 'retur_menunggu_kadepsc' && in_array($jobdesk, ['KADEPSC', 'ADMIN'])) {
            $allowed = true;
        } elseif ($st === 'retur_menunggu_dirop' && in_array($jobdesk, ['DIREKTUROP', 'ADMIN'])) {
            $allowed = true;
        } elseif ($st === 'retur_menunggu_dirut' && in_array($jobdesk, ['DIREKTURUTAMA', 'ADMIN'])) {
            $allowed = true;
        }

        if (!$allowed) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk memberikan persetujuan pada tahap ini.');
            redirect('retur_penjualan/retur/detail/' . $id_retur);
            return;
        }

        $data['page_title']   = 'KARISMA — Persetujuan Retur ' . $retur['no_retur'];
        $data['retur']        = $retur;
        $data['retur_detail'] = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $data['user']         = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_approval.php', $data);
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
        
        $nama_barang_arr = $this->input->post('nama_barang') ?: [];
        $no_faktur_arr   = $this->input->post('no_faktur') ?: [];
        $no_batch_arr    = $this->input->post('no_batch') ?: [];
        $expired_date_arr= $this->input->post('expired_date') ?: [];

        foreach ($id_retur_det as $i => $idd) {
            $update_data = [
                'qty_retur'    => (float)($qty_retur[$i] ?? 0),
                'harga_satuan' => (float)str_replace(',', '', ($harga_satuan[$i] ?? 0)),
            ];

            if (isset($nama_barang_arr[$i])) {
                $update_data['nama_barang'] = $nama_barang_arr[$i];
            }
            if (isset($no_faktur_arr[$i])) {
                $update_data['no_faktur'] = $no_faktur_arr[$i];
            }
            if (isset($no_batch_arr[$i])) {
                $update_data['no_batch'] = $no_batch_arr[$i];
            }
            if (!empty($expired_date_arr[$i])) {
                $update_data['expired_date'] = $expired_date_arr[$i];
            } else {
                $update_data['expired_date'] = null;
            }

            $this->M_ReturPenjualan->update_retur_penjualan_detail_row((int)$idd, $update_data);
        }

        if ($aksi === 'setuju') {
            $new_status = 'retur_menunggu_mngacc';
            $next_label = 'Manager Account';
        } else {
            $new_status = 'ditolak';
        }

        $this->M_ReturPenjualan->update_retur_penjualan_status($id_retur, $new_status, [
            'admin_stock_by_retur' => $user['nama'],
            'admin_stock_at_retur' => date('Y-m-d H:i:s'),
            'catatan_admin_stock'  => $catatan,
            'update_by_retur'      => $user['nama'],
        ]);

        // Record Log
        $this->M_ReturPenjualan->record_log($retur['no_retur'], 'retur', $aksi === 'setuju' ? 'retur_verify' : 'retur_reject', $retur['status_retur'], $new_status, $catatan, $user['nama']);

        $msg = ($aksi === 'setuju')
            ? "Retur <strong>{$retur['no_retur']}</strong> diverifikasi, lanjut ke {$next_label}."
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

        // Record Log
        $this->M_ReturPenjualan->record_log($retur['no_retur'], 'retur', 'retur_collection', $retur['status_retur'], 'menunggu_kasir', $catatan_collection, $user['nama']);

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

        // Record Log
        $this->M_ReturPenjualan->record_log($retur['no_retur'], 'retur', 'retur_kasir', $retur['status_retur'], 'selesai', $catatan_kasir, $user['nama']);

        $this->session->set_flashdata('success', "Retur Penjualan <strong>{$retur['no_retur']}</strong> selesai diproses oleh Kasir.");
        redirect('retur_penjualan/retur/detail/' . $id_retur);
    }

    /** ADMLPB2: Form edit Retur Penjualan yang ditolak */
    public function retur_edit($id_retur)
    {
        if (!$this->_isAdmlpb2()) {
            $this->_denyAccess('Hanya ADMLPB2 yang dapat mengedit Retur Penjualan.');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'ditolak') {
            $this->session->set_flashdata('error', 'Retur Penjualan tidak valid atau statusnya bukan Ditolak.');
            redirect('retur_penjualan/retur');
            return;
        }

        $this->load->model('M_SalesOrder');
        $data['page_title']  = 'KARISMA — Edit Retur Penjualan ' . $retur['no_retur'];
        $data['retur']       = $retur;
        $data['retur_detail'] = $this->M_ReturPenjualan->get_retur_penjualan_detail($id_retur);
        $data['user']        = $this->_getUser();
        $data['no_retur']    = $retur['no_retur'];
        $data['gudang_list'] = $this->db->where('is_active', 1)->order_by('nama_gudang', 'ASC')->get('tb_gudang')->result_array();

        // Agar retur_form.php reuseable, kita mapping $spr ke info dasar
        $data['spr'] = [
            'id_spr'        => $retur['id_spr'],
            'no_spr'        => $retur['no_spr'],
            'nama_customer' => $retur['nama_customer'],
            'alamat'        => $retur['alamat'],
            'nama_sales'    => $retur['nama_sales'],
            'tipe_retur'    => $retur['tipe_retur'],
        ];

        // Mapping $retur_detail ke bentuk yang mirip dengan $spr_detail agar foreach di retur_form.php jalan
        $data['spr_detail'] = [];
        foreach ($data['retur_detail'] as $rd) {
            $data['spr_detail'][] = [
                'id_spr_detail' => $rd['id_spr_detail'],
                'kd_barang'     => $rd['kd_barang'] ?? '',
                'nama_barang'   => $rd['nama_barang'],
                'satuan'        => $rd['satuan'],
                'no_faktur'     => $rd['no_faktur'],
                'no_batch'      => $rd['no_batch'],
                'expired_date'  => $rd['expired_date'],
                'qty'           => $rd['qty_retur'],
                'harga'         => $rd['harga_satuan'],
            ];
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/retur_form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** ADMLPB2: Update Retur Penjualan yang ditolak */
    public function retur_update($id_retur)
    {
        if (!$this->_isAdmlpb2() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/retur');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'ditolak') {
            $this->session->set_flashdata('error', 'Retur tidak valid atau statusnya bukan Ditolak.');
            redirect('retur_penjualan/retur');
            return;
        }

        $user        = $this->_getUser();
        $tanggal     = $this->input->post('tanggal_retur');
        $catatan_log = $this->input->post('catatan_admlpb2');
        $gudang_id   = $this->input->post('gudang_id');
        $submit_action = $this->input->post('submit_action');

        $header = [
            'tanggal_retur'     => $tanggal ?: date('Y-m-d'),
            'catatan_logistik'  => $catatan_log,
            'gudang_id'         => $gudang_id,
            'update_by_retur'   => $user['nama'],
            'update_at_retur'   => date('Y-m-d H:i:s'),
        ];

        if ($submit_action === 'resubmit') {
            $header['status_retur'] = 'menunggu_verifikasi';
            $header['admin_stock_by_retur'] = null;
            $header['admin_stock_at_retur'] = null;
            $header['catatan_admin_stock'] = null;
        }

        $this->db->where('id_retur', $id_retur);
        $this->db->update('tbrp_retur_penjualan_header', $header);

        // Record Log
        $this->M_ReturPenjualan->record_log($retur['no_retur'], 'retur', $submit_action === 'resubmit' ? 'retur_edit_submit' : 'retur_edit', $retur['status_retur'], $submit_action === 'resubmit' ? 'menunggu_verifikasi' : $retur['status_retur'], $catatan_log, $user['nama']);

        $msg = ($submit_action === 'resubmit')
            ? "Retur Penjualan <strong>{$retur['no_retur']}</strong> berhasil diupdate dan diajukan kembali."
            : "Retur Penjualan <strong>{$retur['no_retur']}</strong> berhasil diupdate.";

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/retur/detail/' . $id_retur);
    }

    /** ADMLPB2: Ajukan kembali Retur Penjualan yang ditolak */
    public function retur_submit($id_retur)
    {
        if (!$this->_isAdmlpb2()) {
            $this->_denyAccess('Hanya ADMLPB2 yang dapat mengajukan Retur Penjualan.');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur || $retur['status_retur'] !== 'ditolak') {
            $this->session->set_flashdata('error', 'Retur tidak valid atau statusnya bukan Ditolak.');
            redirect('retur_penjualan/retur');
            return;
        }

        $user = $this->_getUser();

        $this->M_ReturPenjualan->update_retur_penjualan_status($id_retur, 'menunggu_verifikasi', [
            'update_by_retur' => $user['nama'],
            // Reset rejection info
            'admin_stock_by_retur' => null,
            'admin_stock_at_retur' => null,
            'catatan_admin_stock' => null,
        ]);

        // Record Log
        $this->M_ReturPenjualan->record_log($retur['no_retur'], 'retur', 'retur_submit', $retur['status_retur'], 'menunggu_verifikasi', null, $user['nama']);

        $this->session->set_flashdata('success', "Retur Penjualan <strong>{$retur['no_retur']}</strong> berhasil diajukan kembali ke Admin Stock.");
        redirect('retur_penjualan/retur');
    }

    /** View activity logs */
    public function activity_log()
    {
        $data['page_title']    = 'KARISMA — Activity Log Modul Retur';
        $data['logs']          = $this->M_ReturPenjualan->get_activity_logs();
        $data['user']          = $this->_getUser();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/retur/activity_log.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /** Real-time pending approval notifications */
    public function get_pending_notifications()
    {
        $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
        $pending_items = [];
        
        // 1. Koor SC
        if (in_array($jobdesk, ['KOORSC', 'ADMIN'])) {
            $sprs = $this->db->get_where('tbrp_spr_header', ['status' => 'diajukan'])->result_array();
            foreach ($sprs as $s) {
                $pending_items[] = [
                    'id' => 'spr_' . $s['id_spr'] . '_' . $s['status'],
                    'title' => 'Persetujuan SPR Baru',
                    'body' => 'SPR ' . $s['no_spr'] . ' menunggu verifikasi Anda.',
                    'url' => base_url('retur_penjualan/koor_sc/verifikasi/' . $s['id_spr'])
                ];
            }
        }

        // 1.5. KADEPUB
        if (in_array($jobdesk, ['KADEPUB', 'ADMIN'])) {
            $sprs = $this->db->get_where('tbrp_spr_header', ['status' => 'menunggu_kadepub'])->result_array();
            foreach ($sprs as $s) {
                $pending_items[] = [
                    'id' => 'spr_' . $s['id_spr'] . '_' . $s['status'],
                    'title' => 'Persetujuan SPR Jagung',
                    'body' => 'SPR ' . $s['no_spr'] . ' menunggu persetujuan Kadep Unit Bisnis.',
                    'url' => base_url('retur_penjualan/kadepub/verifikasi/' . $s['id_spr'])
                ];
            }
        }
        
        // 2. Admin Stock
        if (in_array($jobdesk, ['ADMSTOCK', 'ADMIN'])) {
            $sprs = $this->db->get_where('tbrp_spr_header', ['status' => 'diverifikasi_koor'])->result_array();
            foreach ($sprs as $s) {
                $pending_items[] = [
                    'id' => 'spr_' . $s['id_spr'] . '_' . $s['status'],
                    'title' => 'Pengecekan SPR (Admin Stock)',
                    'body' => 'SPR ' . $s['no_spr'] . ' menunggu pengecekan Anda.',
                    'url' => base_url('retur_penjualan/admin_stock/cek/' . $s['id_spr'])
                ];
            }
            
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'menunggu_verifikasi'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Verifikasi Retur (Admin Stock)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu verifikasi Anda.',
                    'url' => base_url('retur_penjualan/retur/verifikasi/' . $r['id_retur'])
                ];
            }
        }
        
        // 3. Kadep SC
        if (in_array($jobdesk, ['KADEPSC', 'ADMIN'])) {
            $sprs = $this->db->get_where('tbrp_spr_header', ['status' => 'dicek_admin_stock'])->result_array();
            foreach ($sprs as $s) {
                $pending_items[] = [
                    'id' => 'spr_' . $s['id_spr'] . '_' . $s['status'],
                    'title' => 'Persetujuan SPR (Kadep SC)',
                    'body' => 'SPR ' . $s['no_spr'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/kadep_sc/approve/' . $s['id_spr'])
                ];
            }
        }
        
        // 4. Logistik / ADMLPB2 / LOGISTIC (sprlog)
        $user_info = $this->_getUser();
        $username = strtoupper((string)$user_info['username']);
        if (in_array($jobdesk, ['ADMLPB2', 'LOGISTIC', 'ADMIN']) || $username === 'SPRLOG') {
            $sprs = $this->db->get_where('tbrp_spr_header', ['status' => 'disetujui_kadep'])->result_array();
            foreach ($sprs as $s) {
                $pending_items[] = [
                    'id' => 'spr_' . $s['id_spr'] . '_' . $s['status'],
                    'title' => 'Proses SPR (Logistik)',
                    'body' => 'SPR ' . $s['no_spr'] . ' siap diproses menjadi Retur.',
                    'url' => base_url('retur_penjualan/retur/buat/' . $s['id_spr'])
                ];
            }
        }
        
        // 5. Collection
        if (in_array($jobdesk, ['COLLECTION', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'menunggu_collection'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Proses Retur (Collection)',
                    'body' => 'Retur ' . $r['no_retur'] . ' siap diproses oleh Collection.',
                    'url' => base_url('retur_penjualan/retur/collection/' . $r['id_retur'])
                ];
            }
        }
        
        // 6. Kasir
        if (in_array($jobdesk, ['KASIR', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'menunggu_kasir'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Penyelesaian Retur (Kasir)',
                    'body' => 'Retur ' . $r['no_retur'] . ' siap diselesaikan oleh Kasir.',
                    'url' => base_url('retur_penjualan/retur/kasir/' . $r['id_retur'])
                ];
            }
        }
        
        // 6.5. Kadep UB (Retur Jagung)
        if (in_array($jobdesk, ['KADEPUB', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_kadepub'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Kadep UB)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }

        // 6.6. Manager Account (Retur)
        if (in_array($jobdesk, ['MANAGERACC', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_mngacc'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Manager Account)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }

        // 6.7. Koor SC (Retur)
        if (in_array($jobdesk, ['KOORSC', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_koorsc'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Koor SC)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }

        // 6.8. Manager SE (Retur)
        if (in_array($jobdesk, ['MANAGERSE', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_mngse'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Manager SE)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }

        // 6.9. Kadep SC (Retur)
        if (in_array($jobdesk, ['KADEPSC', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_kadepsc'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Kadep SC)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }

        // 6.10. Direktur Operasional (Retur)
        if (in_array($jobdesk, ['DIREKTUROP', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_dirop'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Direktur Operasional)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }

        // 6.11. Direktur Utama (Retur)
        if (in_array($jobdesk, ['DIREKTURUTAMA', 'ADMIN'])) {
            $returs = $this->db->get_where('tbrp_retur_penjualan_header', ['status_retur' => 'retur_menunggu_dirut'])->result_array();
            foreach ($returs as $r) {
                $pending_items[] = [
                    'id' => 'retur_' . $r['id_retur'] . '_' . $r['status_retur'],
                    'title' => 'Persetujuan Retur (Direktur Utama)',
                    'body' => 'Retur ' . $r['no_retur'] . ' menunggu persetujuan Anda.',
                    'url' => base_url('retur_penjualan/retur/detail/' . $r['id_retur'])
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['status' => true, 'data' => $pending_items]);
        exit;
    }

    /** Simpan persetujuan / penolakan Retur Penjualan oleh Manager/Direktur */
    public function retur_approve_simpan($id_retur)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('retur_penjualan/retur');
            return;
        }

        $retur = $this->M_ReturPenjualan->get_retur_penjualan($id_retur);
        if (!$retur) {
            $this->session->set_flashdata('error', 'Retur Penjualan tidak ditemukan.');
            redirect('retur_penjualan/retur');
            return;
        }

        $st = $retur['status_retur'];
        $user = $this->_getUser();
        $jobdesk = $user['jobdesk'];

        // Map status to required role check and database column prefix
        $status_map = [
            'retur_menunggu_mngacc'   => ['role' => 'MANAGERACC',   'prefix' => 'mngacc',   'label' => 'Manager Account'],
            'retur_menunggu_koorsc'   => ['role' => 'KOORSC',       'prefix' => 'koorsc',   'label' => 'Koor SC'],
            'retur_menunggu_kadepub'  => ['role' => 'KADEPUB',      'prefix' => 'kadepub',  'label' => 'Kadep UB'],
            'retur_menunggu_mngse'    => ['role' => 'MANAGERSE',    'prefix' => 'mngse',    'label' => 'Manager SE'],
            'retur_menunggu_kadepsc'  => ['role' => 'KADEPSC',      'prefix' => 'kadepsc',  'label' => 'Kadep SC'],
            'retur_menunggu_dirop'    => ['role' => 'DIREKTUROP',   'prefix' => 'dirop',    'label' => 'Direktur Operasional'],
            'retur_menunggu_dirut'    => ['role' => 'DIREKTURUTAMA', 'prefix' => 'dirut',    'label' => 'Direktur Utama'],
        ];

        if (!isset($status_map[$st])) {
            $this->session->set_flashdata('error', 'Retur Penjualan tidak sedang menunggu persetujuan Anda.');
            redirect('retur_penjualan/retur/detail/' . $id_retur);
            return;
        }

        $cfg = $status_map[$st];

        // Check permission
        if (!$this->_isJobdesk([$cfg['role'], 'ADMIN'])) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk menyetujui tahap ini.');
            redirect('retur_penjualan/retur/detail/' . $id_retur);
            return;
        }

        $aksi    = $this->input->post('aksi');
        $catatan = $this->input->post('catatan');

        if ($aksi === 'setuju') {
            $is_jagung = !empty($retur['is_jagung']);
            if ($st === 'retur_menunggu_mngacc') {
                $new_status = 'retur_menunggu_koorsc';
            } elseif ($st === 'retur_menunggu_koorsc') {
                $new_status = $is_jagung ? 'retur_menunggu_kadepub' : 'retur_menunggu_mngse';
            } elseif ($st === 'retur_menunggu_kadepub') {
                $new_status = 'retur_menunggu_mngse';
            } elseif ($st === 'retur_menunggu_mngse') {
                $new_status = 'retur_menunggu_kadepsc';
            } elseif ($st === 'retur_menunggu_kadepsc') {
                $new_status = 'retur_menunggu_dirop';
            } elseif ($st === 'retur_menunggu_dirop') {
                $new_status = 'retur_menunggu_dirut';
            } elseif ($st === 'retur_menunggu_dirut') {
                $new_status = 'menunggu_collection';
            } else {
                $new_status = 'menunggu_collection';
            }
            $log_action = 'retur_approve_' . $cfg['prefix'];
            $msg = "Retur {$retur['no_retur']} berhasil disetujui, lanjut ke tahap berikutnya.";
        } else {
            $new_status = 'ditolak';
            $log_action = 'retur_reject_' . $cfg['prefix'];
            $msg = "Retur {$retur['no_retur']} ditolak.";
        }

        // Update database header
        $update_fields = [
            $cfg['prefix'] . '_by_retur' => $user['nama'],
            $cfg['prefix'] . '_at_retur' => date('Y-m-d H:i:s'),
            'catatan_' . $cfg['prefix'] . '_retur' => $catatan,
            'update_by_retur' => $user['nama'],
        ];

        $this->M_ReturPenjualan->update_retur_penjualan_status($id_retur, $new_status, $update_fields);

        // Record Activity Log
        $this->M_ReturPenjualan->record_log(
            $retur['no_retur'],
            'retur',
            $log_action,
            $st,
            $new_status,
            $catatan,
            $user['nama']
        );

        $this->session->set_flashdata('success', $msg);
        redirect('retur_penjualan/retur/detail/' . $id_retur);
    }
}


