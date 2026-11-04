# Konfigurasi koneksi database production melalui environment

## Tujuan

Konfigurasi database CodeIgniter tidak lagi mengharuskan kredensial production disimpan di source. `application/config/database.php` membaca variabel berikut:

| Variabel | Fungsi | Nilai lokal bawaan |
| --- | --- | --- |
| `KARISMA_DB_HOST` | Host database | `localhost` |
| `KARISMA_DB_USER` | Akun database aplikasi | `root` |
| `KARISMA_DB_PASSWORD` | Kata sandi akun database | kosong |
| `KARISMA_DB_NAME` | Nama database; mengesampingkan pilihan profil | Sesuai profil |
| `KARISMA_DB_PROFILE` | `portalerp` untuk database/prefix Portal | Profil ERP utama |

## Pengaturan production

Atur `KARISMA_DB_HOST`, `KARISMA_DB_USER`, dan `KARISMA_DB_PASSWORD` melalui panel hosting atau konfigurasi PHP-FPM/virtual host yang berlaku. Atur `KARISMA_DB_NAME` jika nama database production berbeda dari nama bawaan profil. Jangan menaruh nilai rahasia ke dalam Git atau dokumentasi.

Gunakan akun database khusus aplikasi dengan akses hanya ke database yang diperlukan. Jangan gunakan `root` untuk koneksi web production.

## Diagnosis log saat ini

Log production menunjukkan `root@localhost` dengan password kosong, kemudian ditolak MySQL/MariaDB. Itu adalah kegagalan kredensial/izin database, bukan deprecation PHP. Setelah environment berisi kredensial yang benar, uji koneksi ulang. Jika hosting tidak meneruskan environment ke PHP, konfigurasi perlu disetel pada mekanisme environment yang didukung hosting; jangan menyalin password production ke source.

## Dampak database

Tidak ada perubahan tabel, kolom, indeks, data, migrasi, ataupun dump SQL. Dokumen ini hanya menjelaskan parameter koneksi aplikasi.
