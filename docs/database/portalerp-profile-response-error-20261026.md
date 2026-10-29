# Catatan database: perbaikan profil Portal dan respons JSON

Tanggal: 26 Oktober 2026

## Dampak database

Tidak ada perubahan schema database dan tidak ada SQL yang perlu dijalankan. Perbaikan menyelaraskan koneksi CI3 dengan nama database dan prefix tabel yang sudah didokumentasikan:

- Profil ERP default: database `kiucoid_karismaerp`, tanpa prefix.
- Profil Portal: aktifkan `KARISMA_DB_PROFILE=portalerp`; database `portalerp`, prefix `portalerp_`.

Tanpa prefix Portal, CI3 mencari tabel seperti `tb_users` alih-alih `portalerp_tb_users`. Kesalahan query dapat menghasilkan halaman error HTML pada endpoint yang diharapkan mengembalikan JSON.

## Penggunaan

Pastikan schema Portal sudah diimpor sesuai [panduan setup database Portal](portalerp_setup_20261026.md). Atur environment `KARISMA_DB_PROFILE=portalerp` hanya pada proses PHP/Apache Portal, lalu restart proses tersebut. Hapus environment itu untuk kembali ke database ERP default.

Perubahan ini tidak menjalankan migrasi, menyalin akun, atau mengubah data.
