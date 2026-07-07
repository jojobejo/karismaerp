# Database - Modul ICS PO / LPB

Tanggal: 2026-07-07

## Database Yang Digunakan

Konfigurasi aktif CodeIgniter memakai koneksi:

- Host: `localhost`
- User: `root`
- Database lokal: `kiucoid_karismaerp_local`
- Driver: `mysqli`
- Charset: `utf8`
- Collation koneksi: `utf8_general_ci`

Di file konfigurasi juga ada konfigurasi production yang dikomentari:

- Database production: `u471548307_karismaerp`

Struktur yang dicek berasal dari dump:

- `db/kiucoid_karismaerp.sql`

## Tabel Utama Flow Baru

### `tb_pre_po`

Fungsi: staging data PO hasil sync dari KIU_PO/API sebelum diterima menjadi LPB.

Kolom penting:

- `id_pre_po`
- `no_po`
- `kd_po`
- `tgl_transaksi`
- `kd_suplier`
- `kd_barang`
- `satuan`
- `qty`
- `hrg_satuan`
- `harga_total`
- `status`
- `create_at`

Dipakai untuk:

- Daftar PO di `ics/icspo`.
- Detail barang PO.
- Progress qty order.
- Status final PO.
- Adjustment harga Admin PO.

Catatan:

- `status = 1` masih aktif/belum final.
- `status = 2` dipakai setelah LPB final atau invoice/adjustment diproses.
- `tgl_transaksi` bertipe `text`, jadi query filter tanggal memakai konversi string.
- Perubahan 2026-07-07: `qty`, `hrg_satuan`, dan `harga_total` diubah dari integer menjadi `DECIMAL(18,4)` supaya data dari `kiu_po.tb_detail_po` tidak kehilangan desimal dan tidak terpotong saat nilai total melewati batas `INT`.

SQL migrasi:

```sql
ALTER TABLE `tb_pre_po`
    MODIFY `qty` DECIMAL(18,4) NOT NULL DEFAULT 0,
    MODIFY `hrg_satuan` DECIMAL(18,4) NOT NULL DEFAULT 0,
    MODIFY `harga_total` DECIMAL(18,4) NOT NULL DEFAULT 0;
```

File migrasi aplikasi:

```text
db/2026/20260707_alter_tb_pre_po_numeric_precision.sql
```

### `tb_lpb`

Fungsi: header LPB final.

Kolom penting:

- `id_lpb`
- `kd_po`
- `nosj`
- `tgl_sj`
- `no_po`
- `no_invoice`
- `gudang_id`
- `keterangan`
- `input_at`

Dipakai untuk:

- Record LPB per PO.
- Header print LPB.
- Progress penerimaan.
- Update invoice Admin PO.

Relasi:

- `tb_lpb.kd_po` mengacu ke `tb_pre_po.kd_po`.
- `tb_lpb.gudang_id` mengacu ke `tb_gudang.id_gudang`.

### `tb_lpb_detail`

Fungsi: detail barang yang diterima dalam satu LPB.

Kolom penting:

- `id_detail_lpb`
- `id_lpb`
- `kd_barang`
- `qty_diterima`
- `no_lot`
- `expired_date`
- `input_at`

Relasi:

- `tb_lpb_detail.id_lpb` mengacu ke `tb_lpb.id_lpb`.
- `tb_lpb_detail.kd_barang` dicocokkan ke `tb_master_barang_all.kd_barang`.

### `tb_lpb_batch`

Fungsi: detail batch/lot dari baris LPB.

Kolom penting:

- `id_batch`
- `id_detail_lpb`
- `no_lot`
- `expired_date`
- `qty`

Relasi:

- `tb_lpb_batch.id_detail_lpb` mengacu ke `tb_lpb_detail.id_detail_lpb`.

### `tb_tmp_po_received`

Fungsi: draft sementara penerimaan barang sebelum difinalkan menjadi LPB.

