# Development - Notifikasi Penjualan LPB dan Log Checker

Tanggal: 2026-07-22

## Scope

Route:

- `ics/icspo`
- `ics/detail_record_lpb`
- `ics/ajax_update_lpb_detail_price`
- `ics/ajax_finalize_tmp_po_received`

File aplikasi:

- `application/models/M_Logistik.php`
- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/icspo.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Perubahan

Daftar LPB Purchasing sekarang menampilkan kolom `Notif` berisi dua indikator:

- ikon transaksi penjualan;
- ikon jurnal LPB.

Indikator penjualan membaca korelasi detail LPB ke detail faktur penjualan berdasarkan:

- `kd_barang`;
- `no_lot`;
- `expired_date`.

Indikator jurnal membaca `tbkeu_jurnal` untuk source:

- `source_module = LOGISTIK`;
- `source_type = LPB_FINAL`;
- `posting_event = GOODS_RECEIPT`.

## Guard Update Harga

Endpoint update harga dan qty LPB sekarang menolak update langsung bila:

- LPB sudah memiliki transaksi penjualan berdasarkan barang, lot, dan expired date yang sama;
- LPB sudah memiliki jurnal pembelian `GOODS_RECEIPT` berstatus `POSTED` dan belum direversal.

Tujuannya menjaga agar Purchasing tidak mengubah nilai LPB yang sudah mempengaruhi HPP, persediaan, atau jurnal tanpa workflow koreksi accounting.

## Log Checker

Saat LPB dibuat dari draft temporary penerimaan, sistem menyimpan:

- `checker_name`;
- `checker_by`;
- `checker_at`.

Log aktivitas LPB juga membawa `checker_name` dan `checker_by`. Jika activity berikutnya hanya mengirim `id_lpb`, model mengambil checker dari header LPB agar log tetap konsisten.

## Catatan Accounting

Perubahan ini belum membuat jurnal koreksi otomatis. Flow yang aman adalah:

1. Purchasing melihat notifikasi bahwa LPB sudah terjual atau sudah punya jurnal aktif.
2. Update harga langsung diblokir.
3. Accounting melakukan reversal atau workflow koreksi harga/jurnal sebelum data harga LPB diubah.

