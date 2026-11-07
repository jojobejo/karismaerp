# Development Note - Scan Dua Database KarismaERP - 2026-07-22

## Tujuan

Melakukan full scanning dua dump database KarismaERP untuk mencari perbedaan struktur dan tipe data:

- `C:/Users/bram/Downloads/kiucoid_karismaerp_local_bram.sql`
- `C:/Users/bram/Documents/OMessenger/Received files/u471548307_karismaerp_yoga.sql`

## Cara Scan

Scan dilakukan lokal dari file dump SQL, tanpa import ke MySQL dan tanpa mengubah database aktif. Parser membaca struktur `CREATE TABLE` dengan pencarian tanda kurung seimbang agar tipe seperti `int(11)`, `decimal(18,4)`, dan `enum(...)` tidak memotong definisi kolom.

Area yang dibandingkan:

- Base table dan view real.
- Nama kolom, tipe data, nullability, default, enum, comment, dan collation kolom.
- Table option seperti engine, charset, collation, dan comment.
- Primary key, unique key, index biasa.
- Auto increment attribute dari bagian `ALTER TABLE ... MODIFY`.
- Foreign key dari bagian constraints.

## Hasil Utama

| Metrik | Jumlah |
| --- | --- |
| Base table local_bram | 257 |
| Base table Yoga | 251 |
| Tabel common | 248 |
| Tabel hanya local_bram | 9 |
| Tabel hanya Yoga | 3 |
| View hanya Yoga | 8 |
| Kolom hanya local_bram | 73 |
| Kolom hanya Yoga | 38 |
| Perbedaan tipe/default/enum berdampak | 3 |
| Perbedaan kolom hanya collation | 6 |
| Perbedaan table option/collation | 247 |
| Perbedaan index/PK/unique | 87 |
| Perbedaan auto increment | 64 |
| Perbedaan foreign key | 28 |

## Output Dokumentasi

- Detail database: `docs/database/schema-diff-kiucoid-local-vs-u471548307-20260722.md`
- CSV audit lengkap: `docs/database/schema-diff-kiucoid-local-vs-u471548307-20260722.csv`

## Catatan Implementasi

Belum dibuat migration SQL otomatis karena arah sinkronisasi harus diputuskan dulu: apakah `local_bram` menjadi acuan untuk update Yoga, atau Yoga menjadi acuan untuk update local. Beberapa perbedaan bersifat destruktif jika langsung di-`DROP` atau dipersempit, terutama enum status retur dan panjang `metode_pembayaran`.
