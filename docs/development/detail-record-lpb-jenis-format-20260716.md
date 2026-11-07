# Development Detail Record LPB - Format Jenis dan Nomor LPB

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

Perubahan lanjutan pada data LPB:

- Tombol `Input Nomor Invoice` dan `Edit Jenis PO` dihapus dari setiap card `Daftar LPB`.
- Tombol `Setting Jenis PO` ditampilkan pada header card `Detail LPB`, tepat setelah tombol `Update Invoice`.
- Card `Daftar LPB` menampilkan badge `Jenis LPB belum ditentukan` bila `tb_lpb.jenis_lpb` kosong.
- Jenis LPB memakai pilihan tetap:
  - `LPB CP`
  - `LPB Benih`
  - `LPB Konsinyasi`
  - `LPB Barang Non Pajak (A)`
  - `LPB Promosi`
  - `LPB Barang Pengganti Retur (RA)`
- Nomor LPB disimpan pada kolom `tb_lpb.nomor_lpb` setelah jenis LPB ditentukan.
- Format nomor menggunakan bulan tanpa nol depan + tahun 2 digit + urutan 5 digit berdasarkan jenis LPB.

## Format Nomor

- `LPB CP`: `72600001`
- `LPB Benih`: `72600001B`
- `LPB Konsinyasi`: `72600002K`
- `LPB Barang Non Pajak (A)`: `A72600001`
- `LPB Promosi`: `X72600001`
- `LPB Barang Pengganti Retur (RA)`: `RA72600001`

Keterangan: `7` = bulan, `26` = tahun, `00001` = nomor urut per jenis LPB.

## Catatan Update 2026-07-21

Format aktif setelah update `ics/detail_po` tidak lagi memakai angka bulan di depan nomor LPB. Format baru memakai tahun 2 digit + urutan 5 digit, misalnya `2600001`.

Dokumentasi detail update ada di `docs/development/ics-detail-po-nomor-lpb-format-year-only-20260721.md` dan `docs/database/ics-detail-po-nomor-lpb-format-year-only-20260721.md`.

## File Aplikasi

- `application/config/routes.php`
  - Menambah route `ics/ajax_update_lpb_type`.
- `application/controllers/logistik/C_Ics.php`
  - Menyediakan pilihan jenis LPB ke view.
  - Menambah endpoint `ajax_update_lpb_type`.
  - Membuka update invoice dari guard Admin PO agar tombol input invoice dapat digunakan dari halaman detail record LPB.
- `application/models/M_Logistik.php`
  - Menambah mapping jenis LPB.
  - Menambah generator `nomor_lpb`.
  - Menyimpan `jenis_lpb` dan `nomor_lpb` saat jenis LPB sudah ditentukan.
  - Mengupdate `jenis_lpb` dan membuat ulang `nomor_lpb` saat tombol `Setting Jenis PO` dipakai.
- `application/views/content/logistik/ics/detail_record_lpb.php`
  - Menampilkan nomor LPB pada card daftar dan detail.
  - Menghapus tombol aksi dari card daftar LPB.
  - Menambahkan tombol `Setting Jenis PO` di header detail LPB setelah `Update Invoice`.
  - Menambahkan badge `Jenis LPB belum ditentukan` pada daftar LPB.
  - Menggabungkan `Jenis LPB` ke dalam box `Nomor / Jenis LPB` pada header detail.
  - Menyembunyikan `Input At` dari tampilan Data LPB.
  - Memadatkan margin/padding box detail tanpa mengubah header panel dan tabel.
  - Menambahkan modal edit jenis LPB.

## Database

Migration: `docs/database/ics_lpb_nomor_format_20260716.sql`

Kolom baru:

- `tb_lpb.jenis_lpb`
- `tb_lpb.nomor_lpb`
