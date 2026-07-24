# Development - ICS ICSPo Header Purchasing LPB/SJ

Tanggal: 2026-07-24

## Scope

Route: `ics/icspo`

File aplikasi:

- `application/views/content/logistik/ics/icspo.php`

## Perubahan

Header tabel Purchasing pada route `ics/icspo` disesuaikan menjadi:

`Tgl LPB | NO LPB | Tgl PO | No PO | Tgl SJ | No SJ | No Invoice | No FP | Suplier | Grand Total | Status Data | Satatus Barang`

## Detail Implementasi

- Kolom `NO LPB` tetap menjadi akses menuju detail record LPB dengan membuka route `ics/detail_record_lpb`.
- Kolom `Tgl PO`, `Tgl SJ`, `No SJ`, `No Invoice`, dan `No FP` memakai data yang sudah tersedia dari `M_Logistik::get_lpb_purchasing_view()`.
- Kolom `Status Data` tetap menampilkan indikator invoice, faktur pajak, dan afirmasi harga.
- Kolom `Satatus Barang` menampilkan status lifecycle LPB dari `tb_lpb.status_lpb` serta indikator operasional sales/jurnal yang sebelumnya berada pada kolom `Notif`.
- Kolom aksi `#` tidak lagi ditampilkan agar struktur tabel mengikuti header yang diminta.

## Cara Penggunaan

1. Buka route `ics/icspo`.
2. Masuk ke panel `Purchasing` jika user memiliki akses panel tersebut.
3. Klik nilai pada kolom `NO LPB` untuk membuka detail record LPB.
4. Gunakan ikon pada `Status Data` dan `Satatus Barang` untuk membaca kelengkapan data dan status operasional LPB.

## Validasi

- Jalankan lint PHP untuk view:

`C:\xampp\php\php.exe -l application/views/content/logistik/ics/icspo.php`

