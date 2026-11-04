# Data superuser dan status direktori (4 November 2026)

Tidak ada perubahan skema. Sumber status adalah `tb_portal_app_directory`, `tb_portal_sso_client`, `tb_portal_app_access`, dan `tb_portal_sso_client_account_assignment` yang sudah ada.

Skrip [superuser_akses_direktori_20261104.sql](superuser_akses_direktori_20261104.sql) membuat user `superuser` bila belum ada, dengan hash bcrypt untuk password awal `karisma`, kemudian memberikan izin eksplisit ke semua `app_code` direktori. Skrip telah dijalankan pada database lokal development `kiucoid_karismaerp_dev`: 13 aplikasi direktori dan 13 izin aktif terverifikasi. Skrip tidak membuat klien SSO, akun lokal klien, atau penetapan akun kerja.

Untuk rollback data yang dibuat khusus oleh skrip ini, pastikan user `superuser` tidak digunakan untuk data lain, lalu hapus izin `tb_portal_app_access` miliknya dan hapus baris `tb_users` tersebut dalam satu transaksi. Jangan jalankan rollback pada user `superuser` yang sudah ada sebelum skrip diterapkan.
