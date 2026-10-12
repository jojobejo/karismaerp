-- Modul konfirmasi penerimaan berkas expedisi.
-- Jalankan sekali pada database Karisma ERP setelah backup database dibuat.

ALTER TABLE tb_expedisi
    ADD COLUMN IF NOT EXISTS penerima_berkas_id INT(11) NULL AFTER keterangan,
    ADD COLUMN IF NOT EXISTS status_berkas ENUM('BELUM_DITERIMA', 'SUDAH_DITERIMA') NOT NULL DEFAULT 'BELUM_DITERIMA' AFTER penerima_berkas_id,
    ADD COLUMN IF NOT EXISTS diterima_oleh_id INT(11) NULL AFTER status_berkas,
    ADD COLUMN IF NOT EXISTS diterima_pada DATETIME NULL AFTER diterima_oleh_id,
    ADD COLUMN IF NOT EXISTS catatan_penerimaan VARCHAR(500) NULL AFTER diterima_pada;

CREATE TABLE IF NOT EXISTS tb_expedisi_penerimaan_riwayat (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    expedisi_id INT(11) NOT NULL,
    penerima_id INT(11) NOT NULL,
    status_berkas ENUM('SUDAH_DITERIMA') NOT NULL DEFAULT 'SUDAH_DITERIMA',
    catatan VARCHAR(500) NULL,
    diterima_pada DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_expedisi_penerimaan_riwayat_expedisi (expedisi_id),
    KEY idx_expedisi_penerimaan_riwayat_penerima (penerima_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Akun petugas Logistik. Password awal: 1234. Wajib diganti melalui prosedur admin setelah login pertama.
INSERT INTO tb_user (
    nik, kode_user, nama_user, nama_lengkap, departemen, alamat,
    tgl_lahir, username, password, akses_lv
)
SELECT
    'LOG-YUNIKA', 'LOG-YUNIKA', 'Yunika', 'Yunika', 'LOGISTIK', '-',
    '-', 'yunika', '$2y$10$75wnNAY8JjBR8ydogJ0HRuhNuIQPsgUy2HFwMu1xmUuL5GzrVPgmK', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tb_user WHERE username = 'yunika'
);
