# Informasi Nomor Lambung pada Trip Checker

Tanggal: 7 Oktober 2026

## Perubahan

Bagian **Trip Pengiriman Aktif** pada halaman `checker/so_loading` sekarang menampilkan:

- nomor lambung kendaraan;
- nomor polisi kendaraan sebagai informasi pendamping.

Data kendaraan mengikuti plan pengiriman yang telah ditentukan pada halaman `logistik/so_siap_loading`. Untuk ekspedisi kantor, ID kendaraan diterjemahkan melalui master `tb_op_plat`. Untuk ekspedisi luar, nilai kendaraan dari plan tetap ditampilkan secara langsung.
