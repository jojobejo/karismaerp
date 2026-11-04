# Penataan dokumentasi per modul — 4 November 2026

## Cakupan

Dokumen pada akar `docs/` dan folder lama dipindahkan ke `docs/development/<modul>/`, `docs/database/<modul>/`, atau `docs/penggunaan/<modul>/` sesuai jenis dan modul. Nama berkas dipertahankan. Folder `docs/accounting/`, `docs/sales/`, `docs/konsinyasi/`, dan `docs/application/` yang berisi dokumen modul dirapikan ke kategori tersebut. Berkas bukti serta presentasi mengikuti modul terkait.

Rujukan path di dokumentasi dan pesan aplikasi yang mengarah ke berkas pindahan diperbarui. [Peta dokumentasi](../../README.md) menjadi titik awal pencarian.

## Dampak aplikasi

Perubahan pada source aplikasi hanya berupa path dokumentasi yang tampil pada pesan atau panduan. Route, kontrol akses, query, dan alur bisnis tidak diubah.

## Pemeriksaan

- Pemeriksaan tautan Markdown relatif yang mengarah ke berkas pindahan dilakukan setelah relokasi.
- Pemeriksaan `git diff --check` dan lint PHP untuk dua controller yang pesannya berubah dilakukan.
- Pemeriksaan isi SQL dipisahkan pada [catatan database](../../database/umum/penataan-dokumentasi-dan-migrasi-20261104.md).

Tidak ada perubahan skema database.
