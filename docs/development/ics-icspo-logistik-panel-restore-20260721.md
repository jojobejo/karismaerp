# Development - ICS ICSPo Restore Tab Logistik

Tanggal: 2026-07-21

## Scope

Route: `ics/icspo`

File aplikasi:

- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/icspo.php`

## Perubahan

Tab panel Logistik pada route `ics/icspo` dikembalikan ke tampilan standar sebelumnya.

Header tabel Logistik kembali menjadi:

`No PO | Tgl Transaksi | Kode Supplier | Nama Supplier | Total Barang Order | Total Barang Diterima | Progress | Input Terakhir | Status | #`

Tombol detail pada tab Logistik kembali mengarah ke:

`ics/detail_po`

Sumber data tab Logistik kembali memakai:

`M_Logistik::get_lpb()`

## Batasan Perubahan

Perubahan header baru:

`Tgl LPB | No LPB | No PO | Nama Supplier | Grand Total LPB | LPB Status | Status Data | #`

hanya berlaku untuk tab panel Purchasing.

Tab Logistik tidak memakai header Purchasing dan tidak memakai kolom `Grand Total LPB`, `LPB Status`, atau `Status Data`.
