# Database - ICS Mutasi Barang Modal AJAX

Tanggal: 2026-07-20

## Ringkasan

Tidak ada migrasi database baru pada development ini.

Perubahan hanya memakai tabel dan kolom yang sudah digunakan flow mutasi barang:

- `tb_tmp_mutasi`
- `tb_mutasi`
- `tb_detail_mutasi`
- `tb_stock_hold`
- `tb_saldo_awal`
- `tb_gudang`
- `tb_gudang_wilayah`
- `tb_master_barang_all`
- `tbpo_satuan`
- `tb_log_mutasi`

## Kolom Pendukung

Flow tetap memanggil `M_Ics::ensure_mutasi_barang_schema()` untuk memastikan kolom pendukung lama tersedia:

- `tb_tmp_mutasi.kode_barang`
- `tb_tmp_mutasi.kode_barang_system`
- `tb_tmp_mutasi.no_lot`
- `tb_tmp_mutasi.gudang_asal`
- `tb_detail_mutasi.no_lot`
- `tb_stock_hold.no_lot`

Tidak ada DDL baru di luar mekanisme existing tersebut.

## Master Satuan

Dropdown inline kolom `Satuan` mengambil data dari `tbpo_satuan`:

- `id_satuan`
- `nm_satuan`

Nilai yang tersimpan di `tb_tmp_mutasi.satuan_id` tetap berupa `id_satuan`.

## Kolom No Lot dan Expired Date

Tidak ada DDL baru. Tabel draft mutasi memakai kolom existing yang sudah dijaga oleh `ensure_mutasi_barang_schema()`:

- `tb_tmp_mutasi.no_lot`
- `tb_tmp_mutasi.exp_date`

Kolom tersebut sekarang juga ditampilkan di grid utama list barang mutasi.

## Query Lot

Daftar lot barang pada modal `Data Lot Barang` mengambil data dari `tb_saldo_awal`, dengan filter:

- gudang asal melalui relasi `tb_gudang_wilayah`
- `kode_barang_system` jika tersedia
- fallback `nama_barang`
- pencarian pada `nolot` atau `exp_date`

Hasil dikelompokkan berdasarkan lot dan expired date, lalu hanya menampilkan saldo `qty_gudang > 0`.

Validasi qty membandingkan jumlah draft terhadap saldo database dari `tb_saldo_awal` berdasarkan gudang asal, barang, lot, dan expired date.

Halaman `Data Persediaan` memakai query lot/expired dari `tb_saldo_awal` untuk Select2:

- Select2 `No Lot`: filter gudang asal dan barang.
- Select2 `Expired Date`: filter gudang asal, barang, dan no lot.
- Readonly `Qty Stock`: hasil agregasi `SUM(qty)` untuk kombinasi gudang, barang, no lot, dan expired date.

## Dashboard Faktur Mutasi

View route `ics/mutasi_barang` mengambil data master dari `tb_mutasi` pada level `noreff`.

- Jika 1 faktur mutasi memiliki beberapa barang, data detail tetap berada di `tb_detail_mutasi`.
- Dashboard hanya menampilkan 1 baris master untuk setiap `tb_mutasi.noreff`.
- Query memakai subquery `MAX(id) GROUP BY noreff` sebagai pengaman jika terdapat lebih dari satu header dengan nomor faktur yang sama.
- Join inputer ke `tb_karyawan` memakai subquery unik per `nik` karena data master karyawan dapat memiliki beberapa baris dengan NIK yang sama.
