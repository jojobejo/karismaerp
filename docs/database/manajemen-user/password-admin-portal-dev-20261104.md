# Pembaruan password admin Portal pada database dev

Tanggal: 4 November 2026. Target: `kiucoid_karismaerp_dev.tb_users`, akun `admin` dengan ID 183. Akun sudah ada sebelum pekerjaan ini, aktif (`status=1`), memiliki `level=1` dan `jabatan=ADMIN`. Tidak dibuat akun duplikat.

Password akun diperbarui menggunakan `password_hash(..., PASSWORD_DEFAULT)` PHP, selaras dengan `password_verify()` pada `M_Auth`. Nilai password dan hash tidak disimpan dalam repository atau dokumen ini. Perubahan dilakukan dalam transaksi dengan syarat ID, username, status, level, dan jabatan cocok; verifikasi hash berhasil sebelum commit.

Backup satu baris akun sebelum pembaruan: `C:\xampp\mysql\backup\karismaerp-dev-portal\tb_users_admin_183_before_password_20261104_091525.sql` (SHA-256 `6C3FFD43BC88EEDAB7AA3112069F4472D399E555286C971B75AF7C80DE8BDC64`). File backup berada di luar repository karena memuat hash lama dan data akun. Jika perlu rollback, tinjau perubahan akun sesudah waktu backup sebelum memulihkan baris atau hash lama.

Verifikasi setelah commit: tepat satu akun bernama `admin`; `status=1`, `level=1`, `jabatan=ADMIN`; format password berupa hash; `password_verify()` dengan password yang diminta berhasil. Uji HTTP login dan halaman admin dicatat terpisah di [catatan development](../../development/portal/admin-login-dev-20261104.md).

**Tidak ada perubahan skema database.**
