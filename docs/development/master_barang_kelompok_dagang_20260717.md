# Development Master Barang - Kelompok Dagang

Tanggal: 2026-07-17

## Tujuan

Menyesuaikan form route `master_barang` agar posisi field lebih sesuai alur input barang:

- posisi lama `Kelompok Barang` di bagian atas diganti menjadi field baru `Kelompok Dagang`;
- field `Kelompok Barang` dipindahkan ke tab `Informasi Barang`, tepat setelah `Bahan Aktif`.

## Scope Implementasi

1. Route yang terdampak:
   - `master_barang`
   - alias `purchase/listBarang`
2. Controller:
   - `application/controllers/keuangan/C_Keuangan.php`
   - payload master barang ditambah `kelompok_dagang`.
   - view menerima `kelompok_dagang_options` dari `tbkeu_kelompok_dagang`.
   - nilai `kelompok_dagang` divalidasi agar sesuai master dropdown.
3. Model:
   - `application/models/M_Keuangan.php`
   - query detail/list mengembalikan `kelompok_dagang`.
   - query detail/list mengembalikan `kelompok_dagang_label` dari `tbkeu_kelompok_dagang.DESKRIPSI`.
   - simpan/update `kelompok_dagang` dilakukan jika kolom sudah tersedia di `tbpo_barang`.
   - pencarian ikut membaca `kelompok_dagang` jika kolom sudah tersedia.
   - opsi dropdown diambil dari `tbkeu_kelompok_dagang`.
4. View:
   - `application/views/content/keuangan/master_barang.php`
   - field `Kelompok Dagang` ditempatkan pada posisi lama `Kelompok Barang` sebagai dropdown.
   - field `Kelompok Barang` dipindah setelah `Bahan Aktif`.
5. AJAX:
   - `application/views/content/keuangan/ajax/ajax_master_barang.php`
   - populate form, readonly state, dan submit data sudah membawa `kelompok_dagang`.
   - daftar kiri menampilkan label kelompok dagang agar plotting barang mudah dipantau.

## Kontrak Dropdown

Dropdown menampilkan `tbkeu_kelompok_dagang.DESKRIPSI`, tetapi nilai yang disimpan ke `tbpo_barang.kelompok_dagang` adalah `tbkeu_kelompok_dagang.NOINDEX`. Keputusan ini membuat plotting barang stabil ke master kelompok dagang dan tidak bergantung pada teks deskripsi yang bisa berubah.

## Catatan Kompatibilitas

Aplikasi dibuat aman untuk database yang belum menjalankan migration. Jika kolom `kelompok_dagang` belum ada, model mengembalikan nilai kosong dan tidak memaksa insert/update ke kolom tersebut. Jika tabel `tbkeu_kelompok_dagang` belum ada, dropdown akan kosong dan validasi tidak memblokir data lama. Setelah migration dijalankan, data `Kelompok Dagang` langsung tersimpan sebagai `NOINDEX`.
