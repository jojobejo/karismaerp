-- Menambahkan kolom jabatan pada data karyawan untuk User Management.
ALTER TABLE `tb_karyawan`
  ADD COLUMN `jabatan` varchar(100) DEFAULT NULL AFTER `jobdesk`;
