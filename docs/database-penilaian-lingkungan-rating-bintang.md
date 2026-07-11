# Database - Rating Bintang Penilaian Lingkungan

## Ringkasan

Tab `Penilaian Lingkungan` memakai tabel baru yang terpisah dari laporan issue:

- `tbhrd_nilai_lingkungan.location_id`
- `tbhrd_nilai_lingkungan.rating_id`
- `tbhrd_nilai_lingkungan.star_rating`
- `tbhrd_nilai_lingkungan.description`
- `tbhrd_nilai_lingkungan.report_datetime`
- `tbhrd_nilai_lingkungan.status_id`
- `tbhrd_issue_rating.id`
- `tbhrd_issue_rating.score`

Tab `Penilaian Lingkungan` menyimpan nilai bintang utama melalui `tbhrd_nilai_lingkungan.star_rating`. Kolom `rating_id` tetap diisi agar kompatibel dengan master `tbhrd_issue_rating`.

Tab `Laporan Issue` tetap menyimpan laporan pada `tbhrd_environment_issues` dan tidak lagi mengirim kolom `star_rating`.

## Migration

File SQL:

`database/sql/penilaian_lingkungan_rating_bintang.sql`

Migration ini melakukan:

1. Membuat tabel `tbhrd_nilai_lingkungan` jika belum ada.
2. Menambahkan index dan foreign key untuk lokasi, rating, status, bintang, dan tanggal laporan.
3. Menormalkan master rating menjadi:

| id | name | score |
| --- | --- | --- |
| 1 | Nilai 1 | 1 |
| 2 | Nilai 2 | 2 |
| 3 | Nilai 3 | 3 |
| 4 | Nilai 4 | 4 |
| 5 | Nilai 5 | 5 |

Script bersifat idempotent sehingga aman dijalankan ulang pada local, staging, atau production.

## Cara Menjalankan

Contoh lokal XAMPP:

```powershell
Get-Content database/sql/penilaian_lingkungan_rating_bintang.sql | & C:\xampp\mysql\bin\mysql.exe -u root kiucoid_karismaerp_local
```

## Catatan

Kolom `tbhrd_nilai_lingkungan.star_rating` menyimpan angka bintang langsung untuk penilaian lokasi. Kolom `rating_id` tetap menyimpan id master rating.
