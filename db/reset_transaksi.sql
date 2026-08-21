-- ========================================================
-- SCRIPT RESET TRANSAKSI DATABASE KARISMA ERP
-- Menghapus seluruh data transaksi: PO, LPB, Penjualan/DO, 
-- SO, Pembayaran/Kas/Bank, Jurnal, dan Kartu Stok/Mutasi.
-- Master Data (User, Customer, Supplier, Barang, COA, Gudang, dll) TETAP AMAN.
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. TRANSAKSI PURCHASE ORDER (PO) & PEMBELIAN
TRUNCATE TABLE tbpo_po;
TRUNCATE TABLE tbpo_detail_po;
TRUNCATE TABLE tbpo_po_nk;
TRUNCATE TABLE tbpo_detail_po_nk;
TRUNCATE TABLE tbpo_realisasi_po_nk;
TRUNCATE TABLE tbpo_realisasi_detail_po_nk;
TRUNCATE TABLE tbpo_realisasi_harganyata_log;
TRUNCATE TABLE tbpo_req_nk;
TRUNCATE TABLE tbpo_detail_req;
TRUNCATE TABLE tbpo_req_masterbarang;
TRUNCATE TABLE tbpo_transaksi;
TRUNCATE TABLE tbpo_transaksi_tmp;
TRUNCATE TABLE tbpo_transaksi_trashbin;
TRUNCATE TABLE tbpo_tracking_po;
TRUNCATE TABLE tbpo_file_bukti_beli;
TRUNCATE TABLE tbpo_file_nk;
TRUNCATE TABLE tbpo_diskon;
TRUNCATE TABLE tbpo_diskon_merk;
TRUNCATE TABLE tbpo_tmp_diskon;
TRUNCATE TABLE tbpo_tmp_item;
TRUNCATE TABLE tbpo_tmp_item_nk;
TRUNCATE TABLE tbpo_tmp_tax;
TRUNCATE TABLE tbpo_tmp_note_barang;
TRUNCATE TABLE tbpo_note_barang;
TRUNCATE TABLE tbpo_note_pembelian;
TRUNCATE TABLE tbpo_note_direktur;
TRUNCATE TABLE tbpo_nt_tmp_pembelian;
TRUNCATE TABLE tbpo_generate_kd;
TRUNCATE TABLE tbpo_generate_kd_ponk;
TRUNCATE TABLE tb_po_pending;
TRUNCATE TABLE tb_po_received;
TRUNCATE TABLE tb_tmp_po_received;
TRUNCATE TABLE tb_pre_po;
TRUNCATE TABLE tb_pre_po_adjustment_log;
TRUNCATE TABLE tb_pre_po_diskon_history;
TRUNCATE TABLE tb_pre_po_invoice_adjustment;
TRUNCATE TABLE tb_ics_po;

-- 2. TRANSAKSI LPB (PENERIMAAN BARANG) & RETUR PEMBELIAN
TRUNCATE TABLE tb_lpb;
TRUNCATE TABLE tb_lpb_detail;
TRUNCATE TABLE tb_lpb_batch;
TRUNCATE TABLE tb_lpb_log;
TRUNCATE TABLE tb_lpb_manual_log;
TRUNCATE TABLE tb_lpb_price_adjustment;
TRUNCATE TABLE tb_lpb_price_adjustment_detail;
TRUNCATE TABLE tblpb_faktur_pajak;
TRUNCATE TABLE tb_retur_pembelian;
TRUNCATE TABLE tb_retur_pembelian_detail;
TRUNCATE TABLE tb_retur_pembelian_log;

-- 3. TRANSAKSI SALES ORDER (SO)
TRUNCATE TABLE tbso_sales_order;
TRUNCATE TABLE tbso_sales_order_detail;
TRUNCATE TABLE tbso_so_approval;
TRUNCATE TABLE tbso_stock_reservation;
TRUNCATE TABLE tbso_cancel_partial_request;
TRUNCATE TABLE tbso_approval_harga;
TRUNCATE TABLE tbso_activity_log;

