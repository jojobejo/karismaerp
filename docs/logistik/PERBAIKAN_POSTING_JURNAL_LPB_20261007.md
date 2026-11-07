# Perbaikan Posting Jurnal LPB

Tanggal: 7 Oktober 2026

## Masalah

LPB pembelian biasa dapat tersimpan sebagai `POST`, tetapi jurnal penerimaan barang tidak terbentuk. Browser juga menampilkan pesan gagal walaupun perubahan status LPB sudah tersimpan.

## Penyebab

Query perhitungan jurnal `GOODS_RECEIPT` membaca kolom `p.keterangan_harga_ppn`, sedangkan alias tabel `p` tidak ada pada query. MariaDB menghasilkan error 1054 setelah transaksi perubahan status LPB telanjur di-commit.

## Perbaikan

- Sumber keterangan harga PPN menggunakan detail PO (`pp.keterangan_harga_ppn`) yang memang tersedia pada query.
- Posting status LPB dan pembuatan jurnal dijalankan dalam satu transaksi database.
- Jika jurnal gagal, perubahan status LPB dibatalkan sehingga tidak terbentuk kondisi LPB pembelian biasa `POST` tanpa jurnal.
- LPB konsinyasi tetap dikecualikan dari jurnal penerimaan sesuai aturan bisnis.

## Pemulihan Data

LPB nomor `2600001` (`id_lpb` 3, PO `Q003/KIU/X/2026`) telah dipulihkan dengan jurnal `PJ-202610-00001` berstatus `POSTED`.
