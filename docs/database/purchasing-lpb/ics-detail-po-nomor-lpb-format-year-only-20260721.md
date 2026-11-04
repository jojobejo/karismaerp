# Database ICS - Format Nomor LPB Tanpa Angka Bulan Depan

Tanggal: 2026-07-21

## Ringkasan

Tidak ada perubahan struktur database untuk update format nomor LPB ini.

## Tabel yang Digunakan

- `tb_lpb`
  - Kolom `jenis_lpb` tetap menjadi acuan jenis LPB.
  - Kolom `nomor_lpb` tetap menjadi tempat penyimpanan nomor final LPB.

## Dampak Data

Data lama dengan format nomor seperti `72600001`, `72600001B`, atau `A72600001` tidak diubah oleh update ini.

Nomor baru yang digenerate setelah update memakai format tahun 2 digit + nomor urut 5 digit, misalnya `2600001`.

Generator masih membaca data lama untuk menentukan urutan berikutnya, sehingga tidak diperlukan update massal nomor LPB lama.

## SQL Migration

Tidak ada SQL migration baru.
