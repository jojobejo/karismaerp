# Development Detail Record LPB Invoice dan Faktur Pajak

Tanggal: 2026-07-17

## Route

`ics/detail_record_lpb`

## Perubahan Aplikasi

- Tombol `Purchasing` pada header detail LPB dihapus.
- Tombol `Update Invoice` hanya menampilkan `No Invoice` dan `Tanggal Terbit Invoice`.
- Tombol baru `Update Faktur` menampilkan form `Kode Faktur Pajak` dan `Tanggal Terbit Faktur Pajak`.
- Header card detail LPB menjadi: `Nomor / Jenis LPB`, `Status LPB`, `Nomor SJ`, `Tanggal SJ`, `No Invoice`, `Tgl Terbit Invoice`, `Faktur Pajak`, dan `Tanggal Terbit Faktur`.
- Kolom header `Gudang` dan `Keterangan` dikeluarkan dari header card detail.
- Tabel detail LPB menampilkan: `Kode Barang`, `Nama Barang`, `No Lot`, `Expired Date`, `Qty LPB`, `Total Harga`, `Harga Satuan`, dan `#`.
- Tombol `Rekam` tetap dipertahankan untuk verifikasi harga detail LPB.

## Endpoint Baru

`ics/ajax_update_faktur_pajak`

Payload:

- `id_lpb`
- `kode_faktur_pajak`
- `tanggal_faktur_pajak`

## Catatan Teknis

Model `M_Logistik::ensure_lpb_invoice_faktur_columns()` menjaga kolom baru tersedia bila belum ada di `tb_lpb`. SQL migrasi tetap disediakan di `docs/database` untuk deployment database yang eksplisit.
