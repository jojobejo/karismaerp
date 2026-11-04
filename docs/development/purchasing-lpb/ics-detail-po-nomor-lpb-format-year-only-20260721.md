# Development ICS - Format Nomor LPB Tanpa Angka Bulan Depan

Tanggal: 2026-07-21

## Ringkasan

Generate `Nomor LPB` pada pilihan `Jenis PO` di route `ics/detail_po` diperbarui agar tidak lagi memakai angka bulan di depan format nomor.

Sebelumnya periode nomor memakai format:

- `bulan tanpa nol depan` + `tahun 2 digit` + `nomor urut 5 digit`
- Contoh Juli 2026: `72600001`

Setelah update, periode nomor memakai format:

- `tahun 2 digit` + `nomor urut 5 digit`
- Contoh 2026: `2600001`

## Perubahan Aplikasi

File yang diubah:

- `application/models/M_Logistik.php`

Fungsi yang terdampak:

- `M_Logistik::get_lpb_type_options()`
- `M_Logistik::generate_lpb_number()`

Format contoh pada pilihan jenis LPB ikut diperbarui:

- `LPB CP`: `2600001`
- `LPB Benih`: `2600001B`
- `LPB Konsinyasi`: `2600002K`
- `LPB Barang Non Pajak (A)`: `A2600001`
- `LPB Promosi`: `X2600001`
- `LPB Barang Pengganti Retur (RA)`: `RA2600001`

## Catatan Kompatibilitas

Generator nomor baru tetap membaca nomor lama dengan format bulan + tahun, misalnya `72600001`, untuk menentukan nomor urut berikutnya pada periode berjalan.

Dengan cara ini, output nomor LPB baru sudah memakai format tanpa angka bulan depan, tetapi urutan tidak kembali dari awal saat data lama masih ada di `tb_lpb`.

## Cara Validasi

1. Buka route `ics/detail_po`.
2. Lihat field readonly `Nomor LPB` pada area draft/final penerimaan.
3. Pilih `Jenis PO`.
4. Pastikan nomor preview tidak lagi diawali angka bulan. Untuk tahun 2026 contoh formatnya menjadi `2600001`, `2600001B`, atau `A2600001` sesuai jenis.
5. Saat LPB disimpan, backend tetap membuat ulang nomor final melalui `M_Logistik::generate_lpb_number()`.
