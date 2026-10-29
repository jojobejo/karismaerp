# Persiapan database Portal ERP `portalerp`

Tanggal: 26 Oktober 2026

## Ringkasan audit

Audit membandingkan `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`, migration Portal sampai 24 Oktober 2026, konfigurasi CI3, dan pemakaian aktual pada `Auth`, `Portal`, `Sso`, `M_Auth`, `M_PortalAccess`, `M_PortalSso`, `M_PortalAppModules`, serta controller reset password dan API module.

Dump Agustus memiliki `tb_karyawan`, `tb_users`, dan `tb_login_log`, tetapi tidak memiliki tabel Portal baru. Skema SSO di sini menggabungkan migration klien/kode (22 Okt), kolom `client_secret_encrypted` (22 Okt), `launch_uri` (24 Okt), dan katalog module (24 Okt). Acuan Agustus tidak menjadi sumber schema Portal terbaru.

| Sumber | Nama tujuan | Status dan alasan |
|---|---|---|
| `tb_karyawan` | `portalerp_tb_karyawan` | Wajib untuk autentikasi/identitas. Kolom terbatas ke data yang dipakai Auth, session, daftar user Portal, SSO, dan akses HRD; ID tetap dipertahankan saat salin data. |
| `tb_users` | `portalerp_tb_users` | Wajib sebagai sumber autentikasi/identitas kedua. Hash/password, status, username, nama, NIK, jabatan, departemen, level, dan metadata login dipakai kode. |
| `tb_portal_app_access` | `portalerp_tb_portal_app_access` | Wajib untuk penyimpanan izin user per aplikasi dan pemeriksaan akses SSO. Identitas akun adalah nilai literal `tb_karyawan` / `tb_users` di `auth_source`, tanpa prefix. |
| `tb_portal_sso_client` | `portalerp_tb_portal_sso_client` | Wajib untuk daftar klien, callback, launch URL, hash dan ciphertext secret. Termasuk `client_secret_encrypted` dan `launch_uri` sesuai kode terbaru. |
| `tb_portal_sso_code` | `portalerp_tb_portal_sso_code` | Wajib untuk authorization code, PKCE, masa berlaku, pemakaian sekali, dan klaim identitas saat tukar token. |
| `tb_portal_app_module` | `portalerp_tb_portal_app_module` | Diperlukan untuk katalog module dan endpoint API module. Tanpanya, dashboard Portal dasar dan SSO tetap dapat bekerja; UI setting/API module tidak tersedia (API memberi 503). |
| `tb_login_log` | `portalerp_tb_login_log` | Opsional. Tanpanya login tetap berjalan; pencatatan aktivitas login dilewati. Struktur dump Agustus hanya dapat menampung sebagian payload model saat ini. |
| `tb_password_reset_request` | `portalerp_tb_password_reset_request` | Opsional. Tanpanya login berjalan, tetapi pengajuan dan halaman pemrosesan reset password tidak berfungsi. Definisi diambil dari migration 21 Okt. |
| `portal_user` | — | Tidak dibuat dan tidak disalin. Kode aktif Portal/Auth tidak membaca tabel ini; keberadaan file migration saja bukan kebutuhan runtime. |
| Katalog aplikasi di `M_PortalAccess::apps()` | — | Tetap sumber di kode. Tidak ada tabel katalog aplikasi baru. |

Kolom akun mengikuti tipe/default/collation sumber Agustus untuk kolom yang dipakai. Kolom administratif/bisnis ERP lainnya sengaja tidak disalin. Karena beberapa kolom sumber bertipe `NOT NULL` tanpa default (`tb_karyawan.tim`, `tb_karyawan.wilayah`, dan sebagian profil `tb_users`), salin data harus mengambil ID dan semua kolom eksplisit yang disertakan oleh file data terpisah.

### Kolom yang dipakai

