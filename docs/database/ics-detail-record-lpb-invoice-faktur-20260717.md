# Struktur Database Detail Record LPB Invoice dan Faktur Pajak

Tanggal: 2026-07-17

## Tabel Terdampak

`tb_lpb`

## Kolom Baru

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `tanggal_invoice` | `DATE NULL` | Tanggal terbit invoice supplier pada header LPB. |
| `kode_faktur_pajak` | `VARCHAR(100) NULL` | Kode faktur pajak yang diinput melalui tombol Update Faktur. |
| `tanggal_faktur_pajak` | `DATE NULL` | Tanggal terbit faktur pajak. |

## SQL

Jalankan file:

`docs/database/ics-detail-record-lpb-invoice-faktur-20260717.sql`

SQL dibuat idempotent dengan `ADD COLUMN IF NOT EXISTS`, sehingga aman dijalankan ulang pada database yang kolomnya sudah tersedia.