-- 4. TRANSAKSI PENJUALAN, FAKTUR, DO & LOGISTIK
TRUNCATE TABLE tbso_faktur_penjualan;
TRUNCATE TABLE tbso_faktur_detail;
TRUNCATE TABLE tbso_faktur_jurnal;
TRUNCATE TABLE tbso_faktur_log;
TRUNCATE TABLE tb_editlog_faktur;
TRUNCATE TABLE tb_do;
TRUNCATE TABLE tb_detail_do;
TRUNCATE TABLE tb_pnd_do;
TRUNCATE TABLE tb_pre_do;
TRUNCATE TABLE tb_tmp_do;
TRUNCATE TABLE tb_tmp_detaildo;
TRUNCATE TABLE tb_log_do;
TRUNCATE TABLE trashbin_do;
TRUNCATE TABLE tb_ics_do;
TRUNCATE TABLE tb_pengajuan_od;
TRUNCATE TABLE tb_pengajuan_od_faktur;
TRUNCATE TABLE tb_retur_barang;
TRUNCATE TABLE tb_detail_retur_barang;
TRUNCATE TABLE tbrp_retur_penjualan_header;
TRUNCATE TABLE tbrp_retur_penjualan_detail;
TRUNCATE TABLE tbrp_spr_header;
TRUNCATE TABLE tbrp_spr_detail;
TRUNCATE TABLE tbrp_activity_log;
TRUNCATE TABLE tb_spr_header;
TRUNCATE TABLE tb_spr_detail;
TRUNCATE TABLE tb_log_confirm_sales;
TRUNCATE TABLE tb_lap_distribusi;
TRUNCATE TABLE tb_tmp_lap_distribusi;
TRUNCATE TABLE tb_loading_kk;
TRUNCATE TABLE tb_loading_kk_bck;
TRUNCATE TABLE tb_loading_lk;
TRUNCATE TABLE tb_loading_lk_bck;
TRUNCATE TABLE tb_det_tracking_driver;
TRUNCATE TABLE tb_order_tracking_driver;
TRUNCATE TABLE tb_bongkaran;
TRUNCATE TABLE tb_bongkaran_checker;

-- 5. TRANSAKSI KEUANGAN, PEMBAYARAN, KAS & BANK
TRUNCATE TABLE tbkeu_pembayaran;
TRUNCATE TABLE tbkeu_pembayaran_alokasi;
TRUNCATE TABLE tbkeu_pembayaran_faktur;
TRUNCATE TABLE tbkeu_kas_masuk;
TRUNCATE TABLE tbkeu_kas_masuk_detail;
TRUNCATE TABLE tbkeu_kas_keluar;
TRUNCATE TABLE tbkeu_kas_keluar_detail;
TRUNCATE TABLE tbkeu_transaksi_kasir;
TRUNCATE TABLE tbkeu_kasir_saldo;
TRUNCATE TABLE tbkeu_nomor_dokumen;
TRUNCATE TABLE tb_kasbon;

-- 6. JURNAL & AKUNTANSI
TRUNCATE TABLE tbkeu_jurnal;
TRUNCATE TABLE tbkeu_jurnal_detail;
TRUNCATE TABLE tbkeu_jurnal_log;
TRUNCATE TABLE tbkeu_karismaerp_import_batch;
TRUNCATE TABLE tbkeu_posting_exception;

-- 7. TRANSAKSI MUTASI & KARTU STOK
TRUNCATE TABLE tb_mutasi;
TRUNCATE TABLE tb_detail_mutasi;
TRUNCATE TABLE tb_tmp_mutasi;
TRUNCATE TABLE tb_log_mutasi;
TRUNCATE TABLE tberp_stock_batch;
TRUNCATE TABLE tberp_stock_ledger;
TRUNCATE TABLE tb_dailystock;
TRUNCATE TABLE tb_dailystock_global;
TRUNCATE TABLE tbkeu_penyesuaian_barang;
TRUNCATE TABLE tbkeu_penyesuaian_barang_detail;
TRUNCATE TABLE tb_stock_hold;
TRUNCATE TABLE tb_ics;
TRUNCATE TABLE tb_log_ics;
TRUNCATE TABLE stockopname_master;
TRUNCATE TABLE stockopname_master_box;
TRUNCATE TABLE stockopname_master_item;
TRUNCATE TABLE stockopname_master_manual_item;
TRUNCATE TABLE stockopname_opname;
TRUNCATE TABLE stockopname_opname_log;
TRUNCATE TABLE stockopname_opname_manual;
TRUNCATE TABLE stockopname_pending;
TRUNCATE TABLE stockopname_recyclebin_input;
TRUNCATE TABLE tb_req_opname;
TRUNCATE TABLE tb_qty_lot;

SET FOREIGN_KEY_CHECKS = 1;
