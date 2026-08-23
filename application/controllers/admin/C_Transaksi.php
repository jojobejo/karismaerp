<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Transaksi (Admin & ADMPNJ Transaction Hub)
 * Mengelola semua jenis transaksi di Karisma ERP khusus untuk Admin & Admin Penjualan (ADMPNJ)
 * Termasuk audit, repost, edit dengan auto-sync jurnal, delete, dan activity audit log.
 */
class C_Transaksi extends CI_Controller
{
    private $user_context;

    public function __construct()
    {
        parent::__construct();

        // 1. Validasi Login
        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request()) {
                $this->_json(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.'], 401);
            }
            redirect('Auth');
            return;
        }

        $this->load->model('M_Dashboard');
        $this->load->model('admin/M_Transaksi', 'm_transaksi');

        // 2. Validasi Hak Akses Admin & ADMPNJ
        $this->user_context = $this->M_Dashboard->current_user_context();
        $is_admin = !empty($this->user_context['is_admin']);
        $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
        $is_admpnj = in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true);

        if (!$is_admin && !$is_admpnj) {
            if ($this->input->is_ajax_request()) {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Modul transaksi ini hanya dapat diakses oleh Administrator atau Admin Penjualan.'], 403);
            }
            $this->session->set_flashdata('gagal', 'Anda tidak memiliki hak akses untuk membuka modul Transaksi.');
            redirect('dashboard');
            return;
        }
    }

    /**
     * Halaman Utama Modul Semua Transaksi Admin / Faktur Penjualan ADMPNJ
     */
    public function index()
    {
        $is_admin = !empty($this->user_context['is_admin']);
        $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
        $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

        $data['page_title'] = $is_admpnj_only ? 'Faktur Penjualan - Transaksi Penjualan' : 'Semua Transaksi - Admin Transaction Hub';
        $data['user_context'] = $this->user_context;
        $data['is_admpnj_only'] = $is_admpnj_only;
        $data['today'] = date('Y-m-d');
        $data['first_day_of_month'] = date('Y-m-01');

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/transaksi/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Endpoint AJAX: Mengambil list transaksi dengan filter & paginasi
     */
    public function ajax_get_transactions()
    {
        try {
            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            $category = $this->input->get_post('category') ?: ($is_admpnj_only ? 'penjualan' : 'all');
            if ($is_admpnj_only) {
                $category = 'penjualan';
            }

            $filters = [
                'date_from' => $this->input->get_post('date_from'),
                'date_to'   => $this->input->get_post('date_to'),
                'search'    => $this->input->get_post('search'),
                'status'    => $this->input->get_post('status'),
            ];
            $limit  = (int)($this->input->get_post('limit') ?: 50);
            $offset = (int)($this->input->get_post('offset') ?: 0);

            $result = $this->m_transaksi->get_transactions($category, $filters, $limit, $offset);
            $this->_json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil rincian transaksi beserta jurnal lengkap
     */
    public function ajax_get_detail($category = '', $idTransaksi = '')
    {
        try {
            $category = trim((string)$category) ?: $this->input->get_post('category');
            $idTransaksi = trim((string)$idTransaksi) ?: $this->input->get_post('id_transaksi');

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Parameter kategori atau ID transaksi tidak valid.'], 400);
                return;
            }

            $detail = $this->m_transaksi->get_transaction_detail($category, $idTransaksi);
            if (empty($detail['header'])) {
                $this->_json(['success' => false, 'message' => 'Data transaksi tidak ditemukan.'], 404);
                return;
            }

            $this->_json(['success' => true, 'data' => $detail]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil preview live jurnal akuntansi
     */
    public function ajax_get_journal_preview($category = '', $idTransaksi = '')
    {
        try {
            $category = trim((string)$category) ?: $this->input->get_post('category');
            $idTransaksi = trim((string)$idTransaksi) ?: $this->input->get_post('id_transaksi');

            $detail = $this->m_transaksi->get_transaction_detail($category, $idTransaksi);
            $this->_json([
                'success' => true,
                'journal' => $detail['journal'],
                'journal_lines' => $detail['journal_lines'],
            ]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil data form edit transaksi
     */
    public function ajax_get_edit_data($category = '', $idTransaksi = '')
    {
        try {
            $category = trim((string)$category) ?: $this->input->get_post('category');
            $idTransaksi = trim((string)$idTransaksi) ?: $this->input->get_post('id_transaksi');

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && $category !== 'penjualan' && $category !== 'faktur_penjualan') {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat mengedit Faktur Penjualan.'], 403);
                return;
            }

            $detail = $this->m_transaksi->get_transaction_detail($category, $idTransaksi);
            if (empty($detail['header'])) {
                $this->_json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
                return;
            }

            $this->_json(['success' => true, 'header' => $detail['header'], 'items' => $detail['items'], 'category' => $category]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Menyimpan perubahan transaksi dan sinkronisasi jurnal
     */
    public function ajax_update_transaction()
    {
        try {
            $category = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && $category !== 'penjualan' && $category !== 'faktur_penjualan') {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat mengedit Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 1);

            $payload = [
                'tanggal_transaksi' => $this->input->post('tanggal_transaksi'),
                'keterangan'        => $this->input->post('keterangan'),
                'total_nominal'     => $this->input->post('total_nominal'),
                'jumlah_diskon'     => $this->input->post('jumlah_diskon'),
                'metode_pembayaran' => $this->input->post('metode_pembayaran'),
                'items'             => $this->input->post('items'),
            ];

            $res = $this->m_transaksi->update_transaction_with_journal_sync($category, $idTransaksi, $payload, $userId);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Memproses repost transaksi & regenerasi jurnal
     */
    public function ajax_repost_transaction()
    {
        try {
            $category = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && $category !== 'penjualan' && $category !== 'faktur_penjualan') {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat memproses Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 1);

            $res = $this->m_transaksi->repost_transaction_with_journal_sync($category, $idTransaksi, $userId);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Memproses delete/void transaksi & pembersihan jurnal
     */
    public function ajax_delete_transaction()
    {
        try {
            $category = trim((string)$this->input->post('category'));
            $idTransaksi = trim((string)$this->input->post('id_transaksi'));
            $reason = trim((string)$this->input->post('reason'));

            $is_admin = !empty($this->user_context['is_admin']);
            $jobdesk = strtoupper((string)($this->user_context['jobdesk'] ?? ''));
            $is_admpnj_only = (!$is_admin && in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true));

            if ($is_admpnj_only && $category !== 'penjualan' && $category !== 'faktur_penjualan') {
                $this->_json(['success' => false, 'message' => 'Akses ditolak. Admin Penjualan hanya dapat menghapus Faktur Penjualan.'], 403);
                return;
            }

            if (empty($category) || empty($idTransaksi)) {
                $this->_json(['success' => false, 'message' => 'Kategori dan ID transaksi wajib diisi.'], 400);
                return;
            }

            $userId = (int)($this->session->userdata('id_karyawan') 
                ?: $this->session->userdata('id') 
                ?: $this->session->userdata('id_user') 
                ?: 1);

            $res = $this->m_transaksi->delete_transaction_with_journal_sync($category, $idTransaksi, $userId, $reason);
            $this->_json($res, $res['success'] ? 200 : 422);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX: Mengambil riwayat Activity Log perubahan faktur / transaksi
     */
    public function ajax_get_activity_logs()
    {
        try {
            $filters = [
                'date_from' => $this->input->get_post('date_from'),
                'date_to'   => $this->input->get_post('date_to'),
                'search'    => $this->input->get_post('search'),
            ];
            $limit  = (int)($this->input->get_post('limit') ?: 50);
            $offset = (int)($this->input->get_post('offset') ?: 0);

            $result = $this->m_transaksi->get_activity_logs($filters, $limit, $offset);
            $this->_json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->_json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper Respon JSON
     */
    private function _json($data, $statusCode = 200)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
