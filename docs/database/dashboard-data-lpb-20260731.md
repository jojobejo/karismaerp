# Database - Dashboard Data LPB

Tanggal: 2026-07-31

## Status Database

Tidak ada perubahan struktur database.

## Catatan

Perubahan ini hanya menambah route, card dashboard, mode view, filter DataTables, dan rule tombol berbasis session user.

Sumber data halaman `ics/data_lpb` tetap memakai query existing `M_Logistik::get_lpb()` dengan tabel utama:

- `tbpo_po`
- `tbpo_detail_po`
- `tbpo_suplier`
- `tb_lpb`
- `tb_lpb_detail`

Tidak ada migration, `ALTER TABLE`, `INSERT`, atau update data master yang dibutuhkan untuk menjalankan halaman ini.
