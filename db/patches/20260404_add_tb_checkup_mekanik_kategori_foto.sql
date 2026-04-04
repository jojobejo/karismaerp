CREATE TABLE IF NOT EXISTS `tb_checkup_mekanik_kategori_foto` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_ckup` INT NOT NULL,
  `id_kategori` INT NOT NULL,
  `foto` VARCHAR(255) NOT NULL,
  `input_by` VARCHAR(100) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ckup_kategori` (`id_ckup`, `id_kategori`),
  KEY `idx_ckup` (`id_ckup`),
  KEY `idx_kategori` (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
