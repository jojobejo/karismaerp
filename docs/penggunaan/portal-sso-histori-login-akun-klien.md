# Panduan Histori Login Akun Klien

## Memantau pemakaian akun

1. Login menggunakan akun admin.
2. Buka menu Integrasi SSO, lalu pilih **Histori Login**. Admin juga dapat membuka route `admin/portal-sso-clients/login-history`.
3. Lihat ringkasan jumlah login tercatat, login hari ini, dan akun unik yang sudah digunakan.
4. Pilih aplikasi atau masukkan kata kunci untuk mencari user Portal, username akun aplikasi, atau alamat IP.
5. Tinjau waktu login, aplikasi tujuan, user Portal, akun lokal yang dipakai, IP, dan informasi browser/perangkat.

Tabel menampilkan maksimal 500 login terakhir. Username ditampilkan sebagai identitas akun, tetapi password dan ID session tidak disimpan. Event masuk ketika aplikasi klien mengonfirmasi bahwa session lokal berhasil dibuat. KIU PO saat ini mengirim konfirmasi tersebut; aplikasi klien lain harus mengimplementasikan API event agar pemakaiannya tampil.

Jika halaman menyatakan tabel histori belum tersedia, minta administrator database menerapkan migrasi `docs/database/portal_sso_login_history_20261030.sql`. Login menggunakan akun pilihan berhenti jika Portal tidak dapat mencatat konfirmasi, untuk menjaga histori penggunaan tetap utuh.
