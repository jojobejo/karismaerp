# Development - Global Topbar Navigation

Tanggal: 2026-07-31

## Ringkasan

Menambahkan topbar biru global seperti referensi dashboard pada seluruh halaman aplikasi yang memakai layout ERP utama dan modul KIU PO/Purchasing, dengan pengecualian halaman portal serta printout/cetak dokumen.

## File yang Diubah

- `application/views/partial/main/app_topbar.php`
  - Partial baru untuk topbar global.
  - Label kiri selalu mengarah ke `dashboard`.
  - Label kanan mengambil `departemen`/`departement` dari session, fallback ke `jobdesk`, lalu `KARISMAERP`.
  - Tombol logout mengarah ke route `logout`.
- `application/views/partial/main/navbar.php`
  - Menggunakan partial `app_topbar.php` agar semua view ERP utama yang memanggil `partial/main/navbar` otomatis memakai topbar baru.
- `application/modules/kiupo/views/partial/sidebar.php`
  - Mengganti navbar putih bawaan KIU PO dengan topbar global.
- `application/modules/kiupo/views/partial/sidebar_ics.php`
  - Mengganti navbar putih bawaan dashboard ICS KIU PO dengan topbar global.
- `application/modules/kiupo/views/partial/header.php`
  - Menambahkan CSS `assets/dist/css/karisma-custom.css` agar topbar global terbaca di layout KIU PO.
- `application/views/partial/main/headeripkp.php`
  - Menambahkan CSS `assets/dist/css/karisma-custom.css` untuk halaman IPKP.
- `application/views/content/dashboard/index.php`
  - Menggunakan topbar global dengan mode tanpa sidebar.
- `application/views/content/pelanggan/body.php`
  - Menambahkan topbar global dengan mode tanpa sidebar.
- `application/views/content/schedule/list_tamu_dirutadm.php`
- `application/views/content/list_tamu_dirut.php`
- `application/views/content/list_tamu_diruts.php`
  - Mengganti navbar putih khusus dengan topbar global.
- `application/views/content/extravaganza/undian/body.php`
  - Menambahkan topbar global dengan mode tanpa sidebar.
- `assets/dist/css/karisma-custom.css`
  - Menambahkan styling topbar biru, tombol dashboard, label modul, tombol logout, responsif mobile, dan hide pada media print.

## Cakupan

Halaman yang sudah memakai `partial/main/navbar` otomatis ikut berubah karena titik rendernya satu partial. Halaman KIU PO yang memakai `partial/sidebar` dan `partial/sidebar_ics` juga ikut berubah. Halaman `portal/index` tidak disentuh.

## Catatan Teknis

Pada halaman bersidebar, ikon kotak kiri tetap menjadi toggle sidebar AdminLTE melalui `data-widget="pushmenu"`. Pada halaman tanpa sidebar, ikon kiri menjadi link ke `dashboard`.
