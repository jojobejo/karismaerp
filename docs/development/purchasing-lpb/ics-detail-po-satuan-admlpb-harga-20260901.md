# Development - ICS Detail PO Satuan dan Hak Akses Harga

Tanggal: 2026-09-01

## Modul

- Route: `ics/detail_po`
- Controller: `application/controllers/logistik/C_Ics.php`
- Model: `application/models/M_Logistik.php`
- View: `application/views/content/logistik/ics/detail_po.php`

## Latar Belakang

Pada detail PO dari sumber data `tbpo_detail_po`, barang `Booster 250 SC 40 X 250 ml` memiliki satuan order `Btl`, tetapi subkolom `Qty Order > Box` ikut menampilkan nilai `500`. Kondisi ini membuat sajian data seolah-olah 500 box, padahal satuan order yang diinput bukan box.

Selain itu, akun operasional LPB seperti `admlpb` tidak boleh melihat kolom nominal `Harga Satuan` pada halaman detail PO.

## Perubahan Aplikasi

1. `M_Logistik::detail_po_received()`
   - Subkolom `qty_order_box` sekarang hanya terisi jika satuan detail PO dikenali sebagai `box`.
   - Subkolom `qty_diterima_box` juga mengikuti aturan yang sama.
   - Satuan non-box seperti `Btl`, `pcs`, atau satuan lain tidak lagi dipaksa tampil sebagai box.

2. `detail_po.php`
   - Grup header `Harga Satuan` beserta subkolom `Include` dan `Exclude` disembunyikan jika `can_view_lpb_nominal` bernilai false.
   - Sel data harga satuan juga disembunyikan mengikuti flag yang sama.
   - Jumlah kolom kosong dan target kolom non-sortable DataTables dibuat adaptif agar tabel tetap valid.

## Dampak Bisnis

- Data pembelian lebih akurat untuk pembacaan LPB karena satuan botol tidak lagi terbaca sebagai box.
- Akun `admlpb`, `adminloglpb`, dan `admlpb2` mengikuti pembatasan nominal yang sudah berlaku di modul LPB.

## Cara Uji Manual

1. Login sebagai akun yang dapat membuka `ics/detail_po`.
2. Buka PO yang memiliki barang `Booster 250 SC 40 X 250 ml`.
3. Pastikan:
   - Kolom utama `Qty Order` tetap menampilkan total qty kecil/order sesuai data.
   - Subkolom `Qty Order > Box` bernilai `0` atau kosong secara angka untuk satuan `Btl`.
   - Barang dengan satuan `Box` tetap menampilkan jumlah box hasil konversi.
4. Login sebagai `admlpb`.
5. Buka route yang sama dan pastikan grup `Harga Satuan`, `Include`, `Exclude`, serta nilai rupiahnya tidak tampil.

## Catatan

Perubahan ini hanya memperbaiki perhitungan sajian dan pembatasan tampilan nominal. Proses simpan draft/final LPB tidak diubah.
