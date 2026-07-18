# Panduan Penggunaan Jurnal Pembelian LPB PO

Tanggal: 2026-07-18

## Membuka Daftar Jurnal Pembelian

1. Login sebagai user yang memiliki akses modul jurnal.
2. Buka route:

```text
jurnal
```

atau:

```text
keuangan/jurnal
```

3. Panel `Daftar Jurnal Pembelian` tampil sebelum panel `Daftar Jurnal Penjualan`.

## Cara Membaca Panel

Kolom yang tersedia:

| Kolom | Arti |
| --- | --- |
| Referensi | Nomor LPB yang menjadi dokumen sumber |
| Tanggal | Tanggal transaksi jurnal dari LPB |
| No PO | Nomor PO sumber |
| Supplier | Nama supplier PO |
| Kurs | Saat ini IDR |
| Nilai | Total debit/kredit jurnal pembelian |

Gunakan search untuk mencari nomor LPB, nomor PO, atau nama supplier.

## Melihat Detail Jurnal

Klik salah satu baris pada `Daftar Jurnal Pembelian`.

Modal detail menampilkan format jurnal:

- header jenis jurnal `PJ`;
- tanggal transaksi;
- judul `Pembelian, {Nama Supplier}`;
- baris kode akun;
- debit dan kredit;
- user penginput jurnal;
- tombol `Print` dan `Batal`.

## Rule Akun Saat Ini

Untuk tahap validasi, akun masih hardcode.

Jika barang pada LPB memiliki kelompok dagang `2` atau BKP:

- Debit `14010` Persediaan # 1;
- Debit `13017` Q PPN M Ymh Diterima;
- Kredit `21098` Hutang Usaha.

Jika barang pada LPB memiliki kelompok dagang `3` atau BKPS:

- Debit `14011` Q Persediaan Brg Dagangan BKPS;
- Kredit `21098` Hutang Usaha.

Kelompok dagang `4` dan `5` belum diposting otomatis sampai akun jurnalnya ditentukan.

## Kapan Jurnal Terbit

Jurnal pembelian terbit setelah LPB final memiliki `nomor_lpb`.

Jika LPB belum memiliki nomor LPB, accounting akan membuat posting exception dan tidak membuat jurnal.

Jika akun hardcode atau nilai LPB belum valid, jurnal juga tidak dibuat agar laporan tidak memakai akun tebakan.
