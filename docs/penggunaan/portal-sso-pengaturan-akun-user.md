# Pengaturan Akun Aplikasi Klien

## Prasyarat

- Admin telah mengimpor akun klien melalui **Akun List**.
- Migration `docs/database/portal_sso_client_account_assignment_20261030.sql` telah diterapkan.
- User sudah diberi akses untuk aplikasi terkait.

## Admin: menetapkan akun

1. Buka `admin/portal-app-access?source=tb_users&user_id={id}` atau gunakan sumber dan ID user yang benar.
2. Pastikan akses aplikasi sudah diberikan dan tekan **Simpan akses**.
3. Pada kartu aplikasi, pilih **Akun** di samping **Detail Module**. Status akun yang sedang ditetapkan terlihat langsung pada kartu.
4. Di modal, pilih akun yang tersedia lalu tekan **Simpan akun**. Untuk melepasnya, tekan **Cabut penetapan**.

Akun yang digunakan user lain tidak tersedia dalam pilihan. Mencabut akses aplikasi akan melepas penetapan agar akun dapat digunakan kembali.

## User: menggunakan akun yang ditetapkan admin

User tidak dapat memilih atau mengganti akun. Jika akses aplikasi dan akun kerja tersedia, kartu menampilkan **Akun kerja tersedia**. Tombol **Mulai Bekerja** membuka aplikasi menggunakan akun yang ditetapkan admin.

Jika akses aplikasi sudah diberikan tetapi akun kerja belum ditetapkan, kartu menampilkan **Akun belum tersedia · hubungi admin**. Hubungi admin untuk menetapkan akun sebelum membuka aplikasi; Portal tidak akan menggunakan akun lain sebagai pengganti.

Saat aplikasi mendukung klaim SSO akun terpilih, Portal mengirim username akun melalui respons token server-ke-server dan aplikasi membuat session lokal untuk akun itu. Password tidak dikirim saat redirect.
