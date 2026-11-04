# Development Detail Record LPB Purchasing Layout

Tanggal: 2026-07-18

## Route

`ics/detail_record_lpb`

## Konteks Akses

Halaman detail record LPB kini menyamakan konteks Purchasing dengan halaman `ics/icspo`.
User dengan departemen yang mengandung kata `PURCHASING`, jobdesk `ADMINPURCHASING` / `ADMIN PO`, atau username `admpo` diperlakukan sebagai akses Purchasing pada route ini.

## Perubahan Aplikasi

- Tombol kembali diubah dari `Kembali ke Detail PO` menjadi `Kembali ke Data PO`.
- Redirect tombol kembali diarahkan langsung ke route `ics/icspo`.
- Judul hero `Record Semua Data LPB` diganti menjadi blok informasi `Nomor PO`.
- Informasi di bawah nomor PO menampilkan `PO Komersil : {kd_po}`.
- Card `LPB Invoice & Adjustment Harga` dihilangkan dari tampilan.
- Card `Daftar LPB` dihilangkan dari tampilan.
- Detail LPB diperluas menjadi full width karena panel daftar LPB tidak lagi tampil di sisi kiri.
- Loader daftar LPB tetap tersedia secara tersembunyi untuk menjaga auto-select detail LPB pertama dan flow tombol detail tetap berjalan.
- Panggilan otomatis ke panel PRE PO adjustment dinonaktifkan karena card tersebut tidak lagi tampil.

## Tata Cara Penggunaan

1. Buka `ics/icspo`.
2. Pada akses Purchasing, pilih aksi menuju detail record LPB pada PO yang diinginkan.
3. Halaman `ics/detail_record_lpb` akan menampilkan tombol `Kembali ke Data PO`, informasi `Nomor PO`, dan `PO Komersil : {kd_po}`.
4. Detail LPB dimuat otomatis tanpa panel `Daftar LPB`.
5. Gunakan tombol pada panel `Detail LPB` untuk update invoice, update faktur, cetak LPB terpilih, atau proses harga sesuai akses yang tersedia.
