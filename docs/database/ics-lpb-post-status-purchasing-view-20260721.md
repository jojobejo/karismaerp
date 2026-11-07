# Database - ICS LPB POST Status pada View Purchasing

Tanggal: 2026-07-21

## Scope Database

Perubahan ini tidak menambah tabel, tidak menambah kolom, dan tidak membutuhkan migration SQL.

## Tabel yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`

## Kontrak Data

- Status final LPB menggunakan `tb_lpb.status_lpb`.
- Nilai status:
  - `0`: `UNPOST`
  - `1`: `POST`
- Progress verifikasi harga tetap dibaca dari `tb_lpb_detail.harga_verified_at`.
- Status LPB dan progress verifikasi harga adalah dua konsep berbeda.

## Migration

Tidak ada migration SQL baru.
