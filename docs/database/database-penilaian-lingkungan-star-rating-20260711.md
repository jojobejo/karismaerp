# Database Penilaian Lingkungan - Nilai 5 Bintang

Tanggal development: 2026-07-11

## Tujuan Struktur Database

Tabel laporan lingkungan perlu menyimpan nilai bintang yang dipilih user dari form `penilaian_lingkungan`. Nilai disimpan sebagai angka agar mudah dipakai untuk laporan, monitoring, atau rekap kualitas lingkungan.

## File SQL

Script migration tersedia di:

`database/sql/penilaian_lingkungan_star_rating.sql`

## Perubahan Tabel

Tabel: `tbhrd_environment_issues`

| Kolom | Tipe | Default | Fungsi |
| --- | --- | --- | --- |
| `star_rating` | `TINYINT(1)` | `0` | Nilai bintang dari user. Input baru divalidasi wajib `1` sampai `5`; nilai `0` hanya penanda data lama yang belum punya nilai bintang. |

## Index

| Index | Kolom | Tujuan |
| --- | --- | --- |
| `idx_tbhrd_environment_issues_star_rating` | `star_rating` | Mempercepat filter atau rekap laporan berdasarkan nilai bintang. |

## SQL Migration

```sql
ALTER TABLE `tbhrd_environment_issues`
    ADD COLUMN IF NOT EXISTS `star_rating` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rating_id`;

ALTER TABLE `tbhrd_environment_issues`
    ADD INDEX IF NOT EXISTS `idx_tbhrd_environment_issues_star_rating` (`star_rating`);
```

## Catatan Data Lama

Data laporan lama otomatis memiliki `star_rating = 0` setelah migration karena sebelumnya belum ada input nilai bintang. Data baru dari form wajib mengirim nilai `1` sampai `5`.
