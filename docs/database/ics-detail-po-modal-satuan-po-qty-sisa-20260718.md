# Database - ICS Detail PO Modal Satuan PO dan Qty Sisa

Tanggal: 2026-07-18

## Kesimpulan

Tidak ada perubahan struktur database.

## Field yang Dipakai

Perubahan ini memakai field existing:

- `tbpo_detail_po.satuan` sebagai sumber satuan draft penerimaan.
- Alias existing `qty_kecil_sisa` tetap dipakai internal untuk menghitung sisa kuantitas, tetapi label user-facing ditampilkan sebagai `Qty Sisa`.

## Dampak Schema

- Tidak ada tabel baru.
- Tidak ada kolom baru.
- Tidak ada indeks baru.
- Tidak ada migrasi SQL.

Perubahan hanya membatasi input satuan agar mengikuti data PO dan menambah validasi server-side.
