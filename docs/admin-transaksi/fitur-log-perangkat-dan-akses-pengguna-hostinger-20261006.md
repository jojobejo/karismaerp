# Fitur Log Perangkat & Akses Pengguna (Hostinger & Local)

## 1. Deskripsi Fitur
Fitur ini dirancang untuk mencatat dan memonitor secara real-time setiap perangkat yang mengakses atau membuka halaman aplikasi **Karisma ERP**. Fitur ini dirancang khusus agar tangguh (*resilient*), aman, dan siap digunakan ketika di-upload ke layanan hosting seperti **Hostinger** (Cloud / Shared Hosting / cPanel).

---

## 2. Informasi yang Dicatat
Setiap kali ada pengunjung/pengguna yang membuka halaman aplikasi, sistem secara otomatis mencatat:
1. **Jenis Perangkat (`device_type`)**:
   - `Desktop` (PC / Laptop)
   - `Mobile Phone` (Smartphone / HP)
   - `Tablet` (iPad / Android Tablet)
   - `Bot / Crawler` (Googlebot, Bingbot, dll)
2. **Merk / Model Perangkat (`device_brand`)**:
   - Apple iPhone, Samsung Mobile, Xiaomi / Redmi, OPPO, Vivo, Realme, Infinix, Apple Mac, Windows PC, dll.
3. **Sistem Operasi (`os`)**:
   - Windows 10/11, Android OS, iOS, macOS, Linux, dll.
4. **Browser (`browser`)**:
   - Google Chrome, Safari, Mozilla Firefox, Microsoft Edge, Opera, dll. beserta versinya.
5. **Alamat IP (`ip_address`)**:
   - Mendukung proxy Hostinger / Cloudflare (`HTTP_CF_CONNECTING_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_CLIENT_IP`, `REMOTE_ADDR`).
6. **Pengguna (`username` & `nama_user`)**:
   - Nama akun jika sudah login, atau `Guest` jika belum login/membuka halaman login.
7. **Lokasi Geografis Presisi & Provider Internet (ISP)**:
   - **Kecamatan (`district`)**: Contoh: `Kecamatan Sumbersari`, `Kecamatan Kaliwates`, dll.
   - **Kota / Kabupaten (`city`)**: Contoh: `Jember`, `Surabaya`, `Jakarta`, dll.
   - **Wilayah / Provinsi (`region`)**: Contoh: `Jawa Timur`, `DKI Jakarta`, dll.
   - **Negara (`country`, `country_code`)**: Contoh: `Indonesia` (`ID`) atau `Indonesia (Lokal)`.
   - **ISP / Provider (`isp`)**: Contoh: `Telkom Indonesia`, `Biznet`, `Indosat Ooredoo`, `XL Axiata`, dll.
   - **Koordinat GPS (`latitude`, `longitude`) & Akurasi (`accuracy`)**: Titik koordinat peta presisi.
   - **Sumber Lokasi (`location_source`)**:
     - `Lokal`: Jaringan Localhost / LAN Kantor (otomatis diset: *Indonesia (Lokal), Jember, Kecamatan Sumbersari*).
     - `GPS`: Deteksi presisi dari sensor GPS browser perangkat pengguna (bisa sampai tingkat kecamatan dan titik peta Google Maps).
     - `IP`: Perkiraan lokasi berbasis IP publik jaringan ISP (tingkat kota/kabupaten).
8. **Halaman / URL yang Diakses (`url_accessed`) & Metode HTTP (`GET` / `POST`)**.
9. **Waktu Akses (`created_at`)**: Tanggal dan jam real-time.

---

## 3. Komponen Teknis
1. **Hook Otomatis**: [Access_logger.php](file:///c:/laragon/www/karismaerp/application/hooks/Access_logger.php)
   - Bekerja secara global pada event `post_controller_constructor` CodeIgniter 3.
   - Mengambil data lokasi dan perangkat tanpa perlu modifikasi controller lain.
   - Dilengkapi proteksi *silent fail-safe* (`try-catch`) agar jika terjadi kendala jaringan atau database, aplikasi tetap berjalan normal tanpa error 500.
2. **Model Database**: [M_Access_Log.php](file:///c:/laragon/www/karismaerp/application/models/M_Access_Log.php)
   - Tabel: `tb_access_log`.
   - **Auto-create & Auto-alter Table**: Jika tabel belum ada atau belum memiliki kolom lokasi di Hostinger, model otomatis menyesuaikan struktur kolom (`city`, `district`, `region`, `country`, `country_code`, `isp`, `latitude`, `longitude`, `accuracy`, `location_source`) secara transparan.
   - **Method `update_gps_location()`**: Memperbarui log dengan koordinat GPS dan nama kecamatan saat browser mengirim sinyal lokasi.
   - **Sistem Cache Lokasi**: Menyimpan data lokasi berdasarkan IP di database, sehingga request berikutnya dari IP yang sama bernilai 0ms delay.
   - **Log File Cadangan**: Menyimpan ringkasan harian di `application/logs/device_access_YYYY-MM-DD.log`.
   - **Fitur Purge (Pembersihan Log)**: Mencegah pembengkakan penyimpanan database di Hostinger.
3. **Controller**: [C_Access_log.php](file:///c:/laragon/www/karismaerp/application/controllers/admin/C_Access_log.php)
   - Rute: `admin/access_log`
   - Endpoint: `admin/access_log/update_location` (menerima AJAX GPS & reverse geocoding server).
4. **Client-Side Script**: [footer.php](file:///c:/laragon/www/karismaerp/application/views/partial/main/footer.php)
   - Menjalankan `navigator.geolocation` secara asinkron di browser pengguna.
   - Melakukan reverse-geocoding via API client lalu mengirimkan detail kecamatan ke server.
5. **Tampilan Antarmuka**: [index.php](file:///c:/laragon/www/karismaerp/application/views/content/admin/access_log/index.php)
   - Kartu statistik (Total Hari Ini, % Desktop, % Mobile, IP Unik).
   - Kolom **Lokasi Perangkat** dengan badge:
     - Badge Hijau `Jaringan Kantor`: Menampilkan `Indonesia (Lokal), Jember, Kecamatan Sumbersari`.
     - Badge Biru `GPS Presisi`: Menampilkan `Kecamatan`, Kota, dan tautan langsung ke **Google Maps**.
     - Badge Abu `Estimasi IP`: Menampilkan Kota dan ISP provider.
   - Filter tanggal, filter jenis perangkat, dan pencarian komprehensif (mendukung pencarian nama Kecamatan).
   - Modal detail User-Agent dilengkapi tombol Google Maps koordinat presisi.

---

## 4. Cara Penggunaan & Akses
1. Login sebagai **Admin**.
2. Buka URL: `https://domain-anda.com/admin/access_log` (atau di localhost: `https://localhost/karismaerp/admin/access_log`).
3. Anda dapat mencari log berdasarkan nama kecamatan, kota, negara, atau ISP melalui kolom pencarian di halaman tersebut.
