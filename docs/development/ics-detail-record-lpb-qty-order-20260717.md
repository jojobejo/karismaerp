# Development Detail Record LPB Qty Order

Tanggal: 2026-07-17

## Route

`ics/detail_record_lpb`

## Perubahan Aplikasi

- Menambahkan kolom `Qty Order` pada tabel detail LPB.
- Posisi kolom baru ditempatkan sebelum `Qty LPB`.
- Nilai `Qty Order` diambil dari detail PO: prioritas `tbpo_detail_po.qty_kecil`, fallback ke `tbpo_detail_po.qty`.

## Tampilan Tabel Terbaru

`Kode Barang | Nama Barang | No Lot | Expired Date | Qty Order | Qty LPB | Total Harga | Harga Satuan | #`
