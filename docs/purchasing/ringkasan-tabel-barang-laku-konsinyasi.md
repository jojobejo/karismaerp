# Ringkasan Tabel Barang Laku Konsinyasi

## Perubahan Tampilan

Tabel utama pada tab Penyelesaian — Barang Laku disederhanakan agar informasi penting lebih mudah dibaca. Kolom berikut tidak lagi ditampilkan langsung:

- Lokasi Kios / Pembeli.
- Harga Jual Kios.

Kolom **Qty LPB Masuk** ditampilkan untuk menunjukkan kuantitas awal yang diterima pada LPB barang dan lot terkait. Nilai ini berbeda dari Qty Laku, yang hanya menunjukkan jumlah barang yang sudah dibeli kios.

Kolom tanggal menggunakan **Tanggal LPB Masuk** dari tanggal surat jalan pada header LPB, bukan tanggal barang dinyatakan laku atau tanggal pembayaran kios.

Informasi tersebut tidak dihapus dari data. Pengguna dapat menekan tombol **Detail** pada kolom Aksi untuk melihat:

- customer atau lokasi kios;
- nomor SO dan faktur K;
- barang, lot, dan expired date;
- LPB asal dan referensi PO;
- qty terjual;
- informasi tagihan supplier dan jurnal.

Tombol Detail tersedia untuk transaksi berstatus Barang Laku maupun transaksi yang sudah dilunasi.

## Pembatasan Harga Penjualan

Harga jual kios dan subtotal penjualan tidak ditampilkan pada halaman Purchasing maupun dikirim melalui endpoint AJAX detail/tracking. Informasi harga penjualan hanya boleh diakses melalui modul yang berwenang di bagian Sales atau Keuangan.
