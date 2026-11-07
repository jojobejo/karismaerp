# Development - ICS ICSPo Purchasing Status Icons

Tanggal: 2026-07-18

## Scope

Route `ics/icspo` untuk user departemen Purchasing menampilkan tabel LPB Purchasing dengan urutan header baru:

`Tgl PO`, `No PO`, `No LPB`, `Jenis PO`, `Nomor SJ`, `Tanggal SJ`, `Nama Suplier`, `Invoice`, `Tgl Invoice`, `Faktur Pajak`, `Tgl Faktur`, `Status`, `#`.

## File yang Berubah

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/icspo.php`

## Detail Implementasi

`M_Logistik::get_lpb_purchasing_view()` menambahkan field `tgl_po` dari `tbpo_po.tgl_transaksi`. Jika tanggal PO tidak tersedia, sistem fallback ke tanggal input LPB.

Kolom `Status` pada tabel Purchasing tidak lagi memakai progress bar. Kolom ini berisi tiga tombol icon:

- Icon invoice: hijau jika `no_invoice` sudah terisi.
- Icon pajak: hijau jika `kode_faktur_pajak` sudah terisi.
- Icon uang: hijau jika seluruh detail LPB sudah selesai diverifikasi harga.

Jika data status belum ada, tombol menggunakan warna default abu-abu.

## Tata Cara Penggunaan

1. Login sebagai user departemen Purchasing.
2. Buka `ics/icspo`.
3. Periksa tabel LPB Purchasing.
4. Gunakan kolom `Status` untuk membaca kelengkapan invoice, faktur pajak, dan verifikasi harga tanpa membuka detail satu per satu.
5. Tombol `#` tetap membuka `ics/detail_record_lpb` untuk melihat atau melanjutkan proses detail LPB.

## Catatan QA

- LPB tanpa invoice harus menampilkan icon invoice default.
- LPB dengan `no_invoice` harus menampilkan icon invoice hijau.
- LPB dengan `kode_faktur_pajak` harus menampilkan icon pajak hijau.
- LPB dengan semua detail harga terverifikasi harus menampilkan icon uang hijau.
- Sorting default tabel Purchasing memakai `Tgl PO` terbaru.
