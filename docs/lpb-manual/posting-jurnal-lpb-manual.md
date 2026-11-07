# Posting Jurnal LPB Manual

## Tujuan

LPB Manual mengikuti aturan posting akuntansi yang sama dengan LPB berbasis PO. Jenis LPB yang dipilih pengguna menentukan apakah layanan akuntansi membuat jurnal atau melewatinya.

## Alur

1. Sistem memvalidasi dan menyimpan header, detail, batch, serta stock ledger LPB Manual.
2. LPB Manual berstatus draft tidak membentuk jurnal. Jurnal diproses saat Purchasing melakukan posting final.
3. LPB Manual non-draft diteruskan ke `Accounting_source_service::post_goods_receipt()` dalam transaksi database yang sama.
4. LPB Konsinyasi dilewati dari jurnal penerimaan karena merupakan titipan fisik. Jurnal dibentuk saat settlement sesuai aturan layanan akuntansi.
5. Jenis LPB pembelian lainnya diproses menjadi jurnal penerimaan apabila harga, kelompok barang, akun, dan periode fiskal memenuhi validasi.
6. Apabila pembentukan jurnal gagal, transaksi penyimpanan LPB Manual dibatalkan agar tidak ada LPB berstatus POST tanpa jurnal yang seharusnya.

## Supplier

Untuk LPB berbasis PO, supplier tetap diperoleh dari PO. Untuk LPB Manual, supplier dibaca dari kolom `kd_suplier` dan `nama_suplier` pada header `tb_lpb`, karena transaksi tidak memiliki relasi ke PO.

Daftar dan detail pada menu **Jurnal Pembelian** menggunakan urutan sumber supplier berikut:

1. Nama supplier pada header LPB Manual.
2. Master supplier berdasarkan kode supplier header LPB.
3. Supplier dari PO untuk LPB reguler.
4. Supplier settlement untuk transaksi konsinyasi.

Dengan urutan tersebut, LPB Manual tetap menampilkan supplier meskipun `no_po` berisi nomor referensi manual dan tidak ditemukan pada tabel PO.

## Catatan Operasional

Data LPB Manual lama yang sudah berstatus POST sebelum perbaikan ini tidak otomatis dibuatkan jurnal. Data tersebut perlu diposting ulang melalui workflow yang tersedia atau direkonsiliasi secara terkontrol oleh Accounting.
