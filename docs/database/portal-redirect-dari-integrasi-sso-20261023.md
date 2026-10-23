# Catatan Database: Sumber Redirect Portal

Tanggal: 23 Oktober 2026

Tidak ada perubahan skema atau data database. Route `portal` membaca kolom `redirect_uri` dan `is_enabled` yang sudah tersedia pada tabel `tb_portal_sso_client`, lalu menggunakan callback integrasi aktif sebagai tujuan kartu aplikasi. Pengaturan hak akses user tetap dibaca dari `tb_portal_app_access`.

Tidak dibuat kolom redirect tambahan pada tabel hak akses. Jika callback belum terdaftar atau klien dinonaktifkan, redirect tidak tersedia sampai admin memperbarui integrasi pada menu `admin/portal-sso-clients`.
