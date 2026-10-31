# Penetapan Akun Klien per User Portal

## Alur

- Pada `admin/portal-app-access?source=tb_users&user_id={id}`, kartu aplikasi menampilkan status akun dan tombol **Akun** di samping **Detail Module** untuk klien yang sudah mempunyai daftar akun. Tombol aktif hanya untuk aplikasi yang izinnya sudah diberikan dan disimpan. Tombol membuka modal untuk melihat, mengganti, atau mencabut penetapan.
- Admin dapat menetapkan satu akun klien untuk satu user per aplikasi. Daftar hanya menampilkan akun yang belum digunakan serta akun yang saat ini dipakai user tersebut.
- User hanya melihat status kesiapan akun pada kartu Portal. User tidak memperoleh kontrol untuk memilih, mengganti, atau melepas akun.
- Aplikasi menyimpan penetapan pada `tb_portal_sso_client_account_assignment`. Unique key mencegah satu akun klien digunakan oleh lebih dari satu user dan mencegah beberapa akun untuk user yang sama pada aplikasi tersebut.
- Saat akses aplikasi dicabut, penetapan akun untuk aplikasi itu dilepas sehingga akun kembali tersedia.
- Saat code SSO ditukar server-ke-server, Portal menyertakan username akun terpilih sebagai klaim opsional `client_account_username`. Klien SSO dapat memetakannya ke akun lokal lalu membuat session lokal. Password tidak dikirim ke browser, URL, atau respons token.

## Keamanan dan batasan

Pengaturan akun hanya tersedia melalui endpoint admin yang memerlukan izin `master/user-management`. Tidak ada endpoint pengaturan akun mandiri untuk user. Jika daftar akun aplikasi sudah diimpor, SSO ditolak sampai admin menetapkan akun; ini mencegah fallback ke akun lain. Password akun tidak dikirim ke halaman setting atau aplikasi klien. Klien yang belum membaca klaim opsional tetap memakai pemetaan identitas SSO Portal hanya untuk aplikasi yang belum memiliki daftar akun impor.

## Berkas terkait

- `application/controllers/admin/C_PortalAccess.php`
- `application/controllers/Portal.php`
- `application/models/M_PortalSsoClientAccounts.php`
- `application/views/content/admin/portal_app_access.php`
- `application/views/content/admin/portal_app_account_setting.php`
- `application/views/portal/index.php`
- `application/config/routes.php`

## Validasi

Migration database disiapkan terpisah. UAT admin, user, pemakaian akun lintas user, serta browser belum dilakukan.
