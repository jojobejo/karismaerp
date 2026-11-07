# Perhitungan HPP Penjualan Barang Konsinyasi Menggunakan Metode FIFO

## 1. Latar Belakang & Akar Masalah

Pada modul konsinyasi KarismaERP sebelumnya, jurnal Harga Pokok Penjualan (HPP) konsinyasi yang terbentuk saat realisasi pembelian/pelunasan kios (`tbkeu_pembayaran_faktur`) masih mengambil harga pokok dari `tbso_faktur_detail.hrg_pokok`. Nilai tersebut merupakan rata-rata bergerak (*Moving Average Cost*) dari seluruh penerimaan LPB konsinyasi yang ada di gudang.

### Bukti Kasus Riil pada Sistem:
Penerimaan LPB konsinyasi untuk barang `QROUN011` (Round Up 486 SL 12 X 1 ltr):
1. **LPB 1 (`2600001K`)**: Masuk 2.000 pcs @ **Rp 63.063,0631** (DPP tanpa PPN).
2. **LPB 2 (`2600002K`)**: Masuk 2.000 pcs @ **Rp 72.072,0721** (DPP tanpa PPN).

Rata-rata bergerak yang tersimpan sebelumnya:
$$\text{Average HPP} = \frac{63.063,0631 + 72.072,0721}{2} = \text{Rp 67.567,57}$$

Ketika terjadi penjualan 20 pcs kepada customer *Abdul Rohman* (`TINV1110260001-1`), sistem lama mencatat jurnal HPP sebesar:
$$20 \text{ pcs} \times \text{Rp } 67.567,57 = \text{Rp } 1.351.351,40$$

Padahal secara prinsip akuntansi konsinyasi FIFO, 20 pcs yang keluar pertama harus menghabiskan stok dari **LPB 1 (`2600001K`)** terlebih dahulu dengan harga beli aktual **Rp 63.063,0631**, sehingga nilai HPP yang benar seharusnya adalah **Rp 1.261.261,26**.

---

## 2. Mekanisme & Logika FIFO yang Diterapkan

Sistem kini menerapkan metode **FIFO (First In, First Out)** khusus untuk barang konsinyasi:
1. **Urutan Penerimaan LPB**: Stok dialokasikan berdasarkan `tgl_lpb ASC, id_lpb ASC, id_detail_lpb ASC`.
2. **Harga Pokok Aktual per Batch**: Setiap barang yang laku menggunakan harga beli DPP murni dari batch LPB terkait.
3. **Mendukung Penjualan Lintas Batch**: Jika jumlah penjualan melebihi sisa stok batch LPB pertama, sisa kekurangan secara otomatis diambil dari batch LPB berikutnya sesuai urutan waktu masuk.
4. **Saldo Stok Terbarui Otomatis**: Setiap alokasi memperbarui `qty_terjual`, `qty_sisa`, dan `status` (`AVAILABLE`, `PARTIAL`, `EXHAUSTED`).
5. **Rollback Konsisten**: Jika pembayaran di-unpost atau dibatalkan di Keuangan, kuantitas stok batch LPB yang dialokasikan dikembalikan secara utuh ke saldo batch asal.
6. **Proteksi Modul Lain**: Perubahan ini terisolasi khusus pada siklus konsinyasi dan tidak mengubah HPP barang penjualan reguler.

---

## 3. Perubahan Struktur Database

