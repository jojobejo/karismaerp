# Development Master Barang - Filter Kelompok Barang

Tanggal: 2026-07-18

## Tujuan

Menambahkan filter `Kelompok Barang` pada card daftar barang route `master_barang`, sejajar dengan kolom `Search`, agar user dapat mempersempit daftar barang berdasarkan master `tbkeu_kelompok_dagang`.

## Scope Implementasi

1. Route terdampak:
   - `master_barang`
   - alias `purchase/listBarang`
2. Controller:
   - `application/controllers/keuangan/C_Keuangan.php`
   - view menerima `kelompok_barang_filter_options` dari data `tbkeu_kelompok_dagang`.
   - endpoint list membaca POST `kelompok_barang`.
3. Model:
   - `application/models/M_Keuangan.php`
   - query list dan count filtered menerima parameter filter dari dropdown `Kelompok`.
   - opsi filter memakai method existing `master_barang_kelompok_dagang_options()`.
   - filter diterapkan ke `tbpo_barang.kelompok_dagang` dengan nilai `tbkeu_kelompok_dagang.NOINDEX`.
4. View:
   - `application/views/content/keuangan/master_barang.php`
   - card `Daftar Barang` menampilkan select `Kelompok` sejajar dengan input `Search`.
5. AJAX:
   - `application/views/content/keuangan/ajax/ajax_master_barang.php`
   - perubahan nilai search atau filter akan memuat ulang daftar barang dengan parameter yang sama.

## Perilaku

Dropdown menampilkan `tbkeu_kelompok_dagang.DESKRIPSI`, sedangkan value yang dikirim adalah `tbkeu_kelompok_dagang.NOINDEX`. Filter memakai exact match ke `tbpo_barang.kelompok_dagang`. Search tetap berjalan seperti sebelumnya dan dapat dikombinasikan dengan filter. Angka pada label jumlah data memakai nilai `filtered`, sehingga mencerminkan hasil kombinasi search dan filter.
