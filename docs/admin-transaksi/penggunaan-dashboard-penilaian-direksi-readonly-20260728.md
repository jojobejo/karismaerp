# Panduan Penggunaan Dashboard Penilaian Direksi Readonly

Tanggal: 2026-07-28

## Akun Login

| Username | Password |
| --- | --- |
| `direktur1` | `direktur89` |
| `direktur2` | `direktur89` |
| `direktur3` | `direktur89` |

## Cara Akses

1. Buka halaman login ERP.
2. Login menggunakan salah satu akun direksi.
3. Setelah login berhasil, sistem otomatis membuka `dashboard_penilaian`.

## Hak Akses Direksi

Akun direksi hanya dapat melihat data:

- ringkasan dashboard;
- grafik issue per lokasi;
- grafik prioritas rating;
- daftar issue;
- detail analisa issue;
- monitoring readonly.

Akun direksi tidak dapat melakukan:

- input laporan baru;
- update status issue;
- upload bukti tambahan;
- tambah/edit/hapus master lokasi;
- tambah/edit/hapus master rating.

Jika mencoba melakukan aksi CRUD lewat URL atau request manual, sistem akan menolak dengan status `403`.
