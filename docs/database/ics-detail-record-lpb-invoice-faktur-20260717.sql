ALTER TABLE `tb_lpb`
    ADD COLUMN IF NOT EXISTS `tanggal_invoice` DATE NULL AFTER `no_invoice`,
    ADD COLUMN IF NOT EXISTS `kode_faktur_pajak` VARCHAR(100) NULL AFTER `tanggal_invoice`,
    ADD COLUMN IF NOT EXISTS `tanggal_faktur_pajak` DATE NULL AFTER `kode_faktur_pajak`;
