# Penggunaan Master Barang - Kelompok Dagang

Tanggal: 2026-07-17

## Route

Buka route:

- `master_barang`
- atau alias lama `purchase/listBarang`

## Cara Input

1. Pilih barang dari daftar kiri atau klik tombol tambah data baru.
2. Pilih `Kelompok Dagang` pada area atas form, di posisi lama `Kelompok Barang`.
3. Buka tab `Informasi Barang`.
4. Isi `Bahan Aktif`.
5. Isi `Kelompok Barang` pada field tepat setelah `Bahan Aktif`.
6. Klik `Simpan`.

## Perilaku Dropdown

Dropdown `Kelompok Dagang` mengambil data dari tabel `tbkeu_kelompok_dagang`. User melihat nama kelompok seperti `Barang Dagangan BKP`, `Barang Dagangan`, atau `Barang Promosi`; aplikasi menyimpan kode `NOINDEX` kelompok tersebut ke `tbpo_barang.kelompok_dagang`.

## Catatan

Field `Kelompok Dagang` baru akan tersimpan setelah database menjalankan migration `docs/database/master_barang_kelompok_dagang_20260717.sql`. Opsi dropdown tersedia setelah database memiliki tabel master dari `docs/database/tbkeu_kelompok_dagang_20260717.sql`.
