# Database - Ranking Nilai Lokasi Penilaian Lingkungan

## Ringkasan

Fitur ranking nilai lokasi membaca tabel khusus penilaian:

- `tbhrd_nilai_lingkungan.location_id`
- `tbhrd_nilai_lingkungan.rating_id`
- `tbhrd_nilai_lingkungan.star_rating`
- `tbhrd_nilai_lingkungan.report_datetime`
- `tbhrd_nilai_lingkungan.status_id`
- `tbhrd_issue_rating.id`
- `tbhrd_issue_rating.score`
- `tbhrd_lokasi.id`
- `tbhrd_lokasi.name`

## Pemisahan Makna Data

`tbhrd_issue_rating.score` memiliki dua konteks lama yang harus dipisahkan:

- Pada laporan issue, score adalah level prioritas penyelesaian. Nilai `1` berarti high dan `5` berarti low.
- Pada penilaian lingkungan, nilai lokasi disimpan di `tbhrd_nilai_lingkungan.star_rating`.

Karena itu ranking lokasi tidak boleh dihitung dari semua `rating_id/score` laporan.

## Rumus Data Ranking

Nilai penilaian per baris dihitung dengan aturan:

1. Pakai `tbhrd_nilai_lingkungan.star_rating` jika bernilai 1 sampai 5.
2. Baris laporan issue di `tbhrd_environment_issues` tidak ikut ranking nilai lokasi.

Rumus inti:

```sql
AVG(n.star_rating)
```

Rata-rata lokasi memakai `AVG()` dari rumus tersebut. Ranking 1 sampai 5 memakai `ROUND()` dari rata-rata.

## Migration

Migration wajib untuk production yang belum memiliki tabel `tbhrd_nilai_lingkungan`:

`database/sql/penilaian_lingkungan_rating_bintang.sql`

Migration tersebut menambahkan:

- Tabel `tbhrd_nilai_lingkungan`
- Index `idx_tbhrd_nilai_lingkungan_location`
- Index `idx_tbhrd_nilai_lingkungan_rating`
- Index `idx_tbhrd_nilai_lingkungan_status`
- Index `idx_tbhrd_nilai_lingkungan_star`
- Index `idx_tbhrd_nilai_lingkungan_report_datetime`

Tidak disediakan backfill dari data issue lama ke `tbhrd_nilai_lingkungan`, karena data prioritas issue tidak boleh diubah menjadi nilai penilaian lokasi.

## Route Data

Modul all data penilaian memakai endpoint aplikasi `hrd/penilaian_lingkungan/penilaian-list`. Endpoint ini membaca data dari `tbhrd_nilai_lingkungan`.

## Catatan Operasional

Data nilai baru harus masuk dari tab `Penilaian Lingkungan`, karena tab tersebut mengirim `star_rating`. Tab `Laporan Issue` tetap memakai `rating_id` sebagai level prioritas penyelesaian di tabel issue dan tidak menyimpan nilai bintang.
