# Scanning Database - Modul Kartu Stok Berbasis Harga

Tanggal scan: 2026-07-23
Database lokal: `kiucoid_karismaerp_local`
Status pekerjaan: analisa struktur dan data read-only. Tidak ada migration yang dijalankan.

## Tabel Existing yang Relevan

### Ledger Stok Existing

`tberp_stock_ledger`

Kolom utama:

- `id`
- `kd_barang`
- `gudang_id`
- `no_lot`
- `expired_date`
- `qty`
- `tipe`
- `ref_no`
- `ref_type`
- `created_at`

Enum `tipe`:

- `SALDO_AWAL`
- `IN`
- `RESERVE`
- `RELEASE`
- `OUT`
- `RBELI`
- `RJUAL`
- `MUTASI`
- `ADJIN`
- `ADJOUT`

Kekurangan untuk kartu stok harga:

- tidak ada `harga_beli`;
- tidak ada `harga_jual`;
- tidak ada `hpp_pembelian`;
- tidak ada `hpp_penjualan`;
- tidak ada `dpp`;
- tidak ada `ppn`;
- tidak ada `nilai_masuk`;
- tidak ada `nilai_keluar`;
- tidak ada `saldo_nilai`;
- tidak ada relasi langsung ke detail LPB/faktur/detail movement selain `ref_no/ref_type`.

`tberp_stock_batch`

Kolom utama:

- `id`
- `kd_barang`
- `gudang_id`
- `no_lot`
- `expired_date`
- `qty_on_hand`
- `qty_reserved`
- `created_at`
- `update_at`

Fungsi yang cocok:

- snapshot stok operasional;
- sumber cepat untuk available stock;
- bukan sumber utama nilai persediaan karena tidak menyimpan harga layer.

### Pembelian/LPB

`tb_lpb`

Kolom terkait:

- `id_lpb`
- `kd_po`
- `no_po`
- `no_invoice`
- `jenis_lpb`
- `nomor_lpb`
- `status_lpb`
- `gudang_id`
- `input_at`

`tb_lpb_detail`

Kolom terkait:

- `id_detail_lpb`
- `id_lpb`
- `kd_barang`
- `qty_diterima`
- `no_lot`
- `expired_date`
- `harga_satuan_sebelumnya`
- `total_harga_sebelumnya`
- `harga_satuan`
- `total_harga`
- `harga_update_by`
- `harga_update_at`
- `harga_verified_by`
- `harga_verified_at`

`tbpo_detail_po`

Kolom terkait:

- `kd_po`
- `no_po`
- `kd_barang`
- `qty`
- `qty_kecil`
- `hrg_satuan`
- `harga_satuan_exclude`
- `harga_satuan_kecil`
- `harga_satuan_kecil_exclude`
- `hrg_total`
- `hrg_total_diskon`
- `harga_satuan_kecil_setelah_diskon`
- `total_harga_setelah_diskon`
- `keterangan_harga_ppn`

Catatan:

- DPP LPB existing dihitung dari `qty_diterima * harga_satuan_kecil_exclude` dengan fallback ke harga PO/detail LPB.
- `dpp_nilai_lain` dan `ppn` pada LPB detail saat ini kalkulasi query, bukan kolom permanen.

### Penjualan

`tbso_faktur_detail`

Kolom terkait:

- `id`
- `id_faktur`
- `no_faktur`
- `id_so`
- `id_so_detail`
- `kd_barang`
- `no_lot`
- `expired_date`
- `qty`
- `hrg_satuan`
- `hrg_pokok`
- `disc`
- `pajak`
- `subtotal_before_disc`
- `subtotal_after_disc`
- `total_harga`
- `gudang_id`
- `create_at`

Catatan:

- `hrg_pokok` sudah tersedia pada faktur detail.
- Di data lokal, semua `hrg_pokok` bernilai `20000.00`, sama seperti `tb_master_barang_all.hpp`; ini perlu dianggap fallback master, bukan HPP layer pembelian final.

### Master Barang

`tbpo_barang`

Kolom terkait:

- `kelompok_dagang`
- `hpp_average`
- `hpp_fifo`
- `hpp_lifo`
- `kode_akun_harga_pokok`

`tb_master_barang_all`

