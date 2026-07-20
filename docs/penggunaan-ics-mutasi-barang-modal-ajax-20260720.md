# Penggunaan Modul - ICS Mutasi Barang Modal AJAX

Tanggal: 2026-07-20

Route:

- `ics/mutasi_barang/input`

## Cara Penggunaan

1. Buka menu input mutasi barang.
2. Isi atau cek header transaksi: Ref, Tanggal, Keterangan, Dari Gudang, dan Ke Gudang.
3. Grid utama menampilkan 5 baris default dan kolom `No Lot` serta `Expired Date`.
4. Klik tombol `List Barang` sejajar dengan pilihan gudang untuk membuka halaman `Data Persediaan`.
5. Cari barang pada halaman `Data Persediaan`.
6. Double-click baris barang yang diinginkan untuk membuka modal input.
7. Isi `Jumlah`.
8. Pilih `No Lot` melalui Select2.
9. Pilih `Expired Date` melalui Select2 yang muncul berdasarkan No Lot.
10. Cek readonly `Qty Stock`.
11. Klik `Rekam` untuk menambahkan barang ke list mutasi.
12. Di grid utama, klik kolom `Jumlah` untuk update qty tanpa reload.
13. Klik kolom `Satuan` untuk memilih satuan memakai Select2.
14. Klik tombol icon edit pada kolom `#` untuk membuka modal edit barang mutasi.
15. Pada modal edit, update `Jumlah`, `No Lot`, dan `Expired Date`, lalu klik `Update`.
16. Klik area baris selain kolom editable untuk memilih data yang akan dihapus dengan tombol `Hapus Baris`.
17. Tekan `Rekam` pada layar utama untuk menyimpan mutasi final.

## Validasi

- Jumlah harus lebih dari 0.
- Jumlah tidak boleh melebihi stock lot tersedia.
- Rekam barang dari halaman `Data Persediaan` ditolak jika qty yang diminta melebihi `Qty Stock`.
- Semua barang draft wajib memiliki lot dan expired date sebelum rekam final.
- `Dari Gudang` dan `Ke Gudang` tidak boleh sama.
- Tabel utama tidak lagi membuka modal saat diklik; input barang dilakukan dari halaman `Data Persediaan`.
- Update inline `Jumlah` tetap divalidasi terhadap stok database.
- Modal edit menolak update jika qty melebihi `Qty Stock`.

## Notifikasi

Modul menggunakan SweetAlert untuk:

- peringatan validasi
- konfirmasi hapus baris
- konfirmasi batal input
- notifikasi sukses penyimpanan lot
- notifikasi sukses rekam final
- toast pilihan barang, No Lot, Expired Date, dan Qty Stock pada halaman `Data Persediaan`
- konfirmasi sebelum barang direkam ke list mutasi

## Dashboard Mutasi

Pada route `ics/mutasi_barang`, 1 nomor faktur mutasi tampil sebagai 1 baris master transaksi. Jika faktur tersebut berisi beberapa barang, daftar barang dilihat melalui tombol detail.
