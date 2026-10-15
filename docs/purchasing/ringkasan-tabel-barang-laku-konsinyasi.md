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

## Export Excel

Tab **Penyelesaian** menyediakan dua export Excel yang mengikuti filter supplier, periode, dan pencarian pada halaman:

- **Export Barang Laku** berisi rincian barang dengan qty laku yang masih menunggu pelunasan supplier.
- **Export Riwayat Barang Lunas** berisi transaksi berstatus lunas, termasuk invoice supplier dan nomor jurnal.

Kedua file memuat referensi settlement, supplier, barang, lot/expired, LPB/PO, kios, SO/faktur, kuantitas, harga PO, nilai tagihan, serta status. Harga jual kios tidak disertakan karena pembatasan akses informasi Sales.

## Penggabungan Riwayat Barang Lunas

Riwayat barang lunas digabung berdasarkan LPB asal, supplier, barang, dan lot yang sama. Beberapa settlement hasil pelunasan bertahap tidak lagi ditampilkan sebagai baris terpisah. Qty laku, DPP, PPN, dan total tagihan dijumlahkan pada satu baris kelompok.

Saat pengguna menginput atau memperbarui invoice supplier dari baris gabungan, nomor dan tanggal invoice diterapkan sekaligus ke seluruh settlement dalam kelompok tersebut. Keterangan jurnal pembelian setiap settlement terkait juga disinkronkan. Dengan demikian satu invoice supplier tidak perlu diinput berulang kali untuk transaksi yang dilunasi sebagai satu kelompok barang laku.

## Pagination

Daftar supplier Barang Laku, rincian barang laku per supplier, Riwayat Barang Lunas, dan tab Semua menggunakan pagination sebanyak 20 data per halaman. Navigasi halaman mempertahankan filter status, supplier, periode, dan pencarian. Nomor halaman yang melebihi jumlah halaman otomatis diarahkan ke halaman terakhir.
