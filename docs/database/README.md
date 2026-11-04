# Dokumentasi database

Setiap subfolder berisi catatan database dan SQL migrasi untuk modul terkait. Mulai dari [peta modul](../README.md), lalu baca dokumen fitur sebelum menerapkan SQL. Urutan migrasi harus mengikuti prasyarat pada dokumen fitur; urutan nama berkas bukan instruksi eksekusi.

Skrip SQL lain berada di [database/sql/](../../database/sql/). Dump acuan `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql` tetap di lokasi semula dan hanya menjadi acuan struktur. Pemindahan dokumentasi ini tidak mengubah skema database.

## Rujukan SQL lama yang belum tersedia

Beberapa dokumen lama merujuk skrip `database/sql/stockopname_barang_pending.sql`, `database/sql/stockopname_manual_tables.sql`, `database/sql/stockopname_detail_input_enhancement.sql`, dan `database/sql/tb_users_auth_penilaian_lingkungan.sql`. Berkas tersebut tidak ditemukan di checkout ini pada saat penataan. Rujukannya dipertahankan sebagai catatan historis; jangan menganggap skrip tersebut siap dijalankan.
