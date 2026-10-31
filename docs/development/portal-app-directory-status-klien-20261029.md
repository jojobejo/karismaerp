# Status konfigurasi klien pada Application Directory

## Perubahan

Kartu di halaman Portal kini menampilkan aksen berwarna apabila integrasi klien untuk aplikasi tersebut aktif dan memiliki URL peluncuran (`launch_uri`). Kartu yang belum memenuhi kondisi itu ditampilkan dalam warna abu-abu. Label status dan teks aksesibel juga menjelaskan apakah klien sudah terhubung. URL akses kartu menggunakan `launch_uri` milik klien aktif; jika belum tersedia, Portal mempertahankan URL katalog aplikasi.

Status dibaca dari `tb_portal_sso_client` melalui daftar URL peluncuran terdaftar. Integrasi yang nonaktif, URL peluncuran kosong, tabel yang belum tersedia, atau kolom `launch_uri` yang belum tersedia dianggap belum diatur. Penetapan hak akses pengguna tetap mengikuti konfigurasi **Akses Aplikasi** yang sudah ada.

## Berkas terkait

- `application/models/M_PortalAccess.php`: menambahkan status `client_configured` dan memilih URL akses klien aktif pada data aplikasi untuk portal.
- `application/views/portal/index.php`: menerapkan gaya abu-abu/berwarna dan label status pada kartu.
- `docs/penggunaan/portal-application-directory-20261029.md`: panduan pemeriksaan dan pengaturan status.

## Database

Tidak ada perubahan skema database dan tidak ada SQL baru. Status memanfaatkan tabel integrasi klien yang sudah ada. Jika kolom URL peluncuran belum tersedia, terapkan migration yang telah ada di `docs/database/portal_sso_launch_uri_20261024.sql` sesuai prosedur database.

## Validasi

Perubahan ditinjau pada sumber model dan view. Pemeriksaan tampilan pada browser serta UAT admin dan akun pengguna belum dilakukan.
