# Halaman Panduan Integrasi SSO Klien

## Ringkasan

Menambahkan halaman web publik `/sso/panduan-klien` untuk membantu tim aplikasi klien (termasuk KIUPOBaru) mengintegrasikan login melalui SSO Portal. Halaman menjelaskan alur Authorization Code + PKCE S256 berdasarkan implementasi endpoint Portal yang ada.

## Perubahan aplikasi

- `application/controllers/SsoGuide.php` memuat halaman panduan tanpa mewajibkan login administrator, agar dapat dibagikan ke tim klien.
- `application/views/portal/sso_guide.php` menyediakan panduan responsif: persiapan registrasi, alur browser/backend, parameter endpoint, pemetaan identitas, keamanan, batas fitur, dan UAT.
- `application/config/routes.php` menambahkan route `sso/panduan-klien`.
- `application/views/portal/index.php` menambahkan tautan Panduan Integrasi SSO pada navigasi portal.

## Cara menggunakan

1. Buka `https://<host-portal>/sso/panduan-klien` atau pilih Panduan Integrasi SSO dari navigasi Portal.
2. Tim aplikasi klien menyiapkan domain HTTPS, callback, PKCE, session, dan pemetaan identitas lokal.
3. Admin Portal mendaftarkan `app_code`, URL peluncuran, callback HTTPS, dan secret, lalu memberikan akses kepada user uji.
4. Implementasikan authorize/callback server sesuai endpoint dan kontrak respons yang ditampilkan halaman.
5. Jalankan skenario UAT positif dan negatif sebelum merilis integrasi.

## Batas dan keamanan

- Halaman ini dokumentasi saja; tidak menambah atau mengubah proses autentikasi maupun API.
- Client secret hanya untuk backend dan tidak boleh dimasukkan ke browser atau log.
- Role dan permission tetap ditentukan aplikasi klien. Sinkronisasi role, provisioning, OIDC penuh, dan logout lintas aplikasi belum tersedia.
- Tidak ada perubahan skema database.

## Verifikasi

Review statis terhadap `Sso::authorize`, `Sso::token`, `M_PortalSso`, route dan halaman dokumentasi API existing untuk menyelaraskan parameter, validasi PKCE, masa berlaku code, dan klaim. Lint PHP belum dijalankan.
