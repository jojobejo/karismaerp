# Migrasi URL Peluncuran Klien SSO

## Perubahan skema

Menambahkan `tb_portal_sso_client.launch_uri` untuk menyimpan alamat awal aplikasi. Kolom `redirect_uri` tetap menjadi callback SSO yang harus cocok persis saat authorization dan pertukaran code. Tidak ada perubahan pada tabel akun portal atau hak akses.

Migration: `portal_sso_launch_uri_20261024.sql`  
Rollback: `portal_sso_launch_uri_rollback_20261024.sql`

Validasi ulang identitas dan pembersihan session lokal lama merupakan tanggung jawab aplikasi klien dan tidak menambah atau mengubah skema Portal. Setiap klien menyiapkan tabel pemetaan subject pada database aplikasinya sendiri; Portal tidak menentukan nama tabel atau migration klien.

## Penerapan

1. Backup database KARISMA ERP.
2. Jalankan SQL migration satu kali.
3. Buka **Portal → Integrasi SSO**, lalu simpan URL peluncuran HTTPS untuk setiap aplikasi yang perlu dibuka dari portal.
4. Untuk KIU PO lokal, URL peluncuran adalah `https://localhost/kiupobaru/`; callback tetap `https://localhost/kiupobaru/sso/callback`.
5. Pastikan aplikasi klien mengaktifkan SSO dan callback yang sama sudah didaftarkan sebelum UAT.

Sebelum `launch_uri` diisi, kartu aplikasi terkait tidak menampilkan tujuan yang dapat dibuka. Jangan isi URL callback sebagai URL peluncuran karena callback membutuhkan `state` dan verifier yang dibuat aplikasi klien.

## Rollback

Ekspor nilai `launch_uri` bila perlu menyimpannya, lalu jalankan rollback. Penghapusan kolom membuat portal tidak dapat mengarahkan kartu aplikasi ke URL awal; callback authorization tetap tersimpan.

Migration telah diterapkan pada database pengembangan lokal `kiucoid_karismaerp_local` pada 24 Oktober 2026. Migration production tetap belum dijalankan. Perubahan menambah skema konfigurasi portal; tabel akun dan isi tabel hak akses tidak berubah.
