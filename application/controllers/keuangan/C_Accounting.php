<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Accounting extends CI_Controller
{
    private $events = [
        'SALES_INVOICE' => 'Sales invoice',
        'GOODS_ISSUE' => 'Pengeluaran persediaan / COGS',
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

        $data['page_title'] = 'KARISMA - ACCOUNTING';
        $data['schema_ready'] = $this->accounting_service->schema_ready();
        $data['accounts'] = $this->posting_accounts();
        $data['events'] = $this->events;
        $data['mappings'] = $this->accounting_service->mapping_rows();
        $data['mapping_readiness'] = $this->accounting_service->mapping_readiness();
        $data['exceptions'] = $this->accounting_service->exception_rows('OPEN', 20);
        $data['journals'] = $this->accounting_service->journal_rows(['limit' => 20]);
        $data['periods'] = $this->accounting_service->fiscal_period_rows('', 18);
        $data['payments'] = $this->accounting_service->payment_rows('', 20);
        $data['opening_balances'] = $this->accounting_service->opening_balance_rows();
        $data['uat_mode'] = $this->is_uat_route();
        $data['dummy_sources'] = $data['uat_mode'] ? $this->dummy_sources() : [];
        $data['accounting_csrf'] = $this->accounting_csrf_token();

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
        if (!$this->is_uat_route()) {
            return $this->json_result([
                'success' => false,
                'message' => 'Simulator auto-post hanya tersedia melalui route accounting-test.',
                'data' => null,
                'errors' => ['UAT_ROUTE_REQUIRED'],
            ], 403);
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

    public function exception_action()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $id = (int)$this->input->post('id_exception', true);
        $action = strtoupper(trim((string)$this->input->post('action', true)));
        $note = trim((string)$this->input->post('note', true));

        if ($action === 'RETRY') {
            $result = $this->accounting_service->retry_exception($id, $this->user_id());
        } elseif (in_array($action, ['RESOLVED', 'IGNORED'], true)) {
            $result = $this->accounting_service->update_exception_status($id, $action, $note, $this->user_id());
        } else {
            $result = [
                'success' => false,
                'message' => 'Aksi exception tidak valid.',
                'data' => null,
                'errors' => ['INVALID_EXCEPTION_ACTION'],
            ];
        }

        return $this->json_result($result, $result['success'] ? 200 : 422);
    }

    public function period_store()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $result = $this->accounting_service->save_fiscal_period([
            'id_periode' => (int)$this->input->post('id_periode', true),
            'kode_periode' => trim((string)$this->input->post('kode_periode', true)),
            'nama_periode' => trim((string)$this->input->post('nama_periode', true)),
            'tanggal_mulai' => trim((string)$this->input->post('tanggal_mulai', true)),
            'tanggal_selesai' => trim((string)$this->input->post('tanggal_selesai', true)),
            'reason' => trim((string)$this->input->post('reason', true)),
        ], $this->user_id());

        return $this->json_result($result, $result['success'] ? 201 : 422);
    }

    public function period_action()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $result = $this->accounting_service->change_fiscal_period_status(
            (int)$this->input->post('id_periode', true),
            trim((string)$this->input->post('action', true)),
            trim((string)$this->input->post('reason', true)),
            $this->user_id()
        );

        return $this->json_result($result, $result['success'] ? 200 : 422);
    }

    public function payment_store()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $allocations = json_decode((string)$this->input->post('allocations_json', false), true);
        if (!is_array($allocations)) {
            $allocations = [];
        }

        $result = $this->accounting_service->create_payment([
            'payment_type' => trim((string)$this->input->post('payment_type', true)),
            'nomor_pembayaran' => trim((string)$this->input->post('nomor_pembayaran', true)),
            'tanggal_pembayaran' => trim((string)$this->input->post('tanggal_pembayaran', true)),
            'source_module' => trim((string)$this->input->post('source_module', true)),
            'source_id' => trim((string)$this->input->post('source_id', true)),
            'source_no' => trim((string)$this->input->post('nomor_pembayaran', true)),
            'id_customer' => (int)$this->input->post('id_customer', true),
            'id_supplier' => (int)$this->input->post('id_supplier', true),
            'amount' => $this->decimal_input('amount'),
            'keterangan' => trim((string)$this->input->post('keterangan', true)),
            'allocations' => $allocations,
        ], $this->user_id());

        return $this->json_result($result, $result['success'] ? 201 : 422);
    }

    public function opening_balance_store()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $result = $this->accounting_service->save_opening_balance([
            'id_akun' => (int)$this->input->post('id_akun', true),
            'tanggal_saldo' => trim((string)$this->input->post('tanggal_saldo', true)),
            'debit' => $this->decimal_input('debit'),
            'kredit' => $this->decimal_input('kredit'),
            'keterangan' => trim((string)$this->input->post('keterangan', true)),
        ], $this->user_id());

        return $this->json_result($result, $result['success'] ? 201 : 422);
    }

    public function opening_balance_migrate()
    {
        if (!$this->require_access(true)) {
            return;
        }

        $result = $this->accounting_service->migrate_opening_balance(
            trim((string)$this->input->post('tanggal_saldo', true)),
            trim((string)$this->input->post('reason', true)),
            $this->user_id()
        );

        return $this->json_result($result, $result['success'] ? 201 : 422);
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
            if ($json && !$this->valid_accounting_csrf()) {
                $this->json_result([
                    'success' => false,
                    'message' => 'Token keamanan accounting tidak valid atau sesi sudah berubah.',
                    'data' => null,
                    'errors' => ['INVALID_CSRF_TOKEN'],
                ], 403);
                return false;
            }
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
        return is_numeric($value) ? bcadd($value, '0', 4) : '0.0000';
    }

    private function accounting_csrf_token()
    {
        $token = (string)$this->session->userdata('accounting_csrf');
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata('accounting_csrf', $token);
        }
        return $token;
    }

    private function valid_accounting_csrf()
    {
        $expected = (string)$this->session->userdata('accounting_csrf');
        $received = (string)$this->input->post('accounting_csrf', false);
        return $expected !== '' && $received !== '' && hash_equals($expected, $received);
    }

    private function is_uat_route()
    {
        return strpos(trim((string)$this->uri->uri_string(), '/'), 'accounting-test') !== false;
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
