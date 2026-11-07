# Perbaikan Qty Pembayaran Konsinyasi

## Masalah

Pada form pembayaran faktur konsinyasi, perubahan nominal pembayaran menghitung ulang kolom **Qty yang Dibeli Kios** dengan rumus `nominal / harga satuan`. Apabila nominal mengandung diskon atau pembulatan, qty yang sudah dimasukkan petugas dapat tertimpa. Contoh: qty 500 dengan nominal Rp52.395.000 dan harga Rp105.000 berubah menjadi 499.

Akibatnya proses sinkronisasi memecah satu baris titipan 500 PCS menjadi 499 PCS barang laku dan 1 PCS sisa di kios.

## Perbaikan

- Qty yang dimasukkan petugas dijadikan data fisik utama.
- Nominal tetap dihitung otomatis ketika qty diubah.
- Perubahan nominal tidak lagi menimpa qty yang telah diisi.
- Perhitungan qty dari nominal hanya digunakan jika kolom qty masih kosong dan belum pernah diubah petugas.

## Verifikasi

1. Buka halaman pembayaran faktur konsinyasi.
2. Isi qty, misalnya 500 PCS.
3. Ubah nominal pembayaran untuk mengakomodasi diskon atau pembulatan.
4. Pastikan qty tetap 500 PCS saat form dikirim.
5. Buka halaman Purchasing > Konsinyasi dan pastikan tidak terbentuk sisa 1 PCS akibat perubahan nominal.

## Koreksi Data Terdampak

Untuk transaksi faktur `TINV2909260001`, qty pembayaran dan baris barang laku dikoreksi dari 499 menjadi 500 PCS. Baris sisa 1 PCS yang terbentuk karena bug dihapus. Baris titipan 500 PCS lainnya tetap dipertahankan karena merupakan sisa fisik yang masih berada di kios dari total pengiriman 1.000 PCS.

## Perbaikan Harga Tagihan Supplier

Harga tagihan pada modal penyelesaian sempat kosong karena LPB konsinyasi lama tidak memiliki kode supplier pada header. Sistem sebelumnya menolak LPB tersebut sebagai sumber barang, meskipun barang, gudang, lot, dan PO sudah cocok.

Perbaikannya:

- LPB tetap diakui sebagai sumber barang berdasarkan barang, gudang, dan lot walaupun supplier header kosong.
- Identitas supplier dilengkapi dari master barang jika diperlukan.
- Data settlement lama yang belum memiliki relasi LPB diperbaiki otomatis ketika detail tagihan dibuka.
- Harga dan tipe pajak tetap diambil dari detail PO yang terhubung melalui LPB.

## Penyederhanaan Modal Input Tagihan

- Modal diperlebar dan isinya disusun menjadi dua kolom pada layar desktop agar tidak terlalu panjang ke bawah.
- Kolom **Qty Laku yang Ditagihkan** dihilangkan karena nilainya sudah tampil pada ringkasan Barang Laku.
- Qty tetap dikirim sebagai data tersembunyi sehingga kalkulasi tagihan dan proses posting jurnal tidak berubah.
- Pada layar ponsel, susunan otomatis kembali menjadi satu kolom agar tetap mudah dibaca.

## Koreksi Akun Jurnal Pembelian Konsinyasi

Jurnal pengakuan tagihan supplier konsinyasi menggunakan akun yang sama dengan jurnal pembelian reguler. Perbedaannya hanya pada waktu posting, yaitu ketika barang konsinyasi telah laku dan tagihan supplier diproses.

Pemetaan akun yang digunakan:

- Debit `140-10` — Persediaan # 1 sebesar DPP.
- Debit `130-17` — Q PPN M Ymh Diterima sebesar PPN masukan.
- Kredit `210-98` — Hutang Usaha sebesar total tagihan.

Akun HPP `510-10` dan Utang Konsinyasi `219-20` tidak lagi digunakan untuk posting settlement konsinyasi.

## Tanggal Jurnal Pembelian Konsinyasi

Tanggal transaksi jurnal pembelian konsinyasi mengikuti **Tgl LPB** barang masuk (`tb_lpb.tgl_sj`), bukan tanggal invoice yang dimasukkan pada modal tagihan supplier.

Ketentuannya:

- Sumber utama tanggal adalah `tgl_sj` LPB asal.
- Jika `tgl_sj` kosong, sistem memakai tanggal `input_at` LPB.
- Jika LPB asal atau tanggalnya tidak ditemukan, posting ditolak agar jurnal tidak menggunakan tanggal yang keliru.
- Tanggal invoice supplier tetap disimpan sebagai informasi dokumen tagihan.
