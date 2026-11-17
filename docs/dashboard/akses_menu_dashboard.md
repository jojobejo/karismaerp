# Dokumentasi Fitur — Hak Akses Menu Dashboard Per User

## 1. Ringkasan Fitur
Fitur **Akses Menu Dashboard** memungkinkan **Administrator** mengatur menu apa saja di halaman `/dashboard` yang boleh dibuka atau harus dikunci untuk masing-masing user.

- **Hanya Admin yang dapat mengakses halaman pengaturan**: `/master/akses-dashboard` dilindungi oleh pengecekan ketat [`M_Dashboard::is_strict_admin()`](file:///c:/laragon/www/karismaerp/application/models/M_Dashboard.php#L27-L40) pada [`C_Aksesdashboard`](file:///c:/laragon/www/karismaerp/application/controllers/master/C_Aksesdashboard.php).
- **Menu terkunci menampilkan ikon gembok**: Menu yang dinonaktifkan (`is_allowed = 0`) tetap tampil di Dashboard user dengan ikon gembok, tampilan redup, status **Terkunci / Tidak ada akses**, dan tidak dapat diklik (baik di grid tab departemen maupun di hasil pencarian cepat).
- **Proteksi Sisi Server (Anti-Bypass URL)**: Hook [`Dashboard_access_guard`](file:///c:/laragon/www/karismaerp/application/hooks/Dashboard_access_guard.php) memblokir akses langsung ke URL modul maupun endpoint AJAX-nya dengan status HTTP `403` apabila menu terkait sedang dikunci untuk user yang login.
- **Sinkron Lintas Tab**: Pengaturan menggunakan `menu_key` kanonik, sehingga mengunci satu modul (misalnya **Retur Pembelian**) otomatis mengunci modul tersebut di seluruh tab tempat modul itu muncul (`KEUANGAN`, `LOGISTIK`, `PURCHASING`, dan `IT`).
- **Standardisasi Redirect Pasca-Login ke Dashboard**: Seluruh user setelah login melalui [`Auth::process()`](file:///c:/laragon/www/karismaerp/application/controllers/auth.php) kini langsung diarahkan ke `/dashboard` (tidak lagi langsung ke modul tertentu seperti `sales_order` atau `checker`), sehingga tab departemen masing-masing serta status kunci/buka menu dari halaman Dashboard diterapkan secara konsisten.

---

## 2. Migrasi Database
File migrasi: [`20261111_user_dashboard_access.sql`](file:///c:/laragon/www/karismaerp/db/migrations/20261111_user_dashboard_access.sql)

Tabel baru `tb_user_dashboard_access`:
- `id`: Primary key (`INT UNSIGNED AUTO_INCREMENT`)
- `user_id`: ID user pada `tb_users`
- `menu_key`: Kode unik kanonik modul dashboard (mis. `retur_pembelian`, `jurnal_pembelian`, `sales_order`)
- `is_allowed`: `1` = diizinkan (terbuka), `0` = dikunci (ikon gembok)
- `updated_by`, `created_at`, `updated_at`: Jejak perubahan oleh Admin

> Tabel juga otomatis dibuat oleh [`M_Dashboardaccess::ensure_schema()`](file:///c:/laragon/www/karismaerp/application/models/master/M_Dashboardaccess.php#L21-L39) jika belum ada.

---

## 3. Daftar File Terkait
| Komponen | Lokasi File |
|---|---|
| Migrasi SQL | [`db/migrations/20261111_user_dashboard_access.sql`](file:///c:/laragon/www/karismaerp/db/migrations/20261111_user_dashboard_access.sql) |
| Model Dashboard & Katalog Menu | [`application/models/M_Dashboard.php`](file:///c:/laragon/www/karismaerp/application/models/M_Dashboard.php) |
| Model Akses Dashboard | [`application/models/master/M_Dashboardaccess.php`](file:///c:/laragon/www/karismaerp/application/models/master/M_Dashboardaccess.php) |
| Controller Admin | [`application/controllers/master/C_Aksesdashboard.php`](file:///c:/laragon/www/karismaerp/application/controllers/master/C_Aksesdashboard.php) |
| View Pengaturan Admin | [`application/views/content/master/aksesdashboard/index.php`](file:///c:/laragon/www/karismaerp/application/views/content/master/aksesdashboard/index.php) |
| View Dashboard (Ikon Gembok) | [`application/views/content/dashboard/index.php`](file:///c:/laragon/www/karismaerp/application/views/content/dashboard/index.php) |
| Sidebar Master User | [`application/views/partial/main/sidebar.php`](file:///c:/laragon/www/karismaerp/application/views/partial/main/sidebar.php) |
| Hook Proteksi URL | [`application/hooks/Dashboard_access_guard.php`](file:///c:/laragon/www/karismaerp/application/hooks/Dashboard_access_guard.php) |
| Registrasi Hook | [`application/config/hooks.php`](file:///c:/laragon/www/karismaerp/application/config/hooks.php) |
| Registrasi Rute | [`application/config/routes.php`](file:///c:/laragon/www/karismaerp/application/config/routes.php) |
| Redirect Pasca-Login | [`application/controllers/auth.php`](file:///c:/laragon/www/karismaerp/application/controllers/auth.php), [`application/controllers/Dashboard.php`](file:///c:/laragon/www/karismaerp/application/controllers/Dashboard.php) |

---

## 4. Cara Penggunaan (Untuk Admin)
1. Login menggunakan akun **Admin** (`username: admin` atau level `99`).
2. Di halaman **Dashboard** tab **ADMIN**, klik menu **Akses Menu Dashboard** (atau melalui sidebar **Master User → Akses Menu Dashboard**), URL: `/master/akses-dashboard`.
3. Pilih user di panel kiri (misalnya `vita`).
4. Di panel kanan, gunakan pencarian modul atau tab departemen untuk menemukan menu yang ingin dibatasi (misalnya **Retur Pembelian**), lalu matikan toggle switch-nya menjadi **Terkunci**.
5. Perubahan tersimpan otomatis. Saat user tersebut membuka `/dashboard`, menu **Retur Pembelian** di semua tab akan menampilkan **ikon gembok** dan akses URL langsung ke `/ics/retur` otomatis diblokir dengan halaman **Akses Modul Terkunci (403)**.

