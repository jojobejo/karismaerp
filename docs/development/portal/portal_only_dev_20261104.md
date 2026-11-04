# Portal pada database dev setelah pembersihan

Konfigurasi default aplikasi memakai `kiucoid_karismaerp_dev` tanpa prefix. Database tersebut kini hanya menyimpan tabel yang dibutuhkan untuk login, manajemen admin, direktori aplikasi, akses aplikasi, katalog modul, dan SSO. Rincian tabel, backup, dan skrip penghapusan ada di [catatan database](../../database/portal/portal_only_prune_dev_20261104.md).

Tidak ada source code, route, atau aturan izin yang diubah. Login admin serta halaman dan endpoint baca utama Portal telah diuji melalui HTTPS lokal. Akun selain `admin` sudah dihapus dari `tb_users`; `tb_karyawan` kosong. Administrator dapat membuat user baru melalui User Management. Kartu aplikasi yang memerlukan SSO tetap menunggu pendaftaran klien, URL peluncuran, dan izin per akun; tabelnya belum berisi konfigurasi tersebut.

Modul ERP lama masih memiliki route dan source code di proyek, tetapi tabel bisnisnya sudah tidak tersedia pada database dev. Pengujian hanya mencakup modul Portal yang disebut di atas. Jangan mengarahkan modul ERP lama ke database dev ini tanpa memulihkan skema dan datanya.
