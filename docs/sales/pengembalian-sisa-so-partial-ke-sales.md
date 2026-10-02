# Pengembalian Sisa Sales Order Partial ke Sales

## Tujuan

Memungkinkan Admin SC mengembalikan sisa barang Sales Order ke Sales walaupun sebagian barang pada SO yang sama sudah terfaktur dan masuk Delivery Order.

## Aturan proses

- Sisa barang dihitung dari `qty_siap_faktur` (atau `qty` jika belum diisi) dikurangi `qty_faktur` pada setiap detail SO.
- Faktur dan Delivery Order terdahulu tetap menjadi histori pengiriman dan tidak diubah.
- Jika belum ada faktur aktif dan total `qty_faktur` masih 0, status SO kembali menjadi `open`.
- Jika sudah ada faktur aktif atau total `qty_faktur` lebih dari 0, status SO menjadi `partial`.
- Jika masih ada kuantitas outstanding, SO tetap dapat dikembalikan meskipun pengiriman sebelumnya sudah masuk Delivery Order.
- Nilai `checker_loaded` hanya direset untuk detail yang masih memiliki kuantitas outstanding.
- Plan pengiriman lama (tanggal, jenis pengiriman, driver, nomor lambung, dan urutan) direset saat SO dikembalikan. Ketika SO masuk loading lagi, Logistik harus mengisi plan pengiriman baru.
- Jika tidak ada kuantitas outstanding dan faktur SO sudah masuk Delivery Order, pengembalian tetap ditolak.

## Kasus acuan

Pada `SO/011026/0003`, barang yang telah terfaktur tetap tercatat pada DO sebelumnya, sedangkan sisa barang yang belum terfaktur dapat dikembalikan ke Sales untuk dijadwalkan ulang.
