# Development - Dashboard Data LPB

Tanggal: 2026-07-31

## Scope

Route:

- `dashboard`
- `ics/data_lpb`
- `ics/icspo`

File aplikasi:

- `application/models/M_Dashboard.php`
- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/icspo.php`
- `application/config/routes.php`
- `application/modules/kiupo/routes.php`

## Perubahan

- Menambahkan card module `Data LPB` pada dashboard section `LOGISTIK` dan `PURCHASING`.
- Menambahkan route baru `ics/data_lpb` yang membuka halaman khusus Data LPB tanpa tab panel.
- Halaman `ics/data_lpb` memakai sumber data `M_Logistik::get_lpb()` agar sajian sama dengan tabel penerimaan PO/LPB yang sudah berjalan.
- Pada halaman baru, kolom `Kode Supplier` dan `Input Terakhir` disembunyikan.
- Menambahkan filter status sajian data: `Semua`, `Belum`, `Partial`, dan `Done`.
- Rule tombol halaman Data LPB:
  - Akun ADMLPB (`ADMLPB`, `ADMINLOGLPB`, `ADMLPB2`, atau username sejenis) melihat tombol kembali dashboard berbentuk icon saja, tombol `Retur`, dan tombol `Laporan LPB`.
  - Akun Purchasing/Admin tetap melihat tombol `Input LPB Manual`, `Retur`, dan `Laporan LPB`.
- Route `ics/icspo` lama tetap dipertahankan untuk halaman gabungan PO/LPB dan panel Purchasing.

## Cara Penggunaan

1. Login ke aplikasi.
2. Buka route `dashboard`.
3. Klik card `Data LPB`.
4. Sistem membuka route `ics/data_lpb`.
5. Gunakan filter status untuk menyaring data LPB berdasarkan progress penerimaan barang:
   - `Belum`: belum ada qty diterima.
   - `Partial`: sebagian qty sudah diterima.
   - `Done`: qty diterima sudah memenuhi qty order.
6. Klik tombol `Detail` untuk membuka detail penerimaan PO.

## Validasi

- Lint PHP controller, model, view, dan route registry.
- Route baru sudah didaftarkan di dua registry CodeIgniter yang dipakai project.
