# Development Dashboard Menu Retur

Tanggal: 2026-07-22

## Tujuan

Menambahkan tombol menu `Retur` di dashboard yang mengarah ke `ics/retur`, dan memastikan module retur dapat diakses oleh Logistik, Purchasing, Keuangan, dan IT.

## File Yang Diubah

- `application/models/M_Dashboard.php`
- `application/controllers/logistik/C_Ics.php`

## Perubahan Dashboard

Menu `Retur` ditambahkan pada section:

- `KEUANGAN`
- `LOGISTIK`
- `PURCHASING`
- `IT`

Route tombol:

```text
ics/retur
```

Model dashboard juga memfilter tombol `ics/retur` agar hanya tampil untuk user yang termasuk group akses Retur.

## Rule Akses Retur

Module retur dapat diakses jika user memiliki salah satu sinyal berikut:

- departemen mengandung `LOGISTIK`
- departemen mengandung `PURCHASING`
- departemen mengandung `KEUANGAN`
- departemen mengandung `FINANCE`
- departemen mengandung `ACCOUNTING`
- departemen mengandung `IT`
- jobdesk terkait Logistik, Purchasing, Keuangan/Accounting/Finance, atau IT
- user admin dashboard

Guard server-side dipasang di `C_Ics` untuk method:

- `dash_retur`
- `detail_retur`
- `retur_penjualan`
- `retur_pembelian`
- semua method dengan prefix `ajax_retur`

Jika tidak login, user diarahkan ke `Auth`. Jika login tetapi role tidak sesuai, sistem mengembalikan `403 Akses Ditolak` atau JSON error untuk request AJAX.
