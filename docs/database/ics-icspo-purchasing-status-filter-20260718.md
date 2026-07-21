# Database - ICS ICSPo Purchasing Status Filter

Tanggal: 2026-07-18

## Kesimpulan

Tidak ada perubahan struktur database.

## Alasan

Filter status Purchasing di route `ics/icspo` memakai data existing yang sudah ditampilkan dan menyeleksi data yang belum lengkap:

- `tb_lpb.no_invoice`
- `tb_lpb.kode_faktur_pajak`
- agregasi `tb_lpb_detail.harga_verified_at`

## Dampak Schema

- Tidak ada tabel baru.
- Tidak ada kolom baru.
- Tidak ada indeks baru.
- Tidak ada migrasi SQL.

Perubahan hanya menambahkan filter client-side pada DataTables.