Kolom terkait:

- `kd_barang`
- `hpp`
- dimensi/isi/qty minimum lain yang dipakai stock dan SO

Catatan:

- `tbpo_barang` cocok menjadi sumber konfigurasi metode HPP dan akun.
- `tb_master_barang_all.hpp` cocok sebagai fallback, bukan sumber final HPP penjualan.

## Hasil Agregasi Read-only

Jumlah baris:

| Tabel | Jumlah |
| --- | ---: |
| `tberp_stock_ledger` | 10.315 |
| `tberp_stock_batch` | 10.078 |
| `tb_lpb` | 7 |
| `tb_lpb_detail` | 8 |
| `tbso_faktur_detail` | 31 |
| `tbpo_detail_po` | 5 |

Distribusi `tberp_stock_ledger`:

| Tipe | Ref Type | Baris | Total Qty |
| --- | --- | ---: | ---: |
| kosong | `SALES_ORDER_CANCEL` | 1 | 50.000 |
| `SALDO_AWAL` | `INIT` | 10.063 | 334.398,795 |
| `IN` | `LPB_PRICE_ADJUSTMENT_IN` | 1 | 250.000 |
| `IN` | `PO_RECEIVED` | 14 | 502.850 |
| `IN` | `REPOST_FAKTUR` | 11 | 2.145 |
| `RESERVE` | `REPOST_FAKTUR_RESERVE` | 11 | 2.145 |
| `RESERVE` | `SALES_ORDER` | 108 | 19.222 |
| `RELEASE` | `SALES_ORDER_CANCEL` | 1 | 50 |
| `OUT` | `FAKTUR PENJUALAN` | 103 | 18.002 |
| `RBELI` | `LPB_PRICE_ADJUSTMENT_PRPP` | 1 | 250.000 |
| `RBELI` | `RETUR_PEMBELIAN` | 1 | 50 |

Data quality:

- Ada `tipe` kosong pada ledger cancel SO. Wajib dibereskan/mapping sebelum laporan final.
- `LPB_PRICE_ADJUSTMENT_IN` dan `LPB_PRICE_ADJUSTMENT_PRPP` memakai qty besar. Perlu dipastikan apakah itu qty fisik atau nilai yang tertulis di field qty.
- Tidak ada kolom financial pada `tberp_stock_ledger`, sehingga nilai movement tidak bisa diaudit dari ledger existing saja.

## Database yang Perlu Ditambahkan

### 1. Tabel Ledger Nilai Stok

Rekomendasi nama: `tberp_stock_value_ledger`

Tujuan:

- menyimpan snapshot nilai uang per movement stok;
- menghindari perhitungan historis berubah ketika PO/LPB/faktur diubah;
- menjadi sumber laporan kartu stok nilai.

Kolom yang disarankan:

- `id`
- `stock_ledger_id`
- `movement_date`
- `kd_barang`
- `gudang_id`
- `no_lot`
- `expired_date`
- `tipe`
- `ref_type`
- `ref_no`
- `source_table`
- `source_id`
- `source_detail_id`
- `qty_in`
- `qty_out`
- `harga_beli`
- `harga_jual`
- `dpp_pembelian`
- `ppn_pembelian`
- `hpp_pembelian_unit`
- `hpp_penjualan_unit`
- `nilai_masuk`
- `nilai_keluar`
- `saldo_qty`
- `saldo_nilai`
- `average_cost`
- `cost_method`
- `cost_status`
- `created_at`
- `created_by`

`cost_status` yang disarankan:

- `FINAL`
- `DRAFT`
- `UNVERIFIED_PRICE`
- `FALLBACK_MASTER_HPP`
- `MISSING_COST`
- `ADJUSTED`
- `MIGRATED`

Index yang disarankan:

- `idx_value_barang_tanggal (kd_barang, movement_date)`
- `idx_value_barang_gudang_lot (kd_barang, gudang_id, no_lot, expired_date)`
- `idx_value_ref (ref_type, ref_no)`
- `idx_value_source (source_table, source_id, source_detail_id)`
- unique opsional `uk_stock_ledger_id (stock_ledger_id)` jika satu stock ledger hanya boleh punya satu value ledger.

### 2. Tabel Layer Cost

