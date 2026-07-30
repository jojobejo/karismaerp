<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PembayaranSupplier extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PembayaranSupplier');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
        $this->load->database();
        $this->ensure_access();
    }

    public function index()
    {
        $keyword = trim((string)$this->input->get('q', true));

        $data = [
            'page_title' => 'KARISMA - PEMBAYARAN SUPPLIER',
            'keyword' => $keyword,
            'schema_ready' => $this->M_PembayaranSupplier->schema_ready(),
            'summary' => $this->M_PembayaranSupplier->summary_cards(),
            'suppliers' => $this->M_PembayaranSupplier->supplier_rows($keyword),
            'payments' => $this->M_PembayaranSupplier->payment_rows('', 10),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_supplier/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function supplier($idSupplier = null)
    {
        $idSupplier = (int)$idSupplier;
        if ($idSupplier <= 0) {
            $this->session->set_flashdata('error', 'Supplier tidak valid.');
            redirect('keuangan/pembayaran-supplier');
        }

        $supplier = $this->M_PembayaranSupplier->supplier_by_id($idSupplier);
        $documents = $this->M_PembayaranSupplier->document_rows($idSupplier);

        $data = [
            'page_title' => 'KARISMA - DETAIL HUTANG SUPPLIER',
            'supplier' => $supplier,
            'documents' => $documents,
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_supplier/detail.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function form($idSupplier = null)
    {
        $idSupplier = (int)$idSupplier;
        if ($idSupplier <= 0) {
            $this->session->set_flashdata('error', 'Supplier tidak valid.');
            redirect('keuangan/pembayaran-supplier');
        }

        $selectedDocs = $this->input->get('dokumen', true);
        if (!is_array($selectedDocs)) {
            $selectedDocs = [];
        }

        $documents = $this->M_PembayaranSupplier->selected_document_rows($idSupplier, $selectedDocs);
        if (empty($documents)) {
            $documents = $this->M_PembayaranSupplier->document_rows($idSupplier);
        }

        $data = [
            'page_title' => 'KARISMA - FORM PEMBAYARAN SUPPLIER',
            'supplier' => $this->M_PembayaranSupplier->supplier_by_id($idSupplier),
            'documents' => $documents,
            'cash_bank_accounts' => $this->M_PembayaranSupplier->cash_bank_accounts(),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_supplier/form.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function post()
    {
        if (strtoupper((string)$this->input->method()) !== 'POST') {
            show_404();
        }

        $idSupplier = (int)$this->input->post('id_supplier', true);
        $allocations = $this->posted_allocations();
        $result = $this->M_PembayaranSupplier->post_supplier_payment([
            'id_supplier' => $idSupplier,
            'id_akun_kas_bank' => (int)$this->input->post('id_akun_kas_bank', true),
            'nomor_pembayaran' => trim((string)$this->input->post('nomor_pembayaran', true)),
            'tanggal_pembayaran' => trim((string)$this->input->post('tanggal_pembayaran', true)),
            'amount' => $this->input->post('amount', true),
            'keterangan' => trim((string)$this->input->post('keterangan', true)),
            'allocations' => $allocations,
        ], $this->user_id());

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message'] . ' Nomor: ' . html_escape($result['data']['nomor_pembayaran'] ?? '-'));
            redirect('keuangan/pembayaran-supplier/history');
        }

        $this->session->set_flashdata('error', $result['message']);
        redirect('keuangan/pembayaran-supplier/form/' . $idSupplier);
    }

    public function history()
    {
        $keyword = trim((string)$this->input->get('q', true));
        $data = [
            'page_title' => 'KARISMA - HISTORI PEMBAYARAN SUPPLIER',
            'keyword' => $keyword,
            'payments' => $this->M_PembayaranSupplier->payment_rows($keyword, 200),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/pembayaran_supplier/history.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function void($idPembayaran = null)
    {
        if (strtoupper((string)$this->input->method()) !== 'POST') {
            show_404();
        }

        $result = $this->M_PembayaranSupplier->void_payment(
            (int)$idPembayaran,
            $this->input->post('reason', true),
            $this->user_id()
        );

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('keuangan/pembayaran-supplier/history');
    }

    private function posted_allocations()
    {
        $invoiceNos = $this->input->post('invoice_no', true);
        $sourceIds = $this->input->post('invoice_source_id', true);
        $amounts = $this->input->post('amount_allocated', true);
        $notes = $this->input->post('allocation_note', true);

        $invoiceNos = is_array($invoiceNos) ? $invoiceNos : [];
        $sourceIds = is_array($sourceIds) ? $sourceIds : [];
        $amounts = is_array($amounts) ? $amounts : [];
        $notes = is_array($notes) ? $notes : [];

        $rows = [];
        foreach ($invoiceNos as $index => $invoiceNo) {
            $rows[] = [
                'invoice_no' => $invoiceNo,
                'invoice_source_id' => $sourceIds[$index] ?? '',
                'amount_allocated' => $amounts[$index] ?? 0,
                'keterangan' => $notes[$index] ?? '',
            ];
        }

        return $rows;
    }

    private function ensure_access()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }

        if ($this->can_access()) {
            return;
        }

        show_error('Akses pembayaran supplier hanya untuk admin dan keuangan.', 403, 'Akses Ditolak');
    }

    private function can_access()
    {
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $level = (int)$this->session->userdata('lv');

        return $username === 'admin'
            || (bool)$this->session->userdata('is_admin_dashboard')
            || in_array($jobdesk, ['KIUKEU', 'ADMINKEU', 'ADMINKEUTC', 'ACCOUNTING', 'FINANCE'], true)
            || ($level === 1 && in_array($jobdesk, ['ADMIN', 'ADMINKEU', 'ADMINKEUTC'], true));
    }

    private function user_id()
    {
        return (int)(
            $this->session->userdata('id')
            ?: $this->session->userdata('id_karyawan')
            ?: $this->session->userdata('id_user')
            ?: 0
        ) ?: null;
    }
}
