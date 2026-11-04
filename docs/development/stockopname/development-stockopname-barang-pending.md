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
| `admin/stockopname/monitoring/pending-opname` | View ringkas detail pending opname dari halaman monitoring |

## File Yang Ditambahkan atau Diubah

| File | Perubahan |
| --- | --- |
| `application/config/routes.php` | Menambahkan route modul Barang Pending |
| `application/controllers/admin/C_Stockopname.php` | Menambahkan halaman, CRUD AJAX, dan export CSV |
| `application/models/admin/M_Stockopname.php` | Menambahkan struktur pending, CRUD pending, summary, list, dan sinkronisasi ke master opname |
| `application/views/content/admin/stockopname_barang_pending.php` | View input, list, edit, delete, summary, dan export CSV |
| `application/views/content/admin/stockopname_pending_opname_detail.php` | View ringkas detail pending opname untuk drilldown dari monitoring |
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
- Halaman `admin/stockopname/monitoring` menampilkan tombol/kartu ringkas `Pending Opname` yang mengarah ke detail pending opname.

## Update 2026-07-03: Input Berbasis Master Opname

Perubahan pada route `admin/stockopname/barang-pending`:

- `kode_barang` tidak lagi diketik bebas; user memilih dari data `stockopname_master_item`.
- `nama_barang` otomatis mengikuti `kode_barang` dan `expired_date` yang dipilih dari master opname.
- `expired_date` berbentuk select option dan isinya disaring berdasarkan `kode_barang` yang dipilih.
- `no_lot` disembunyikan dari form dan list, tetapi tetap disimpan internal dari data master agar kompatibel dengan struktur lama.
- `qty` dibuat readonly dan dihitung otomatis dari:

```text
qty = (qty_box * dimensi) + qty_pcs
```

- `dimensi` ditampilkan readonly sebagai referensi hitungan. Nilainya diambil dari master opname atau fallback master barang yang sudah dipakai modul stockopname.
- `kode faktur` ditambahkan sebagai input wajib dan disimpan ke kolom `stockopname_pending.kd_do`.

Validasi server tetap menghitung ulang `qty` dari master opname. Artinya nilai readonly di browser tidak menjadi satu-satunya sumber kebenaran.

## Update 2026-07-03: Select2 Pencarian Master Opname

Field `kode_barang` pada route `admin/stockopname/barang-pending` menggunakan Select2 agar user bisa mencari data master opname dari `stockopname_master_item` dengan:

- kode barang
- nama barang

Opsi yang tampil tetap berasal dari daftar master opname yang dikirim controller melalui `pending_master_options()`. Setelah kode barang dipilih, sistem tetap memfilter `expired_date`, mengisi `nama_barang`, dan menghitung `qty` berdasarkan dimensi master seperti aturan sebelumnya.

## Update 2026-07-03: Perbaikan Gagal Simpan Legacy Pending

Root cause gagal simpan pada database lokal adalah struktur legacy `stockopname_pending`:

- `id` belum `PRIMARY KEY AUTO_INCREMENT`, sehingga insert tidak menghasilkan ID valid untuk aplikasi.
- Kolom legacy `kd_faktur`, `tgl_transaksi`, dan `exp_date` masih wajib diisi pada sebagian database.

Perbaikan aplikasi:

- `ensure_pending_tables()` sekarang memperbaiki `id` legacy menjadi primary key auto increment.
- `save_pending()` mengisi kolom legacy yang masih ada, tanpa mengubah kontrak field baru.
- Database lokal sudah diverifikasi dengan insert dalam transaksi rollback dan menghasilkan `LAST_INSERT_ID()` valid.

## Update 2026-07-03: Aksi Monitoring Pending Opname

Perubahan pada route `admin/stockopname/barang-pending`:

- Tombol kembali diarahkan ke dashboard pending dengan label `Dashboard Pending`.
- Target tombol diarahkan ke `admin/stockopname/monitoring/pending-opname`.
- Halaman mendukung query `edit_id` agar data pending tertentu bisa langsung dibuka pada mode update.

Perubahan pada route `admin/stockopname/monitoring/pending-opname`:

- Tabel `Data Barang Pending Opname` mendapat kolom `Aksi`.
- Tombol `Update` mengarah ke `admin/stockopname/barang-pending?edit_id={id}` dan membuka form kelola pending pada baris yang dipilih.
- Tombol `Delete` menjalankan endpoint existing `admin/stockopname/barang-pending/delete` lewat AJAX, memakai konfirmasi, lalu menghapus baris dari tabel bila proses berhasil.

Tata cara penggunaan:

1. Buka `admin/stockopname/monitoring/pending-opname`.
2. Klik `Update` pada baris pending yang ingin diubah; user akan diarahkan ke halaman kelola pending dengan data baris tersebut sudah masuk ke form.
3. Ubah qty atau data yang diperlukan, lalu klik `Update`.
4. Untuk menghapus data pending langsung dari monitoring, klik `Delete`, konfirmasi hapus, lalu sistem akan menyinkronkan ulang qty pending ke master melalui endpoint yang sama dengan modul kelola pending.
