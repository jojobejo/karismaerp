# Database API SSO dan pemeriksaan klien

Tanggal: 31 Oktober 2026.

**Tidak ada perubahan skema database (no schema change).** Tidak ada migration, import data, perubahan assignment, atau perubahan database yang dijalankan pada pekerjaan ini. Diagnosis hanya membaca data; login normal tetap memakai tabel code dan histori yang sudah ada.

## Prasyarat yang sudah tersedia dalam dokumentasi

- `portal_sso_20261022.sql`: registry klien dan authorization code.
- `portal_sso_secret_reveal_20261022.sql`: secret terenkripsi untuk pengelolaan admin.
- `portal_sso_launch_uri_20261024.sql`: URL awal SSO klien.
- `portal_sso_client_account_20261029.sql`: katalog akun klien.
- `portal_sso_client_account_assignment_20261030.sql`: assignment unik per app_code/auth_source/user_id dan akun kerja.
- `portal_sso_login_history_20261030.sql`: histori konfirmasi login.
- Tabel akses Portal dan sumber identitas tb_users/tb_karyawan mengikuti schema aplikasi yang sudah ada. Database master proyek tetap `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`; patch SSO terdokumentasi di atas merupakan prasyarat tambahan sesuai lingkungan.

## Pemeriksaan deployment

1. Backup sebelum menerapkan patch lama yang belum terpasang. Tinjau kondisi target serta dokumentasi rollback masing-masing patch; jangan mengimpor ulang master ke database berjalan.
2. Pastikan kode sekali pakai memakai tabel transaksional dan semua kolom/index migration tersedia. Diagnosis halaman memeriksa keberadaan tabel, bukan audit lengkap struktur/index.
3. Pastikan satu izin eksplisit is_allowed=1 dan satu assignment untuk identitas serta app_code tujuan. Join katalog harus cocok pada account_id dan app_code.
4. Pastikan akun lokal yang ditetapkan cocok tepat satu baris aktif pada database klien dan level aksesnya sah. Pemeriksaan ini dilakukan klien; Portal tidak mengakses database klien.
5. Lakukan pengujian transaksi konkurensi pertukaran code pada database UAT. Update bersyarat used_at IS NULL membatasi pemakaian code, tetapi uji memori bukan pembuktian konkurensi MySQL.

Tidak ada rollback DDL baru. Jika kode aplikasi dikembalikan, evaluasi dampak kembalinya kebijakan izin/assignment lama; jangan menghapus tabel atau data SSO yang sudah digunakan.
