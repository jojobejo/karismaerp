# Catatan Database UI Akses dan Master Portal

## Status perubahan

Tidak ada perubahan skema database atau data untuk penyegaran UI route `admin/portal-app-access`, `admin/portal-sso-clients`, `master/user-management`, dan `master/menu`.

Implementasi menggunakan tabel serta endpoint existing. Tabel SSO yang diperlukan tetap mengikuti migration terpisah `portal_sso_20261022.sql`; pekerjaan UI ini tidak menjalankan migration tersebut.

## Halaman dokumentasi API portal

Halaman `admin/api-documentation` hanya menampilkan dokumentasi dan checklist penggunaan di sisi browser. Perubahan ini tidak menambah, mengubah, atau menghapus tabel, kolom, indeks, maupun data. Skema SSO yang sudah didokumentasikan pada `portal_sso_20261022.sql` tetap terpisah.
