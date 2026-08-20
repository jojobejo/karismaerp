<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_ReturPembelian extends CI_Model
{
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_PURCHASING_VERIFIED = 'PURCHASING_VERIFIED';
    const STATUS_ACCOUNTING_VERIFIED = 'ACCOUNTING_VERIFIED';
    const STATUS_POSTED = 'POSTED';
    const STATUS_POSTING_EXCEPTION = 'POSTING_EXCEPTION';
    const STATUS_VOID = 'VOID';

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_retur_pembelian` (
            `id_retur_pembelian` INT(11) NOT NULL AUTO_INCREMENT,
            `no_retur_pembelian` VARCHAR(50) NOT NULL,
            `id_lpb` INT(11) NOT NULL,
            `kd_po` VARCHAR(100) DEFAULT NULL,
            `no_po` VARCHAR(100) DEFAULT NULL,
            `kd_supplier` VARCHAR(100) DEFAULT NULL,
            `tanggal_retur` DATE NOT NULL,
            `gudang_id` VARCHAR(30) DEFAULT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'DRAFT',
            `jenis_penyelesaian` VARCHAR(40) NOT NULL DEFAULT 'POTONG_HUTANG',
            `alasan_retur` TEXT DEFAULT NULL,
            `catatan_purchasing` TEXT DEFAULT NULL,
            `catatan_accounting` TEXT DEFAULT NULL,
            `total_dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total_ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `grand_total` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `id_jurnal` INT(11) DEFAULT NULL,
            `id_jurnal_reversal` INT(11) DEFAULT NULL,
            `created_by` VARCHAR(100) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_by` VARCHAR(100) DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            `submitted_by` VARCHAR(100) DEFAULT NULL,
            `submitted_at` DATETIME DEFAULT NULL,
            `purchasing_verified_by` VARCHAR(100) DEFAULT NULL,
            `purchasing_verified_at` DATETIME DEFAULT NULL,
            `accounting_verified_by` VARCHAR(100) DEFAULT NULL,
            `accounting_verified_at` DATETIME DEFAULT NULL,
            `posted_by` VARCHAR(100) DEFAULT NULL,
            `posted_at` DATETIME DEFAULT NULL,
            `reversed_by` VARCHAR(100) DEFAULT NULL,
            `reversed_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id_retur_pembelian`),
            UNIQUE KEY `uk_no_retur_pembelian` (`no_retur_pembelian`),
            KEY `idx_lpb` (`id_lpb`),
            KEY `idx_status` (`status`),
            KEY `idx_jurnal` (`id_jurnal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_retur_pembelian_detail` (
            `id_detail_retur_pembelian` INT(11) NOT NULL AUTO_INCREMENT,
            `id_retur_pembelian` INT(11) NOT NULL,
            `id_detail_lpb` INT(11) NOT NULL,
            `kd_barang` VARCHAR(100) NOT NULL,
            `no_lot` VARCHAR(50) DEFAULT NULL,
            `expired_date` DATE DEFAULT NULL,
            `qty_retur` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `harga_satuan` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `kelompok_dagang` VARCHAR(10) DEFAULT NULL,
            `alasan_retur` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_detail_retur_pembelian`),
            KEY `idx_retur` (`id_retur_pembelian`),
            KEY `idx_detail_lpb` (`id_detail_lpb`),
            KEY `idx_barang_batch` (`kd_barang`, `no_lot`, `expired_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_retur_pembelian_log` (
            `id_log` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `id_retur_pembelian` INT(11) DEFAULT NULL,
            `no_retur_pembelian` VARCHAR(50) DEFAULT NULL,
            `action_type` VARCHAR(40) NOT NULL,
            `status_before` VARCHAR(30) DEFAULT NULL,
            `status_after` VARCHAR(30) DEFAULT NULL,
            `data_before` LONGTEXT DEFAULT NULL,
            `data_after` LONGTEXT DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `dilakukan_oleh` VARCHAR(100) DEFAULT NULL,
            `dilakukan_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_log`),
            KEY `idx_retur_log` (`id_retur_pembelian`),
            KEY `idx_action` (`action_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function header_rows($limit = 100)
    {
        $this->ensure_schema();
        $sql = "SELECT r.*, l.nomor_lpb, s.nama_suplier,
                    COUNT(d.id_detail_retur_pembelian) AS total_item
                FROM tb_retur_pembelian r
                LEFT JOIN tb_lpb l ON l.id_lpb = r.id_lpb
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = r.kd_supplier
                LEFT JOIN tb_retur_pembelian_detail d ON d.id_retur_pembelian = r.id_retur_pembelian
                GROUP BY r.id_retur_pembelian
                ORDER BY r.created_at DESC, r.id_retur_pembelian DESC
                LIMIT ?";
        return $this->db->query($sql, [(int)$limit])->result_array();
    }

    public function lpb_options($search = '')
    {
        $search = trim((string)$search);
        $params = [];
        $where = "WHERE COALESCE(NULLIF(TRIM(l.nomor_lpb), ''), '') <> ''";

        if ($search !== '') {
            $where .= " AND (l.nomor_lpb LIKE ? OR l.no_po LIKE ? OR l.kd_po LIKE ? OR s.nama_suplier LIKE ?)";
            $like = '%' . $search . '%';
            $params = [$like, $like, $like, $like];
        }

        $sql = "SELECT l.id_lpb, l.nomor_lpb, l.kd_po, l.no_po, l.gudang_id,
                    p.kd_suplier, COALESCE(s.nama_suplier, '') AS nama_suplier
                FROM tb_lpb l
                LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
                {$where}
                ORDER BY l.input_at DESC, l.id_lpb DESC
                LIMIT 30";
        return $this->db->query($sql, $params)->result_array();
    }

    public function lpb_detail_rows($idLpb)
    {
        $sql = "SELECT d.id_detail_lpb, d.id_lpb, d.kd_barang,
                    COALESCE(m.nama_barang, b.nama_barang, d.kd_barang) AS nama_barang,
                    d.no_lot, d.expired_date, COALESCE(d.qty_diterima, 0) AS qty_diterima,
                    COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan,
                    COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') AS kelompok_dagang,
                    COALESCE((
                        SELECT SUM(rd.qty_retur)
                        FROM tb_retur_pembelian_detail rd
                        INNER JOIN tb_retur_pembelian rh ON rh.id_retur_pembelian = rd.id_retur_pembelian
                        WHERE rd.id_detail_lpb = d.id_detail_lpb
                            AND rh.status NOT IN ('VOID')
                    ), 0) AS qty_retur_sebelumnya,
                    COALESCE(sb.qty_on_hand, 0) AS qty_on_hand
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                LEFT JOIN tbpo_detail_po pp ON pp.kd_po = l.kd_po AND pp.no_po = l.no_po AND pp.kd_barang = d.kd_barang
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                LEFT JOIN tbpo_barang m ON m.kode_barang = d.kd_barang
                LEFT JOIN tberp_stock_batch sb ON sb.kd_barang = d.kd_barang
                    AND sb.gudang_id = CAST(l.gudang_id AS CHAR)
                    AND COALESCE(sb.no_lot, '') = COALESCE(d.no_lot, '')
                    AND (sb.expired_date <=> d.expired_date)
                WHERE d.id_lpb = ?
                ORDER BY d.id_detail_lpb ASC";
        return $this->db->query($sql, [(int)$idLpb])->result_array();
    }

    public function create_draft($payload, $details, $user)
    {
        $this->ensure_schema();
        $idLpb = (int)($payload['id_lpb'] ?? 0);
        $tanggal = $this->normalize_date($payload['tanggal_retur'] ?? date('Y-m-d'));
        $jenis = strtoupper(trim((string)($payload['jenis_penyelesaian'] ?? 'POTONG_HUTANG')));
        $alasan = trim((string)($payload['alasan_retur'] ?? ''));

        if ($idLpb <= 0 || $tanggal === '' || empty($details)) {
            return $this->fail('LPB, tanggal retur, dan detail barang wajib diisi.', ['RETUR_DATA_REQUIRED']);
        }

        $normalizedDetails = [];
        foreach ($details as $detail) {
            $idDetailLpb = (int)($detail['id_detail_lpb'] ?? 0);
            $qtyRetur = (float)($detail['qty_retur'] ?? 0);
            if ($idDetailLpb <= 0 || $qtyRetur <= 0) {
                continue;
            }
            if (!isset($normalizedDetails[$idDetailLpb])) {
                $normalizedDetails[$idDetailLpb] = [
                    'id_detail_lpb' => $idDetailLpb,
                    'qty_retur' => 0,
                    'alasan_retur' => trim((string)($detail['alasan_retur'] ?? '')),
                ];
            }
            $normalizedDetails[$idDetailLpb]['qty_retur'] += $qtyRetur;
            if ($normalizedDetails[$idDetailLpb]['alasan_retur'] === '') {
                $normalizedDetails[$idDetailLpb]['alasan_retur'] = trim((string)($detail['alasan_retur'] ?? ''));
            }
        }

        if (empty($normalizedDetails)) {
            return $this->fail('Minimal satu qty retur harus lebih dari 0.', ['RETUR_DETAIL_EMPTY']);
        }

        $lpb = $this->lpb_header($idLpb);
        if (!$lpb || trim((string)$lpb['nomor_lpb']) === '') {
            return $this->fail('LPB final dengan nomor LPB tidak ditemukan.', ['LPB_NUMBER_REQUIRED']);
        }

        $this->db->trans_begin();
        $noRetur = $this->generate_number();
        $header = [
            'no_retur_pembelian' => $noRetur,
            'id_lpb' => $idLpb,
            'kd_po' => $lpb['kd_po'],
            'no_po' => $lpb['no_po'],
            'kd_supplier' => $lpb['kd_suplier'],
            'tanggal_retur' => $tanggal,
            'gudang_id' => (string)$lpb['gudang_id'],
            'status' => self::STATUS_DRAFT,
            'jenis_penyelesaian' => $jenis !== '' ? $jenis : 'POTONG_HUTANG',
            'alasan_retur' => $alasan,
            'created_by' => $user,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tb_retur_pembelian', $header);
        $idRetur = (int)$this->db->insert_id();

        $totals = ['dpp' => '0.0000', 'ppn' => '0.0000', 'grand' => '0.0000'];
        foreach ($normalizedDetails as $detail) {
            $valid = $this->validate_line((int)($detail['id_detail_lpb'] ?? 0), (float)($detail['qty_retur'] ?? 0), $idLpb);
            if (!$valid['success']) {
                $this->db->trans_rollback();
                return $valid;
            }

            $row = $valid['data'];
            $line = $this->calculate_line($row, (float)$detail['qty_retur'], trim((string)($detail['alasan_retur'] ?? $alasan)));
            $line['id_retur_pembelian'] = $idRetur;
            $this->db->insert('tb_retur_pembelian_detail', $line);

            $totals['dpp'] = bcadd($totals['dpp'], $this->money($line['dpp']), 4);
            $totals['ppn'] = bcadd($totals['ppn'], $this->money($line['ppn']), 4);
            $totals['grand'] = bcadd($totals['grand'], $this->money($line['total']), 4);
        }

        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', [
            'total_dpp' => $totals['dpp'],
            'total_ppn' => $totals['ppn'],
            'grand_total' => $totals['grand'],
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $after = $this->header($idRetur);
        $this->write_log($idRetur, 'CREATE_DRAFT', null, self::STATUS_DRAFT, null, $after, 'Draft retur pembelian dibuat.', $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Draft retur pembelian gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Draft retur pembelian berhasil dibuat.', ['id_retur_pembelian' => $idRetur, 'no_retur_pembelian' => $noRetur]);
    }

    public function submit($idRetur, $user)
    {
        return $this->change_status((int)$idRetur, [self::STATUS_DRAFT], self::STATUS_SUBMITTED, 'SUBMIT', [
            'submitted_by' => $user,
            'submitted_at' => date('Y-m-d H:i:s'),
        ], 'Draft retur pembelian diajukan.', $user);
    }

    public function verify_purchasing($idRetur, $catatan, $user)
    {
        return $this->change_status((int)$idRetur, [self::STATUS_SUBMITTED, self::STATUS_DRAFT], self::STATUS_PURCHASING_VERIFIED, 'VERIFY_PURCHASING', [
            'catatan_purchasing' => trim((string)$catatan),
            'purchasing_verified_by' => $user,
            'purchasing_verified_at' => date('Y-m-d H:i:s'),
        ], 'Purchasing memverifikasi harga, supplier, alasan retur, dan penyelesaian.', $user);
    }

    public function verify_accounting($idRetur, $catatan, $user)
    {
        $result = $this->change_status((int)$idRetur, [self::STATUS_PURCHASING_VERIFIED], self::STATUS_ACCOUNTING_VERIFIED, 'VERIFY_ACCOUNTING', [
            'catatan_accounting' => trim((string)$catatan),
            'accounting_verified_by' => $user,
            'accounting_verified_at' => date('Y-m-d H:i:s'),
        ], 'Accounting memverifikasi dampak hutang dan PPN.', $user);

        if ($result['success']) {
            $after = $this->header((int)$idRetur);
            $this->write_log((int)$idRetur, 'APPROVE', self::STATUS_ACCOUNTING_VERIFIED, self::STATUS_ACCOUNTING_VERIFIED, $after, $after, 'Retur pembelian disetujui untuk posting.', $user);
        }

        return $result;
    }

    public function post($idRetur, $user)
    {
        $this->ensure_schema();
        $this->load->library('Accounting_service');
        $this->db->trans_begin();

        $header = $this->locked_header((int)$idRetur);
        if (!$header) {
            $this->db->trans_rollback();
            return $this->fail('Retur pembelian tidak ditemukan.', ['RETUR_NOT_FOUND']);
        }
        if (!in_array($header['status'], [self::STATUS_ACCOUNTING_VERIFIED, self::STATUS_POSTING_EXCEPTION], true)) {
            $this->db->trans_rollback();
            return $this->fail('Retur hanya bisa diposting setelah verifikasi Accounting.', ['RETUR_NOT_APPROVED']);
        }
        if ($header['jenis_penyelesaian'] !== 'POTONG_HUTANG') {
            return $this->posting_exception($header, 'Jenis penyelesaian retur belum memiliki mapping akun final selain POTONG_HUTANG.', ['PURCHASE_RETURN_SETTLEMENT_UNMAPPED'], $user);
        }

        $details = $this->details((int)$idRetur);
        if (empty($details)) {
            $this->db->trans_rollback();
            return $this->fail('Detail retur pembelian kosong.', ['RETUR_DETAIL_EMPTY']);
        }

        foreach ($details as $detail) {
            $kelompok = trim((string)$detail['kelompok_dagang']);
            if (!in_array($kelompok, ['2', '3'], true)) {
                return $this->posting_exception($header, 'Kelompok dagang ' . ($kelompok !== '' ? $kelompok : '-') . ' belum aman untuk posting otomatis retur pembelian.', ['PURCHASE_RETURN_GROUP_UNSUPPORTED'], $user);
            }
            $valid = $this->validate_line((int)$detail['id_detail_lpb'], (float)$detail['qty_retur'], (int)$header['id_lpb'], (int)$detail['id_detail_retur_pembelian']);
            if (!$valid['success']) {
                $this->db->trans_rollback();
                return $valid;
            }
        }

        foreach ($details as $detail) {
            $this->decrease_stock($header, $detail);
        }

        $journal = $this->post_purchase_return_journal($header, $details, $user);
        if (!$journal['success']) {
            $this->db->trans_rollback();
            return $journal;
        }

        $before = $header;
        $this->db->where('id_retur_pembelian', (int)$idRetur)->update('tb_retur_pembelian', [
            'status' => self::STATUS_POSTED,
            'id_jurnal' => (int)$journal['data']['id_jurnal'],
            'posted_by' => $user,
            'posted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $after = $this->header((int)$idRetur);
        $this->write_log((int)$idRetur, 'POST', $before['status'], self::STATUS_POSTED, $before, $after, 'Retur pembelian diposting ke stock ledger RBELI dan jurnal PURCHASE_RETURN.', $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Posting retur pembelian gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Retur pembelian berhasil diposting.', ['id_jurnal' => (int)$journal['data']['id_jurnal']]);
    }

    public function void_posted($idRetur, $reason, $user)
    {
        $reason = trim((string)$reason);
        if ($reason === '') {
            return $this->fail('Alasan void/reversal wajib diisi.', ['VOID_REASON_REQUIRED']);
        }

        $this->ensure_schema();
        $this->load->library('Accounting_service');
        $this->db->trans_begin();

        $header = $this->locked_header((int)$idRetur);
        if (!$header || $header['status'] !== self::STATUS_POSTED || (int)$header['id_jurnal'] <= 0) {
            $this->db->trans_rollback();
            return $this->fail('Hanya retur POSTED yang dapat di-void.', ['RETUR_NOT_POSTED']);
        }

        $reversal = $this->accounting_service->reversal_journal((int)$header['id_jurnal'], 'Void retur pembelian ' . $header['no_retur_pembelian'] . ': ' . $reason, $user);
        if (!$reversal['success']) {
            $this->db->trans_rollback();
            return $reversal;
        }
        $this->write_log((int)$idRetur, 'REVERSE', self::STATUS_POSTED, self::STATUS_POSTED, $header, $header, 'Jurnal reversal dibuat untuk void retur pembelian.', $user);

        foreach ($this->details((int)$idRetur) as $detail) {
            $this->reverse_stock($header, $detail);
        }

        $this->db->where('id_retur_pembelian', (int)$idRetur)->update('tb_retur_pembelian', [
            'status' => self::STATUS_VOID,
            'id_jurnal_reversal' => (int)$reversal['data']['id_jurnal'],
            'reversed_by' => $user,
            'reversed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $after = $this->header((int)$idRetur);
        $this->write_log((int)$idRetur, 'VOID', self::STATUS_POSTED, self::STATUS_VOID, $header, $after, $reason, $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Void retur pembelian gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Retur pembelian berhasil di-void dengan jurnal reversal.', ['id_jurnal_reversal' => (int)$reversal['data']['id_jurnal']]);
    }

    private function post_purchase_return_journal($header, $details, $user)
    {
        $idSupplier = $this->supplier_id($header['kd_supplier']);
        $idGudang = (int)$header['gudang_id'];
        $amountBkp = '0.0000';
        $vatBkp = '0.0000';
        $amountBkps = '0.0000';

        foreach ($details as $detail) {
            if ((string)$detail['kelompok_dagang'] === '2') {
                $amountBkp = bcadd($amountBkp, $this->money($detail['dpp']), 4);
                $vatBkp = bcadd($vatBkp, $this->money($detail['ppn']), 4);
            } elseif ((string)$detail['kelompok_dagang'] === '3') {
                $amountBkps = bcadd($amountBkps, $this->money($detail['dpp']), 4);
            }
        }

        $lines = [];
        if (bccomp($amountBkp, '0.0000', 4) === 1) {
            $ruleCode = 'RBELI-PH-BKP';
            $lines[] = $this->journal_line('13013', $ruleCode . ' - Piutang Non Dagang Retur Pembelian Belum Dipotong', bcadd($amountBkp, $vatBkp, 4), '0.0000', $idSupplier, $idGudang, $header['no_retur_pembelian']);
            $lines[] = $this->journal_line('14010', $ruleCode . ' - Persediaan # 1', '0.0000', $amountBkp, $idSupplier, $idGudang, $header['no_retur_pembelian']);
            $lines[] = $this->journal_line('13017', $ruleCode . ' - PPN Masukan / PPN M Ymh Diterima', '0.0000', $vatBkp, $idSupplier, $idGudang, $header['no_retur_pembelian']);
        }
        if (bccomp($amountBkps, '0.0000', 4) === 1) {
            $ruleCode = 'RBELI-PH-BKPS';
            $lines[] = $this->journal_line('13013', $ruleCode . ' - Piutang Non Dagang Retur Pembelian Belum Dipotong', $amountBkps, '0.0000', $idSupplier, $idGudang, $header['no_retur_pembelian']);
            $lines[] = $this->journal_line('14011', $ruleCode . ' - Persediaan Brg Dagangan BKPS', '0.0000', $amountBkps, $idSupplier, $idGudang, $header['no_retur_pembelian']);
        }

        foreach ($lines as $line) {
            if ((int)$line['id_akun'] <= 0) {
                return $this->fail('Akun retur pembelian belum valid di COA.', ['PURCHASE_RETURN_ACCOUNT_NOT_FOUND']);
            }
        }

        return $this->accounting_service->post_retur([
            'retur_type' => 'PURCHASE_RETURN',
            'journal_type' => 'PJ',
            'tanggal_transaksi' => $header['tanggal_retur'],
            'keterangan' => 'Retur pembelian ' . $header['no_retur_pembelian'],
            'source_module' => 'LOGISTIK',
            'source_type' => 'RETUR_PEMBELIAN',
            'source_id' => (string)$header['id_retur_pembelian'],
            'source_no' => $header['no_retur_pembelian'],
            'idempotency_key' => 'PURCHASE_RETURN-' . $header['id_retur_pembelian'],
            'scope_type' => 'WAREHOUSE',
            'scope_key' => (string)$header['gudang_id'],
            'amount' => bcadd($amountBkp, $amountBkps, 4),
            'tax' => $vatBkp,
            'cogs' => '0.0000',
            'lines' => $lines,
        ], $user);
    }

    private function posting_exception($header, $message, $errors, $user)
    {
        $this->load->library('Accounting_service');
        $this->accounting_service->capture_posting_exception('PURCHASE_RETURN', [
            'source_module' => 'LOGISTIK',
            'source_type' => 'RETUR_PEMBELIAN',
            'source_id' => (string)$header['id_retur_pembelian'],
            'source_no' => $header['no_retur_pembelian'],
            'idempotency_key' => 'PURCHASE_RETURN-' . $header['id_retur_pembelian'],
            'amount' => $header['total_dpp'],
            'tax' => $header['total_ppn'],
        ], $message, $errors);

        $this->db->where('id_retur_pembelian', (int)$header['id_retur_pembelian'])->update('tb_retur_pembelian', [
            'status' => self::STATUS_POSTING_EXCEPTION,
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $after = $this->header((int)$header['id_retur_pembelian']);
        $this->write_log((int)$header['id_retur_pembelian'], 'POST', $header['status'], self::STATUS_POSTING_EXCEPTION, $header, $after, $message, $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Posting exception gagal dicatat.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->fail($message, $errors);
    }

    private function validate_line($idDetailLpb, $qtyRetur, $idLpb, $excludeReturDetailId = 0)
    {
        if ($idDetailLpb <= 0 || $qtyRetur <= 0) {
            return $this->fail('Qty retur harus lebih dari 0.', ['RETUR_QTY_INVALID']);
        }

        $sql = "SELECT d.*, l.nomor_lpb, l.gudang_id, l.kd_po, l.no_po,
                    COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_satuan_final,
                    COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') AS kelompok_dagang,
                    COALESCE(lb.qty, 0) AS qty_batch,
                    COALESCE(sb.qty_on_hand, 0) AS qty_on_hand,
                    COALESCE((
                        SELECT SUM(rd.qty_retur)
                        FROM tb_retur_pembelian_detail rd
                        INNER JOIN tb_retur_pembelian rh ON rh.id_retur_pembelian = rd.id_retur_pembelian
                        WHERE rd.id_detail_lpb = d.id_detail_lpb
                            AND rh.status NOT IN ('VOID')
                            AND rd.id_detail_retur_pembelian <> ?
                    ), 0) AS qty_retur_sebelumnya
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                LEFT JOIN tb_lpb_batch lb ON lb.id_detail_lpb = d.id_detail_lpb
                    AND COALESCE(lb.no_lot, '') = COALESCE(d.no_lot, '')
                    AND (lb.expired_date <=> d.expired_date)
                LEFT JOIN tbpo_detail_po pp ON pp.kd_po = l.kd_po AND pp.no_po = l.no_po AND pp.kd_barang = d.kd_barang
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                LEFT JOIN tberp_stock_batch sb ON sb.kd_barang = d.kd_barang
                    AND sb.gudang_id = CAST(l.gudang_id AS CHAR)
                    AND COALESCE(sb.no_lot, '') = COALESCE(d.no_lot, '')
                    AND (sb.expired_date <=> d.expired_date)
                WHERE d.id_detail_lpb = ? AND d.id_lpb = ?
                LIMIT 1";
        $row = $this->db->query($sql, [(int)$excludeReturDetailId, (int)$idDetailLpb, (int)$idLpb])->row_array();

        if (!$row || trim((string)$row['nomor_lpb']) === '') {
            return $this->fail('LPB/detail final tidak valid atau nomor LPB belum tersedia.', ['LPB_DETAIL_INVALID']);
        }
        if ((float)$row['qty_batch'] <= 0) {
            return $this->fail('Lot/expired tidak cocok dengan tb_lpb_batch.', ['LPB_BATCH_NOT_FOUND']);
        }

        $availableFromLpb = (float)$row['qty_diterima'] - (float)$row['qty_retur_sebelumnya'];
        if ($qtyRetur > $availableFromLpb + 0.0001) {
            return $this->fail('Qty retur melebihi qty diterima dikurangi retur sebelumnya.', ['RETUR_QTY_EXCEEDS_LPB']);
        }
        if ($qtyRetur > (float)$row['qty_on_hand'] + 0.0001) {
            return $this->fail('Qty stok fisik gudang tidak cukup untuk retur.', ['STOCK_NOT_ENOUGH']);
        }
        if ((float)$row['harga_satuan_final'] <= 0) {
            return $this->fail('Harga satuan LPB belum valid untuk retur.', ['LPB_PRICE_INVALID']);
        }

        return $this->ok('Valid.', $row);
    }

    private function calculate_line($row, $qty, $alasan)
    {
        $harga = $this->money($row['harga_satuan_final']);
        $dpp = $this->money(bcmul((string)$qty, $harga, 4));
        $ppn = ((string)$row['kelompok_dagang'] === '2') ? $this->money(bcdiv(bcmul($dpp, '11', 4), '100', 4)) : '0.0000';

        return [
            'id_detail_lpb' => (int)$row['id_detail_lpb'],
            'kd_barang' => $row['kd_barang'],
            'no_lot' => $row['no_lot'],
            'expired_date' => $row['expired_date'],
            'qty_retur' => $qty,
            'harga_satuan' => $harga,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'total' => bcadd($dpp, $ppn, 4),
            'kelompok_dagang' => (string)$row['kelompok_dagang'],
            'alasan_retur' => $alasan,
        ];
    }

    private function decrease_stock($header, $detail)
    {
        $this->db->where('kd_barang', $detail['kd_barang']);
        $this->db->where('gudang_id', (string)$header['gudang_id']);
        $this->db->where('no_lot', $detail['no_lot']);
        if ($detail['expired_date'] !== null) {
            $this->db->where('expired_date', $detail['expired_date']);
        } else {
            $this->db->where('expired_date', null);
        }
        $this->db->set('qty_on_hand', 'qty_on_hand - ' . (float)$detail['qty_retur'], false);
        $this->db->set('update_at', date('Y-m-d H:i:s'));
        $this->db->update('tberp_stock_batch');

        $this->db->insert('tberp_stock_ledger', [
            'kd_barang' => $detail['kd_barang'],
            'gudang_id' => (string)$header['gudang_id'],
            'no_lot' => $detail['no_lot'],
            'expired_date' => $detail['expired_date'],
            'qty' => (float)$detail['qty_retur'],
            'tipe' => 'RBELI',
            'ref_no' => $header['no_retur_pembelian'],
            'ref_type' => 'RETUR_PEMBELIAN',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function reverse_stock($header, $detail)
    {
        $this->db->where('kd_barang', $detail['kd_barang']);
        $this->db->where('gudang_id', (string)$header['gudang_id']);
        $this->db->where('no_lot', $detail['no_lot']);
        if ($detail['expired_date'] !== null) {
            $this->db->where('expired_date', $detail['expired_date']);
        } else {
            $this->db->where('expired_date', null);
        }
        $this->db->set('qty_on_hand', 'qty_on_hand + ' . (float)$detail['qty_retur'], false);
        $this->db->set('update_at', date('Y-m-d H:i:s'));
        $this->db->update('tberp_stock_batch');

        $this->db->insert('tberp_stock_ledger', [
            'kd_barang' => $detail['kd_barang'],
            'gudang_id' => (string)$header['gudang_id'],
            'no_lot' => $detail['no_lot'],
            'expired_date' => $detail['expired_date'],
            'qty' => 0 - (float)$detail['qty_retur'],
            'tipe' => 'RBELI',
            'ref_no' => $header['no_retur_pembelian'],
            'ref_type' => 'RETUR_PEMBELIAN_VOID',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function change_status($idRetur, $allowed, $status, $action, $updates, $note, $user)
    {
        $this->ensure_schema();
        $this->db->trans_begin();
        $before = $this->locked_header($idRetur);
        if (!$before || !in_array($before['status'], $allowed, true)) {
            $this->db->trans_rollback();
            return $this->fail('Status retur pembelian tidak valid untuk aksi ini.', ['INVALID_RETUR_STATUS']);
        }

        $updates['status'] = $status;
        $updates['updated_by'] = $user;
        $updates['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', $updates);
        $after = $this->header($idRetur);
        $this->write_log($idRetur, $action, $before['status'], $status, $before, $after, $note, $user);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Perubahan status retur gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Status retur pembelian diperbarui.', ['status' => $status]);
    }

    private function lpb_header($idLpb)
    {
        $sql = "SELECT l.*, p.kd_suplier
                FROM tb_lpb l
                LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
                WHERE l.id_lpb = ?
                LIMIT 1";
        return $this->db->query($sql, [(int)$idLpb])->row_array();
    }

    private function header($idRetur)
    {
        return $this->db->where('id_retur_pembelian', (int)$idRetur)->get('tb_retur_pembelian')->row_array();
    }

    private function locked_header($idRetur)
    {
        return $this->db->query('SELECT * FROM tb_retur_pembelian WHERE id_retur_pembelian = ? FOR UPDATE', [(int)$idRetur])->row_array();
    }

    private function details($idRetur)
    {
        return $this->db->where('id_retur_pembelian', (int)$idRetur)->order_by('id_detail_retur_pembelian', 'ASC')->get('tb_retur_pembelian_detail')->result_array();
    }

    private function generate_number()
    {
        $prefix = 'RBELI-' . date('Ymd') . '-';
        $row = $this->db->query(
            "SELECT no_retur_pembelian FROM tb_retur_pembelian WHERE no_retur_pembelian LIKE ? ORDER BY no_retur_pembelian DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        )->row_array();
        $next = $row ? ((int)substr($row['no_retur_pembelian'], -4) + 1) : 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    private function supplier_id($kdSupplier)
    {
        $row = $this->db->select('id_suplier')->where('kd_suplier', $kdSupplier)->get('tbpo_suplier')->row_array();
        return $row ? (int)$row['id_suplier'] : 0;
    }

    private function journal_line($kodeAkun, $label, $debit, $kredit, $idSupplier, $idGudang, $nomorDokumen)
    {
        return [
            'id_akun' => $this->account_id($kodeAkun),
            'keterangan' => $label,
            'debit' => $this->money($debit),
            'kredit' => $this->money($kredit),
            'id_customer' => 0,
            'id_supplier' => $idSupplier,
            'id_barang' => 0,
            'id_gudang' => $idGudang,
            'id_departemen' => 0,
            'tanggal_jatuh_tempo' => '',
            'nomor_dokumen' => $nomorDokumen,
        ];
    }

    private function account_id($kodeAkun)
    {
        $row = $this->db->select('id_akun')->where('kode_akun', $kodeAkun)->get('tbkeu_akun')->row_array();
        return $row ? (int)$row['id_akun'] : 0;
    }

    private function write_log($idRetur, $action, $beforeStatus, $afterStatus, $before, $after, $note, $user)
    {
        $this->db->insert('tb_retur_pembelian_log', [
            'id_retur_pembelian' => $idRetur,
            'no_retur_pembelian' => is_array($after) ? ($after['no_retur_pembelian'] ?? null) : (is_array($before) ? ($before['no_retur_pembelian'] ?? null) : null),
            'action_type' => $action,
            'status_before' => $beforeStatus,
            'status_after' => $afterStatus,
            'data_before' => $before !== null ? json_encode($before) : null,
            'data_after' => $after !== null ? json_encode($after) : null,
            'keterangan' => $note,
            'dilakukan_oleh' => $user,
            'dilakukan_pada' => date('Y-m-d H:i:s'),
        ]);
    }

    private function normalize_date($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }
        $time = strtotime($raw);
        return $time ? date('Y-m-d', $time) : '';
    }

    private function money($value)
    {
        $value = str_replace(',', '.', trim((string)$value));
        if ($value === '' || !preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            return '0.0000';
        }
        return bcadd($value, '0', 4);
    }

    private function ok($message, $data = [])
    {
        return ['success' => true, 'message' => $message, 'data' => $data, 'errors' => []];
    }

    private function fail($message, $errors = [])
    {
        return ['success' => false, 'message' => $message, 'data' => null, 'errors' => $errors];
    }
}
