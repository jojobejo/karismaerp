# Akses Aplikasi dari User Management

## Perubahan

Menu Akses Aplikasi tidak lagi tampil pada navigasi administrasi di halaman Portal. Kolom **Akses** pada User Management hanya menampilkan tombol gear. Pengelola klik tombol pada baris akun yang ingin diubah; tautan membawa `source=tb_karyawan` dan ID user sehingga halaman `admin/portal-app-access` langsung menampilkan akun tersebut. Jika route dibuka tanpa identitas user, aplikasi kembali ke User Management.

Pemilih pengguna dihilangkan dari halaman akses. Ringkasan halaman menampilkan nama karyawan, departemen, dan jobdesk tanpa username atau sumber akun. Formulir tetap menyimpan daftar aplikasi yang diizinkan melalui `tb_portal_app_access`. Endpoint detail dan simpan memvalidasi identitas akun; akses halaman mengikuti izin `master/user-management`. Tombol kembali mengarah ke User Management.

Label **Master Organisasi** di User Management dan halaman organisasi ditampilkan sebagai **Setting Master Kantor**. Route `master/organisasi` tetap dipertahankan.

## Cara penggunaan

1. Buka **Master > User Management** dengan akun yang memiliki izin `master/user-management`.
2. Temukan user dan klik ikon roda gigi pada kolom **Akses**.
3. Periksa nama user yang tampil, atur sakelar aplikasi, lalu klik **Simpan akses**.
4. Klik **Kembali ke User Management** untuk melanjutkan pengelolaan user.
5. Buka **Setting Master Kantor** untuk mengelola departemen, jobdesk, dan jabatan.

## Validasi

Tidak ada perubahan skema database. Validasi sintaks PHP dan pemeriksaan diff dilakukan; uji browser dengan sesi berizin belum dilakukan.
