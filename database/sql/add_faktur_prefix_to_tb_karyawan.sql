-- Prefix faktur permanen per karyawan.
-- Kode aplikasi juga akan membuat kolom ini otomatis jika belum ada.

ALTER TABLE tb_karyawan
    ADD COLUMN IF NOT EXISTS faktur_prefix VARCHAR(4) NULL AFTER username;
