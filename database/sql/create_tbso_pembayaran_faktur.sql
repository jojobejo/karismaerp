CREATE TABLE IF NOT EXISTS tbso_pembayaran_faktur (
    id_pembayaran INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_faktur INT UNSIGNED NOT NULL,
    no_faktur VARCHAR(30) NOT NULL,
    tanggal_pembayaran DATE NOT NULL,
    jumlah_pembayaran DECIMAL(16,2) NOT NULL DEFAULT 0,
    metode_pembayaran VARCHAR(30) NULL,
    keterangan TEXT NULL,
    create_by VARCHAR(100) NULL,
    create_at DATETIME NULL,
    PRIMARY KEY (id_pembayaran),
    KEY idx_id_faktur (id_faktur),
    KEY idx_no_faktur (no_faktur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
