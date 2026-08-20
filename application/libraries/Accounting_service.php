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
            'tbkeu_nomor_dokumen',
            'tbkeu_periode_fiskal_log',
            'tbkeu_pembayaran',
            'tbkeu_pembayaran_alokasi',
            'tbkeu_saldo_awal_akun',
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

    public function mapping_readiness()
    {
        $required = $this->required_mapping_specs();
        $result = [
            'ready' => false,
            'required_count' => count($required),
            'valid_count' => 0,
            'missing' => [],
            'invalid' => [],
        ];

        if (!$this->CI->db->table_exists('tbkeu_mapping_akun') || !$this->CI->db->table_exists('tbkeu_akun')) {
            $result['missing'] = $required;
            return $result;
        }

        foreach ($required as $spec) {
            $this->CI->db->select('m.id_mapping, m.id_akun, a.kode_akun, a.nama_akun, a.tipe_akun, a.is_active');
            $this->CI->db->from('tbkeu_mapping_akun m');
            $this->CI->db->join('tbkeu_akun a', 'a.id_akun = m.id_akun', 'left');
            $this->CI->db->where('m.posting_event', $spec['posting_event']);
            $this->CI->db->where('m.account_role', $spec['account_role']);
            $this->CI->db->where('m.entry_side', $spec['entry_side']);
            $this->CI->db->where('m.is_active', 1);
            if ($this->CI->db->field_exists('scope_type', 'tbkeu_mapping_akun')) {
                $this->CI->db->where('m.scope_type', 'GLOBAL');
                $this->CI->db->where('m.scope_key', '*');
            }
            $this->CI->db->order_by('m.priority', 'ASC');
            $row = $this->CI->db->get()->row();

            if (!$row) {
                $result['missing'][] = $spec;
                continue;
            }

            $isEligible = true;
            if ($this->CI->db->field_exists('is_transaction_eligible', 'tbkeu_akun')) {
                $account = $this->CI->db
                    ->select('is_transaction_eligible')
                    ->where('id_akun', (int)$row->id_akun)
                    ->get('tbkeu_akun')
                    ->row();
                $isEligible = $account ? (int)$account->is_transaction_eligible === 1 : false;
            }

            if ($row->tipe_akun !== 'POSTING' || (int)$row->is_active !== 1 || !$isEligible) {
                $spec['kode_akun'] = $row->kode_akun;
                $spec['nama_akun'] = $row->nama_akun;
                $result['invalid'][] = $spec;
                continue;
            }

            $result['valid_count']++;
        }

        $result['ready'] = empty($result['missing']) && empty($result['invalid']);
        return $result;
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

    public function fiscal_period_rows($status = '', $limit = 36)
    {
        if (!$this->CI->db->table_exists('tbkeu_periode_fiskal')) {
            return [];
        }

        if ($status !== '') {
            $this->CI->db->where('status', strtoupper(trim((string)$status)));
        }

        return $this->CI->db
            ->order_by('tanggal_mulai', 'DESC')
            ->limit((int)$limit > 0 ? (int)$limit : 36)
            ->get('tbkeu_periode_fiskal')
            ->result();
    }

    public function payment_rows($status = '', $limit = 100)
    {
        if (!$this->CI->db->table_exists('tbkeu_pembayaran')) {
            return [];
        }

        if ($status !== '') {
            $this->CI->db->where('status', strtoupper(trim((string)$status)));
        }

        return $this->CI->db
            ->order_by('tanggal_pembayaran', 'DESC')
            ->order_by('id_pembayaran', 'DESC')
            ->limit((int)$limit > 0 ? (int)$limit : 100)
            ->get('tbkeu_pembayaran')
            ->result();
    }

    public function opening_balance_rows($tanggal = '')
    {
        if (!$this->CI->db->table_exists('tbkeu_saldo_awal_akun')) {
            return [];
        }

        $this->CI->db->select('s.*, a.kode_akun, a.nama_akun');
        $this->CI->db->from('tbkeu_saldo_awal_akun s');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = s.id_akun', 'inner');
        if ($tanggal !== '') {
            $this->CI->db->where('s.tanggal_saldo', $tanggal);
        }
        $this->CI->db->order_by('a.kode_akun', 'ASC');

        return $this->CI->db->get()->result();
    }

    public function journal_rows($filters = [])
    {
        if (!$this->CI->db->table_exists('tbkeu_jurnal')) {
            return [];
        }

        $this->CI->db->select("j.*, jj.kode_jenis_jurnal, jj.nama_jenis_jurnal,
            CASE WHEN j.reversed_at IS NOT NULL THEN 'REVERSED' ELSE j.status END AS display_status", false);
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

        if (empty($payload['source_id']) && !empty($payload['nomor_jurnal'])) {
            $payload['source_id'] = $payload['nomor_jurnal'];
            $payload['source_no'] = $payload['nomor_jurnal'];
        }

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
        $journalType = strtoupper(trim((string)($payload['journal_type'] ?? 'AUTO')));
        $payload['journal_type'] = $journalType !== '' ? $journalType : 'AUTO';
        $payload['status'] = 'POSTED';
        if (empty($payload['lines']) || !is_array($payload['lines'])) {
            $payload['lines'] = $this->build_auto_lines($event, $payload);
        }

        $result = $this->create_journal($payload, $userId, true);
        if (!$result['success']) {
            $this->record_exception($payload, $result['message'], $result['errors']);
        }

        return $result;
    }

    public function capture_posting_exception($event, $payload, $message, $errors = [])
    {
        $payload['posting_event'] = strtoupper(trim((string)$event));
        return $this->record_exception($payload, $message, $errors);
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
        $reason = trim((string)$reason);
        if ($reason === '') {
            return $this->fail('Alasan reversal wajib diisi.', ['REVERSAL_REASON_REQUIRED']);
        }

        $this->CI->db->trans_begin();
        $journal = $this->CI->db->query(
            'SELECT * FROM tbkeu_jurnal WHERE id_jurnal = ? FOR UPDATE',
            [(int)$idJurnal]
        )->row();
        if (!$journal) {
            $this->CI->db->trans_rollback();
            return $this->fail('Jurnal sumber tidak ditemukan.', ['JOURNAL_NOT_FOUND']);
        }

        if ($journal->status !== 'POSTED' || $journal->reversed_at !== null) {
            $this->CI->db->trans_rollback();
            return $this->fail('Hanya jurnal POSTED yang dapat direversal.', ['JOURNAL_NOT_POSTED']);
        }

        $existing = $this->CI->db
            ->where('reversal_of_journal_id', (int)$idJurnal)
            ->where_in('status', ['DRAFT', 'POSTED'])
            ->get('tbkeu_jurnal')
            ->row();

        if ($existing) {
            $this->CI->db->trans_rollback();
            return $this->fail('Jurnal ini sudah memiliki reversal.', ['REVERSAL_EXISTS']);
        }

        $source = $this->journal_detail($idJurnal);
        $lines = [];
        foreach ($source['details'] as $detail) {
            $lines[] = [
                'id_akun' => (int)$detail->id_akun,
                'keterangan' => 'Reversal: ' . (string)$detail->keterangan,
                'debit' => $detail->kredit,
                'kredit' => $detail->debit,
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
            'keterangan' => $reason,
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

        $created = $this->create_journal_inside_transaction($payload, $userId, true);

        if (!$created['success']) {
            $this->CI->db->trans_rollback();
            return $created;
        }

        // Jurnal sumber tetap POSTED agar laporan membaca transaksi asli dan
        // jurnal pembalik secara bersamaan. Status reversal ditampilkan dari
        // reversed_at; nilai jurnal sumber tidak pernah diubah.
        $this->CI->db
            ->where('id_jurnal', (int)$journal->id_jurnal)
            ->where('status', 'POSTED')
            ->where('reversed_at IS NULL', null, false)
            ->update('tbkeu_jurnal', [
                'reversed_by' => $userId ?: null,
                'reversed_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
                'lock_version' => (int)$journal->lock_version + 1,
            ]);

        if ($this->CI->db->affected_rows() !== 1) {
            $this->CI->db->trans_rollback();
            return $this->fail('Jurnal berubah saat proses reversal.', ['CONCURRENT_UPDATE']);
        }

        $this->log_journal((int)$journal->id_jurnal, 'REVERSED', $reason, $userId);

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Reversal gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $created;
    }

    public function save_fiscal_period($payload, $userId = null)
    {
        if (!$this->CI->db->table_exists('tbkeu_periode_fiskal')) {
            return $this->fail('Schema periode fiskal belum tersedia.', ['SCHEMA_NOT_READY']);
        }

        $id = (int)($payload['id_periode'] ?? 0);
        $kode = trim((string)($payload['kode_periode'] ?? ''));
        $nama = trim((string)($payload['nama_periode'] ?? ''));
        $tanggalMulai = $this->normalize_date($payload['tanggal_mulai'] ?? '');
        $tanggalSelesai = $this->normalize_date($payload['tanggal_selesai'] ?? '');
        $reason = trim((string)($payload['reason'] ?? ''));

        if ($kode === '' || $nama === '' || $tanggalMulai === '' || $tanggalSelesai === '') {
            return $this->fail('Kode, nama, tanggal mulai, dan tanggal selesai periode wajib diisi.', ['INVALID_PERIOD']);
        }

        if (strtotime($tanggalMulai) > strtotime($tanggalSelesai)) {
            return $this->fail('Tanggal mulai periode tidak boleh melewati tanggal selesai.', ['INVALID_PERIOD_RANGE']);
        }

        if ($reason === '') {
            return $this->fail('Alasan/approval periode wajib diisi.', ['APPROVAL_REASON_REQUIRED']);
        }

        $overlap = $this->CI->db
            ->where('tanggal_mulai <=', $tanggalSelesai)
            ->where('tanggal_selesai >=', $tanggalMulai)
            ->where('id_periode !=', $id)
            ->get('tbkeu_periode_fiskal')
            ->row();
        if ($overlap) {
            return $this->fail('Rentang periode bertabrakan dengan ' . $overlap->kode_periode . '.', ['PERIOD_OVERLAP']);
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'kode_periode' => $kode,
            'nama_periode' => $nama,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status' => 'OPEN',
            'is_active' => 1,
            'updated_at' => $now,
        ];

        $this->CI->db->trans_begin();
        if ($id > 0) {
            $period = $this->CI->db->where('id_periode', $id)->get('tbkeu_periode_fiskal')->row();
            if (!$period) {
                $this->CI->db->trans_rollback();
                return $this->fail('Periode fiskal tidak ditemukan.', ['PERIOD_NOT_FOUND']);
            }
            if ($period->status === 'CLOSED') {
                $this->CI->db->trans_rollback();
                return $this->fail('Periode CLOSED harus dibuka melalui workflow reopen.', ['PERIOD_CLOSED']);
            }
            $this->CI->db->where('id_periode', $id)->update('tbkeu_periode_fiskal', $data);
        } else {
            $data['created_at'] = $now;
            $this->CI->db->insert('tbkeu_periode_fiskal', $data);
            $id = (int)$this->CI->db->insert_id();
        }

        $this->log_period($id, 'OPEN', $reason, $userId);

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Periode fiskal gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $this->ok('Periode fiskal berhasil disimpan dan berstatus OPEN.', ['id_periode' => $id]);
    }

    public function change_fiscal_period_status($idPeriode, $action, $reason, $userId = null)
    {
        if (!$this->CI->db->table_exists('tbkeu_periode_fiskal')) {
            return $this->fail('Schema periode fiskal belum tersedia.', ['SCHEMA_NOT_READY']);
        }

        $action = strtoupper(trim((string)$action));
        $reason = trim((string)$reason);
        if (!in_array($action, ['OPEN', 'CLOSE', 'REOPEN'], true)) {
            return $this->fail('Aksi periode fiskal tidak valid.', ['INVALID_PERIOD_ACTION']);
        }
        if ($reason === '') {
            return $this->fail('Alasan approval wajib diisi.', ['APPROVAL_REASON_REQUIRED']);
        }

        $this->CI->db->trans_begin();
        $period = $this->CI->db->query(
            'SELECT * FROM tbkeu_periode_fiskal WHERE id_periode = ? FOR UPDATE',
            [(int)$idPeriode]
        )->row();
        if (!$period) {
            $this->CI->db->trans_rollback();
            return $this->fail('Periode fiskal tidak ditemukan.', ['PERIOD_NOT_FOUND']);
        }

        $newStatus = $action === 'CLOSE' ? 'CLOSED' : 'OPEN';
        if ($action === 'CLOSE' && $period->status !== 'OPEN') {
            $this->CI->db->trans_rollback();
            return $this->fail('Hanya periode OPEN yang dapat ditutup.', ['PERIOD_NOT_OPEN']);
        }
        if ($action === 'REOPEN' && $period->status !== 'CLOSED') {
            $this->CI->db->trans_rollback();
            return $this->fail('Hanya periode CLOSED yang dapat dibuka ulang.', ['PERIOD_NOT_CLOSED']);
        }

        if ($action === 'CLOSE') {
            $draftCount = (int)$this->CI->db
                ->where('id_periode', (int)$idPeriode)
                ->where('status', 'DRAFT')
                ->count_all_results('tbkeu_jurnal');
            if ($draftCount > 0) {
                $this->CI->db->trans_rollback();
                return $this->fail('Periode masih memiliki ' . $draftCount . ' jurnal DRAFT.', ['PERIOD_HAS_DRAFT']);
            }

            $openExceptionCount = (int)$this->CI->db
                ->where('status', 'OPEN')
                ->count_all_results('tbkeu_posting_exception');
            if ($openExceptionCount > 0) {
                $this->CI->db->trans_rollback();
                return $this->fail('Masih ada ' . $openExceptionCount . ' posting exception OPEN.', ['PERIOD_HAS_OPEN_EXCEPTION']);
            }

            $unappliedCount = (int)$this->CI->db
                ->where('tanggal_pembayaran >=', $period->tanggal_mulai)
                ->where('tanggal_pembayaran <=', $period->tanggal_selesai)
                ->where('status', 'POSTED')
                ->where('unapplied_amount >', 0)
                ->count_all_results('tbkeu_pembayaran');
            if ($unappliedCount > 0) {
                $this->CI->db->trans_rollback();
                return $this->fail('Masih ada ' . $unappliedCount . ' pembayaran belum dialokasikan penuh.', ['PERIOD_HAS_UNAPPLIED_PAYMENT']);
            }

            $invalid = $this->CI->db->query(
                "SELECT j.id_jurnal
                 FROM tbkeu_jurnal j
                 LEFT JOIN tbkeu_jurnal_detail d ON d.id_jurnal = j.id_jurnal
                 WHERE j.id_periode = ? AND j.status = 'POSTED'
                 GROUP BY j.id_jurnal, j.total_debit, j.total_kredit
                 HAVING j.total_debit <> j.total_kredit OR j.total_debit <= 0
                    OR j.total_debit <> COALESCE(SUM(d.debit),0)
                    OR j.total_kredit <> COALESCE(SUM(d.kredit),0)
                 LIMIT 1",
                [(int)$idPeriode]
            )->row();
            if ($invalid) {
                $this->CI->db->trans_rollback();
                return $this->fail('Periode memiliki jurnal POSTED tidak balance/bernilai nol.', ['PERIOD_HAS_INVALID_JOURNAL']);
            }
        }

        $statusData = [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->CI->db->field_exists('closed_at', 'tbkeu_periode_fiskal')) {
            if ($action === 'CLOSE') {
                $statusData['closed_by'] = $userId ?: null;
                $statusData['closed_at'] = date('Y-m-d H:i:s');
            } elseif ($action === 'REOPEN') {
                $statusData['reopened_by'] = $userId ?: null;
                $statusData['reopened_at'] = date('Y-m-d H:i:s');
            }
        }
        $this->CI->db
            ->where('id_periode', (int)$idPeriode)
            ->update('tbkeu_periode_fiskal', $statusData);
        $this->log_period((int)$idPeriode, $action, $reason, $userId);

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Status periode fiskal gagal diubah.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $this->ok('Status periode fiskal berhasil diubah.', ['id_periode' => (int)$idPeriode, 'status' => $newStatus]);
    }

    public function create_payment($payload, $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema runtime accounting belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $paymentType = strtoupper(trim((string)($payload['payment_type'] ?? 'CUSTOMER_PAYMENT')));
        if (!in_array($paymentType, ['CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT'], true)) {
            return $this->fail('Tipe pembayaran tidak valid.', ['INVALID_PAYMENT_TYPE']);
        }

        $amount = $this->money($payload['amount'] ?? 0);
        if ((float)$amount <= 0) {
            return $this->fail('Nominal pembayaran harus lebih dari nol.', ['INVALID_PAYMENT_AMOUNT']);
        }

        $allocations = $payload['allocations'] ?? [];
        if (!is_array($allocations)) {
            $allocations = [];
        }

        $allocated = '0.0000';
        $seenInvoices = [];
        $invoiceAccountIds = [];
        foreach ($allocations as $allocation) {
            $allocationAmount = $this->money($allocation['amount_allocated'] ?? 0);
            if (bccomp($allocationAmount, '0', 4) <= 0) {
                continue;
            }
            $invoiceNo = trim((string)($allocation['invoice_no'] ?? ''));
            if ($invoiceNo === '') {
                return $this->fail('Nomor invoice wajib diisi pada setiap alokasi.', ['PAYMENT_INVOICE_REQUIRED']);
            }
            if (isset($seenInvoices[$invoiceNo])) {
                return $this->fail('Invoice tidak boleh dialokasikan lebih dari sekali.', ['DUPLICATE_PAYMENT_ALLOCATION']);
            }
            $seenInvoices[$invoiceNo] = true;
            $outstanding = $this->invoice_outstanding($paymentType, $invoiceNo);
            if (bccomp($allocationAmount, $outstanding, 4) === 1) {
                return $this->fail('Alokasi invoice ' . $invoiceNo . ' melebihi outstanding ' . $outstanding . '.', ['PAYMENT_EXCEEDS_OUTSTANDING']);
            }
            $invoiceAccountIds[$invoiceNo] = $this->invoice_outstanding_account_id($paymentType, $invoiceNo);
            $allocated = bcadd($allocated, $allocationAmount, 4);
        }
        if (bccomp($allocated, $amount, 4) === 1) {
            return $this->fail('Total alokasi tidak boleh melebihi pembayaran.', ['PAYMENT_OVER_ALLOCATED']);
        }

        $nomor = trim((string)($payload['nomor_pembayaran'] ?? $payload['source_no'] ?? ''));
        if ($nomor === '') {
            $nomor = ($paymentType === 'CUSTOMER_PAYMENT' ? 'RCV' : 'PAY') . '-' . date('YmdHis');
        }

        $existing = $this->CI->db
            ->where('nomor_pembayaran', $nomor)
            ->get('tbkeu_pembayaran')
            ->row();
        if ($existing) {
            return $this->fail('Nomor pembayaran sudah digunakan.', ['DUPLICATE_PAYMENT_NUMBER']);
        }

        $journalPayload = [
            'tanggal_transaksi' => $payload['tanggal_pembayaran'] ?? $payload['tanggal_transaksi'] ?? date('Y-m-d'),
            'keterangan' => trim((string)($payload['keterangan'] ?? $nomor)),
            'source_module' => strtoupper(trim((string)($payload['source_module'] ?? 'ACCOUNTING'))),
            'source_type' => $paymentType,
            'source_id' => trim((string)($payload['source_id'] ?? $nomor)),
            'source_no' => $nomor,
            'posting_event' => $paymentType,
            'idempotency_key' => trim((string)($payload['idempotency_key'] ?? ($paymentType . '-' . $nomor))),
            'journal_type' => 'AUTO',
            'amount' => $amount,
        ];
        $journalPayload['lines'] = $this->build_auto_lines($paymentType, $journalPayload + $payload);

        // Baris kontrol piutang/hutang dipecah per invoice supaya aging dan
        // outstanding dapat direkonsiliasi per dokumen, bukan per nomor bayar.
        if (!empty($allocations) && count($journalPayload['lines']) >= 2) {
            $cashLine = $journalPayload['lines'][$paymentType === 'CUSTOMER_PAYMENT' ? 0 : 1];
            $controlTemplate = $journalPayload['lines'][$paymentType === 'CUSTOMER_PAYMENT' ? 1 : 0];
            $journalPayload['lines'] = [$cashLine];
            foreach ($allocations as $allocation) {
                $allocationAmount = $this->money($allocation['amount_allocated'] ?? 0);
                if (bccomp($allocationAmount, '0', 4) <= 0) {
                    continue;
                }
                $controlLine = $controlTemplate;
                $controlLine[$paymentType === 'CUSTOMER_PAYMENT' ? 'kredit' : 'debit'] = $allocationAmount;
                $controlLine[$paymentType === 'CUSTOMER_PAYMENT' ? 'debit' : 'kredit'] = '0.0000';
                $controlLine['nomor_dokumen'] = trim((string)$allocation['invoice_no']);
                $controlLine['keterangan'] = $journalPayload['keterangan'] . ' - ' . $controlLine['nomor_dokumen'];
                if ($paymentType === 'SUPPLIER_PAYMENT' && !empty($invoiceAccountIds[$controlLine['nomor_dokumen']])) {
                    $controlLine['id_akun'] = (int)$invoiceAccountIds[$controlLine['nomor_dokumen']];
                }
                $journalPayload['lines'][] = $controlLine;
            }
            $unapplied = bcsub($amount, $allocated, 4);
            if (bccomp($unapplied, '0', 4) === 1) {
                $controlLine = $controlTemplate;
                $controlLine[$paymentType === 'CUSTOMER_PAYMENT' ? 'kredit' : 'debit'] = $unapplied;
                $controlLine[$paymentType === 'CUSTOMER_PAYMENT' ? 'debit' : 'kredit'] = '0.0000';
                $controlLine['nomor_dokumen'] = $nomor;
                $controlLine['keterangan'] = $journalPayload['keterangan'] . ' - belum dialokasikan';
                $journalPayload['lines'][] = $controlLine;
            }
        }

        $this->CI->db->trans_begin();
        $journal = $this->create_journal_inside_transaction($journalPayload, $userId, true);
        if (!$journal['success']) {
            $this->CI->db->trans_rollback();
            $this->record_exception($journalPayload, $journal['message'], $journal['errors']);
            return $journal;
        }

        $unapplied = bcsub($amount, $allocated, 4);
        $this->CI->db->insert('tbkeu_pembayaran', [
            'payment_type' => $paymentType,
            'nomor_pembayaran' => $nomor,
            'tanggal_pembayaran' => $journalPayload['tanggal_transaksi'],
            'source_module' => $journalPayload['source_module'],
            'source_type' => $journalPayload['source_type'],
            'source_id' => $journalPayload['source_id'],
            'source_no' => $journalPayload['source_no'],
            'id_customer' => (int)($payload['id_customer'] ?? 0) ?: null,
            'id_supplier' => (int)($payload['id_supplier'] ?? 0) ?: null,
            'amount' => $amount,
            'allocated_amount' => $allocated,
            'unapplied_amount' => $unapplied,
            'status' => 'POSTED',
            'id_jurnal' => (int)($journal['data']['id_jurnal'] ?? 0) ?: null,
            'keterangan' => $journalPayload['keterangan'],
            'created_by' => $userId ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $idPembayaran = (int)$this->CI->db->insert_id();

        foreach ($allocations as $index => $allocation) {
            $allocationAmount = $this->money($allocation['amount_allocated'] ?? 0);
            if ((float)$allocationAmount <= 0) {
                continue;
            }
            $this->CI->db->insert('tbkeu_pembayaran_alokasi', [
                'id_pembayaran' => $idPembayaran,
                'nomor_baris' => $index + 1,
                'invoice_source_module' => strtoupper(trim((string)($allocation['invoice_source_module'] ?? ''))),
                'invoice_source_type' => strtoupper(trim((string)($allocation['invoice_source_type'] ?? ''))),
                'invoice_source_id' => trim((string)($allocation['invoice_source_id'] ?? '')),
                'invoice_no' => trim((string)($allocation['invoice_no'] ?? '')),
                'amount_allocated' => $allocationAmount,
                'keterangan' => trim((string)($allocation['keterangan'] ?? '')),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Pembayaran gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $this->ok('Pembayaran berhasil diposting dan dialokasikan.', [
            'id_pembayaran' => $idPembayaran,
            'id_jurnal' => (int)($journal['data']['id_jurnal'] ?? 0),
            'nomor_pembayaran' => $nomor,
        ]);
    }

    public function create_supplier_return_deduction($payload, $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema runtime accounting belum lengkap.', ['SCHEMA_NOT_READY']);
        }
        if (!$this->CI->db->table_exists('tbkeu_pembayaran') || !$this->CI->db->table_exists('tbkeu_pembayaran_alokasi')) {
            return $this->fail('Schema pembayaran supplier belum lengkap.', ['PAYMENT_SCHEMA_NOT_READY']);
        }

        $amount = $this->money($payload['amount'] ?? 0);
        if (bccomp($amount, '0.0000', 4) <= 0) {
            return $this->fail('Nominal potong hutang harus lebih dari nol.', ['INVALID_DEDUCTION_AMOUNT']);
        }

        $nomor = trim((string)($payload['nomor_pembayaran'] ?? $payload['source_no'] ?? ''));
        if ($nomor === '') {
            $nomor = 'PHS-' . date('YmdHis');
        }

        $existing = $this->CI->db
            ->where('nomor_pembayaran', $nomor)
            ->get('tbkeu_pembayaran')
            ->row();
        if ($existing) {
            return $this->fail('Nomor potong hutang sudah digunakan.', ['DUPLICATE_PAYMENT_NUMBER']);
        }

        $debtAllocations = is_array($payload['debt_allocations'] ?? null) ? $payload['debt_allocations'] : [];
        $returnAllocations = is_array($payload['return_allocations'] ?? null) ? $payload['return_allocations'] : [];
        if (empty($debtAllocations) || empty($returnAllocations)) {
            return $this->fail('Dokumen hutang dan dokumen retur wajib dipilih.', ['DEDUCTION_ALLOCATION_REQUIRED']);
        }

        $journalPayload = [
            'tanggal_transaksi' => $payload['tanggal_pembayaran'] ?? $payload['tanggal_transaksi'] ?? date('Y-m-d'),
            'keterangan' => trim((string)($payload['keterangan'] ?? ('Potong hutang supplier ' . $nomor))),
            'source_module' => 'KEUANGAN',
            'source_type' => 'SUPPLIER_RETURN_DEDUCTION',
            'source_id' => $nomor,
            'source_no' => $nomor,
            'posting_event' => 'SUPPLIER_PAYMENT',
            'idempotency_key' => trim((string)($payload['idempotency_key'] ?? ('SUPPLIER_RETURN_DEDUCTION-' . $nomor))),
            'journal_type' => 'AUTO',
            'amount' => $amount,
            'lines' => $payload['lines'] ?? [],
        ];

        $this->CI->db->trans_begin();
        $journal = $this->create_journal_inside_transaction($journalPayload, $userId, true);
        if (!$journal['success']) {
            $this->CI->db->trans_rollback();
            $this->record_exception($journalPayload, $journal['message'], $journal['errors']);
            return $journal;
        }

        $this->CI->db->insert('tbkeu_pembayaran', [
            'payment_type' => 'SUPPLIER_PAYMENT',
            'nomor_pembayaran' => $nomor,
            'tanggal_pembayaran' => $journalPayload['tanggal_transaksi'],
            'source_module' => 'KEUANGAN',
            'source_type' => 'SUPPLIER_RETURN_DEDUCTION',
            'source_id' => $nomor,
            'source_no' => $nomor,
            'id_supplier' => (int)($payload['id_supplier'] ?? 0) ?: null,
            'amount' => $amount,
            'allocated_amount' => $amount,
            'unapplied_amount' => '0.0000',
            'status' => 'POSTED',
            'id_jurnal' => (int)($journal['data']['id_jurnal'] ?? 0) ?: null,
            'keterangan' => $journalPayload['keterangan'],
            'created_by' => $userId ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $idPembayaran = (int)$this->CI->db->insert_id();

        $baris = 1;
        foreach ($debtAllocations as $allocation) {
            $amountAllocated = $this->money($allocation['amount_allocated'] ?? 0);
            if (bccomp($amountAllocated, '0.0000', 4) <= 0) {
                continue;
            }
            $this->CI->db->insert('tbkeu_pembayaran_alokasi', [
                'id_pembayaran' => $idPembayaran,
                'nomor_baris' => $baris++,
                'invoice_source_module' => strtoupper(trim((string)($allocation['invoice_source_module'] ?? 'LOGISTIK'))),
                'invoice_source_type' => strtoupper(trim((string)($allocation['invoice_source_type'] ?? 'LPB_FINAL'))),
                'invoice_source_id' => trim((string)($allocation['invoice_source_id'] ?? '')),
                'invoice_no' => trim((string)($allocation['invoice_no'] ?? '')),
                'amount_allocated' => $amountAllocated,
                'keterangan' => trim((string)($allocation['keterangan'] ?? 'Potong hutang retur pembelian')),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        foreach ($returnAllocations as $allocation) {
            $amountAllocated = $this->money($allocation['amount_allocated'] ?? 0);
            if (bccomp($amountAllocated, '0.0000', 4) <= 0) {
                continue;
            }
            $this->CI->db->insert('tbkeu_pembayaran_alokasi', [
                'id_pembayaran' => $idPembayaran,
                'nomor_baris' => $baris++,
                'invoice_source_module' => strtoupper(trim((string)($allocation['invoice_source_module'] ?? 'LOGISTIK'))),
                'invoice_source_type' => 'RETUR_PEMBELIAN_CREDIT',
                'invoice_source_id' => trim((string)($allocation['invoice_source_id'] ?? '')),
                'invoice_no' => trim((string)($allocation['invoice_no'] ?? '')),
                'amount_allocated' => $amountAllocated,
                'keterangan' => trim((string)($allocation['keterangan'] ?? 'Sumber potong hutang retur pembelian')),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Potong hutang retur gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $this->ok('Potong hutang retur berhasil diposting.', [
            'id_pembayaran' => $idPembayaran,
            'id_jurnal' => (int)($journal['data']['id_jurnal'] ?? 0),
            'nomor_pembayaran' => $nomor,
        ]);
    }

    public function save_opening_balance($payload, $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema runtime accounting belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $idAkun = (int)($payload['id_akun'] ?? 0);
        $tanggal = $this->normalize_date($payload['tanggal_saldo'] ?? '');
        $debit = $this->money($payload['debit'] ?? 0);
        $kredit = $this->money($payload['kredit'] ?? 0);

        if ($tanggal === '') {
            return $this->fail('Tanggal saldo awal wajib diisi.', ['INVALID_DATE']);
        }
        if ((float)$debit > 0 && (float)$kredit > 0) {
            return $this->fail('Saldo awal satu akun hanya boleh debit atau kredit.', ['DOUBLE_SIDE_LINE']);
        }
        if ((float)$debit == 0.0 && (float)$kredit == 0.0) {
            return $this->fail('Saldo awal tidak boleh nol.', ['ZERO_LINE']);
        }

        $accountCheck = $this->validate_account($idAkun, true);
        if (!$accountCheck['success']) {
            return $accountCheck;
        }

        $now = date('Y-m-d H:i:s');
        $row = $this->CI->db
            ->where('id_akun', $idAkun)
            ->where('tanggal_saldo', $tanggal)
            ->get('tbkeu_saldo_awal_akun')
            ->row();

        $data = [
            'id_akun' => $idAkun,
            'tanggal_saldo' => $tanggal,
            'debit' => $debit,
            'kredit' => $kredit,
            'keterangan' => trim((string)($payload['keterangan'] ?? '')),
            'updated_by' => $userId ?: null,
            'updated_at' => $now,
        ];

        if ($row) {
            if ((int)$row->is_migrated === 1) {
                return $this->fail('Saldo awal yang sudah dimigrasikan tidak dapat diubah.', ['OPENING_BALANCE_MIGRATED']);
            }
            $this->CI->db->where('id_saldo_awal', (int)$row->id_saldo_awal)->update('tbkeu_saldo_awal_akun', $data);
            $id = (int)$row->id_saldo_awal;
        } else {
            $data['created_by'] = $userId ?: null;
            $data['created_at'] = $now;
            $this->CI->db->insert('tbkeu_saldo_awal_akun', $data);
            $id = (int)$this->CI->db->insert_id();
        }

        return $this->ok('Saldo awal akun berhasil disimpan.', ['id_saldo_awal' => $id]);
    }

    public function migrate_opening_balance($tanggal, $reason, $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema runtime accounting belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $tanggal = $this->normalize_date($tanggal);
        $reason = trim((string)$reason);
        if ($tanggal === '' || $reason === '') {
            return $this->fail('Tanggal dan alasan migrasi saldo awal wajib diisi.', ['INVALID_OPENING_MIGRATION']);
        }

        $rows = $this->opening_balance_rows($tanggal);
        if (empty($rows)) {
            return $this->fail('Saldo awal pada tanggal tersebut belum tersedia.', ['OPENING_BALANCE_EMPTY']);
        }

        $lines = [];
        $totalDebit = '0.0000';
        $totalKredit = '0.0000';
        foreach ($rows as $row) {
            $totalDebit = bcadd($totalDebit, $this->money($row->debit), 4);
            $totalKredit = bcadd($totalKredit, $this->money($row->kredit), 4);
            $lines[] = [
                'id_akun' => (int)$row->id_akun,
                'keterangan' => 'Migrasi saldo awal ' . $tanggal,
                'debit' => $row->debit,
                'kredit' => $row->kredit,
                'nomor_dokumen' => 'OPENING-' . $tanggal,
            ];
        }

        if (bccomp($totalDebit, $totalKredit, 4) !== 0) {
            return $this->fail('Total saldo awal debit dan kredit harus balance sebelum migrasi.', ['UNBALANCED_OPENING_BALANCE']);
        }

        $payload = [
            'journal_type' => 'AUTO',
            'tanggal_transaksi' => $tanggal,
            'keterangan' => $reason,
            'source_module' => 'ACCOUNTING',
            'source_type' => 'OPENING_BALANCE',
            'source_id' => $tanggal,
            'source_no' => 'OPENING-' . $tanggal,
            'posting_event' => 'OPENING_BALANCE',
            'idempotency_key' => 'OPENING_BALANCE-' . $tanggal,
            'lines' => $lines,
        ];

        $this->CI->db->trans_begin();
        $created = $this->create_journal_inside_transaction($payload, $userId, true);
        if (!$created['success']) {
            $this->CI->db->trans_rollback();
            return $created;
        }

        $this->CI->db
            ->where('tanggal_saldo', $tanggal)
            ->update('tbkeu_saldo_awal_akun', [
                'is_migrated' => 1,
                'id_jurnal' => (int)($created['data']['id_jurnal'] ?? 0) ?: null,
                'migrated_by' => $userId ?: null,
                'migrated_at' => date('Y-m-d H:i:s'),
            ]);

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Migrasi saldo awal gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();
        return $this->ok('Migrasi saldo awal berhasil diposting.', $created['data']);
    }

    public function retry_exception($idException, $userId = null)
    {
        $exception = $this->CI->db
            ->where('id_exception', (int)$idException)
            ->get('tbkeu_posting_exception')
            ->row();
        if (!$exception) {
            return $this->fail('Exception posting tidak ditemukan.', ['EXCEPTION_NOT_FOUND']);
        }
        if ($exception->status !== 'OPEN') {
            return $this->fail('Hanya exception OPEN yang dapat di-retry.', ['EXCEPTION_NOT_OPEN']);
        }

        $payload = json_decode((string)$exception->payload_json, true);
        if (!is_array($payload)) {
            return $this->fail('Payload exception tidak valid.', ['INVALID_EXCEPTION_PAYLOAD']);
        }

        if ($exception->source_module === 'LOGISTIK' && $exception->source_type === 'LPB_FINAL') {
            $this->CI->load->library('Accounting_source_service');
            $result = $this->CI->accounting_source_service->post_goods_receipt((int)$exception->source_id, $userId);
        } elseif ($exception->source_module === 'SALES' && $exception->source_type === 'FAKTUR_PENJUALAN') {
            $this->CI->load->library('Accounting_source_service');
            $result = $this->CI->accounting_source_service->post_sales_invoice((string)$exception->source_id, '', $userId);
        } else {
            $result = $this->post_auto((string)$exception->posting_event, $payload, $userId);
        }
        $update = [
            'retry_count' => (int)($exception->retry_count ?? 0) + 1,
            'last_retry_at' => date('Y-m-d H:i:s'),
        ];
        if ($result['success']) {
            $update['status'] = 'RESOLVED';
            $update['id_jurnal'] = (int)($result['data']['id_jurnal'] ?? 0) ?: null;
            $update['resolved_by'] = $userId ?: null;
            $update['resolved_at'] = date('Y-m-d H:i:s');
            $update['resolution_note'] = 'Resolved by retry';
        }
        $this->CI->db->where('id_exception', (int)$idException)->update('tbkeu_posting_exception', $update);

        return $result['success'] ? $this->ok('Exception berhasil di-retry dan resolved.', $result['data']) : $result;
    }

    public function update_exception_status($idException, $status, $note, $userId = null)
    {
        $status = strtoupper(trim((string)$status));
        $note = trim((string)$note);
        if (!in_array($status, ['RESOLVED', 'IGNORED'], true)) {
            return $this->fail('Status exception tidak valid.', ['INVALID_EXCEPTION_STATUS']);
        }
        if ($note === '') {
            return $this->fail('Catatan resolve/ignore wajib diisi.', ['RESOLUTION_NOTE_REQUIRED']);
        }

        $this->CI->db
            ->where('id_exception', (int)$idException)
            ->where('status', 'OPEN')
            ->update('tbkeu_posting_exception', [
                'status' => $status,
                'resolved_by' => $userId ?: null,
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolution_note' => $note,
            ]);

        if ($this->CI->db->affected_rows() < 1) {
            return $this->fail('Exception OPEN tidak ditemukan atau sudah diproses.', ['EXCEPTION_NOT_OPEN']);
        }

        return $this->ok('Status exception berhasil diperbarui.', ['id_exception' => (int)$idException, 'status' => $status]);
    }

    public function reports($report, $dateFrom, $dateTo, $accountId = 0)
    {
        $report = strtolower(trim((string)$report));
        if (!$this->report_schema_ready()) {
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

    private function report_schema_ready()
    {
        foreach (['tbkeu_akun', 'tbkeu_klasifikasi_akun', 'tbkeu_jurnal', 'tbkeu_jurnal_detail'] as $table) {
            if (!$this->CI->db->table_exists($table)) {
                return false;
            }
        }

        return true;
    }

    private function required_mapping_specs()
    {
        return [
            ['posting_event' => 'SALES_INVOICE', 'account_role' => 'ACCOUNT_RECEIVABLE', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'SALES_INVOICE', 'account_role' => 'SALES_REVENUE', 'entry_side' => 'KREDIT'],
            ['posting_event' => 'SALES_INVOICE', 'account_role' => 'VAT_OUTPUT', 'entry_side' => 'KREDIT'],
            ['posting_event' => 'GOODS_ISSUE', 'account_role' => 'COGS', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'GOODS_ISSUE', 'account_role' => 'INVENTORY', 'entry_side' => 'KREDIT'],
            ['posting_event' => 'GOODS_RECEIPT', 'account_role' => 'INVENTORY', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'GOODS_RECEIPT', 'account_role' => 'GRNI', 'entry_side' => 'KREDIT'],
            ['posting_event' => 'PURCHASE_INVOICE', 'account_role' => 'GRNI', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'PURCHASE_INVOICE', 'account_role' => 'VAT_INPUT', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'PURCHASE_INVOICE', 'account_role' => 'ACCOUNT_PAYABLE', 'entry_side' => 'KREDIT'],
            ['posting_event' => 'CUSTOMER_PAYMENT', 'account_role' => 'CASH_BANK', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'CUSTOMER_PAYMENT', 'account_role' => 'ACCOUNT_RECEIVABLE', 'entry_side' => 'KREDIT'],
            ['posting_event' => 'SUPPLIER_PAYMENT', 'account_role' => 'ACCOUNT_PAYABLE', 'entry_side' => 'DEBIT'],
            ['posting_event' => 'SUPPLIER_PAYMENT', 'account_role' => 'CASH_BANK', 'entry_side' => 'KREDIT'],
        ];
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
        $existing = $this->existing_journal_for($normalized);
        if ($existing) {
            return $this->idempotent_result($existing, $postNow);
        }

        $validation = $this->validate_journal_payload($normalized, $postNow);
        if (!$validation['success']) {
            return $validation;
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
        if ($nomor === '') {
            return $this->fail('Nomor jurnal gagal dibuat.', ['JOURNAL_NUMBER_FAILED']);
        }

        if (empty($normalized['source_id']) && ($normalized['source_type'] === 'MANUAL' || $normalized['posting_event'] === 'MANUAL_JOURNAL')) {
            $normalized['source_id'] = $nomor;
            $normalized['source_no'] = $nomor;
        }

        // Counter nomor di atas mengunci dan menserialkan proses insert. Cek
        // ulang setelah lock untuk menutup race dua request event sumber sama.
        $existing = $this->existing_journal_for($normalized);
        if ($existing) {
            return $this->idempotent_result($existing, $postNow);
        }

        $now = date('Y-m-d H:i:s');
        $status = $postNow ? 'POSTED' : 'DRAFT';

        $keterangan_journal = str_replace('[NOMOR_JURNAL]', $nomor, $normalized['keterangan']);

        $this->CI->db->insert('tbkeu_jurnal', [
            'nomor_jurnal' => $nomor,
            'id_jenis_jurnal' => (int)$journalType->id_jenis_jurnal,
            'tanggal_transaksi' => $normalized['tanggal_transaksi'],
            'id_periode' => (int)$period->id_periode,
            'keterangan' => $keterangan_journal,
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
                'keterangan' => str_replace('[NOMOR_JURNAL]', $nomor, $line['keterangan']),
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

        $this->log_journal($idJurnal, $postNow ? 'POSTED' : 'DRAFT_CREATED', $keterangan_journal, $userId);

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

        $this->CI->db->trans_begin();
        $journal = $this->CI->db->query(
            'SELECT * FROM tbkeu_jurnal WHERE id_jurnal = ? FOR UPDATE',
            [(int)$idJurnal]
        )->row();

        if (!$journal) {
            $this->CI->db->trans_rollback();
            return $this->fail('Jurnal tidak ditemukan.', ['JOURNAL_NOT_FOUND']);
        }

        if ($journal->status === 'POSTED') {
            $this->CI->db->trans_commit();
            return $this->ok('Jurnal sudah POSTED.', ['id_jurnal' => (int)$journal->id_jurnal, 'nomor_jurnal' => $journal->nomor_jurnal]);
        }

        if ($journal->status !== 'DRAFT') {
            $this->CI->db->trans_rollback();
            return $this->fail('Hanya jurnal DRAFT yang dapat diposting.', ['JOURNAL_NOT_DRAFT']);
        }

        $period = $this->CI->db->query(
            'SELECT * FROM tbkeu_periode_fiskal WHERE id_periode = ? FOR UPDATE',
            [(int)$journal->id_periode]
        )->row();
        if (!$period || $period->status !== 'OPEN' || (int)$period->is_active !== 1) {
            $this->CI->db->trans_rollback();
            $result = $this->fail('Periode jurnal sudah CLOSED atau tidak aktif.', ['PERIOD_NOT_OPEN']);
            $this->record_exception((array)$journal, $result['message'], $result['errors']);
            return $result;
        }

        $detailRows = $this->CI->db
            ->where('id_jurnal', (int)$journal->id_jurnal)
            ->order_by('nomor_baris', 'ASC')
            ->get('tbkeu_jurnal_detail')
            ->result();

        $lines = [];
        foreach ($detailRows as $row) {
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
        ], false);
        if (!$validation['success']) {
            $this->CI->db->trans_rollback();
            $this->record_exception((array)$journal, $validation['message'], $validation['errors']);
            return $validation;
        }

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

        if ($this->CI->db->affected_rows() !== 1) {
            $this->CI->db->trans_rollback();
            return $this->fail('Jurnal berubah saat proses posting. Muat ulang data.', ['CONCURRENT_UPDATE']);
        }
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

    private function existing_journal_for($payload)
    {
        if (!empty($payload['idempotency_key'])) {
            $row = $this->CI->db
                ->where('idempotency_key', $payload['idempotency_key'])
                ->get('tbkeu_jurnal')
                ->row();
            if ($row) {
                return $row;
            }
        }

        if (!empty($payload['source_module']) && !empty($payload['source_type'])
            && !empty($payload['source_id']) && !empty($payload['posting_event'])
            && $payload['source_type'] !== 'MANUAL' && $payload['posting_event'] !== 'MANUAL_JOURNAL') {
            return $this->CI->db
                ->where('source_module', $payload['source_module'])
                ->where('source_type', $payload['source_type'])
                ->where('source_id', $payload['source_id'])
                ->where('posting_event', $payload['posting_event'])
                ->get('tbkeu_jurnal')
                ->row();
        }

        return null;
    }

    private function idempotent_result($journal, $mustBePosted = false)
    {
        if ($mustBePosted && $journal->status !== 'POSTED') {
            return $this->fail('Event sumber sudah memiliki jurnal yang tidak POSTED.', ['SOURCE_JOURNAL_NOT_POSTED']);
        }
        return $this->ok('Jurnal sudah pernah dibuat untuk event sumber ini.', [
            'id_jurnal' => (int)$journal->id_jurnal,
            'nomor_jurnal' => $journal->nomor_jurnal,
            'idempotent' => true,
        ]);
    }

    private function validate_journal_payload($payload, $automatedPosting = false)
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

            $accountCheck = $this->validate_account((int)$line['id_akun'], $automatedPosting);
            if (!$accountCheck['success']) {
                return $accountCheck;
            }
        }

        return $this->ok('Valid.');
    }

    private function validate_account($idAkun, $automatedPosting = false)
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

        if (!$automatedPosting && (int)$account->allow_manual_journal !== 1) {
            return $this->fail('Akun ini tidak diizinkan untuk jurnal manual.', ['ACCOUNT_MANUAL_NOT_ALLOWED']);
        }

        return $this->ok('Akun valid.');
    }

    private function build_auto_lines($event, $payload)
    {
        $amount = $this->money($payload['amount'] ?? 0);
        $tax = $this->money($payload['tax'] ?? 0);
        $cogs = $this->money($payload['cogs'] ?? 0);
        $total = bcadd($amount, $tax, 4);
        $description = trim((string)($payload['keterangan'] ?? $event));
        $lineSpecs = [];

        if ($event === 'SALES_INVOICE') {
            $lineSpecs = [
                ['ACCOUNT_RECEIVABLE', 'DEBIT', $total],
            ];
            if (!empty($payload['sales_revenue_lines'])) {
                foreach ($payload['sales_revenue_lines'] as $custom_line) {
                    $lineSpecs[] = ['SALES_REVENUE', 'KREDIT', $custom_line['amount'], $custom_line['id_akun']];
                }
            } else {
                $lineSpecs[] = ['SALES_REVENUE', 'KREDIT', $amount];
            }
            $lineSpecs[] = ['VAT_OUTPUT', 'KREDIT', $tax];
        } elseif ($event === 'GOODS_ISSUE') {
            if (!empty($payload['cogs_lines']) && !empty($payload['inventory_lines'])) {
                foreach ($payload['cogs_lines'] as $custom_line) {
                    $lineSpecs[] = ['COGS', 'DEBIT', $custom_line['amount'], $custom_line['id_akun']];
                }
                foreach ($payload['inventory_lines'] as $custom_line) {
                    $lineSpecs[] = ['INVENTORY', 'KREDIT', $custom_line['amount'], $custom_line['id_akun']];
                }
            } else {
                $lineSpecs = [
                    ['COGS', 'DEBIT', $cogs],
                    ['INVENTORY', 'KREDIT', $cogs],
                ];
            }
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
            $cashAccountId = (int)($payload['cash_bank_account_id'] ?? $payload['id_akun_kas_bank'] ?? 0);
            $lineSpecs = [
                $cashAccountId > 0 ? ['CASH_BANK', 'DEBIT', $amount, $cashAccountId] : ['CASH_BANK', 'DEBIT', $amount],
                ['ACCOUNT_RECEIVABLE', 'KREDIT', $amount],
            ];
        } elseif ($event === 'SUPPLIER_PAYMENT') {
            $cashAccountId = (int)($payload['cash_bank_account_id'] ?? $payload['id_akun_kas_bank'] ?? 0);
            $lineSpecs = [
                ['ACCOUNT_PAYABLE', 'DEBIT', $amount],
                $cashAccountId > 0 ? ['CASH_BANK', 'KREDIT', $amount, $cashAccountId] : ['CASH_BANK', 'KREDIT', $amount],
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

            if (isset($spec[3])) {
                $idAkun = $spec[3];
            } else {
                $idAkun = $this->resolve_mapping($payload, $spec[0], $spec[1]);
            }
            $roleLabel = [
                'ACCOUNT_RECEIVABLE' => 'Piutang',
                'SALES_REVENUE' => 'Pendapatan',
                'VAT_OUTPUT' => 'PPN Keluaran',
                'INVENTORY' => 'Persediaan',
                'COGS' => 'HPP',
                'CASH_BANK' => 'Kas/Bank',
                'ACCOUNT_PAYABLE' => 'Hutang',
                'SALES_RETURN' => 'Retur Penjualan',
            ][$spec[0]] ?? $spec[0];
            $lines[] = [
                'id_akun' => $idAkun,
                'keterangan' => $description . ' - ' . $roleLabel,
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
        $is_pajak = !empty($payload['is_pajak'])
            || (isset($payload['tax_mode']) && $payload['tax_mode'] === 'pajak')
            || (isset($payload['tax']) && (float)$payload['tax'] > 0);

        if ($role === 'ACCOUNT_RECEIVABLE' && isset($payload['posting_event']) && $payload['posting_event'] === 'SALES_INVOICE') {
            return 461; // 13099 Piutang Usaha
        }

        if ($role === 'SALES_REVENUE') {
            if (!empty($payload['is_promosi'])) {
                return 466; // 41036 A Penjualan Barang Promosi
            } elseif (!empty($payload['is_dagangan'])) {
                return 314; // 41032 A Penjualan Barang Dagangan
            } elseif (!empty($payload['is_bkps'])) {
                return 308; // Q Penjualan BKPS (410-12)
            } else {
                return 307; // Q Penjualan BKP (410-11)
            }
        }
        if ($role === 'VAT_OUTPUT') {
            if ($is_pajak) {
                return 280; // Q PPN K (210-24)
            }
        }

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
        if ($this->CI->db->field_exists('scope_type', 'tbkeu_mapping_akun')) {
            $scopeType = strtoupper(trim((string)($payload['scope_type'] ?? 'GLOBAL')));
            $scopeKey = trim((string)($payload['scope_key'] ?? '*'));
            $this->CI->db->where_in('m.scope_type', [$scopeType, 'GLOBAL']);
            $this->CI->db->where_in('m.scope_key', [$scopeKey, '*']);
            $this->CI->db->order_by(
                'CASE WHEN m.scope_type = ' . $this->CI->db->escape($scopeType)
                . ' AND m.scope_key = ' . $this->CI->db->escape($scopeKey)
                . ' THEN 0 ELSE 1 END',
                '',
                false
            );
        }
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
        $type = strtoupper(trim((string)$type));
        if ($type === 'AUTO') {
            $period = 'ALL';
            $prefix = 'B25';
        } elseif ($type === 'SJ') {
            $period = date('ymd', strtotime($date));
            $prefix = 'SJ-' . date('dmy', strtotime($date)) . '-';
        } else {
            $period = date('Ym', strtotime($date));
            $prefix = $type . '-' . $period . '-';
        }

        $defaultInitial = $type === 'AUTO' ? -1 : 0;

        // create_journal() selalu membuka transaction sebelum method ini.
        // Counter row dikunci agar dua request paralel tidak memperoleh nomor sama.
        $this->CI->db->query(
            'INSERT INTO tbkeu_nomor_dokumen (kode_jenis_jurnal, periode_yyyymm, last_number, updated_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE updated_at = updated_at',
            [$type, $period, $defaultInitial]
        );

        $counter = $this->CI->db->query(
            'SELECT id_nomor, last_number FROM tbkeu_nomor_dokumen
             WHERE kode_jenis_jurnal = ? AND periode_yyyymm = ? FOR UPDATE',
            [$type, $period]
        )->row();

        if (!$counter) {
            return '';
        }

        $next = (int)$counter->last_number + 1;
        $this->CI->db
            ->where('id_nomor', (int)$counter->id_nomor)
            ->update('tbkeu_nomor_dokumen', [
                'last_number' => $next,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if ($type === 'AUTO') {
            return $prefix . sprintf('%07d', $next);
        } elseif ($type === 'SJ') {
            return $prefix . sprintf('%04d', $next);
        }
        return $prefix . sprintf('%05d', $next);
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
        $value = trim((string)$value);
        if ($value === '') {
            return '0.0000';
        }

        $value = str_replace(' ', '', $value);
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        if (!preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            return '0.0000';
        }

        return bcadd($value, '0', 4);
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

    private function log_period($idPeriode, $action, $reason, $userId = null)
    {
        if (!$this->CI->db->table_exists('tbkeu_periode_fiskal_log')) {
            return;
        }

        $this->CI->db->insert('tbkeu_periode_fiskal_log', [
            'id_periode' => (int)$idPeriode,
            'action' => strtoupper(trim((string)$action)),
            'reason' => trim((string)$reason),
            'approval_by' => $userId ?: null,
            'approval_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function record_exception($payload, $message, $errors)
    {
        if (!$this->CI->db->table_exists('tbkeu_posting_exception')) {
            return false;
        }

        $sourceModule = strtoupper(trim((string)($payload['source_module'] ?? '')));
        $sourceType = strtoupper(trim((string)($payload['source_type'] ?? '')));
        $sourceId = trim((string)($payload['source_id'] ?? ''));
        $event = strtoupper(trim((string)($payload['posting_event'] ?? '')));
        $errorCode = !empty($errors[0]) ? (string)$errors[0] : 'POSTING_FAILED';

        $existing = $this->CI->db
            ->where('source_module', $sourceModule)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('posting_event', $event)
            ->where('error_code', $errorCode)
            ->where('status', 'OPEN')
            ->get('tbkeu_posting_exception')
            ->row();
        if ($existing) {
            $update = [
                'error_message' => $message,
                'payload_json' => json_encode($payload),
            ];
            if ($this->CI->db->field_exists('occurrence_count', 'tbkeu_posting_exception')) {
                $this->CI->db->set('occurrence_count', 'occurrence_count + 1', false);
                $update['last_occurred_at'] = date('Y-m-d H:i:s');
            }
            return $this->CI->db->where('id_exception', (int)$existing->id_exception)->update('tbkeu_posting_exception', $update);
        }

        $data = [
            'source_module' => $sourceModule,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_no' => trim((string)($payload['source_no'] ?? '')),
            'posting_event' => $event,
            'error_code' => $errorCode,
            'error_message' => $message,
            'payload_json' => json_encode($payload),
            'status' => 'OPEN',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->CI->db->field_exists('occurrence_count', 'tbkeu_posting_exception')) {
            $data['occurrence_count'] = 1;
            $data['last_occurred_at'] = date('Y-m-d H:i:s');
        }
        return $this->CI->db->insert('tbkeu_posting_exception', $data);
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
        $rows = $this->CI->db->get()->result();
        $balances = [];
        foreach ($rows as $row) {
            $key = (string)$row->kode_akun;
            if (!isset($balances[$key])) {
                $balances[$key] = '0.0000';
            }
            $balances[$key] = bcadd($balances[$key], bcsub($this->money($row->debit), $this->money($row->kredit), 4), 4);
            $row->saldo_berjalan = $balances[$key];
        }
        return $rows;
    }

    private function report_trial_balance($dateFrom, $dateTo)
    {
        $conditions = "j.status = 'POSTED'";
        $params = [];
        if ($dateFrom !== '') {
            $conditions .= ' AND j.tanggal_transaksi >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $conditions .= ' AND j.tanggal_transaksi <= ?';
            $params[] = $dateTo;
        }

        return $this->CI->db->query(
            "SELECT a.id_akun, a.kode_akun, a.nama_akun, a.saldo_normal,
                    k.nama_klasifikasi, COALESCE(x.debit, 0) AS debit,
                    COALESCE(x.kredit, 0) AS kredit
             FROM tbkeu_akun a
             LEFT JOIN tbkeu_klasifikasi_akun k ON k.id_klasifikasi = a.id_klasifikasi
             LEFT JOIN (
                 SELECT d.id_akun, SUM(d.debit) AS debit, SUM(d.kredit) AS kredit
                 FROM tbkeu_jurnal_detail d
                 INNER JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
                 WHERE {$conditions}
                 GROUP BY d.id_akun
             ) x ON x.id_akun = a.id_akun
             WHERE a.tipe_akun = 'POSTING'
             ORDER BY a.kode_akun ASC",
            $params
        )->result();
    }

    private function report_by_statement($dateFrom, $dateTo, $statement)
    {
        $this->CI->db->select('k.id_klasifikasi, k.kode_klasifikasi, k.nama_klasifikasi, k.alias_klasifikasi, k.saldo_normal AS klasifikasi_saldo_normal, k.urutan, a.kode_akun, a.nama_akun, a.saldo_normal, COALESCE(SUM(d.debit),0) AS debit, COALESCE(SUM(d.kredit),0) AS kredit', false);
        $this->CI->db->from('tbkeu_jurnal_detail d');
        $this->CI->db->join('tbkeu_jurnal j', 'j.id_jurnal = d.id_jurnal', 'inner');
        $this->CI->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'inner');
        $this->CI->db->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'inner');
        $this->CI->db->where('j.status', 'POSTED');
        $this->CI->db->where('k.jenis_laporan', $statement);
        if ($statement === 'NERACA') {
            if ($dateTo !== '') {
                $this->CI->db->where('j.tanggal_transaksi <=', $dateTo);
            }
        } else {
            $this->apply_date_range($dateFrom, $dateTo);
        }
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

    private function invoice_outstanding($paymentType, $invoiceNo)
    {
        $control = $paymentType === 'CUSTOMER_PAYMENT' ? 'PIUTANG' : 'HUTANG';
        $extraPayableSql = '';
        if ($paymentType === 'SUPPLIER_PAYMENT') {
            $extraPayableSql = " OR a.kode_akun = '21098'";
        }

        $row = $this->CI->db->query(
            "SELECT COALESCE(SUM(d.debit),0) AS debit, COALESCE(SUM(d.kredit),0) AS kredit
             FROM tbkeu_jurnal_detail d
             INNER JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal AND j.status = 'POSTED' AND j.reversed_at IS NULL
             INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
             WHERE (a.tipe_kontrol = ?{$extraPayableSql}) AND d.nomor_dokumen = ?",
            [$control, $invoiceNo]
        )->row();
        if (!$row) {
            return '0.0000';
        }
        return $paymentType === 'CUSTOMER_PAYMENT'
            ? bcsub($this->money($row->debit), $this->money($row->kredit), 4)
            : bcsub($this->money($row->kredit), $this->money($row->debit), 4);
    }

    private function invoice_outstanding_account_id($paymentType, $invoiceNo)
    {
        $control = $paymentType === 'CUSTOMER_PAYMENT' ? 'PIUTANG' : 'HUTANG';
        $extraPayableSql = '';
        if ($paymentType === 'SUPPLIER_PAYMENT') {
            $extraPayableSql = " OR a.kode_akun = '21098'";
        }

        $row = $this->CI->db->query(
            "SELECT d.id_akun,
                    COALESCE(SUM(d.debit),0) AS debit,
                    COALESCE(SUM(d.kredit),0) AS kredit
             FROM tbkeu_jurnal_detail d
             INNER JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal AND j.status = 'POSTED' AND j.reversed_at IS NULL
             INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
             WHERE (a.tipe_kontrol = ?{$extraPayableSql}) AND d.nomor_dokumen = ?
             GROUP BY d.id_akun
             HAVING " . ($paymentType === 'CUSTOMER_PAYMENT' ? 'debit - kredit' : 'kredit - debit') . " > 0
             ORDER BY ABS(COALESCE(SUM(d.kredit),0) - COALESCE(SUM(d.debit),0)) DESC
             LIMIT 1",
            [$control, $invoiceNo]
        )->row();

        return $row ? (int)$row->id_akun : 0;
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
