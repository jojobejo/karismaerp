# Development - Split Detail LPB

Tanggal: 2026-07-31

Update 2026-08-05: rules total harga diperbarui. Total qty tetap wajib sama dengan qty awal, tetapi total harga hasil split boleh berbeda dan selisihnya dicatat di log aktivitas. Detail update ada di `docs/development/ics-detail-record-lpb-split-detail-flexible-price-20260805.md`.

## Scope

Route utama: `ics/detail_record_lpb`

Fitur ini menambahkan fungsi split data detail LPB per barang. Split hanya tersedia saat LPB berstatus `UNPOST`, mengikuti aturan tombol edit detail/harga yang sudah ada.

## File Aplikasi

- `application/config/routes.php`
- `application/modules/kiupo/routes.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Endpoint Baru

`POST ics/ajax_split_lpb_detail`

Payload:

- `id_detail_lpb`
- `splits` dalam format JSON array. Setiap baris berisi `label`, `qty`, `harga_satuan`, dan `total_harga`.
- `keterangan`

Response sukses mengembalikan ID detail sumber, daftar baris hasil split, qty/harga/total awal, total qty input, dan total harga input.

## Aturan Bisnis

- LPB harus `UNPOST`.
- Tombol split detail tampil bersama tombol edit, hanya saat data bisa diedit.
- Modal berjudul `Split Qty dan Harga Barang - [nama barang]`.
- Baris pertama adalah `Data Sekarang`, yaitu detail LPB yang sedang dipilih.
- Baris kedua dan seterusnya adalah baris split baru yang dapat ditambah atau dihapus user.
- Setiap baris harus memiliki qty lebih dari 0 dan harga satuan tidak boleh minus.
- Total qty seluruh baris harus sama dengan qty awal.
- Total harga seluruh baris boleh berbeda dari total nilai awal.
- Jika total qty lebih besar atau kurang dari data acuan, proses ditolak.
- Jika total harga berbeda dari data acuan, proses tetap boleh disimpan dan selisihnya dicatat di log aktivitas.
- Setelah split, verifikasi harga di-reset pada baris asal dan semua baris baru.

## Alur Teknis

1. View menampilkan tombol split dengan icon `fa-code-branch` di baris detail ketika LPB `UNPOST`.
2. Klik tombol membuka modal `Split Qty dan Harga Barang - [nama barang]`.
3. Modal menampilkan data acuan, keterangan, baris `Data Sekarang`, baris `Split 1`, tombol tambah baris, total input, dan selisih.
4. Submit modal memanggil `C_Ics::ajax_split_lpb_detail()`.
5. Controller memvalidasi role, parameter, status LPB, lalu memanggil `M_Logistik::split_lpb_detail()`.
6. Model mengubah baris asal menjadi baris pertama dari komposisi split.
7. Model membuat baris baru untuk baris split kedua dan seterusnya.
8. `tb_lpb_batch` disesuaikan untuk baris asal dan baris baru.
9. Aktivitas dicatat sebagai `SPLIT_LPB_DETAIL` di log LPB.

## Catatan Integrasi

Jalur lama `ajax_split_lpb_multiple_invoice` ikut diberi guard `UNPOST` agar seluruh aksi pecah LPB konsisten dengan aturan edit data.
