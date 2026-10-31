# Development Daftar Akun Klien Portal SSO

## Tujuan dan alur

Menu **Akun List** pada route `admin/portal-sso-clients` membuka pengelolaan kredensial yang disediakan klien aplikasi. Administrator mengunggah CSV, memetakan header username dan password, lalu sistem memasukkan akun ke tabel Portal sesuai `app_code`. CSV dengan username yang sudah ada memperbarui password dan mempertahankan PIC. Username duplikat dalam satu CSV membatalkan impor; baris dengan username/password kosong dilewati.

Setiap akun dapat diberi PIC dari sumber akun Portal `tb_karyawan` atau `tb_users`. Identitas PIC disimpan sebagai pasangan `pic_auth_source` dan `pic_user_id`. Password terenkripsi AES-256-GCM saat disimpan dan hanya dapat dilihat administrator setelah memasukkan `PORTAL_SSO_KEY_PIN`; penampilan password dicatat ke log tanpa mencatat nilainya.

Unggah SQL mentah tidak didukung dan tidak dieksekusi. Penggunaan CSV membatasi operasi database pada baris akun serta kolom yang dipetakan.

## Batasan dan keamanan

- CSV maksimal 5 MB dan 10.000 baris data, dengan pemisah koma atau titik koma.
- File CSV dipegang sementara selama 30 menit pada nama acak di direktori temporary server; file dihapus setelah impor selesai.
- Kunci enkripsi yang dipakai harus sama selama data password masih dibutuhkan. Kehilangan atau penggantian kunci akan membuat ciphertext lama tidak dapat dibuka.
- Modul hanya tersedia untuk sesi admin dashboard dan seluruh perubahan menggunakan token formulir.
- Pengelolaan ini adalah penyimpanan akun aplikasi klien di Portal; integrasi klien tetap bertanggung jawab atas cara memakai kredensial dan otorisasi di aplikasinya.

## Berkas implementasi dan panduan

- `application/config/routes.php`: route daftar, unggah, impor, penetapan PIC, tampilkan password, dan hapus akun.
- `application/controllers/admin/C_PortalSsoClients.php`: validasi admin, alur CSV, enkripsi/dekripsi, PIC, dan penghapusan.
- `application/models/M_PortalSsoClientAccounts.php`: penyimpanan per klien.
- `application/views/content/admin/portal_sso_client_accounts.php`: form, tabel akun, penetapan PIC, dan modal password.
- `docs/penggunaan/portal-sso-daftar-akun-klien.md`: panduan admin.
- `docs/database/portal-sso-daftar-akun-klien-20261029.md`: skema, kunci enkripsi, dan rollback.

## Validasi

Pemeriksaan sintaks PHP berhasil untuk route, controller, model, dan view. UAT impor CSV, pemeriksaan encryption key/PIN, penetapan PIC, dan tampilan browser belum dilakukan pada database pengembangan yang sudah menerapkan migration. Migration disiapkan, tetapi belum dijalankan pada database.
