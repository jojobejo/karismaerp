# Dokumentasi Database Stockopname Barang Pending

Tanggal development: 2026-06-30

## File SQL

Script database tersedia di:

`database/sql/stockopname_barang_pending.sql`

## Tabel Utama

Tabel modul:

`stockopname_pending`

Tabel tujuan sinkronisasi:

`stockopname_master_item`

## Struktur `stockopname_pending`

| Kolom | Tipe | Fungsi |
| --- | --- | --- |
| `id` | `int(11)` | Primary key |
| `kd_do` | `varchar(100)` | Kode faktur/DO yang diinput di modul Barang Pending |
| `kode_barang` | `varchar(50)` | Kode barang pending |
| `nama_barang` | `text` | Nama barang; dipakai sebagai kunci hitungan bersama `expired_date` |
| `expired_date` | `date` | Expired date; dipakai sebagai kunci hitungan bersama `nama_barang` |
| `no_lot` | `varchar(100)` | Lot barang; disimpan tetapi tidak masuk kunci hitungan |
| `qty` | `int(12)` | Qty pending yang ditambahkan ke master |
| `qty_pcs` | `int(12)` | Qty pcs pending yang ditambahkan ke master |
| `qty_box` | `int(12)` | Qty box pending yang ditambahkan ke master |
| `created_by` | `varchar(100)` | User pembuat data |
| `updated_by` | `varchar(100)` | User terakhir update data |
| `created_at` | `datetime` | Waktu data dibuat |
| `updated_at` | `datetime` | Waktu data terakhir diubah |

## Kolom Tambahan `stockopname_master_item`

| Kolom | Tipe | Fungsi |
| --- | --- | --- |
| `pending_qty` | `int(12)` | Total qty pending yang sedang aktif pada baris target master |
| `pending_qty_pcs` | `int(12)` | Total qty pcs pending yang sedang aktif pada baris target master |
| `pending_qty_box` | `int(12)` | Total qty box pending yang sedang aktif pada baris target master |

Kolom ini bukan input manual. Kolom ini dipakai sebagai penanda agar sistem bisa menghitung ulang base qty tanpa menambah pending dua kali saat data diedit.

## Index

| Tabel | Index | Kolom | Tujuan |
| --- | --- | --- | --- |
| `stockopname_pending` | `idx_pending_kd_do` | `kd_do` | Mempercepat pencarian kode faktur/DO |
| `stockopname_pending` | `idx_pending_barang_expired` | `nama_barang(191), expired_date` | Mempercepat agregasi pending ke master |
| `stockopname_pending` | `idx_pending_kode_barang` | `kode_barang` | Mempercepat pencarian kode barang |

## Mapping Hitungan

Agregasi pending:

```sql
SELECT
  nama_barang,
  expired_date,
  SUM(qty) AS pending_qty,
  SUM(qty_pcs) AS pending_qty_pcs,
  SUM(qty_box) AS pending_qty_box
FROM stockopname_pending
GROUP BY nama_barang, expired_date;
```

Target sinkronisasi di `stockopname_master_item`:

```sql
WHERE nama_barang = stockopname_pending.nama_barang
  AND expired_date = stockopname_pending.expired_date
```

`no_lot` tidak dipakai di `WHERE` sinkronisasi.

## Kompatibilitas Legacy

Dump lama sudah memiliki tabel `stockopname_pending` dengan struktur:

- `kd_do`
- `kd_faktur`
- `tgl_transaksi`
- `nama_barang`
- `qty`
- `no_lot`
- `exp_date`
- `input_at`

Script migrasi tidak menghapus kolom legacy. Modul baru menambahkan kolom yang dibutuhkan dan mencoba mengisi `expired_date` dari `exp_date` jika kolom legacy tersebut tersedia.

## Update 2026-07-03

Kolom `kd_do` dipakai sebagai penyimpanan kode faktur dari form `admin/stockopname/barang-pending`. Pada database legacy yang sudah memiliki `kd_do`, migration tidak membuat ulang kolom tersebut. Pada database yang belum memiliki `kd_do`, script `database/sql/stockopname_barang_pending.sql` akan menambah:

```sql
ALTER TABLE `stockopname_pending`
  ADD `kd_do` varchar(100) NOT NULL DEFAULT '' AFTER `id`;
```

Sumber pilihan barang dan expired date berasal dari `stockopname_master_item`. Nilai `qty` yang tersimpan di `stockopname_pending` dihitung aplikasi dari `qty_box`, `qty_pcs`, dan dimensi master:

```text
qty = (qty_box * dimensi) + qty_pcs
```

## Update 2026-07-03: Pencarian Select2

Pencarian Select2 pada form Barang Pending tidak menambah tabel atau kolom baru. Data opsi tetap membaca dari tabel `stockopname_master_item` melalui model aplikasi, lalu user dapat mencari berdasarkan `kode_barang` atau `nama_barang`.

## Update 2026-07-03: Kompatibilitas Tabel Legacy

Pada database legacy, tabel `stockopname_pending` dapat ditemukan dengan kolom `id` yang belum `PRIMARY KEY AUTO_INCREMENT`. Kondisi ini membuat insert aplikasi gagal karena `insert_id()` tidak menghasilkan ID valid. Migration dan guard model sekarang memastikan:

- `stockopname_pending.id` menjadi primary key.
- `stockopname_pending.id` menjadi `AUTO_INCREMENT`.
- Index `idx_pending_kd_do` memakai prefix `kd_do(100)` agar kompatibel ketika `kd_do` masih bertipe `TEXT`.

Model juga mengisi kolom legacy `kd_faktur`, `tgl_transaksi`, `exp_date`, dan `input_at` jika kolom tersebut masih ada di tabel, sehingga insert tetap aman pada database lama yang menjalankan mode strict.

## Update 2026-07-03: Aksi Monitoring Pending Opname

Tidak ada perubahan struktur database untuk penambahan tombol `Update` dan `Delete` pada `admin/stockopname/monitoring/pending-opname`.

Operasi tetap memakai tabel dan endpoint yang sudah ada:

- `Update` memakai data `stockopname_pending.id` untuk membuka form update pada route `admin/stockopname/barang-pending`.
- `Delete` memakai endpoint `admin/stockopname/barang-pending/delete`, lalu model menjalankan ulang sinkronisasi pending ke `stockopname_master_item`.

Dengan keputusan ini, kontrak database tetap stabil: tidak ada tabel, kolom, index, atau migration baru.

## Update 2026-07-03: Perbaikan Urutan Migration Legacy

Jika tabel legacy `stockopname_pending` belum memiliki kolom `kode_barang`, script lama bisa berhenti dengan error:

```text
#1054 - Unknown column 'kode_barang' in 'ORDER BY'
```

Perbaikan migration:

- Blok penambahan kolom `stockopname_pending.kode_barang` dipindahkan sebelum proses normalisasi `id`.
- Proses normalisasi `id` tetap memakai `ORDER BY id, kode_barang, nama_barang`, tetapi kolom `kode_barang` kini sudah dijamin tersedia terlebih dahulu.
- Tidak ada perubahan data bisnis; perubahan ini hanya memperbaiki urutan migration agar aman untuk database legacy.
