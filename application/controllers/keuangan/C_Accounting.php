<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Accounting extends CI_Controller
{
    private $events = [
        'SALES_INVOICE' => 'Sales invoice',
        'PURCHASE_INVOICE' => 'Purchase invoice',
        'GOODS_RECEIPT' => 'LPB / Goods receipt',
        'CUSTOMER_PAYMENT' => 'Payment customer',
        'SUPPLIER_PAYMENT' => 'Payment supplier',
        'SALES_RETURN' => 'Retur penjualan',
        'PURCHASE_RETURN' => 'Retur pembelian',
        'STOCK_TRANSFER' => 'Mutasi stock',
        'STOCK_ADJUSTMENT_IN' => 'Stock adjustment masuk',
        'STOCK_ADJUSTMENT_OUT' => 'Stock adjustment keluar',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form']);
        $this->load->library('Accounting_service');
    }

    public function index()
    {
        if (!$this->require_access()) {
            return;
        }

        $data['page_title'] = 'KARISMA - ACCOUNTING TEST';
        $data['schema_ready'] = $this->accounting_service->schema_ready();
        $data['accounts'] = $this->posting_accounts();
        $data['events'] = $this->events;
        $data['mappings'] = $this->accounting_service->mapping_rows();
        $data['exceptions'] = $this->accounting_service->exception_rows('OPEN', 20);
        $data['journals'] = $this->accounting_service->journal_rows(['limit' => 20]);
        $data['dummy_sources'] = $this->dummy_sources();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/accounting_runtime_test.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    public function manual_store()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $result = $this->accounting_service->create_manual_journal($this->journal_payload(), $this->user_id());
        return $this->json_result($result, $result['success'] ? 201 : 422);
    }

    public function manual_post()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_jurnal', true);
        $result = $this->accounting_service->post_manual_journal($id, $this->user_id());
        return $this->json_result($result, $result['success'] ? 200 : 422);
    }

    public function auto_post()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $event = strtoupper(trim((string)$this->input->post('posting_event', true)));
        if (!isset($this->events[$event])) {
            return $this->json_result([
                'success' => false,
                'message' => 'Posting event tidak valid.',
                'data' => null,
                'errors' => ['INVALID_EVENT'],
            ], 422);
        }

        $payload = [
            'tanggal_transaksi' => $this->input->post('tanggal_transaksi', true),
            'keterangan' => trim((string)$this->input->post('keterangan', true)),
            'source_module' => strtoupper(trim((string)$this->input->post('source_module', true))),
            'source_type' => strtoupper(trim((string)$this->input->post('source_type', true))),
            'source_id' => trim((string)$this->input->post('source_id', true)),
            'source_no' => trim((string)$this->input->post('source_no', true)),
            'idempotency_key' => trim((string)$this->input->post('idempotency_key', true)),
            'amount' => $this->decimal_input('amount'),
            'tax' => $this->decimal_input('tax'),
            'cogs' => $this->decimal_input('cogs'),
        ];

        if ($payload['source_module'] === '') {
            $payload['source_module'] = 'MANUAL_TEST';
        }
        if ($payload['source_type'] === '') {
            $payload['source_type'] = $event;
        }
        if ($payload['source_id'] === '') {
            $payload['source_id'] = uniqid('SRC-', false);
        }
        if ($payload['source_no'] === '') {
            $payload['source_no'] = $payload['source_id'];
        }
        if ($payload['idempotency_key'] === '') {
            $payload['idempotency_key'] = $event . '-' . $payload['source_id'];
        }

        $result = $this->accounting_service->post_auto($event, $payload, $this->user_id());
        return $this->json_result($result, $result['success'] ? 201 : 422);
    }

    public function reverse()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_jurnal', true);
        $reason = trim((string)$this->input->post('reason', true));
        $result = $this->accounting_service->reverse_journal($id, $reason, $this->user_id());
        return $this->json_result($result, $result['success'] ? 201 : 422);
    }

    public function journal_detail()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_jurnal', true);
        $detail = $this->accounting_service->journal_detail($id);
        if (!$detail) {
            return $this->json_result([
                'success' => false,
                'message' => 'Jurnal tidak ditemukan.',
                'data' => null,
                'errors' => ['JOURNAL_NOT_FOUND'],
            ], 404);
        }

        return $this->json_result([
            'success' => true,
            'message' => 'Detail jurnal berhasil dimuat.',
            'data' => $detail,
            'errors' => [],
        ]);
    }

    public function journal_list()
    {
        if (!$this->require_access(true)) {
            return;
        }

        return $this->json_result([
            'success' => true,
            'message' => 'Daftar jurnal berhasil dimuat.',
            'data' => [
                'rows' => $this->accounting_service->journal_rows([
                    'status' => trim((string)$this->input->post('status', true)),
                    'date_from' => trim((string)$this->input->post('date_from', true)),
                    'date_to' => trim((string)$this->input->post('date_to', true)),
                    'limit' => 100,
                ]),
            ],
            'errors' => [],
        ]);
    }

    public function exceptions()
    {
        if (!$this->require_access(true)) {
            return;
        }

        return $this->json_result([
            'success' => true,
            'message' => 'Exception posting berhasil dimuat.',
            'data' => [
                'rows' => $this->accounting_service->exception_rows(trim((string)$this->input->post('status', true)), 100),
            ],
            'errors' => [],
        ]);
    }

    public function report()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $report = trim((string)$this->input->post('report', true));
        $dateFrom = trim((string)$this->input->post('date_from', true));
        $dateTo = trim((string)$this->input->post('date_to', true));
        $accountId = (int)$this->input->post('id_akun', true);

        return $this->json_result([
            'success' => true,
            'message' => 'Laporan berhasil dimuat.',
            'data' => [
                'rows' => $this->accounting_service->reports($report, $dateFrom, $dateTo, $accountId),
            ],
            'errors' => [],
        ]);
    }

    private function journal_payload()
    {
        $lines = json_decode((string)$this->input->post('lines_json', false), true);
        if (!is_array($lines)) {
            $lines = [];
        }

        return [
            'tanggal_transaksi' => $this->input->post('tanggal_transaksi', true),
            'keterangan' => trim((string)$this->input->post('keterangan', true)),
            'source_id' => trim((string)$this->input->post('source_id', true)),
            'source_no' => trim((string)$this->input->post('source_no', true)),
            'idempotency_key' => trim((string)$this->input->post('idempotency_key', true)),
            'lines' => $lines,
        ];
    }

    private function posting_accounts()
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return [];
        }

        $this->db->where('tipe_akun', 'POSTING');
        $this->db->where('is_active', 1);
        if ($this->db->field_exists('is_transaction_eligible', 'tbkeu_akun')) {
            $this->db->where('is_transaction_eligible', 1);
        }

        return $this->db
            ->order_by('kode_akun', 'ASC')
            ->get('tbkeu_akun')
            ->result();
    }

    private function dummy_sources()
    {
        if (!$this->db->table_exists('tbkeu_dummy_source')) {
            return [];
        }

        return $this->db
            ->order_by('posting_event', 'ASC')
            ->order_by('source_no', 'ASC')
            ->get('tbkeu_dummy_source')
            ->result();
    }

    private function can_access()
    {
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));
        $level = (int)$this->session->userdata('lv');

        return $username === 'admin'
            || (bool)$this->session->userdata('is_admin_dashboard')
            || ($level === 1 && in_array($jobdesk, ['ADMIN', 'ADMINKEU', 'ADMINKEUTC'], true));
    }

    private function require_access($json = false)
    {
        if ($this->can_access()) {
            return true;
        }

        if ($json) {
            $this->json_result([
                'success' => false,
                'message' => 'Akses accounting hanya untuk admin dan keuangan.',
                'data' => null,
                'errors' => ['FORBIDDEN'],
            ], 403);
            return false;
        }

        show_error('Akses accounting hanya untuk admin dan keuangan.', 403, 'Akses Ditolak');
        return false;
    }

    private function user_id()
    {
        return (int)$this->session->userdata('id') ?: null;
    }

    private function decimal_input($key)
    {
        $value = trim((string)$this->input->post($key, true));
        $value = str_replace(' ', '', $value);
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        return is_numeric($value) ? number_format((float)$value, 4, '.', '') : '0.0000';
    }

    private function json_result($result, $code = 200)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => (bool)($result['success'] ?? false),
                'message' => (string)($result['message'] ?? ''),
                'data' => $result['data'] ?? null,
                'errors' => $result['errors'] ?? [],
                'meta' => [
                    'request_id' => uniqid('acct_', true),
                    'timestamp' => date('c'),
                ],
            ]));
    }
}
