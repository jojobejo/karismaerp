# Development Aplikasi - Ranking Nilai Lokasi Penilaian Lingkungan

## Ringkasan

Route `hrd/penilaian_lingkungan/admin` dikembangkan menjadi dashboard admin penilaian lingkungan per lokasi. Admin dapat melihat rata-rata nilai lokasi, ranking nilai 1 sampai 5, dan membuka detail nilai penilaian setiap lokasi.

Nilai penilaian dan prioritas issue dipisahkan:

- `Nilai` adalah hasil penilaian lokasi, dengan skala 1 sampai 5.
- `Prioritas issue` adalah level penyelesaian laporan, dengan makna `1` high dan `5` low.

## Perubahan

- Menambahkan KPI `Rata-rata` nilai penilaian pada dashboard admin.
- Menambahkan panel `Ranking Penilaian Lokasi` berisi lokasi, total penilaian, rata-rata nilai, ranking nilai 1 sampai 5, dan tombol `Detail`.
- Menambahkan tombol `All Data` pada panel ranking untuk redirect ke route `hrd/penilaian_lingkungan/semua-penilaian`.
- Menambahkan modul `All Data Penilaian Lingkungan` untuk melihat seluruh data nilai penilaian yang telah diinput.
- Tombol `Detail` membuka modal `Detail Penilaian Lokasi`.
- Modal detail hanya menampilkan data penilaian lokasi, bukan semua laporan issue.
- Endpoint statistik `hrd/penilaian_lingkungan/stats` mengirim `location_rankings`.
- Endpoint data all penilaian memakai route `hrd/penilaian_lingkungan/penilaian-list`.
- Halaman dan endpoint all penilaian hanya dapat diakses oleh Admin dan Superadmin.
- Query ranking memakai `tbhrd_environment_issues.star_rating` untuk data nilai penilaian.
- Data laporan issue yang memiliki evidence/foto tidak ikut ranking penilaian lokasi.
- Untuk kompatibilitas data penilaian lama yang belum mengisi `star_rating`, sistem hanya fallback ke `tbhrd_issue_rating.score` jika baris tersebut tidak memiliki evidence/foto.
- Form mobile tab `Penilaian Lingkungan` mengirim `star_rating` saat user memilih bintang.
- Form mobile tab `Laporan Issue` tidak mengirim nilai penilaian; `star_rating` disimpan `0`.

## File Terkait

- `application/controllers/hrd/C_Hrd.php`
- `application/config/routes.php`
- `application/models/M_Hrd.php`
- `application/views/content/hrd/penilaian_lingkungan/admin_dashboard.php`
- `application/views/content/hrd/penilaian_lingkungan/all_penilaian.php`
- `application/views/content/hrd/penilaian_lingkungan/js.php`
- `application/views/content/mobile_erp/form_laporan.php`
- `assets/js/mobile-erp.js`

## Cara Pakai

1. Buka route `hrd/penilaian_lingkungan/admin`.
2. Gunakan filter lokasi, status, dan tanggal jika dibutuhkan.
3. Lihat panel `Ranking Penilaian Lokasi`.
4. Klik tombol `All Data` untuk membuka route `hrd/penilaian_lingkungan/semua-penilaian`.
5. Gunakan filter lokasi, status, dan tanggal untuk menyaring all data penilaian.
6. Klik tombol `Detail` pada data penilaian untuk melihat dan memperbarui status/nilai jika diperlukan.

## Hak Akses

Route berikut hanya untuk Admin dan Superadmin:

- `hrd/penilaian_lingkungan/semua-penilaian`
- `hrd/penilaian_lingkungan/penilaian-list`

Validasi akses memakai session `lv = 1` atau `jobdesk = ADMIN/SUPERADMIN`.

## Catatan Validasi

Ranking dihitung dari rata-rata nilai bintang per lokasi. Nilai ranking yang ditampilkan adalah pembulatan rata-rata ke skala 1 sampai 5.
