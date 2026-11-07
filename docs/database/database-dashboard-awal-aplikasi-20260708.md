# Database Dashboard Awal Aplikasi

Tanggal: 2026-07-08

## Status Perubahan Database

Tidak ada perubahan struktur database untuk development dashboard awal aplikasi.

## Alasan

Dashboard baru memakai data session login yang sudah tersedia:

| Session | Fungsi |
| --- | --- |
| `logged_in` | Validasi user sudah login pada controller `Dashboard`. |
| `lv` | Memastikan user berada pada level admin (`1`). |
| `jobdesk` | Menentukan tab aktif default dan menjadi dasar rules akses berikutnya. |
| `is_admin_dashboard` | Menandai user admin umum pada konteks dashboard. |
| `nama` / `username` | Ditampilkan pada topbar dashboard. |

## Tabel Terdampak

Tidak ada tabel baru, kolom baru, index baru, migration, atau seed data yang dibutuhkan.

## Rencana Rules Akses

Rules akses per user dan level saat ini disiapkan di `application/models/M_Dashboard.php`, method `apply_access_rules()`. Jika nanti rules dipindah ke database, kandidat tabel dapat dibuat untuk menyimpan modul, menu, role, level, dan relasi permission.

## Risiko Database

Tidak ada risiko migrasi database karena perubahan bersifat routing, controller, dan view.
