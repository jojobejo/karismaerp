# Chart of Accounts

Tanggal: 2026-07-13

## Aturan Akun

1. `kode_akun` unik.
2. Akun `HEADER` dipakai untuk struktur kelompok.
3. Akun `POSTING` dipakai untuk transaksi jurnal.
4. Akun `HEADER` tidak boleh dipakai jurnal manual.
5. Parent akun harus bertipe `HEADER`.
6. Akun yang memiliki child account tidak dapat menjadi `POSTING`.
7. Akun yang sudah digunakan jurnal tidak boleh dihapus.
8. Akun yang tidak dipakai lagi dinonaktifkan melalui `is_active = 0`.

## Tipe Kontrol

Tipe kontrol awal:

- `NONE`
- `KAS`
- `BANK`
- `PIUTANG`
- `HUTANG`
- `PERSEDIAAN`
- `GRNI`
- `PAJAK_MASUKAN`
- `PAJAK_KELUARAN`
- `UANG_MUKA_CUSTOMER`
- `UANG_MUKA_SUPPLIER`
- `LABA_DITAHAN`

## Penggunaan UI

1. Buka `jurnal`.
2. Buat akun `HEADER` sebagai kelompok.
3. Buat akun `POSTING` di bawah parent `HEADER`.
4. Centang `Boleh Jurnal Manual` hanya untuk akun posting yang boleh dipakai jurnal manual.
5. Gunakan `Nonaktifkan` untuk menghentikan pemakaian akun tanpa menghapus histori.
