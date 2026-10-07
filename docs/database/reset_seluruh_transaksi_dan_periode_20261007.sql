-- Reset seluruh transaksi operasional dan akuntansi KarismaERP.
-- Master barang, customer, supplier, akun, user, gudang, satuan,
-- formula, mapping akun, serta konfigurasi aplikasi dipertahankan.
-- Wajib membuat dan menguji backup penuh sebelum menjalankan skrip ini.

SET FOREIGN_KEY_CHECKS = 0;

-- Closing, periode fiskal, jurnal, pembayaran, dan kas.
TRUNCATE TABLE `tbkeu_reopen_approval_log`;
TRUNCATE TABLE `tbkeu_reopen_request`;
TRUNCATE TABLE `tbkeu_closing_validation_detail`;
TRUNCATE TABLE `tbkeu_closing_validation_run`;
TRUNCATE TABLE `tbkeu_closing_balance_account`;
TRUNCATE TABLE `tbkeu_closing_period`;
TRUNCATE TABLE `tbkeu_posting_exception`;
TRUNCATE TABLE `tbkeu_pembayaran_alokasi`;
TRUNCATE TABLE `tbkeu_pembayaran`;
TRUNCATE TABLE `tbkeu_pembayaran_faktur`;
TRUNCATE TABLE `tbkeu_kas_keluar_detail`;
TRUNCATE TABLE `tbkeu_kas_keluar`;
TRUNCATE TABLE `tbkeu_kas_masuk_detail`;
TRUNCATE TABLE `tbkeu_kas_masuk`;
TRUNCATE TABLE `tbkeu_transaksi_kasir`;
TRUNCATE TABLE `tbkeu_kasir_saldo`;
TRUNCATE TABLE `tbkeu_penyesuaian_barang_detail`;
TRUNCATE TABLE `tbkeu_penyesuaian_barang`;
TRUNCATE TABLE `tbkeu_jurnal_log`;
TRUNCATE TABLE `tbkeu_jurnal_detail`;
TRUNCATE TABLE `tbkeu_jurnal`;
TRUNCATE TABLE `tbkeu_saldo_awal_akun`;
TRUNCATE TABLE `tbkeu_nomor_dokumen`;
TRUNCATE TABLE `tbkeu_periode_fiskal_log`;
TRUNCATE TABLE `tbkeu_periode_fiskal`;

-- Faktur, sales order, delivery, loading, dan aktivitas penjualan.
TRUNCATE TABLE `tbso_faktur_jurnal`;
TRUNCATE TABLE `tbso_faktur_log`;
TRUNCATE TABLE `tbso_faktur_detail`;
TRUNCATE TABLE `tbso_faktur_z_pecah_detail`;
TRUNCATE TABLE `tbso_faktur_z_pecah`;
TRUNCATE TABLE `tbso_faktur_penjualan`;
TRUNCATE TABLE `tbso_cancel_partial_request`;
TRUNCATE TABLE `tbso_so_approval`;
TRUNCATE TABLE `tbso_approval_harga`;
TRUNCATE TABLE `tbso_sales_order_detail`;
TRUNCATE TABLE `tbso_sales_order`;
TRUNCATE TABLE `tbso_activity_log`;
TRUNCATE TABLE `tb_detail_do`;
TRUNCATE TABLE `tb_do`;
TRUNCATE TABLE `tb_log_do`;
TRUNCATE TABLE `tb_delivery_trip`;
TRUNCATE TABLE `tb_loading_kk`;
TRUNCATE TABLE `tb_loading_kk_bck`;
TRUNCATE TABLE `tb_loading_lk`;
TRUNCATE TABLE `tb_loading_lk_bck`;
TRUNCATE TABLE `tb_bongkaran_checker`;
TRUNCATE TABLE `tb_order_tracking_driver`;
TRUNCATE TABLE `tb_log_confirm_sales`;
TRUNCATE TABLE `tb_pengajuan_od_faktur`;
TRUNCATE TABLE `tb_editlog_faktur`;

-- Retur penjualan dan retur barang legacy.
TRUNCATE TABLE `tbrp_retur_penjualan_detail`;
TRUNCATE TABLE `tbrp_retur_penjualan_header`;
TRUNCATE TABLE `tbrp_spr_detail`;
TRUNCATE TABLE `tbrp_spr_header`;
TRUNCATE TABLE `tbrp_activity_log`;
TRUNCATE TABLE `tb_detail_retur_barang`;
TRUNCATE TABLE `tb_retur_barang`;

