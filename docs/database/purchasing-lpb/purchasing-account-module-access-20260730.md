# Catatan Database Akses Akun Purchasing

Tanggal: 2026-07-30

## Scope

Dokumen ini mencatat hasil pengecekan database untuk permintaan daftar module yang dapat digunakan oleh akun `purchasing`.

Tidak ada perubahan schema, migration, trigger, index, atau seed baru.

## Database Lokal

Koneksi aktif aplikasi:

- Host: `localhost`
- Database: `kiucoid_karismaerp_local`
- Driver: `mysqli`

## Data User

User ditemukan pada tabel `tb_karyawan`.

| Field | Nilai |
| --- | --- |
| `id` | `122` |
| `nik` | `QKIU018072026` |
| `username` | `purchasing` |
| `nm_karyawan` | `PURCHASING` |
| `akses_lv` | `1` |
| `jobdesk` | `ADMIN PO` |
| `departemen` | `purchasing` |
| `status` | `1` |

Tabel `tb_users` tidak memiliki row `username = purchasing` pada pengecekan ini.

## Tabel Menu dan Permission

Tabel berikut tersedia:

- `tb_menu`
- `tb_akses_menu`
- `tb_akses_level`

Namun `tb_menu` pada database lokal berisi `0` row saat pengecekan. Karena itu sidebar dinamis tidak memberikan daftar module tambahan untuk akun `purchasing`.

`tb_akses_level` memiliki row:

| id | nama_level | kode_level | status | is_active |
| --- | --- | --- | --- | --- |
| `1` | `Super Administrator` | `SUPERADMIN` | `1` | `1` |

## Dampak Database ke Akses

1. Akses utama akun `purchasing` saat ini ditentukan oleh row `tb_karyawan`, khususnya `akses_lv=1`, `jobdesk=ADMIN PO`, dan `departemen=purchasing`.
2. Karena `tb_menu` kosong, menu dinamis belum menjadi sumber pembatasan akses.
3. Karena `akses_lv=1`, beberapa fungsi yang membaca level saja dapat memberi akses luas.
4. Guard Retur membaca `departemen` dan `jobdesk`, sehingga `purchasing` lolos untuk module Retur.
5. Guard LPB Manual membaca rule admin PO/Purchasing, sehingga `purchasing` lolos untuk module LPB Manual.

## Rekomendasi Database

Tidak ada migration yang wajib untuk dokumentasi ini.

Jika ingin akses Purchasing lebih terkontrol:

1. Isi `tb_menu` dengan daftar module resmi.
2. Isi `tb_akses_menu` untuk level khusus Purchasing, bukan memakai level `Super Administrator`.
3. Buat level baru seperti `PURCHASING` atau `ADMIN_PO` bila belum ada.
4. Pindahkan akun `purchasing` dari `akses_lv=1` ke level khusus tersebut setelah matrix permission siap.
5. Sinkronkan nama kolom menu dinamis (`url_menu`, `icon_menu`, `is_active`) dengan model sidebar bila menu dinamis akan dipakai penuh.
