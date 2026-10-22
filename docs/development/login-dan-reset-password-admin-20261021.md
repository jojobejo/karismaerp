# Login dan permintaan reset password admin

Tanggal: 21 Oktober 2026

## Fitur

- Login menampilkan SweetAlert2 khusus untuk username tidak ditemukan, password salah, dan akun nonaktif.
- Tautan **Lupa password?** menerima username dan membuat satu permintaan aktif untuk satu akun. Permintaan berulang saat masih menunggu tidak membuat baris ganda.
- Admin membuka menu **Permintaan Reset Password**, meninjau username, sumber akun, waktu, dan status.
- Admin memproses permintaan dengan tombol **Reset ke karisma123**. Password disimpan menggunakan `password_hash()`, dan status antrean diselesaikan dalam transaksi.

## Cara penggunaan

1. User memilih **Lupa password?** di halaman masuk, mengisi username, lalu mengirim permintaan.
2. Admin masuk menggunakan akun dengan hak admin, lalu membuka menu **Permintaan Reset Password**.
3. Admin memeriksa username dan memilih **Reset ke karisma123** pada permintaan `PENDING` yang benar.
4. Admin menyampaikan password sementara kepada pemilik akun melalui jalur internal yang telah diverifikasi. User masuk dengan password sementara.

## File utama

- `application/controllers/Auth.php`, `application/models/M_Auth.php`
- `application/controllers/admin/C_PasswordResetRequests.php`
- `application/views/content/login/body.php`, `application/views/partial/login/footer.php`
- `application/views/content/admin/password_reset_requests.php`
- `application/config/routes.php`, `application/views/partial/main/sidebar.php`

## Kebutuhan database dan batas validasi

Terapkan tabel baru dari dokumentasi `docs/database/password_reset_request_20261021.md` sebelum fitur permintaan digunakan. Tidak dilakukan perubahan pada tabel akun lama. Pemeriksaan runtime terhadap database, autentikasi, maupun UAT browser belum dilakukan.
