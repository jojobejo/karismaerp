# Development Detail Record LPB - Tombol Purchasing

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

Menambahkan tombol `Purchasing` pada header card `Detail LPB`, tepat sebelum tombol `Update Invoice`.

Saat tombol diklik, area header dan tabel `Detail LPB` berubah menjadi view purchasing. Tombol yang sama berubah menjadi `Data LPB` untuk kembali ke tampilan detail LPB.

Data purchasing mengikuti LPB yang sedang dipilih. Jika LPB hanya memiliki 1 barang diterima, view purchasing hanya menampilkan 1 barang tersebut, lalu melengkapi harga exclude dari data PO yang ada di database.

Header purchasing dibuat ringkas:

- `Jenis LPB` digabung dalam box `Nomor / Jenis LPB`.
- `Total Item`, `Qty LPB`, `Total Harga Exclude`, dan indikator `Harga Exclude` disembunyikan dari summary header.

## Header Tabel Purchasing

- `No`
- `Kode Barang`
- `Nama Barang`
- `No Lot`
- `Expired Date`
- `Qty Sisa`
- `Qty LPB`
- `Total Harga`
- `Harga Satuan`

## Aturan Harga

- `Harga Satuan` selalu memakai harga exclude dari `tbpo_detail_po`.
- Prioritas harga satuan:
  - `harga_satuan_kecil_exclude`
  - `harga_satuan_exclude`
  - `0` jika harga exclude tidak tersedia
- `Total Harga` dihitung dari `Qty LPB x Harga Satuan Exclude`.
- Harga include seperti `hrg_satuan` tidak dipakai pada modal purchasing.

## Format Tanggal

- Tanggal: `dd/mm/yyyy`
- Tanggal beserta jam: `dd/mm/yyyy hh:mm`

## File Aplikasi

- `application/config/routes.php`
  - Menambah route `ics/ajax_get_purchasing_po_detail`.
- `application/controllers/logistik/C_Ics.php`
  - Menambah endpoint `ajax_get_purchasing_po_detail`.
  - Endpoint menerima `id_lpb` dan mengembalikan summary serta rows purchasing untuk LPB terpilih.
- `application/models/M_Logistik.php`
  - Menambah query `get_purchasing_lpb_detail_rows()`.
- `application/views/content/logistik/ics/detail_record_lpb.php`
  - Menambah tombol `Purchasing`.
  - Menambah switch view `Purchasing` / `Data LPB` pada panel Detail LPB.
  - Menggabungkan `Jenis LPB` dengan `Nomor LPB`.
  - Menyembunyikan `Input At` dari view Data LPB.
  - Menyembunyikan summary purchasing: `Total Item`, `Qty LPB`, `Total Harga Exclude`, dan `Harga Exclude`.
  - Menambah format tanggal tampilan `dd/mm/yyyy` dan `dd/mm/yyyy hh:mm`.

## Database

Tidak ada perubahan schema database.
