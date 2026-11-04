# Database - ICS Detail PO Purchasing LPB List

Tanggal: 2026-07-31

## Status Migrasi

Tidak ada perubahan struktur database.

Perubahan ini hanya membaca data dari tabel yang sudah ada dan memakai helper query yang sudah tersedia di model logistik.

## Tabel yang Dibaca

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_po`
- `tbpo_suplier`
- `tb_lpb_log`
- `tb_lpb_batch`
- `tbso_faktur_detail`
- `tbkeu_jurnal`

## Sumber Status

- Lifecycle LPB:
  - `tb_lpb.status_lpb`
  - `NULL` atau kosong ditampilkan sebagai `DRAFT`
  - `0` ditampilkan sebagai `UNPOST`
  - selain `0` ditampilkan sebagai `TERPOSTING`
- Invoice:
  - `tb_lpb.no_invoice`
  - `tb_lpb.tanggal_invoice` bila kolom tersedia
  - Pada `ics/detail_po`, invoice dihitung sebagai jumlah `tb_lpb.no_invoice` distinct per `tb_lpb.nomor_lpb`.
- Faktur Pajak:
  - `tb_lpb.kode_faktur_pajak`
  - `tb_lpb.tanggal_faktur_pajak` bila kolom tersedia
- Afirmasi harga:
  - agregasi `tb_lpb_detail.harga_verified_at`
- Transaksi barang:
  - korelasi `tb_lpb_detail` / `tb_lpb_batch` ke `tbso_faktur_detail` berdasarkan barang, lot, dan expired date
- Jurnal LPB:
  - `tbkeu_jurnal` dengan `source_module = LOGISTIK`, `source_type = LPB_FINAL`, dan `posting_event = GOODS_RECEIPT`

## Catatan

Tidak diperlukan script SQL baru untuk deployment perubahan ini.

## Tampilan Detail PO

- Tidak ada kolom database baru.
- Grouping dilakukan di layer aplikasi berdasarkan `tb_lpb.nomor_lpb`.
- Kolom supplier tidak dihapus dari database; hanya tidak ditampilkan pada tabel `List Data LPB Yang Telah Direkam`.
