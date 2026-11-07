# Perbaikan Refresh Plafon Customer Sales Order

## Masalah

Tombol **Update Data Customer** pada halaman `sales_order/create` menampilkan pesan `Gagal mengambil data plafon customer dari API.`

## Penyebab

Konfigurasi lokal menggunakan `https://plafon.kiu.co.id`. Domain tersebut tidak dapat di-resolve oleh DNS. API plafon yang aktif berada di `https://plafon.karismaerp.com`.

## Perbaikan

- Base URL pada `application/config/plafon_api_local.php` diarahkan ke domain API yang aktif.
- Controller sekarang memberikan pesan penyebab yang lebih spesifik untuk konfigurasi kosong, ekstensi cURL tidak aktif, kegagalan koneksi, status HTTP API, dan respons data tidak valid.
- Detail kegagalan koneksi dicatat melalui log CodeIgniter tanpa mencatat API key.

## Validasi

Endpoint `sales_order/refresh_plafon_customers` berhasil mengembalikan HTTP 200 dan memperbarui 3.600 customer ke database lokal.

File konfigurasi lokal mengandung kredensial dan tetap diabaikan oleh Git.
