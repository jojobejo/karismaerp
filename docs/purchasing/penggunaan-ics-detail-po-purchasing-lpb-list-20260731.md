# Penggunaan - ICS Detail PO Purchasing LPB List

Tanggal: 2026-07-31

## Akses

Route:

`ics/detail_po?no_po=...&kd_suplier=...`

## Admin dan ADMLPB

Tidak ada perubahan cara pakai.

1. Buka detail PO.
2. Klik tombol tambah pada baris barang untuk membuat draft penerimaan.
3. Lengkapi header draft temporary.
4. Simpan untuk merekam LPB.

## Purchasing

Purchasing tidak lagi melihat form draft temporary pada halaman ini.

1. Buka detail PO dari menu ICS PO.
2. Lihat card `Detail Barang PO` untuk memeriksa qty order, qty masuk, qty diterima, dan qty sisa.
3. Lihat card `List Data LPB Yang Telah Direkam` untuk daftar LPB sesuai nomor PO, dengan sajian satu baris per `NO LPB`.
4. Cek kolom `Invoice` untuk melihat jumlah invoice yang sudah tercatat pada nomor LPB tersebut.
5. Gunakan filter `Status Data` untuk menampilkan LPB yang belum invoice, belum pajak, atau belum afirmasi harga.
6. Gunakan filter `Status Barang` untuk melihat LPB yang sudah terkait transaksi penjualan atau jurnal LPB.
7. Klik `NO LPB` untuk membuka detail record LPB yang dipilih.

## Arti Indikator

- `TERPOSTING`: LPB sudah berstatus posting.
- `UNPOST`: LPB sedang dibuka kembali atau belum diposting.
- `DRAFT`: status LPB belum terisi.
- Kolom `Invoice` menunjukkan jumlah invoice distinct per `NO LPB`.
- Ikon invoice menyala hijau bila jumlah invoice lebih dari 0.
- Ikon pajak menyala hijau bila nomor faktur pajak sudah ada.
- Ikon afirmasi harga menyala hijau bila seluruh detail harga sudah diafirmasi.
- Ikon transaksi menunjukkan barang LPB sudah terpakai pada transaksi penjualan.
- Ikon jurnal menunjukkan LPB sudah memiliki jurnal pembelian aktif.
