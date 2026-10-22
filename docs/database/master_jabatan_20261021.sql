-- Menambahkan tabel master jabatan untuk modul Master Organisasi.
-- Jalankan pada database KARISMA ERP setelah backup dan verifikasi lingkungan.
CREATE TABLE IF NOT EXISTS `tb_jabatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tb_jabatan_nama` (`nama_jabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
