<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Service Modul Tutup Buku & Cut-Off KarismaERP
 * Menangani validasi integritas pre-closing, rekonsiliasi GL vs Subledger,
 * pembuatan snapshot saldo historis, otomasi jurnal penutup tahunan,
 * dan workflow reopen berjenjang.
 */
class Accounting_closing_service
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->CI->load->library('Accounting_service');
    }

    /**
     * Memeriksa apakah skema database untuk modul closing sudah lengkap
     */
    public function schema_ready()
    {
        $tables = [
            'tbkeu_periode_fiskal',
            'tbkeu_closing_period',
            'tbkeu_closing_balance_account',
            'tbkeu_closing_balance_inventory',
            'tbkeu_closing_balance_ar',
            'tbkeu_closing_balance_ap',
            'tbkeu_closing_validation_run',
            'tbkeu_closing_validation_log',
            'tbkeu_reopen_request',
            'tbkeu_jurnal',
            'tbkeu_jurnal_detail',
            'tbkeu_mapping_akun',
        ];

        foreach ($tables as $table) {
            if (!$this->CI->db->table_exists($table)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Menjalankan checklist validasi pre-closing untuk satu periode fiskal
     *
     * @param int $idPeriode
     * @param string $tipeClosing BULANAN|TAHUNAN
     * @param int|null $userId
     * @return array
     */
    public function validate_pre_closing($idPeriode, $tipeClosing = 'BULANAN', $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Skema basis data modul closing belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $idPeriode = (int)$idPeriode;
        $tipeClosing = strtoupper(trim((string)$tipeClosing)) === 'TAHUNAN' ? 'TAHUNAN' : 'BULANAN';

        $period = $this->CI->db->where('id_periode', $idPeriode)->get('tbkeu_periode_fiskal')->row();
        if (!$period) {
            return $this->fail('Periode fiskal tidak ditemukan.', ['PERIOD_NOT_FOUND']);
        }

        $startDate = $period->tanggal_mulai;
        $endDate = $period->tanggal_selesai;

        $logs = [];

        // 1. Cek Keseimbangan GL (Debit = Kredit)
        $glBalanceCheck = $this->check_gl_balance($idPeriode, $startDate, $endDate);
        $logs[] = $glBalanceCheck;

        // 2. Cek Tidak Ada Jurnal DRAFT
        $draftCheck = $this->check_draft_journals($idPeriode, $startDate, $endDate);
        $logs[] = $draftCheck;

        // 3. Cek Tidak Ada Posting Exception OPEN
        $exceptionCheck = $this->check_posting_exceptions();
        $logs[] = $exceptionCheck;

        // 4. Cek Pembayaran Belum Teralokasi Penuh (Unapplied Payments)
        $paymentCheck = $this->check_unapplied_payments($startDate, $endDate);
        $logs[] = $paymentCheck;

        // 5. Cek Rekonsiliasi Piutang (AR Subledger vs GL)
        $arCheck = $this->check_ar_reconciliation($endDate);
        $logs[] = $arCheck;

        // 6. Cek Rekonsiliasi Hutang (AP Subledger vs GL)
        $apCheck = $this->check_ap_reconciliation($endDate);
        $logs[] = $apCheck;

        // 7. Cek Rekonsiliasi Nilai Persediaan (Inventory Subledger vs GL)
        $invCheck = $this->check_inventory_reconciliation($endDate);
        $logs[] = $invCheck;

        // 8. Cek Kuantitas Stok Negatif pada Gudang
        $negStockCheck = $this->check_negative_stock();
        $logs[] = $negStockCheck;

        // 9. Jika Tutup Buku Tahunan: Cek Prasyarat Tutup Buku Tahunan
        if ($tipeClosing === 'TAHUNAN') {
            $yearEndCheck = $this->check_year_end_prerequisites($period);
            $logs[] = $yearEndCheck;
        }

        // Hitung ringkasan hasil
        $blockingCount = 0;
        $warningCount = 0;
        $infoCount = 0;
        $isPassed = true;

        foreach ($logs as $log) {
            if ($log['tingkat_keparahan'] === 'BLOCKING' && $log['status'] === 'FAILED') {
                $blockingCount++;
                $isPassed = false;
            } elseif ($log['tingkat_keparahan'] === 'WARNING' && $log['status'] === 'FAILED') {
                $warningCount++;
            } elseif ($log['tingkat_keparahan'] === 'INFO') {
                $infoCount++;
            }
        }

        // Simpan sesi validasi ke database
        $this->CI->db->trans_begin();

        $runData = [
            'id_periode' => $idPeriode,
            'tipe_closing' => $tipeClosing,
            'run_by' => $userId ?: (int)$this->CI->session->userdata('id'),
            'run_at' => date('Y-m-d H:i:s'),
            'status_overall' => $isPassed ? 'PASSED' : 'FAILED',
            'blocking_count' => $blockingCount,
            'warning_count' => $warningCount,
            'info_count' => $infoCount,
            'notes' => $isPassed ? 'Semua pemeriksaan prasyarat lolos.' : 'Ditemukan ' . $blockingCount . ' anomali blocking yang harus diselesaikan.',
        ];
        $this->CI->db->insert('tbkeu_closing_validation_run', $runData);
        $idValidationRun = (int)$this->CI->db->insert_id();

        foreach ($logs as $log) {
            $logData = [
                'id_validation_run' => $idValidationRun,
                'id_periode' => $idPeriode,
                'rule_code' => $log['rule_code'],
                'rule_title' => $log['rule_title'],
                'tingkat_keparahan' => $log['tingkat_keparahan'],
                'status' => $log['status'],
                'deskripsi' => $log['deskripsi'],
                'expected_amount' => $log['expected_amount'],
                'actual_amount' => $log['actual_amount'],
                'difference_amount' => $log['difference_amount'],
                'jumlah_anomali' => $log['jumlah_anomali'],
                'data_referensi' => !empty($log['data_referensi']) ? json_encode($log['data_referensi']) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->CI->db->insert('tbkeu_closing_validation_log', $logData);
        }

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Gagal mencatat log validasi pre-closing.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();

        return $this->ok('Validasi pre-closing selesai dijalankan.', [
            'id_validation_run' => $idValidationRun,
            'status_overall' => $isPassed ? 'PASSED' : 'FAILED',
            'blocking_count' => $blockingCount,
            'warning_count' => $warningCount,
            'info_count' => $infoCount,
            'logs' => $logs,
        ]);
    }

    /**
     * Eksekusi Tutup Buku (Closing Period) secara atomik
     *
     * @param int $idPeriode
     * @param array $payload
     * @param int|null $userId
     * @return array
     */
    public function execute_closing($idPeriode, $payload = [], $userId = null)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Skema basis data modul closing belum lengkap.', ['SCHEMA_NOT_READY']);
        }

        $idPeriode = (int)$idPeriode;
        $userId = $userId ?: (int)$this->CI->session->userdata('id');
        $catatan = trim((string)($payload['catatan'] ?? ''));
        $tipeClosing = strtoupper(trim((string)($payload['tipe_closing'] ?? 'BULANAN'))) === 'TAHUNAN' ? 'TAHUNAN' : 'BULANAN';
        $bypassWarning = !empty($payload['bypass_warning']);

        $this->CI->db->trans_begin();

        // Kunci baris periode fiskal untuk update
        $period = $this->CI->db->query(
            'SELECT * FROM tbkeu_periode_fiskal WHERE id_periode = ? FOR UPDATE',
            [$idPeriode]
        )->row();

        if (!$period) {
            $this->CI->db->trans_rollback();
            return $this->fail('Periode fiskal tidak ditemukan.', ['PERIOD_NOT_FOUND']);
        }

        if ($period->status !== 'OPEN') {
            $this->CI->db->trans_rollback();
            return $this->fail('Hanya periode berstatus OPEN yang dapat ditutup.', ['PERIOD_NOT_OPEN']);
        }

        // Ambil hasil validasi terakhir atau jalankan validasi otomatis
        $lastRun = $this->CI->db
            ->where('id_periode', $idPeriode)
            ->order_by('id_validation_run', 'DESC')
            ->limit(1)
            ->get('tbkeu_closing_validation_run')
            ->row();

        if (!$lastRun || $lastRun->status_overall !== 'PASSED') {
            // Jalankan validasi langsung jika belum ada atau gagal
            $valResult = $this->validate_pre_closing($idPeriode, $tipeClosing, $userId);
            if (!$valResult['success'] || $valResult['data']['status_overall'] !== 'PASSED') {
                $this->CI->db->trans_rollback();
                $blocking = $valResult['data']['blocking_count'] ?? 0;
                return $this->fail("Tutup buku ditolak: Ditemukan {$blocking} anomali BLOCKING pada validasi pre-closing.", [
                    'PRE_CLOSING_FAILED',
                    'validation_details' => $valResult['data'] ?? null
                ]);
            }
        }

        // Tentukan versi closing berikutnya
        $currentMaxVer = (int)$this->CI->db
            ->select_max('closing_version', 'max_ver')
            ->where('id_periode', $idPeriode)
            ->get('tbkeu_closing_period')
            ->row()->max_ver;

        $newVersion = $currentMaxVer + 1;

        // Tandai closing versi sebelumnya menjadi SUPERSEDED jika ada
        $this->CI->db
            ->where('id_periode', $idPeriode)
            ->where_in('status', ['COMPLETED', 'REOPENED'])
            ->update('tbkeu_closing_period', [
                'status' => 'SUPERSEDED',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $cutoffTimestamp = date('Y-m-d H:i:s');
        $executionTime = date('Y-m-d H:i:s');

        // Buat header closing sementara untuk mendapatkan id_closing
        $headerData = [
            'id_periode' => $idPeriode,
            'kode_periode' => $period->kode_periode,
            'closing_version' => $newVersion,
            'tipe_closing' => $tipeClosing,
            'tanggal_closing' => $period->tanggal_selesai,
            'cutoff_at' => $cutoffTimestamp,
            'waktu_eksekusi' => $executionTime,
            'eksekutor_user_id' => $userId,
            'approved_by' => $userId,
            'approved_at' => $executionTime,
            'status' => 'EXECUTING',
            'catatan' => $catatan,
            'created_at' => $executionTime,
            'updated_at' => $executionTime,
        ];
        $this->CI->db->insert('tbkeu_closing_period', $headerData);
        $idClosing = (int)$this->CI->db->insert_id();

        // 1. Ekstraksi dan Snapshot Saldo Akun GL
        $snapshotGL = $this->create_gl_snapshot($idClosing, $period);
        $totalDebitGL = $snapshotGL['total_debit'];
        $totalKreditGL = $snapshotGL['total_kredit'];
        $netIncome = $snapshotGL['net_income'];

        // 2. Ekstraksi dan Snapshot Persediaan Gudang
        $snapshotInv = $this->create_inventory_snapshot($idClosing, $period->tanggal_selesai);
        $totalNilaiPersediaan = $snapshotInv['total_value'];

        // 3. Ekstraksi dan Snapshot AR (Piutang Usaha Terbuka)
        $snapshotAR = $this->create_ar_snapshot($idClosing, $period->tanggal_selesai);
        $totalPiutangBerjalan = $snapshotAR['total_open'];

        // 4. Ekstraksi dan Snapshot AP (Hutang Usaha Terbuka)
        $snapshotAP = $this->create_ap_snapshot($idClosing, $period->tanggal_selesai);
        $totalHutangBerjalan = $snapshotAP['total_open'];

        // 5. Jika TAHUNAN: Eksekusi Jurnal Penutup Otomatis (Closing Journal)
        $idJurnalPenutup = null;
        if ($tipeClosing === 'TAHUNAN') {
            $closingJournalResult = $this->generate_year_end_closing_journal($idClosing, $period, $snapshotGL['nominal_accounts'], $userId);
            if (!$closingJournalResult['success']) {
                $this->CI->db->trans_rollback();
                return $this->fail('Gagal membentuk jurnal penutup tahunan: ' . $closingJournalResult['message'], $closingJournalResult['errors']);
            }
            $idJurnalPenutup = $closingJournalResult['data']['id_jurnal'];
        }

        // 6. Hitung Checksum SHA-256 untuk Integritas Audit
        $checksumPayload = implode('|', [
            $idClosing,
            $period->kode_periode,
            $newVersion,
            $tipeClosing,
            number_format($totalDebitGL, 4, '.', ''),
            number_format($totalKreditGL, 4, '.', ''),
            number_format($netIncome, 4, '.', ''),
            number_format($totalNilaiPersediaan, 4, '.', ''),
            number_format($totalPiutangBerjalan, 4, '.', ''),
            number_format($totalHutangBerjalan, 4, '.', ''),
            $cutoffTimestamp,
        ]);
        $checksumHash = hash('sha256', $checksumPayload);

        // Update header closing menjadi COMPLETED dengan ringkasan nilai dan checksum
        $this->CI->db->where('id_closing', $idClosing)->update('tbkeu_closing_period', [
            'status' => 'COMPLETED',
            'total_debit_gl' => $totalDebitGL,
            'total_kredit_gl' => $totalKreditGL,
            'total_laba_bersih_periode' => $netIncome,
            'total_nilai_persediaan' => $totalNilaiPersediaan,
            'total_piutang_berjalan' => $totalPiutangBerjalan,
            'total_hutang_berjalan' => $totalHutangBerjalan,
            'id_jurnal_penutup' => $idJurnalPenutup,
            'checksum' => $checksumHash,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Kunci periode fiskal menjadi CLOSED
        $this->CI->db->where('id_periode', $idPeriode)->update('tbkeu_periode_fiskal', [
            'status' => 'CLOSED',
            'closed_by' => $userId,
            'closed_at' => $executionTime,
            'updated_at' => $executionTime,
        ]);

        // Catat di log periode fiskal
        $this->log_period(
            $idPeriode,
            'CLOSE',
            "Tutup Buku {$tipeClosing} versi {$newVersion} berhasil dieksekusi. Checksum: {$checksumHash}. Catatan: {$catatan}",
            $userId
        );

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Gagal mengeksekusi tutup buku pada basis data.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();

        return $this->ok("Tutup buku {$tipeClosing} periode {$period->kode_periode} berhasil diselesaikan.", [
            'id_closing' => $idClosing,
            'closing_version' => $newVersion,
            'kode_periode' => $period->kode_periode,
            'tipe_closing' => $tipeClosing,
            'checksum' => $checksumHash,
            'total_debit_gl' => $totalDebitGL,
            'total_kredit_gl' => $totalKreditGL,
            'net_income' => $netIncome,
            'id_jurnal_penutup' => $idJurnalPenutup,
        ]);
    }

    /**
     * Mengajukan permohonan pembukaan kembali (Reopen Request) periode yang terkunci
     */
    public function request_reopen($idPeriode, $alasan, $userId = null)
    {
        $idPeriode = (int)$idPeriode;
        $alasan = trim((string)$alasan);
        $userId = $userId ?: (int)$this->CI->session->userdata('id');

        if ($alasan === '') {
            return $this->fail('Alasan permohonan buka periode wajib diisi dengan jelas.', ['REASON_REQUIRED']);
        }

        $period = $this->CI->db->where('id_periode', $idPeriode)->get('tbkeu_periode_fiskal')->row();
        if (!$period) {
            return $this->fail('Periode fiskal tidak ditemukan.', ['PERIOD_NOT_FOUND']);
        }

        if ($period->status !== 'CLOSED') {
            return $this->fail('Hanya periode yang berstatus CLOSED yang dapat diajukan pembukaan kembali.', ['PERIOD_NOT_CLOSED']);
        }

        // Ambil closing aktif
        $activeClosing = $this->CI->db
            ->where('id_periode', $idPeriode)
            ->where('status', 'COMPLETED')
            ->order_by('closing_version', 'DESC')
            ->limit(1)
            ->get('tbkeu_closing_period')
            ->row();

        if (!$activeClosing) {
            return $this->fail('Data closing aktif untuk periode ini tidak ditemukan.', ['CLOSING_NOT_FOUND']);
        }

        // Cek apakah ada permohonan yang masih PENDING
        $existing = $this->CI->db
            ->where('id_periode', $idPeriode)
            ->where('status_approval', 'PENDING')
            ->get('tbkeu_reopen_request')
            ->row();

        if ($existing) {
            return $this->fail('Sudah ada permohonan reopen yang berstatus PENDING untuk periode ini.', ['REOPEN_ALREADY_PENDING']);
        }

        $data = [
            'id_periode' => $idPeriode,
            'id_closing' => $activeClosing->id_closing,
            'alasan_pembukaan' => $alasan,
            'diajukan_oleh' => $userId,
            'diajukan_pada' => date('Y-m-d H:i:s'),
            'status_approval' => 'PENDING',
            'reopened_closing_version' => $activeClosing->closing_version,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->CI->db->insert('tbkeu_reopen_request', $data);
        $idReopen = (int)$this->CI->db->insert_id();

        return $this->ok('Permohonan buka periode berhasil diajukan dan menunggu persetujuan.', ['id_reopen' => $idReopen]);
    }

    /**
     * Memproses persetujuan / penolakan pembukaan kembali periode
     */
    public function approve_reopen($idReopen, $action, $catatan = '', $userId = null)
    {
        $idReopen = (int)$idReopen;
        $action = strtoupper(trim((string)$action));
        $catatan = trim((string)$catatan);
        $userId = $userId ?: (int)$this->CI->session->userdata('id');

        if (!in_array($action, ['APPROVE', 'REJECT'], true)) {
            return $this->fail('Aksi approval tidak valid.', ['INVALID_ACTION']);
        }

        $request = $this->CI->db->where('id_reopen', $idReopen)->get('tbkeu_reopen_request')->row();
        if (!$request) {
            return $this->fail('Permohonan reopen tidak ditemukan.', ['REQUEST_NOT_FOUND']);
        }

        if ($request->status_approval !== 'PENDING') {
            return $this->fail('Permohonan ini telah diproses sebelumnya.', ['REQUEST_ALREADY_PROCESSED']);
        }

        $this->CI->db->trans_begin();

        $now = date('Y-m-d H:i:s');

        if ($action === 'REJECT') {
            $this->CI->db->where('id_reopen', $idReopen)->update('tbkeu_reopen_request', [
                'status_approval' => 'REJECTED',
                'disetujui_manager_by' => $userId,
                'disetujui_manager_at' => $now,
                'catatan_koreksi' => $catatan,
            ]);
            $this->CI->db->trans_commit();
            return $this->ok('Permohonan buka periode telah ditolak.', ['id_reopen' => $idReopen, 'status' => 'REJECTED']);
        }

        // Action APPROVE
        $this->CI->db->where('id_reopen', $idReopen)->update('tbkeu_reopen_request', [
            'status_approval' => 'APPROVED',
            'disetujui_manager_by' => $userId,
            'disetujui_manager_at' => $now,
            'disetujui_direktur_by' => $userId,
            'disetujui_direktur_at' => $now,
            'catatan_koreksi' => $catatan,
        ]);

        // Ubah status closing header menjadi REOPENED
        $closing = $this->CI->db->where('id_closing', $request->id_closing)->get('tbkeu_closing_period')->row();
        if ($closing) {
            $this->CI->db->where('id_closing', $closing->id_closing)->update('tbkeu_closing_period', [
                'status' => 'REOPENED',
                'updated_at' => $now,
            ]);

            // Jika ada jurnal penutup tahunan pada closing ini, buat reversal jurnal
            if (!empty($closing->id_jurnal_penutup)) {
                $revResult = $this->CI->accounting_service->reverse_journal(
                    (int)$closing->id_jurnal_penutup,
                    "Reversal otomatis atas pembukaan kembali periode {$closing->kode_periode} (Permohonan Reopen #{$idReopen})",
                    $userId
                );
                if (!$revResult['success']) {
                    $this->CI->db->trans_rollback();
                    return $this->fail('Gagal melakukan reversal jurnal penutup: ' . $revResult['message'], $revResult['errors']);
                }
            }
        }

        // Buka status periode fiskal menjadi OPEN
        $this->CI->db->where('id_periode', $request->id_periode)->update('tbkeu_periode_fiskal', [
            'status' => 'OPEN',
            'reopened_by' => $userId,
            'reopened_at' => $now,
            'updated_at' => $now,
        ]);

        // Catat log periode
        $this->log_period(
            $request->id_periode,
            'REOPEN',
            "Periode dibuka kembali atas approval Reopen Request #{$idReopen}. Alasan: {$request->alasan_pembukaan}. Catatan: {$catatan}",
            $userId
        );

        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Gagal memproses pembukaan periode di basis data.', ['DATABASE_ERROR']);
        }

        $this->CI->db->trans_commit();

        return $this->ok('Periode berhasil dibuka kembali. Transaksi koreksi dapat dilakukan sebelum tutup buku ulang.', [
            'id_reopen' => $idReopen,
            'id_periode' => $request->id_periode,
            'status' => 'APPROVED',
        ]);
    }

    /**
     * Memuat daftar snapshot closing
     */
    public function get_closing_list($limit = 50)
    {
        if (!$this->CI->db->table_exists('tbkeu_closing_period')) {
            return [];
        }

        $this->CI->db->select('c.*, p.nama_periode, p.tanggal_mulai, p.tanggal_selesai, COALESCE(u.nama_lngkp, u.username) as eksekutor_name');
        $this->CI->db->from('tbkeu_closing_period c');
        $this->CI->db->join('tbkeu_periode_fiskal p', 'p.id_periode = c.id_periode', 'left');
        $this->CI->db->join('tb_users u', 'u.id = c.eksekutor_user_id', 'left');
        $this->CI->db->order_by('c.id_closing', 'DESC');
        $this->CI->db->limit((int)$limit > 0 ? (int)$limit : 50);

        return $this->CI->db->get()->result();
    }

    /**
     * Memuat detail satu closing beserta seluruh snapshotnya
     */
    public function get_closing_detail($idClosing)
    {
        $idClosing = (int)$idClosing;
        $header = $this->CI->db
            ->select('c.*, p.nama_periode, p.tanggal_mulai, p.tanggal_selesai, COALESCE(u.nama_lngkp, u.username) as eksekutor_name, COALESCE(apv.nama_lngkp, apv.username) as approver_name')
            ->from('tbkeu_closing_period c')
            ->join('tbkeu_periode_fiskal p', 'p.id_periode = c.id_periode', 'left')
            ->join('tb_users u', 'u.id = c.eksekutor_user_id', 'left')
            ->join('tb_users apv', 'apv.id = c.approved_by', 'left')
            ->where('c.id_closing', $idClosing)
            ->get()
            ->row();

        if (!$header) {
            return null;
        }

        $accounts = $this->CI->db
            ->where('id_closing', $idClosing)
            ->order_by('kode_akun', 'ASC')
            ->get('tbkeu_closing_balance_account')
            ->result();

        $inventory = $this->CI->db
            ->where('id_closing', $idClosing)
            ->order_by('kd_barang', 'ASC')
            ->get('tbkeu_closing_balance_inventory')
            ->result();

        $ar = $this->CI->db
            ->where('id_closing', $idClosing)
            ->order_by('customer_name', 'ASC')
            ->get('tbkeu_closing_balance_ar')
            ->result();

        $ap = $this->CI->db
            ->where('id_closing', $idClosing)
            ->order_by('nama_suplier', 'ASC')
            ->get('tbkeu_closing_balance_ap')
            ->result();

        // Info Jurnal Penutup jika ada
        $closingJournal = null;
        if (!empty($header->id_jurnal_penutup)) {
            $closingJournal = $this->CI->accounting_service->journal_detail((int)$header->id_jurnal_penutup);
        }

        return [
            'header' => $header,
            'accounts' => $accounts,
            'inventory' => $inventory,
            'ar' => $ar,
            'ap' => $ap,
            'closing_journal' => $closingJournal,
        ];
    }

    /**
     * Memuat riwayat permohonan reopen
     */
    public function get_reopen_requests($idPeriode = null, $limit = 50)
    {
        if (!$this->CI->db->table_exists('tbkeu_reopen_request')) {
            return [];
        }

        $this->CI->db->select('r.*, p.kode_periode, p.nama_periode, COALESCE(u.nama_lngkp, u.username) as pemohon_name, COALESCE(m.nama_lngkp, m.username) as manager_name');
        $this->CI->db->from('tbkeu_reopen_request r');
        $this->CI->db->join('tbkeu_periode_fiskal p', 'p.id_periode = r.id_periode', 'left');
        $this->CI->db->join('tb_users u', 'u.id = r.diajukan_oleh', 'left');
        $this->CI->db->join('tb_users m', 'm.id = r.disetujui_manager_by', 'left');

        if ($idPeriode !== null) {
            $this->CI->db->where('r.id_periode', (int)$idPeriode);
        }

        $this->CI->db->order_by('r.id_reopen', 'DESC');
        $this->CI->db->limit((int)$limit > 0 ? (int)$limit : 50);

        return $this->CI->db->get()->result();
    }

    // =========================================================================
    // PRIVATE VALIDATION CHECKS
    // =========================================================================

    private function check_gl_balance($idPeriode, $startDate, $endDate)
    {
        $query = $this->CI->db->query(
            "SELECT 
                COUNT(*) as unbalance_count,
                COALESCE(SUM(total_debit), 0) as total_debit,
                COALESCE(SUM(total_kredit), 0) as total_kredit
             FROM tbkeu_jurnal 
             WHERE id_periode = ? AND status = 'POSTED'",
            [(int)$idPeriode]
        )->row();

        $totalDebit = (float)($query->total_debit ?? 0);
        $totalKredit = (float)($query->total_kredit ?? 0);
        $diff = abs($totalDebit - $totalKredit);

        // Cek juga per lembar jurnal
        $invalidJournal = $this->CI->db->query(
            "SELECT id_jurnal, nomor_jurnal, total_debit, total_kredit
             FROM tbkeu_jurnal
             WHERE id_periode = ? AND status = 'POSTED' AND ABS(total_debit - total_kredit) > 0.0001
             LIMIT 5",
            [(int)$idPeriode]
        )->result();

        $unbalancedCount = count($invalidJournal);
        $isPassed = ($diff < 0.0001) && ($unbalancedCount === 0);

        return [
            'rule_code' => 'GL_BALANCE',
            'rule_title' => 'Keseimbangan Buku Besar GL (Debit = Kredit)',
            'tingkat_keparahan' => 'BLOCKING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Seluruh jurnal POSTED berimbang sempurna (Debit = Kredit = Rp ' . number_format($totalDebit, 2, ',', '.') . ').'
                : 'Ditemukan selisih GL sebesar Rp ' . number_format($diff, 2, ',', '.') . ' dengan ' . $unbalancedCount . ' jurnal tidak seimbang.',
            'expected_amount' => $totalDebit,
            'actual_amount' => $totalKredit,
            'difference_amount' => $diff,
            'jumlah_anomali' => $unbalancedCount,
            'data_referensi' => $invalidJournal,
        ];
    }

    private function check_draft_journals($idPeriode, $startDate, $endDate)
    {
        $drafts = $this->CI->db
            ->where('id_periode', (int)$idPeriode)
            ->where('status', 'DRAFT')
            ->get('tbkeu_jurnal')
            ->result();

        $count = count($drafts);
        $isPassed = ($count === 0);

        return [
            'rule_code' => 'NO_DRAFT_JOURNAL',
            'rule_title' => 'Pemeriksaan Jurnal Belum Diposting (DRAFT)',
            'tingkat_keparahan' => 'BLOCKING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Tidak ada jurnal berstatus DRAFT pada periode ini.'
                : "Masih terdapat {$count} lembar jurnal berstatus DRAFT yang belum diposting atau dibatalkan.",
            'expected_amount' => 0,
            'actual_amount' => $count,
            'difference_amount' => $count,
            'jumlah_anomali' => $count,
            'data_referensi' => array_slice($drafts, 0, 10),
        ];
    }

    private function check_posting_exceptions()
    {
        // Pengecualian khusus faktur konsinyasi (awalan T): faktur titipan konsinyasi tidak dijurnal saat penerbitan faktur
        $exceptions = $this->CI->db
            ->where('status', 'OPEN')
            ->where("source_no NOT LIKE 'T%'", null, false)
            ->get('tbkeu_posting_exception')
            ->result();

        $count = count($exceptions);
        $isPassed = ($count === 0);

        return [
            'rule_code' => 'NO_OPEN_EXCEPTION',
            'rule_title' => 'Pemeriksaan Posting Exception Menggantung',
            'tingkat_keparahan' => 'BLOCKING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Tidak ada antrean posting exception yang berstatus OPEN.'
                : "Masih terdapat {$count} posting exception berstatus OPEN yang gagal diposting ke General Ledger.",
            'expected_amount' => 0,
            'actual_amount' => $count,
            'difference_amount' => $count,
            'jumlah_anomali' => $count,
            'data_referensi' => array_slice($exceptions, 0, 10),
        ];
    }

    private function check_unapplied_payments($startDate, $endDate)
    {
        $unapplied = $this->CI->db
            ->where('tanggal_pembayaran >=', $startDate)
            ->where('tanggal_pembayaran <=', $endDate)
            ->where('status', 'POSTED')
            ->where('unapplied_amount >', 0.0001)
            ->get('tbkeu_pembayaran')
            ->result();

        $count = count($unapplied);
        $isPassed = ($count === 0);

        return [
            'rule_code' => 'UNAPPLIED_PAYMENTS',
            'rule_title' => 'Pemeriksaan Alokasi Pembayaran AR/AP',
            'tingkat_keparahan' => 'BLOCKING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Seluruh pembayaran customer dan supplier telah dialokasikan penuh ke faktur terkait.'
                : "Terdapat {$count} dokumen pembayaran yang belum dialokasikan penuh ke faktur (sisa unapplied).",
            'expected_amount' => 0,
            'actual_amount' => $count,
            'difference_amount' => $count,
            'jumlah_anomali' => $count,
            'data_referensi' => array_slice($unapplied, 0, 10),
        ];
    }

    private function check_ar_reconciliation($endDate)
    {
        // 1. Saldo Akun Kontrol Piutang Usaha di GL (Akun 13099, 13011, 13031 atau kontrol PIUTANG dagang)
        $glARQuery = $this->CI->db->query(
            "SELECT COALESCE(SUM(d.debit - d.kredit), 0) as saldo_gl
             FROM tbkeu_jurnal_detail d
             JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
             JOIN tbkeu_akun a ON a.id_akun = d.id_akun
             WHERE j.status = 'POSTED' 
               AND j.tanggal_transaksi <= ?
               AND (a.kode_akun IN ('13099', '13011', '13031') OR (a.tipe_kontrol = 'PIUTANG' AND (a.nama_akun LIKE '%Piutang Dagang%' OR a.nama_akun LIKE '%Piutang Usaha%')))",
            [$endDate]
        )->row();
        $glAR = (float)($glARQuery->saldo_gl ?? 0);

        // 2. Open AR Faktur Penjualan Reguler (Subledger)
        // Catatan: Faktur konsinyasi awalan 'T' adalah titipan kios (tidak dijurnal saat terbit). Jurnal piutang konsinyasi baru timbul saat pelunasan/kios membeli.
        $subARQuery = $this->CI->db->query(
            "SELECT 
                COALESCE(SUM(sub.total_faktur - sub.total_bayar), 0) as open_ar
             FROM (
                SELECT 
                    f.id_faktur,
                    COALESCE(SUM(fd.total_harga), 0) as total_faktur,
                    COALESCE(MAX(pf.total_bayar), 0) as total_bayar
                FROM tbso_faktur_penjualan f
                LEFT JOIN tbso_faktur_detail fd ON fd.id_faktur = f.id_faktur
                LEFT JOIN (
                    SELECT no_faktur, SUM(jumlah_pembayaran + jumlah_diskon) as total_bayar
                    FROM tbkeu_pembayaran_faktur
                    WHERE tanggal_pembayaran <= ? AND status = 'POSTED'
                    GROUP BY no_faktur
                ) pf ON pf.no_faktur = f.no_faktur
                WHERE f.tanggal_faktur <= ? 
                  AND f.status NOT IN ('draft','cancelled')
                  AND f.no_faktur NOT LIKE 'T%'
                GROUP BY f.id_faktur
             ) sub",
            [$endDate, $endDate]
        )->row();
        $subARReguler = (float)($subARQuery->open_ar ?? 0);

        // 3. Saldo Piutang Konsinyasi yang sudah diakui penjualan namun belum dilunasi kios di GL
        $konsinARQuery = $this->CI->db->query(
            "SELECT COALESCE(SUM(d.debit - d.kredit), 0) as saldo_konsin
             FROM tbkeu_jurnal_detail d
             JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
             JOIN tbkeu_akun a ON a.id_akun = d.id_akun
             WHERE j.status = 'POSTED' 
               AND j.tanggal_transaksi <= ?
               AND (a.kode_akun IN ('13099', '13011', '13031'))
               AND (j.source_type LIKE '%KONSINYASI%' OR j.keterangan LIKE '%Konsinyasi%')",
            [$endDate]
        )->row();
        $subARKonsin = (float)($konsinARQuery->saldo_konsin ?? 0);

        $subAR = $subARReguler + $subARKonsin;
        $diff = abs($glAR - $subAR);
        $isPassed = ($diff < 1.00);

        return [
            'rule_code' => 'AR_RECONCILIATION',
            'rule_title' => 'Rekonsiliasi Piutang (GL Akun Kontrol vs Subledger AR)',
            'tingkat_keparahan' => 'WARNING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Saldo Akun Piutang GL (Rp ' . number_format($glAR, 2, ',', '.') . ') cocok dengan Subledger AR (Rp ' . number_format($subAR, 2, ',', '.') . ').'
                : 'Ditemukan selisih antara Piutang GL (Rp ' . number_format($glAR, 2, ',', '.') . ') dengan Subledger AR (Rp ' . number_format($subAR, 2, ',', '.') . ') sebesar Rp ' . number_format($diff, 2, ',', '.') . '. (Faktur titipan konsinyasi T di kios dikecualikan; hanya penjualan konsinyasi terealisasi yang dihitung).',
            'expected_amount' => $glAR,
            'actual_amount' => $subAR,
            'difference_amount' => $diff,
            'jumlah_anomali' => $diff > 0 ? 1 : 0,
            'data_referensi' => ['gl_ar' => $glAR, 'subledger_ar' => $subAR, 'subledger_reguler' => $subARReguler, 'piutang_konsinyasi' => $subARKonsin, 'diff' => $diff],
        ];
    }

    private function check_ap_reconciliation($endDate)
    {
        // 1. Saldo Akun Kontrol Hutang di GL (Akun 21098 Hutang Usaha)
        $glAPQuery = $this->CI->db->query(
            "SELECT COALESCE(SUM(d.kredit - d.debit), 0) as saldo_gl
             FROM tbkeu_jurnal_detail d
             JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
             JOIN tbkeu_akun a ON a.id_akun = d.id_akun
             WHERE j.status = 'POSTED' 
               AND j.tanggal_transaksi <= ?
               AND (a.kode_akun = '21098' OR (a.tipe_kontrol = 'HUTANG' AND a.nama_akun LIKE '%Hutang Usaha%'))",
            [$endDate]
        )->row();
        $glAP = (float)($glAPQuery->saldo_gl ?? 0);

        // 2. Open AP Tagihan LPB Non-Konsinyasi (Subledger)
        // Catatan: LPB konsinyasi di gudang 13 bukan hutang sebelum laku terjual di kios
        $subAPQuery = $this->CI->db->query(
            "SELECT 
                COALESCE(SUM(sub.total_tagihan - sub.total_bayar), 0) as open_ap
             FROM (
                SELECT 
                    l.id_lpb,
                    COALESCE(SUM(ld.total_harga), 0) as total_tagihan,
                    COALESCE(MAX(bayar.total_bayar), 0) as total_bayar
                FROM tb_lpb l
                LEFT JOIN tb_lpb_detail ld ON ld.id_lpb = l.id_lpb
                LEFT JOIN (
                    SELECT invoice_no, SUM(amount_allocated) as total_bayar
                    FROM tbkeu_pembayaran_alokasi
                    GROUP BY invoice_no
                ) bayar ON bayar.invoice_no = l.nomor_lpb
                WHERE l.tgl_sj <= ? 
                  AND l.status_lpb = 1
                  AND (l.gudang_id != 13 OR l.gudang_id IS NULL)
                  AND l.nomor_lpb NOT LIKE '%K'
                GROUP BY l.id_lpb
             ) sub",
            [$endDate]
        )->row();
        $subAP = (float)($subAPQuery->open_ap ?? 0);

        $diff = abs($glAP - $subAP);
        $isPassed = ($diff < 1.00);

        return [
            'rule_code' => 'AP_RECONCILIATION',
            'rule_title' => 'Rekonsiliasi Hutang (GL Akun Kontrol vs Subledger AP)',
            'tingkat_keparahan' => 'WARNING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Saldo Akun Hutang GL (Rp ' . number_format($glAP, 2, ',', '.') . ') cocok dengan Subledger AP (Rp ' . number_format($subAP, 2, ',', '.') . ').'
                : 'Ditemukan selisih antara Hutang GL (Rp ' . number_format($glAP, 2, ',', '.') . ') dengan Subledger AP (Rp ' . number_format($subAP, 2, ',', '.') . ') sebesar Rp ' . number_format($diff, 2, ',', '.') . '. (LPB titipan konsinyasi gudang 13 dikecualikan).',
            'expected_amount' => $glAP,
            'actual_amount' => $subAP,
            'difference_amount' => $diff,
            'jumlah_anomali' => $diff > 0 ? 1 : 0,
            'data_referensi' => ['gl_ap' => $glAP, 'subledger_ap' => $subAP, 'diff' => $diff],
        ];
    }

    private function check_inventory_reconciliation($endDate)
    {
        // 1. Saldo Akun Persediaan di GL
        $glInvQuery = $this->CI->db->query(
            "SELECT COALESCE(SUM(d.debit - d.kredit), 0) as saldo_gl
             FROM tbkeu_jurnal_detail d
             JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
             JOIN tbkeu_akun a ON a.id_akun = d.id_akun
             WHERE j.status = 'POSTED' 
               AND j.tanggal_transaksi <= ?
               AND (a.tipe_kontrol = 'PERSEDIAAN' OR a.kode_akun LIKE '14%')",
            [$endDate]
        )->row();
        $glInv = (float)($glInvQuery->saldo_gl ?? 0);

        // 2. Total Estimasi Nilai Stok Fisik
        $subInvQuery = $this->CI->db->query(
            "SELECT 
                COALESCE(SUM(sl.qty * COALESCE(lp.hpp_terakhir, 0)), 0) as total_nilai_stok
             FROM tberp_stock_ledger sl
             LEFT JOIN (
                 SELECT ld.kd_barang, ld.harga_satuan as hpp_terakhir
                 FROM tb_lpb_detail ld
                 JOIN (SELECT kd_barang, MAX(id_detail_lpb) as max_id FROM tb_lpb_detail GROUP BY kd_barang) m ON m.max_id = ld.id_detail_lpb
             ) lp ON lp.kd_barang = sl.kd_barang
             WHERE sl.created_at <= CONCAT(?, ' 23:59:59')",
            [$endDate]
        )->row();
        $subInv = (float)($subInvQuery->total_nilai_stok ?? 0);

        $diff = abs($glInv - $subInv);
        // Persediaan bisa berbeda jika HPP moving average belum dihitung penuh di database lama; beri severity WARNING jika selisih ada
        $isPassed = ($diff < 1.00);

        return [
            'rule_code' => 'INVENTORY_RECONCILIATION',
            'rule_title' => 'Rekonsiliasi Persediaan (GL Akun Persediaan vs Valuasi Stok)',
            'tingkat_keparahan' => 'WARNING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Saldo Akun Persediaan GL (Rp ' . number_format($glInv, 2, ',', '.') . ') selaras dengan nilai persediaan fisik.'
                : 'Terdapat perbedaan antara Persediaan GL (Rp ' . number_format($glInv, 2, ',', '.') . ') dengan nilai persediaan fisik (Rp ' . number_format($subInv, 2, ',', '.') . ') sebesar Rp ' . number_format($diff, 2, ',', '.') . '.',
            'expected_amount' => $glInv,
            'actual_amount' => $subInv,
            'difference_amount' => $diff,
            'jumlah_anomali' => $diff > 0 ? 1 : 0,
            'data_referensi' => ['gl_inventory' => $glInv, 'physical_inventory' => $subInv, 'diff' => $diff],
        ];
    }

    private function check_negative_stock()
    {
        $negativeStocks = $this->CI->db->query(
            "SELECT kd_barang, gudang_id, SUM(qty) as total_qty
             FROM tberp_stock_ledger
             GROUP BY kd_barang, gudang_id
             HAVING total_qty < -0.0001
             LIMIT 10"
        )->result();

        $count = count($negativeStocks);
        $isPassed = ($count === 0);

        return [
            'rule_code' => 'NO_NEGATIVE_STOCK',
            'rule_title' => 'Pemeriksaan Kuantitas Stok Negatif',
            'tingkat_keparahan' => 'BLOCKING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => $isPassed
                ? 'Tidak ditemukan stok bernilai negatif pada seluruh gudang.'
                : "Ditemukan {$count} barang dengan total kuantitas stok bernilai negatif di gudang.",
            'expected_amount' => 0,
            'actual_amount' => $count,
            'difference_amount' => $count,
            'jumlah_anomali' => $count,
            'data_referensi' => $negativeStocks,
        ];
    }

    private function check_year_end_prerequisites($period)
    {
        $year = date('Y', strtotime($period->tanggal_selesai));

        // Cek apakah ada periode bulanan pada tahun yang sama yang masih OPEN
        $openPeriods = $this->CI->db
            ->where('id_periode !=', (int)$period->id_periode)
            ->where("tanggal_mulai LIKE '{$year}%'")
            ->where('status', 'OPEN')
            ->get('tbkeu_periode_fiskal')
            ->result();

        $openCount = count($openPeriods);

        // Cek kesiapan mapping Akun Laba Ditahan (RETAINED_EARNINGS)
        $mapping = $this->CI->db
            ->where('posting_event', 'YEAR_END_CLOSING')
            ->where('account_role', 'RETAINED_EARNINGS')
            ->where('is_active', 1)
            ->get('tbkeu_mapping_akun')
            ->row();

        $isPassed = ($openCount === 0) && ($mapping !== null);

        $deskripsi = [];
        if ($openCount > 0) {
            $deskripsi[] = "Masih ada {$openCount} periode bulanan di tahun {$year} yang belum berstatus CLOSED.";
        }
        if (!$mapping) {
            $deskripsi[] = "Mapping Akun Laba Ditahan (YEAR_END_CLOSING / RETAINED_EARNINGS) belum dikonfigurasi.";
        }
        if ($isPassed) {
            $deskripsi[] = "Seluruh periode tahun {$year} telah CLOSED dan mapping Akun Laba Ditahan siap.";
        }

        return [
            'rule_code' => 'YEAR_END_PREREQUISITES',
            'rule_title' => 'Prasyarat Tutup Buku Tahunan (Year-End Readiness)',
            'tingkat_keparahan' => 'BLOCKING',
            'status' => $isPassed ? 'PASSED' : 'FAILED',
            'deskripsi' => implode(' ', $deskripsi),
            'expected_amount' => 0,
            'actual_amount' => $openCount,
            'difference_amount' => $openCount,
            'jumlah_anomali' => $openCount + (!$mapping ? 1 : 0),
            'data_referensi' => ['open_periods' => $openPeriods, 'retained_earnings_configured' => (bool)$mapping],
        ];
    }

    // =========================================================================
    // SNAPSHOT GENERATION
    // =========================================================================

    private function create_gl_snapshot($idClosing, $period)
    {
        $idPeriode = (int)$period->id_periode;
        $endDate = $period->tanggal_selesai;

        // Ambil seluruh akun tipe POSTING
        $accounts = $this->CI->db
            ->select('a.id_akun, a.kode_akun, a.nama_akun, a.saldo_normal, k.jenis_laporan')
            ->from('tbkeu_akun a')
            ->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'inner')
            ->where('a.tipe_akun', 'POSTING')
            ->where('a.is_active', 1)
            ->order_by('a.kode_akun', 'ASC')
            ->get()
            ->result();

        $totalDebitAll = 0.0;
        $totalKreditAll = 0.0;
        $netIncome = 0.0;
        $nominalAccounts = [];

        foreach ($accounts as $acc) {
            $idAkun = (int)$acc->id_akun;
            $saldoNormal = strtoupper($acc->saldo_normal) === 'KREDIT' ? 'KREDIT' : 'DEBIT';
            $jenisLaporan = $acc->jenis_laporan;

            // Hitung mutasi pada periode berjalan
            $mutasiRow = $this->CI->db->query(
                "SELECT 
                    COALESCE(SUM(d.debit), 0) as mutasi_debit,
                    COALESCE(SUM(d.kredit), 0) as mutasi_kredit
                 FROM tbkeu_jurnal_detail d
                 JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
                 WHERE j.id_periode = ? AND d.id_akun = ? AND j.status = 'POSTED'",
                [$idPeriode, $idAkun]
            )->row();

            $mutasiDebit = (float)($mutasiRow->mutasi_debit ?? 0);
            $mutasiKredit = (float)($mutasiRow->mutasi_kredit ?? 0);

            // Hitung saldo awal kumulatif sebelum periode ini
            $saldoAwalRow = $this->CI->db->query(
                "SELECT 
                    COALESCE(SUM(d.debit), 0) as prev_debit,
                    COALESCE(SUM(d.kredit), 0) as prev_kredit
                 FROM tbkeu_jurnal_detail d
                 JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
                 WHERE j.id_periode != ? AND j.tanggal_transaksi < ? AND d.id_akun = ? AND j.status = 'POSTED'",
                [$idPeriode, $period->tanggal_mulai, $idAkun]
            )->row();

            $prevDebit = (float)($saldoAwalRow->prev_debit ?? 0);
            $prevKredit = (float)($saldoAwalRow->prev_kredit ?? 0);

            if ($saldoNormal === 'DEBIT') {
                $saldoAwal = $prevDebit - $prevKredit;
                $saldoAkhir = $saldoAwal + ($mutasiDebit - $mutasiKredit);
            } else {
                $saldoAwal = $prevKredit - $prevDebit;
                $saldoAkhir = $saldoAwal + ($mutasiKredit - $mutasiDebit);
            }

            // Untuk akun LABA_RUGI, saldo awal bulanan lazimnya dihitung per periode kalender
            if ($jenisLaporan === 'LABA_RUGI') {
                $saldoAwal = 0.0;
                $saldoAkhir = $saldoNormal === 'KREDIT' ? ($mutasiKredit - $mutasiDebit) : ($mutasiDebit - $mutasiKredit);

                // Hitung kontribusi terhadap net income
                if ($saldoNormal === 'KREDIT') {
                    // Pendapatan menambah laba
                    $netIncome += ($mutasiKredit - $mutasiDebit);
                } else {
                    // Beban / HPP mengurangi laba
                    $netIncome -= ($mutasiDebit - $mutasiKredit);
                }

                $nominalAccounts[] = [
                    'id_akun' => $idAkun,
                    'kode_akun' => $acc->kode_akun,
                    'nama_akun' => $acc->nama_akun,
                    'saldo_normal' => $saldoNormal,
                    'mutasi_debit' => $mutasiDebit,
                    'mutasi_kredit' => $mutasiKredit,
                    'saldo_akhir' => $saldoAkhir,
                ];
            }

            $totalDebitAll += $mutasiDebit;
            $totalKreditAll += $mutasiKredit;

            $this->CI->db->insert('tbkeu_closing_balance_account', [
                'id_closing' => $idClosing,
                'id_akun' => $idAkun,
                'kode_akun' => $acc->kode_akun,
                'nama_akun' => $acc->nama_akun,
                'jenis_laporan' => $jenisLaporan,
                'saldo_normal' => $saldoNormal,
                'saldo_awal' => $saldoAwal,
                'mutasi_debit' => $mutasiDebit,
                'mutasi_kredit' => $mutasiKredit,
                'saldo_akhir' => $saldoAkhir,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return [
            'total_debit' => $totalDebitAll,
            'total_kredit' => $totalKreditAll,
            'net_income' => $netIncome,
            'nominal_accounts' => $nominalAccounts,
        ];
    }

    private function create_inventory_snapshot($idClosing, $endDate)
    {
        $items = $this->CI->db->query(
            "SELECT 
                sl.kd_barang,
                MAX(b.nm_barang) as nama_barang,
                sl.gudang_id,
                COALESCE(sl.no_lot, '-') as batch_no,
                sl.expired_date,
                SUM(sl.qty) as qty_akhir,
                COALESCE(MAX(lp.hpp_terakhir), 0) as hpp_rata_rata
             FROM tberp_stock_ledger sl
             LEFT JOIN tb_master_barang b ON b.kode_barang = sl.kd_barang
             LEFT JOIN (
                 SELECT ld.kd_barang, ld.harga_satuan as hpp_terakhir
                 FROM tb_lpb_detail ld
                 JOIN (SELECT kd_barang, MAX(id_detail_lpb) as max_id FROM tb_lpb_detail GROUP BY kd_barang) m ON m.max_id = ld.id_detail_lpb
             ) lp ON lp.kd_barang = sl.kd_barang
             WHERE sl.created_at <= CONCAT(?, ' 23:59:59')
             GROUP BY sl.kd_barang, sl.gudang_id, sl.no_lot, sl.expired_date
             HAVING qty_akhir != 0",
            [$endDate]
        )->result();

        $totalValuation = 0.0;

        foreach ($items as $item) {
            $qty = (float)$item->qty_akhir;
            $hpp = (float)$item->hpp_rata_rata;
            $totalNilai = $qty * $hpp;
            $totalValuation += $totalNilai;

            $this->CI->db->insert('tbkeu_closing_balance_inventory', [
                'id_closing' => $idClosing,
                'kd_barang' => $item->kd_barang,
                'nama_barang' => $item->nama_barang,
                'gudang_id' => $item->gudang_id,
                'batch_no' => $item->batch_no,
                'expired_date' => $item->expired_date,
                'qty_akhir' => $qty,
                'hpp_rata_rata' => $hpp,
                'total_nilai_stok' => $totalNilai,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return ['total_value' => $totalValuation];
    }

    private function create_ar_snapshot($idClosing, $endDate)
    {
        $openInvoices = $this->CI->db->query(
            "SELECT 
                f.id_faktur,
                f.no_faktur,
                f.kd_customer,
                f.customer_name,
                f.tanggal_faktur,
                f.tanggal_jatuh_tempo,
                COALESCE(SUM(fd.total_harga), 0) as total_tagihan,
                COALESCE(MAX(pf.total_bayar), 0) as total_bayar
             FROM tbso_faktur_penjualan f
             LEFT JOIN tbso_faktur_detail fd ON fd.id_faktur = f.id_faktur
             LEFT JOIN (
                 SELECT no_faktur, SUM(jumlah_pembayaran + jumlah_diskon) as total_bayar
                 FROM tbkeu_pembayaran_faktur
                 WHERE tanggal_pembayaran <= ? AND status = 'POSTED'
                 GROUP BY no_faktur
             ) pf ON pf.no_faktur = f.no_faktur
             WHERE f.tanggal_faktur <= ? 
               AND f.status NOT IN ('draft','cancelled')
               AND f.no_faktur NOT LIKE 'T%'
             GROUP BY f.id_faktur, f.no_faktur, f.kd_customer, f.customer_name, f.tanggal_faktur, f.tanggal_jatuh_tempo
             HAVING (total_tagihan - total_bayar) > 0.0001",
            [$endDate, $endDate]
        )->result();

        $totalOpenAR = 0.0;

        foreach ($openInvoices as $inv) {
            $tagihan = (float)$inv->total_tagihan;
            $bayar = (float)$inv->total_bayar;
            $sisa = $tagihan - $bayar;
            $totalOpenAR += $sisa;

            $this->CI->db->insert('tbkeu_closing_balance_ar', [
                'id_closing' => $idClosing,
                'kd_customer' => $inv->kd_customer,
                'customer_name' => $inv->customer_name,
                'no_faktur' => $inv->no_faktur,
                'tanggal_faktur' => $inv->tanggal_faktur,
                'tanggal_jatuh_tempo' => $inv->tanggal_jatuh_tempo,
                'total_tagihan' => $tagihan,
                'total_bayar' => $bayar,
                'sisa_piutang' => $sisa,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return ['total_open' => $totalOpenAR];
    }

    private function create_ap_snapshot($idClosing, $endDate)
    {
        $openBills = $this->CI->db->query(
            "SELECT 
                l.id_lpb,
                l.nomor_lpb,
                l.kd_suplier,
                l.nama_suplier,
                l.tgl_sj as tanggal_faktur,
                l.tgl_sj as tanggal_jatuh_tempo,
                COALESCE(SUM(ld.total_harga), 0) as total_tagihan,
                COALESCE(MAX(bayar.total_bayar), 0) as total_bayar
             FROM tb_lpb l
             LEFT JOIN tb_lpb_detail ld ON ld.id_lpb = l.id_lpb
             LEFT JOIN (
                 SELECT invoice_no, SUM(amount_allocated) as total_bayar
                 FROM tbkeu_pembayaran_alokasi
                 GROUP BY invoice_no
             ) bayar ON bayar.invoice_no = l.nomor_lpb
             WHERE l.tgl_sj <= ? 
               AND l.status_lpb = 1
               AND (l.gudang_id != 13 OR l.gudang_id IS NULL)
               AND l.nomor_lpb NOT LIKE '%K'
             GROUP BY l.id_lpb, l.nomor_lpb, l.kd_suplier, l.nama_suplier, l.tgl_sj
             HAVING (total_tagihan - total_bayar) > 0.0001",
            [$endDate]
        )->result();

        $totalOpenAP = 0.0;

        foreach ($openBills as $bill) {
            $tagihan = (float)$bill->total_tagihan;
            $bayar = (float)$bill->total_bayar;
            $sisa = $tagihan - $bayar;
            $totalOpenAP += $sisa;

            $this->CI->db->insert('tbkeu_closing_balance_ap', [
                'id_closing' => $idClosing,
                'kd_suplier' => $bill->kd_suplier ?: '-',
                'nama_suplier' => $bill->nama_suplier ?: '-',
                'nomor_dokumen' => $bill->nomor_lpb,
                'tanggal_faktur' => $bill->tanggal_faktur,
                'tanggal_jatuh_tempo' => $bill->tanggal_jatuh_tempo,
                'total_tagihan' => $tagihan,
                'total_bayar' => $bayar,
                'sisa_hutang' => $sisa,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return ['total_open' => $totalOpenAP];
    }

    /**
     * Otomasi Jurnal Penutup Tahunan (Closing Entries)
     */
    private function generate_year_end_closing_journal($idClosing, $period, $nominalAccounts, $userId)
    {
        // Cari mapping akun Laba Ditahan (RETAINED_EARNINGS)
        $retainedMapping = $this->CI->db
            ->where('posting_event', 'YEAR_END_CLOSING')
            ->where('account_role', 'RETAINED_EARNINGS')
            ->where('is_active', 1)
            ->get('tbkeu_mapping_akun')
            ->row();

        if (!$retainedMapping) {
            return $this->fail('Mapping Akun Laba Ditahan (RETAINED_EARNINGS) belum dikonfigurasi.', ['MAPPING_NOT_FOUND']);
        }

        $idAkunLabaDitahan = (int)$retainedMapping->id_akun;

        $lines = [];
        $totalDebitClosing = 0.0;
        $totalKreditClosing = 0.0;
        $baris = 1;

        // Tutup akun-akun nominal (Pendapatan & Beban)
        foreach ($nominalAccounts as $nom) {
            $saldo = (float)$nom['saldo_akhir'];
            if (abs($saldo) < 0.0001) {
                continue;
            }

            if ($nom['saldo_normal'] === 'KREDIT') {
                // Pendapatan bersaldo normal Kredit -> DEBIT untuk menutup ke 0
                $lines[] = [
                    'nomor_baris' => $baris++,
                    'id_akun' => $nom['id_akun'],
                    'keterangan' => 'Penutupan Akun Pendapatan: ' . $nom['nama_akun'],
                    'debit' => $saldo,
                    'kredit' => 0.0,
                ];
                $totalDebitClosing += $saldo;
            } else {
                // Beban / HPP bersaldo normal Debit -> KREDIT untuk menutup ke 0
                $lines[] = [
                    'nomor_baris' => $baris++,
                    'id_akun' => $nom['id_akun'],
                    'keterangan' => 'Penutupan Akun Beban/HPP: ' . $nom['nama_akun'],
                    'debit' => 0.0,
                    'kredit' => $saldo,
                ];
                $totalKreditClosing += $saldo;
            }
        }

        // Hitung selisih laba / rugi bersih
        $netDiff = $totalDebitClosing - $totalKreditClosing;
        if (abs($netDiff) > 0.0001) {
            if ($netDiff > 0) {
                // Laba bersih -> KREDIT ke Laba Ditahan
                $lines[] = [
                    'nomor_baris' => $baris++,
                    'id_akun' => $idAkunLabaDitahan,
                    'keterangan' => 'Pemindahan Laba Bersih Tahun Berjalan ke Laba Ditahan',
                    'debit' => 0.0,
                    'kredit' => $netDiff,
                ];
                $totalKreditClosing += $netDiff;
            } else {
                // Rugi bersih -> DEBIT ke Laba Ditahan
                $lines[] = [
                    'nomor_baris' => $baris++,
                    'id_akun' => $idAkunLabaDitahan,
                    'keterangan' => 'Pemindahan Rugi Bersih Tahun Berjalan ke Laba Ditahan',
                    'debit' => abs($netDiff),
                    'kredit' => 0.0,
                ];
                $totalDebitClosing += abs($netDiff);
            }
        }

        if (empty($lines)) {
            // Tidak ada akun nominal yang bersaldo
            return $this->ok('Tidak ada saldo akun nominal yang perlu ditutup.', ['id_jurnal' => null]);
        }

        // Bentuk Header Jurnal Penutup
        $nomorJurnal = 'JRN-CLOSING-' . date('Y', strtotime($period->tanggal_selesai)) . '-' . $period->kode_periode;
        $now = date('Y-m-d H:i:s');

        // Pastikan nomor jurnal unik jika ada versi re-closing
        $exists = $this->CI->db->where('nomor_jurnal', $nomorJurnal)->count_all_results('tbkeu_jurnal');
        if ($exists > 0) {
            $nomorJurnal .= '-V' . date('His');
        }

        $journalData = [
            'nomor_jurnal' => $nomorJurnal,
            'id_jenis_jurnal' => 1, // Memorial
            'tanggal_transaksi' => $period->tanggal_selesai,
            'id_periode' => (int)$period->id_periode,
            'keterangan' => "Jurnal Penutup Tutup Buku Tahunan Periode {$period->kode_periode}",
            'source_module' => 'ACCOUNTING',
            'source_type' => 'YEAR_END_CLOSING',
            'source_id' => (string)$idClosing,
            'source_no' => $period->kode_periode,
            'posting_event' => 'YEAR_END_CLOSING',
            'status' => 'POSTED',
            'total_debit' => $totalDebitClosing,
            'total_kredit' => $totalKreditClosing,
            'idempotency_key' => 'CLOSING-' . $idClosing,
            'created_by' => $userId,
            'created_at' => $now,
            'posted_by' => $userId,
            'posted_at' => $now,
            'lock_version' => 1,
        ];

        $this->CI->db->insert('tbkeu_jurnal', $journalData);
        $idJurnal = (int)$this->CI->db->insert_id();

        foreach ($lines as $line) {
            $line['id_jurnal'] = $idJurnal;
            $line['created_at'] = $now;
            $line['updated_at'] = $now;
            $this->CI->db->insert('tbkeu_jurnal_detail', $line);
        }

        return $this->ok('Jurnal penutup tahunan berhasil dibentuk.', ['id_jurnal' => $idJurnal]);
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
