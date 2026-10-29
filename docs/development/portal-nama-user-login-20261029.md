# Nama Pengguna pada Header Portal

Tanggal: 2026-10-29

Header Portal menampilkan nama pengguna yang sedang login di samping tombol Logout. Nama dipilih dari session `nama_user`, lalu `nama`, kemudian `username` sebagai fallback. Nilai ditampilkan dengan escaping HTML.

Pengguna selain `admin` tetap hanya melihat identitas akun dan tombol Logout; menu administrasi tidak berubah.

## Berkas

- `application/controllers/Portal.php`: meneruskan nama pengguna dari session ke view.
- `application/views/portal/index.php`: menampilkan chip nama di header dengan tata letak responsif.

## Validasi

Belum dilakukan UAT browser dengan beberapa jenis akun.