- `tb_karyawan`: `id`, `nik`, `nm_karyawan`, `departemen`, `jobdesk`, `username`, `password`, `tim`, `wilayah`, `jobdesk_id`, `akses_lv_id`, `status`, `last_login`, `akses_lv`.
- `tb_users`: `id`, `username`, `password`, `level`, `jobdesk_hrd`, `nama_lngkp`, `nik`, `departement`, `jabatan`, `status`, `default_redirect`, `last_login`, `created_at`, `updated_at`.
- Tabel Portal menggunakan seluruh kolom pada file SQL skema; tabel reset mengikuti migration terbaru yang tersedia di `docs/database/password_reset_request_20261021.sql`.

### Perbedaan dan batas teknis

- Dump lama `tb_login_log` memakai `browser`, `platform`, `status_login` (ENUM `SUCCESS`/`FAILED`); `M_Auth::log_login()` menyiapkan `user_agent`, `status`, `message`, dan `nik`, lalu menyaring kolom yang tidak tersedia. Login tercatat, tetapi status login tidak tersimpan di skema lama. Jika audit log wajib memuat hasil login, perlu migration terpisah yang menambah kolom status kompatibel atau menyesuaikan model secara eksplisit.
- Dump `tb_users` memakai collation `utf8mb4_general_ci`; sumber `tb_karyawan` memakai `utf8mb4_unicode_ci`. Skema tujuan mempertahankan perbedaan ini.
- Tidak ada foreign key Portal ke akun ERP. Ini menjaga dukungan dua sumber identitas dan menghindari konflik tipe ID legacy.
- Login halaman Portal menggunakan guard sesi. Akses admin (pengelolaan izin, klien SSO, module, reset) juga dilindungi permission/master user-management. Database minimal ini tidak menyalin seluruh tabel menu, role, dan permission ERP; ketersediaan halaman admin bergantung pada lapisan otorisasi yang sudah berfungsi di deployment. Jangan mengklaim sistem ERP penuh dapat berjalan dengan database minimal ini.
- `M_Auth::get_auth_user()` mengambil akun dari kedua sumber; keberhasilan password/status ditentukan kode. Password legacy plaintext akan di-hash ulang saat login berhasil jika jalur pembaruan password tersedia.

## Berkas SQL

- `portalerp_database_20261026.sql`: buat database dan tabel prefiks, tanpa menyalin data. Semua tabel dibuat dengan `CREATE ... IF NOT EXISTS`, termasuk definisi SSO terbaru yang sudah digabungkan; aman diulang untuk tabel yang sudah ada dan tidak mengubah struktur yang telanjur ada.
- `portalerp_account_data_20261026.sql`: salin kolom akun terpilih secara terpisah dari `kiucoid_karismaerp` (nama yang saat ini tertulis di konfigurasi aplikasi). `ON DUPLICATE KEY UPDATE` menyelaraskan ID yang bentrok; file ini bersifat mutatif terhadap data tujuan. Pastikan nama database sumber benar, buat backup, dan review dampaknya sebelum menjalankan.

Tidak ada SQL yang dijalankan dalam pekerjaan ini. Database lokal dan data sumber tidak diubah. File skema tidak mengandung data akun, password, hash, atau data bisnis.

## Impor, konfigurasi, dan penggunaan

1. Pastikan MySQL/MariaDB menyediakan `utf8mb4`, gunakan akun database dengan hak `CREATE DATABASE` / `CREATE TABLE` untuk setup, dan siapkan kredensial aplikasi terpisah dengan hak minimum.
2. Backup database sumber dan tujuan sebelum migrasi data. Untuk arsip sumber, contoh: `mysqldump --single-transaction --routines --triggers kiucoid_karismaerp > backup_sumber.sql`. Amankan backup karena memuat kredensial hash dan data pribadi.
3. Import skema: `mysql -u <user> -p < docs/database/portalerp_database_20261026.sql`.
4. Tinjau source database dalam `portalerp_account_data_20261026.sql`; sesuaikan nama database bila koneksi aktual berbeda. Jalankan terpisah hanya jika memang memindahkan akun: `mysql -u <user> -p < docs/database/portalerp_account_data_20261026.sql`.
5. Atur environment `KARISMA_DB_PROFILE=portalerp` untuk proses PHP/Apache yang melayani Portal. `application/config/database.php` kemudian memilih database `portalerp` dan `$db['default']['dbprefix']='portalerp_'`. Tanpa variabel tersebut, konfigurasi default lama tetap memakai `kiucoid_karismaerp` tanpa prefix. Profil portalerp adalah basis minimum Portal, bukan salinan untuk semua modul ERP.
6. Jaga `PORTAL_SSO_ENCRYPTION_KEY` yang stabil (minimal 32 karakter) dan `PORTAL_SSO_KEY_PIN` melalui environment. Jangan menaruh secret SSO di repository. Daftarkan klien dari menu Integrasi SSO setelah permission admin tersedia, lalu isi URL callback dan launch.

