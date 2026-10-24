# Peluncuran SSO Portal ke KIU PO

## Tujuan

Memperbaiki alur klik aplikasi dari Portal KarismaERP ke KIU PO. URL awal aplikasi dan callback SSO adalah dua tujuan berbeda. Kartu portal sekarang memakai URL peluncuran, sementara `redirect_uri` tetap dipakai khusus untuk membawa authorization code dan state ke KIU PO.

## Perubahan aplikasi

- Registrasi klien SSO memiliki `launch_uri`, ditampilkan dan diatur dari menu **Portal → Integrasi SSO**.
- Daftar kartu portal membaca URL peluncuran klien yang aktif. Bila belum ada URL, kartu tetap tidak dapat dibuka sampai administrator mengisi konfigurasi.
- Portal memvalidasi URL peluncuran dan callback sebagai HTTPS.
- Callback authorize/token dan izin per user/aplikasi yang sudah ada tetap menjadi gerbang penerbitan identitas. KIU PO tetap membuat session dan memakai role lokal `tbpo_user`.

## Konfigurasi KIU PO

Untuk instalasi lokal yang menggunakan subfolder contoh:

| Pengaturan | Nilai |
| --- | --- |
| URL peluncuran | `https://localhost/kiupobaru/` |
| Callback SSO | `https://localhost/kiupobaru/sso/callback` |
| `KARISMAERP_SSO_CLIENT_ID` | `kiu_po` |
| `KARISMAERP_SSO_PORTAL_BASE_URL` | `https://localhost/karismaerp/` |
| `KARISMAERP_SSO_REDIRECT_URI` | Callback di atas, persis sama dengan yang didaftarkan |
| `KARISMAERP_SSO_CLIENT_SECRET` | Secret `kiu_po` dari Integrasi SSO, simpan di environment server |

Setelah migration database portal diterapkan, buka **Integrasi SSO** dan simpan kedua URL. Penyimpanan menghasilkan/merotasi secret klien; masukkan secret baru ke environment KIU PO dan restart Apache. Pastikan schema pemetaan identitas KIU PO dari `database/sql/portal_sso_identity_20261023.sql` juga diterapkan sebelum login.

## Alur penggunaan

1. User login ke Portal KarismaERP dan memiliki akses aplikasi `kiu_po`.
2. User memilih kartu KIU PO; browser membuka URL awal KIU PO.
3. KIU PO membuat state dan PKCE verifier pada session server, lalu mengirim user ke Portal. Bila session portal masih aktif, portal langsung memeriksa izin; bila tidak, user login portal satu kali dan portal melanjutkan permintaan.
4. KIU PO menerima callback, memvalidasi state dan menukar code melalui backend HTTPS.
5. KIU PO menghubungkan subject portal ke satu akun lokal yang telah disediakan, lalu membuka dashboard dengan role lokal.

User tanpa izin aplikasi ditolak portal. Pengguna dengan izin tidak perlu mengisi ulang username/password KIU PO. Akun lokal dan role tetap harus tersedia. Logout KIU PO mengakhiri session KIU PO; belum ada single logout lintas aplikasi.

## Pembaruan alur pergantian akun

- URL peluncuran KIU PO selalu memulai pemeriksaan SSO, sekalipun browser masih memiliki session lokal.
- KIU PO menghapus identitas/session user lokal sebelumnya sebelum redirect ke Portal. Session baru hanya dibuat setelah state, code, izin Portal, mapping subject ke satu akun lokal, dan level akses lokal lolos validasi.
- Bila user Portal atau mapping lokal tidak cocok, akses ditolak dan akun sebelumnya tidak tertinggal.
- Untuk berpindah user Portal, logout dari Portal lalu login sebagai user tujuan sebelum memilih kartu KIU PO. Logout KIU PO saja tidak mengakhiri session Portal.
- Role/menu KIU PO tetap berasal dari data akun lokal; Portal hanya memutuskan izin masuk aplikasi.

## Dokumentasi API admin

Route `admin/api-documentation` kini memuat langkah admin dan pengguna KIU PO, perbedaan URL peluncuran dan callback, aturan pergantian akun, kebutuhan server/akun lokal, serta ringkasan endpoint SSO. Daftar migration ditampilkan di bagian database dan detailnya tetap berada di `docs/database/`; tabel mapping subject KIU PO berada pada database aplikasi klien. Perubahan alur session tidak membutuhkan DDL baru.

## Validasi yang masih diperlukan

Perubahan kode dan migration ini belum diaktifkan pada database target. Sebelum penggunaan bersama, administrator perlu menerapkan migration, mengatur URL dan secret pada kedua server, lalu menjalankan UAT browser dengan user yang diizinkan dan ditolak. UAT juga perlu memastikan role lokal KIU PO tidak berubah, code hanya sekali pakai, state tidak dapat dipalsukan, dan logout lokal bekerja.
