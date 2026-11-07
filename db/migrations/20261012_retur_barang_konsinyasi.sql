-- Menambahkan tipe retur barang konsinyasi pada SPR dan retur penjualan.

ALTER TABLE `tbrp_spr_header`
    MODIFY COLUMN `tipe_retur` ENUM('biasa','replace','service','konsinyasi')
    NOT NULL DEFAULT 'biasa';

ALTER TABLE `tbrp_retur_penjualan_header`
    MODIFY COLUMN `tipe_retur` ENUM('biasa','replace','service','konsinyasi')
    NOT NULL DEFAULT 'biasa';
