# Analisis dan Checklist SSO Karisma ERP ke KIU PO

Tanggal analisis: 31 Oktober 2026  
Ruang lingkup: alur login user Portal Karisma ERP, akses ke KIU PO, penetapan akun kerja, dan pembentukan session KIU PO.

## Hasil yang diharapkan

1. Bram masuk ke Portal Karisma ERP menggunakan username Bram dan password Portal yang aktif.
2. Portal menampilkan kartu KIU PO hanya jika integrasi aktif dan hak akses Bram mengizinkannya.
3. Administrator sudah menetapkan satu akun KIU PO untuk identitas Portal Bram.
4. Saat Bram memilih **Mulai Bekerja**, browser memulai SSO ke Portal. Jika session Portal masih aktif, Bram tidak diminta login ulang ke Portal.
5. KIU PO menerima authorization code sekali pakai, memvalidasi callback, menemukan tepat satu akun lokal yang ditetapkan, memeriksa level akses lokal, lalu membuat session KIU PO.
6. KIU PO membuka dashboard tanpa meminta username/password akun lokal KIU PO.

## Temuan pada source code

| Bagian | Kondisi yang ditemukan | Tindak lanjut |
| --- | --- | --- |
| Portal SSO | Portal menyediakan `GET /sso/authorize`, `POST /api/sso/token`, dan `POST /api/sso/login-event`. Flow menggunakan Authorization Code + PKCE S256, code sekali pakai, pemeriksaan akses aplikasi, serta penetapan akun. | Pertahankan protokol dan validasi yang sudah ada. Verifikasi konfigurasi runtime serta UAT di server target. |
| Kartu Portal | URL kartu dibaca dari `launch_uri`; callback disimpan terpisah pada `redirect_uri`. | Pastikan `launch_uri` produksi benar-benar menuju endpoint awal SSO KIU PO dan `redirect_uri` sama persis dengan callback KIU PO. |
| Pemilihan akun | Portal mengirim klaim `client_account_username` hanya untuk akun yang ditetapkan bagi user/app. Jika katalog akun telah ada tetapi assignment tidak ada, authorize menolak akses. | Pastikan data Bram sudah memiliki izin KIU PO dan satu assignment akun yang tepat. |
| KIU PO | `Auth::sso_callback()` mengonsumsi state sebelum pertukaran code, memvalidasi respons Portal, membuat session baru, dan menolak akun lokal yang tidak unik atau level aksesnya tidak didukung. | Pastikan akun target sudah tersedia dan hanya cocok ke satu baris `tbpo_user`; role tetap dikelola di KIU PO. |
| Histori | KIU PO mengirim event konfirmasi login ke Portal setelah validasi akun. Jika event gagal dicatat, KIU PO membatalkan session. | Pastikan tabel histori Portal dan secret server KIU PO sesuai sebelum UAT. |
| Paparan password | Halaman kartu Portal saat ini menampilkan username dan password akun kerja yang didekripsi kepada user yang mendapat assignment. Password ini tidak dibutuhkan untuk proses SSO. | Prioritas tinggi: hapus tampilan password dari halaman user. Bila kredensial perlu tersedia untuk dukungan, batasi ke admin dan gunakan alur pengungkapan terkontrol serta audit. |
| Cookie session KIU PO | `application/config/config.php` pada checkout KIU PO menetapkan `cookie_secure = FALSE` dan `cookie_httponly = FALSE`. | Prioritas tinggi: untuk deployment HTTPS, set cookie session `Secure` dan `HttpOnly`, lalu verifikasi konfigurasi host/proxy agar HTTPS terdeteksi konsisten. |
| Konfigurasi lintas lingkungan | Konfigurasi KIU PO memuat secret lokal dan CA bundle; base URL Portal serta callback memiliki nilai default/env. Berkas config saja tidak membuktikan nilai environment produksi. | Audit nama variabel dan kecocokan nilainya di server tanpa mencetak secret ke log/tiket. Rotasi secret jika pernah terekspos. |
| Logout | Logout KIU PO mengakhiri session KIU PO, bukan session Portal. | Dokumentasikan perilaku ini; SSO lintas aplikasi belum menyediakan single logout. |

## Checklist pelaksanaan

### A. Identitas dan akses Bram di Portal

- [ ] Pastikan akun Bram yang akan dipakai adalah akun Portal yang benar; catat `auth_source` dan `user_id` internal untuk audit admin.
- [ ] Pastikan login Portal menggunakan password yang memang sudah disesuaikan dan akun masih aktif.
- [ ] Di **Akses Aplikasi**, izinkan `kiu_po` untuk identitas Bram yang tepat.
- [ ] Di **Akun** untuk aplikasi KIU PO, tetapkan akun kerja yang disetujui pemilik proses.
- [ ] Pastikan assignment hanya satu akun untuk Bram dan akun tersebut tidak terpasang ke user Portal lain.
- [ ] Pastikan akun kerja tidak berstatus ganda, masih aktif, dan pemilik bisnis menyetujui level akses KIU PO-nya.

### B. Registry dan koneksi SSO

