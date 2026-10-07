<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Closing_service
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->library('Accounting_service');
    }

    public function schema_ready()
    {
        foreach (['tbkeu_closing_period', 'tbkeu_closing_validation_run', 'tbkeu_closing_validation_detail', 'tbkeu_closing_balance_account', 'tbkeu_reopen_request', 'tbkeu_reopen_approval_log'] as $table) {
            if (!$this->CI->db->table_exists($table)) {
                return false;
            }
        }
        return true;
    }

    public function closing_rows($limit = 36)
    {
        if (!$this->schema_ready()) {
            return [];
        }
        return $this->CI->db
            ->select('c.*, p.kode_periode, p.nama_periode')
            ->from('tbkeu_closing_period c')
            ->join('tbkeu_periode_fiskal p', 'p.id_periode = c.id_periode')
            ->order_by('c.id_closing', 'DESC')
            ->limit(max(1, (int)$limit))
            ->get()->result();
    }

    public function reopen_rows($limit = 36)
    {
        if (!$this->schema_ready()) return [];
        return $this->CI->db
            ->select('r.*, p.kode_periode, p.nama_periode')
            ->from('tbkeu_reopen_request r')
            ->join('tbkeu_periode_fiskal p', 'p.id_periode = r.id_periode')
            ->order_by('r.id_reopen', 'DESC')->limit(max(1, (int)$limit))->get()->result();
    }

    public function request_closing($idPeriode, $closingType, $notes, $userId)
    {
        if (!$this->schema_ready()) {
            return $this->fail('Schema tutup buku belum tersedia.', ['CLOSING_SCHEMA_NOT_READY']);
        }
        $closingType = strtoupper(trim((string)$closingType));
        if (!in_array($closingType, ['MONTH_END', 'YEAR_END'], true)) {
            return $this->fail('Jenis tutup buku tidak valid.', ['INVALID_CLOSING_TYPE']);
        }
        $period = $this->CI->db->where('id_periode', (int)$idPeriode)->get('tbkeu_periode_fiskal')->row();
        if (!$period || $period->status !== 'OPEN') {
            return $this->fail('Hanya periode OPEN yang dapat diajukan untuk tutup buku.', ['PERIOD_NOT_OPEN']);
        }
        if ($closingType === 'YEAR_END' && date('m', strtotime($period->tanggal_selesai)) !== '12') {
            return $this->fail('Tutup buku tahunan hanya dapat dijalankan pada periode yang berakhir di bulan Desember.', ['NOT_YEAR_END_PERIOD']);
        }

        $active = $this->CI->db->where('id_periode', (int)$idPeriode)
            ->where('closing_type', $closingType)
            ->where_in('status', ['DRAFT','VALIDATING','READY_TO_APPROVE','APPROVED','EXECUTING'])
            ->get('tbkeu_closing_period')->row();
        if ($active) {
            return $this->fail('Masih ada proses tutup buku aktif untuk periode ini.', ['ACTIVE_CLOSING_EXISTS']);
        }

        $versionRow = $this->CI->db->select_max('closing_version', 'max_version')
            ->where('id_periode', (int)$idPeriode)->where('closing_type', $closingType)
            ->get('tbkeu_closing_period')->row();
        $version = ((int)($versionRow->max_version ?? 0)) + 1;
        $this->CI->db->insert('tbkeu_closing_period', [
            'id_periode' => (int)$idPeriode,
            'closing_version' => $version,
            'closing_type' => $closingType,
            'status' => 'DRAFT',
            'cutoff_at' => $period->tanggal_selesai . ' 23:59:59',
            'source_watermark' => date('Y-m-d H:i:s'),
            'requested_by' => (int)$userId,
            'notes' => trim((string)$notes),
        ]);
        $idClosing = (int)$this->CI->db->insert_id();
        return $this->run_validation($idClosing, $userId);
    }

    public function run_validation($idClosing, $userId)
    {
        $closing = $this->closing_for_update_read($idClosing);
        if (!$closing || in_array($closing->status, ['COMPLETED','REOPENED','SUPERSEDED'], true)) {
            return $this->fail('Proses tutup buku tidak ditemukan atau sudah final.', ['CLOSING_NOT_AVAILABLE']);
        }
        $runRow = $this->CI->db->select_max('run_number', 'max_run')->where('id_closing', (int)$idClosing)
            ->get('tbkeu_closing_validation_run')->row();
        $runNumber = ((int)($runRow->max_run ?? 0)) + 1;
        $this->CI->db->where('id_closing', (int)$idClosing)->update('tbkeu_closing_period', ['status' => 'VALIDATING', 'failure_message' => null]);
        $this->CI->db->insert('tbkeu_closing_validation_run', [
            'id_closing' => (int)$idClosing, 'run_number' => $runNumber, 'status' => 'RUNNING', 'started_by' => (int)$userId,
        ]);
        $idRun = (int)$this->CI->db->insert_id();
        $rules = $this->validation_rules($closing);
        $blocking = 0;
        $warning = 0;
        foreach ($rules as $rule) {
            if ($rule['status'] === 'FAILED' && $rule['severity'] === 'BLOCKING') $blocking++;
            if ($rule['status'] === 'FAILED' && $rule['severity'] === 'WARNING') $warning++;
            $rule['id_validation_run'] = $idRun;
            $this->CI->db->insert('tbkeu_closing_validation_detail', $rule);
        }
        $passed = $blocking === 0;
        $this->CI->db->where('id_validation_run', $idRun)->update('tbkeu_closing_validation_run', [
            'status' => $passed ? 'PASSED' : 'FAILED', 'blocking_count' => $blocking,
            'warning_count' => $warning, 'finished_at' => date('Y-m-d H:i:s'),
        ]);
        $this->CI->db->where('id_closing', (int)$idClosing)->update('tbkeu_closing_period', [
            'status' => $passed ? 'READY_TO_APPROVE' : 'DRAFT',
            'failure_message' => $passed ? null : 'Validasi pra-closing masih memiliki pemeriksaan blocking.',
        ]);
        return $passed
            ? $this->ok('Validasi selesai dan pengajuan siap disetujui.', ['id_closing' => (int)$idClosing, 'warning_count' => $warning])
            : $this->fail('Tutup buku belum dapat dilanjutkan karena validasi blocking gagal.', ['CLOSING_VALIDATION_FAILED'], ['id_closing' => (int)$idClosing, 'blocking_count' => $blocking]);
    }

    public function approve_and_execute($idClosing, $userId, $allowSelfApproval = false)
    {
        $closing = $this->closing_for_update_read($idClosing);
        if (!$closing || $closing->status !== 'READY_TO_APPROVE') {
            return $this->fail('Pengajuan belum siap disetujui.', ['CLOSING_NOT_READY']);
        }
        if ((int)$closing->requested_by === (int)$userId && !$allowSelfApproval) {
            return $this->fail('Pemohon tidak boleh menyetujui tutup buku miliknya sendiri.', ['SELF_APPROVAL_NOT_ALLOWED']);
        }
        $validation = $this->run_validation($idClosing, $userId);
        if (!$validation['success']) return $validation;
        $this->CI->db->where('id_closing', (int)$idClosing)->update('tbkeu_closing_period', [
            'status' => 'APPROVED', 'approved_by' => (int)$userId, 'approved_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->execute_closing($idClosing, $userId);
    }

    public function request_reopen($idPeriode, $reason, $correctionPlan, $userId)
    {
        if (!$this->schema_ready()) return $this->fail('Schema tutup buku belum tersedia.', ['CLOSING_SCHEMA_NOT_READY']);
        if (trim((string)$reason) === '' || trim((string)$correctionPlan) === '') {
            return $this->fail('Alasan dan rencana koreksi wajib diisi.', ['REOPEN_REASON_REQUIRED']);
        }
        $period = $this->CI->db->where('id_periode', (int)$idPeriode)->get('tbkeu_periode_fiskal')->row();
        $closing = $this->CI->db->where('id_periode', (int)$idPeriode)->where('status', 'COMPLETED')
            ->order_by('closing_version', 'DESC')->get('tbkeu_closing_period')->row();
        if (!$period || $period->status !== 'CLOSED' || !$closing) {
            return $this->fail('Periode tidak CLOSED atau tidak mempunyai closing aktif.', ['PERIOD_NOT_CLOSED']);
        }
        $pending = $this->CI->db->where('id_periode', (int)$idPeriode)
            ->where_in('status', ['PENDING_MANAGER','PENDING_DIRECTOR','APPROVED'])
            ->count_all_results('tbkeu_reopen_request');
        if ($pending > 0) return $this->fail('Permohonan reopen aktif sudah tersedia.', ['ACTIVE_REOPEN_EXISTS']);
        $this->CI->db->insert('tbkeu_reopen_request', [
            'id_periode' => (int)$idPeriode, 'id_closing' => (int)$closing->id_closing,
            'reason' => trim((string)$reason), 'correction_plan' => trim((string)$correctionPlan),
            'requested_by' => (int)$userId,
        ]);
        return $this->ok('Permohonan buka buku dikirim dan menunggu approval Manager.', ['id_reopen' => (int)$this->CI->db->insert_id()]);
    }

    public function approve_reopen($idReopen, $level, $note, $userId)
    {
        $level = strtoupper(trim((string)$level));
        if (!in_array($level, ['MANAGER','DIRECTOR'], true) || trim((string)$note) === '') {
            return $this->fail('Level approval atau catatan tidak valid.', ['INVALID_REOPEN_APPROVAL']);
        }
        $this->CI->db->trans_begin();
        $request = $this->CI->db->query('SELECT * FROM tbkeu_reopen_request WHERE id_reopen = ? FOR UPDATE', [(int)$idReopen])->row();
        $expected = $level === 'MANAGER' ? 'PENDING_MANAGER' : 'PENDING_DIRECTOR';
        if (!$request || $request->status !== $expected || (int)$request->requested_by === (int)$userId) {
            $this->CI->db->trans_rollback();
            return $this->fail('Permohonan tidak berada pada tahap approval yang sesuai atau terjadi self-approval.', ['REOPEN_NOT_APPROVABLE']);
        }
        $this->CI->db->insert('tbkeu_reopen_approval_log', [
            'id_reopen' => (int)$idReopen, 'approval_level' => $level, 'decision' => 'APPROVED',
            'decision_by' => (int)$userId, 'note' => trim((string)$note),
        ]);
        if ($level === 'MANAGER') {
            $this->CI->db->where('id_reopen', (int)$idReopen)->update('tbkeu_reopen_request', [
                'status' => 'PENDING_DIRECTOR', 'manager_approved_by' => (int)$userId, 'manager_approved_at' => date('Y-m-d H:i:s'),
            ]);
            $this->CI->db->trans_commit();
            return $this->ok('Approval Manager tersimpan. Permohonan menunggu Direktur.', ['id_reopen' => (int)$idReopen]);
        }
        if ((int)$request->manager_approved_by === (int)$userId) {
            $this->CI->db->trans_rollback();
            return $this->fail('Approver Direktur harus berbeda dari approver Manager.', ['DUPLICATE_APPROVER']);
        }
        $until = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->CI->db->where('id_reopen', (int)$idReopen)->update('tbkeu_reopen_request', [
            'status' => 'APPROVED', 'director_approved_by' => (int)$userId,
            'director_approved_at' => date('Y-m-d H:i:s'), 'reopen_until' => $until,
        ]);
        $this->CI->db->where('id_periode', (int)$request->id_periode)->update('tbkeu_periode_fiskal', [
            'status' => 'OPEN', 'reopened_by' => (int)$userId, 'reopened_at' => date('Y-m-d H:i:s'),
        ]);
        $this->CI->db->where('id_closing', (int)$request->id_closing)->update('tbkeu_closing_period', ['status' => 'REOPENED']);
        $this->insert_period_log((int)$request->id_periode, 'REOPEN', 'Reopen disetujui dua tingkat. ' . trim((string)$note), $userId);
        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Gagal membuka periode.', ['DATABASE_ERROR']);
        }
        $this->CI->db->trans_commit();
        return $this->ok('Periode dibuka selama 24 jam untuk koreksi yang disetujui.', ['id_reopen' => (int)$idReopen, 'reopen_until' => $until]);
    }

    private function execute_closing($idClosing, $userId)
    {
        $closing = $this->closing_for_update_read($idClosing);
        $period = $this->CI->db->where('id_periode', (int)$closing->id_periode)->get('tbkeu_periode_fiskal')->row();
        $this->CI->db->where('id_closing', (int)$idClosing)->update('tbkeu_closing_period', ['status' => 'EXECUTING']);
        $journalId = null;
        $netIncome = '0.0000';
        if ($closing->closing_type === 'YEAR_END') {
            $journalResult = $this->create_year_end_journal($closing, $period, $userId);
            if (!$journalResult['success']) {
                $this->CI->db->where('id_closing', (int)$idClosing)->update('tbkeu_closing_period', ['status' => 'FAILED', 'failure_message' => $journalResult['message']]);
                return $journalResult;
            }
            $journalId = (int)$journalResult['data']['id_jurnal'];
            $netIncome = $journalResult['data']['net_income'];
        }
        $this->CI->db->trans_begin();
        $period = $this->CI->db->query('SELECT * FROM tbkeu_periode_fiskal WHERE id_periode = ? FOR UPDATE', [(int)$closing->id_periode])->row();
        if (!$period || $period->status !== 'OPEN') {
            $this->CI->db->trans_rollback();
            return $this->fail('Periode berubah sebelum eksekusi closing.', ['CONCURRENT_PERIOD_CHANGE']);
        }
        $this->snapshot_accounts($closing, $period);
        $checksum = $this->snapshot_checksum($idClosing);
        $this->CI->db->where('id_periode', (int)$period->id_periode)->where('status', 'OPEN')
            ->update('tbkeu_periode_fiskal', ['status' => 'CLOSED', 'closed_by' => (int)$userId, 'closed_at' => date('Y-m-d H:i:s')]);
        if ($this->CI->db->affected_rows() !== 1) {
            $this->CI->db->trans_rollback();
            return $this->fail('Periode berubah saat closing dijalankan.', ['CONCURRENT_PERIOD_CHANGE']);
        }
        $this->CI->db->where('id_closing', (int)$idClosing)->update('tbkeu_closing_period', [
            'status' => 'COMPLETED', 'executed_by' => (int)$userId, 'executed_at' => date('Y-m-d H:i:s'),
            'id_jurnal_penutup' => $journalId, 'net_income' => $netIncome, 'snapshot_checksum' => $checksum,
        ]);
        $this->CI->db->where('id_periode', (int)$period->id_periode)->where('closing_type', $closing->closing_type)
            ->where('id_closing !=', (int)$idClosing)->where('status', 'REOPENED')->update('tbkeu_closing_period', ['status' => 'SUPERSEDED']);
        $this->CI->db->where('id_periode', (int)$period->id_periode)->where('status', 'APPROVED')->update('tbkeu_reopen_request', [
            'status' => 'COMPLETED', 'completed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->insert_period_log((int)$period->id_periode, 'CLOSE', 'Closing ' . $closing->closing_type . ' versi ' . $closing->closing_version, $userId);
        if ($this->CI->db->trans_status() === false) {
            $this->CI->db->trans_rollback();
            return $this->fail('Eksekusi tutup buku gagal.', ['DATABASE_ERROR']);
        }
        $this->CI->db->trans_commit();
        return $this->ok('Tutup buku selesai dan periode telah dikunci.', ['id_closing' => (int)$idClosing, 'checksum' => $checksum]);
    }

    private function validation_rules($closing)
    {
        $period = $this->CI->db->where('id_periode', (int)$closing->id_periode)->get('tbkeu_periode_fiskal')->row();
        $draft = (int)$this->CI->db->where('id_periode', (int)$period->id_periode)->where('status', 'DRAFT')->count_all_results('tbkeu_jurnal');
        $invalid = $this->CI->db->query("SELECT COUNT(*) total FROM (SELECT j.id_jurnal FROM tbkeu_jurnal j LEFT JOIN tbkeu_jurnal_detail d ON d.id_jurnal=j.id_jurnal WHERE j.id_periode=? AND j.status='POSTED' GROUP BY j.id_jurnal,j.total_debit,j.total_kredit HAVING j.total_debit<>j.total_kredit OR j.total_debit<=0 OR j.total_debit<>COALESCE(SUM(d.debit),0) OR j.total_kredit<>COALESCE(SUM(d.kredit),0)) x", [(int)$period->id_periode])->row();
        $exceptions = $this->CI->db->table_exists('tbkeu_posting_exception') ? (int)$this->CI->db->where('status', 'OPEN')->count_all_results('tbkeu_posting_exception') : 0;
        $unapplied = $this->CI->db->table_exists('tbkeu_pembayaran') ? (int)$this->CI->db->where('tanggal_pembayaran >=', $period->tanggal_mulai)->where('tanggal_pembayaran <=', $period->tanggal_selesai)->where('status', 'POSTED')->where('unapplied_amount >', 0)->count_all_results('tbkeu_pembayaran') : 0;
        $rules = [
            $this->rule('PERIOD_CODE_DATE_MATCH','BLOCKING',$period->kode_periode === date('Y-m', strtotime($period->tanggal_mulai)),$period->kode_periode === date('Y-m', strtotime($period->tanggal_mulai)) ? 0 : 1,'Kode periode harus sama dengan tahun-bulan tanggal mulai.'),
            $this->rule('FULL_CALENDAR_MONTH','BLOCKING',date('d', strtotime($period->tanggal_mulai)) === '01' && $period->tanggal_selesai === date('Y-m-t', strtotime($period->tanggal_mulai)),(date('d', strtotime($period->tanggal_mulai)) === '01' && $period->tanggal_selesai === date('Y-m-t', strtotime($period->tanggal_mulai))) ? 0 : 1,'Periode bulanan harus mencakup satu bulan kalender penuh.'),
            $this->rule('NO_DRAFT_JOURNAL','BLOCKING',$draft === 0,$draft,'Tidak boleh ada jurnal DRAFT.'),
            $this->rule('BALANCED_POSTED_JOURNAL','BLOCKING',(int)$invalid->total === 0,(int)$invalid->total,'Semua jurnal POSTED harus balance dan sesuai detail.'),
            $this->rule('NO_OPEN_POSTING_EXCEPTION','BLOCKING',$exceptions === 0,$exceptions,'Posting exception OPEN harus diselesaikan.'),
            $this->rule('NO_UNAPPLIED_PAYMENT','BLOCKING',$unapplied === 0,$unapplied,'Pembayaran periode harus dialokasikan penuh.'),
        ];
        if ($closing->closing_type === 'YEAR_END') {
            $config = $this->CI->db->where('config_key','RETAINED_EARNINGS_ACCOUNT')->where('is_active',1)->get('tbkeu_closing_config')->row();
            $rules[] = $this->rule('RETAINED_EARNINGS_CONFIG','BLOCKING',!empty($config->id_akun),empty($config->id_akun) ? 1 : 0,'Akun laba ditahan wajib dikonfigurasi untuk closing tahunan.');
            $yearStart = date('Y-01-01', strtotime($period->tanggal_selesai));
            $monthCount = (int)$this->CI->db->where('tanggal_mulai >=', $yearStart)->where('tanggal_selesai <=', $period->tanggal_selesai)->count_all_results('tbkeu_periode_fiskal');
            $notClosed = (int)$this->CI->db->where('tanggal_mulai >=', $yearStart)->where('tanggal_selesai <', $period->tanggal_mulai)->where('status !=', 'CLOSED')->count_all_results('tbkeu_periode_fiskal');
            $rules[] = $this->rule('FISCAL_YEAR_PERIODS_CLOSED','BLOCKING',$monthCount === 12 && $notClosed === 0,$monthCount === 12 ? $notClosed : 12-$monthCount,'Dua belas periode bulanan harus tersedia dan seluruh periode sebelumnya harus CLOSED.');
        }
        return $rules;
    }

    private function create_year_end_journal($closing, $period, $userId)
    {
        $config = $this->CI->db->where('config_key','RETAINED_EARNINGS_ACCOUNT')->where('is_active',1)->get('tbkeu_closing_config')->row();
        if (!$config || !(int)$config->id_akun) return $this->fail('Akun laba ditahan belum dikonfigurasi.', ['RETAINED_EARNINGS_NOT_CONFIGURED']);
        $yearStart = date('Y-01-01', strtotime($period->tanggal_selesai));
        $rows = $this->CI->db->query("SELECT a.id_akun,a.saldo_normal,COALESCE(SUM(CASE WHEN j.id_jurnal IS NOT NULL THEN d.debit ELSE 0 END),0) debit,COALESCE(SUM(CASE WHEN j.id_jurnal IS NOT NULL THEN d.kredit ELSE 0 END),0) kredit FROM tbkeu_akun a JOIN tbkeu_klasifikasi_akun k ON k.id_klasifikasi=a.id_klasifikasi LEFT JOIN tbkeu_jurnal_detail d ON d.id_akun=a.id_akun LEFT JOIN tbkeu_jurnal j ON j.id_jurnal=d.id_jurnal AND j.status='POSTED' AND j.tanggal_transaksi BETWEEN ? AND ? WHERE a.tipe_akun='POSTING' AND k.jenis_laporan='LABA_RUGI' GROUP BY a.id_akun,a.saldo_normal HAVING debit<>kredit", [$yearStart,$period->tanggal_selesai])->result();
        $lines = [];
        $net = '0.0000';
        foreach ($rows as $row) {
            $balance = bcsub((string)$row->debit, (string)$row->kredit, 4);
            $net = bcadd($net, $balance, 4);
            $lines[] = ['id_akun'=>(int)$row->id_akun,'debit'=>bccomp($balance,'0',4)<0?ltrim($balance,'-'):'0.0000','kredit'=>bccomp($balance,'0',4)>0?$balance:'0.0000','keterangan'=>'Penutupan saldo akun laba rugi'];
        }
        if (bccomp($net,'0',4) !== 0) {
            $lines[] = ['id_akun'=>(int)$config->id_akun,'debit'=>bccomp($net,'0',4)>0?$net:'0.0000','kredit'=>bccomp($net,'0',4)<0?ltrim($net,'-'):'0.0000','keterangan'=>'Pemindahan laba rugi bersih ke laba ditahan'];
        }
        if (count($lines) < 2) return $this->fail('Tidak ada saldo akun laba rugi yang dapat ditutup.', ['NO_PROFIT_LOSS_BALANCE']);
        $number = 'CLOSE-' . str_replace('-','',$period->kode_periode) . '-V' . $closing->closing_version;
        $created = $this->CI->accounting_service->create_manual_journal(['nomor_jurnal'=>$number,'tanggal_transaksi'=>$period->tanggal_selesai,'keterangan'=>'Jurnal penutup tahunan '.$period->nama_periode,'lines'=>$lines], $userId);
        if (!$created['success']) return $created;
        $posted = $this->CI->accounting_service->post_manual_journal((int)$created['data']['id_jurnal'], $userId);
        if (!$posted['success']) return $posted;
        return $this->ok('Jurnal penutup tahunan berhasil diposting.', ['id_jurnal'=>(int)$created['data']['id_jurnal'],'net_income'=>bcsub('0.0000',$net,4)]);
    }

    private function snapshot_accounts($closing, $period)
    {
        $sql = "INSERT INTO tbkeu_closing_balance_account (id_closing,id_akun,opening_balance,period_debit,period_credit,ending_balance,normal_balance) SELECT ?,a.id_akun,CASE WHEN a.saldo_normal='DEBIT' THEN COALESCE(SUM(CASE WHEN j.tanggal_transaksi<? THEN d.debit-d.kredit ELSE 0 END),0) ELSE COALESCE(SUM(CASE WHEN j.tanggal_transaksi<? THEN d.kredit-d.debit ELSE 0 END),0) END,COALESCE(SUM(CASE WHEN j.tanggal_transaksi BETWEEN ? AND ? THEN d.debit ELSE 0 END),0),COALESCE(SUM(CASE WHEN j.tanggal_transaksi BETWEEN ? AND ? THEN d.kredit ELSE 0 END),0),CASE WHEN a.saldo_normal='DEBIT' THEN COALESCE(SUM(CASE WHEN j.tanggal_transaksi<=? THEN d.debit-d.kredit ELSE 0 END),0) ELSE COALESCE(SUM(CASE WHEN j.tanggal_transaksi<=? THEN d.kredit-d.debit ELSE 0 END),0) END,a.saldo_normal FROM tbkeu_akun a LEFT JOIN tbkeu_jurnal_detail d ON d.id_akun=a.id_akun LEFT JOIN tbkeu_jurnal j ON j.id_jurnal=d.id_jurnal AND j.status='POSTED' WHERE a.tipe_akun='POSTING' GROUP BY a.id_akun,a.saldo_normal";
        $this->CI->db->query($sql, [(int)$closing->id_closing,$period->tanggal_mulai,$period->tanggal_mulai,$period->tanggal_mulai,$period->tanggal_selesai,$period->tanggal_mulai,$period->tanggal_selesai,$period->tanggal_selesai,$period->tanggal_selesai]);
    }

    private function snapshot_checksum($idClosing)
    {
        $rows = $this->CI->db->select('id_akun,opening_balance,period_debit,period_credit,ending_balance,normal_balance')->where('id_closing',(int)$idClosing)->order_by('id_akun','ASC')->get('tbkeu_closing_balance_account')->result_array();
        return hash('sha256', json_encode($rows));
    }

    private function rule($code, $severity, $passed, $count, $message)
    {
        return ['rule_code'=>$code,'rule_version'=>'1.0','severity'=>$severity,'status'=>$passed?'PASSED':'FAILED','expected_amount'=>null,'actual_amount'=>null,'difference_amount'=>null,'anomaly_count'=>(int)$count,'message'=>$message,'reference_json'=>null];
    }

    private function closing_for_update_read($idClosing)
    {
        return $this->CI->db->where('id_closing',(int)$idClosing)->get('tbkeu_closing_period')->row();
    }

    private function insert_period_log($idPeriode, $action, $reason, $userId)
    {
        $this->CI->db->insert('tbkeu_periode_fiskal_log', ['id_periode'=>$idPeriode,'action'=>$action,'reason'=>$reason,'approval_by'=>$userId,'approval_at'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s')]);
    }

    private function ok($message, $data = []) { return ['success'=>true,'message'=>$message,'data'=>$data,'errors'=>[]]; }
    private function fail($message, $errors = [], $data = null) { return ['success'=>false,'message'=>$message,'data'=>$data,'errors'=>$errors]; }
}
