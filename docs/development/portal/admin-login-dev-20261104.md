# Verifikasi login admin Portal dev

Akun `admin` yang sudah ada di database dev memakai `tb_users` sebagai sumber autentikasi. `Auth/process` memverifikasi password melalui `M_Auth::verify_password()` dan membentuk sesi `is_admin_dashboard` untuk username `admin`. Tidak ada perubahan source aplikasi, route, atau aturan izin.

Uji melalui HTTPS lokal dengan sesi yang sama menghasilkan login HTTP 303 ke `/portal`, lalu HTTP 200 untuk `/portal`, `/admin/portal-sso-clients`, `/admin/portal-app-directory`, dan `/master/user-management`. Uji ini memastikan akses halaman dasar; operasi tulis admin, alur SSO klien, dan pengujian browser manual belum dilakukan. Catatan perubahan data dan backup ada di [dokumentasi database](../../database/manajemen-user/password-admin-portal-dev-20261104.md).

**Tidak ada perubahan skema database.**
