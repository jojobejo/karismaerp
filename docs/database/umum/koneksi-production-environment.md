# Konfigurasi koneksi database production melalui environment

`application/config/database.php` membaca variabel berikut. Nilai lokal bawaan hanya berlaku pada `CI_ENV=development` melalui CLI, `localhost`, atau loopback.

| Variabel | Fungsi | Nilai lokal bawaan |
| --- | --- | --- |
| `KARISMA_DB_HOST` | Host MySQL dari panel hosting | `localhost` |
| `KARISMA_DB_USER` | User MySQL yang diberi akses ke database tujuan | `root` |
| `KARISMA_DB_PASSWORD` | Password user MySQL | kosong |
| `KARISMA_DB_NAME` | Nama database fisik | `portalerp` untuk profil Portal; `kiucoid_karismaerp_dev` untuk profil default |
| `KARISMA_DB_PROFILE` | `portalerp` mengaktifkan prefix tabel `portalerp_` | Profil default tanpa prefix |
| `CI_ENV` | Mode CodeIgniter; set `production` pada server publik | `development` pada `index.php` |

## Pemulihan Portal production

1. Di panel hosting, periksa database `u676129830_portalerp`, user MySQL yang memiliki akses ke database tersebut, password user itu, dan host MySQL yang diberikan hosting. Nama database **bukan** nama user; jangan menebak user dari nama database.
2. Berdasarkan daftar tabel production yang diberikan pada 4 November 2026, tabel fisik bernama `tb_users`, `tb_portal_app_access`, dan seterusnya tanpa prefix. Karena itu **jangan set `KARISMA_DB_PROFILE=portalerp`**; `dbprefix` harus kosong. Atur `CI_ENV=production`, `KARISMA_DB_HOST=localhost`, `KARISMA_DB_USER`, `KARISMA_DB_PASSWORD`, dan `KARISMA_DB_NAME=u676129830_portalerp` pada environment yang benar-benar terbaca proses PHP web. Jangan menyimpan password di Git, dokumen, `.htaccess` publik, atau URL.
3. Pastikan user MySQL tersebut ditetapkan pada database `u676129830_portalerp` dengan izin yang dibutuhkan aplikasi. Jika password diragukan, reset lewat panel hosting lalu perbarui environment server. Reload PHP/Apache sesuai mekanisme hosting.
4. Uji halaman `Auth` lewat HTTPS dan login akun uji. Bila masih `1045`, periksa user, password, host, dan hak akses database di panel hosting; bila muncul pesan konfigurasi 503, variabel environment belum terbaca oleh PHP atau belum lengkap. Periksa log aplikasi tanpa mencetak password.

Kode menolak koneksi nonlokal yang belum lengkap atau masih memakai `root` sebelum mencoba MySQL. Kesalahan koneksi tidak ditampilkan rinci pada halaman publik. File `application/config/database.php` yang diubah perlu dideploy pada instalasi `/portal/`; perubahan lokal saja tidak memperbaiki server production. Kredensial dan akses hosting belum tersedia dalam repository ini, sehingga koneksi production belum diverifikasi.

Cuplikan konfigurasi production yang diberikan pengguna memakai user database khusus dan `dbprefix` kosong; struktur tabel pada tangkapan layar cocok dengan `dbprefix` kosong. Namun cuplikan dan daftar tabel tidak membuktikan bahwa PHP web berhasil terhubung atau bahwa file yang sedang berjalan sama dengan checkout ini. Jika masih gagal, ambil status HTTP respons `POST /Auth/process` dan baris log aplikasi/server pada waktu kejadian, tanpa menyertakan password atau cookie.

## Dampak database

Tidak ada perubahan skema maupun data, migrasi, atau SQL yang perlu dijalankan untuk perbaikan konfigurasi ini.
