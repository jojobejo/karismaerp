# Database Master Barang - Filter Kelompok Barang

Tanggal: 2026-07-18

## Status Perubahan Database

Tidak ada perubahan struktur database dan tidak ada migration baru.

## Sumber Data

Filter `Kelompok Barang` mengambil opsi dari tabel master existing:

- tabel: `tbkeu_kelompok_dagang`
- value dropdown: `NOINDEX`
- label dropdown: `DESKRIPSI`

Data list difilter dengan exact match ke:

- tabel: `tbpo_barang`
- kolom: `kelompok_dagang`
- nilai pembanding: `tbkeu_kelompok_dagang.NOINDEX`

## Dampak Data

Tidak ada insert, update, delete, atau perubahan data lama. Opsi filter mengikuti master `tbkeu_kelompok_dagang`. Agar hasil filter muncul, data barang perlu memiliki nilai `tbpo_barang.kelompok_dagang` yang sesuai dengan `tbkeu_kelompok_dagang.NOINDEX`.
