# Pembersihan Akun dan Master Organisasi

## Lingkungan

- Database lokal: `kiucoid_karismaerp`
- Tanggal pelaksanaan: 21 Oktober 2026
- Jenis perubahan: penghapusan data, tanpa perubahan skema

## Backup

Dump penuh sebelum perubahan:

`database/backups/kiucoid_karismaerp_before_account_cleanup_20261021_101437.sql`

SHA-256: `77B4E88645A6D58AEE6FEC8A641A255CB98D7486FBFCAFC5134F1C0D8C61FCE0`

Pemulihan penuh dapat dilakukan dengan mengimpor dump tersebut ke database lokal setelah memastikan target database benar.

## Data yang dihapus

- Seluruh baris akun di `tb_users` dan `tb_user`.
- Seluruh baris di `tb_karyawan` selain akun admin yang dipertahankan.
- Nilai departemen dan jobdesk pada akun admin yang dipertahankan dikosongkan, termasuk ID relasinya.
- Seluruh data master di `tb_departemen` dan `tb_jobdesk`.

Akun yang dipertahankan adalah `tb_karyawan.id=46`, username `admin`, akses level `1`, status aktif. ID tersebut merupakan baris `admin` pertama yang dipilih jalur autentikasi saat ditemukan nama pengguna duplikat.

## Hasil verifikasi

| Tabel | Baris tersisa |
|---|---:|
| `tb_users` | 0 |
| `tb_user` | 0 |
| `tb_karyawan` | 1 |
| `tb_departemen` | 0 |
| `tb_jobdesk` | 0 |
| `tb_user_level_mapping` | 7 |

Skema lokal tidak memiliki tabel `tb_jabatan` atau tabel khusus subdepartemen. Data bagian dan jabatan di `tb_users` terhapus bersama baris akunnya. `tb_user_level_mapping` dipertahankan karena berisi pemetaan jabatan ke level aturan aplikasi, bukan daftar akun atau master organisasi.
