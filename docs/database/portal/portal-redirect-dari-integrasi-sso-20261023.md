# Catatan Database: URL Peluncuran dan Callback Portal

Tanggal: 23 Oktober 2026

Catatan awal menyebut kartu portal membaca callback dari `redirect_uri`. Setelah pemisahan pada 24 Oktober 2026, kartu membaca `launch_uri` dan callback tetap menggunakan `redirect_uri`. Kedua URL diambil hanya untuk integrasi dengan `is_enabled = 1`; hak akses user tetap dibaca dari `tb_portal_app_access`.

Kolom `launch_uri` ditambahkan pada `tb_portal_sso_client` melalui `portal_sso_launch_uri_20261024.sql`; tabel akses user tidak berubah. Migration sudah dijalankan pada database lokal pengembangan. Jika URL peluncuran kosong atau klien dinonaktifkan, kartu tidak memiliki tujuan sampai admin memperbarui integrasi pada menu `admin/portal-sso-clients`.
