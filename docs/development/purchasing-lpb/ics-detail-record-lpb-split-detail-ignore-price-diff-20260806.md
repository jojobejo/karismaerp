# Development Log - Split Detail LPB Selisih Harga Satuan Diabaikan

Tanggal: 2026-08-06
Route: `ics/detail_record_lpb`

## Latar Belakang

Pada modal split qty dan harga barang (`#modalSplitLpbDetail`), aturan penanganan selisih harga disesuaikan agar selisih pada harga satuan diabaikan secara penuh. Apabila terdapat perbedaan harga satuan/total harga pada baris-baris hasil split, sistem menghiraukan selisih tersebut dan tetap menyimpan data sesuai dengan form inputan pengguna.

## File Terdampak

1. `application/views/content/logistik/ics/detail_record_lpb.php`
   - Mengubah pesan keterangan pada `#splitDetailInfo` di modal HTML.
   - Memperbarui fungsi JS `updateSplitDetailPreview()` agar selisih harga tidak menampilkan alert warning (`alert-warning`) atau memblokir simpan split.
   - Mengatur penanda selisih harga agar diabaikan dan data disimpan langsung sesuai form inputan jika Qty sudah sesuai.

2. `application/models/M_Logistik.php`
   - Memperbarui method `split_lpb_detail($payload)`.
   - Mengubah `harga_rule` menjadi `'SELISIH_HARGA_DIABAIKAN'`.
   - Mengubah deskripsi `keterangan` pada log aktivitas `insert_lpb_activity_log` untuk mencatat bahwa selisih harga diabaikan dan disimpan sesuai inputan form.

## Validasi Code & Syntax

- `C:\xampp\php\php.exe -l application/views/content/logistik/ics/detail_record_lpb.php` -> No syntax errors detected
- `C:\xampp\php\php.exe -l application/models/M_Logistik.php` -> No syntax errors detected
