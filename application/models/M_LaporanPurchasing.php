<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_LaporanPurchasing
 * Mengelola data laporan digital purchasing & LPB komprehensif
 * Berelasi dengan tb_lpb, tb_lpb_detail, tb_lpb_batch, tbpo_po, tbpo_suplier/tbpo_supplier, tbpo_barang, dan tblpb_faktur_pajak
 */
class M_LaporanPurchasing extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Memastikan kolom-kolom pendukung dan tabel tblpb_faktur_pajak tersedia secara fleksibel
     */
    public function ensure_schema()
    {
        // 1. tbpo_barang schema
        if ($this->db->table_exists('tbpo_barang')) {
            $colsBarang = [
                'produsen'         => "ALTER TABLE `tbpo_barang` ADD COLUMN `produsen` VARCHAR(150) NULL AFTER `nama_barang`",
                'spesifikasi_merk' => "ALTER TABLE `tbpo_barang` ADD COLUMN `spesifikasi_merk` VARCHAR(255) NULL AFTER `produsen`",
                'golongan'         => "ALTER TABLE `tbpo_barang` ADD COLUMN `golongan` VARCHAR(100) NULL AFTER `spesifikasi_merk`",
                'kelompok'         => "ALTER TABLE `tbpo_barang` ADD COLUMN `kelompok` VARCHAR(100) NULL AFTER `golongan`",
                'komposisi'        => "ALTER TABLE `tbpo_barang` ADD COLUMN `komposisi` TEXT NULL AFTER `kelompok`",
                'grup'             => "ALTER TABLE `tbpo_barang` ADD COLUMN `grup` VARCHAR(100) NULL AFTER `komposisi`",
            ];
            foreach ($colsBarang as $col => $sql) {
                if (!$this->db->field_exists($col, 'tbpo_barang')) {
                    @$this->db->query($sql);
                }
            }
        }

        // 2. tbpo_po schema
        if ($this->db->table_exists('tbpo_po')) {
            if (!$this->db->field_exists('tgl_perubahan_po', 'tbpo_po')) {
                @$this->db->query("ALTER TABLE `tbpo_po` ADD COLUMN `tgl_perubahan_po` DATE NULL AFTER `tgl_po`");
            }
            if (!$this->db->field_exists('top', 'tbpo_po')) {
                @$this->db->query("ALTER TABLE `tbpo_po` ADD COLUMN `top` INT DEFAULT 0 COMMENT 'Term of Payment' AFTER `tgl_perubahan_po`");
            }
        }

        // 3. tb_lpb schema
        if ($this->db->table_exists('tb_lpb')) {
            if (!$this->db->field_exists('tgl_perubahan_invoice', 'tb_lpb')) {
                @$this->db->query("ALTER TABLE `tb_lpb` ADD COLUMN `tgl_perubahan_invoice` DATE NULL AFTER `tanggal_invoice`");
            }
            if (!$this->db->field_exists('tgl_riil_invoice', 'tb_lpb')) {
                @$this->db->query("ALTER TABLE `tb_lpb` ADD COLUMN `tgl_riil_invoice` DATE NULL AFTER `tgl_perubahan_invoice`");
            }
        }

        // 4. tb_lpb_detail schema
        if ($this->db->table_exists('tb_lpb_detail')) {
            $colsDetail = [
                'sales_disc'     => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `sales_disc` DECIMAL(15,2) DEFAULT 0.00 AFTER `total_harga`",
                'cbd'            => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `cbd` DECIMAL(15,2) DEFAULT 0.00 AFTER `sales_disc`",
                'foc'            => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `foc` DECIMAL(15,2) DEFAULT 0.00 AFTER `cbd`",
                'insentif_cn'    => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `insentif_cn` DECIMAL(15,2) DEFAULT 0.00 AFTER `foc`",
                'dpp'            => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `dpp` DECIMAL(15,2) DEFAULT 0.00 AFTER `insentif_cn`",
                'ppn_11'         => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `ppn_11` DECIMAL(15,2) DEFAULT 0.00 AFTER `dpp`",
                'ppn_12'         => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `ppn_12` DECIMAL(15,2) DEFAULT 0.00 AFTER `ppn_11`",
                'dpp_nilai_lain' => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `dpp_nilai_lain` DECIMAL(15,2) DEFAULT 0.00 AFTER `ppn_12`",
            ];
            foreach ($colsDetail as $col => $sql) {
                if (!$this->db->field_exists($col, 'tb_lpb_detail')) {
                    @$this->db->query($sql);
                }
            }
        }

        // 5. tblpb_faktur_pajak creation
        if (!$this->db->table_exists('tblpb_faktur_pajak')) {
            @$this->db->query("CREATE TABLE `tblpb_faktur_pajak` (
              `id_faktur_pajak` INT(11) NOT NULL AUTO_INCREMENT,
              `id_lpb` INT(11) NOT NULL,
              `no_seri_fp` VARCHAR(50) DEFAULT NULL,
              `tgl_fp` DATE DEFAULT NULL,
              `tgl_terima_fp` DATE DEFAULT NULL,
              `tgl_input_fp` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `lapor_spt_masa` VARCHAR(50) DEFAULT NULL,
              PRIMARY KEY (`id_faktur_pajak`),
              KEY `idx_fp_lpb` (`id_lpb`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    /**
     * Membangun Base Query SQL untuk Laporan Digital Purchasing & LPB dengan Safe Field Inspection
     */
    private function build_base_query(array $filters = [])
    {
        $hasBatchTable     = $this->db->table_exists('tb_lpb_batch');
        $hasSupplierTable  = $this->db->table_exists('tbpo_suplier') ? 'tbpo_suplier' : ($this->db->table_exists('tbpo_supplier') ? 'tbpo_supplier' : '');
        $hasFpTable        = $this->db->table_exists('tblpb_faktur_pajak');
        $hasPoTable        = $this->db->table_exists('tbpo_po');
        $hasBarangTable    = $this->db->table_exists('tbpo_barang');

        // Field Inspections in tb_lpb
        $hasSourceType       = $this->db->field_exists('source_type', 'tb_lpb');
        $hasNomorLpb        = $this->db->field_exists('nomor_lpb', 'tb_lpb');
        $hasJenisLpb        = $this->db->field_exists('jenis_lpb', 'tb_lpb');
        $hasTglPerubahanInv  = $this->db->field_exists('tgl_perubahan_invoice', 'tb_lpb');
        $hasTglRiilInv       = $this->db->field_exists('tgl_riil_invoice', 'tb_lpb');

        // Field Inspections in tbpo_po
        $hasPoTglPerubahan   = $hasPoTable && $this->db->field_exists('tgl_perubahan_po', 'tbpo_po');
        $hasPoTop            = $hasPoTable && $this->db->field_exists('top', 'tbpo_po');
        $hasPoTgl            = $hasPoTable && ($this->db->field_exists('tgl_po', 'tbpo_po') || $this->db->field_exists('tgl_transaksi', 'tbpo_po'));

        // Field Inspections in tb_lpb_detail
        $hasDetailSalesDisc  = $this->db->field_exists('sales_disc', 'tb_lpb_detail');
        $hasDetailCbd        = $this->db->field_exists('cbd', 'tb_lpb_detail');
        $hasDetailFoc        = $this->db->field_exists('foc', 'tb_lpb_detail');
        $hasDetailInsentifCn = $this->db->field_exists('insentif_cn', 'tb_lpb_detail');
        $hasDetailDpp        = $this->db->field_exists('dpp', 'tb_lpb_detail');
        $hasDetailPpn11      = $this->db->field_exists('ppn_11', 'tb_lpb_detail');
        $hasDetailPpn12      = $this->db->field_exists('ppn_12', 'tb_lpb_detail');
        $hasDetailDppLain    = $this->db->field_exists('dpp_nilai_lain', 'tb_lpb_detail');

        // Field Inspections in tbpo_barang
        $hasBrgProdusen      = $hasBarangTable && $this->db->field_exists('produsen', 'tbpo_barang');
        $hasBrgSpesifikasi   = $hasBarangTable && $this->db->field_exists('spesifikasi_merk', 'tbpo_barang');
        $hasBrgGolongan      = $hasBarangTable && $this->db->field_exists('golongan', 'tbpo_barang');
        $hasBrgKelompok      = $hasBarangTable && $this->db->field_exists('kelompok', 'tbpo_barang');
        $hasBrgKomposisi     = $hasBarangTable && $this->db->field_exists('komposisi', 'tbpo_barang');
        $hasBrgGrup          = $hasBarangTable && $this->db->field_exists('grup', 'tbpo_barang');

        // Select Clauses
        $nomorLpbExpr        = $hasNomorLpb ? "COALESCE(NULLIF(TRIM(h.nomor_lpb), ''), '-')" : "'-'";
        $jenisLpbExpr        = $hasJenisLpb ? "COALESCE(NULLIF(TRIM(h.jenis_lpb), ''), 'LOGISTIK')" : "'LOGISTIK'";
        $sourceTypeExpr      = $hasSourceType ? "COALESCE(NULLIF(TRIM(h.source_type), ''), 'PO')" : "'PO'";
        $tglPerubahanInvExpr = $hasTglPerubahanInv ? "h.tgl_perubahan_invoice" : "NULL";
        $tglRiilInvExpr      = $hasTglRiilInv ? "h.tgl_riil_invoice" : "NULL";

        $poTglExpr           = $hasPoTable && $this->db->field_exists('tgl_po', 'tbpo_po') 
                                ? "po.tgl_po" 
                                : ($hasPoTable && $this->db->field_exists('tgl_transaksi', 'tbpo_po') ? "po.tgl_transaksi" : "NULL");
        $poTglPerubahanExpr  = $hasPoTglPerubahan ? "po.tgl_perubahan_po" : "NULL";
        $poTopExpr           = $hasPoTop ? "COALESCE(po.top, 0)" : "0";

        // Batch & Lot Inspections
        if ($hasBatchTable) {
            $hasBatchNoLot       = $this->db->field_exists('no_lot', 'tb_lpb_batch');
            $hasBatchNoBatch     = $this->db->field_exists('no_batch', 'tb_lpb_batch');
            $hasBatchExpDate     = $this->db->field_exists('expired_date', 'tb_lpb_batch');
            $hasBatchExpDateShort= $this->db->field_exists('exp_date', 'tb_lpb_batch');

            $batchNoExpr  = $hasBatchNoLot 
                ? "COALESCE(NULLIF(TRIM(b.no_lot), ''), NULLIF(TRIM(d.no_lot), ''), '-')" 
                : ($hasBatchNoBatch ? "COALESCE(NULLIF(TRIM(b.no_batch), ''), NULLIF(TRIM(d.no_lot), ''), '-')" : "COALESCE(NULLIF(TRIM(d.no_lot), ''), '-')");
            
            $batchExpExpr = $hasBatchExpDate 
                ? "COALESCE(NULLIF(TRIM(b.expired_date), ''), NULLIF(TRIM(d.expired_date), ''), '-')" 
                : ($hasBatchExpDateShort ? "COALESCE(NULLIF(TRIM(b.exp_date), ''), NULLIF(TRIM(d.expired_date), ''), '-')" : "COALESCE(NULLIF(TRIM(d.expired_date), ''), '-')");

            $batchSelect = "{$batchNoExpr} AS no_batch, {$batchExpExpr} AS exp_date,";
            $batchJoin   = "LEFT JOIN tb_lpb_batch b ON b.id_detail_lpb = d.id_detail_lpb";
        } else {
            $batchSelect = "COALESCE(NULLIF(TRIM(d.no_lot), ''), '-') AS no_batch, COALESCE(NULLIF(TRIM(d.expired_date), ''), '-') AS exp_date,";
            $batchJoin   = "";
        }

        // Supplier Join & Select
        $supplierJoin = "";
        $supplierSelect = "'-' AS kd_supplier, '-' AS nama_supplier,";

        if ($hasSupplierTable === 'tbpo_suplier') {
            $hasPoKdSuplier = $hasPoTable && $this->db->field_exists('kd_suplier', 'tbpo_po');
            $hasPoKdSupplier = $hasPoTable && $this->db->field_exists('kd_supplier', 'tbpo_po');

            if ($hasPoKdSuplier) {
                $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_suplier), ''), NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_suplier s ON s.kd_suplier = po.kd_suplier";
            } elseif ($hasPoKdSupplier) {
                $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_supplier), ''), NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_suplier s ON s.kd_suplier = po.kd_supplier";
            } else {
                $supplierSelect = "COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_suplier s ON 1=0";
            }
        } elseif ($hasSupplierTable === 'tbpo_supplier') {
            $hasPoKdSupplier = $hasPoTable && $this->db->field_exists('kd_supplier', 'tbpo_po');
            if ($hasPoKdSupplier) {
                $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_supplier), ''), NULLIF(TRIM(s.kd_supplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_supplier), ''), '-') AS nama_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_supplier s ON s.kd_supplier = po.kd_supplier";
            } else {
                $supplierSelect = "COALESCE(NULLIF(TRIM(s.kd_supplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_supplier), ''), '-') AS nama_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_supplier s ON 1=0";
            }
        }

        // Barang Selects
        $produsenExpr    = $hasBrgProdusen ? "COALESCE(NULLIF(TRIM(brg.produsen), ''), '-')" : "'-'";
        $spesifikasiExpr = $hasBrgSpesifikasi ? "COALESCE(NULLIF(TRIM(brg.spesifikasi_merk), ''), '-')" : "'-'";
        $golonganExpr    = $hasBrgGolongan ? "COALESCE(NULLIF(TRIM(brg.golongan), ''), '-')" : "'-'";
        $kelompokExpr    = $hasBrgKelompok ? "COALESCE(NULLIF(TRIM(brg.kelompok), ''), '-')" : "'-'";
        $komposisiExpr   = $hasBrgKomposisi ? "COALESCE(NULLIF(TRIM(brg.komposisi), ''), '-')" : "'-'";
        $grupExpr        = $hasBrgGrup ? "COALESCE(NULLIF(TRIM(brg.grup), ''), '-')" : "'-'";

        // Detail Financials
        $salesDiscExpr   = $hasDetailSalesDisc ? "COALESCE(d.sales_disc, 0)" : "0";
        $cbdExpr         = $hasDetailCbd ? "COALESCE(d.cbd, 0)" : "0";
        $focExpr         = $hasDetailFoc ? "COALESCE(d.foc, 0)" : "0";
        $insentifCnExpr  = $hasDetailInsentifCn ? "COALESCE(d.insentif_cn, 0)" : "0";
        $dppExpr         = $hasDetailDpp ? "COALESCE(d.dpp, 0)" : "0";
        $ppn11Expr       = $hasDetailPpn11 ? "COALESCE(d.ppn_11, 0)" : "0";
        $ppn12Expr       = $hasDetailPpn12 ? "COALESCE(d.ppn_12, 0)" : "0";
        $dppLainExpr     = $hasDetailDppLain ? "COALESCE(d.dpp_nilai_lain, 0)" : "0";

        // Faktur Pajak Join
        $fpJoin = $hasFpTable ? "LEFT JOIN tblpb_faktur_pajak fp ON fp.id_lpb = h.id_lpb" : "";
        $fpSelect = $hasFpTable 
            ? "fp.no_seri_fp, fp.tgl_fp, fp.tgl_terima_fp, fp.tgl_input_fp, fp.lapor_spt_masa,"
            : "NULL AS no_seri_fp, NULL AS tgl_fp, NULL AS tgl_terima_fp, NULL AS tgl_input_fp, NULL AS lapor_spt_masa,";

        $fpTerimaExpr = $hasFpTable ? "fp.tgl_terima_fp" : "NULL";

        // PO Join
        $poJoin = $hasPoTable ? "LEFT JOIN tbpo_po po ON po.kd_po = h.kd_po OR po.no_po = h.no_po" : "";

        $sql = "SELECT 
                    h.id_lpb,
                    h.input_at,
                    h.tgl_sj AS tgl_lpb,
                    h.kd_po,
                    h.no_po,
                    {$nomorLpbExpr} AS nomor_lpb,
                    {$jenisLpbExpr} AS jenis_lpb,
                    {$sourceTypeExpr} AS source_type,
                    h.nosj,
                    h.no_invoice,
                    h.tanggal_invoice,
                    {$tglPerubahanInvExpr} AS tgl_perubahan_invoice,
                    {$tglRiilInvExpr} AS tgl_riil_invoice,
                    {$poTglExpr} AS tgl_po,
                    {$poTglPerubahanExpr} AS tgl_perubahan_po,
                    {$poTopExpr} AS top_days,
                    {$supplierSelect}
                    d.id_detail_lpb,
                    d.kd_barang,
                    COALESCE(NULLIF(TRIM(brg.nama_barang), ''), d.kd_barang) AS nama_barang,
                    {$produsenExpr} AS produsen,
                    {$spesifikasiExpr} AS spesifikasi_merk,
                    {$golonganExpr} AS golongan,
                    {$kelompokExpr} AS kelompok,
                    {$komposisiExpr} AS komposisi,
                    {$grupExpr} AS grup,
                    {$batchSelect}
                    COALESCE(d.qty_diterima, 0) AS qty_diterima,
                    COALESCE(d.harga_satuan, 0) AS harga_satuan,
                    COALESCE(d.total_harga, 0) AS total_harga,
                    {$salesDiscExpr} AS sales_disc,
                    {$cbdExpr} AS cbd,
                    {$focExpr} AS foc,
                    {$insentifCnExpr} AS insentif_cn,
                    {$dppExpr} AS dpp,
                    {$ppn11Expr} AS ppn_11,
                    {$ppn12Expr} AS ppn_12,
                    {$dppLainExpr} AS dpp_nilai_lain,
                    {$fpSelect}
                    -- Lead Time PO ke LPB (Hari)
                    CASE 
                        WHEN {$poTglExpr} IS NOT NULL AND h.tgl_sj IS NOT NULL THEN DATEDIFF(h.tgl_sj, {$poTglExpr})
                        ELSE NULL 
                    END AS lead_time_po_lpb,
                    -- Lead Time FP ke Hari Ini (Hari)
                    CASE 
                        WHEN {$fpTerimaExpr} IS NOT NULL THEN DATEDIFF(CURRENT_DATE, {$fpTerimaExpr})
                        ELSE NULL 
                    END AS lead_time_fp_today,
                    -- Aging Penerimaan Faktur Pajak
                    CASE 
                        WHEN {$fpTerimaExpr} IS NULL THEN 'Belum Diterima'
                        WHEN DATEDIFF(CURRENT_DATE, {$fpTerimaExpr}) BETWEEN 0 AND 15 THEN '0 - 15 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, {$fpTerimaExpr}) BETWEEN 16 AND 30 THEN '16 - 30 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, {$fpTerimaExpr}) BETWEEN 31 AND 45 THEN '31 - 45 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, {$fpTerimaExpr}) BETWEEN 46 AND 60 THEN '46 - 60 Hari'
                        ELSE '> 60 Hari'
                    END AS aging_fp_category,
                    -- Aging Invoice
                    CASE 
                        WHEN COALESCE({$tglRiilInvExpr}, h.tanggal_invoice) IS NULL THEN 'Belum Diterima'
                        WHEN DATEDIFF(CURRENT_DATE, COALESCE({$tglRiilInvExpr}, h.tanggal_invoice)) BETWEEN 0 AND 15 THEN '0 - 15 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, COALESCE({$tglRiilInvExpr}, h.tanggal_invoice)) BETWEEN 16 AND 30 THEN '16 - 30 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, COALESCE({$tglRiilInvExpr}, h.tanggal_invoice)) BETWEEN 31 AND 45 THEN '31 - 45 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, COALESCE({$tglRiilInvExpr}, h.tanggal_invoice)) BETWEEN 46 AND 60 THEN '46 - 60 Hari'
                        ELSE '> 60 Hari'
                    END AS aging_invoice_category
                FROM tb_lpb h
                LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
                {$poJoin}
                {$supplierJoin}
                LEFT JOIN tbpo_barang brg ON brg.kode_barang = d.kd_barang
                {$batchJoin}
                {$fpJoin}";

        return $sql;
    }

    /**
     * Memproses filter kustom pada SQL Query
     */
    private function apply_filters($sql, array $filters = [], &$params = [])
    {
        $where = [" 1=1 "];

        if (!empty($filters['date1'])) {
            $where[] = "DATE(h.tgl_sj) >= ?";
            $params[] = $filters['date1'];
        }
        if (!empty($filters['date2'])) {
            $where[] = "DATE(h.tgl_sj) <= ?";
            $params[] = $filters['date2'];
        }
        if (!empty($filters['source']) && $filters['source'] !== 'all') {
            $hasSourceType = $this->db->field_exists('source_type', 'tb_lpb');
            if ($filters['source'] === 'manual') {
                if ($hasSourceType) {
                    $where[] = "h.source_type = 'MANUAL'";
                } else {
                    $where[] = "1=0";
                }
            } elseif ($filters['source'] === 'logistik') {
                if ($hasSourceType) {
                    $where[] = "(h.source_type IS NULL OR h.source_type <> 'MANUAL')";
                }
            }
        }
        if (!empty($filters['aging_fp']) && $filters['aging_fp'] !== 'all') {
            $hasFpTable = $this->db->table_exists('tblpb_faktur_pajak');
            if (!$hasFpTable || $filters['aging_fp'] === 'belum') {
                $where[] = ($hasFpTable ? "fp.tgl_terima_fp IS NULL" : "1=1");
            } else {
                $where[] = "CASE 
                    WHEN fp.tgl_terima_fp IS NULL THEN 'Belum Diterima'
                    WHEN DATEDIFF(CURRENT_DATE, fp.tgl_terima_fp) BETWEEN 0 AND 15 THEN '0 - 15 Hari'
                    WHEN DATEDIFF(CURRENT_DATE, fp.tgl_terima_fp) BETWEEN 16 AND 30 THEN '16 - 30 Hari'
                    WHEN DATEDIFF(CURRENT_DATE, fp.tgl_terima_fp) BETWEEN 31 AND 45 THEN '31 - 45 Hari'
                    WHEN DATEDIFF(CURRENT_DATE, fp.tgl_terima_fp) BETWEEN 46 AND 60 THEN '46 - 60 Hari'
                    ELSE '> 60 Hari'
                END = ?";
                $params[] = $filters['aging_fp'];
            }
        }

        $sql .= " WHERE " . implode(" AND ", $where);
        return $sql;
    }

    /**
     * Mendapatkan total record tanpa limit untuk pagination DataTables
     */
    public function get_total_records(array $filters = [])
    {
        $params = [];
        $baseSql = $this->build_base_query($filters);
        $filteredSql = $this->apply_filters($baseSql, $filters, $params);
        $countSql = "SELECT COUNT(*) as total FROM ({$filteredSql}) AS sub";
        $row = $this->db->query($countSql, $params)->row_array();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Mendapatkan DataTables Server-Side Result
     */
    public function get_datatables_data(array $filters = [], $search = '', $start = 0, $length = 25, $orderCol = 'h.id_lpb', $orderDir = 'DESC')
    {
        $params = [];
        $baseSql = $this->build_base_query($filters);
        $sql = $this->apply_filters($baseSql, $filters, $params);

        if (!empty($search)) {
            $hasFpTable = $this->db->table_exists('tblpb_faktur_pajak');
            $hasNomorLpb = $this->db->field_exists('nomor_lpb', 'tb_lpb');
            $nomorLpbSearch = $hasNomorLpb ? "h.nomor_lpb LIKE ?" : "1=0";
            $fpSearch = $hasFpTable ? "fp.no_seri_fp LIKE ?" : "1=0";

            $sql .= " AND (
                {$nomorLpbSearch} OR 
                h.no_po LIKE ? OR 
                h.nosj LIKE ? OR 
                h.no_invoice LIKE ? OR 
                d.kd_barang LIKE ? OR 
                brg.nama_barang LIKE ? OR 
                {$fpSearch}
            )";
            $searchTerm = '%' . $search . '%';
            if ($hasNomorLpb) $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            if ($hasFpTable) $params[] = $searchTerm;
        }

        // Safe columns for ordering
        $allowedOrderCols = [
            'id_lpb'                => 'h.id_lpb',
            'tgl_po'                => 'tgl_po',
            'no_po'                 => 'h.no_po',
            'tgl_perubahan_po'      => 'po.tgl_perubahan_po',
            'top_days'              => 'po.top',
            'tgl_lpb'               => 'h.tgl_sj',
            'nomor_lpb'             => 'h.nomor_lpb',
            'jenis_lpb'             => 'h.jenis_lpb',
            'source_type'           => 'h.source_type',
            'nosj'                  => 'h.nosj',
            'no_invoice'            => 'h.no_invoice',
            'tanggal_invoice'       => 'h.tanggal_invoice',
            'tgl_perubahan_invoice' => 'h.tgl_perubahan_invoice',
            'tgl_riil_invoice'      => 'h.tgl_riil_invoice',
            'kd_supplier'           => 'kd_supplier',
            'nama_supplier'         => 'nama_supplier',
            'kd_barang'             => 'd.kd_barang',
            'nama_barang'           => 'brg.nama_barang',
            'produsen'              => 'brg.produsen',
            'spesifikasi_merk'      => 'brg.spesifikasi_merk',
            'golongan'              => 'brg.golongan',
            'kelompok'              => 'brg.kelompok',
            'komposisi'             => 'brg.komposisi',
            'grup'                  => 'brg.grup',
            'qty_diterima'          => 'd.qty_diterima',
            'harga_satuan'          => 'd.harga_satuan',
            'total_harga'           => 'd.total_harga',
            'sales_disc'            => 'd.sales_disc',
            'cbd'                   => 'd.cbd',
            'foc'                   => 'd.foc',
            'insentif_cn'            => 'd.insentif_cn',
            'dpp'                   => 'd.dpp',
            'ppn_11'                => 'd.ppn_11',
            'ppn_12'                => 'd.ppn_12',
            'dpp_nilai_lain'        => 'd.dpp_nilai_lain',
            'no_seri_fp'            => 'fp.no_seri_fp',
            'tgl_fp'                => 'fp.tgl_fp',
            'tgl_terima_fp'         => 'fp.tgl_terima_fp',
            'tgl_input_fp'          => 'fp.tgl_input_fp',
            'lead_time_po_lpb'      => 'lead_time_po_lpb',
            'lead_time_fp_today'    => 'lead_time_fp_today'
        ];

        $orderBy = $allowedOrderCols[$orderCol] ?? 'h.id_lpb';
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY {$orderBy} {$orderDir}";

        if ($length > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int) $length;
            $params[] = (int) $start;
        }

        return $this->db->query($sql, $params)->result_array();
    }
}
