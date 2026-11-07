# Penggunaan Master Barang - Filter Kelompok Barang

Tanggal: 2026-07-18

## Route

Buka route:

- `master_barang`
- atau alias `purchase/listBarang`

## Cara Menggunakan Filter

1. Lihat card `Daftar Barang` di sisi kiri.
2. Gunakan kolom `Search` untuk mencari kode, nama, atau supplier.
3. Pilih dropdown `Kelompok` untuk memfilter daftar berdasarkan master `tbkeu_kelompok_dagang`.
4. Search dan dropdown `Kelompok` dapat dipakai bersamaan.
5. Pilih salah satu barang dari daftar hasil filter untuk membuka detail di form kanan.

## Catatan

Dropdown `Kelompok` tidak di-hardcode di view. Opsi diambil dari `tbkeu_kelompok_dagang.DESKRIPSI`, lalu sistem memfilter barang berdasarkan nilai `tbpo_barang.kelompok_dagang` yang sama dengan `tbkeu_kelompok_dagang.NOINDEX`.
