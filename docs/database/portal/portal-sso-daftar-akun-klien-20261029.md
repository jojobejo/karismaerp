# Catatan Database Akun Klien Portal SSO

## Perubahan skema

Modul memerlukan tabel baru `tb_portal_sso_client_account`. Jalankan `portal_sso_client_account_20261029.sql` pada database aplikasi Portal sebelum fitur impor digunakan. Tabel menyimpan akun per `app_code`, ciphertext password, dan penanggung jawab (`pic_auth_source` + `pic_user_id`). Tidak ada foreign key ke tabel legacy akun karena PIC dapat berasal dari `tb_karyawan` atau `tb_users`.

Password disimpan menggunakan AES-256-GCM dengan kunci server `PORTAL_SSO_ENCRYPTION_KEY` atau konfigurasi encryption key Portal. Jaga backup database dan kunci enkripsi secara terpisah dengan kontrol akses yang sesuai. Mengganti atau kehilangan kunci membuat password lama tidak dapat didekripsi.

Rollback tersedia di `portal_sso_client_account_rollback_20261029.sql`. **Rollback menghapus seluruh data akun klien**; backup dan konfirmasi data wajib dilakukan sebelum menjalankannya.

## Batas impor

Modul menerima CSV berukuran maksimal 5 MB dan 10.000 baris. SQL dari klien tidak dieksekusi oleh aplikasi. File CSV sementara disimpan di direktori temporary server dengan nama acak dan masa berlaku 30 menit; setelah impor berhasil, file dihapus. Pemetaan kolom dilakukan per impor.
