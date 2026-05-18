-- ============================================================
-- MIGRASI: Tambah kolom qty_reserved pada tberp_stock_ledger
-- Tanggal : 2026-05-17
-- Deskripsi:
--   Mekanisme reservasi stok SO tidak lagi menggunakan tabel
--   tbso_stock_reservation. Sebagai gantinya, kolom qty_reserved
--   ditambahkan pada tberp_stock_ledger agar setiap entri histori
--   sekaligus mencatat berapa qty yang ter-reserved.
-- ============================================================

ALTER TABLE `tberp_stock_ledger`
  ADD COLUMN `qty_reserved` DECIMAL(12,3) NOT NULL DEFAULT 0
  AFTER `qty`;

-- Verifikasi struktur setelah ALTER
-- DESCRIBE tberp_stock_ledger;
