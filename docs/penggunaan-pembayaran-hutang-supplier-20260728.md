# Penggunaan Module Pembayaran Hutang Supplier

Tanggal: 2026-07-28  
Pengguna: Keuangan / Accounting / Admin Keuangan.

## Membuka Module

Buka:

`keuangan/pembayaran-supplier`

Halaman awal menampilkan:

- total supplier yang masih punya hutang;
- total dokumen hutang;
- total sisa hutang;
- total payment supplier yang sudah posted;
- daftar supplier dengan hutang terbuka.

## Melihat Detail Supplier

1. Klik tombol `Detail` pada supplier.
2. Sistem menampilkan dokumen hutang terbuka.
3. Kolom utama:
   - Dokumen / nomor LPB;
   - PO dan invoice supplier;
   - total hutang;
   - retur/payment;
   - sisa hutang;
   - tanggal;
   - status LPB.

## Membayar Dokumen

Ada dua cara:

- klik `Bayar` dari dashboard supplier untuk membawa semua dokumen terbuka;
- pilih beberapa dokumen pada detail supplier, lalu klik `Bayar Pilihan`.

Di form pembayaran:

1. Pilih tanggal pembayaran.
2. Isi nomor pembayaran, atau kosongkan agar sistem membuat nomor otomatis.
3. Pilih akun kas/bank.
4. Cek nominal pembayaran.
5. Cek alokasi per dokumen.
6. Pastikan total nominal pembayaran sama dengan total alokasi.
7. Klik `Posting Pembayaran`.

Setelah berhasil:

- payment tersimpan di `tbkeu_pembayaran`;
- alokasi tersimpan di `tbkeu_pembayaran_alokasi`;
- jurnal `SUPPLIER_PAYMENT` dibuat;
- user diarahkan ke histori pembayaran.

## Histori Pembayaran

Buka:

`keuangan/pembayaran-supplier/history`

Halaman ini menampilkan:

- nomor pembayaran;
- tanggal;
- supplier;
- nomor jurnal;
- amount;
- allocated;
- unapplied;
- status;
- tombol void untuk payment posted.

## Void Pembayaran

1. Buka histori pembayaran supplier.
2. Klik `Void` pada payment berstatus `POSTED`.
3. Isi alasan void.
4. Sistem membuat jurnal reversal dan mengubah payment menjadi `VOID`.

Payment yang sudah `VOID` tidak dihapus agar audit trail tetap tersedia.

## Catatan Operasional

- Module ini untuk pembayaran hutang supplier, bukan penerimaan piutang customer.
- Untuk fase pertama, pembayaran harus dialokasikan penuh ke dokumen hutang.
- Jika total pembayaran tidak sama dengan total alokasi, sistem menolak posting.
- Jika dokumen tidak muncul, cek apakah LPB sudah punya jurnal hutang `POSTED`.
- Jika posting ditolak karena outstanding, cek jurnal LPB/retur dan akun hutang.

