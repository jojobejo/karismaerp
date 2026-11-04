# Catatan Database: Pembaruan User Berdasarkan ID

Tanggal: 23 Oktober 2026

**Tidak ada perubahan skema database.** Perbaikan hanya mengubah cara aplikasi menjalankan pembaruan pada `tb_karyawan`; struktur tabel dan data tidak dimigrasikan atau diubah oleh pekerjaan ini.

Acuan struktur yang diperiksa: `tb_karyawan.id` adalah `PRIMARY KEY` pada dump `database/backups/tb_karyawan_before_jabatan_20261021.sql`. Pastikan skema lingkungan aktif tetap memiliki kunci tersebut.