Kolom penting:

- `id_tmp_recieved`
- `kd_po`
- `kd_suplier`
- `kd_barang`
- `qty_diterima`
- `satuan`
- `no_lot`
- `expired_date`
- `crete_at`

Catatan:

- Nama kolom `id_tmp_recieved` dan `crete_at` mengikuti struktur legacy yang sudah dipakai aplikasi.
- Data dihapus setelah finalisasi LPB berhasil.

## Tabel Pendukung Master

### `tb_master_barang_all`

Fungsi: master barang dan konversi dimensi.

Dipakai untuk:

- Nama barang.
- Perhitungan qty kecil: `qty * (p*l*t)`.
- Join dari `kd_barang`.

Risiko:

- Jika `p`, `l`, atau `t` kosong/tidak akurat, progress LPB ikut tidak akurat.

### `tb_suplier`

Fungsi: master supplier.

Dipakai untuk:

- Menampilkan `nama_suplier` berdasarkan `kd_suplier`.

### `tb_satuan`

Fungsi: master satuan.

Dipakai untuk:

- Pilihan satuan di detail PO/penerimaan.

### `tb_gudang`

Fungsi: master gudang.

Dipakai untuk:

- Pilihan gudang saat finalisasi LPB.
- Nama gudang pada record dan print LPB.

## Tabel Log dan Admin PO

### `tb_pre_po_adjustment_log`

Fungsi: audit trail adjustment harga Admin PO.

Kolom penting:

- `id_log`
- `kd_po`
- `kd_barang`
- `harga_satuan_lama`
- `harga_satuan_baru`
- `harga_total_lama`
- `harga_total_baru`
- `alasan`
- `dilakukan_oleh`
- `dilakukan_pada`

### `tb_lpb_log`

Fungsi: audit trail update invoice LPB.

Kolom penting:

- `id_log`
- `kd_po`
- `no_invoice`
- `action_type`
- `keterangan`
- `dilakukan_oleh`
- `dilakukan_pada`

Action type:

- `CREATE_INVOICE`
- `UPDATE_INVOICE`

### `tb_pre_po_diskon_history`

Fungsi: histori diskon dari hasil sync KIU_PO/API.

Kolom penting:

- `id`
- `kd_po`
- `id_diskon_source`
- `kd_suplier`
- `no_po`
- `tgl_transaksi`
- `nama_suplier`
- `keterangan`
- `nominal`
- `source_payload`
- `synced_at`

### `tb_pre_po_invoice_adjustment`

Fungsi: data invoice dan harga diskon hasil sync.

Kolom penting:

- `id`
- `no_po`
- `kd_po`
- `tgl_transaksi`
- `kd_suplier`
- `kd_barang`
- `satuan`
- `qty`
- `harga_satuan`
- `harga`
- `harga_diskon`
- `total_harga`
- `total_harga_diskon`
- `tax_percent`
- `tax`
- `tax_diskon`
- `grand_total`
- `grand_total_diskon`
- `source_payload`
- `synced_at`

## Tabel Stok ERP Opsional

### `tberp_stock_batch`

Fungsi: saldo stok per barang, gudang, lot, dan expired date.

Dipakai saat finalisasi LPB jika tabel tersedia. Jika batch sudah ada, `qty_on_hand` ditambah. Jika belum ada, sistem insert batch baru.

### `tberp_stock_ledger`

Fungsi: jurnal pergerakan stok.

Dipakai saat finalisasi LPB jika tabel tersedia.

Payload utama:

- `kd_barang`
- `gudang_id`
- `no_lot`
- `expired_date`
- `qty`
- `tipe = IN`
- `ref_no = kd_po`
- `ref_type = PO_RECEIVED`
- `created_at`

## Tabel Legacy / Transisi

### `tb_ics_po`

Fungsi: tabel PO/LPB ICS lama yang masih dipakai oleh import CSV `ics/import_csv`.

