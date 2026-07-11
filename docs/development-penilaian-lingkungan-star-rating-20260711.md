# Development Penilaian Lingkungan - Nilai 5 Bintang

Tanggal development: 2026-07-11

## Tujuan

Modul `penilaian_lingkungan` dikembangkan agar user dapat memberi nilai lingkungan memakai kontrol 5 bintang. Nilai yang diklik user dikirim bersama laporan dan disimpan di database sebagai angka `1` sampai `5`.

## Alur Penggunaan

1. User membuka route `penilaian_lingkungan`.
2. User memilih lokasi laporan.
3. User klik salah satu dari 5 bintang pada bagian `Penilaian lingkungan`.
4. Sistem menyesuaikan tampilan bintang aktif sesuai nilai yang diklik.
5. User mengisi deskripsi dan upload bukti foto.
6. Saat tombol `Kirim Laporan` ditekan, AJAX mengirim `star_rating` ke endpoint `hrd/penilaian_lingkungan/submit`.
7. Controller memvalidasi `star_rating` wajib angka `1` sampai `5`.
8. Data laporan disimpan ke tabel `tbhrd_environment_issues`.

## File Yang Diubah

- `application/controllers/hrd/C_Hrd.php`
  - Menambahkan validasi `star_rating`.
  - Menyimpan nilai bintang ke data insert laporan lingkungan.
- `application/models/M_Hrd.php`
  - Menambahkan field `star_rating` pada query list dan detail issue.
- `application/views/content/mobile_erp/form_laporan.php`
  - Menambahkan kontrol UI 5 bintang untuk form mobile route utama.
- `assets/js/mobile-erp.js`
  - Menambahkan handler klik bintang, validasi sebelum submit, reset nilai setelah sukses, dan tampilan bintang di list/detail mobile.
- `assets/css/mobile-erp.css`
  - Menambahkan styling kontrol bintang mobile.
- `application/views/content/hrd/penilaian_lingkungan/form.php`
  - Menambahkan kontrol UI 5 bintang pada form desktop lama.
- `application/views/content/hrd/penilaian_lingkungan/js.php`
  - Menambahkan styling, handler, validasi, dan tampilan nilai bintang di dashboard/monitoring desktop.
- `application/views/content/hrd/penilaian_lingkungan/admin_dashboard.php`
  - Menyesuaikan label kolom menjadi `Prioritas / Nilai`.
- `application/views/content/hrd/penilaian_lingkungan/monitoring.php`
  - Menyesuaikan label detail menjadi `Rating / Nilai saat ini`.

## Catatan Implementasi

Kolom lama `rating_id` tetap dipertahankan untuk kompatibilitas prioritas/dashboard yang sudah ada. Nilai klik bintang disimpan terpisah di `star_rating`, sehingga data prioritas lama tidak berubah.

## Update UI Bintang

Perubahan lanjutan:

- Bintang aktif memakai fill solid warna emas.
- Jika user klik bintang ke-5, bintang 1 sampai 5 menjadi emas.
- Jika user klik bintang ke-4, bintang 1 sampai 4 menjadi emas.
- Nilai `star_rating` selalu mengikuti bintang terakhir yang diklik.
- Teks `Pilih nilai` berubah menjadi angka rating yang dipilih, misalnya `4`.
- Saat bintang diklik, kontrol menampilkan animasi pop/glow singkat agar feedback klik lebih jelas.
