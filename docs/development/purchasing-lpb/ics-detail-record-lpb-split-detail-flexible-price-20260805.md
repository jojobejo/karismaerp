# Development - Split Detail LPB Flexible Harga

Tanggal: 2026-08-05

## Scope

Route utama: `ics/detail_record_lpb`

Perubahan ini memperbarui modal `Split Qty dan Harga Barang` pada detail LPB. Qty hasil split tetap wajib sama persis dengan qty awal detail LPB, sedangkan harga satuan dan total harga hasil split boleh berbeda dari total harga awal.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Aturan Bisnis Baru

- LPB tetap harus berstatus `UNPOST` untuk bisa melakukan split detail.
- Minimal ada dua baris komposisi split.
- Setiap baris split wajib memiliki qty lebih dari 0.
- Total qty seluruh baris split wajib sama dengan `Qty In Awal`.
- Harga satuan tidak boleh minus.
- Total harga seluruh baris tidak lagi wajib sama dengan `Total Harga Awal`.
- Jika total harga berubah, sistem tetap menyimpan split dan mencatat selisih nilai pada log aktivitas.
- Baris detail yang pernah menjadi bagian split diberi badge `Split` pada view detail LPB dan view Purchasing.

## Alur Teknis

1. User membuka `ics/detail_record_lpb` dan memilih LPB berstatus `UNPOST`.
2. User menekan tombol split pada baris barang.
3. Modal menampilkan data acuan, baris `Data Sekarang`, baris `Split 1`, dan tombol `Tambah Baris`.
4. JavaScript memvalidasi jumlah baris, qty per baris, harga non-minus, dan total qty.
5. JavaScript tetap menampilkan selisih harga sebagai informasi, bukan sebagai blocker.
6. Submit memanggil `POST ics/ajax_split_lpb_detail`.
7. `M_Logistik::split_lpb_detail()` mengulang validasi server-side untuk qty dan harga non-minus.
8. Model meng-update baris asal, membuat baris detail baru, menyesuaikan `tb_lpb_batch`, dan menulis log `SPLIT_LPB_DETAIL`.
9. Query detail LPB membaca log `SPLIT_LPB_DETAIL` untuk menampilkan badge `Split`.

## Audit

Log aktivitas menyimpan:

- `data_before`: id detail awal, kode barang, qty awal, harga satuan awal, dan total harga awal.
- `data_after`: daftar baris hasil split, total qty input, total harga awal, total harga input, selisih total harga, dan marker rules harga fleksibel.
- `keterangan`: ringkasan split, selisih total harga, dan catatan user.

Admin dan Purchasing dapat meninjau aktivitas melalui panel `Log Aktivitas` pada detail LPB.
