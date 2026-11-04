# Dashboard direktori dan cek SSO (4 November 2026)

## Perubahan aplikasi

- Route `portal` menampilkan seluruh aplikasi direktori kepada administrator, termasuk yang tersembunyi. Pengguna biasa melihat seluruh aplikasi yang tidak tersembunyi, kecuali `karisma_online`.
- Kartu admin berwarna jika klien SSO aktif dan memiliki URL peluncuran. Kartu lain abu-abu dengan status Offline. Tombol pengaturan membuka form URL aplikasi dan callback dengan `app_code` aplikasi terpilih.
- Kartu pengguna berwarna dan Online hanya jika integrasi aktif, izin aplikasi diberikan, dan akun kerja telah ditetapkan. Kartu yang belum siap abu-abu dan tidak dapat diluncurkan. Status akun kerja ditampilkan terpisah.
- Route `admin/portal-sso-check` memuat identitas yang memiliki izin serta penetapan akun kerja pada aplikasi terpilih. Callback dibaca dari konfigurasi klien tersimpan; server memvalidasi ulang pilihan saat formulir dikirim. Bila tidak ada identitas terkait, halaman memberi peringatan.
- `superuser` memperoleh izin eksplisit seluruh aplikasi direktori pada database development. Hak aplikasi klien tetap bergantung pada penetapan akun kerja dan kebijakan SSO klien.

## Validasi

Pemeriksaan `php -l` pada controller, model, dan view yang berubah berhasil; `git diff --check` bersih. Query database development menunjukkan 13 dari 13 aplikasi memiliki izin untuk `superuser`. Belum dilakukan UAT browser dengan session admin/customer atau uji login SSO aplikasi klien.

## Batasan

Status Online berarti konfigurasi Portal dan akses siap menurut database Portal, bukan pemeriksaan jaringan langsung atau bukti session aplikasi klien. Callback yang dibaca adalah callback terdaftar di Portal; kecocokan dengan konfigurasi efektif di backend klien tetap perlu uji backend ke backend.
