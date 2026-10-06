-- ==============================================================================
-- SKRIP RESET DATABASE TRANSAKSI KARISMA ERP (CLEAN SLATE / KOSONGAN)
-- Tanggal: 06 Oktober 2026
-- Tujuan : Mengosongkan seluruh data transaksi (Jurnal, Penjualan, Pembelian,
--          PO, LPB, Retur, Pembayaran, Konsinyasi, dan Stok Ledger) agar sistem
--          dapat diuji coba dari awal transaksi kosongan.
-- CATATAN: Seluruh Master Data (Barang, Akun COA, Customer, Supplier, Gudang,
--          User, Mapping Akun, Setting) TETAP AMAN DAN TIDAK DIHAPUS.
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. MODUL KEUANGAN & GENERAL LEDGER (GL)
TRUNCATE TABLE `tbkeu_jurnal_detail`;
TRUNCATE TABLE `tbkeu_jurnal`;
TRUNCATE TABLE `tbkeu_jurnal_log`;
TRUNCATE TABLE `tbkeu_posting_exception`;
TRUNCATE TABLE `tbkeu_nomor_dokumen`;
TRUNCATE TABLE `tbkeu_penyesuaian_barang_detail`;
TRUNCATE TABLE `tbkeu_penyesuaian_barang`;
TRUNCATE TABLE `tbkeu_kas_masuk_detail`;
TRUNCATE TABLE `tbkeu_kas_masuk`;
TRUNCATE TABLE `tbkeu_kas_keluar_detail`;
TRUNCATE TABLE `tbkeu_kas_keluar`;
TRUNCATE TABLE `tbkeu_kasir_saldo`;
TRUNCATE TABLE `tbkeu_transaksi_kasir`;
TRUNCATE TABLE `tbkeu_saldo_awal_akun`;

-- 2. MODUL TUTUP BUKU & CUT-OFF (RESET RIWAYAT CLOSING)
TRUNCATE TABLE `tbkeu_closing_balance_account`;
TRUNCATE TABLE `tbkeu_closing_balance_inventory`;
TRUNCATE TABLE `tbkeu_closing_balance_ar`;
TRUNCATE TABLE `tbkeu_closing_balance_ap`;
TRUNCATE TABLE `tbkeu_closing_validation_log`;
TRUNCATE TABLE `tbkeu_closing_validation_run`;
TRUNCATE TABLE `tbkeu_reopen_request`;
TRUNCATE TABLE `tbkeu_closing_period`;

-- Kembalikan status periode fiskal ke OPEN
UPDATE `tbkeu_periode_fiskal` 
SET `status` = 'OPEN', 
    `closed_by` = NULL, 
    `closed_at` = NULL, 
    `reopened_by` = NULL, 
    `reopened_at` = NULL;

-- 3. MODUL PEMBAYARAN (AR & AP)
TRUNCATE TABLE `tbkeu_pembayaran_alokasi`;
TRUNCATE TABLE `tbkeu_pembayaran_faktur`;
TRUNCATE TABLE `tbkeu_pembayaran`;

-- 4. MODUL PENJUALAN (SALES ORDER, FAKTUR, DO, TRIP)
TRUNCATE TABLE `tbso_sales_order_detail`;
TRUNCATE TABLE `tbso_sales_order`;
TRUNCATE TABLE `tbso_so_approval`;
TRUNCATE TABLE `tbso_approval_harga`;
TRUNCATE TABLE `tbso_faktur_detail`;
TRUNCATE TABLE `tbso_faktur_penjualan`;
TRUNCATE TABLE `tbso_faktur_jurnal`;
TRUNCATE TABLE `tbso_faktur_log`;
TRUNCATE TABLE `tbso_activity_log`;
TRUNCATE TABLE `tbso_faktur_z_pecah_detail`;
TRUNCATE TABLE `tbso_faktur_z_pecah`;
TRUNCATE TABLE `tbso_stock_reservation`;
TRUNCATE TABLE `tbso_cancel_partial_request`;
TRUNCATE TABLE `tb_detail_do`;
TRUNCATE TABLE `tb_do`;
TRUNCATE TABLE `tb_log_do`;
TRUNCATE TABLE `tb_pre_do`;
TRUNCATE TABLE `trashbin_do`;
TRUNCATE TABLE `tb_delivery_trip`;
TRUNCATE TABLE `tb_loading_kk`;
TRUNCATE TABLE `tb_loading_lk`;
TRUNCATE TABLE `tb_log_confirm_sales`;

