# Perbaikan koneksi database Portal production

Tanggal: 4 November 2026.

## Masalah dan perubahan

Laporan production menunjukkan `mysqli::real_connect(): (HY000/1045): Access denied for user 'root'@'localhost'` saat konstruktor `Auth` memuat model. Ini menandakan koneksi memakai user `root`; pesan `using password: YES` menunjukkan file/konfigurasi di server tidak identik dengan fallback lokal repository yang berpassword kosong. Isi file server dan izin MySQL belum dapat diperiksa dari workspace.

`application/config/database.php` kini membaca `KARISMA_DB_HOST`, `KARISMA_DB_USER`, `KARISMA_DB_PASSWORD`, dan `KARISMA_DB_NAME`. Profil `KARISMA_DB_PROFILE=portalerp` tetap menetapkan prefix `portalerp_`; nama database fisik bisa berbeda melalui `KARISMA_DB_NAME`. Default `root` hanya tersedia untuk development lokal. Koneksi nonlokal tanpa parameter lengkap atau yang memakai `root` berhenti dengan pesan 503 umum sebelum memanggil MySQL. `db_debug` hanya aktif untuk development lokal. Contoh kredensial lama yang terkomentar dihapus.

## Cara menerapkan

Deploy perubahan kode ke instalasi Portal, atur environment dan hak akses MySQL sesuai [panduan database](../../database/umum/koneksi-production-environment.md), lalu uji `Auth` dan login menggunakan akun uji. Set `CI_ENV=production` di server publik agar PHP tidak menampilkan warning. Verifikasi nama tabel fisik sebelum mengaktifkan prefix Portal. Jika pengaturan environment lewat panel hosting tidak diteruskan ke proses PHP, gunakan mekanisme konfigurasi rahasia di luar repository yang disediakan hosting dan verifikasi pembacaannya; jangan menaruh password ke source.

## Batas verifikasi

Lint PHP dan simulasi pembacaan konfigurasi lokal memeriksa sintaks serta pemilihan nilai. Akses production, password user MySQL, dan UAT login production belum tersedia; keberhasilan koneksi server belum diklaim.

## Klarifikasi konfigurasi production

Pengguna menunjukkan konfigurasi production dengan user MySQL khusus, database `u676129830_portalerp`, dan `dbprefix` kosong. Tangkapan layar phpMyAdmin memperlihatkan tabel `tb_users` dan `tb_portal_*` tanpa prefix tambahan; maka profil `KARISMA_DB_PROFILE=portalerp` tidak boleh diaktifkan untuk database tersebut. Laporan lama mengenai `root@localhost` kemungkinan berasal dari konfigurasi atau waktu deploy yang berbeda; perlu log terbaru untuk menentukan penyebab peringatan login saat ini. Kode checkout ini memerlukan semua variabel `KARISMA_DB_*` pada request production, sehingga konfigurasi array hardcode di server tidak sama dengan implementasi checkout. Pesan peringatan yang dilaporkan tidak ditemukan sebagai string literal dalam source checkout, sehingga versi kode server juga perlu diperiksa. Password MySQL yang pernah dibagikan harus diganti dan disimpan di konfigurasi rahasia server.
