# Dampak aplikasi dari migrasi Portal dev

Migrasi database tanggal 4 November 2026 pada `kiucoid_karismaerp_dev` menyiapkan tabel akses aplikasi, katalog modul, akun klien, penetapan akun, histori login SSO, dan permintaan reset password. Rincian audit, backup, serta SQL yang dijalankan ada pada [catatan database](../../database/portal/audit-migrasi-kiucoid-karismaerp-dev-20261104.md).

Tidak ada perubahan source aplikasi, route, role, atau izin pada pekerjaan ini. Tabel yang baru dibuat masih kosong. Karena itu pendaftaran klien, akses user, akun klien, dan penetapan akun tetap harus dilakukan melalui alur admin yang sudah ada sebelum SSO dapat dipakai. Pengujian browser dan login per role belum dilakukan.
