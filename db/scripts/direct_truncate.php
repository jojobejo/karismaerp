<?php
$db = new mysqli('127.0.0.1', 'root', '', 'kiucoid_karismaerp_local');
$db->query("SET FOREIGN_KEY_CHECKS = 0");

$tablesToTruncate = [
    'tbkeu_jurnal_detail',
    'tbkeu_jurnal',
    'tbkeu_jurnal_log',
    'tbkeu_posting_exception',
    'tbkeu_nomor_dokumen',
    'tbkeu_penyesuaian_barang_detail',
    'tbkeu_penyesuaian_barang',
    'tbkeu_kas_masuk_detail',
    'tbkeu_kas_masuk',
    'tbkeu_kas_keluar_detail',
    'tbkeu_kas_keluar',
    'tbkeu_kasir_saldo',
    'tbkeu_transaksi_kasir',
    'tbkeu_saldo_awal_akun',
    'tbkeu_closing_balance_account',
    'tbkeu_closing_balance_inventory',
    'tbkeu_closing_balance_ar',
    'tbkeu_closing_balance_ap',
    'tbkeu_closing_validation_log',
    'tbkeu_closing_validation_run',
    'tbkeu_reopen_request',
    'tbkeu_closing_period',
    'tbkeu_pembayaran_alokasi',
    'tbkeu_pembayaran_faktur',
    'tbkeu_pembayaran',
    'tbso_sales_order_detail',
    'tbso_sales_order',
    'tbso_so_approval',
    'tbso_approval_harga',
    'tbso_faktur_detail',
    'tbso_faktur_penjualan',
    'tbso_faktur_jurnal',
    'tbso_faktur_log',
    'tbso_activity_log',
    'tbso_faktur_z_pecah_detail',
    'tbso_faktur_z_pecah',
    'tbso_stock_reservation',
    'tbso_cancel_partial_request',
    'tb_detail_do',
    'tb_do',
    'tb_log_do',
    'tb_pre_do',
    'trashbin_do',
    'tb_delivery_trip',
    'tb_loading_kk',
    'tb_loading_lk',
    'tb_log_confirm_sales',
    'tbrp_retur_penjualan_detail',
    'tbrp_retur_penjualan_header',
    'tbrp_spr_detail',
    'tbrp_spr_header',
    'tbrp_activity_log',
    'tb_retur_barang',
    'tb_detail_retur_barang',
    'tbpo_detail_po',
    'tbpo_po',
    'tbpo_detail_po_nk',
    'tbpo_po_nk',
    'tbpo_tracking_po',
    'tbpo_transaksi',
    'tbpo_transaksi_tmp',
    'tbpo_transaksi_trashbin',
    'tb_lpb_detail',
    'tb_lpb',
    'tb_lpb_batch',
    'tb_lpb_log',
    'tb_lpb_manual_log',
    'tb_lpb_price_adjustment_detail',
    'tb_lpb_price_adjustment',
    'tb_lpb_revision_request_detail',
    'tb_lpb_revision_request_log',
    'tb_lpb_revision_request',
    'tblpb_faktur_pajak',
    'tb_retur_pembelian_detail',
    'tb_retur_pembelian',
    'tb_retur_pembelian_log',
    'tb_konsinyasi_settlement',
    'tb_konsinyasi_faktur',
    'tb_konsinyasi_masuk_detail',
    'tb_konsinyasi_masuk',
    'tberp_stock_ledger',
    'tberp_stock_batch',
    'tberp_bundling_request_detail',
    'tberp_bundling_request',
    'tberp_bundling_assembly_detail',
    'tberp_bundling_assembly',
    'tberp_bundling_disassembly_detail',
    'tberp_bundling_disassembly',
    'tb_mutasi',
    'tb_detail_mutasi',
    'tb_log_mutasi',
    'tb_dailystock',
    'tb_dailystock_global'
];

foreach ($tablesToTruncate as $tbl) {
    if ($db->query("TRUNCATE TABLE `$tbl`")) {
        echo "Truncate $tbl: OK\n";
    } else {
        echo "Truncate $tbl: FAILED - " . $db->error . "\n";
    }
}

// Reset periode fiskal menjadi OPEN
$db->query("UPDATE tbkeu_periode_fiskal SET status = 'OPEN', closed_by = NULL, closed_at = NULL, reopened_by = NULL, reopened_at = NULL");
echo "Update tbkeu_periode_fiskal to OPEN: " . ($db->affected_rows >= 0 ? "OK" : "FAILED") . "\n";

$db->query("SET FOREIGN_KEY_CHECKS = 1");
$db->close();
