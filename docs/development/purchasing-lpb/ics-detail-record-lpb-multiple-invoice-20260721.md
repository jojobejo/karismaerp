# Development - ICS Detail Record LPB Multiple Invoice

Tanggal: 2026-07-21

## Ringkasan

Module `ics/detail_record_lpb` ditambah fitur pecah LPB menjadi multiple invoice. Kebutuhan bisnisnya adalah satu LPB dari supplier dapat datang dengan lebih dari satu nomor invoice, sementara total kuantitas barang tetap sama.

Contoh:

- LPB `00123`, barang `abacel`, qty awal `100`
- Hasil split:
  - Invoice `01`, qty `50`
  - Invoice `02`, qty `50`

## Perubahan Aplikasi

File yang berubah:

- `application/config/routes.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

Route baru:

```php
$route['ics/ajax_split_lpb_multiple_invoice'] = 'logistik/C_Ics/ajax_split_lpb_multiple_invoice';
```

Controller baru:

- `C_Ics::ajax_split_lpb_multiple_invoice()`
- Hanya ADMIN PO/Purchasing yang bisa mengakses, mengikuti guard `reject_non_admin_po_ajax()`.
- Validasi status LPB harus `UNPOST`.
- Payload `splits` dikirim sebagai JSON dari modal view.

Model baru:

- `M_Logistik::split_lpb_multiple_invoice()`
- `M_Logistik::replace_lpb_detail_batch()`

## Kontrak Data

Invoice pertama tetap memakai `id_lpb` asal. Invoice kedua dan seterusnya dibuat sebagai header `tb_lpb` baru dengan:

- `nomor_lpb` sama dengan LPB asal
- `jenis_lpb` sama dengan LPB asal
- `nosj`, `tgl_sj`, `no_po`, `kd_po`, `gudang_id`, dan `keterangan` mengikuti LPB asal
- `no_invoice` dan `tanggal_invoice` mengikuti input masing-masing invoice
- `status_lpb = 0` atau `UNPOST`

Detail barang dibagi ke `tb_lpb_detail` sesuai qty input. Total qty seluruh hasil split per detail harus sama dengan qty awal detail LPB.

## Guard Penting

- Split hanya berjalan saat LPB asal `UNPOST`.
- Minimal 2 invoice.
- Nomor invoice tidak boleh duplikat dalam satu proses split.
- Setiap invoice harus memiliki qty barang lebih dari 0.
- Total qty split per barang harus sama dengan qty LPB awal.
- Metadata verifikasi harga (`harga_verified_by`, `harga_verified_at`) dikosongkan kembali karena qty detail berubah dan harus direkam/verifikasi ulang.

## UI

Tombol baru:

- Label: `Pecah Invoice`
- Posisi: panel aksi Purchasing pada `ics/detail_record_lpb`, sejajar dengan `Update Invoice` dan `Update Faktur`.
- Tombol disabled saat LPB berstatus `POST`.

Modal baru:

- `modalSplitInvoice`
- Default membuat 2 invoice.
- Default qty dibagi 50/50 untuk mempercepat case umum.
- User dapat menambah invoice dan mengubah matrix qty per barang.

## Catatan Implementasi

Split tidak menambah atau mengurangi total stok. Oleh karena itu `tberp_stock_batch` dan `tberp_stock_ledger` tidak ditambah ulang. Yang diperbarui adalah struktur detail LPB dan `tb_lpb_batch` agar batch mengikuti detail hasil split.

