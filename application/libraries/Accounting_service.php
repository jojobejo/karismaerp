<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Accounting_service
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    public function schema_ready()
    {
        $tables = [
            'tbkeu_akun',
            'tbkeu_periode_fiskal',
            'tbkeu_jenis_jurnal',
            'tbkeu_jurnal',
            'tbkeu_jurnal_detail',
            'tbkeu_mapping_akun',
            'tbkeu_posting_exception',
            'tbkeu_jurnal_log',
        ];

        foreach ($tables as $table) {
            if (!$this->CI->db->table_exists($table)) {
                return false;
            }
        }

        return true;
    }

    public function mapping_rows($filters = [])
    {
        if (!$this->CI->db->table_exists('tbkeu_mapping_akun')) {
            return [];
        }

        $this->CI->db->select('m.*, a.kode_akun, a.nama_akun, a.tipe_akun, a.is_active');
        $this->CI->db->from('tbkeu_mapping_akun m');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = m.id_akun', 'left');

        if (!empty($filters['posting_event'])) {
            $this->CI->db->where('m.posting_event', $filters['posting_event']);
        }

        $this->CI->db->order_by('m.source_module', 'ASC');
        $this->CI->db->order_by('m.posting_event', 'ASC');
        $this->CI->db->order_by('m.priority', 'ASC');
        $this->CI->db->order_by('m.account_role', 'ASC');

        return $this->CI->db->get()->result();
    }

    public function exception_rows($status = 'OPEN', $limit = 100)
    {
        if (!$this->CI->db->table_exists('tbkeu_posting_exception')) {
            return [];
        }

        if ($status !== '') {
            $this->CI->db->where('status', $status);
        }

        return $this->CI->db
            ->order_by('id_exception', 'DESC')
            ->limit((int)$limit > 0 ? (int)$limit : 100)
            ->get('tbkeu_posting_exception')
            ->result();
    }

    public function journal_rows($filters = [])
    {
        if (!$this->CI->db->table_exists('tbkeu_jurnal')) {
            return [];
        }

        $this->CI->db->select('j.*, jj.kode_jenis_jurnal, jj.nama_jenis_jurnal');
        $this->CI->db->from('tbkeu_jurnal j');
        $this->CI->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');

        if (!empty($filters['status'])) {
            $this->CI->db->where('j.status', $filters['status']);
        }

        if (!empty($filters['source_module'])) {
            $this->CI->db->where('j.source_module', $filters['source_module']);
        }

        if (!empty($filters['date_from'])) {
            $this->CI->db->where('j.tanggal_transaksi >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->CI->db->where('j.tanggal_transaksi <=', $filters['date_to']);
        }

        return $this->CI->db
            ->order_by('j.tanggal_transaksi', 'DESC')
            ->order_by('j.id_jurnal', 'DESC')
            ->limit(!empty($filters['limit']) ? (int)$filters['limit'] : 100)
            ->get()
            ->result();
    }

    public function journal_detail($idJurnal)
    {
        $journal = $this->CI->db
            ->where('id_jurnal', (int)$idJurnal)
            ->get('tbkeu_jurnal')
            ->row();

        if (!$journal) {
            return null;
        }

        $this->CI->db->select('d.*, a.kode_akun, a.nama_akun');
        $this->CI->db->from('tbkeu_jurnal_detail d');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');
        $this->CI->db->where('d.id_jurnal', (int)$idJurnal);
        $this->CI->db->order_by('d.nomor_baris', 'ASC');

        return [
            'journal' => $journal,
            'details' => $this->CI->db->get()->result(),
        ];
    }

    public function create_manual_journal($payload, $userId = null)
    {
        $payload['source_module'] = 'ACCOUNTING';
        $payload['source_type'] = 'MANUAL';
        $payload['posting_event'] = 'MANUAL_JOURNAL';
        $payload['journal_type'] = 'JU';
        $payload['status'] = 'DRAFT';

        return $this->create_journal($payload, $userId, false);
    }

    public function input_manual_journal($payload, $userId = null)
    {
        return $this->create_manual_journal($payload, $userId);
    }

    public function post_manual_journal($idJurnal, $userId = null)
    {
        return $this->post_journal((int)$idJurnal, $userId);
    }

    public function validate_and_post_journal($idJurnal, $userId = null)
    {
        return $this->post_manual_journal($idJurnal, $userId);
    }

    public function post_auto($event, $payload, $userId = null)
    {
        $event = strtoupper(trim((string)$event));
        $payload['posting_event'] = $event;
        $payload['journal_type'] = 'AUTO';
        $payload['status'] = 'POSTED';
        $payload['lines'] = $this->build_auto_lines($event, $payload);

        $result = $this->create_journal($payload, $userId, true);
        if (!$result['success']) {
            $this->record_exception($payload, $result['message'], $result['errors']);
        }

        return $result;
    }

    public function post_sales($payload, $userId = null)
    {
        return $this->post_auto('SALES_INVOICE', $payload, $userId);
    }

    public function post_purchase($payload, $userId = null)
    {
        return $this->post_auto('PURCHASE_INVOICE', $payload, $userId);
    }

    public function post_lpb($payload, $userId = null)
    {
        return $this->post_auto('GOODS_RECEIPT', $payload, $userId);
    }

    public function post_payment($payload, $userId = null)
    {
        $event = strtoupper(trim((string)($payload['payment_type'] ?? 'CUSTOMER_PAYMENT')));
        if (!in_array($event, ['CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT'], true)) {
            $event = 'CUSTOMER_PAYMENT';
        }
        return $this->post_auto($event, $payload, $userId);
    }

    public function post_retur($payload, $userId = null)
    {
        $event = strtoupper(trim((string)($payload['retur_type'] ?? 'SALES_RETURN')));
        if (!in_array($event, ['SALES_RETURN', 'PURCHASE_RETURN'], true)) {
            $event = 'SALES_RETURN';
        }
        return $this->post_auto($event, $payload, $userId);
    }

    public function post_mutasi($payload, $userId = null)
    {
        return $this->post_auto('STOCK_TRANSFER', $payload, $userId);
    }

    public function post_stock_adjustment($payload, $userId = null)
    {
        $event = strtoupper(trim((string)($payload['adjustment_type'] ?? 'STOCK_ADJUSTMENT_OUT')));
        if (!in_array($event, ['STOCK_ADJUSTMENT_IN', 'STOCK_ADJUSTMENT_OUT'], true)) {
            $event = 'STOCK_ADJUSTMENT_OUT';
        }
        return $this->post_auto($event, $payload, $userId);
    }

    public function reversal_journal($idJurnal, $reason, $userId = null)
    {
        return $this->reverse_journal($idJurnal, $reason, $userId);
    }

    public function reverse_journal($idJurnal, $reason, $userId = null)
    {
        $source = $this->journal_detail($idJurnal);
        if (!$source || !$source['journal']) {
            return $this->fail('Jurnal sumber tidak ditemukan.', ['JOURNAL_NOT_FOUND']);
        }

        $journal = $source['journal'];
        if ($journal->status !== 'POSTED') {
            return $this->fail('Hanya jurnal POSTED yang dapat direversal.', ['JOURNAL_NOT_POSTED']);
        }

        $existing = $this->CI->db
            ->where('reversal_of_journal_id', (int)$idJurnal)
            ->where_in('status', ['DRAFT', 'POSTED'])
            ->get('tbkeu_jurnal')
            ->row();

        if ($existing) {
            return $this->fail('Jurnal ini sudah memiliki reversal.', ['REVERSAL_EXISTS']);
        }

        $lines = [];
        foreach ($source['details'] as $detail) {
            $lines[] = [
                'id_akun' => (int)$detail->id_akun,
                'keterangan' => 'Reversal: ' . (string)$detail->keterangan,
                'debit' => (float)$detail->kredit,
                'kredit' => (float)$detail->debit,
                'id_customer' => $detail->id_customer,
                'id_supplier' => $detail->id_supplier,
                'id_barang' => $detail->id_barang,
                'id_gudang' => $detail->id_gudang,
                'id_departemen' => $detail->id_departemen,
                'tanggal_jatuh_tempo' => $detail->tanggal_jatuh_tempo,
                'nomor_dokumen' => $detail->nomor_dokumen,
            ];
        }

        $payload = [
            'journal_type' => 'REV',
            'tanggal_transaksi' => date('Y-m-d'),
            'keterangan' => trim((string)$reason) !== '' ? trim((string)$reason) : 'Reversal ' . $journal->nomor_jurnal,
            'source_module' => 'ACCOUNTING',
            'source_type' => 'REVERSAL',
            'source_id' => (string)$journal->id_jurnal,
            'source_no' => $journal->nomor_jurnal,
            'posting_event' => 'REVERSAL',
            'idempotency_key' => 'REVERSAL-' . $journal->id_jurnal,
            'reversal_of_journal_id' => (int)$journal->id_jurnal,
            'status' => 'POSTED',
            'lines' => $lines,
        ];

        $this->CI->db->trans_begin();
        $created = $this->create_journal_inside_transaction($payload, $userId, true);

        if (!$created['success']) {
            $this->CI->db->trans_rollback();
            return $created;
        }

        $this->CI->db
            ->where('id_jurnal', (int)$journal->id_jurnal)
            ->where('status', 'POSTED')
            ->update('tbkeu_jurnal', [
                'status' => 'REVERSED',
                'reversed_by' => $userId ?: null,
                'reversed_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
                'lock_version' => (int)$journal->lock_version + 1,
            ]);

        $this->log_journal((int)$journal->id_jurnal, 'REVERSED', $reason, $userId);

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Reversal gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $created;
    }

    public function reports($report, $dateFrom, $dateTo, $accountId = 0)
    {
        $report = strtolower(trim((string)$report));
        if (!$this->schema_ready()) {
            return [];
        }

        if ($report === 'buku_besar') {
            return $this->report_ledger($dateFrom, $dateTo, $accountId);
        }

        if ($report === 'neraca_saldo') {
            return $this->report_trial_balance($dateFrom, $dateTo);
        }

        if ($report === 'laba_rugi') {
            return $this->report_by_statement($dateFrom, $dateTo, 'LABA_RUGI');
        }

        if ($report === 'neraca') {
            return $this->report_by_statement($dateFrom, $dateTo, 'NERACA');
        }

        if ($report === 'piutang') {
            return $this->report_by_control($dateFrom, $dateTo, 'PIUTANG');
        }

        if ($report === 'hutang') {
            return $this->report_by_control($dateFrom, $dateTo, 'HUTANG');
        }

        if ($report === 'kas_bank') {
            return $this->report_cash_bank($dateFrom, $dateTo);
        }

        return [];
    }

    private function create_journal($payload, $userId = null, $postNow = false)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema runtime accounting belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $this->CI->db->trans_begin();
        $result = $this->create_journal_inside_transaction($payload, $userId, $postNow);

        if (!$result['success'] || $this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return !$result['success'] ? $result : $this->fail('Transaksi database gagal.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $result;
    }

    private function create_journal_inside_transaction($payload, $userId = null, $postNow = false)
    {
        $normalized = $this->normalize_payload($payload);
        $validation = $this->validate_journal_payload($normalized, $postNow);
        if (!$validation['success']) {
            return $validation;
        }

        if ($normalized['idempotency_key'] !== '') {
            $existing = $this->CI->db
                ->where('idempotency_key', $normalized['idempotency_key'])
                ->get('tbkeu_jurnal')
                ->row();
            if ($existing) {
                return $this->ok('Jurnal sudah pernah dibuat untuk idempotency key ini.', [
                    'id_jurnal' => (int)$existing->id_jurnal,
                    'nomor_jurnal' => $existing->nomor_jurnal,
                    'idempotent' => true,
                ]);
            }
        }

        $period = $this->period_for_date($normalized['tanggal_transaksi']);
        if (!$period) {
            return $this->fail('Periode fiskal tidak ditemukan atau sudah CLOSED.', ['PERIOD_NOT_OPEN']);
        }

        $journalType = $this->journal_type($normalized['journal_type']);
        if (!$journalType) {
            return $this->fail('Jenis jurnal tidak valid atau tidak aktif.', ['JOURNAL_TYPE_NOT_FOUND']);
        }

        $totals = $this->line_totals($normalized['lines']);
        $nomor = $normalized['nomor_jurnal'] !== ''
            ? $normalized['nomor_jurnal']
            : $this->generate_journal_number($normalized['journal_type'], $normalized['tanggal_transaksi']);

        $now = date('Y-m-d H:i:s');
        $status = $postNow ? 'POSTED' : 'DRAFT';

        $this->CI->db->insert('tbkeu_jurnal', [
            'nomor_jurnal' => $nomor,
            'id_jenis_jurnal' => (int)$journalType->id_jenis_jurnal,
            'tanggal_transaksi' => $normalized['tanggal_transaksi'],
            'id_periode' => (int)$period->id_periode,
            'keterangan' => $normalized['keterangan'],
            'source_module' => $normalized['source_module'],
            'source_type' => $normalized['source_type'],
            'source_id' => $normalized['source_id'],
            'source_no' => $normalized['source_no'],
            'posting_event' => $normalized['posting_event'],
            'status' => $status,
            'total_debit' => $totals['debit'],
            'total_kredit' => $totals['kredit'],
            'reversal_of_journal_id' => $normalized['reversal_of_journal_id'] ?: null,
            'idempotency_key' => $normalized['idempotency_key'] !== '' ? $normalized['idempotency_key'] : null,
            'created_by' => $userId ?: null,
            'created_at' => $now,
            'updated_by' => $userId ?: null,
            'updated_at' => $now,
            'posted_by' => $postNow ? ($userId ?: null) : null,
            'posted_at' => $postNow ? $now : null,
        ]);

        $idJurnal = (int)$this->CI->db->insert_id();
        foreach ($normalized['lines'] as $index => $line) {
            $this->CI->db->insert('tbkeu_jurnal_detail', [
                'id_jurnal' => $idJurnal,
                'nomor_baris' => $index + 1,
                'id_akun' => (int)$line['id_akun'],
                'keterangan' => $line['keterangan'],
                'debit' => $this->money($line['debit']),
                'kredit' => $this->money($line['kredit']),
                'id_customer' => $line['id_customer'] ?: null,
                'id_supplier' => $line['id_supplier'] ?: null,
                'id_barang' => $line['id_barang'] ?: null,
                'id_gudang' => $line['id_gudang'] ?: null,
                'id_departemen' => $line['id_departemen'] ?: null,
                'tanggal_jatuh_tempo' => $line['tanggal_jatuh_tempo'] ?: null,
                'nomor_dokumen' => $line['nomor_dokumen'],
            ]);
        }

        $this->log_journal($idJurnal, $postNow ? 'POSTED' : 'DRAFT_CREATED', $normalized['keterangan'], $userId);

        return $this->ok($postNow ? 'Jurnal berhasil diposting.' : 'Draft jurnal berhasil dibuat.', [
            'id_jurnal' => $idJurnal,
            'nomor_jurnal' => $nomor,
            'status' => $status,
        ]);
    }

    private function post_journal($idJurnal, $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema runtime accounting belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $detail = $this->journal_detail($idJurnal);
        if (!$detail || !$detail['journal']) {
            return $this->fail('Jurnal tidak ditemukan.', ['JOURNAL_NOT_FOUND']);
        }

        $journal = $detail['journal'];
        if ($journal->status === 'POSTED') {
            return $this->ok('Jurnal sudah POSTED.', ['id_jurnal' => (int)$journal->id_jurnal, 'nomor_jurnal' => $journal->nomor_jurnal]);
        }

        if ($journal->status !== 'DRAFT') {
            return $this->fail('Hanya jurnal DRAFT yang dapat diposting.', ['JOURNAL_NOT_DRAFT']);
        }

        $lines = [];
        foreach ($detail['details'] as $row) {
            $lines[] = [
                'id_akun' => (int)$row->id_akun,
                'debit' => $row->debit,
                'kredit' => $row->kredit,
                'keterangan' => $row->keterangan,
            ];
        }

        $validation = $this->validate_journal_payload([
            'tanggal_transaksi' => $journal->tanggal_transaksi,
            'journal_type' => 'JU',
            'lines' => $lines,
        ], true);
        if (!$validation['success']) {
            $this->record_exception((array)$journal, $validation['message'], $validation['errors']);
            return $validation;
        }

        $this->CI->db->trans_begin();
        $now = date('Y-m-d H:i:s');
        $this->CI->db
            ->where('id_jurnal', (int)$journal->id_jurnal)
            ->where('status', 'DRAFT')
            ->update('tbkeu_jurnal', [
                'status' => 'POSTED',
                'posted_by' => $userId ?: null,
                'posted_at' => $now,
                'updated_by' => $userId ?: null,
                'updated_at' => $now,
                'lock_version' => (int)$journal->lock_version + 1,
            ]);
        $this->log_journal((int)$journal->id_jurnal, 'POSTED', 'Posting manual jurnal', $userId);

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Posting jurnal gagal.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $this->ok('Jurnal berhasil diposting.', ['id_jurnal' => (int)$journal->id_jurnal, 'nomor_jurnal' => $journal->nomor_jurnal]);
    }

    private function normalize_payload($payload)
    {
        $lines = [];
        foreach (($payload['lines'] ?? []) as $line) {
            $lines[] = [
                'id_akun' => (int)($line['id_akun'] ?? 0),
                'keterangan' => trim((string)($line['keterangan'] ?? '')),
                'debit' => $this->money($line['debit'] ?? 0),
                'kredit' => $this->money($line['kredit'] ?? 0),
                'id_customer' => (int)($line['id_customer'] ?? 0),
                'id_supplier' => (int)($line['id_supplier'] ?? 0),
                'id_barang' => (int)($line['id_barang'] ?? 0),
                'id_gudang' => (int)($line['id_gudang'] ?? 0),
                'id_departemen' => (int)($line['id_departemen'] ?? 0),
                'tanggal_jatuh_tempo' => trim((string)($line['tanggal_jatuh_tempo'] ?? '')),
                'nomor_dokumen' => trim((string)($line['nomor_dokumen'] ?? '')),
            ];
        }

        return [
            'nomor_jurnal' => trim((string)($payload['nomor_jurnal'] ?? '')),
            'journal_type' => strtoupper(trim((string)($payload['journal_type'] ?? 'JU'))),
            'tanggal_transaksi' => $this->normalize_date($payload['tanggal_transaksi'] ?? date('Y-m-d')),
            'keterangan' => trim((string)($payload['keterangan'] ?? '')),
            'source_module' => strtoupper(trim((string)($payload['source_module'] ?? 'ACCOUNTING'))),
            'source_type' => strtoupper(trim((string)($payload['source_type'] ?? 'MANUAL'))),
            'source_id' => trim((string)($payload['source_id'] ?? '')),
            'source_no' => trim((string)($payload['source_no'] ?? '')),
            'posting_event' => strtoupper(trim((string)($payload['posting_event'] ?? 'MANUAL_JOURNAL'))),
            'idempotency_key' => trim((string)($payload['idempotency_key'] ?? '')),
            'reversal_of_journal_id' => (int)($payload['reversal_of_journal_id'] ?? 0),
            'lines' => $lines,
        ];
    }

    private function validate_journal_payload($payload, $posting = false)
    {
        if (empty($payload['tanggal_transaksi'])) {
            return $this->fail('Tanggal transaksi tidak valid.', ['INVALID_DATE']);
        }

        if (empty($payload['lines']) || count($payload['lines']) < 2) {
            return $this->fail('Jurnal minimal memiliki dua baris.', ['MINIMUM_TWO_LINES']);
        }

        $totals = $this->line_totals($payload['lines']);
        if ($totals['debit'] <= 0 || $totals['kredit'] <= 0) {
            return $this->fail('Total debit dan kredit harus lebih dari nol.', ['ZERO_TOTAL']);
        }

        if (bccomp($totals['debit'], $totals['kredit'], 4) !== 0) {
            return $this->fail('Total debit harus sama dengan total kredit.', ['UNBALANCED_JOURNAL']);
        }

        foreach ($payload['lines'] as $line) {
            if ((float)$line['debit'] < 0 || (float)$line['kredit'] < 0) {
                return $this->fail('Nominal jurnal tidak boleh negatif.', ['NEGATIVE_AMOUNT']);
            }

            if ((float)$line['debit'] > 0 && (float)$line['kredit'] > 0) {
                return $this->fail('Satu baris jurnal hanya boleh debit atau kredit.', ['DOUBLE_SIDE_LINE']);
            }

            if ((float)$line['debit'] == 0.0 && (float)$line['kredit'] == 0.0) {
                return $this->fail('Baris jurnal tidak boleh bernilai nol.', ['ZERO_LINE']);
            }

            $accountCheck = $this->validate_account((int)$line['id_akun'], $posting);
            if (!$accountCheck['success']) {
                return $accountCheck;
            }
        }

        return $this->ok('Valid.');
    }

    private function validate_account($idAkun, $posting = false)
    {
        $account = $this->CI->db
            ->where('id_akun', (int)$idAkun)
            ->get('tbkeu_akun')
            ->row();

        if (!$account) {
            return $this->fail('Akun transaksi tidak ditemukan.', ['ACCOUNT_NOT_FOUND']);
        }

        if ($account->tipe_akun !== 'POSTING') {
            return $this->fail('Akun transaksi harus bertipe POSTING.', ['ACCOUNT_NOT_POSTING']);
        }

        if ((int)$account->is_active !== 1) {
            return $this->fail('Akun transaksi harus aktif.', ['ACCOUNT_INACTIVE']);
        }

        if ($this->CI->db->field_exists('is_transaction_eligible', 'tbkeu_akun') && (int)$account->is_transaction_eligible !== 1) {
            return $this->fail('Akun tidak eligible untuk transaksi.', ['ACCOUNT_NOT_ELIGIBLE']);
        }

        if (!$posting && (int)$account->allow_manual_journal !== 1) {
            return $this->fail('Akun ini tidak diizinkan untuk jurnal manual.', ['ACCOUNT_MANUAL_NOT_ALLOWED']);
        }

        return $this->ok('Akun valid.');
    }

    private function build_auto_lines($event, $payload)
    {
        $amount = $this->money($payload['amount'] ?? 0);
        $tax = $this->money($payload['tax'] ?? 0);
        $cogs = $this->money($payload['cogs'] ?? 0);
        $total = $this->money((float)$amount + (float)$tax);
        $description = trim((string)($payload['keterangan'] ?? $event));
        $lineSpecs = [];

        if ($event === 'SALES_INVOICE') {
            $lineSpecs = [
                ['ACCOUNT_RECEIVABLE', 'DEBIT', $total],
                ['SALES_REVENUE', 'KREDIT', $amount],
                ['VAT_OUTPUT', 'KREDIT', $tax],
                ['COGS', 'DEBIT', $cogs],
                ['INVENTORY', 'KREDIT', $cogs],
            ];
        } elseif ($event === 'PURCHASE_INVOICE') {
            $lineSpecs = [
                ['GRNI', 'DEBIT', $amount],
                ['VAT_INPUT', 'DEBIT', $tax],
                ['ACCOUNT_PAYABLE', 'KREDIT', $total],
            ];
        } elseif ($event === 'GOODS_RECEIPT') {
            $lineSpecs = [
                ['INVENTORY', 'DEBIT', $amount],
                ['GRNI', 'KREDIT', $amount],
            ];
        } elseif ($event === 'CUSTOMER_PAYMENT') {
            $lineSpecs = [
                ['CASH_BANK', 'DEBIT', $amount],
                ['ACCOUNT_RECEIVABLE', 'KREDIT', $amount],
            ];
        } elseif ($event === 'SUPPLIER_PAYMENT') {
            $lineSpecs = [
                ['ACCOUNT_PAYABLE', 'DEBIT', $amount],
                ['CASH_BANK', 'KREDIT', $amount],
            ];
        } elseif ($event === 'SALES_RETURN') {
            $lineSpecs = [
                ['SALES_RETURN', 'DEBIT', $amount],
                ['VAT_OUTPUT', 'DEBIT', $tax],
                ['ACCOUNT_RECEIVABLE', 'KREDIT', $total],
                ['INVENTORY', 'DEBIT', $cogs],
                ['COGS', 'KREDIT', $cogs],
            ];
        } elseif ($event === 'PURCHASE_RETURN') {
            $lineSpecs = [
                ['ACCOUNT_PAYABLE', 'DEBIT', $total],
                ['INVENTORY', 'KREDIT', $amount],
                ['VAT_INPUT', 'KREDIT', $tax],
            ];
        } elseif ($event === 'STOCK_TRANSFER') {
            $lineSpecs = [
                ['INVENTORY', 'DEBIT', $amount],
                ['INVENTORY', 'KREDIT', $amount],
            ];
        } elseif ($event === 'STOCK_ADJUSTMENT_IN') {
            $lineSpecs = [
                ['INVENTORY', 'DEBIT', $amount],
                ['STOCK_GAIN', 'KREDIT', $amount],
            ];
        } elseif ($event === 'STOCK_ADJUSTMENT_OUT') {
            $lineSpecs = [
                ['STOCK_LOSS', 'DEBIT', $amount],
                ['INVENTORY', 'KREDIT', $amount],
            ];
        }

        $lines = [];
        foreach ($lineSpecs as $spec) {
            if ((float)$spec[2] == 0.0) {
                continue;
            }

            $idAkun = $this->resolve_mapping($payload, $spec[0], $spec[1]);
            $lines[] = [
                'id_akun' => $idAkun,
                'keterangan' => $description . ' - ' . $spec[0],
                'debit' => $spec[1] === 'DEBIT' ? $spec[2] : 0,
                'kredit' => $spec[1] === 'KREDIT' ? $spec[2] : 0,
                'id_customer' => (int)($payload['id_customer'] ?? 0),
                'id_supplier' => (int)($payload['id_supplier'] ?? 0),
                'id_barang' => (int)($payload['id_barang'] ?? 0),
                'id_gudang' => (int)($payload['id_gudang'] ?? 0),
                'id_departemen' => (int)($payload['id_departemen'] ?? 0),
                'tanggal_jatuh_tempo' => trim((string)($payload['tanggal_jatuh_tempo'] ?? '')),
                'nomor_dokumen' => trim((string)($payload['source_no'] ?? '')),
            ];
        }

        return $lines;
    }

    private function resolve_mapping($payload, $role, $side)
    {
        $sourceModule = strtoupper(trim((string)($payload['source_module'] ?? '')));
        $sourceType = strtoupper(trim((string)($payload['source_type'] ?? '')));
        $event = strtoupper(trim((string)($payload['posting_event'] ?? '')));

        $this->CI->db->select('m.id_akun');
        $this->CI->db->from('tbkeu_mapping_akun m');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = m.id_akun', 'inner');
        $this->CI->db->where('m.account_role', $role);
        $this->CI->db->where('m.is_active', 1);
        $this->CI->db->where('a.tipe_akun', 'POSTING');
        $this->CI->db->where('a.is_active', 1);
        $this->CI->db->where_in('m.entry_side', [$side, 'ANY']);
        $this->CI->db->where_in('m.posting_event', [$event, '*']);
        $this->CI->db->where_in('m.source_module', [$sourceModule, '*']);
        $this->CI->db->where_in('m.source_type', [$sourceType, '*']);
        $this->CI->db->order_by('m.priority', 'ASC');
        $this->CI->db->limit(1);

        $row = $this->CI->db->get()->row();
        return $row ? (int)$row->id_akun : 0;
    }

    private function period_for_date($date)
    {
        return $this->CI->db
            ->where('tanggal_mulai <=', $date)
            ->where('tanggal_selesai >=', $date)
            ->where('status', 'OPEN')
            ->where('is_active', 1)
            ->get('tbkeu_periode_fiskal')
            ->row();
    }

    private function journal_type($kode)
    {
        return $this->CI->db
            ->where('kode_jenis_jurnal', $kode)
            ->where('is_active', 1)
            ->get('tbkeu_jenis_jurnal')
            ->row();
    }

    private function generate_journal_number($type, $date)
    {
        $prefix = strtoupper($type) . '-' . date('Ym', strtotime($date)) . '-';
        $row = $this->CI->db
            ->select('MAX(CAST(RIGHT(nomor_jurnal, 5) AS UNSIGNED)) AS last_no', false)
            ->like('nomor_jurnal', $prefix, 'after')
            ->get('tbkeu_jurnal')
            ->row();

        return $prefix . sprintf('%05d', ((int)($row->last_no ?? 0)) + 1);
    }

    private function line_totals($lines)
    {
        $debit = '0.0000';
        $kredit = '0.0000';
        foreach ($lines as $line) {
            $debit = bcadd($debit, $this->money($line['debit'] ?? 0), 4);
            $kredit = bcadd($kredit, $this->money($line['kredit'] ?? 0), 4);
        }

        return ['debit' => $debit, 'kredit' => $kredit];
    }

    private function money($value)
    {
        return number_format((float)$value, 4, '.', '');
    }

    private function normalize_date($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : '';
    }

    private function log_journal($idJurnal, $action, $message, $userId = null)
    {
        if (!$this->CI->db->table_exists('tbkeu_jurnal_log')) {
            return;
        }

        $this->CI->db->insert('tbkeu_jurnal_log', [
            'id_jurnal' => (int)$idJurnal,
            'action' => strtoupper(trim((string)$action)),
            'message' => trim((string)$message),
            'created_by' => $userId ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function record_exception($payload, $message, $errors)
    {
        if (!$this->CI->db->table_exists('tbkeu_posting_exception')) {
            return false;
        }

        return $this->CI->db->insert('tbkeu_posting_exception', [
            'source_module' => strtoupper(trim((string)($payload['source_module'] ?? ''))),
            'source_type' => strtoupper(trim((string)($payload['source_type'] ?? ''))),
            'source_id' => trim((string)($payload['source_id'] ?? '')),
            'source_no' => trim((string)($payload['source_no'] ?? '')),
            'posting_event' => strtoupper(trim((string)($payload['posting_event'] ?? ''))),
            'error_code' => !empty($errors[0]) ? (string)$errors[0] : 'POSTING_FAILED',
            'error_message' => $message,
            'payload_json' => json_encode($payload),
            'status' => 'OPEN',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function report_ledger($dateFrom, $dateTo, $accountId = 0)
    {
        $this->CI->db->select('j.tanggal_transaksi, j.nomor_jurnal, j.source_no, j.keterangan AS jurnal_keterangan, d.keterangan, d.debit, d.kredit, a.kode_akun, a.nama_akun');
        $this->CI->db->from('tbkeu_jurnal_detail d');
        $this->CI->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
        $this->CI->db->where('j.status', 'POSTED');
        $this->apply_date_range($dateFrom, $dateTo);
        if ((int)$accountId > 0) {
            $this->CI->db->where('d.id_akun', (int)$accountId);
        }
        $this->CI->db->order_by('a.kode_akun', 'ASC');
        $this->CI->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->CI->db->order_by('j.id_jurnal', 'ASC');
        $this->CI->db->order_by('d.nomor_baris', 'ASC');
        return $this->CI->db->get()->result();
    }

    private function report_trial_balance($dateFrom, $dateTo)
    {
        $this->CI->db->select('a.id_akun, a.kode_akun, a.nama_akun, a.saldo_normal, k.nama_klasifikasi, COALESCE(SUM(d.debit),0) AS debit, COALESCE(SUM(d.kredit),0) AS kredit', false);
        $this->CI->db->from('tbkeu_akun a');
        $this->CI->db->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'left');
        $this->CI->db->join('tbkeu_jurnal_detail d', 'd.id_akun = a.id_akun', 'left');
        $this->CI->db->join('tbkeu_jurnal j', "j.id_jurnal = d.id_jurnal AND j.status = 'POSTED'", 'left', false);
        if ($dateFrom !== '') {
            $this->CI->db->where('j.tanggal_transaksi >=', $dateFrom);
        }
        if ($dateTo !== '') {
            $this->CI->db->where('j.tanggal_transaksi <=', $dateTo);
        }
        $this->CI->db->where('a.tipe_akun', 'POSTING');
        $this->CI->db->group_by('a.id_akun');
        $this->CI->db->order_by('a.kode_akun', 'ASC');
        return $this->CI->db->get()->result();
    }

    private function report_by_statement($dateFrom, $dateTo, $statement)
    {
        $this->CI->db->select('k.nama_klasifikasi, a.kode_akun, a.nama_akun, a.saldo_normal, COALESCE(SUM(d.debit),0) AS debit, COALESCE(SUM(d.kredit),0) AS kredit', false);
        $this->CI->db->from('tbkeu_jurnal_detail d');
        $this->CI->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
        $this->CI->db->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'inner');
        $this->CI->db->where('j.status', 'POSTED');
        $this->CI->db->where('k.jenis_laporan', $statement);
        $this->apply_date_range($dateFrom, $dateTo);
        $this->CI->db->group_by('a.id_akun');
        $this->CI->db->order_by('k.urutan', 'ASC');
        $this->CI->db->order_by('a.kode_akun', 'ASC');
        return $this->CI->db->get()->result();
    }

    private function report_by_control($dateFrom, $dateTo, $control)
    {
        $this->CI->db->select('a.kode_akun, a.nama_akun, d.id_customer, d.id_supplier, d.nomor_dokumen, COALESCE(SUM(d.debit),0) AS debit, COALESCE(SUM(d.kredit),0) AS kredit', false);
        $this->CI->db->from('tbkeu_jurnal_detail d');
        $this->CI->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
        $this->CI->db->where('j.status', 'POSTED');
        $this->CI->db->where('a.tipe_kontrol', $control);
        $this->apply_date_range($dateFrom, $dateTo);
        $this->CI->db->group_by(['a.id_akun', 'd.id_customer', 'd.id_supplier', 'd.nomor_dokumen']);
        $this->CI->db->order_by('a.kode_akun', 'ASC');
        return $this->CI->db->get()->result();
    }

    private function report_cash_bank($dateFrom, $dateTo)
    {
        $this->CI->db->select('j.tanggal_transaksi, j.nomor_jurnal, j.source_no, a.kode_akun, a.nama_akun, d.keterangan, d.debit, d.kredit');
        $this->CI->db->from('tbkeu_jurnal_detail d');
        $this->CI->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
        $this->CI->db->where('j.status', 'POSTED');
        $this->CI->db->where_in('a.tipe_kontrol', ['KAS', 'BANK']);
        $this->apply_date_range($dateFrom, $dateTo);
        $this->CI->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->CI->db->order_by('j.id_jurnal', 'ASC');
        return $this->CI->db->get()->result();
    }

    private function apply_date_range($dateFrom, $dateTo)
    {
        if ($dateFrom !== '') {
            $this->CI->db->where('j.tanggal_transaksi >=', $dateFrom);
        }
        if ($dateTo !== '') {
            $this->CI->db->where('j.tanggal_transaksi <=', $dateTo);
        }
    }

    private function ok($message, $data = [])
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ];
    }

    private function fail($message, $errors = [])
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ];
    }
}
