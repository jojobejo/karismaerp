# Database Integrasi SSO Portal

## Perubahan

Migration menambah `tb_portal_sso_client` untuk callback dan hash secret klien, serta `tb_portal_sso_code` untuk kode otorisasi sementara yang sekali pakai. Tidak ada perubahan pada tabel akun dan tidak ada perubahan pada dump database acuan. Migration tidak membuat foreign key ke tabel legacy.

## Penerapan

1. Backup database sesuai prosedur operasional.
2. Jalankan `portal_sso_20261022.sql` pada database KARISMA ERP.
3. Jalankan satu kali `portal_sso_secret_reveal_20261022.sql` sebelum membuka halaman Integrasi SSO.
4. Atur `PORTAL_SSO_ENCRYPTION_KEY` yang persisten dan acak (minimal 32 karakter) pada environment server. Jangan commit nilainya. PIN dapat diatur melalui `PORTAL_SSO_KEY_PIN`; nilai awal bila tidak disetel adalah `898989`.
5. Daftarkan klien melalui menu **Integrasi SSO** dan simpan salinan secret pada secret manager server klien. Hash SHA-256 dipakai untuk verifikasi; ciphertext memungkinkan secret dibuka admin setelah verifikasi PIN.
6. Klien yang sudah terdaftar sebelum migration tambahan memiliki secret terenkripsi `NULL`; secret lamanya tidak dapat dipulihkan. Rotasikan secret untuk klien tersebut dan perbarui secret manager klien.

Skrip awal menggunakan `CREATE TABLE IF NOT EXISTS` dan dapat dijalankan ulang. Migration tambahan memakai `ALTER TABLE` dan hanya dijalankan sekali. Untuk menghapus hanya kolom ciphertext, jalankan `portal_sso_secret_reveal_rollback_20261022.sql` setelah mempertimbangkan dampaknya. Untuk rollback seluruh SSO, backup dahulu data tabel integrasi dan kemudian jalankan `portal_sso_rollback_20261022.sql`; rollback tersebut menghapus konfigurasi dan semua kode yang belum kedaluwarsa.

**Catatan keamanan:** PIN default enam digit cocok hanya sebagai gerbang tambahan untuk lingkungan terbatas. Atur PIN yang unik melalui environment dan batasi akses administrator. PIN bukan pengganti autentikasi kuat. Setelah lima percobaan salah pada sesi yang sama, pembukaan secret dikunci selama lima menit.

## Hubungan hak akses aplikasi

Hak masuk ke aplikasi menggunakan tabel `tb_portal_app_access` yang sudah ada. Simpan pilihan untuk setiap user melalui menu **Akses Aplikasi** sebelum mengaktifkan penggunaan SSO untuk user tersebut. Pada flow SSO, user yang belum memiliki penetapan akses ditolak. Aturan kompatibilitas menu portal yang menampilkan katalog lama bagi user tanpa konfigurasi tidak memberikan izin SSO.
