# Database - ICS ICSPo Purchasing Status Icons

Tanggal: 2026-07-18

## Kesimpulan

Tidak ada perubahan struktur database.

## Field yang Dipakai

Tabel Purchasing pada route `ics/icspo` memakai field existing:

- `tbpo_po.tgl_transaksi` untuk `Tgl PO`.
- `tb_lpb.no_invoice` untuk status invoice.
- `tb_lpb.tanggal_invoice` untuk `Tgl Invoice` jika kolom tersedia.
- `tb_lpb.kode_faktur_pajak` untuk status faktur pajak.
- `tb_lpb.tanggal_faktur_pajak` untuk `Tgl Faktur` jika kolom tersedia.
- `tb_lpb_detail.harga_verified_at` untuk status verifikasi harga.

## Dampak Schema

- Tidak ada tabel baru.
- Tidak ada kolom baru.
- Tidak ada perubahan indeks.
- Tidak ada migrasi SQL.

Perubahan hanya menampilkan data existing dengan urutan kolom dan indikator status baru.