Query Builder, `table_exists()`, `field_exists()`, `list_fields()`, dan method metadata CI3 dipanggil dengan nama logis seperti `tb_users`. Dengan `dbprefix='portalerp_'`, CI3 menambahkan prefix saat mengakses tabel. Kode tidak memakai raw SQL untuk tabel Portal. `auth_source` sengaja tetap berisi `tb_karyawan` / `tb_users` karena kode membandingkan literal itu, kemudian meneruskannya sebagai nama tabel logis ke Query Builder. Jangan simpan nilai `portalerp_tb_users` pada `auth_source`, dan jangan menambahkan prefix secara manual di pemanggilan model.

## Backup, verifikasi, dan rollback

- Sebelum setup atau refresh data, backup target: `mysqldump --single-transaction --routines --triggers portalerp > backup_portalerp.sql`. Backup akun dan SSO harus disimpan terbatas karena dapat memuat hash dan ciphertext secret.
- Verifikasi hanya struktur tanpa menampilkan data sensitif: `SHOW TABLES;`, `SHOW CREATE TABLE portalerp_tb_portal_sso_client;`, lalu cek kolom `launch_uri`, `client_secret_encrypted`, indeks unik code, dan tabel lain memakai `SHOW COLUMNS`.
- Setelah import data akun, bandingkan hitungan baris sumber/tujuan dan pemeriksaan ID, username, `status`, serta keberadaan password (jangan SELECT atau mencetak isi password/hash). Cek duplicate username lintas `tb_karyawan`/`tb_users`; username sama mungkin memang ada, login mencoba kandidat aktif satu per satu.
- Konfirmasi halaman Auth berhasil membaca kedua tabel, login akun uji aktif, dashboard `/portal` tampil, izin dari `tb_portal_app_access` mengatur kartu serta akses authorize, admin dapat membuat klien, dan flow SSO berhasil membuat lalu menukar authorization code satu kali dengan PKCE. Ulangi dengan akun tidak berizin, code bekas/kedaluwarsa, callback salah, serta verifier salah. Belum ada import/login/UAT yang dijalankan.
- Rollback aplikasi: hapus `KARISMA_DB_PROFILE` agar konfigurasi kembali ke koneksi lama. Rollback basis data: setelah backup, pilih tindakan sesuai pemakaian; untuk database baru sekali pakai dapat `DROP DATABASE portalerp` (menghapus semua data di dalamnya). Jangan gunakan sebagai rollback bila database target sudah berisi data yang harus dipertahankan. Untuk deployment yang memakai target sebelumnya, pulihkan dump backup, bukan menebak DROP tabel.

### Checklist cakupan

- [ ] Auth membaca akun dari `portalerp_tb_karyawan` dan `portalerp_tb_users`, dengan ID/status/password/username terjaga.
- [ ] `/portal` menampilkan katalog yang masih didefinisikan di `M_PortalAccess::apps()`.
- [ ] Izin tersimpan dan diperiksa melalui `portalerp_tb_portal_app_access`.
- [ ] Klien SSO menyimpan callback, launch URI, hash dan ciphertext secret.
- [ ] Authorization code tercipta, kedaluwarsa sesuai batas, sekali pakai, dan token mengembalikan identitas.
- [ ] Katalog module berjalan bila `portalerp_tb_portal_app_module` tersedia.
- [ ] Log login dan reset password diuji hanya bila tabel opsional masing-masing disertakan.
