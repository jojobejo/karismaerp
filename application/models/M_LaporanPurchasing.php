<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_LaporanPurchasing
 * Mengelola data laporan digital purchasing & LPB komprehensif
 * Berelasi dengan tb_lpb, tb_lpb_detail, tb_lpb_batch, tbpo_po, tbpo_suplier/tbpo_supplier, tbpo_barang, dan tblpb_faktur_pajak
 * 
 * @author KARISMA ERP Development Team
 * @since  2026-08-06 (Updated for Option C Hybrid Reporting)
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
                'bhn_aktif'        => "ALTER TABLE `tbpo_barang` ADD COLUMN `bhn_aktif` TEXT NULL AFTER `grup`",
                'satuan'           => "ALTER TABLE `tbpo_barang` ADD COLUMN `satuan` VARCHAR(50) NULL AFTER `bhn_aktif`",
                'isi'              => "ALTER TABLE `tbpo_barang` ADD COLUMN `isi` DECIMAL(15,2) DEFAULT 0.00 AFTER `satuan`",
                'kemasan'          => "ALTER TABLE `tbpo_barang` ADD COLUMN `kemasan` DECIMAL(15,2) DEFAULT 0.00 AFTER `isi`",
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
            if (!$this->db->field_exists('status_lpb', 'tb_lpb')) {
                @$this->db->query("ALTER TABLE `tb_lpb` ADD COLUMN `status_lpb` TINYINT(1) DEFAULT 1 AFTER `nomor_lpb`");
            }
        }

        // 4. tb_lpb_detail schema
        if ($this->db->table_exists('tb_lpb_detail')) {
            $colsDetail = [
                'harga_satuan_sebelumnya' => "ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_satuan_sebelumnya` DECIMAL(18,4) DEFAULT 0.0000 AFTER `harga_satuan`",
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
        $hasPoDetailTable  = $this->db->table_exists('tbpo_detail_po');

        // Field Inspections in tb_lpb
        $hasSourceType       = $this->db->field_exists('source_type', 'tb_lpb');
        $hasNomorLpb        = $this->db->field_exists('nomor_lpb', 'tb_lpb');
        $hasJenisLpb        = $this->db->field_exists('jenis_lpb', 'tb_lpb');
        $hasStatusLpb       = $this->db->field_exists('status_lpb', 'tb_lpb');
        $hasGudangId        = $this->db->field_exists('gudang_id', 'tb_lpb');
        $hasTglPerubahanInv  = $this->db->field_exists('tgl_perubahan_invoice', 'tb_lpb');
        $hasTglRiilInv       = $this->db->field_exists('tgl_riil_invoice', 'tb_lpb');

        // Field Inspections in tbpo_po
        $hasPoTglPerubahan   = $hasPoTable && $this->db->field_exists('tgl_perubahan_po', 'tbpo_po');
        $hasPoTop            = $hasPoTable && $this->db->field_exists('top', 'tbpo_po');

        // Field Inspections in tb_lpb_detail
        $hasDetailSalesDisc  = $this->db->field_exists('sales_disc', 'tb_lpb_detail');
        $hasDetailCbd        = $this->db->field_exists('cbd', 'tb_lpb_detail');
        $hasDetailFoc        = $this->db->field_exists('foc', 'tb_lpb_detail');
        $hasDetailInsentifCn = $this->db->field_exists('insentif_cn', 'tb_lpb_detail');
        $hasDetailDpp        = $this->db->field_exists('dpp', 'tb_lpb_detail');
        $hasDetailPpn11      = $this->db->field_exists('ppn_11', 'tb_lpb_detail');
        $hasDetailPpn12      = $this->db->field_exists('ppn_12', 'tb_lpb_detail');
        $hasDetailDppLain    = $this->db->field_exists('dpp_nilai_lain', 'tb_lpb_detail');
        $hasDetailHrgSBLM   = $this->db->field_exists('harga_satuan_sebelumnya', 'tb_lpb_detail');

        // Field Inspections in tbpo_barang
        $hasBrgProdusen      = $hasBarangTable && $this->db->field_exists('produsen', 'tbpo_barang');
        $hasBrgSpesifikasi   = $hasBarangTable && $this->db->field_exists('spesifikasi_merk', 'tbpo_barang');
        $hasBrgGolongan      = $hasBarangTable && $this->db->field_exists('golongan', 'tbpo_barang');
        $hasBrgKelompok      = $hasBarangTable && $this->db->field_exists('kelompok', 'tbpo_barang');
        $hasBrgKomposisi     = $hasBarangTable && $this->db->field_exists('komposisi', 'tbpo_barang');
        $hasBrgGrup          = $hasBarangTable && $this->db->field_exists('grup', 'tbpo_barang');
        $hasBrgBhnAktif      = $hasBarangTable && $this->db->field_exists('bhn_aktif', 'tbpo_barang');
        $hasBrgSatuan        = $hasBarangTable && $this->db->field_exists('satuan', 'tbpo_barang');
        $hasBrgIsi           = $hasBarangTable && $this->db->field_exists('isi', 'tbpo_barang');
        $hasBrgKemasan       = $hasBarangTable && $this->db->field_exists('kemasan', 'tbpo_barang');

        // Select Clauses
        $nomorLpbExpr        = $hasNomorLpb ? "COALESCE(NULLIF(TRIM(h.nomor_lpb), ''), '-')" : "'-'";
        $jenisLpbExpr        = $hasJenisLpb ? "COALESCE(NULLIF(TRIM(h.jenis_lpb), ''), 'LOGISTIK')" : "'LOGISTIK'";
        $sourceTypeExpr      = $hasSourceType ? "COALESCE(NULLIF(TRIM(h.source_type), ''), 'PO')" : "'PO'";
        $statusLpbCodeExpr   = $hasStatusLpb ? "COALESCE(h.status_lpb, 1)" : "1";
        $gudangIdExpr        = $hasGudangId ? "COALESCE(h.gudang_id, 0)" : "0";
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
        $supplierSelect = "'-' AS kd_supplier, '-' AS nama_supplier, '-' AS alamat_supplier,";

        if ($hasSupplierTable === 'tbpo_suplier') {
            $hasPoKdSuplier = $hasPoTable && $this->db->field_exists('kd_suplier', 'tbpo_po');
            $hasPoKdSupplier = $hasPoTable && $this->db->field_exists('kd_supplier', 'tbpo_po');
            $hasAlamat = $this->db->field_exists('alamat', 'tbpo_suplier');
            $alamatCol = $hasAlamat ? "COALESCE(NULLIF(TRIM(s.alamat), ''), '-')" : "'-'";

            if ($hasPoKdSuplier) {
                $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_suplier), ''), NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,
                                   {$alamatCol} AS alamat_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_suplier s ON s.kd_suplier = po.kd_suplier";
            } elseif ($hasPoKdSupplier) {
                $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_supplier), ''), NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,
                                   {$alamatCol} AS alamat_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_suplier s ON s.kd_suplier = po.kd_supplier";
            } else {
                $supplierSelect = "COALESCE(NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,
                                   {$alamatCol} AS alamat_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_suplier s ON 1=0";
            }
        } elseif ($hasSupplierTable === 'tbpo_supplier') {
            $hasPoKdSupplier = $hasPoTable && $this->db->field_exists('kd_supplier', 'tbpo_supplier');
            $hasAlamat = $this->db->field_exists('alamat', 'tbpo_supplier');
            $alamatCol = $hasAlamat ? "COALESCE(NULLIF(TRIM(s.alamat), ''), '-')" : "'-'";

            if ($hasPoKdSupplier) {
                $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_supplier), ''), NULLIF(TRIM(s.kd_supplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_supplier), ''), '-') AS nama_supplier,
                                   {$alamatCol} AS alamat_supplier,";
                $supplierJoin = "LEFT JOIN tbpo_supplier s ON s.kd_supplier = po.kd_supplier";
            } else {
                $supplierSelect = "COALESCE(NULLIF(TRIM(s.kd_supplier), ''), '-') AS kd_supplier,
                                   COALESCE(NULLIF(TRIM(s.nama_supplier), ''), '-') AS nama_supplier,
                                   {$alamatCol} AS alamat_supplier,";
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
        $bhnAktifExpr    = $hasBrgBhnAktif ? "COALESCE(NULLIF(TRIM(brg.bhn_aktif), ''), '-')" : "'-'";
        $satuanExpr      = $hasBrgSatuan ? "COALESCE(NULLIF(TRIM(brg.satuan), ''), '-')" : "'-'";
        $isiExpr         = $hasBrgIsi ? "COALESCE(brg.isi, 0)" : "0";
        $kemasanExpr     = $hasBrgKemasan ? "COALESCE(brg.kemasan, 0)" : "0";

        // Detail Financials
        $salesDiscExpr       = $hasDetailSalesDisc ? "COALESCE(d.sales_disc, 0)" : "0";
        $cbdExpr             = $hasDetailCbd ? "COALESCE(d.cbd, 0)" : "0";
        $focExpr             = $hasDetailFoc ? "COALESCE(d.foc, 0)" : "0";
        $insentifCnExpr      = $hasDetailInsentifCn ? "COALESCE(d.insentif_cn, 0)" : "0";
        $dppExpr             = $hasDetailDpp ? "COALESCE(d.dpp, 0)" : "0";
        $ppn11Expr           = $hasDetailPpn11 ? "COALESCE(d.ppn_11, 0)" : "0";
        $ppn12Expr           = $hasDetailPpn12 ? "COALESCE(d.ppn_12, 0)" : "0";
        $dppLainExpr         = $hasDetailDppLain ? "COALESCE(d.dpp_nilai_lain, 0)" : "0";
        $hargaSebelumnyaExpr = $hasDetailHrgSBLM ? "COALESCE(d.harga_satuan_sebelumnya, 0)" : "0";

        // Subquery Price List PO & Jumlah Per Faktur
        $priceListPoExpr = $hasPoDetailTable 
            ? "(SELECT COALESCE(pod.hrg_satuan, 0) FROM tbpo_detail_po pod WHERE (pod.kd_po = h.kd_po OR pod.no_po = h.no_po) AND pod.kd_barang = d.kd_barang LIMIT 1)"
            : "0";

        $jumlahPerFakturExpr = "(SELECT SUM(COALESCE(sub_d.dpp_nilai_lain, 0) + COALESCE(sub_d.ppn_12, 0)) 
                                FROM tb_lpb sub_h 
                                JOIN tb_lpb_detail sub_d ON sub_d.id_lpb = sub_h.id_lpb 
                                WHERE sub_h.no_invoice = h.no_invoice 
                                  AND NULLIF(TRIM(h.no_invoice), '') IS NOT NULL)";

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
                    {$statusLpbCodeExpr} AS status_lpb_code,
                    CASE 
                        WHEN {$statusLpbCodeExpr} = 2 THEN 'POSTED'
                        WHEN {$statusLpbCodeExpr} = 0 THEN 'VOID'
                        ELSE 'UNPOST'
                    END AS status_lpb_text,
                    {$gudangIdExpr} AS gudang_id,
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
                    {$bhnAktifExpr} AS bhn_aktif,
                    {$satuanExpr} AS satuan,
                    {$isiExpr} AS isi,
                    {$kemasanExpr} AS kemasan,
                    {$batchSelect}
                    COALESCE(d.qty_diterima, 0) AS qty_diterima,
                    COALESCE(d.harga_satuan, 0) AS harga_satuan,
                    {$hargaSebelumnyaExpr} AS harga_satuan_sebelumnya,
                    {$priceListPoExpr} AS price_list_po,
                    COALESCE(d.total_harga, 0) AS total_harga,
                    {$salesDiscExpr} AS sales_disc,
                    {$cbdExpr} AS cbd,
                    {$focExpr} AS foc,
                    {$insentifCnExpr} AS insentif_cn,
                    {$dppExpr} AS dpp,
                    {$ppn11Expr} AS ppn_11,
                    {$ppn12Expr} AS ppn_12,
                    {$dppLainExpr} AS dpp_nilai_lain,
                    ({$dppLainExpr} + {$ppn12Expr}) AS jumlah_hutang,
                    COALESCE({$jumlahPerFakturExpr}, ({$dppLainExpr} + {$ppn12Expr})) AS jumlah_per_faktur,
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
     * Memproses filter kustom pada SQL Query (Termasuk Nested Filter)
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
        if (!empty($filters['status_lpb']) && $filters['status_lpb'] !== 'all') {
            $hasStatusLpb = $this->db->field_exists('status_lpb', 'tb_lpb');
            if ($hasStatusLpb) {
                $val = strtolower(trim($filters['status_lpb']));
                if ($val === 'posted' || $val === '2') {
                    $where[] = "h.status_lpb = 2";
                } elseif ($val === 'void' || $val === '0') {
                    $where[] = "h.status_lpb = 0";
                } elseif ($val === 'unpost' || $val === '1') {
                    $where[] = "(h.status_lpb = 1 OR h.status_lpb IS NULL)";
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
        if (!empty($filters['jenis_lpb']) && $filters['jenis_lpb'] !== 'all') {
            $hasJenisLpb = $this->db->field_exists('jenis_lpb', 'tb_lpb');
            if ($hasJenisLpb) {
                $where[] = "UPPER(TRIM(h.jenis_lpb)) = ?";
                $params[] = strtoupper($filters['jenis_lpb']);
            }
        }
        if (!empty($filters['aging_invoice']) && $filters['aging_invoice'] !== 'all') {
            $hasTglRiil = $this->db->field_exists('tgl_riil_invoice', 'tb_lpb');
            $tglInvExpr = $hasTglRiil ? "COALESCE(h.tgl_riil_invoice, h.tanggal_invoice)" : "h.tanggal_invoice";

            if ($filters['aging_invoice'] === 'belum') {
                $where[] = "{$tglInvExpr} IS NULL";
            } else {
                $where[] = "CASE 
                    WHEN {$tglInvExpr} IS NULL THEN 'Belum Diterima'
                    WHEN DATEDIFF(CURRENT_DATE, {$tglInvExpr}) BETWEEN 0 AND 15 THEN '0 - 15 Hari'
                    WHEN DATEDIFF(CURRENT_DATE, {$tglInvExpr}) BETWEEN 16 AND 30 THEN '16 - 30 Hari'
                    WHEN DATEDIFF(CURRENT_DATE, {$tglInvExpr}) BETWEEN 31 AND 45 THEN '31 - 45 Hari'
                    WHEN DATEDIFF(CURRENT_DATE, {$tglInvExpr}) BETWEEN 46 AND 60 THEN '46 - 60 Hari'
                    ELSE '> 60 Hari'
                END = ?";
                $params[] = $filters['aging_invoice'];
            }
        }

        $whereStr = implode(" AND ", $where);
        return "SELECT * FROM ({$sql}) AS main_report WHERE {$whereStr}";
    }

    /**
     * Menghitung total data record laporan
     */
    public function get_total_records(array $filters = [])
    {
        $params = [];
        $baseSql = $this->build_base_query($filters);
        $filteredSql = $this->apply_filters($baseSql, $filters, $params);

        $countSql = "SELECT COUNT(*) as total FROM ({$filteredSql}) AS count_tbl";
        $query = $this->db->query($countSql, $params);
        $row = $query->row();
        return $row ? (int)$row->total : 0;
    }

    /**
     * Memuat data untuk DataTables Server-Side Processing (Laporan Detail LPB)
     */
    public function get_datatables_data(array $filters = [], $search = '', $start = 0, $length = 25, $orderCol = 0, $orderDir = 'asc')
    {
        $params = [];
        $baseSql = $this->build_base_query($filters);
        $filteredSql = $this->apply_filters($baseSql, $filters, $params);

        $searchClause = "";
        if (!empty($search)) {
            $escapedSearch = $this->db->escape_like_str($search);
            $searchClause = " AND (
                nomor_lpb LIKE '%{$escapedSearch}%' OR
                no_po LIKE '%{$escapedSearch}%' OR
                nosj LIKE '%{$escapedSearch}%' OR
                no_invoice LIKE '%{$escapedSearch}%' OR
                kd_supplier LIKE '%{$escapedSearch}%' OR
                nama_supplier LIKE '%{$escapedSearch}%' OR
                kd_barang LIKE '%{$escapedSearch}%' OR
                nama_barang LIKE '%{$escapedSearch}%' OR
                no_seri_fp LIKE '%{$escapedSearch}%'
            )";
        }

        $columns = [
            0 => 'id_lpb',
            1 => 'tgl_po',
            2 => 'no_po',
            3 => 'tgl_lpb',
            4 => 'nomor_lpb',
            5 => 'no_invoice',
            6 => 'nama_supplier',
            7 => 'nama_barang',
            8 => 'qty_diterima',
            9 => 'jumlah_hutang',
            10 => 'no_seri_fp',
            11 => 'lead_time_po_lpb'
        ];

        $sortCol = isset($columns[$orderCol]) ? $columns[$orderCol] : 'id_lpb';
        $sortDir = strtolower($orderDir) === 'desc' ? 'DESC' : 'ASC';

        $finalSql = "SELECT * FROM ({$filteredSql}) AS data_tbl WHERE 1=1 {$searchClause} ORDER BY {$sortCol} {$sortDir} LIMIT {$start}, {$length}";

        $query = $this->db->query($finalSql, $params);
        return $query ? $query->result_array() : [];
    }

    /**
     * Memuat seluruh dataset laporan untuk keperluan Export Excel
     */
    public function get_report_data_for_export(array $filters = [])
    {
        $params = [];
        $baseSql = $this->build_base_query($filters);
        $filteredSql = $this->apply_filters($baseSql, $filters, $params);

        $finalSql = "SELECT * FROM ({$filteredSql}) AS export_tbl ORDER BY id_lpb DESC";
        $query = $this->db->query($finalSql, $params);
        return $query ? $query->result_array() : [];
    }

    /**
     * =========================================================================
     * DASHBOARD SUMMARY HUTANG PURCHASING (AGREGASI PER FAKTUR / INVOICE)
     * =========================================================================
     */

    /**
     * Query Agregasi Hutang Per Invoice / Faktur
     */
    private function build_summary_query(array $filters = [])
    {
        $hasSupplierTable = $this->db->table_exists('tbpo_suplier') ? 'tbpo_suplier' : ($this->db->table_exists('tbpo_supplier') ? 'tbpo_supplier' : '');
        $hasPoTable       = $this->db->table_exists('tbpo_po');
        $hasFpTable       = $this->db->table_exists('tblpb_faktur_pajak');

        $supplierJoin = "";
        $supplierSelect = "'-' AS kd_supplier, '-' AS nama_supplier,";

        if ($hasSupplierTable === 'tbpo_suplier') {
            $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_suplier), ''), NULLIF(TRIM(s.kd_suplier), ''), '-') AS kd_supplier,
                               COALESCE(NULLIF(TRIM(s.nama_suplier), ''), '-') AS nama_supplier,";
            $supplierJoin = "LEFT JOIN tbpo_suplier s ON s.kd_suplier = po.kd_suplier";
        } elseif ($hasSupplierTable === 'tbpo_supplier') {
            $supplierSelect = "COALESCE(NULLIF(TRIM(po.kd_supplier), ''), NULLIF(TRIM(s.kd_supplier), ''), '-') AS kd_supplier,
                               COALESCE(NULLIF(TRIM(s.nama_supplier), ''), '-') AS nama_supplier,";
            $supplierJoin = "LEFT JOIN tbpo_supplier s ON s.kd_supplier = po.kd_supplier";
        }

        $poJoin = $hasPoTable ? "LEFT JOIN tbpo_po po ON po.kd_po = h.kd_po OR po.no_po = h.no_po" : "";
        $fpJoin = $hasFpTable ? "LEFT JOIN tblpb_faktur_pajak fp ON fp.id_lpb = h.id_lpb" : "";

        $fpSelect = $hasFpTable 
            ? "MAX(fp.no_seri_fp) AS no_seri_fp, MAX(fp.tgl_terima_fp) AS tgl_terima_fp,"
            : "NULL AS no_seri_fp, NULL AS tgl_terima_fp,";

        $sql = "SELECT 
                    COALESCE(NULLIF(TRIM(h.no_invoice), ''), 'TANPA INVOICE') AS no_invoice,
                    MAX(h.tanggal_invoice) AS tanggal_invoice,
                    MAX(h.tgl_riil_invoice) AS tgl_riil_invoice,
                    MAX(h.tgl_sj) AS tgl_lpb_max,
                    GROUP_CONCAT(DISTINCT h.nomor_lpb SEPARATOR ', ') AS daftar_lpb,
                    GROUP_CONCAT(DISTINCT h.no_po SEPARATOR ', ') AS daftar_po,
                    GROUP_CONCAT(DISTINCT h.nosj SEPARATOR ', ') AS daftar_sj,
                    {$supplierSelect}
                    COUNT(DISTINCT d.id_detail_lpb) AS total_item,
                    SUM(COALESCE(d.qty_diterima, 0)) AS total_qty,
                    SUM(COALESCE(d.dpp, 0)) AS total_dpp,
                    SUM(COALESCE(d.ppn_11, 0)) AS total_ppn_11,
                    SUM(COALESCE(d.dpp_nilai_lain, 0)) AS total_dpp_nilai_lain,
                    SUM(COALESCE(d.ppn_12, 0)) AS total_ppn_12,
                    SUM(COALESCE(d.dpp_nilai_lain, 0) + COALESCE(d.ppn_12, 0)) AS total_jumlah_hutang,
                    {$fpSelect}
                    MAX(h.status_lpb) AS status_lpb_code,
                    CASE 
                        WHEN MAX(h.status_lpb) = 2 THEN 'POSTED'
                        WHEN MAX(h.status_lpb) = 0 THEN 'VOID'
                        ELSE 'UNPOST'
                    END AS status_lpb_text,
                    -- Aging Invoice
                    CASE 
                        WHEN MAX(COALESCE(h.tgl_riil_invoice, h.tanggal_invoice)) IS NULL THEN 'Belum Diterima'
                        WHEN DATEDIFF(CURRENT_DATE, MAX(COALESCE(h.tgl_riil_invoice, h.tanggal_invoice))) BETWEEN 0 AND 15 THEN '0 - 15 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, MAX(COALESCE(h.tgl_riil_invoice, h.tanggal_invoice))) BETWEEN 16 AND 30 THEN '16 - 30 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, MAX(COALESCE(h.tgl_riil_invoice, h.tanggal_invoice))) BETWEEN 31 AND 45 THEN '31 - 45 Hari'
                        WHEN DATEDIFF(CURRENT_DATE, MAX(COALESCE(h.tgl_riil_invoice, h.tanggal_invoice))) BETWEEN 46 AND 60 THEN '46 - 60 Hari'
                        ELSE '> 60 Hari'
                    END AS aging_invoice_category
                FROM tb_lpb h
                LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
                {$poJoin}
                {$supplierJoin}
                {$fpJoin}
                GROUP BY COALESCE(NULLIF(TRIM(h.no_invoice), ''), 'TANPA INVOICE'), s.kd_suplier";

        return $sql;
    }

    /**
     * Memuat data Summary Hutang Per Faktur untuk DataTables
     */
    public function get_summary_hutang_data(array $filters = [], $search = '', $start = 0, $length = 25, $orderCol = 0, $orderDir = 'desc')
    {
        $baseSql = $this->build_summary_query($filters);

        $searchClause = "";
        if (!empty($search)) {
            $escapedSearch = $this->db->escape_like_str($search);
            $searchClause = " HAVING (
                no_invoice LIKE '%{$escapedSearch}%' OR
                daftar_po LIKE '%{$escapedSearch}%' OR
                daftar_lpb LIKE '%{$escapedSearch}%' OR
                daftar_sj LIKE '%{$escapedSearch}%' OR
                nama_supplier LIKE '%{$escapedSearch}%' OR
                no_seri_fp LIKE '%{$escapedSearch}%'
            )";
        }

        $finalSql = "SELECT * FROM ({$baseSql}) AS sum_tbl WHERE 1=1 {$searchClause} ORDER BY total_jumlah_hutang DESC LIMIT {$start}, {$length}";

        $query = $this->db->query($finalSql);
        return $query ? $query->result_array() : [];
    }

    /**
     * Menghitung total data summary hutang per faktur
     */
    public function get_summary_hutang_total_records(array $filters = [])
    {
        $baseSql = $this->build_summary_query($filters);
        $countSql = "SELECT COUNT(*) as total FROM ({$baseSql}) AS sum_count";
        $query = $this->db->query($countSql);
        $row = $query->row();
        return $row ? (int)$row->total : 0;
    }
}
