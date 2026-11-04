# Penataan migrasi database per modul — 4 November 2026

SQL yang sebelumnya berada langsung di `docs/database/` dan `database/sql/` ditempatkan pada subfolder modul masing-masing. Skrip `db/patches/tambah_user_admin.sql` dipindah ke modul manajemen user; skrip data KPI yang sebelumnya di akar proyek dipindah ke modul HRD. Nama berkas serta urutan historisnya dipertahankan. Skrip KPI menargetkan database KPI, sehingga database tujuan harus diperiksa sebelum dijalankan. Dump acuan master di `db/` dan backup tidak dipindahkan. Peta lokasi baru ada di [indeks dokumentasi](../../README.md).

Pemeriksaan perbandingan file SQL yang dipindah terhadap versi Git menunjukkan **tidak ada perubahan pada baris SQL di luar komentar**. Beberapa komentar path ikut diperbarui agar menunjuk lokasi baru. Tidak ada SQL yang dijalankan pada database dalam penataan ini.

Beberapa dokumen stock opname dan autentikasi HRD masih merujuk skrip SQL lama yang tidak ditemukan di checkout ini; rinciannya dicatat di [README database](../README.md). Keberadaan dokumen tersebut tidak membuktikan migrasi telah diterapkan.

**Tidak ada perubahan skema database.**
