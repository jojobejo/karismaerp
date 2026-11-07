-- Reset transaksi penjualan lokal mulai dari SO sampai pembayaran.
-- Master data, transaksi pembelian, batch stok, dan mutasi stok pembelian dipertahankan.

SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- Simpan jurnal yang berasal dari penjualan dan pembayaran faktur untuk dibersihkan.
CREATE TEMPORARY TABLE tmp_jurnal_penjualan (id_jurnal BIGINT UNSIGNED PRIMARY KEY);
INSERT INTO tmp_jurnal_penjualan (id_jurnal)
SELECT id_jurnal
FROM tbkeu_jurnal
WHERE source_module = 'SALES'
   OR (source_module = 'KEUANGAN' AND source_type = 'PEMBAYARAN_FAKTUR')
   OR (source_module = 'PURCHASING' AND source_type = 'CONSIGNMENT_SETTLEMENT');

-- Kembalikan stok fisik QROUN011 yang pernah keluar melalui faktur penjualan.
UPDATE tberp_stock_batch batch
JOIN (
    SELECT kd_barang, gudang_id, no_lot, SUM(qty) AS qty_keluar
    FROM tberp_stock_ledger
    WHERE kd_barang = 'QROUN011'
      AND tipe = 'OUT'
      AND ref_type = 'FAKTUR PENJUALAN'
    GROUP BY kd_barang, gudang_id, no_lot
) keluar
  ON keluar.kd_barang = batch.kd_barang
 AND keluar.gudang_id = batch.gudang_id
 AND keluar.no_lot = batch.no_lot
SET batch.qty_on_hand = batch.qty_on_hand + keluar.qty_keluar;

-- Semua reservasi penjualan tidak lagi berlaku setelah SO dikosongkan.
UPDATE tberp_stock_batch
SET qty_reserved = 0
WHERE qty_reserved <> 0;

-- Hapus hanya mutasi stok yang bersumber dari proses penjualan.
DELETE FROM tberp_stock_ledger
WHERE ref_type IN ('SALES_ORDER', 'FAKTUR PENJUALAN');

-- Pembayaran dan faktur konsinyasi penjualan.
DELETE FROM tb_konsinyasi_faktur;
DELETE FROM tb_konsinyasi_settlement;
DELETE FROM tbkeu_pembayaran_alokasi;
DELETE FROM tbkeu_pembayaran_faktur;
DELETE FROM tbkeu_pembayaran;

-- Jurnal turunan penjualan/pembayaran tanpa menyentuh jurnal pembelian dan LPB.
DELETE FROM tbkeu_posting_exception WHERE id_jurnal IN (SELECT id_jurnal FROM tmp_jurnal_penjualan);
DELETE FROM tbkeu_jurnal_log WHERE id_jurnal IN (SELECT id_jurnal FROM tmp_jurnal_penjualan);
DELETE FROM tbkeu_jurnal_detail WHERE id_jurnal IN (SELECT id_jurnal FROM tmp_jurnal_penjualan);
DELETE FROM tbkeu_jurnal WHERE id_jurnal IN (SELECT id_jurnal FROM tmp_jurnal_penjualan);

-- Faktur, DO, logistik penjualan, dan retur penjualan.
DELETE FROM tbso_faktur_z_pecah_detail;
DELETE FROM tbso_faktur_z_pecah;
DELETE FROM tbso_faktur_jurnal;
DELETE FROM tbso_faktur_log;
DELETE FROM tbso_faktur_detail;
DELETE FROM tbso_faktur_penjualan;
DELETE FROM tb_detail_do;
DELETE FROM tb_do;
DELETE FROM tb_pnd_do;
DELETE FROM tb_pre_do;
DELETE FROM tb_tmp_detaildo;
DELETE FROM tb_tmp_do;
DELETE FROM tb_log_do;
DELETE FROM trashbin_do;
DELETE FROM tb_ics_do;
DELETE FROM tb_pengajuan_od_faktur;
DELETE FROM tb_pengajuan_od;
DELETE FROM tb_detail_retur_barang;
DELETE FROM tb_retur_barang;
DELETE FROM tb_log_confirm_sales;
DELETE FROM tb_det_tracking_driver;
DELETE FROM tb_order_tracking_driver;
DELETE FROM tb_bongkaran_checker;
DELETE FROM tb_bongkaran;

-- SO dan seluruh data turunannya.
DELETE FROM tbso_cancel_partial_request;
DELETE FROM tbso_so_approval;
DELETE FROM tbso_approval_harga;
DELETE FROM tbso_activity_log;
DELETE FROM tbso_sales_order_detail;
DELETE FROM tbso_sales_order;
DELETE FROM tb_delivery_trip;

DROP TEMPORARY TABLE tmp_jurnal_penjualan;
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
