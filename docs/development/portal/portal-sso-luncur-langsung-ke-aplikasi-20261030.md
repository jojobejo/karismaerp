# Peluncuran Portal Langsung ke Login SSO Aplikasi

## Perubahan

Kartu POERP pada Portal kini membuka `https://10.10.10.26/poerp/sso/login` secara langsung. Sebelumnya kartu membuka halaman awal `/poerp/`, yang baru kemudian mengalihkan browser ke route SSO. Callback tetap `https://10.10.10.26/poerp/sso/callback`.

Endpoint awal SSO membersihkan identitas lokal lama, memulai authorization code + PKCE, lalu Portal mengautentikasi user dan memeriksa akses serta penetapan akun kerja. POERP membuat session lokal memakai akun yang ditetapkan admin. Secret dan password tidak dikirim melalui URL atau browser.

## Hasil aktivasi lokal

URL peluncuran pada registry klien aktif `kiu_po` di database Portal lokal telah diperbarui satu baris. Callback tidak berubah, status klien tetap aktif, dan secret tidak dirotasi.

## Validasi

Nilai URL setelah simpan dibaca ulang dan cocok dengan endpoint awal SSO POERP. UAT browser belum dilakukan; pastikan user Portal masih login, akses aplikasi diberikan, akun kerja ditetapkan, dan server dapat menjangkau Portal melalui HTTPS.
