## Alur Purchasing
1. Login sebagai `purchasing`.
2. Buka dashboard section `PURCHASING`.
3. Cek `Pending PO` untuk PO yang belum selesai.
4. Buka `Data PO` / `ics/icspo`.
5. Filter LPB yang belum invoice, belum faktur pajak, atau belum afirmasi harga.
6. Buka detail LPB.
7. Update invoice dan faktur pajak.
8. Cek harga detail terhadap invoice supplier.
9. Jika harga benar, lakukan `Accept` harga.
10. Jika semua data sudah benar, lakukan `POST` / Rekam LPB.
11. Cek `Laporan LPB` untuk memastikan transaksi tercatat.
12. Jika ada barang harus dikembalikan, buat `Retur Pembelian`.
13. Jika ada koreksi harga setelah LPB berjalan, gunakan `Adjustment Harga LPB`.
14. Koordinasikan hasil retur/adjustment dengan Accounting.

## Alur Saat Ada Selisih
 Selisih PO vs barang datang
1. Cek LPB terkait di `Data PO`.
2. Pastikan qty datang sudah dicatat Logistik.

### Invoice belum masuk
1. Filter `Belum Invoice`.
2. Buka detail LPB.
3. Update nomor dan tanggal invoice.
4. Jika satu LPB memiliki beberapa invoice, gunakan `Pecah Invoice`.

### Faktur pajak belum masuk
1. Filter `Belum Pajak`.
2. Buka detail LPB.
3. Update nomor dan tanggal faktur pajak.

### Harga invoice berbeda
1. Jika LPB masih `UNPOST`, update harga detail dari detail LPB.
2. Jika LPB sudah berjalan/posted dan perlu koreksi harga, gunakan `Adjustment Harga LPB`.
3. Jangan koreksi langsung dari database.

### Barang harus dikembalikan
1. Buka `Retur Pembelian`.
2. Pilih LPB final.
3. Buat draft retur.
4. Submit dan lakukan verifikasi Purchasing.
5. Lanjutkan ke Accounting/posting sesuai otorisasi.