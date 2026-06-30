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
