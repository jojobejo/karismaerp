# Development - ICS ICSPo Header Purchasing dan Status Data

Tanggal: 2026-07-21

## Scope

Route: `ics/icspo`

File aplikasi:

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/icspo.php`

## Perubahan

Tabel Purchasing pada route `ics/icspo` disederhanakan menjadi header:

`Tgl LPB | No LPB | No PO | Nama Supplier | Grand Total LPB | LPB Status | Status Data | #`

Kolom operasional seperti `Jenis PO`, `Nomor SJ`, `Tanggal SJ`, `Invoice`, `Tgl Invoice`, `Faktur Pajak`, dan `Tgl Faktur` tidak lagi ditampilkan sebagai kolom terpisah pada tabel Purchasing.

## Detail Implementasi

`M_Logistik::get_lpb_purchasing_view()` sekarang mengirim:

- `tgl_lpb` dari `tb_lpb.input_at`
- `grand_total_lpb` dari total `tb_lpb_detail.total_harga`
- `status_lpb` asli dari `tb_lpb.status_lpb`

View `icspo.php` merender `LPB Status` sebagai:

- `DRAFT` jika status kosong/null
- `UNPOST` jika `status_lpb = 0`
- `POST` jika `status_lpb = 1`

Kolom `Status Data` berisi tiga tombol icon:

- Icon invoice untuk status invoice
- Icon pajak untuk status faktur pajak
- Icon afirmasi harga untuk status verifikasi/afirmasi harga

Warna tombol:

- Hijau jika data sudah lengkap
- Abu-abu jika data belum lengkap

Filter `Belum Uang` diganti menjadi `Belum Afirmasi Harga`.

## Catatan

Perubahan hanya menyentuh tampilan tabel Purchasing dan query data pendukungnya.

Flow detail LPB, update invoice, update faktur pajak, update harga, Accept, POST, dan UNPOST tetap memakai route dan endpoint yang sudah ada.
