<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PembayaranSupplier extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function schema_ready()
    {
        foreach ([
            'tbkeu_jurnal',
            'tbkeu_jurnal_detail',
            'tbkeu_akun',
            'tbkeu_pembayaran',
            'tbkeu_pembayaran_alokasi',
            'tbpo_suplier',
        ] as $table) {
            if (!$this->db->table_exists($table)) {
                return false;
            }
        }

        return true;
    }

    public function summary_cards()
    {
        $rows = $this->supplier_rows('');
        $result = [
            'total_supplier' => 0,
            'total_dokumen' => 0,
            'total_outstanding' => 0,
            'total_payment_posted' => 0,
        ];

        foreach ($rows as $row) {
            $result['total_supplier']++;
            $result['total_dokumen'] += (int)$row['total_dokumen'];
            $result['total_outstanding'] += (float)$row['outstanding'];
        }

        if ($this->db->table_exists('tbkeu_pembayaran')) {
            $payment = $this->db
                ->select('COALESCE(SUM(amount), 0) AS total_payment', false)
                ->where('payment_type', 'SUPPLIER_PAYMENT')
                ->where('status', 'POSTED')
                ->get('tbkeu_pembayaran')
                ->row_array();
            $result['total_payment_posted'] = (float)($payment['total_payment'] ?? 0);
        }

        return $result;
    }

    public function supplier_rows($keyword = '')
    {
        if (!$this->schema_ready()) {
            return [];
        }

        $params = [];
        $searchSql = '';
        $keyword = trim((string)$keyword);
        if ($keyword !== '') {
            $searchSql = " AND (COALESCE(s.nama_suplier, '') LIKE ? OR CAST(x.id_supplier AS CHAR) LIKE ?)";
            $like = '%' . $keyword . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "
            SELECT
                x.id_supplier,
                COALESCE(NULLIF(TRIM(s.nama_suplier), ''), CONCAT('Supplier #', x.id_supplier)) AS nama_supplier,
                COUNT(*) AS total_dokumen,
                SUM(x.total_hutang) AS total_hutang,
                SUM(x.total_pengurang) AS total_pengurang,
                SUM(x.outstanding) AS outstanding,
                MIN(x.tanggal_tertua) AS tanggal_tertua,
                MAX(x.tanggal_terakhir) AS tanggal_terakhir
            FROM (
                {$this->ledger_document_sql()}
            ) x
            LEFT JOIN tbpo_suplier s ON s.id_suplier = x.id_supplier
            WHERE x.outstanding > 0
            {$searchSql}
            GROUP BY x.id_supplier, s.nama_suplier
            ORDER BY outstanding DESC, nama_supplier ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    public function supplier_by_id($idSupplier)
    {
        $row = $this->db
            ->where('id_suplier', (int)$idSupplier)
            ->get('tbpo_suplier')
            ->row_array();

        if ($row) {
            return $row;
        }

        return [
            'id_suplier' => (int)$idSupplier,
            'kd_suplier' => '',
            'nama_suplier' => 'Supplier #' . (int)$idSupplier,
        ];
    }

    public function document_rows($idSupplier)
    {
        if (!$this->schema_ready()) {
            return [];
        }

        $ledgerRows = $this->db->query(
            "
            SELECT x.*
            FROM (
                {$this->ledger_document_sql()}
            ) x
            WHERE x.id_supplier = ? AND x.outstanding > 0
            ORDER BY x.tanggal_tertua ASC, x.nomor_dokumen ASC
            ",
            [(int)$idSupplier]
        )->result_array();

        if (empty($ledgerRows)) {
            return [];
        }

        $docs = array_values(array_unique(array_column($ledgerRows, 'nomor_dokumen')));
        $meta = $this->lpb_meta_by_documents($docs);

        foreach ($ledgerRows as &$row) {
            $docNo = (string)$row['nomor_dokumen'];
            $row['lpb_meta'] = $meta[$docNo] ?? null;
            $row['status_label'] = $this->document_status_label($row, $row['lpb_meta']);
        }

        return $ledgerRows;
    }

    public function return_credit_rows($idSupplier)
    {
        if (!$this->schema_ready()) {
            return [];
        }

        return $this->db->query(
            "
            SELECT x.*
            FROM (
                SELECT
                    d.id_supplier,
                    d.nomor_dokumen,
                    MIN(j.tanggal_transaksi) AS tanggal_retur,
                    MAX(j.source_id) AS source_id,
                    MAX(j.source_no) AS source_no,
                    MAX(j.keterangan) AS keterangan,
                    COALESCE(SUM(d.debit), 0) AS total_retur,
                    COALESCE(SUM(d.kredit), 0) AS total_dipotong,
                    COALESCE(SUM(d.debit - d.kredit), 0) AS available_amount
                FROM tbkeu_jurnal_detail d
                INNER JOIN tbkeu_jurnal j
                    ON j.id_jurnal = d.id_jurnal
                   AND j.status = 'POSTED'
                   AND j.reversed_at IS NULL
                INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
                WHERE a.kode_akun = '13013'
                  AND d.id_supplier = ?
                  AND COALESCE(d.nomor_dokumen, '') <> ''
                GROUP BY d.id_supplier, d.nomor_dokumen
            ) x
            WHERE x.available_amount > 0
            ORDER BY x.tanggal_retur ASC, x.nomor_dokumen ASC
            ",
            [(int)$idSupplier]
        )->result_array();
    }

    public function selected_return_credit_rows($idSupplier, $returnNos)
    {
        $returnNos = array_values(array_filter(array_map('trim', (array)$returnNos), static function ($value) {
            return $value !== '';
        }));
        if (empty($returnNos)) {
            return [];
        }

        $rows = $this->return_credit_rows((int)$idSupplier);
        $selected = [];
        foreach ($rows as $row) {
            if (in_array((string)$row['nomor_dokumen'], $returnNos, true)) {
                $selected[] = $row;
            }
        }

        return $selected;
    }

    public function selected_document_rows($idSupplier, $invoiceNos)
    {
        $invoiceNos = array_values(array_filter(array_map('trim', (array)$invoiceNos), static function ($value) {
            return $value !== '';
        }));
        if (empty($invoiceNos)) {
            return [];
        }

        $rows = $this->document_rows((int)$idSupplier);
        $selected = [];
        foreach ($rows as $row) {
            if (in_array((string)$row['nomor_dokumen'], $invoiceNos, true)) {
                $selected[] = $row;
            }
        }

        return $selected;
    }

    public function cash_bank_accounts()
    {
        if (!$this->db->table_exists('tbkeu_akun')) {
            return [];
        }

        return $this->db
            ->select('id_akun, kode_akun, nama_akun, tipe_kontrol')
            ->where_in('tipe_kontrol', ['KAS', 'BANK'])
            ->where('tipe_akun', 'POSTING')
            ->where('is_active', 1)
            ->order_by('tipe_kontrol', 'ASC')
            ->order_by('kode_akun', 'ASC')
            ->get('tbkeu_akun')
            ->result_array();
    }

    public function payment_rows($keyword = '', $limit = 100)
    {
        if (!$this->db->table_exists('tbkeu_pembayaran')) {
            return [];
        }

        $this->db->select("
            p.*,
            COALESCE(s.nama_suplier, CONCAT('Supplier #', p.id_supplier)) AS nama_supplier,
            j.nomor_jurnal
        ", false);
        $this->db->from('tbkeu_pembayaran p');
        $this->db->join('tbpo_suplier s', 's.id_suplier = p.id_supplier', 'left');
        $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = p.id_jurnal', 'left');
        $this->db->where('p.payment_type', 'SUPPLIER_PAYMENT');

        $keyword = trim((string)$keyword);
        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('p.nomor_pembayaran', $keyword);
            $this->db->or_like('s.nama_suplier', $keyword);
            $this->db->or_like('j.nomor_jurnal', $keyword);
            $this->db->group_end();
        }

        return $this->db
            ->order_by('p.tanggal_pembayaran', 'DESC')
            ->order_by('p.id_pembayaran', 'DESC')
            ->limit((int)$limit > 0 ? (int)$limit : 100)
            ->get()
            ->result_array();
    }

    public function payment_detail($idPembayaran)
    {
        $header = $this->db
            ->select("p.*, COALESCE(s.nama_suplier, CONCAT('Supplier #', p.id_supplier)) AS nama_supplier, j.nomor_jurnal", false)
            ->from('tbkeu_pembayaran p')
            ->join('tbpo_suplier s', 's.id_suplier = p.id_supplier', 'left')
            ->join('tbkeu_jurnal j', 'j.id_jurnal = p.id_jurnal', 'left')
            ->where('p.id_pembayaran', (int)$idPembayaran)
            ->where('p.payment_type', 'SUPPLIER_PAYMENT')
            ->get()
            ->row_array();

        if (!$header) {
            return null;
        }

        $details = $this->db
            ->where('id_pembayaran', (int)$idPembayaran)
            ->order_by('nomor_baris', 'ASC')
            ->get('tbkeu_pembayaran_alokasi')
            ->result_array();

        return [
            'header' => $header,
            'details' => $details,
        ];
    }

    public function post_supplier_payment($payload, $userId = null)
    {
        $this->load->library('Accounting_service');

        if (!$this->schema_ready() || !$this->accounting_service->schema_ready()) {
            return $this->fail('Schema accounting pembayaran supplier belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $idSupplier = (int)($payload['id_supplier'] ?? 0);
        $idAkunKasBank = (int)($payload['id_akun_kas_bank'] ?? 0);
        $amount = $this->money($payload['amount'] ?? 0);
        $tanggal = $this->normalize_date($payload['tanggal_pembayaran'] ?? '');
        $nomor = trim((string)($payload['nomor_pembayaran'] ?? ''));
        $keterangan = trim((string)($payload['keterangan'] ?? ''));

        if ($idSupplier <= 0) {
            return $this->fail('Supplier wajib dipilih.', ['SUPPLIER_REQUIRED']);
        }
        if ($idAkunKasBank <= 0 || !$this->valid_cash_bank_account($idAkunKasBank)) {
            return $this->fail('Akun kas/bank pembayaran tidak valid.', ['CASH_BANK_ACCOUNT_INVALID']);
        }
        if ($tanggal === '') {
            return $this->fail('Tanggal pembayaran wajib diisi.', ['PAYMENT_DATE_REQUIRED']);
        }
        if (bccomp($amount, '0.0000', 4) <= 0) {
            return $this->fail('Nominal pembayaran harus lebih dari nol.', ['INVALID_PAYMENT_AMOUNT']);
        }
        if ($nomor === '') {
            $nomor = $this->generate_payment_number($tanggal);
        }

        $allocations = $this->normalize_allocations($payload['allocations'] ?? []);
        if (empty($allocations)) {
            return $this->fail('Minimal satu dokumen hutang harus dialokasikan.', ['ALLOCATION_REQUIRED']);
        }

        $allocated = '0.0000';
        $serviceAllocations = [];
        foreach ($allocations as $allocation) {
            $invoiceNo = $allocation['invoice_no'];
            $allocationAmount = $this->money($allocation['amount_allocated']);
            $outstanding = $this->current_document_outstanding($idSupplier, $invoiceNo);
            if (bccomp($allocationAmount, $outstanding, 4) === 1) {
                return $this->fail('Alokasi ' . $invoiceNo . ' melebihi outstanding ' . $outstanding . '.', ['PAYMENT_EXCEEDS_OUTSTANDING']);
            }
            $allocated = bcadd($allocated, $allocationAmount, 4);
            $serviceAllocations[] = [
                'invoice_source_module' => 'LOGISTIK',
                'invoice_source_type' => 'LPB_FINAL',
                'invoice_source_id' => $allocation['invoice_source_id'],
                'invoice_no' => $invoiceNo,
                'amount_allocated' => $allocationAmount,
                'keterangan' => $allocation['keterangan'],
            ];
        }

        if (bccomp($allocated, $amount, 4) !== 0) {
            return $this->fail('Total alokasi harus sama dengan nominal pembayaran untuk fase pertama.', ['PAYMENT_ALLOCATION_MISMATCH']);
        }

        $supplier = $this->supplier_by_id($idSupplier);
        $result = $this->accounting_service->create_payment([
            'payment_type' => 'SUPPLIER_PAYMENT',
            'nomor_pembayaran' => $nomor,
            'tanggal_pembayaran' => $tanggal,
            'source_module' => 'KEUANGAN',
            'source_type' => 'SUPPLIER_PAYMENT',
            'source_id' => $nomor,
            'source_no' => $nomor,
            'id_supplier' => $idSupplier,
            'amount' => $amount,
            'cash_bank_account_id' => $idAkunKasBank,
            'keterangan' => $keterangan !== '' ? $keterangan : 'Pembayaran supplier ' . ($supplier['nama_suplier'] ?? $idSupplier),
            'allocations' => $serviceAllocations,
        ], $userId);

        return $result;
    }

    public function post_return_deduction($payload, $userId = null)
    {
        $this->load->library('Accounting_service');

        if (!$this->schema_ready() || !$this->accounting_service->schema_ready()) {
            return $this->fail('Schema accounting pembayaran supplier belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $idSupplier = (int)($payload['id_supplier'] ?? 0);
        $tanggal = $this->normalize_date($payload['tanggal_pembayaran'] ?? '');
        $nomor = trim((string)($payload['nomor_pembayaran'] ?? ''));
        $keterangan = trim((string)($payload['keterangan'] ?? ''));

        if ($idSupplier <= 0) {
            return $this->fail('Supplier wajib dipilih.', ['SUPPLIER_REQUIRED']);
        }
        if ($tanggal === '') {
            return $this->fail('Tanggal potong hutang wajib diisi.', ['PAYMENT_DATE_REQUIRED']);
        }
        if ($nomor === '') {
            $nomor = $this->generate_return_deduction_number($tanggal);
        }

        $debtAllocations = $this->normalize_allocations($payload['debt_allocations'] ?? []);
        $returnAllocations = $this->normalize_allocations($payload['return_allocations'] ?? []);
        if (empty($debtAllocations) || empty($returnAllocations)) {
            return $this->fail('Dokumen hutang dan dokumen retur wajib dialokasikan.', ['ALLOCATION_REQUIRED']);
        }

        $amountDebt = '0.0000';
        $amountReturn = '0.0000';
        $lines = [];
        foreach ($debtAllocations as &$allocation) {
            $invoiceNo = $allocation['invoice_no'];
            $allocationAmount = $this->money($allocation['amount_allocated']);
            $outstanding = $this->current_document_outstanding($idSupplier, $invoiceNo);
            if (bccomp($allocationAmount, $outstanding, 4) === 1) {
                return $this->fail('Alokasi hutang ' . $invoiceNo . ' melebihi outstanding ' . $outstanding . '.', ['DEBT_EXCEEDS_OUTSTANDING']);
            }
            $idAkunHutang = $this->current_document_outstanding_account_id($idSupplier, $invoiceNo);
            if ($idAkunHutang <= 0) {
                return $this->fail('Akun hutang dokumen ' . $invoiceNo . ' tidak ditemukan.', ['DEBT_ACCOUNT_NOT_FOUND']);
            }
            $allocation['invoice_source_module'] = 'LOGISTIK';
            $allocation['invoice_source_type'] = 'LPB_FINAL';
            $amountDebt = bcadd($amountDebt, $allocationAmount, 4);
            $lines[] = [
                'id_akun' => $idAkunHutang,
                'keterangan' => 'Potong hutang retur - ' . $invoiceNo,
                'debit' => $allocationAmount,
                'kredit' => '0.0000',
                'id_supplier' => $idSupplier,
                'nomor_dokumen' => $invoiceNo,
            ];
        }
        unset($allocation);

        $idAkunRetur = $this->account_id_by_code('13013');
        if ($idAkunRetur <= 0) {
            return $this->fail('Akun 13013 belum valid di COA.', ['RETURN_CREDIT_ACCOUNT_NOT_FOUND']);
        }

        foreach ($returnAllocations as &$allocation) {
            $returnNo = $allocation['invoice_no'];
            $allocationAmount = $this->money($allocation['amount_allocated']);
            $available = $this->current_return_credit_available($idSupplier, $returnNo);
            if (bccomp($allocationAmount, $available, 4) === 1) {
                return $this->fail('Alokasi retur ' . $returnNo . ' melebihi saldo retur ' . $available . '.', ['RETURN_EXCEEDS_AVAILABLE']);
            }
            $allocation['invoice_source_module'] = 'LOGISTIK';
            $allocation['invoice_source_type'] = 'RETUR_PEMBELIAN_CREDIT';
            $amountReturn = bcadd($amountReturn, $allocationAmount, 4);
            $lines[] = [
                'id_akun' => $idAkunRetur,
                'keterangan' => 'Pemakaian retur pembelian - ' . $returnNo,
                'debit' => '0.0000',
                'kredit' => $allocationAmount,
                'id_supplier' => $idSupplier,
                'nomor_dokumen' => $returnNo,
            ];
        }
        unset($allocation);

        if (bccomp($amountDebt, $amountReturn, 4) !== 0) {
            return $this->fail('Total potong hutang harus sama dengan total retur yang dipakai.', ['DEDUCTION_ALLOCATION_MISMATCH']);
        }

        $supplier = $this->supplier_by_id($idSupplier);
        return $this->accounting_service->create_supplier_return_deduction([
            'id_supplier' => $idSupplier,
            'nomor_pembayaran' => $nomor,
            'tanggal_pembayaran' => $tanggal,
            'amount' => $amountDebt,
            'keterangan' => $keterangan !== '' ? $keterangan : 'Potong hutang retur pembelian ' . ($supplier['nama_suplier'] ?? $idSupplier),
            'debt_allocations' => $debtAllocations,
            'return_allocations' => $returnAllocations,
            'lines' => $lines,
        ], $userId);
    }

    public function void_payment($idPembayaran, $reason, $userId = null)
    {
        $reason = trim((string)$reason);
        if ($reason === '') {
            return $this->fail('Alasan void wajib diisi.', ['VOID_REASON_REQUIRED']);
        }

        $payment = $this->db
            ->where('id_pembayaran', (int)$idPembayaran)
            ->where('payment_type', 'SUPPLIER_PAYMENT')
            ->get('tbkeu_pembayaran')
            ->row_array();

        if (!$payment || $payment['status'] !== 'POSTED' || (int)$payment['id_jurnal'] <= 0) {
            return $this->fail('Hanya pembayaran supplier POSTED yang dapat di-void.', ['PAYMENT_NOT_POSTED']);
        }

        $this->load->library('Accounting_service');
        $this->db->trans_begin();

        $reversal = $this->accounting_service->reversal_journal(
            (int)$payment['id_jurnal'],
            'Void pembayaran supplier ' . $payment['nomor_pembayaran'] . ': ' . $reason,
            $userId
        );

        if (!$reversal['success']) {
            $this->db->trans_rollback();
            return $reversal;
        }

        $this->db
            ->where('id_pembayaran', (int)$idPembayaran)
            ->where('status', 'POSTED')
            ->update('tbkeu_pembayaran', [
                'status' => 'VOID',
                'updated_by' => $userId ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Void pembayaran supplier gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Pembayaran supplier berhasil di-void dengan jurnal reversal.', $reversal['data']);
    }

    private function ledger_document_sql()
    {
        return "
            SELECT
                d.id_supplier,
                d.nomor_dokumen,
                MIN(j.tanggal_transaksi) AS tanggal_tertua,
                MAX(j.tanggal_transaksi) AS tanggal_terakhir,
                COALESCE(SUM(d.kredit), 0) AS total_hutang,
                COALESCE(SUM(d.debit), 0) AS total_pengurang,
                COALESCE(SUM(d.kredit - d.debit), 0) AS outstanding
            FROM tbkeu_jurnal_detail d
            INNER JOIN tbkeu_jurnal j
                ON j.id_jurnal = d.id_jurnal
               AND j.status = 'POSTED'
               AND j.reversed_at IS NULL
            INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
            WHERE (a.tipe_kontrol = 'HUTANG' OR a.kode_akun = '21098')
              AND COALESCE(d.nomor_dokumen, '') <> ''
            GROUP BY d.id_supplier, d.nomor_dokumen
        ";
    }

    private function lpb_meta_by_documents($documents)
    {
        if (empty($documents) || !$this->db->table_exists('tb_lpb')) {
            return [];
        }

        $this->db->select("
            l.nomor_lpb,
            GROUP_CONCAT(DISTINCT l.id_lpb ORDER BY l.id_lpb ASC SEPARATOR ', ') AS id_lpb_list,
            GROUP_CONCAT(DISTINCT NULLIF(l.no_po, '') ORDER BY l.id_lpb ASC SEPARATOR ', ') AS no_po_list,
            GROUP_CONCAT(DISTINCT NULLIF(l.no_invoice, '') ORDER BY l.id_lpb ASC SEPARATOR ', ') AS no_invoice_list,
            MIN(l.tgl_sj) AS tanggal_lpb,
            MIN(l.tanggal_invoice) AS tanggal_invoice,
            MIN(l.status_lpb) AS min_status_lpb,
            MAX(l.status_lpb) AS max_status_lpb
        ", false);
        $this->db->from('tb_lpb l');
        $this->db->where_in('l.nomor_lpb', $documents);
        $this->db->group_by('l.nomor_lpb');
        $rows = $this->db->get()->result_array();

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['nomor_lpb']] = $row;
        }

        return $meta;
    }

    private function document_status_label($row, $meta)
    {
        if (!$meta) {
            return 'Dokumen Jurnal';
        }

        if ((int)$meta['min_status_lpb'] === 1 && (int)$meta['max_status_lpb'] === 1) {
            return 'LPB POST';
        }

        return 'Cek Status LPB';
    }

    private function valid_cash_bank_account($idAkun)
    {
        return $this->db
            ->where('id_akun', (int)$idAkun)
            ->where_in('tipe_kontrol', ['KAS', 'BANK'])
            ->where('tipe_akun', 'POSTING')
            ->where('is_active', 1)
            ->count_all_results('tbkeu_akun') > 0;
    }

    private function current_document_outstanding($idSupplier, $invoiceNo)
    {
        $row = $this->db->query(
            "
            SELECT COALESCE(SUM(d.kredit), 0) AS kredit, COALESCE(SUM(d.debit), 0) AS debit
            FROM tbkeu_jurnal_detail d
            INNER JOIN tbkeu_jurnal j
                ON j.id_jurnal = d.id_jurnal
               AND j.status = 'POSTED'
               AND j.reversed_at IS NULL
            INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
            WHERE (a.tipe_kontrol = 'HUTANG' OR a.kode_akun = '21098')
              AND d.id_supplier = ?
              AND d.nomor_dokumen = ?
            ",
            [(int)$idSupplier, trim((string)$invoiceNo)]
        )->row_array();

        return $this->money(bcsub((string)($row['kredit'] ?? 0), (string)($row['debit'] ?? 0), 4));
    }

    private function current_document_outstanding_account_id($idSupplier, $invoiceNo)
    {
        $row = $this->db->query(
            "
            SELECT d.id_akun,
                   COALESCE(SUM(d.kredit), 0) AS kredit,
                   COALESCE(SUM(d.debit), 0) AS debit
            FROM tbkeu_jurnal_detail d
            INNER JOIN tbkeu_jurnal j
                ON j.id_jurnal = d.id_jurnal
               AND j.status = 'POSTED'
               AND j.reversed_at IS NULL
            INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
            WHERE (a.tipe_kontrol = 'HUTANG' OR a.kode_akun = '21098')
              AND d.id_supplier = ?
              AND d.nomor_dokumen = ?
            GROUP BY d.id_akun
            HAVING kredit - debit > 0
            ORDER BY ABS(COALESCE(SUM(d.kredit),0) - COALESCE(SUM(d.debit),0)) DESC
            LIMIT 1
            ",
            [(int)$idSupplier, trim((string)$invoiceNo)]
        )->row_array();

        return $row ? (int)$row['id_akun'] : 0;
    }

    private function current_return_credit_available($idSupplier, $returnNo)
    {
        $row = $this->db->query(
            "
            SELECT COALESCE(SUM(d.debit), 0) AS debit, COALESCE(SUM(d.kredit), 0) AS kredit
            FROM tbkeu_jurnal_detail d
            INNER JOIN tbkeu_jurnal j
                ON j.id_jurnal = d.id_jurnal
               AND j.status = 'POSTED'
               AND j.reversed_at IS NULL
            INNER JOIN tbkeu_akun a ON a.id_akun = d.id_akun
            WHERE a.kode_akun = '13013'
              AND d.id_supplier = ?
              AND d.nomor_dokumen = ?
            ",
            [(int)$idSupplier, trim((string)$returnNo)]
        )->row_array();

        return $this->money(bcsub((string)($row['debit'] ?? 0), (string)($row['kredit'] ?? 0), 4));
    }

    private function account_id_by_code($kodeAkun)
    {
        $row = $this->db
            ->select('id_akun')
            ->where('kode_akun', trim((string)$kodeAkun))
            ->where('tipe_akun', 'POSTING')
            ->where('is_active', 1)
            ->get('tbkeu_akun')
            ->row_array();

        return $row ? (int)$row['id_akun'] : 0;
    }

    private function normalize_allocations($rows)
    {
        $result = [];
        $seen = [];
        foreach ((array)$rows as $row) {
            $invoiceNo = trim((string)($row['invoice_no'] ?? ''));
            $amount = $this->money($row['amount_allocated'] ?? 0);
            if ($invoiceNo === '' || bccomp($amount, '0.0000', 4) <= 0) {
                continue;
            }
            if (isset($seen[$invoiceNo])) {
                continue;
            }
            $seen[$invoiceNo] = true;
            $result[] = [
                'invoice_no' => $invoiceNo,
                'invoice_source_id' => trim((string)($row['invoice_source_id'] ?? '')),
                'amount_allocated' => $amount,
                'keterangan' => trim((string)($row['keterangan'] ?? '')),
            ];
        }

        return $result;
    }

    private function generate_payment_number($tanggal)
    {
        $period = date('Ym', strtotime($tanggal));
        $prefix = 'BYS-' . $period . '-';
        $row = $this->db
            ->select('nomor_pembayaran')
            ->like('nomor_pembayaran', $prefix, 'after')
            ->order_by('nomor_pembayaran', 'DESC')
            ->limit(1)
            ->get('tbkeu_pembayaran')
            ->row_array();

        $next = 1;
        if ($row && preg_match('/(\d+)$/', $row['nomor_pembayaran'], $matches)) {
            $next = (int)$matches[1] + 1;
        }

        return $prefix . sprintf('%05d', $next);
    }

    private function generate_return_deduction_number($tanggal)
    {
        $period = date('Ym', strtotime($tanggal));
        $prefix = 'PHS-' . $period . '-';
        $row = $this->db
            ->select('nomor_pembayaran')
            ->like('nomor_pembayaran', $prefix, 'after')
            ->order_by('nomor_pembayaran', 'DESC')
            ->limit(1)
            ->get('tbkeu_pembayaran')
            ->row_array();

        $next = 1;
        if ($row && preg_match('/(\d+)$/', $row['nomor_pembayaran'], $matches)) {
            $next = (int)$matches[1] + 1;
        }

        return $prefix . sprintf('%05d', $next);
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

    private function money($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '0.0000';
        }
        $value = str_replace(['Rp', 'rp', ' '], '', $value);
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
