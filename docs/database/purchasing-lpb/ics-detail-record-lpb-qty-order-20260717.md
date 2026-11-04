# Database Detail Record LPB Qty Order

Tanggal: 2026-07-17

## Perubahan Struktur

Tidak ada perubahan struktur database.

## Sumber Data

Kolom `Qty Order` pada route `ics/detail_record_lpb` dibaca dari tabel existing `tbpo_detail_po`:

- Prioritas: `qty_kecil`
- Fallback: `qty`

Tidak ada SQL migrasi yang perlu dijalankan untuk perubahan ini.
