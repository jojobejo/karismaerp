# Database - Rating Bintang Penilaian Lingkungan

## Ringkasan

Tidak ada kolom baru. Tab `Laporan Issue` dan `Penilaian Lingkungan` memakai struktur database yang sudah ada:

- `tbhrd_environment_issues.rating_id`
- `tbhrd_issue_rating.id`
- `tbhrd_issue_rating.score`

Tab `Penilaian Lingkungan` menyimpan nilai bintang melalui `tbhrd_environment_issues.rating_id`. Tab `Laporan Issue` tetap menyimpan laporan pada tabel yang sama dan memakai rating default score 5 secara tersembunyi agar kompatibel dengan foreign key lama.

## Migration

File SQL:

`database/sql/penilaian_lingkungan_rating_bintang.sql`

Migration ini menormalkan master rating menjadi:

| id | name | score |
| --- | --- | --- |
| 1 | Nilai 1 | 1 |
| 2 | Nilai 2 | 2 |
| 3 | Nilai 3 | 3 |
| 4 | Nilai 4 | 4 |
| 5 | Nilai 5 | 5 |

## Cara Menjalankan

Contoh lokal XAMPP:

```powershell
Get-Content database/sql/penilaian_lingkungan_rating_bintang.sql | & C:\xampp\mysql\bin\mysql.exe -u root kiucoid_karismaerp_local
```

## Catatan

Kolom `tbhrd_environment_issues.rating_id` tetap menyimpan id master rating, bukan angka bintang langsung. Angka bintang didapat dari join ke `tbhrd_issue_rating.score`.
