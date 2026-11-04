# Database Katalog Module Aplikasi Portal

Migration ini menambahkan tabel `tb_portal_app_module` untuk menyimpan daftar module yang ditawarkan tiap aplikasi klien. Skema database acuan Agustus 2026 belum memuat tabel tersebut.

## Kolom dan batas data

- `app_code`: kode aplikasi yang sudah terdaftar dalam katalog Portal.
- `module_code`: kode unik module dalam satu aplikasi.
- `module_name`, `description`, `module_url`: informasi yang ditampilkan dan dikelola admin/klien.
- `sort_order`, `is_active`: pengaturan urutan dan ketersediaan module.
- `created_by`, `updated_by`: ID admin yang terakhir mengelola data melalui UI.
- `created_by_client`, `updated_by_client`: kode klien yang mengelola data melalui API.

Kombinasi `app_code` dan `module_code` unik. Tidak dibuat foreign key ke tabel registrasi klien karena katalog kode aplikasi Portal juga memuat aplikasi yang belum memakai SSO. Penghapusan module menghapus baris secara permanen.

## Penerapan

Cadangkan database, jalankan `portal_app_module_20261024.sql`, lalu uji halaman Setting Module dan API klien. Rollback tersedia di `portal_app_module_rollback_20261024.sql`; rollback menghapus tabel beserta seluruh data katalognya.
