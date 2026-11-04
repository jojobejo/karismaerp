# Catatan Database Akun Admin Super Administrator

Tanggal: 2026-10-29

## Perubahan Data

Database lokal `kiucoid_karismaerp_local`, tabel `tb_users`, akun yang sudah ada (ID 205) diperbarui:

- `username`: `admin` (dipertahankan)
- `password`: disimpan sebagai hash PHP `PASSWORD_DEFAULT`; nilai password asli tidak dicatat di dokumen ini
- `level`: `1` (Super Administrator)
- `status`: `1` (aktif)

Pembaruan dijalankan dalam transaksi dan diverifikasi sebelum commit.

## Dampak Skema

Tidak ada perubahan skema, tabel, atau struktur database. Tidak diperlukan migrasi SQL.
