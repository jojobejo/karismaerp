# Penyegaran UI Pengelolaan Akses dan Master

## Route yang diperbarui

- `admin/portal-app-access`: pemilih akun, ringkasan akses, kartu aplikasi, tombol pilih semua/tidak ada, dan simpan tanpa reload melalui AJAX.
- `admin/portal-sso-clients`: ringkasan status, cari/filter integrasi, registrasi, aktivasi/nonaktifkan, rotasi secret, dan salin secret melalui AJAX.
- `master/user-management`: mempertahankan CRUD AJAX, menambah widget jumlah akun, hasil filter, serta jumlah akun aktif pada halaman; menambahkan escaping untuk isi tabel.
- `master/menu`: mempertahankan CRUD AJAX, menambah widget ringkasan, filter status, pencarian, pratinjau ikon/URL, dan memperjelas tampilan induk menu.

Pembaruan lanjutan: navigasi atas dan sidebar dihilangkan pada halaman `master/user-management` dan `master/menu`; konten melebar penuh. Tombol **Kembali ke Portal** menuju route `portal` dan ditempatkan sebaris dengan tombol aksi pada header keempat halaman.

Gaya halaman memakai palet biru-hijau dan kartu yang mengikuti AdminLTE/KARISMA ERP. Tidak ada perubahan aturan akses, alur CRUD user, maupun struktur database. Daftar akses yang disimpan tetap menggunakan endpoint yang ada; registrasi SSO mengembalikan secret hanya satu kali pada respons AJAX.

## Cara menggunakan

1. Dari menu admin, buka **Akses Aplikasi**; pilih akun dan sesuaikan toggle aplikasi. Widget berubah langsung dan tombol **Simpan akses** mengirim perubahan melalui AJAX.
2. Pada **Integrasi SSO**, cari atau filter klien, daftarkan callback HTTPS, atau ubah status/rotasi secret. Ikon key membuka modal PIN sebelum menampilkan secret terenkripsi. Untuk integrasi lama yang belum memiliki ciphertext, rotasi secret setelah migration database tambahan.
3. Pada **Master User**, gunakan filter departemen/jobdesk/akses/status, pencarian tabel, lalu tombol tambah/ubah/reset/nonaktifkan/hapus sesuai kewenangan yang sudah berlaku.
4. Pada **Master Menu**, gunakan pencarian dan filter status. Form menyediakan pilihan induk, pratinjau ikon dan route, urutan, serta status.

## Validasi dan batasan

PHP lint dijalankan pada controller/model dan view yang diubah; `git diff --check` tidak menemukan whitespace error. Tidak ada tes browser role/authenticated yang dijalankan. Tidak ada migrasi atau perubahan skema database. DataTables user dan menu tetap menggunakan endpoint server-side yang sudah tersedia.

## Halaman dokumentasi API di portal

- Route baru `admin/api-documentation` ditangani `admin/C_ApiDocumentation` dan hanya dapat dibuka oleh sesi administrator portal.
- Halaman merangkum alur SSO authorization code + PKCE, endpoint klien, klaim identitas, cakupan akses yang dikelola portal, langkah onboarding, checklist, pencarian, dan salin contoh kode.
- Tautan **Dokumentasi API** tersedia dari navigasi Portal serta sejajar dengan tombol aksi pada halaman Akses Aplikasi dan Integrasi SSO.
- Penggunaan API dan batas implementasi mengikuti `panduan-api-integrasi-portal-sso-hak-akses-20261022.md`. Ini dokumentasi untuk integrasi yang tersedia saat ini; bukan klaim bahwa portal sudah menyediakan API provisioning user/role klien atau OpenID Connect penuh.
- Tidak ada perubahan pada autentikasi SSO, endpoint API, maupun struktur database dalam penambahan halaman dokumentasi ini.

## Pengelolaan dan tampilan ulang secret SSO

- Baris integrasi memiliki tombol ikon key yang membuka modal PIN; AJAX mengirim PIN ke `admin/portal-sso-clients/reveal/{app_code}`. PIN salah menghasilkan notifikasi, lima kegagalan mengunci fitur selama lima menit pada sesi tersebut.
- Secret baru disimpan sebagai ciphertext AES-256-GCM di kolom `client_secret_encrypted` dan tetap diverifikasi menggunakan hash SHA-256. Kunci dekripsi harus stabil pada environment server melalui `PORTAL_SSO_ENCRYPTION_KEY` (minimal 32 karakter). PIN dapat diubah melalui `PORTAL_SSO_KEY_PIN`; default permintaan adalah `898989`.
- Terapkan `docs/database/portal_sso_secret_reveal_20261022.sql` sebelum menggunakan fitur. Data secret lama tidak dapat dibuka karena dahulu hanya menyimpan hash; rotasi diperlukan untuk membuat ciphertext.
- Risiko: PIN tetap enam digit memberi perlindungan tambahan yang terbatas dan akses dapat disalahgunakan bila PIN diketahui. Gunakan PIN environment yang unik, pertahankan akses admin terbatas, dan simpan salinan secret pada secret manager klien.
