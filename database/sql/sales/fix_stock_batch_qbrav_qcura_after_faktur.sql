-- Audit dan koreksi stok batch untuk kasus SO/Faktur:
-- QBRAV03 lot 60407084 exp 2031-04-01
-- QCURA03 lot JAK6D14056 exp 2027-04-01
--
-- Isi @gudang_id sesuai gudang SO sebelum menjalankan UPDATE.
-- UPDATE tidak akan mengubah data jika @gudang_id masih NULL.
-- Jalankan SELECT audit dulu. Jika barisnya sudah benar, COMMIT.

SET @gudang_id = NULL;

SELECT
    id,
    kd_barang,
    gudang_id,
    no_lot,
    expired_date,
    qty_on_hand,
    qty_reserved
FROM tberp_stock_batch
WHERE kd_barang IN ('QBRAV03', 'QCURA03')
  AND no_lot IN ('60407084', 'JAK6D14056')
  AND expired_date IN ('2031-04-01', '2027-04-01')
  AND (@gudang_id IS NULL OR gudang_id = @gudang_id)
ORDER BY kd_barang, gudang_id, no_lot, expired_date, id;

START TRANSACTION;

UPDATE tberp_stock_batch
SET qty_on_hand = 25,
    qty_reserved = 0,
    update_at = NOW()
WHERE kd_barang = 'QBRAV03'
  AND no_lot = '60407084'
  AND expired_date = '2031-04-01'
  AND @gudang_id IS NOT NULL
  AND gudang_id = @gudang_id;

UPDATE tberp_stock_batch
SET qty_on_hand = 300,
    qty_reserved = 0,
    update_at = NOW()
WHERE kd_barang = 'QCURA03'
  AND no_lot = 'JAK6D14056'
  AND expired_date = '2027-04-01'
  AND @gudang_id IS NOT NULL
  AND gudang_id = @gudang_id;

SELECT
    id,
    kd_barang,
    gudang_id,
    no_lot,
    expired_date,
    qty_on_hand,
    qty_reserved
FROM tberp_stock_batch
WHERE kd_barang IN ('QBRAV03', 'QCURA03')
  AND no_lot IN ('60407084', 'JAK6D14056')
  AND expired_date IN ('2031-04-01', '2027-04-01')
  AND (@gudang_id IS NULL OR gudang_id = @gudang_id)
ORDER BY kd_barang, gudang_id, no_lot, expired_date, id;

-- Jika hasil SELECT akhir sudah benar:
-- COMMIT;
--
-- Jika salah gudang/baris:
-- ROLLBACK;