File migrasi: [`db/migrations/20261013_consignment_fifo_cogs.sql`](file:///c:/laragon/www/karismaerp/db/migrations/20261013_consignment_fifo_cogs.sql)

### A. Tabel `tb_konsinyasi_lpb_batch_stock`
Menyimpan saldo stok dan harga beli aktual per batch penerimaan LPB konsinyasi:
- `id_batch_stock` (PK, Auto Increment)
- `id_lpb`, `id_detail_lpb` (Relasi ke LPB penerimaan fisik)
- `nomor_lpb`, `tgl_lpb` (Identitas LPB)
- `kd_suplier`, `nama_suplier` (Supplier pemilik barang)
- `kd_barang`, `nama_barang`, `no_lot`, `expired_date` (Identitas barang fisik)
- `qty_masuk`, `qty_terjual`, `qty_sisa` (Tracking saldo kuantitas)
- `harga_beli_satuan` (Harga pokok DPP per pcs dari LPB)
- `status` (`AVAILABLE`, `PARTIAL`, `EXHAUSTED`)

### B. Tabel `tb_konsinyasi_cogs_allocation`
Menyimpan jejak audit alokasi FIFO setiap transaksi penjualan/pembayaran:
- `id_cogs_alloc` (PK, Auto Increment)
- `id_pembayaran`, `id_faktur`, `no_faktur_konsinyasi`
- `id_batch_stock`, `id_lpb`, `nomor_lpb`
- `kd_barang`, `no_lot`, `expired_date`
- `qty_allocated` (Kuantitas yang diambil dari batch tersebut)
- `harga_pokok_satuan` (Harga beli per batch)
- `subtotal_cogs` (`qty_allocated` $\times$ `harga_pokok_satuan`)
- `id_jurnal_gi` (Tautan ke jurnal penyesuaian persediaan/HPP)

---

## 4. File dan Komponen yang Diubah

1. **[`application/models/M_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/models/M_Konsinyasi.php)**:
   - `ensure_schema()`: Memastikan tabel `tb_konsinyasi_lpb_batch_stock` dan `tb_konsinyasi_cogs_allocation` otomatis tersedia.
   - `sync_lpb_consignment_batches()`: Menyinkronkan seluruh penerimaan LPB konsinyasi ke tabel batch stock.
   - `allocate_fifo_consignment_cogs()`: Mesin alokasi FIFO yang menghitung total HPP, memotong saldo stok batch LPB, dan mendukung lintas batch.
   - `revert_fifo_consignment_cogs()`: Memulihkan saldo stok batch LPB saat transaksi dibatalkan / unpost.
   - `update_cogs_journal_id()`: Menautkan jurnal HPP (`GOODS_ISSUE`) ke riwayat alokasi.

2. **[`application/models/M_Journal.php`](file:///c:/laragon/www/karismaerp/application/models/M_Journal.php)**:
   - `post_jurnal_penjualan_konsinyasi()`: Diperbarui agar memanggil `allocate_fifo_consignment_cogs()` saat membentuk jurnal `GOODS_ISSUE` (HPP debit, Persediaan kredit), menggantikan rumus average cost lama.

3. **[`application/models/M_pembayaran.php`](file:///c:/laragon/www/karismaerp/application/models/M_pembayaran.php)**:
   - `unpost_pembayaran()`: Memanggil `revert_fifo_consignment_cogs()` agar saat pembayaran di-unpost, saldo stok pada batch LPB dipulihkan secara otomatis.

---

## 5. Hasil Pengujian Simulasi dan Validasi

### Pengujian Simulasi Unit (Skenario Permintaan User):
- **Skenario 1 (Penjualan 10 pcs dari Batch A @ Rp 10.000)**:
  - Total HPP: **Rp 100.000,00**
  - Sisa Stok: Batch A = 0 pcs, Batch B = 10 pcs.
- **Skenario 2 (Penjualan 12 pcs lintas batch)**:
  - 10 pcs dari Batch A @ Rp 10.000 = Rp 100.000,00
  - 2 pcs dari Batch B @ Rp 15.000 = Rp 30.000,00
  - Total HPP: **Rp 130.000,00**
  - Sisa Stok: Batch A = 0 pcs, Batch B = 8 pcs.
- **Skenario 3 (Penjualan 10 pcs ketika Batch A tersisa 2 pcs)**:
  - 2 pcs dari Batch A @ Rp 10.000 = Rp 20.000,00
  - 8 pcs dari Batch B @ Rp 15.000 = Rp 120.000,00
  - Total HPP: **Rp 140.000,00**
  - Sisa Stok: Batch A = 0 pcs, Batch B = 2 pcs.

### Rekonsiliasi Transaksi Riil (`SJ-11102600001` & `SJ-11102600002`):
- Penjualan 20 pcs `QROUN011` pada `TINV1110260001-1`:
  - **Sebelum**: Debit HPP `Rp 1.351.351,40` / Kredit Persediaan `Rp 1.351.351,40` (Average).
  - **Sesudah**: Debit HPP **Rp 1.261.261,26** / Kredit Persediaan **Rp 1.261.261,26** (FIFO LPB 1 `2600001K`).
  - Total Debit & Kredit Jurnal Penjualan: Seimbang pada **Rp 3.061.261,26**.