Rekomendasi nama: `tberp_stock_cost_layer`

Tujuan:

- menyimpan layer pembelian untuk FIFO/Average;
- memastikan HPP penjualan mengambil cost dari stok yang benar-benar keluar;
- mengontrol saldo qty dan saldo nilai per layer.

Kolom yang disarankan:

- `id`
- `kd_barang`
- `gudang_id`
- `no_lot`
- `expired_date`
- `source_type`
- `source_no`
- `source_detail_id`
- `tanggal_masuk`
- `qty_masuk`
- `qty_sisa`
- `harga_pokok_unit`
- `dpp_total`
- `ppn_total`
- `nilai_masuk`
- `nilai_sisa`
- `cost_method`
- `status_layer`
- `created_at`
- `updated_at`

Index yang disarankan:

- `idx_layer_lookup (kd_barang, gudang_id, no_lot, expired_date, status_layer)`
- `idx_layer_fifo (kd_barang, gudang_id, tanggal_masuk, id)`
- `idx_layer_source (source_type, source_no, source_detail_id)`

### 3. Tabel Konsumsi Layer Penjualan

Rekomendasi nama: `tberp_stock_cost_consumption`

Tujuan:

- mencatat faktur penjualan mengambil qty dari layer cost mana;
- memungkinkan audit HPP penjualan dan retur penjualan yang akurat.

Kolom yang disarankan:

- `id`
- `faktur_detail_id`
- `stock_ledger_id`
- `cost_layer_id`
- `kd_barang`
- `qty`
- `hpp_unit`
- `nilai_hpp`
- `created_at`

Index yang disarankan:

- `idx_consumption_faktur_detail (faktur_detail_id)`
- `idx_consumption_layer (cost_layer_id)`
- `idx_consumption_stock_ledger (stock_ledger_id)`

### 4. Tabel Closing Persediaan

Rekomendasi nama: `tberp_stock_value_closing`

Tujuan:

- menyimpan saldo akhir periode agar laporan tidak menghitung ulang dari awal selamanya;
- menjadi kontrol closing accounting.

Kolom yang disarankan:

- `id`
- `periode`
- `kd_barang`
- `gudang_id`
- `no_lot`
- `expired_date`
- `saldo_qty`
- `saldo_nilai`
- `average_cost`
- `status_closing`
- `closed_at`
- `closed_by`

## Database yang Perlu Dikurangi/Dihindari

- Jangan menambah kolom harga langsung ke `tberp_stock_batch` sebagai sumber utama. Batch adalah snapshot qty, bukan histori.
- Jangan menjadikan `tberp_stock_ledger.qty` sebagai tempat menyimpan nilai rupiah. Field itu harus tetap qty.
- Jangan membuat view kartu stok nilai yang hanya join live ke LPB/PO/faktur tanpa snapshot.
- Jangan menghapus tabel lama seperti `tb_saldo_awal`, `tb_dailystock`, atau `tb_qty_lot` sebelum semua flow lama terbukti tidak memakai tabel tersebut.
- Jangan mengganti enum `tberp_stock_ledger.tipe` tanpa audit data existing karena sudah dipakai banyak model.

## Mapping Source Data

### Stok Masuk dari LPB

