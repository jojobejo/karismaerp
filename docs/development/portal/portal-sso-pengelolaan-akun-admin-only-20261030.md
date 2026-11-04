# Pengelolaan Akun Klien Khusus Admin

Pengaturan pemilihan, penggantian, dan pencabutan akun hanya tersedia pada menu admin `admin/portal-app-access`. Tombol **Atur akun** dan endpoint self-service di Portal user dihapus. User hanya melihat status kesiapan dan tidak menerima username akun pada halaman Portal.

Jika ada akun impor untuk sebuah klien, SSO wajib menemukan penetapan admin untuk user tersebut pada tahap authorize dan pertukaran token. Tanpa penetapan, login ditolak sehingga tidak menggunakan akun Portal atau akun lain sebagai fallback. Validasi kedua ini juga menangani penetapan yang dicabut setelah authorize.

## Berkas utama

- `application/controllers/Portal.php`
- `application/controllers/Sso.php`
- `application/models/M_PortalSso.php`
- `application/models/M_PortalSsoClientAccounts.php`
- `application/views/portal/index.php`
- `application/config/routes.php`
- Klien KIU PO: `C:\xampp\htdocs\kiupobaru\application\libraries\Portal_sso.php` dan `C:\xampp\htdocs\kiupobaru\application\models\Auth\M_Portal_sso.php`

## Validasi

Pemeriksaan diff dilakukan. UAT hak admin/user dan login dengan akun aktual belum dilakukan.
