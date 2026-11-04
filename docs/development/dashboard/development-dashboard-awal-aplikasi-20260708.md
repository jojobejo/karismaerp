# Development Dashboard Awal Aplikasi

Tanggal: 2026-07-08

## Tujuan

Dashboard awal setelah login dibuat sebagai pintu masuk seluruh user. Semua redirect login sekarang masuk ke route `dashboard`, lalu controller `Dashboard` dan model `M_Dashboard` menyiapkan daftar modul sesuai rancangan rules akses per user dan level.

## Perubahan Aplikasi

1. `application/controllers/Auth.php`
   - Redirect login setelah berhasil dibuat default ke route `dashboard`.
   - Redirect khusus per `jobdesk`, `default_redirect`, dan role lama tidak lagi menjadi pintu awal setelah login.

2. `application/controllers/Dashboard.php`
   - Menjadi satu pintu controller dashboard semua user.
   - Memuat model `M_Dashboard`.
   - Memuat view `content/dashboard/index.php`.

3. `application/models/M_Dashboard.php`
   - Model baru untuk konteks user login, daftar tab modul, daftar menu, default tab aktif, dan titik pusat rules akses.
   - Rules detail per user dan level disiapkan di method `apply_access_rules()`.
   - Tab utama yang disiapkan: `KEUANGAN`, `HRD`, `LOGISTIK`, `PURCHASING`, dan `SALES`.

4. `application/config/routes.php`
   - Route `dashboard` tetap mengarah ke `Dashboard/index`.
   - Ditambahkan alias `dashboad` ke `Dashboard/index` untuk toleransi penulisan route.

5. `application/views/content/dashboard/index.php`
   - View dashboard utama tanpa sidebar kiri.
   - Menggunakan CSS grid, Font Awesome, jQuery, dan JavaScript sederhana untuk hover state.
   - Menampilkan tab panel modul.
   - Saat tab `LOGISTIK` dipilih, menu logistik/ICS ditampilkan.
   - Tab lain sudah disiapkan dengan menu awal untuk pengembangan rules berikutnya.
   - Ukuran semua kotak dibuat rata agar grid tidak meninggalkan kolom kosong.

## Daftar Route Menu Logistik

| Menu | Route |
| --- | --- |
| Data All Barang | `ics/by_allbarang` |
| Data By Expired Date | `ics/by_expdate` |
| Data DO | `ics/icsdo` |
| Master Gudang | `ics/gudang` |
| Data PO | `ics/icspo` |
| Master Barang PIC | `ics/barangpic` |
| Barang Per Gudang | `ics/barangpergudang` |
| Mutasi Barang Gudang | `ics/mutasi_barang` |
| Show Diffrent | `ics/ics_diffrent` |
| List Faktur Terkirim / Belum | `logistik/distibusi/list_faktur_status` |
| Export Data Expired Date | `export-stock` |

## Cara Pakai

1. Login dengan user aplikasi mana pun.
2. Sistem membuka route `dashboard`.
3. Pilih tab modul: `KEUANGAN`, `HRD`, `LOGISTIK`, `PURCHASING`, atau `SALES`.
4. Arahkan mouse ke kotak untuk melihat deskripsi singkat.
5. Klik kotak untuk masuk ke route modul terkait.

## Catatan Validasi

Perubahan ini mengubah alur awal login semua user menjadi dashboard. Route modul lama tetap dipertahankan sebagai tujuan dari menu dashboard.