- [ ] Pastikan klien `kiu_po` berstatus aktif pada Portal.
- [ ] Pastikan URL peluncuran menunjuk endpoint start SSO KIU PO (`/sso/login` atau route aktif yang setara), bukan hanya halaman aplikasi umum.
- [ ] Pastikan callback Portal terdaftar sama persis dengan `redirect_uri` efektif pada server KIU PO (skema, host, subfolder, dan path).
- [ ] Pastikan kedua alamat memakai HTTPS dengan sertifikat yang dipercaya browser dan validasi CA server-ke-server.
- [ ] Pastikan server KIU PO dapat menjangkau endpoint Portal melalui cURL; jangan menonaktifkan verifikasi TLS untuk mengatasi error sertifikat.
- [ ] Pastikan `client_id` KIU PO sama dengan `app_code` Portal (`kiu_po`) dan secret aktif cocok pada kedua sisi.
- [ ] Pastikan secret hanya tersimpan di environment/berkas konfigurasi server yang tidak masuk Git, tidak dikirim ke browser, dan tidak dicatat dalam log.
- [ ] Pastikan route aktif pada deployment sesuai: launch SSO, callback, endpoint token, dan endpoint login-event.

### C. Akun dan otorisasi KIU PO

- [ ] Pastikan username hasil assignment cocok persis dengan satu akun pada `tbpo_user`.
- [ ] Pastikan level akses lokal akun itu termasuk level yang didukung aplikasi (1–6) dan role/menu yang diberikan sesuai persetujuan.
- [ ] Pastikan tabel pemetaan `tbpo_sso_identity` tersedia dan indeks unik subject/akun aktif.
- [ ] Periksa apakah subject Portal Bram sudah dipetakan. Jika assignment diganti, pastikan perubahan target akun tersebut memang disetujui dan histori login dapat menunjukkan akun yang digunakan.
- [ ] Pastikan perubahan password akun KIU PO tidak dibutuhkan untuk SSO; password lokal tidak menjadi bagian dari klaim/token.

### D. UAT alur Bram dan kasus penolakan

- [ ] Login Portal sebagai Bram pada browser bersih, lalu pastikan kartu KIU PO tampil.
- [ ] Klik **Mulai Bekerja**; pastikan browser kembali ke dashboard KIU PO tanpa form login KIU PO.
- [ ] Cocokkan user/role lokal yang terbentuk dengan akun kerja yang ditetapkan; pastikan akses menu dan data sesuai role lokal.
- [ ] Pastikan histori login mencatat waktu, user Portal, aplikasi, dan username akun kerja yang tepat.
- [ ] Uji klik saat session KIU PO lama milik akun lain masih ada; pastikan identitas lama dibersihkan dan tidak terbawa.
- [ ] Uji user tanpa izin aplikasi dan user tanpa assignment; keduanya harus ditolak tanpa session KIU PO.
- [ ] Uji assignment ke username lokal tidak ada, duplikat, nonaktif/tidak memenuhi level; login harus gagal tertutup.
- [ ] Uji state salah/kedaluwarsa, code dipakai ulang, callback tidak cocok, secret salah, kegagalan TLS/jaringan, dan endpoint histori tidak tersedia.
- [ ] Logout KIU PO lalu validasi perilaku session Portal sesuai dokumentasi (Portal masih login sampai logout Portal dilakukan).
- [ ] Catat hasil UAT, environment, waktu, akun uji (tanpa password), bukti hasil, dan temuan sebelum aktivasi pengguna.

## Urutan prioritas

1. **P0 — Aman dan layak UAT:** hentikan paparan password akun kerja pada halaman user; aktifkan `Secure` dan `HttpOnly` untuk cookie session KIU PO; verifikasi tidak ada secret di repository/log; pastikan HTTPS dan registry klien cocok.
2. **P1 — Siap untuk Bram:** validasi identitas/izin/assignment Bram, akun KIU PO lokal unik dan aktif, tabel mapping serta tabel histori tersedia.
3. **P1 — UAT ujung ke ujung:** jalankan skenario sukses dan penolakan pada browser dengan konfigurasi deployment aktual.
4. **P2 — Operasional:** prosedur perubahan assignment, pergantian akun, rotasi secret, penanganan kegagalan Portal, dan logout lintas aplikasi.

## Keputusan modul

Belum terlihat kebutuhan membangun protokol SSO baru. Modul dasar sudah ada pada kedua aplikasi. Pekerjaan yang tersisa adalah hardening tampilan kredensial, audit konfigurasi/data di environment tujuan, lalu UAT lintas aplikasi. Tambahan opsional yang bernilai adalah halaman status kesehatan integrasi untuk admin (callback/launch terdaftar, tabel wajib tersedia, waktu pemeriksaan terakhir, dan status koneksi tanpa menampilkan secret), serta runbook support.

## Batas bukti

Analisis ini memeriksa source code dan dokumentasi lokal pada checkout Karisma ERP dan KIU PO. Analisis ini tidak membaca database produksi, tidak memeriksa environment variable server, tidak memvalidasi DNS/sertifikat target secara langsung, dan tidak melakukan login browser sebagai Bram. Karena itu, kesiapan data/konfigurasi aktual dan keberhasilan UAT masih harus dibuktikan dengan checklist di atas.

## Berkas utama yang ditelusuri

- Portal: `application/controllers/Sso.php`, `application/controllers/Portal.php`, `application/models/M_PortalSso.php`, `application/models/M_PortalAccess.php`, `application/models/M_PortalSsoClientAccounts.php`, `application/views/portal/index.php`.
- KIU PO: `application/controllers/Auth.php`, `application/libraries/Portal_sso.php`, `application/models/Auth/M_Portal_sso.php`, `application/config/portal_sso.php`.
- Catatan flow klien: `C:\xampp\htdocs\kiupobaru\docs\application\2026-10-30-sso-akun-klien-terpilih.md`.
