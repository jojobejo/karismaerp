# Database Dashboard Admin Panel

Tanggal: 2026-07-13

## Status Perubahan Database

Tidak ada perubahan struktur database untuk penambahan tab panel `ADMIN` pada route `dashboard/`.

## Alasan

Tab `ADMIN` dibangun dari konfigurasi menu di `application/models/M_Dashboard.php`, data session login yang sudah tersedia, dan pembacaan opsional terhadap tabel `tb_menu` bila tabel tersebut ada. Tidak ada tabel, kolom, index, trigger, migration, atau seed baru.

## Data yang Dipakai

| Sumber | Fungsi |
| --- | --- |
| Session `username` | Mengenali akun `admin`. |
| Session `lv` | Bagian dari deteksi admin dashboard. |
| Session `jobdesk` | Bagian dari deteksi admin dashboard. |
| Session `is_admin_dashboard` | Flag admin dashboard dari proses login. |
| Tabel `tb_menu` | Sumber opsional daftar menu dinamis aplikasi, tanpa mengubah schema. |

## Dampak Database

Tidak ada perubahan struktur maupun isi database. Katalog admin hanya membaca `tb_menu` bila tersedia, sehingga tidak membutuhkan migration baru.
