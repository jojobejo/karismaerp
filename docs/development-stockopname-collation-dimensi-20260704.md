# Development Aplikasi - Fix Collation Lookup Dimensi Stockopname

Tanggal: 2026-07-04

## Latar Belakang

Halaman/modul stockopname mengalami error database:

`Illegal mix of collations (utf8mb4_general_ci,IMPLICIT) and (utf8mb4_uca1400_ai_ci,IMPLICIT) for operation '='`

Error terjadi saat daftar opsi master pending menjalankan query agregasi `stockopname_master_item` dan mengambil `dimensi` dari `tb_master_barang_all`.

## Perubahan Aplikasi

File yang diubah:

- `application/models/admin/M_Stockopname.php`

Detail perubahan:

- Menambahkan helper `kode_barang_match_sql($leftColumn, $rightColumn)`.
- Helper tersebut memaksa perbandingan kode barang memakai collation eksplisit `utf8mb4_general_ci`.
- Lookup dimensi dari `tb_master_barang_all.kd_barang` ke `stockopname_master_item.kode_barang` sekarang memakai helper tersebut.
- Sumber data dimensi tetap sama: prioritas dari `tb_master_barang_all.dimensi`, fallback ke kolom/rumus dimensi lokal di `stockopname_master_item`.

## Tata Cara Penggunaan Modul

Tidak ada perubahan alur penggunaan operator/admin.

Modul yang terdampak tetap digunakan seperti biasa:

- Buka menu stockopname yang memuat opsi master barang/pending.
- Sistem mengambil data master dari `stockopname_master_item`.
- Nilai dimensi otomatis dicari dari `tb_master_barang_all` berdasarkan kode barang.
- Jika tidak ditemukan di master barang all, sistem memakai fallback dimensi dari data stockopname.

## Validasi

Validasi yang sudah dilakukan:

- `C:\xampp\php\php.exe -l application/models/admin/M_Stockopname.php`
- Menjalankan query SELECT agregasi dengan perbandingan `COLLATE utf8mb4_general_ci`; query berhasil mengembalikan data.

## Catatan Teknis

Patch ini adalah fix defensif di layer aplikasi agar query tetap berjalan saat dua tabel memakai collation berbeda. Strategi jangka panjang tetap disarankan di layer database: samakan collation kolom kode barang yang sering dibandingkan.
