# Perubahan Database Histori Login Akun Klien

## Perubahan

Tambahkan tabel `tb_portal_sso_login_history` menggunakan skrip [portal_sso_login_history_20261030.sql](portal_sso_login_history_20261030.sql). Tabel menyimpan event konfirmasi session yang berhasil dibuat klien SSO: kode aplikasi, sumber dan ID user Portal, ID akun serta snapshot username, IP, user-agent, dan waktu login. Hash event unik membuat pengiriman ulang event idempoten.

Password akun, secret klien, dan ID session aplikasi tidak disimpan pada tabel ini. Tidak ada perubahan pada tabel user atau tabel akun impor.

## Penerapan dan rollback

Jalankan migrasi pada database Portal sebelum mengaktifkan login melalui akun pilihan. Pastikan tabel berhasil dibuat dan backup database tersedia. Rollback opsional ada pada [portal_sso_login_history_rollback_20261030.sql](portal_sso_login_history_rollback_20261030.sql); rollback menghapus seluruh histori, jadi ekspor/backup data terlebih dahulu.

KIU PO tidak memerlukan migrasi database untuk pengiriman event ini. UAT database setelah penerapan migrasi belum dilakukan.