Kolom yang diisi import:

- `tgl_transaksi`
- `kd_faktur_lpb`
- `kd_barang`
- `nama_barang`
- `exp_date`
- `qty`
- `lpb_note`
- `input_at`
- `lpb_status`

Catatan:

- Untuk pengembangan flow `ics/icspo` saat ini, sumber utama adalah `tb_pre_po`, bukan `tb_ics_po`.
- `tb_ics_po` tetap penting untuk fitur lama dan laporan ICS lain yang masih membaca tabel tersebut.

### `tb_po_received`

Fungsi: tabel penerimaan lama yang masih muncul di beberapa method legacy.

Catatan:

- Flow final terbaru memakai `tb_lpb`, `tb_lpb_detail`, dan `tb_lpb_batch`.
- Jangan menambah fitur baru ke `tb_po_received` kecuali request eksplisit menyasar jalur lama.

## Alur Data Utama

```text
KIU_PO database `kiucoid_po`
  -> sync_pre_po_erp
  -> tb_pre_po
  -> ics/icspo daftar PO
  -> detail_po input draft
  -> tb_tmp_po_received
  -> finalize LPB
  -> tb_lpb + tb_lpb_detail + tb_lpb_batch
  -> tberp_stock_batch + tberp_stock_ledger
  -> tb_pre_po.status = 2
```

Fallback bila database `kiucoid_po` tidak tersedia:

```text
http://localhost/kiu_po/get_data_pre_po_erp
```

## Query Business Logic Penting

Progress daftar LPB dihitung dari:

- Total order: `SUM(tb_pre_po.qty * (tb_master_barang_all.p * tb_master_barang_all.l * tb_master_barang_all.t))`
- Total diterima: `SUM(tb_lpb_detail.qty_diterima)` per `tb_lpb.kd_po`

Status:

- `belum`: total diterima `<= 0`
- `partial`: total diterima `< total order`
- `done`: total diterima `>= total order`

## Rekomendasi Database

- Pertahankan `kd_po` sebagai kunci bisnis utama untuk mengikat `tb_pre_po`, `tb_lpb`, log invoice, history diskon, dan adjustment.
- Pastikan `tb_master_barang_all.kd_barang` konsisten dengan `tb_pre_po.kd_barang` dan `tb_lpb_detail.kd_barang`.
- Evaluasi penambahan index di kolom yang sering dipakai:
  - `tb_pre_po.kd_po`
  - `tb_pre_po.kd_barang`
  - `tb_pre_po.kd_suplier`
  - `tb_lpb.kd_po`
  - `tb_lpb_detail.id_lpb`
  - `tb_lpb_detail.kd_barang`
  - `tb_tmp_po_received.kd_po`
  - `tb_tmp_po_received.kd_barang`
- Sebelum mengubah tipe `tgl_transaksi`, audit seluruh sumber sync dan laporan karena saat ini beberapa query memperlakukan field tersebut sebagai string.
- Jalankan migrasi `db/2026/20260707_alter_tb_pre_po_numeric_precision.sql` di server production sebelum sync data PO skala penuh, karena nilai `harga_total` dari `kiu_po` dapat melebihi batas `INT`.

## Validasi Struktur

Struktur tabel diverifikasi dari dump `db/kiucoid_karismaerp.sql` pada tanggal dokumentasi ini dibuat. Database aktif lokal yang terbaca dari konfigurasi adalah `kiucoid_karismaerp_local`.

Validasi lokal 2026-07-07:

- `kiucoid_po.tb_detail_po`: 53 baris sumber.
- `kiucoid_karismaerp_local.tb_pre_po` sebelum sync: 0 baris.
- `kiucoid_karismaerp_local.tb_pre_po` setelah sync: 53 baris.
- Grup PO untuk daftar `ics/icspo`: 24 `kd_po`.
- Contoh nilai besar yang tersimpan benar setelah migrasi: `2381280000.0000`.
