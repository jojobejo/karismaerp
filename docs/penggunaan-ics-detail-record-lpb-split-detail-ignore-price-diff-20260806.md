# Penggunaan - Split Detail LPB (Selisih Harga Satuan Diabaikan)

Tanggal: 2026-08-06
Route: `ics/detail_record_lpb`

## Cara Penggunaan

1. Buka halaman `ics/detail_record_lpb` untuk PO/LPB yang diinginkan.
2. Pastikan status LPB berada pada status `UNPOST`.
3. Klik tombol split dengan icon cabang (`Pecah Detail / Split`) pada baris barang yang ingin di-split.
4. Pada modal **Split Qty dan Harga Barang**, atur jumlah Qty dan Harga Satuan untuk masing-masing baris.
5. **Rules Validasi:**
   - Total Qty seluruh baris split **wajib sama** dengan Qty In awal.
   - **Selisih pada Harga Satuan / Total Harga diabaikan** oleh sistem. Apabila terdapat selisih harga, sistem tidak menampilkan warning dan tetap menyimpan data persis sesuai form inputan.
6. Klik tombol **Simpan Split**.
7. Sistem akan menyimpan baris-baris split sesuai inputan dan mencatat riwayat transaksi di log aktivitas.
