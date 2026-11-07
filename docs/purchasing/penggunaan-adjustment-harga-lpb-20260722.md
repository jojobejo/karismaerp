# Panduan Penggunaan Adjustment Harga LPB

Tanggal: 2026-07-22

## Akses

Buka menu:

`ics/retur/pembelian/adjustment`

Shortcut juga tersedia dari dashboard retur dan halaman Retur Pembelian.

## Membuat Adjustment

1. Pilih `LPB Salah`.
2. Isi `Tanggal Adjustment`.
3. Sistem mengunci nomor lot sebagai `Adj. Harga Beli`.
4. Sistem mengunci expired date sebagai `01/01/1000`.
5. Isi `Harga Invoice Benar` pada semua detail barang.
6. Isi `Alasan Adjustment`.
7. Klik `Posting Adjustment`.

## Hasil Posting

Sistem otomatis membuat:

- LPB adjustment dengan nomor LPB asal ditambah `A`;
- detail LPB adjustment dengan qty sama, lot `Adj. Harga Beli`, expired `1000-01-01`, dan harga invoice benar;
- jurnal LPB adjustment;
- PRPP otomatis dengan supplier, barang, qty, dan harga dari LPB salah;
- jurnal PRPP;
- audit trail pada tabel adjustment, LPB log, dan retur pembelian log.

## Setelah Berhasil

Konfirmasi ke bagian jurnal bahwa adjustment harga LPB sudah dibuat.

Data yang perlu disebutkan:

- nomor adjustment;
- nomor LPB salah;
- nomor LPB adjustment;
- nomor PRPP;
- selisih DPP/PPN/total.

## Validasi Sistem

- Harga invoice benar wajib berbeda minimal pada satu detail.
- Semua detail LPB harus diberi harga invoice benar.
- LPB asal wajib memiliki supplier.
- Kelompok dagang yang bisa otomatis diposting hanya `2` dan `3`.
- Stok lot `Adj. Harga Beli` harus kembali ke saldo sebelum posting.
