# API SSO akun yang ditetapkan dan pemeriksaan admin

Tanggal: 31 Oktober 2026.

## Tujuan dan dasar perubahan

Dokumentasi awal ditinjau melalui controller `C_ApiDocumentation`, view `api_documentation.php`, route SSO, model, dan analisis `docs/application/analisis-checklist-sso-karismaerp-kiupo-20261031.md`.
API authorization code + PKCE sudah tersedia. Sebelumnya katalog kosong membuat assignment opsional, izin aplikasi tanpa baris eksplisit dapat diterima, kartu belum selalu mengikuti integrasi aktif, dan panduan belum mendokumentasikan klaim akun kerja.

Alur yang dituju: login Portal aktif → kartu integrasi aktif dan berizin → satu akun kerja ditetapkan admin → Mulai Bekerja membuka route start SSO klien → authorize menggunakan session Portal → callback klien memvalidasi state → backend menukar code → klien memeriksa tepat satu akun lokal serta level akses → session baru dan dashboard tanpa password lokal.

## Implementasi

- `GET /sso/authorize`: user aktif, izin eksplisit, serta assignment wajib termasuk ketika katalog kosong. Code berlaku 120 detik dengan PKCE S256.
- `POST /api/sso/token`: tetap menerima form client_id, client_secret, code, redirect_uri, code_verifier. Izin dan assignment diperiksa ulang saat pertukaran. Respons memuat user.sub, user_id, auth_source, username, name, app_code, state, dan client_account_username. Klaim username Portal bukan username lokal.
- Assignment menggunakan join yang terikat app_code dan harus tepat satu hasil dengan username tidak kosong. Klien wajib menolak akun lokal hilang, ganda, nonaktif, atau role tidak sah; tanpa provisioning/fallback otomatis.
- Kartu Portal hanya tampil dengan integrasi aktif, launch_uri tersedia, dan izin eksplisit. Password kerja tidak didekripsi/ditampilkan pada Portal user.
- `POST /api/sso/check`: diagnosis baca-saja dari backend klien; autentikasi client_id/client_secret, sub dan callback wajib. Secret terikat aplikasi pemiliknya. Tidak membuat code atau session, dan tidak mengakses URL dari input.
- `GET/POST /admin/portal-sso-check`: halaman admin dengan pilihan aplikasi/user, input callback efektif klien, laporan pass/fail, dan petunjuk UAT. Dilindungi session admin serta token formulir untuk POST. Tidak menyamar sebagai user yang dipilih.
- `admin/api-documentation` diperbarui dengan empat endpoint SSO, kontrak callback, assignment, error, database, serta batas diagnosis. Tautan pemeriksaan tersedia di Portal admin, Integrasi SSO, Akses Aplikasi, dan dokumentasi.
- Respons JSON SSO memakai Cache-Control no-store dan Pragma no-cache.

## Kontrak diagnosis

Kirim form URL-encoded melalui HTTPS dari backend: client_id, client_secret, sub (misalnya tb_users:7), redirect_uri. Jangan menyimpan secret dalam browser, contoh publik, atau log.

HTTP 200 dan success=true hanya berarti pemeriksaan berjalan. `data.portal_ready` merangkum checks, `client_account_username` menunjukkan assignment, dan `client_session_verified` selalu false karena Portal tidak memeriksa database/session klien. HTTP 401 invalid_client untuk kredensial salah/nonaktif, 400 invalid_request untuk input tidak valid, 405 method_not_allowed untuk metode lain.

Diagnosis admin memeriksa keberadaan hash secret, bukan kecocokan secret pada server klien. Request check dari backend klien diperlukan untuk membuktikan koneksi dan kecocokan secret. Histori login adalah laporan klien, bukan bukti seluruh izin modul benar.

## Dampak kompatibilitas

Klien/user yang sebelumnya mengandalkan akses implisit atau mapping tanpa assignment kini ditolak. Administrator harus menyimpan izin eksplisit dan penetapan akun terlebih dahulu. Launch URI wajib menunjuk route start SSO klien, bukan form login umum. Assignment yang berubah sebelum token ditukar mengikuti data terbaru; akun lokal tetap diverifikasi oleh klien.

Session klien yang sudah aktif tidak otomatis dicabut. Perubahan ini tidak mengimplementasikan OIDC penuh, refresh token, provisioning, atau single logout. Implementasi klien harus mengikuti kontrak callback; perubahan ini berada pada checkout Portal.

## Validasi

- Lint PHP pada controller, model, view, route, dan skrip pengujian yang diubah: lulus.
- `C:\xampp\php\php.exe tests/portal_sso_contract_test.php`: uji model/controller dengan penyimpanan memori; meliputi sukses, replay, expired, secret/code/verifier/callback salah, klien/user nonaktif, izin hilang/dicabut, assignment hilang/ganda/kosong/tabel hilang, diagnosis baca-saja, autentikasi check, metode, dan batas aplikasi.
- `git diff --check`: lulus. Perubahan yang sudah ada di working tree dipertahankan.
- HTTP lokal mengarahkan ke HTTPS (301). Request HTTPS lokal berhenti pada validasi revokasi sertifikat Windows: CRYPT_E_NO_REVOCATION_CHECK. Tidak menonaktifkan validasi TLS.
- Belum dilakukan UAT browser terautentikasi, verifikasi database runtime, uji konkurensi pada MySQL, atau login end-to-end aplikasi klien. Pengujian memori tidak membuktikan perilaku transaksi/kunci database riil.

Panduan penggunaan: `docs/penggunaan/cek-api-klien-sso.md`. Catatan database terpisah: `docs/database/api-sso-akun-ditetapkan-dan-diagnostik-20261031.md`.
