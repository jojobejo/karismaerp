# Development - ICS Detail Record LPB Update Harga dan Qty Balance

Tanggal: 2026-07-21

## Scope

Route: `ics/detail_record_lpb`

File aplikasi:

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Perubahan

Modal `Update Harga Detail LPB` sekarang dapat dipakai untuk update:

- `Qty LPB`
- `Harga Satuan Baru`

Field `Qty LPB` yang sebelumnya readonly diubah menjadi input number.

`Total Harga Baru` otomatis dihitung ulang dari:

`Qty LPB x Harga Satuan Baru`

## Hak Akses

Endpoint update harga dan qty detail LPB sekarang divalidasi di server.

User yang dapat melakukan update:

- Departemen Purchasing
- Jobdesk `ADMINPURCHASING`
- Jobdesk `ADMIN PO`
- Jobdesk `ADMIN`
- Username `admpo`
- Username `admin`
- User level `lv = 1`

## Validasi Qty

Sebelum data disimpan, aplikasi menghitung ulang balance qty berdasarkan:

- `id_detail_lpb`
- `kd_barang`
- `id_lpb`
- total qty LPB lain untuk kode barang yang sama
- total qty LPB saat ini dari `tb_lpb_detail`

Aturan:

- Qty LPB baru harus lebih dari 0.
- Qty LPB baru dan total Qty LPB setelah update tidak boleh melebihi total LPB/qty diterima berdasarkan `id_lpb + kd_barang`.
- Jika total Qty LPB setelah update tidak sama dengan total LPB/qty diterima awal, proses simpan tetap boleh berjalan selama tidak melebihi batas, tetapi response membawa warning untuk ditampilkan ke user.

## Efek Simpan

Saat update berhasil:

- `tb_lpb_detail.qty_diterima` diperbarui.
- `tb_lpb_detail.harga_satuan` diperbarui.
- `tb_lpb_detail.total_harga` dihitung ulang dari qty baru dan harga baru.
- `harga_satuan_sebelumnya` dan `total_harga_sebelumnya` menyimpan nilai aktif sebelum update.
- `harga_verified_by` dan `harga_verified_at` direset menjadi `NULL` agar verifikasi harga tetap dilakukan lewat tombol Accept.
- Jika tabel `tb_lpb_batch` ada, kolom `qty` ikut disesuaikan dengan Qty LPB baru.
- Activity log mencatat perubahan `Qty LPB`, `Harga Satuan`, dan `Total Harga`.

## Catatan Workflow

Update harga dan qty tidak otomatis melakukan verifikasi harga.

Tombol Accept tetap menjadi proses terpisah untuk verifikasi Purchasing.
