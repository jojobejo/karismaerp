# Development Aplikasi - Rating Bintang Penilaian Lingkungan

## Ringkasan

Route `penilaian_lingkungan` merender form mobile `application/views/content/mobile_erp/form_laporan.php`. Form ini sekarang memiliki input penilaian 5 bintang sebelum lokasi laporan.

## Perubahan

- Menambahkan dua tab input setelah header form: `Laporan Issue` dan `Penilaian Lingkungan`.
- Tab `Laporan Issue` menampilkan lokasi, deskripsi issue, bukti foto, dan tombol `Kirim Laporan`.
- Tab `Penilaian Lingkungan` menampilkan lokasi, penilaian bintang, deskripsi penilaian, dan tombol `Kirim Penilaian`.
- Menambahkan komponen 5 bintang pada form laporan lingkungan.
- Teks awal `Pilih nilai` berubah menjadi angka `1` sampai `5` sesuai bintang yang diklik.
- Saat bintang ke-5 diklik, bintang 1 sampai 5 menjadi emas.
- Saat bintang ke-4 diklik, bintang 1 sampai 4 menjadi emas.
- Nilai yang dikirim ke server memakai `rating_id` dan `star_rating`, mengikuti master `tbhrd_issue_rating`.
- Submit laporan ditolak jika user belum memilih bintang.
- Controller `hrd/C_Hrd::submit_environment_issue()` memvalidasi `rating_id` dan memastikan score rating berada pada rentang 1 sampai 5.
- Jika form yang dikirim adalah tab `Penilaian Lingkungan`, data disimpan ke `tbhrd_nilai_lingkungan`.
- Jika form yang dikirim adalah tab `Laporan Issue`, data tetap disimpan ke `tbhrd_environment_issues`.

## File Terkait

- `application/views/content/mobile_erp/form_laporan.php`
- `assets/css/mobile-erp.css`
- `assets/js/mobile-erp.js`
- `application/controllers/hrd/C_Hrd.php`
- `application/models/M_Hrd.php`

## Cara Pakai

1. Buka route `penilaian_lingkungan`.
2. Pada bagian `Penilaian Lingkungan`, klik salah satu bintang.
3. Pastikan label di kanan berubah dari `Pilih nilai` menjadi angka yang dipilih.
4. Isi lokasi dan deskripsi penilaian.
5. Klik `Kirim Penilaian`.

Data penilaian tersimpan pada `tbhrd_nilai_lingkungan`.
