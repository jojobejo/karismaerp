# Database tbkeu_kelompok_dagang

Tanggal: 2026-07-17

## Status Perubahan Database

Perubahan database disiapkan sebagai migration manual pada:

`docs/database/tbkeu_kelompok_dagang_20260717.sql`

Migration membuat tabel baru:

- tabel: `tbkeu_kelompok_dagang`
- engine: `InnoDB`
- charset/collation: `utf8mb4` / `utf8mb4_general_ci`
- primary key: `NOINDEX`
- mode seed: idempotent memakai `ON DUPLICATE KEY UPDATE`

## Struktur Kolom

Kolom mengikuti struktur pada screenshot yang diberikan user:

- `NOINDEX` int(11) not null
- `DESKRIPSI` varchar(150) null
- `KODESALES` varchar(20) null
- `KODEINVENTORI` varchar(20) null
- `KODEHARGAPOKOK` varchar(20) null
- `KODEKONSINYASI` varchar(20) null
- `DEPT` varchar(20) null
- `KODERETUR` varchar(20) null
- `GUDANG` varchar(50) null
- `INVENTORI` char(1) null
- `BELI` char(1) null
- `JUAL` char(1) null
- `SISTEM` tinyint(1) not null default 0
- `TANGGALEDIT` datetime null
- `IMAGE` varchar(255) null
- `KODEPENGIRIMANBELI` varchar(20) null
- `KODEPENGIRIMANJUAL` varchar(20) null

## Data Awal

Migration mengisi 5 baris awal sesuai screenshot:

- `0` - `N/A`
- `1` - `Barang Dagangan BKP`
- `3` - `Barang Dagangan BKPS`
- `4` - `Barang Dagangan`
- `5` - `Barang Promosi`

## Dampak Data

Tidak ada tabel lama yang diubah. Tabel baru berdiri sendiri sebagai master kelompok dagang keuangan dan belum dihubungkan ke flow aplikasi.

## Rollback

```sql
DROP TABLE `tbkeu_kelompok_dagang`;
```
