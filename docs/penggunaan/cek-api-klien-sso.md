# Cara menggunakan Cek API Klien SSO

## Persiapan admin

1. Login sebagai administrator Portal. Buka Integrasi SSO, tetapkan client_id, secret server, callback HTTPS, dan URL peluncuran menuju route start SSO klien.
2. Daftarkan/impor akun kerja yang sudah disepakati pemilik aplikasi. Pada Akses Aplikasi, berikan izin eksplisit dan tetapkan satu akun kerja untuk user Portal.
3. Buka **Cek API Klien SSO** (`admin/portal-sso-check`) melalui Integrasi SSO, Akses Aplikasi, atau `admin/api-documentation`.
4. Pilih aplikasi dan user. Salin callback yang benar-benar digunakan konfigurasi backend klien. Klik **Jalankan pemeriksaan**.
5. Perbaiki seluruh pemeriksaan gagal. Jika tabel belum tersedia, ikuti dokumen database terpisah. Jangan menganggap status prasyarat lulus sebagai bukti session atau izin modul klien sudah benar.

## Pengujian backend klien

Backend mengirim POST HTTPS ke `{PORTAL}/api/sso/check`, Content-Type application/x-www-form-urlencoded, dengan client_id, client_secret, sub, dan redirect_uri. Gunakan secret server yang sama dengan token; jangan masukkan secret ke browser/halaman pemeriksaan admin.

Pastikan respons HTTP 200, success=true, dan data.portal_ready=true. Periksa setiap checks. HTTP 200 saja tidak cukup. Field client_session_verified=false berarti pengujian browser masih diperlukan. Panduan lengkap token, callback, dan login-event tersedia di `admin/api-documentation`.

## UAT browser

Gunakan browser uji dengan identitas Portal yang dipilih. Form pemeriksaan tidak mengganti identitas admin.

| Skenario | Hasil yang diharapkan |
| --- | --- |
| User aktif, integrasi aktif, izin dan assignment benar | Kartu muncul; Mulai Bekerja membuka dashboard klien tanpa password lokal dan tanpa login Portal ulang selama session aktif |
| Integrasi nonaktif atau izin tidak ada/dicabut | Kartu tidak tampil; authorize/token ditolak |
| Katalog/assignment kosong | Mulai Bekerja diblokir atau authorize ditolak account_not_assigned |
| Akun lokal hilang/ganda/nonaktif atau role tidak sah | Klien menolak; tidak ada session baru |
| State callback salah atau transaksi klien sudah dipakai | Klien menolak sebelum pertukaran token |
| Secret/verifier/callback salah, code expired atau replay | Token menolak invalid_grant |
| Izin dicabut sesudah authorize sebelum token | Token menolak |
| Session klien lama milik user lain | Identitas lama dibersihkan; ID session diganti dan akun baru sesuai assignment |
| Menu/modul dan cakupan data | Seluruh route/API klien mengikuti role serta batas data lokal; akses tanpa izin ditolak server |
| Logout klien | Session klien berakhir; session Portal tetap mengikuti masa berlakunya sendiri |

Catat tanggal, lingkungan, app_code, sub user uji, skenario, hasil harapan, hasil aktual, bukti tanpa kredensial, dan penguji. Cocokkan histori login klien dengan akun yang ditetapkan. Jangan merekam password, secret, code, verifier, cookie, atau seluruh payload autentikasi.

## Batas dan penanganan

- Lulus Portal + sukses check backend + UAT callback/session/modul diperlukan sebelum menyatakan integrasi lengkap.
- Secret salah/nonaktif: periksa konfigurasi backend dan status registry; rotasi hanya melalui proses admin bila diperlukan.
- Callback gagal: cocokkan skema, host, path, subfolder, trailing slash, dan query secara persis.
- Sertifikat gagal: perbaiki trust chain/CA/revocation di lingkungan uji; jangan menonaktifkan verifikasi TLS klien.
- Histori merupakan laporan backend klien. Tetap verifikasi akun, role, dashboard, dan otorisasi nyata pada aplikasi klien.
