# Catatan Database User Management `tb_users`

Tanggal: 29 Oktober 2026

## Perubahan Skema

Tidak ada perubahan skema database dan tidak ada migrasi SQL. Modul User Management menggunakan tabel `tb_users` beserta kolom yang tercantum pada skema acuan `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`: `id`, `username`, `password`, `level`, `jobdesk_hrd`, `nama_lngkp`, `nik`, `bagian`, `departement`, `jabatan`, `atasan`, `penilai`, `status`, `default_redirect`, `last_login`, `created_at`, dan `updated_at`.

Operasi CRUD hanya menyentuh baris `tb_users` berdasarkan `id`. Pembuatan dan reset password menyimpan hash password. Form mengelola nama, username, NIK, bagian, departemen, jabatan, level, atasan, penilai, halaman awal, status, serta password. Field `jobdesk_hrd` tidak ditampilkan atau diubah; data yang sudah ada pada kolom itu tetap utuh. Kode menerima nama kolom departemen `departemen` dan fallback `departement` sesuai ejaan di skema acuan Agustus. Perubahan ini tidak memigrasikan, menyalin, atau menghapus data dari `tb_karyawan`.

## Dampak Data

Belum ada perubahan data database yang dijalankan pada pengembangan ini. Operasi penambahan, perubahan, penghapusan, status, atau password hanya terjadi saat pengguna berizin memakai halaman User Management.

## Validasi Lingkungan

Sebelum digunakan, pastikan database target memiliki kolom `tb_users` sesuai daftar di atas. Hak aplikasi Portal tetap dicatat secara terpisah berdasarkan pasangan `auth_source='tb_users'` dan `user_id`; tidak ada perubahan tabel akses Portal.