Source:

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_detail_po`

Mapping nilai:

- `qty_in` = `tb_lpb_detail.qty_diterima`
- `harga_beli` = prioritas `tb_lpb_detail.harga_satuan`, fallback `tbpo_detail_po.harga_satuan_kecil_exclude`, fallback `tbpo_detail_po.harga_satuan_exclude`
- `dpp_pembelian` = `qty_in * harga_beli`
- `hpp_pembelian_unit` = `dpp_pembelian / qty_in`
- `nilai_masuk` = `dpp_pembelian`

Catatan:

- Untuk BKP, PPN masukan sebaiknya dipisahkan dari cost bila secara accounting dikreditkan.
- LPB yang belum `POST` atau harga belum verified sebaiknya diberi `cost_status = UNVERIFIED_PRICE`.

### Stok Keluar dari Faktur Penjualan

Source:

- `tbso_faktur_penjualan`
- `tbso_faktur_detail`
- `tberp_stock_ledger` dengan `tipe = OUT`

Mapping nilai:

- `qty_out` = `tbso_faktur_detail.qty`
- `harga_jual` = `tbso_faktur_detail.hrg_satuan`
- `dpp_penjualan` = `tbso_faktur_detail.subtotal_after_disc`
- `ppn_penjualan` = `total_harga - subtotal_after_disc`
- `hpp_penjualan_unit` = dari `tberp_stock_cost_consumption`, bukan dari master barang jika layer tersedia
- `nilai_keluar` = `qty_out * hpp_penjualan_unit`

### Retur Pembelian

Source:

- `RETUR_PEMBELIAN`
- `RBELI`
- tabel retur pembelian final

Mapping:

- mengurangi qty dan nilai dari layer asal;
- jika layer asal tidak ditemukan, buat exception `MISSING_LAYER`.

### Retur Penjualan

Source:

- `RJUAL`
- detail retur penjualan

Mapping:

- mengembalikan qty ke layer berdasarkan faktur asal;
- HPP retur harus sama dengan HPP yang dulu dipakai pada faktur.

### Adjustment Harga LPB

Source:

- `M_LpbPriceAdjustment`
- `LPB_PRICE_ADJUSTMENT_*`

Mapping:

- jangan dianggap qty fisik bila adjustment hanya koreksi harga;
- harus menghasilkan financial adjustment terhadap nilai layer/saldo persediaan;
- jika barang sudah terjual, selisih harus dibagi antara persediaan tersisa dan koreksi HPP yang sudah keluar.

## Query Audit yang Perlu Disiapkan Sebelum Development

Read-only audit sebelum coding:

```sql
SELECT tipe, ref_type, COUNT(*) rows_count, SUM(qty) qty_sum
FROM tberp_stock_ledger
GROUP BY tipe, ref_type;
```

```sql
SELECT COUNT(*) total_rows
FROM tberp_stock_ledger
WHERE tipe IS NULL OR tipe = '';
```

```sql
SELECT fd.kd_barang, fd.no_lot, fd.expired_date, fd.hrg_pokok, COUNT(*) total_rows
FROM tbso_faktur_detail fd
GROUP BY fd.kd_barang, fd.no_lot, fd.expired_date, fd.hrg_pokok
ORDER BY total_rows DESC;
```

```sql
SELECT d.kd_barang, d.no_lot, d.expired_date, d.qty_diterima, d.harga_satuan, d.total_harga, h.nomor_lpb, h.status_lpb
FROM tb_lpb_detail d
JOIN tb_lpb h ON h.id_lpb = d.id_lpb
ORDER BY d.id_detail_lpb DESC;
```

## Rekomendasi Migration Saat Development Disetujui

Urutan aman:

1. Buat tabel baru `tberp_stock_value_ledger`.
2. Buat tabel baru `tberp_stock_cost_layer`.
3. Buat tabel baru `tberp_stock_cost_consumption`.
4. Buat tabel baru `tberp_stock_value_closing`.
5. Tambah index pendukung pada tabel baru.
6. Tidak mengubah tabel transaksi existing pada fase awal.
7. Backfill data dengan status `MIGRATED` dan simpan log batch migrasi.
8. Jalankan rekonsiliasi qty ledger vs batch.
9. Baru aktifkan posting nilai untuk transaksi baru.

## Risiko Database

- Data saldo awal belum memiliki nilai cost historis. Perlu nilai awal dari accounting, master HPP, atau dokumen pembelian terakhir.
- Master HPP lokal seragam `20000.00`, sehingga tidak cukup sebagai dasar audit real HPP.
- Adjustment harga saat ini dapat terlihat seperti qty movement besar. Harus dipisah antara adjustment fisik dan adjustment nilai.
- Perbedaan source lama dan baru bisa menyebabkan dobel hitung jika query kartu stok mencampur `tb_saldo_awal` dan `tberp_stock_ledger`.

## Kesimpulan Database

Struktur database existing siap untuk kartu stok kuantitas, tetapi belum cukup untuk kartu stok harga final. Tambahan paling penting bukan kolom tampilan, melainkan tabel financial ledger dan cost layer. Dengan itu, harga jual, harga beli, harga pokok pembelian, harga pokok penjualan, dan DPP dapat menjadi audit trail yang stabil, bukan angka hasil join sementara.
