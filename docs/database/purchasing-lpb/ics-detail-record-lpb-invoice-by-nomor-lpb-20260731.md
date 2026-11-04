# Database - ICS Detail Record LPB Invoice by Nomor LPB

Tanggal: 2026-07-31

## Status Database

Tidak ada perubahan struktur database.

## Tabel Terkait

- `tb_lpb`
- `tb_lpb_detail`

## Kontrak Data

- Card `List Invoice LPB per Nomor LPB` mengambil header dari `tb_lpb` dengan syarat:
  - `kd_po` sesuai parameter route.
  - `nomor_lpb` tidak `NULL` dan tidak kosong.
- Jika endpoint menerima `id_lpb`, aplikasi menyelesaikan `id_lpb` tersebut ke `tb_lpb.nomor_lpb`, lalu query dibatasi ke `nomor_lpb` yang sama.
- Jika `id_lpb` valid tetapi `nomor_lpb` belum tersedia, query dibatasi ke `tb_lpb.id_lpb` tersebut.
- Field invoice tetap memakai kolom existing:
  - `tb_lpb.no_invoice`
  - `tb_lpb.tanggal_invoice`
  - `tb_lpb.kode_faktur_pajak`
  - `tb_lpb.tanggal_faktur_pajak`
- Total qty tetap dihitung dari agregasi `tb_lpb_detail.qty_diterima`.

## Migrasi

Tidak diperlukan SQL migration.

## Index / Performa

- Tidak ada index baru yang ditambahkan.
- Filter tambahan memakai kolom existing `tb_lpb.id_lpb`, `tb_lpb.kd_po`, dan `tb_lpb.nomor_lpb`.

## Dampak Data

- Data LPB lama yang sudah memiliki `nomor_lpb` otomatis tampil di card invoice.
- Data LPB yang belum memiliki `nomor_lpb` tidak ditampilkan pada card invoice, tetapi tidak dihapus dan tetap bisa dipakai oleh flow detail existing.
- Scope dari klik nomor LPB tidak mengubah data, hanya membatasi hasil baca.
