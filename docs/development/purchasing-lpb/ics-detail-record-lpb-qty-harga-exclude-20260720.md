# Development - ICS Detail Record LPB Qty dan Harga Exclude

Tanggal: 2026-07-20

## Scope

Route: `ics/detail_record_lpb`

Perubahan berfokus pada sajian detail LPB:

- Menghilangkan kolom `Qty Order` dari tampilan Detail LPB.
- Menghilangkan kolom `Satuan` dari tabel Detail LPB.
- Menambahkan kolom `Qty In` setelah `Expired Date`.
- Mengubah area qty menjadi header bertingkat `Qty Satuan` dengan subkolom `BOX`, `Kg/Ltr`, dan `Pcs`.
- Header dan value kolom `Qty In`, `No Lot`, `BOX`, `Kg/Ltr`, dan `Pcs` diratakan tengah.
- Menambahkan grid/border eksplisit pada tabel Detail LPB agar batas antar kolom terlihat jelas.
- Menjaga `Qty LPB` sebagai qty kecil dari `tb_lpb_detail.qty_diterima`.
- Memposisikan `Total Harga` setelah `Harga Satuan`.
- Menampilkan harga exclude dari data PO KIU_PO/KarismaERP yang sudah tersimpan di `tbpo_detail_po`.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Detail Implementasi

- `M_Logistik::get_lpb_record_detail_rows()` sekarang mengirim `qty_in`, `qty_satuan_box`, `qty_satuan_kg_ltr`, `qty_satuan_pcs`, `harga_satuan_exclude`, dan `total_harga_exclude`.
- `M_Logistik::get_purchasing_lpb_detail_rows()` memakai field yang sama untuk panel Purchasing.
- Rumus `Qty Satuan` adalah kebalikan dari rumus qty kecil KIU_PO:
  - `BOX`: `qty_diterima / isi`.
  - `Kg/Ltr`: `qty_diterima x (kemasan / 1000)`.
  - `Pcs`: `qty_diterima`.
- `Qty In` memakai qty kecil LPB langsung dari `tb_lpb_detail.qty_diterima`.
- Header tabel detail LPB memakai dua baris: kolom identitas barang diberi `rowspan`, sedangkan `Qty Satuan` diberi `colspan=3`.
- `Harga Satuan` yang ditampilkan diprioritaskan dari `tbpo_detail_po.harga_satuan_kecil_exclude`, lalu fallback ke `harga_satuan_exclude`, dan terakhir fallback ke harga LPB bila data PO lama belum lengkap.
- `Total Harga` dihitung dari `Qty LPB x Harga Satuan` berbasis exclude.
- Tombol edit harga dan Rekam/Accept tetap memakai nilai harga yang ditampilkan, sehingga workflow verifikasi harga tidak digabung dengan update status lain.

## Urutan Kolom Baru

Data LPB Logistik:

`Kode Barang | Nama Barang | No Lot | Expired Date | Qty In | Qty Satuan: BOX | Kg/Ltr | Pcs`

Data LPB Purchasing:

`Kode Barang | Nama Barang | No Lot | Expired Date | Qty In | Qty Satuan: BOX | Kg/Ltr | Pcs | Harga Satuan | Total Harga | #`

## Validasi

- PHP lint:
  - `C:\xampp\php\php.exe -l application/models/M_Logistik.php`
  - `C:\xampp\php\php.exe -l application/views/content/logistik/ics/detail_record_lpb.php`
- `git diff --check` untuk file yang diubah.