-- 5. MODUL RETUR PENJUALAN
TRUNCATE TABLE `tbrp_retur_penjualan_detail`;
TRUNCATE TABLE `tbrp_retur_penjualan_header`;
TRUNCATE TABLE `tbrp_spr_detail`;
TRUNCATE TABLE `tbrp_spr_header`;
TRUNCATE TABLE `tbrp_activity_log`;
TRUNCATE TABLE `tb_retur_barang`;
TRUNCATE TABLE `tb_detail_retur_barang`;

-- 6. MODUL PEMBELIAN & LOGISTIK (PO, LPB)
TRUNCATE TABLE `tbpo_detail_po`;
TRUNCATE TABLE `tbpo_po`;
TRUNCATE TABLE `tbpo_detail_po_nk`;
TRUNCATE TABLE `tbpo_po_nk`;
TRUNCATE TABLE `tbpo_tracking_po`;
TRUNCATE TABLE `tbpo_transaksi`;
TRUNCATE TABLE `tbpo_transaksi_tmp`;
TRUNCATE TABLE `tbpo_transaksi_trashbin`;
TRUNCATE TABLE `tb_lpb_detail`;
TRUNCATE TABLE `tb_lpb`;
TRUNCATE TABLE `tb_lpb_batch`;
TRUNCATE TABLE `tb_lpb_log`;
TRUNCATE TABLE `tb_lpb_manual_log`;
TRUNCATE TABLE `tb_lpb_price_adjustment_detail`;
TRUNCATE TABLE `tb_lpb_price_adjustment`;
TRUNCATE TABLE `tb_lpb_revision_request_detail`;
TRUNCATE TABLE `tb_lpb_revision_request_log`;
TRUNCATE TABLE `tb_lpb_revision_request`;
TRUNCATE TABLE `tblpb_faktur_pajak`;

-- 7. MODUL RETUR PEMBELIAN
TRUNCATE TABLE `tb_retur_pembelian_detail`;
TRUNCATE TABLE `tb_retur_pembelian`;
TRUNCATE TABLE `tb_retur_pembelian_log`;

-- 8. MODUL KONSINYASI
TRUNCATE TABLE `tb_konsinyasi_settlement`;
TRUNCATE TABLE `tb_konsinyasi_faktur`;
TRUNCATE TABLE `tb_konsinyasi_masuk_detail`;
TRUNCATE TABLE `tb_konsinyasi_masuk`;

-- 9. MODUL PERSEDIAAN & STOK TRANSAKSIONAL
TRUNCATE TABLE `tberp_stock_ledger`;
TRUNCATE TABLE `tberp_stock_batch`;
TRUNCATE TABLE `tberp_bundling_request_detail`;
TRUNCATE TABLE `tberp_bundling_request`;
TRUNCATE TABLE `tberp_bundling_assembly_detail`;
TRUNCATE TABLE `tberp_bundling_assembly`;
TRUNCATE TABLE `tberp_bundling_disassembly_detail`;
TRUNCATE TABLE `tberp_bundling_disassembly`;
TRUNCATE TABLE `tb_mutasi`;
TRUNCATE TABLE `tb_detail_mutasi`;
TRUNCATE TABLE `tb_log_mutasi`;
TRUNCATE TABLE `tb_dailystock`;
TRUNCATE TABLE `tb_dailystock_global`;

SET FOREIGN_KEY_CHECKS = 1;
