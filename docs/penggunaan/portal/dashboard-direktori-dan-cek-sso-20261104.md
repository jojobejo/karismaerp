# Menggunakan direktori dan pemeriksaan SSO

1. Login ke `portal`. Admin melihat seluruh katalog; pengguna biasa melihat katalog tanpa Karisma Online. Kartu abu-abu bertanda Offline belum dapat dipakai.
2. Admin memilih **Atur URL aplikasi & callback** pada kartu. Form Integrasi SSO memilih `app_code` sesuai aplikasi. Isi URL peluncuran dan callback HTTPS sesuai backend aplikasi klien, lalu simpan. Untuk klien yang sudah ada, form edit terbuka otomatis.
3. Berikan akses aplikasi dan tetapkan satu akun kerja klien kepada user melalui pengaturan akses/akun. Kartu user menjadi Online jika integrasi aktif, akses diberikan, dan akun kerja tersedia.
4. Pada `admin/portal-sso-check`, pilih aplikasi klien. Pilihan identitas akan mengikuti user yang sudah terkait; callback tampil otomatis. Jika peringatan muncul, berikan izin dan tetapkan akun kerja dahulu.
5. Akun development `superuser` memakai password awal yang diberikan saat penyiapan. Ganti password sebelum digunakan lebih luas. Izin katalog tidak otomatis membuat akun lokal di setiap aplikasi klien.