-- Purchase order dan transaksi purchasing.
TRUNCATE TABLE `tbpo_realisasi_detail_po_nk`;
TRUNCATE TABLE `tbpo_realisasi_harganyata_log`;
TRUNCATE TABLE `tbpo_realisasi_po_nk`;
TRUNCATE TABLE `tbpo_detail_po_nk`;
TRUNCATE TABLE `tbpo_po_nk`;
TRUNCATE TABLE `tbpo_detail_po`;
TRUNCATE TABLE `tbpo_tracking_po`;
TRUNCATE TABLE `tbpo_file_bukti_beli`;
TRUNCATE TABLE `tbpo_file_nk`;
TRUNCATE TABLE `tbpo_detail_req`;
TRUNCATE TABLE `tbpo_req_nk`;
TRUNCATE TABLE `tbpo_req_masterbarang`;
TRUNCATE TABLE `tbpo_transaksi_tmp`;
TRUNCATE TABLE `tbpo_transaksi_trashbin`;
TRUNCATE TABLE `tbpo_transaksi`;
TRUNCATE TABLE `tbpo_tmp_item`;
TRUNCATE TABLE `tbpo_tmp_item_nk`;
TRUNCATE TABLE `tbpo_tmp_note_barang`;
TRUNCATE TABLE `tbpo_tmp_diskon`;
TRUNCATE TABLE `tbpo_tmp_tax`;
TRUNCATE TABLE `tbpo_note_pembelian`;
TRUNCATE TABLE `tbpo_note_direktur`;
TRUNCATE TABLE `tbpo_nt_tmp_pembelian`;
TRUNCATE TABLE `tbpo_po`;
TRUNCATE TABLE `tb_ics_po`;
TRUNCATE TABLE `tb_po_pending`;
TRUNCATE TABLE `tb_po_received`;
TRUNCATE TABLE `tb_tmp_po_received`;
TRUNCATE TABLE `tb_pre_po_adjustment_log`;
TRUNCATE TABLE `tb_pre_po_diskon_history`;
TRUNCATE TABLE `tb_pre_po_invoice_adjustment`;
TRUNCATE TABLE `tb_pre_po`;

-- LPB, revisi, adjustment, dan faktur pajak LPB.
TRUNCATE TABLE `tb_lpb_revision_request_log`;
TRUNCATE TABLE `tb_lpb_revision_request_detail`;
TRUNCATE TABLE `tb_lpb_revision_request`;
TRUNCATE TABLE `tb_lpb_price_adjustment_detail`;
TRUNCATE TABLE `tb_lpb_price_adjustment`;
TRUNCATE TABLE `tb_lpb_manual_log`;
TRUNCATE TABLE `tb_lpb_log`;
TRUNCATE TABLE `tb_lpb_detail`;
TRUNCATE TABLE `tb_lpb_batch`;
TRUNCATE TABLE `tblpb_faktur_pajak`;
TRUNCATE TABLE `tb_lpb`;

-- Retur pembelian.
TRUNCATE TABLE `tb_retur_pembelian_log`;
TRUNCATE TABLE `tb_retur_pembelian_detail`;
TRUNCATE TABLE `tb_retur_pembelian`;

-- Konsinyasi dan bundling yang menghasilkan transaksi stok.
TRUNCATE TABLE `tb_konsinyasi_settlement`;
TRUNCATE TABLE `tb_konsinyasi_faktur`;
TRUNCATE TABLE `tb_konsinyasi_masuk_detail`;
TRUNCATE TABLE `tb_konsinyasi_masuk`;
TRUNCATE TABLE `tberp_bundling_assembly_detail`;
TRUNCATE TABLE `tberp_bundling_assembly`;
TRUNCATE TABLE `tberp_bundling_request_detail`;
TRUNCATE TABLE `tberp_bundling_request`;

-- Mutasi, saldo, dan jejak stok transaksional.
TRUNCATE TABLE `tberp_stock_ledger`;
TRUNCATE TABLE `tberp_stock_batch`;
TRUNCATE TABLE `tb_detail_mutasi`;
TRUNCATE TABLE `tb_log_mutasi`;
TRUNCATE TABLE `tb_tmp_mutasi`;
TRUNCATE TABLE `tb_mutasi`;
TRUNCATE TABLE `tb_stock_hold`;
TRUNCATE TABLE `tb_stock_status`;
TRUNCATE TABLE `tb_dailystock`;
TRUNCATE TABLE `tb_dailystock_global`;
TRUNCATE TABLE `tb_saldo_awal`;
TRUNCATE TABLE `tb_kd_system_stock`;

-- Stock opname merupakan transaksi fisik, sedangkan master item opname dipertahankan.
TRUNCATE TABLE `stockopname_opname_log`;
TRUNCATE TABLE `stockopname_opname_manual`;
TRUNCATE TABLE `stockopname_pending`;
TRUNCATE TABLE `stockopname_recyclebin_input`;
TRUNCATE TABLE `stockopname_opname`;

SET FOREIGN_KEY_CHECKS = 1;
