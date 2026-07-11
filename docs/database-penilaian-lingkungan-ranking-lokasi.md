# Database - Ranking Nilai Lokasi Penilaian Lingkungan

## Ringkasan

Tidak ada tabel baru dan tidak ada kolom baru. Fitur ranking nilai lokasi memakai struktur tabel yang sudah tersedia:

- `tbhrd_environment_issues.location_id`
- `tbhrd_environment_issues.rating_id`
- `tbhrd_environment_issues.star_rating`
- `tbhrd_issue_rating.id`
- `tbhrd_issue_rating.score`
- `tbhrd_lokasi.id`
- `tbhrd_lokasi.name`
- `tbhrd_issue_evidences.issue_id`

## Pemisahan Makna Data

`tbhrd_issue_rating.score` memiliki dua konteks lama yang harus dipisahkan:

- Pada laporan issue, score adalah level prioritas penyelesaian. Nilai `1` berarti high dan `5` berarti low.
- Pada penilaian lingkungan, nilai lokasi disimpan di `tbhrd_environment_issues.star_rating`.

Karena itu ranking lokasi tidak boleh dihitung dari semua `rating_id/score` laporan.

## Rumus Data Ranking

Nilai penilaian per baris dihitung dengan aturan:

1. Pakai `tbhrd_environment_issues.star_rating` jika bernilai 1 sampai 5.
2. Untuk kompatibilitas data lama, pakai `tbhrd_issue_rating.score` hanya jika baris tidak memiliki evidence/foto.
3. Baris laporan issue yang memiliki evidence/foto tidak ikut ranking nilai lokasi.

Rumus inti:

```sql
CASE
    WHEN e.star_rating BETWEEN 1 AND 5 THEN e.star_rating
    WHEN NOT EXISTS (
        SELECT 1
        FROM tbhrd_issue_evidences ev
        WHERE ev.issue_id = e.id
    )
    AND r.score BETWEEN 1 AND 5 THEN r.score
    ELSE NULL
END
```

Rata-rata lokasi memakai `AVG()` dari rumus tersebut. Ranking 1 sampai 5 memakai `ROUND()` dari rata-rata.

## Migration

Tidak ada migration wajib.

Tidak disediakan backfill dari `tbhrd_issue_rating.score` ke `tbhrd_environment_issues.star_rating`, karena data prioritas issue tidak boleh diubah menjadi nilai penilaian lokasi.

## Route Data

Modul all data penilaian memakai endpoint aplikasi `hrd/penilaian_lingkungan/penilaian-list`. Endpoint ini tidak menambah struktur database; hanya membaca data penilaian dengan filter `assessment_only`.

## Catatan Operasional

Data nilai baru harus masuk dari tab `Penilaian Lingkungan`, karena tab tersebut mengirim `star_rating`. Tab `Laporan Issue` tetap memakai `rating_id` sebagai level prioritas penyelesaian dan menyimpan `star_rating = 0`.
