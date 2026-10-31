# Database Penetapan Akun Klien per User

Migration `portal_sso_client_account_assignment_20261030.sql` membuat tabel `tb_portal_sso_client_account_assignment` untuk menghubungkan akun pada `tb_portal_sso_client_account` dengan user Portal melalui `auth_source` dan `user_id`.

Unique key pada `account_id` memastikan akun tidak dapat dipakai dua user sekaligus. Unique key pada kombinasi `app_code`, `auth_source`, dan `user_id` membatasi satu akun klien per user untuk tiap aplikasi. Migration tidak mengubah tabel akun, akses, maupun data yang telah ada.

Alur redirect SSO membaca username dari tabel penetapan dan menambahkannya sebagai klaim opsional pada respons token. Jika terdapat akun impor untuk klien, token ditolak saat belum ada assignment. Perubahan ini tidak menambah kolom atau tabel database.

Jalankan migration satu kali setelah backup. Rollback tersedia pada `portal_sso_client_account_assignment_rollback_20261030.sql` dan menghapus seluruh pemetaan akun-user; backup data sebelum menjalankannya.
