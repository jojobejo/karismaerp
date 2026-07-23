<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_LpbPriceAdjustment extends CI_Model
{
    const STATUS_POSTED = 'POSTED';
    const ADJ_LOT = 'Adj. Harga Beli';
    const ADJ_EXPIRED = '1000-01-01';

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_lpb_price_adjustment` (
            `id_adjustment` INT(11) NOT NULL AUTO_INCREMENT,
            `no_adjustment` VARCHAR(50) NOT NULL,
            `id_lpb_salah` INT(11) NOT NULL,
            `id_lpb_adjustment` INT(11) DEFAULT NULL,
            `id_retur_pembelian` INT(11) DEFAULT NULL,
            `nomor_lpb_salah` VARCHAR(50) DEFAULT NULL,
            `nomor_lpb_adjustment` VARCHAR(50) DEFAULT NULL,
            `tanggal_adjustment` DATE NOT NULL,
            `kd_po` VARCHAR(100) DEFAULT NULL,
            `no_po` VARCHAR(100) DEFAULT NULL,
            `kd_supplier` VARCHAR(100) DEFAULT NULL,
            `gudang_id` VARCHAR(30) DEFAULT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'DRAFT',
            `alasan_adjustment` TEXT DEFAULT NULL,
            `total_qty` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `total_lpb_salah` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total_lpb_benar` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `selisih_dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `selisih_ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `selisih_total` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `id_jurnal_lpb_adjustment` INT(11) DEFAULT NULL,
            `id_jurnal_prpp` INT(11) DEFAULT NULL,
            `created_by` VARCHAR(100) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `posted_by` VARCHAR(100) DEFAULT NULL,
            `posted_at` DATETIME DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id_adjustment`),
            UNIQUE KEY `uk_no_adjustment` (`no_adjustment`),
            KEY `idx_lpb_salah` (`id_lpb_salah`),
            KEY `idx_lpb_adjustment` (`id_lpb_adjustment`),
            KEY `idx_retur` (`id_retur_pembelian`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_lpb_price_adjustment_detail` (
            `id_adjustment_detail` INT(11) NOT NULL AUTO_INCREMENT,
            `id_adjustment` INT(11) NOT NULL,
            `id_detail_lpb_salah` INT(11) NOT NULL,
            `id_detail_lpb_adjustment` INT(11) DEFAULT NULL,
            `kd_barang` VARCHAR(100) NOT NULL,
            `nama_barang` VARCHAR(255) DEFAULT NULL,
            `qty` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `no_lot_adjustment` VARCHAR(50) DEFAULT NULL,
            `expired_date_adjustment` DATE DEFAULT NULL,
            `harga_salah` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `harga_benar` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `dpp_salah` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `dpp_benar` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `ppn_salah` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `ppn_benar` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total_salah` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `total_benar` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `kelompok_dagang` VARCHAR(10) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_adjustment_detail`),
            KEY `idx_adjustment` (`id_adjustment`),
            KEY `idx_detail_salah` (`id_detail_lpb_salah`),
            KEY `idx_detail_adjustment` (`id_detail_lpb_adjustment`),
            KEY `idx_barang_batch` (`kd_barang`, `no_lot_adjustment`, `expired_date_adjustment`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function rows($limit = 100)
    {
        $this->ensure_schema();
        $sql = "SELECT a.*, s.nama_suplier,
                    rp.no_retur_pembelian,
                    jl.nomor_jurnal AS nomor_jurnal_lpb,
                    jr.nomor_jurnal AS nomor_jurnal_prpp
                FROM tb_lpb_price_adjustment a
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = a.kd_supplier
                LEFT JOIN tb_retur_pembelian rp ON rp.id_retur_pembelian = a.id_retur_pembelian
                LEFT JOIN tbkeu_jurnal jl ON jl.id_jurnal = a.id_jurnal_lpb_adjustment
                LEFT JOIN tbkeu_jurnal jr ON jr.id_jurnal = a.id_jurnal_prpp
                ORDER BY a.created_at DESC, a.id_adjustment DESC
                LIMIT ?";
        return $this->db->query($sql, [(int)$limit])->result_array();
    }

    public function lpb_options($search = '')
    {
        $this->ensure_schema();
        $search = trim((string)$search);
        $params = [];
        $where = "WHERE COALESCE(NULLIF(TRIM(l.nomor_lpb), ''), '') <> ''
            AND adj.id_lpb_adjustment IS NULL";

        if ($search !== '') {
            $where .= " AND (l.nomor_lpb LIKE ? OR l.no_po LIKE ? OR l.kd_po LIKE ? OR l.no_invoice LIKE ? OR s.nama_suplier LIKE ?)";
            $like = '%' . $search . '%';
            $params = [$like, $like, $like, $like, $like];
        }

        $sql = "SELECT l.id_lpb, l.nomor_lpb, l.kd_po, l.no_po, l.no_invoice, l.gudang_id,
                    p.kd_suplier, COALESCE(s.nama_suplier, '') AS nama_suplier,
                    COUNT(d.id_detail_lpb) AS total_detail
                FROM tb_lpb l
                INNER JOIN tb_lpb_detail d ON d.id_lpb = l.id_lpb
                LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
                LEFT JOIN tb_lpb_price_adjustment adj ON adj.id_lpb_adjustment = l.id_lpb
                {$where}
                GROUP BY l.id_lpb
                ORDER BY l.input_at DESC, l.id_lpb DESC
                LIMIT 30";
        return $this->db->query($sql, $params)->result_array();
    }

    public function lpb_detail_rows($idLpb)
    {
        $sql = "SELECT d.id_detail_lpb, d.id_lpb, d.kd_barang,
                    COALESCE(m.nama_barang, b.nama_barang, d.kd_barang) AS nama_barang,
                    d.no_lot, d.expired_date, COALESCE(d.qty_diterima, 0) AS qty_diterima,
                    COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0) AS harga_salah,
                    COALESCE(NULLIF(d.total_harga, 0), d.qty_diterima * COALESCE(NULLIF(d.harga_satuan, 0), NULLIF(pp.harga_satuan_kecil_exclude, 0), NULLIF(pp.harga_satuan_exclude, 0), 0), 0) AS total_salah,
                    COALESCE(NULLIF(TRIM(b.kelompok_dagang), ''), NULLIF(TRIM(b.kelompok_barang), ''), '') AS kelompok_dagang
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                LEFT JOIN tbpo_detail_po pp ON pp.kd_po = l.kd_po AND pp.no_po = l.no_po AND pp.kd_barang = d.kd_barang
                LEFT JOIN tbpo_barang b ON b.kode_barang = d.kd_barang
                LEFT JOIN tbpo_barang m ON m.kode_barang = d.kd_barang
                WHERE d.id_lpb = ?
                ORDER BY d.id_detail_lpb ASC";
        return $this->db->query($sql, [(int)$idLpb])->result_array();
    }

    public function create_and_post($payload, $details, $user, $userId = null)
    {
        $this->ensure_schema();
        $this->load->model('M_Logistik');
        $this->load->model('M_ReturPembelian');
        $this->load->library('Accounting_source_service');
        $this->load->library('Accounting_service');
        $this->M_ReturPembelian->ensure_schema();

        $idLpbSalah = (int)($payload['id_lpb'] ?? 0);
        $tanggal = $this->normalize_date($payload['tanggal_adjustment'] ?? date('Y-m-d'));
        $alasan = trim((string)($payload['alasan_adjustment'] ?? ''));

        if ($idLpbSalah <= 0 || $tanggal === '' || empty($details)) {
            return $this->fail('LPB, tanggal adjustment, dan harga invoice benar wajib diisi.', ['ADJUSTMENT_DATA_REQUIRED']);
        }

        $priceMap = [];
        foreach ((array)$details as $detail) {
            $idDetail = (int)($detail['id_detail_lpb_salah'] ?? 0);
            $harga = $this->to_float($detail['harga_benar'] ?? 0);
            if ($idDetail > 0 && $harga > 0) {
                $priceMap[$idDetail] = $harga;
            }
        }

        if (empty($priceMap)) {
            return $this->fail('Minimal satu harga invoice benar harus lebih dari 0.', ['ADJUSTMENT_PRICE_EMPTY']);
        }

        $source = $this->lpb_header($idLpbSalah);
        if (!$source || trim((string)($source['nomor_lpb'] ?? '')) === '') {
            return $this->fail('LPB salah tidak ditemukan atau belum memiliki nomor LPB.', ['LPB_SOURCE_NOT_FOUND']);
        }
        if (trim((string)($source['kd_suplier'] ?? '')) === '') {
            return $this->fail('Supplier LPB asal tidak ditemukan. Adjustment harga beli membutuhkan supplier untuk PRPP dan jurnal.', ['LPB_SOURCE_SUPPLIER_REQUIRED']);
        }

        $sourceDetails = $this->lpb_detail_rows($idLpbSalah);
        if (empty($sourceDetails)) {
            return $this->fail('Detail LPB salah kosong.', ['LPB_SOURCE_DETAIL_EMPTY']);
        }

        $prepared = [];
        $hasDifferentPrice = false;
        foreach ($sourceDetails as $row) {
            $idDetail = (int)$row['id_detail_lpb'];
            if (!isset($priceMap[$idDetail])) {
                return $this->fail('Harga invoice benar wajib diisi untuk semua detail LPB.', ['ADJUSTMENT_PRICE_INCOMPLETE']);
            }

            $qty = $this->to_float($row['qty_diterima'] ?? 0);
            $hargaSalah = $this->to_float($row['harga_salah'] ?? 0);
            $hargaBenar = $priceMap[$idDetail];
            $kelompok = trim((string)($row['kelompok_dagang'] ?? ''));

            if ($qty <= 0 || $hargaSalah <= 0 || $hargaBenar <= 0) {
                return $this->fail('Qty dan harga LPB harus valid untuk semua detail.', ['ADJUSTMENT_LINE_INVALID']);
            }
            if (!in_array($kelompok, ['2', '3'], true)) {
                return $this->fail('Kelompok dagang ' . ($kelompok !== '' ? $kelompok : '-') . ' belum aman untuk adjustment otomatis.', ['ADJUSTMENT_GROUP_UNSUPPORTED']);
            }
            if (abs($hargaBenar - $hargaSalah) > 0.0001) {
                $hasDifferentPrice = true;
            }

            $prepared[] = $this->prepare_line($row, $qty, $hargaSalah, $hargaBenar, $kelompok);
        }

        if (!$hasDifferentPrice) {
            return $this->fail('Harga invoice benar sama dengan harga LPB salah. Adjustment tidak diperlukan.', ['ADJUSTMENT_NO_PRICE_DIFF']);
        }

        $this->db->trans_begin();

        $noAdjustment = $this->generate_adjustment_number();
        $nomorLpbAdjustment = $this->generate_lpb_adjustment_number($source['nomor_lpb']);
        $stockBefore = $this->stock_snapshot($source['gudang_id'], $prepared);

        $this->db->insert('tb_lpb_price_adjustment', [
            'no_adjustment' => $noAdjustment,
            'id_lpb_salah' => $idLpbSalah,
            'nomor_lpb_salah' => $source['nomor_lpb'],
            'nomor_lpb_adjustment' => $nomorLpbAdjustment,
            'tanggal_adjustment' => $tanggal,
            'kd_po' => $source['kd_po'],
            'no_po' => $source['no_po'],
            'kd_supplier' => $source['kd_suplier'],
            'gudang_id' => (string)$source['gudang_id'],
            'status' => 'PROCESSING',
            'alasan_adjustment' => $alasan,
            'created_by' => $user,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $idAdjustment = (int)$this->db->insert_id();
        if ($idAdjustment <= 0) {
            $this->db->trans_rollback();
            return $this->fail('Header adjustment gagal dibuat.', ['DATABASE_ERROR']);
        }

        $idLpbAdjustment = $this->create_adjustment_lpb($source, $prepared, $nomorLpbAdjustment, $tanggal, $noAdjustment, $alasan, $user);
        if ($idLpbAdjustment <= 0) {
            $this->db->trans_rollback();
            return $this->fail('LPB adjustment gagal dibuat.', ['LPB_ADJUSTMENT_FAILED']);
        }

        $journalLpb = $this->accounting_source_service->post_goods_receipt($idLpbAdjustment, $userId);
        if (!$journalLpb['success']) {
            $this->db->trans_rollback();
            return $journalLpb;
        }

        $idRetur = $this->create_prpp_posted($source, $prepared, $idAdjustment, $idLpbAdjustment, $tanggal, $noAdjustment, $alasan, $user, $userId);
        if ($idRetur <= 0) {
            $this->db->trans_rollback();
            return $this->fail('PRPP adjustment gagal dibuat.', ['PRPP_ADJUSTMENT_FAILED']);
        }

        if (!$this->stock_matches_snapshot($source['gudang_id'], $prepared, $stockBefore)) {
            $this->db->trans_rollback();
            return $this->fail('Validasi stok dummy adjustment gagal. Qty lot Adj. Harga Beli tidak kembali ke saldo awal.', ['ADJUSTMENT_STOCK_NOT_BALANCED']);
        }

        $totals = $this->totals($prepared);
        $retur = $this->db->where('id_retur_pembelian', $idRetur)->get('tb_retur_pembelian')->row_array();
        $this->db->where('id_adjustment', $idAdjustment)->update('tb_lpb_price_adjustment', [
            'id_lpb_adjustment' => $idLpbAdjustment,
            'id_retur_pembelian' => $idRetur,
            'total_qty' => $totals['qty'],
            'total_lpb_salah' => $totals['total_salah'],
            'total_lpb_benar' => $totals['total_benar'],
            'selisih_dpp' => $totals['dpp_benar'] - $totals['dpp_salah'],
            'selisih_ppn' => $totals['ppn_benar'] - $totals['ppn_salah'],
            'selisih_total' => $totals['total_benar'] - $totals['total_salah'],
            'id_jurnal_lpb_adjustment' => (int)($journalLpb['data']['id_jurnal'] ?? 0),
            'id_jurnal_prpp' => (int)($retur['id_jurnal'] ?? 0),
            'status' => self::STATUS_POSTED,
            'posted_by' => $user,
            'posted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->fail('Adjustment harga LPB gagal disimpan.', ['DATABASE_ERROR']);
        }

        $this->db->trans_commit();
        return $this->ok('Adjustment harga LPB berhasil diposting. Konfirmasi ke bagian jurnal bahwa adjustment sudah dibuat.', [
            'id_adjustment' => $idAdjustment,
            'no_adjustment' => $noAdjustment,
            'id_lpb_adjustment' => $idLpbAdjustment,
            'nomor_lpb_adjustment' => $nomorLpbAdjustment,
            'id_retur_pembelian' => $idRetur,
        ]);
    }

    private function create_adjustment_lpb($source, &$lines, $nomorLpbAdjustment, $tanggal, $noAdjustment, $alasan, $user)
    {
        $header = [
            'kd_po' => $source['kd_po'],
            'no_po' => $source['no_po'],
            'nosj' => 'ADJ-HARGA-BELI',
            'tgl_sj' => $tanggal,
            'no_invoice' => $source['no_invoice'],
            'gudang_id' => $source['gudang_id'],
            'keterangan' => 'Adjustment Harga Beli dari LPB ' . $source['nomor_lpb'] . ($alasan !== '' ? '. ' . $alasan : ''),
            'input_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $header['jenis_lpb'] = $source['jenis_lpb'] ?: 'LPB CP';
        }
        if ($this->db->field_exists('nomor_lpb', 'tb_lpb')) {
            $header['nomor_lpb'] = $nomorLpbAdjustment;
        }
        if ($this->db->field_exists('status_lpb', 'tb_lpb')) {
            $header['status_lpb'] = 1;
        }
        if ($this->db->field_exists('checker_name', 'tb_lpb')) {
            $header['checker_name'] = $user;
        }
        if ($this->db->field_exists('checker_by', 'tb_lpb')) {
            $header['checker_by'] = $user;
        }
        if ($this->db->field_exists('checker_at', 'tb_lpb')) {
            $header['checker_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('tb_lpb', $header);
        $idLpb = (int)$this->db->insert_id();
        if ($idLpb <= 0) {
            return 0;
        }

        foreach ($lines as &$line) {
            $detail = [
                'id_lpb' => $idLpb,
                'kd_barang' => $line['kd_barang'],
                'qty_diterima' => $line['qty'],
                'no_lot' => self::ADJ_LOT,
                'expired_date' => self::ADJ_EXPIRED,
                'input_at' => date('Y-m-d H:i:s'),
            ];
            if ($this->db->field_exists('harga_satuan', 'tb_lpb_detail')) {
                $detail['harga_satuan'] = $line['harga_benar'];
            }
            if ($this->db->field_exists('total_harga', 'tb_lpb_detail')) {
                $detail['total_harga'] = $line['dpp_benar'];
            }
            $this->db->insert('tb_lpb_detail', $detail);
            $idDetailAdjustment = (int)$this->db->insert_id();
            if ($idDetailAdjustment <= 0) {
                return 0;
            }
            $line['id_detail_lpb_adjustment'] = $idDetailAdjustment;

            $this->db->insert('tb_lpb_batch', [
                'id_detail_lpb' => $idDetailAdjustment,
                'no_lot' => self::ADJ_LOT,
                'expired_date' => self::ADJ_EXPIRED,
                'qty' => $line['qty'],
            ]);
            $this->increase_stock($source['gudang_id'], $line, $noAdjustment);
        }
        unset($line);

        $this->M_Logistik->insert_lpb_activity_log([
            'id_lpb' => $idLpb,
            'kd_po' => $source['kd_po'],
            'no_invoice' => $source['no_invoice'],
            'action_type' => 'CREATE_LPB_PRICE_ADJUSTMENT',
            'status_before' => null,
            'status_after' => 'POST',
            'data_after' => [
                'nomor_lpb' => $nomorLpbAdjustment,
                'no_adjustment' => $noAdjustment,
                'no_lot' => self::ADJ_LOT,
                'expired_date' => self::ADJ_EXPIRED,
            ],
            'keterangan' => 'LPB adjustment harga beli dibuat otomatis dari LPB ' . $source['nomor_lpb'],
            'dilakukan_oleh' => $user,
        ]);

        return $idLpb;
    }

    private function create_prpp_posted($source, $lines, $idAdjustment, $idLpbAdjustment, $tanggal, $noAdjustment, $alasan, $user, $userId)
    {
        $noRetur = $this->generate_prpp_number();
        $totals = $this->totals($lines);
        $this->db->insert('tb_retur_pembelian', [
            'no_retur_pembelian' => $noRetur,
            'id_lpb' => $idLpbAdjustment,
            'kd_po' => $source['kd_po'],
            'no_po' => $source['no_po'],
            'kd_supplier' => $source['kd_suplier'],
            'tanggal_retur' => $tanggal,
            'gudang_id' => (string)$source['gudang_id'],
            'status' => self::STATUS_POSTED,
            'jenis_penyelesaian' => 'POTONG_HUTANG',
            'alasan_retur' => 'PRPP Adjustment Harga Beli dari LPB ' . $source['nomor_lpb'] . ($alasan !== '' ? '. ' . $alasan : ''),
            'total_dpp' => $totals['dpp_salah'],
            'total_ppn' => $totals['ppn_salah'],
            'grand_total' => $totals['total_salah'],
            'created_by' => $user,
            'created_at' => date('Y-m-d H:i:s'),
            'submitted_by' => $user,
            'submitted_at' => date('Y-m-d H:i:s'),
            'purchasing_verified_by' => $user,
            'purchasing_verified_at' => date('Y-m-d H:i:s'),
            'accounting_verified_by' => $user,
            'accounting_verified_at' => date('Y-m-d H:i:s'),
            'posted_by' => $user,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);
        $idRetur = (int)$this->db->insert_id();
        if ($idRetur <= 0) {
            return 0;
        }

        foreach ($lines as $line) {
            $this->db->insert('tb_retur_pembelian_detail', [
                'id_retur_pembelian' => $idRetur,
                'id_detail_lpb' => $line['id_detail_lpb_adjustment'],
                'kd_barang' => $line['kd_barang'],
                'no_lot' => self::ADJ_LOT,
                'expired_date' => self::ADJ_EXPIRED,
                'qty_retur' => $line['qty'],
                'harga_satuan' => $line['harga_salah'],
                'dpp' => $line['dpp_salah'],
                'ppn' => $line['ppn_salah'],
                'total' => $line['total_salah'],
                'kelompok_dagang' => $line['kelompok_dagang'],
                'alasan_retur' => 'PRPP adjustment harga beli ' . $noAdjustment,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->insert('tb_lpb_price_adjustment_detail', [
                'id_adjustment' => $idAdjustment,
                'id_detail_lpb_salah' => $line['id_detail_lpb_salah'],
                'id_detail_lpb_adjustment' => $line['id_detail_lpb_adjustment'],
                'kd_barang' => $line['kd_barang'],
                'nama_barang' => $line['nama_barang'],
                'qty' => $line['qty'],
                'no_lot_adjustment' => self::ADJ_LOT,
                'expired_date_adjustment' => self::ADJ_EXPIRED,
                'harga_salah' => $line['harga_salah'],
                'harga_benar' => $line['harga_benar'],
                'dpp_salah' => $line['dpp_salah'],
                'dpp_benar' => $line['dpp_benar'],
                'ppn_salah' => $line['ppn_salah'],
                'ppn_benar' => $line['ppn_benar'],
                'total_salah' => $line['total_salah'],
                'total_benar' => $line['total_benar'],
                'kelompok_dagang' => $line['kelompok_dagang'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->decrease_stock($source['gudang_id'], $line, $noRetur);
        }

        $journal = $this->post_prpp_journal($source, $lines, $idRetur, $noRetur, $tanggal, $userId);
        if (!$journal['success']) {
            return 0;
        }

        $this->db->where('id_retur_pembelian', $idRetur)->update('tb_retur_pembelian', [
            'id_jurnal' => (int)($journal['data']['id_jurnal'] ?? 0),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->insert('tb_retur_pembelian_log', [
            'id_retur_pembelian' => $idRetur,
            'no_retur_pembelian' => $noRetur,
            'action_type' => 'ADJUSTMENT_HARGA_BELI',
            'status_before' => null,
            'status_after' => self::STATUS_POSTED,
            'data_before' => null,
            'data_after' => json_encode(['id_adjustment' => $idAdjustment, 'no_adjustment' => $noAdjustment]),
            'keterangan' => 'PRPP otomatis untuk adjustment harga beli.',
            'dilakukan_oleh' => $user,
            'dilakukan_pada' => date('Y-m-d H:i:s'),
        ]);

        return $idRetur;
    }

    private function post_prpp_journal($source, $lines, $idRetur, $noRetur, $tanggal, $userId)
    {
        $idSupplier = (int)($source['id_suplier'] ?? 0);
        $idGudang = (int)$source['gudang_id'];
        $amountBkp = 0;
        $vatBkp = 0;
        $amountBkps = 0;

        foreach ($lines as $line) {
            if ((string)$line['kelompok_dagang'] === '2') {
                $amountBkp += (float)$line['dpp_salah'];
                $vatBkp += (float)$line['ppn_salah'];
            } elseif ((string)$line['kelompok_dagang'] === '3') {
                $amountBkps += (float)$line['dpp_salah'];
            }
        }

        $totalPayable = $amountBkp + $vatBkp + $amountBkps;
        $journalLines = [];
        $journalLines[] = $this->journal_line('21098', 'Hutang Usaha', $totalPayable, 0, $idSupplier, $idGudang, $noRetur);
        if ($amountBkp > 0) {
            $journalLines[] = $this->journal_line('14010', 'Persediaan # 1', 0, $amountBkp, $idSupplier, $idGudang, $noRetur);
            $journalLines[] = $this->journal_line('13017', 'PPN Masukan / PPN M Ymh Diterima', 0, $vatBkp, $idSupplier, $idGudang, $noRetur);
        }
        if ($amountBkps > 0) {
            $journalLines[] = $this->journal_line('14011', 'Persediaan Brg Dagangan BKPS', 0, $amountBkps, $idSupplier, $idGudang, $noRetur);
        }

        foreach ($journalLines as $line) {
            if ((int)$line['id_akun'] <= 0) {
                return $this->fail('Akun PRPP adjustment belum valid di COA.', ['ADJUSTMENT_ACCOUNT_NOT_FOUND']);
            }
        }

        return $this->accounting_service->post_retur([
            'retur_type' => 'PURCHASE_RETURN',
            'journal_type' => 'PJ',
            'tanggal_transaksi' => $tanggal,
            'keterangan' => 'PRPP adjustment harga beli ' . $noRetur,
            'source_module' => 'LOGISTIK',
            'source_type' => 'RETUR_PEMBELIAN',
            'source_id' => (string)$idRetur,
            'source_no' => $noRetur,
            'idempotency_key' => 'PURCHASE_RETURN-' . $idRetur,
            'scope_type' => 'WAREHOUSE',
            'scope_key' => (string)$source['gudang_id'],
            'amount' => $this->money($amountBkp + $amountBkps),
            'tax' => $this->money($vatBkp),
            'cogs' => '0.0000',
            'lines' => $journalLines,
        ], $userId);
    }

    private function prepare_line($row, $qty, $hargaSalah, $hargaBenar, $kelompok)
    {
        $dppSalah = $qty * $hargaSalah;
        $dppBenar = $qty * $hargaBenar;
        $ppnSalah = ($kelompok === '2') ? ($dppSalah * 0.11) : 0;
        $ppnBenar = ($kelompok === '2') ? ($dppBenar * 0.11) : 0;

        return [
            'id_detail_lpb_salah' => (int)$row['id_detail_lpb'],
            'id_detail_lpb_adjustment' => 0,
            'kd_barang' => $row['kd_barang'],
            'nama_barang' => $row['nama_barang'],
            'qty' => $this->money2($qty),
            'harga_salah' => $this->money($hargaSalah),
            'harga_benar' => $this->money($hargaBenar),
            'dpp_salah' => $this->money($dppSalah),
            'dpp_benar' => $this->money($dppBenar),
            'ppn_salah' => $this->money($ppnSalah),
            'ppn_benar' => $this->money($ppnBenar),
            'total_salah' => $this->money($dppSalah + $ppnSalah),
            'total_benar' => $this->money($dppBenar + $ppnBenar),
            'kelompok_dagang' => $kelompok,
        ];
    }

    private function increase_stock($gudangId, $line, $refNo)
    {
        $this->upsert_stock($gudangId, $line['kd_barang'], (float)$line['qty']);
        $this->db->insert('tberp_stock_ledger', [
            'kd_barang' => $line['kd_barang'],
            'gudang_id' => (string)$gudangId,
            'no_lot' => self::ADJ_LOT,
            'expired_date' => self::ADJ_EXPIRED,
            'qty' => (float)$line['qty'],
            'tipe' => 'IN',
            'ref_no' => $refNo,
            'ref_type' => 'LPB_PRICE_ADJUSTMENT_IN',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function decrease_stock($gudangId, $line, $refNo)
    {
        $this->upsert_stock($gudangId, $line['kd_barang'], 0 - (float)$line['qty']);
        $this->db->insert('tberp_stock_ledger', [
            'kd_barang' => $line['kd_barang'],
            'gudang_id' => (string)$gudangId,
            'no_lot' => self::ADJ_LOT,
            'expired_date' => self::ADJ_EXPIRED,
            'qty' => (float)$line['qty'],
            'tipe' => 'RBELI',
            'ref_no' => $refNo,
            'ref_type' => 'LPB_PRICE_ADJUSTMENT_PRPP',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function upsert_stock($gudangId, $kdBarang, $qtyDelta)
    {
        $row = $this->db->where('kd_barang', $kdBarang)
            ->where('gudang_id', (string)$gudangId)
            ->where('no_lot', self::ADJ_LOT)
            ->where('expired_date', self::ADJ_EXPIRED)
            ->get('tberp_stock_batch')
            ->row_array();

        if ($row) {
            $this->db->where('id', (int)$row['id']);
            $this->db->set('qty_on_hand', 'qty_on_hand + ' . (float)$qtyDelta, false);
            $this->db->set('update_at', date('Y-m-d H:i:s'));
            $this->db->update('tberp_stock_batch');
            return;
        }

        $this->db->insert('tberp_stock_batch', [
            'kd_barang' => $kdBarang,
            'gudang_id' => (string)$gudangId,
            'no_lot' => self::ADJ_LOT,
            'expired_date' => self::ADJ_EXPIRED,
            'qty_on_hand' => (float)$qtyDelta,
            'qty_reserved' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function stock_snapshot($gudangId, $lines)
    {
        $snapshot = [];
        foreach ($lines as $line) {
            $key = $line['kd_barang'];
            $row = $this->db->where('kd_barang', $line['kd_barang'])
                ->where('gudang_id', (string)$gudangId)
                ->where('no_lot', self::ADJ_LOT)
                ->where('expired_date', self::ADJ_EXPIRED)
                ->get('tberp_stock_batch')
                ->row_array();
            $snapshot[$key] = $row ? (float)$row['qty_on_hand'] : 0.0;
        }
        return $snapshot;
    }

    private function stock_matches_snapshot($gudangId, $lines, $snapshot)
    {
        foreach ($lines as $line) {
            $row = $this->db->where('kd_barang', $line['kd_barang'])
                ->where('gudang_id', (string)$gudangId)
                ->where('no_lot', self::ADJ_LOT)
                ->where('expired_date', self::ADJ_EXPIRED)
                ->get('tberp_stock_batch')
                ->row_array();
            $current = $row ? (float)$row['qty_on_hand'] : 0.0;
            $before = (float)($snapshot[$line['kd_barang']] ?? 0);
            if (abs($current - $before) > 0.001) {
                return false;
            }
        }
        return true;
    }

    private function lpb_header($idLpb)
    {
        $sql = "SELECT l.*, p.kd_suplier, COALESCE(s.id_suplier, 0) AS id_suplier, COALESCE(s.nama_suplier, '') AS nama_suplier
                FROM tb_lpb l
                LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
                LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
                WHERE l.id_lpb = ?
                LIMIT 1";
        return $this->db->query($sql, [(int)$idLpb])->row_array();
    }

    private function totals($lines)
    {
        $totals = [
            'qty' => 0,
            'dpp_salah' => 0,
            'dpp_benar' => 0,
            'ppn_salah' => 0,
            'ppn_benar' => 0,
            'total_salah' => 0,
            'total_benar' => 0,
        ];
        foreach ($lines as $line) {
            foreach ($totals as $key => $value) {
                if (isset($line[$key])) {
                    $totals[$key] += (float)$line[$key];
                }
            }
        }
        return $totals;
    }

    private function generate_adjustment_number()
    {
        $prefix = 'ADJLPB-' . date('Ymd') . '-';
        $row = $this->db->query(
            "SELECT no_adjustment FROM tb_lpb_price_adjustment WHERE no_adjustment LIKE ? ORDER BY no_adjustment DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        )->row_array();
        $next = $row ? ((int)substr($row['no_adjustment'], -4) + 1) : 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    private function generate_prpp_number()
    {
        $prefix = 'PRPP-' . date('Ymd') . '-';
        $row = $this->db->query(
            "SELECT no_retur_pembelian FROM tb_retur_pembelian WHERE no_retur_pembelian LIKE ? ORDER BY no_retur_pembelian DESC LIMIT 1 FOR UPDATE",
            [$prefix . '%']
        )->row_array();
        $next = $row ? ((int)substr($row['no_retur_pembelian'], -4) + 1) : 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    private function generate_lpb_adjustment_number($nomorLpb)
    {
        $base = trim((string)$nomorLpb);
        $candidate = substr($base . 'A', 0, 30);
        $exists = $this->db->where('nomor_lpb', $candidate)->count_all_results('tb_lpb');
        if ((int)$exists === 0) {
            return $candidate;
        }

        for ($i = 2; $i <= 99; $i++) {
            $suffix = 'A' . $i;
            $candidate = substr($base, 0, 30 - strlen($suffix)) . $suffix;
            $exists = $this->db->where('nomor_lpb', $candidate)->count_all_results('tb_lpb');
            if ((int)$exists === 0) {
                return $candidate;
            }
        }

        return substr($base, 0, 20) . 'A' . date('His');
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

    private function normalize_date($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }
        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        return '';
    }

    private function to_float($value)
    {
        if (is_string($value)) {
            $value = trim($value);
            if (strpos($value, ',') !== false) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        }
        return (float)$value;
    }

    private function money($value)
    {
        return number_format((float)$value, 4, '.', '');
    }

    private function money2($value)
    {
        return number_format((float)$value, 2, '.', '');
    }

    private function ok($message, $data = [])
    {
        return ['success' => true, 'message' => $message, 'data' => $data, 'errors' => []];
    }

    private function fail($message, $errors = [], $data = [])
    {
        return ['success' => false, 'message' => $message, 'data' => $data, 'errors' => (array)$errors];
    }
}
