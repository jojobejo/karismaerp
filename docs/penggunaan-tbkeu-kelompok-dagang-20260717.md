# Penggunaan tbkeu_kelompok_dagang

Tanggal: 2026-07-17

## Tujuan

Tabel `tbkeu_kelompok_dagang` menyimpan master kelompok dagang beserta kode akun terkait penjualan, inventori, harga pokok, konsinyasi, retur, dan pengiriman.

## Cara Menjalankan Migration

Jalankan file berikut pada database KarismaERP:

`docs/database/tbkeu_kelompok_dagang_20260717.sql`

Contoh via XAMPP lokal:

```powershell
Get-Content docs/database/tbkeu_kelompok_dagang_20260717.sql | & 'C:\xampp\mysql\bin\mysql.exe' -uroot kiucoid_karismaerp_local
```

## Cara Cek Data

```sql
SELECT * FROM `tbkeu_kelompok_dagang` ORDER BY `NOINDEX`;
```

## Catatan Penggunaan Aplikasi

Tabel ini menjadi sumber dropdown `Kelompok Dagang` pada route:

- `master_barang`
- `purchase/listBarang`

Saat user memilih kelompok untuk sebuah barang, aplikasi menyimpan `NOINDEX` kelompok ke kolom `tbpo_barang.kelompok_dagang`.
