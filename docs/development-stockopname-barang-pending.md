# Development Modul Stockopname Barang Pending

Tanggal development: 2026-06-30

## Tujuan

Modul Barang Pending dibuat untuk memisahkan barang yang secara fisik masih ada di gudang, tetapi secara sistem transaksi sudah terinput keluar/terproses. Data pending tetap dicatat sendiri, lalu jumlahnya disinkronkan ke `stockopname_master_item` agar proses stockopname membaca stok buku yang sudah ditambah pending.

## Route Modul

| Route | Fungsi |
| --- | --- |
| `admin/stockopname/barang-pending` | Halaman utama modul Barang Pending |
| `admin/stockopname/barang-pending/list` | AJAX list data pending |
| `admin/stockopname/barang-pending/detail` | AJAX detail satu data pending |
| `admin/stockopname/barang-pending/save` | AJAX create/update data pending |
| `admin/stockopname/barang-pending/delete` | AJAX delete data pending |
| `admin/stockopname/barang-pending/export-csv` | Export CSV data pending |

## File Yang Ditambahkan atau Diubah

| File | Perubahan |
| --- | --- |
| `application/config/routes.php` | Menambahkan route modul Barang Pending |
| `application/controllers/admin/C_Stockopname.php` | Menambahkan halaman, CRUD AJAX, dan export CSV |
| `application/models/admin/M_Stockopname.php` | Menambahkan struktur pending, CRUD pending, summary, list, dan sinkronisasi ke master opname |
| `application/views/content/admin/stockopname_barang_pending.php` | View input, list, edit, delete, summary, dan export CSV |
| `database/sql/stockopname_barang_pending.sql` | Script database untuk tabel/kolom modul |

## Data Utama

Field operasional modul:

- `kode_barang`
- `expired_date`
- `no_lot`
- `nama_barang`
- `qty`
- `qty_pcs`
- `qty_box`

`no_lot` tetap disimpan sebagai informasi fisik barang, tetapi tidak dipakai sebagai kunci hitungan sinkronisasi.

## Aturan Sinkronisasi

Kunci hitungan pending adalah:

```text
nama_barang + expired_date
```

Setiap create, update, atau delete data pending akan menghitung ulang total pending untuk kombinasi tersebut:

- total `qty`
- total `qty_pcs`
- total `qty_box`

Total tersebut ditambahkan ke satu baris target `stockopname_master_item` dengan `nama_barang` dan `expired_date` yang sama. Jika ada lebih dari satu baris master dengan kombinasi sama, sistem memakai baris dengan `id` paling kecil sebagai target agar jumlah pending tidak dobel ke banyak lot.

## Proteksi Anti Dobel Hitung

Kolom penanda di `stockopname_master_item` dipakai untuk menyimpan porsi pending yang sedang aktif:

- `pending_qty`
- `pending_qty_pcs`
- `pending_qty_box`

Saat sinkronisasi, sistem menghitung base qty master dari:

```text
qty dasar = qty saat ini - pending_qty sebelumnya
```

Lalu sistem menulis ulang:

```text
qty baru = qty dasar + total pending terbaru
```

Dengan pola ini, edit atau simpan ulang data pending tidak akan menambah qty berkali-kali.

## Catatan Operasional

- Jika data pending tersimpan tetapi belum ada pasangan `stockopname_master_item` dengan `nama_barang + expired_date` yang sama, data tetap tersimpan dan status di UI menjadi `Belum ada master`.
- Jika pasangan master sudah ada, status di UI menjadi `Masuk master`.
- Export CSV mengambil data dari tabel pending, bukan dari master opname.
