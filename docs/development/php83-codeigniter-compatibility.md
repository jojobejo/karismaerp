# Kompatibilitas CodeIgniter dengan PHP 8.2/8.3

## Ringkasan

Aplikasi KARISMA ERP menggunakan CodeIgniter 3.1.13 dan dikembangkan pada PHP 7.4. PHP 8.2 ke atas mengeluarkan deprecation saat framework membuat properti dinamis. Peringatan tersebut dapat tampil sebagai keluaran aplikasi dan mengganggu pengiriman header maupun proses login.

## Perubahan

- `CI_URI`, `CI_Router`, `CI_Controller`, dan `CI_DB_driver` diberi atribut `AllowDynamicProperties`. Pada PHP 8.2/8.3 ini menandai properti dinamis bawaan CI3 sebagai perilaku yang disengaja. Sintaks `#[...]` tetap terbaca sebagai komentar oleh PHP 7.4.
- Properti koneksi cadangan `CI_DB_driver::$failover` dideklarasikan secara eksplisit.
- Konfigurasi koneksi database dapat diisi dari environment server. Lihat panduan terpisah di `docs/database/koneksi-production-environment.md`.

## Cara menerapkan di production

1. Deploy file framework dan konfigurasi aplikasi yang telah diperbarui ke document root yang benar (log saat ini menunjuk ke `portal/main`).
2. Atur environment database production pada PHP-FPM/hosting sesuai panduan konfigurasi. Gunakan akun database aplikasi dengan hak minimum, bukan akun `root`.
3. Pastikan environment `CI_ENV=production` diterapkan oleh hosting. Jika `CI_ENV` tidak diatur, `index.php` memilih `development`, yang mengaktifkan tampilan error.
4. Buka halaman login dan satu halaman yang membutuhkan database. Deprecation dinamis seharusnya tidak lagi muncul. Jika koneksi masih gagal, periksa environment kredensial dan hak akses database pada hosting.

## Validasi dan batas bukti

- File yang diubah diperiksa dengan `php -l` pada PHP 7.4.33 lokal.
- Tidak ada perubahan skema atau data database.
- Akses production, kredensial hosting, dan uji browser login tidak tersedia dari checkout lokal; keberhasilan koneksi production perlu dipastikan setelah environment hosting disetel.
