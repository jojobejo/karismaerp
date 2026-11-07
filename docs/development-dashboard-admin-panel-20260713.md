# Development Dashboard Admin Panel

Tanggal: 2026-07-13

## Tujuan

Route `dashboard/` ditambah tab panel baru `ADMIN` untuk akun admin. Tab ini menjadi pusat kendali admin yang berisi master akses dan seluruh modul aplikasi yang sudah tersedia pada dashboard awal.

## Perubahan Aplikasi

1. `application/models/M_Dashboard.php`
   - Menambahkan tab `ADMIN` saat konteks user dikenali sebagai admin dashboard.
   - Menjadikan tab `ADMIN` sebagai tab aktif default untuk user admin.
   - Menambahkan menu master admin:
     - `User Management` ke route `master/user-management`
     - `Jobdesk` ke route `master/jobdesk`
     - `Akses Level` ke route `master/akses-level`
     - `Menu Aplikasi` ke route `master/menu`
   - Menggabungkan semua menu dari tab `KEUANGAN`, `HRD`, `LOGISTIK`, `PURCHASING`, dan `SALES` ke dalam tab `ADMIN`.
   - Membaca menu aktif dari tabel `tb_menu` jika tabel tersedia, sehingga menu dinamis aplikasi ikut tampil di katalog admin.
   - Mendukung variasi nama kolom menu dinamis seperti `url`/`url_menu` dan `icon`/`icon_menu`.
   - Duplikasi route pada katalog admin dihindari agar satu modul tidak tampil berulang jika dipakai oleh beberapa tab.

2. `application/views/content/dashboard/index.php`
   - Tidak membutuhkan perubahan struktur view karena tab dan panel sudah dirender dinamis dari `dashboard_sections`.

## Cara Pakai

1. Login dengan akun `admin` atau akun yang memenuhi konteks admin dashboard.
2. Buka route `dashboard/`.
3. Tab `ADMIN` akan tampil sebagai tab pertama dan aktif otomatis.
4. Klik kartu menu pada tab `ADMIN` untuk masuk ke master admin atau modul aplikasi terkait.
5. Tab divisi lama tetap tersedia untuk akses per area kerja.

## Catatan Bisnis

Tab `ADMIN` diposisikan sebagai command center aplikasi. Admin tidak perlu berpindah antar tab divisi untuk mencari modul utama, dan menu dinamis dari database tetap bisa ikut terbaca. Struktur tab divisi dipertahankan agar pengguna operasional tetap punya jalur kerja yang familiar.

## Validasi

Validasi teknis dilakukan dengan lint PHP pada file model dashboard.
