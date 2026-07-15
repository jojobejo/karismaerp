# Database and Source Code Analysis - Accounting

Tanggal scan: 2026-07-13  
Acuan: `docs/accounting/MASTER_SPECS.md`  
Project: KARISMA ERP, CodeIgniter 3, MariaDB 10.4

## Ringkasan Status

Modul accounting yang sudah tersedia di aplikasi saat ini masih berada pada tahap fondasi Chart of Accounts.

Yang sudah ada:

- Route `jurnal` dan `keuangan/jurnal`.
- Controller `application/controllers/keuangan/C_Keuangan.php`.
- Model `application/models/M_Keuangan.php`.
- View `application/views/content/keuangan/jurnal.php`.
- AJAX view `application/views/content/keuangan/ajax/ajax_jurnal.php`.
- Migration awal `tbkeu_klasifikasi_akun`, `tbkeu_akun`, `tbkeu_saldo_normal`, `tbkeu_tipe_kontrol`.
- Migration awal GL untuk `tbkeu_periode_fiskal`, `tbkeu_jenis_jurnal`, `tbkeu_jurnal`, `tbkeu_jurnal_detail`.

Yang belum ada sesuai target `MASTER_SPECS.md`:

- Controller modular `C_Akun`, `C_Jurnal`, `C_PeriodeFiskal`, `C_MappingAkun`, `C_FakturPembelian`, `C_Pembayaran`, `C_PostingException`, `C_LaporanKeuangan`.
- Model modular pada `application/models/keuangan`.
- Library posting service dan service pendukung accounting.
- `tbkeu_mapping_akun`, `tbkeu_jurnal_log`, `tbkeu_posting_exception`, `tbkeu_faktur_pembelian`, `tbkeu_faktur_pembelian_detail`, `tbkeu_pembayaran`, `tbkeu_pembayaran_alokasi`, `tbkeu_saldo_awal_akun`.
- Auto-posting transaksi ERP.
- Manual journal posting/reversal penuh.
- Laporan buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, kas/bank berbasis jurnal `POSTED`.

Empat tabel Purchase Order di luar scope accounting tetap tidak boleh digunakan sebagai sumber jurnal:

- `tbpo_transaksi`
- `tbpo_transaksi_tmp`
- `tbpo_transaksi_trashbin`
- `tbpo_akun_tr`

## 1. Proses Sales Order

Source utama:

- Controller: `application/controllers/sales/C_SalesOrder.php`
- Model: `application/models/M_SalesOrder.php`
- View: `application/views/content/sales/*`
- Tabel: `tbso_sales_order`, `tbso_sales_order_detail`, `tb_customer`, `tbso_stock_reservation`, `tberp_stock_ledger`, `tberp_stock_batch`

Alur yang ditemukan:

1. User membuat SO melalui route `sales_order/create` dan submit ke `sales_order/store`.
2. Header masuk ke `tbso_sales_order`.
3. Detail masuk ke `tbso_sales_order_detail`.
4. Stock direserve lewat `tbso_stock_reservation`.
5. Jika tersedia, `tberp_stock_ledger` ditulis dengan tipe `RESERVE`.
6. Jika tersedia, `tberp_stock_batch.qty_reserved` bertambah.
7. Cancel SO menulis ledger `RELEASE` dan mengurangi `qty_reserved`.

Catatan accounting:

- SO belum boleh menjadi jurnal pendapatan karena belum ada penyerahan barang final.
- SO hanya source komitmen penjualan dan reservasi stock.
- Candidate posting event paling aman bukan `SALES_ORDER_CREATED`, melainkan faktur/DO final.

## 2. Proses Faktur Penjualan

Source utama:

- Tabel target baru di dump master: `tbso_faktur_penjualan`, `tbso_faktur_detail`.
- Flow operasional lama masih memakai DO: `tb_do`, `tb_detail_do`, `tb_pre_do`.
- Route relevan: `rekam_order_check`, `do/confirm_sales`, `sales_order/list_do`, `sales_order/detail_do/(:any)`.

Alur yang ditemukan:

1. Data SO atau faktur operasional diproses menjadi DO.
2. `rekam_order_check` memfinalkan pengecekan order logistik dan menulis stock keluar.
3. `do/confirm_sales` mengonfirmasi kesiapan sales dengan action `siap` atau `belum_siap`.
4. Bila action `siap`, status DO menjadi siap/kirim dan status SO terkait disinkronkan sebagai selesai pada sebagian flow.

Catatan accounting:

- Jika memakai tabel faktur baru, posting `SALES_INVOICE` sebaiknya dilakukan saat `tbso_faktur_penjualan.status = confirmed`.
- Jika memakai flow DO existing, posting sebaiknya setelah `do/confirm_sales` action `siap`, bukan saat SO dibuat.
- Perlu keputusan final apakah dokumen akuntansi resmi penjualan adalah `tbso_faktur_penjualan` atau DO existing.

## 3. Proses Konfirmasi Faktur

Konfirmasi final yang paling aman harus memenuhi syarat:

- Dokumen penjualan memiliki nomor final.
- Customer valid.
- Tanggal transaksi valid dan masuk periode fiskal `OPEN`.
- Detail barang lengkap.
- Nilai jual, diskon, pajak, dan HPP tersedia.
- Stock keluar final atau minimal sudah siap difakturkan.

Candidate event:

- `SALES_INVOICE_CONFIRMED` jika memakai `tbso_faktur_penjualan`.
- `DO_SALES_CONFIRMED_READY` jika memakai `do/confirm_sales` action `siap`.

Expected journal:

```text
Debit  Piutang Usaha / Kas Bank
Kredit Pendapatan Penjualan
Kredit PPN Keluaran

Debit  Harga Pokok Penjualan
Kredit Persediaan Barang
```

## 4. Proses Stock Keluar

Source utama:

- `tberp_stock_ledger`
- `tberp_stock_batch`
- `tb_detail_do`

Alur:

1. SO membuat reserve stock.
2. DO final menulis stock ledger tipe `OUT`.
3. Cancel atau repost status dapat mengembalikan status operasional.

Catatan accounting:

- `tberp_stock_ledger` adalah ledger kuantitas, bukan general ledger finansial.
- Jurnal HPP tetap harus dibuat di `tbkeu_jurnal` dan `tbkeu_jurnal_detail`.
- HPP diambil berurutan dari `tbso_faktur_detail.hrg_pokok`, `tbso_sales_order_detail.hrg_pokok`, lalu `tb_master_barang_all.hpp`.
- Jika HPP nol/tidak tersedia, auto-posting wajib gagal dan masuk exception.

## 5. Proses Pembayaran Customer

Source existing:

- `tbkeu_pembayaran_faktur`

Struktur existing memuat nominal pembayaran, metode pembayaran, tanggal BG, dan status BG, tetapi belum setara dengan desain `tbkeu_pembayaran` plus `tbkeu_pembayaran_alokasi` pada spesifikasi.

Catatan accounting:

- Untuk standar AR, source final sebaiknya memakai `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi`.
- Satu pembayaran harus dapat dialokasikan ke banyak faktur.
- Satu faktur harus dapat dibayar bertahap.
- Alokasi tidak boleh melebihi outstanding faktur.

Expected journal:

```text
Debit  Kas/Bank
Kredit Piutang Usaha
```

Untuk BG:

```text
Saat diterima:
Debit  BG Dalam Pencairan
Kredit Piutang Usaha

Saat cair:
Debit  Bank
Kredit BG Dalam Pencairan

Saat ditolak:
Debit  Piutang Usaha
Kredit BG Dalam Pencairan
```

## 6. Proses Retur Penjualan

Route:

- `ics/retur/penjualan`
- `ics/retur/add_detail`
- `ics/retur/rekam_penjualan`
- `ics/retur/detail_retur`

Catatan accounting:

- Retur penjualan baru boleh posting setelah retur direkam final dan barang yang kembali diterima gudang.
- Jika uang dikembalikan langsung, kredit ke kas/bank.
- Jika hanya mengurangi tagihan, kredit ke piutang.

Expected journal:

```text
Debit  Retur Penjualan
Debit  PPN Keluaran
Kredit Piutang Usaha / Kas Bank

Debit  Persediaan Barang
Kredit Harga Pokok Penjualan
```

## 7. Proses Purchase Order

Source utama:

- `tb_pre_po`
- `tb_pre_po_invoice_adjustment`
- `tb_pre_po_diskon_history`
- `tbpo_po`
- `tbpo_detail_po`
- `tb_suplier`
- `tbpo_suplier`

Catatan penting:

- `tbpo_po` dan `tbpo_detail_po` boleh dipakai sebagai source document pembelian.
- Empat tabel PO yang dilarang tetap tidak boleh dipakai accounting.
- PO belum menghasilkan jurnal karena belum ada barang diterima atau invoice supplier final.

Titik accounting:

- PO dipakai sebagai referensi harga saat LPB.
- Jika harga PO kosong/nol, posting LPB harus gagal.

## 8. Proses LPB / Penerimaan Barang

Source utama:

- Controller: `application/controllers/logistik/C_Ics.php`
- Model: `application/models/M_Logistik.php`
- Tabel: `tb_tmp_po_received`, `tb_lpb`, `tb_lpb_detail`, `tb_lpb_batch`, `tb_po_received`, `tberp_stock_batch`, `tberp_stock_ledger`

Alur:

1. Admin PO/logistik membuat draft penerimaan dari PO.
2. Draft disimpan sementara di `tb_tmp_po_received`.
3. `ics/ajax_finalize_tmp_po_received` memvalidasi header, detail, gudang, invoice, dan sisa qty PO.
4. `create_lpb_from_tmp()` membuat `tb_lpb`, `tb_lpb_detail`, dan `tb_lpb_batch`.
5. Stock batch bertambah.
6. Stock ledger ditulis dengan tipe `IN` dan ref `PO_RECEIVED`.
7. Draft temporary dihapus.
8. Status `tb_pre_po` diperbarui.

Titik posting aman:

- Setelah transaksi database LPB sukses commit.

Expected journal:

```text
Debit  Persediaan Barang
Kredit GRNI
```

## 9. Proses Input Invoice Supplier

Existing:

- `tb_lpb.no_invoice`
- `tb_lpb.nosj`
- `tb_lpb.tgl_sj`
- `tb_pre_po_invoice_adjustment`

Target spec:

- `tbkeu_faktur_pembelian`
- `tbkeu_faktur_pembelian_detail`

Catatan accounting:

- Field invoice pada LPB belum cukup sebagai AP subledger.
- Invoice supplier harus punya status `DRAFT`, `CONFIRMED`, `PARTIAL_PAID`, `PAID`, `CANCELLED`.
- Posting AP dilakukan saat invoice supplier confirmed.

Expected journal:

```text
Debit  GRNI
Debit  PPN Masukan
Debit/Kredit Selisih Harga Pembelian
Kredit Hutang Usaha
```

## 10. Proses Pembayaran Supplier

Belum ditemukan source existing yang cukup untuk AP payment sesuai spesifikasi.

Target:

- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`

Expected journal:

```text
Debit  Hutang Usaha
Kredit Kas/Bank
```

## 11. Proses Retur Pembelian

Route:

- `ics/retur/pembelian`
- `ics/retur/pembelian/add_detail`
- `ics/retur/rekam_pembelian`

Expected journal:

```text
Debit  Hutang Usaha / Kas Bank
Kredit Persediaan Barang
Kredit PPN Masukan
```

Catatan:

- Perlu validasi final apakah retur mengurangi AP atau mengembalikan kas.
- Retur pembelian harus menurunkan stock hanya setelah barang keluar gudang.

## 12. Proses Mutasi Stock

Source:

- `tb_mutasi`
- `tb_detail_mutasi`
- `tberp_stock_ledger`
- Route: `ics/mutasi_barang`, `ics/mutasi_barang/input`, `ics/ajax_rekam_mutasi`, `ics/ajax_rollback_mutasi`, `ics/ajax_unpost_mutasi`

Catatan schema:

- `tb_mutasi.tgl_transaksi` bertipe `text`.
- `tb_mutasi.input_at` bertipe `text`.
- Status: `POSTED`, `UNPOST`, `ROLLBACK`, `HOLD`.

Treatment accounting:

- Jika akun persediaan gudang asal dan tujuan sama, tidak membuat jurnal keuangan.
- Jika akun persediaan berbeda, posting transfer:

```text
Debit  Persediaan Gudang Tujuan
Kredit Persediaan Gudang Asal
```

## 13. Proses Stock Adjustment

Source terkait:

- `admin/stockopname/*`
- `stockopname/input`
- `tberp_stock_ledger` tipe `ADJIN` dan `ADJOUT`

Catatan:

- Final approval adjustment harus dikunci sebelum auto-posting.
- Selisih kuantitas harus dinilai dengan HPP valid.

Expected journal:

```text
Adjustment positif:
Debit  Persediaan Barang
Kredit Pendapatan/Selisih Stock

Adjustment negatif:
Debit  Beban/Selisih Stock
Kredit Persediaan Barang
```

## 14. Proses Pembatalan Transaksi

Yang ditemukan:

- SO cancel menulis release reservation.
- DO memiliki repost/status ulang.
- Mutasi memiliki rollback/unpost.
- LPB reversal eksplisit belum ditemukan.
- Accounting reversal belum diimplementasikan.

Aturan target:

- Jurnal `POSTED` tidak boleh diedit atau dihapus.
- Koreksi hanya melalui reversal journal.
- Reversal membuat jurnal baru jenis `RJ` dan membalik debit/kredit jurnal awal.

## 15. Titik Source Transaction Paling Aman

| Event | Source aman | Status saat posting | Catatan |
| --- | --- | --- | --- |
| Sales invoice | `tbso_faktur_penjualan` atau DO confirm sales | Faktur confirmed / DO action `siap` | Perlu keputusan source resmi. |
| HPP penjualan | Detail faktur/SO + stock keluar | Sama dengan sales invoice | HPP nol wajib exception. |
| Customer receipt | `tbkeu_pembayaran` + alokasi | `CONFIRMED` | Existing payment belum cukup penuh. |
| Goods receipt | `tb_lpb` setelah finalize sukses | LPB tersimpan dan stock `IN` | Harga dari PO wajib valid. |
| Purchase invoice | `tbkeu_faktur_pembelian` | `CONFIRMED` | Belum tersedia. |
| Supplier payment | `tbkeu_pembayaran` + alokasi | `CONFIRMED` | Belum tersedia. |
| Sales return | Retur penjualan final | Barang diterima / tagihan dikoreksi | Perlu lock source final. |
| Purchase return | Retur pembelian final | Barang keluar / AP dikoreksi | Perlu lock source final. |
| Stock adjustment | Approval final opname/adjustment | Final approved | Perlu nilai HPP. |
| Mutasi gudang | `tb_mutasi.status = POSTED` | Jika akun gudang berbeda | Jika akun sama, tidak posting GL. |

## SQL Audit Read-Only Wajib

Query berikut tidak memperbaiki data otomatis. Jalankan pada database staging/local lalu simpan hasilnya sebagai lampiran UAT.

```sql
-- Duplicate kode barang
SELECT kd_barang, COUNT(*) total
FROM tb_master_barang_all
GROUP BY kd_barang
HAVING COUNT(*) > 1;

-- Duplicate customer
SELECT kd_customer, COUNT(*) total
FROM tb_customer
GROUP BY kd_customer
HAVING COUNT(*) > 1;

-- Duplicate supplier legacy
SELECT kd_suplier, COUNT(*) total
FROM tb_suplier
GROUP BY kd_suplier
HAVING COUNT(*) > 1;

-- Duplicate supplier PO
SELECT kd_suplier, COUNT(*) total
FROM tbpo_suplier
GROUP BY kd_suplier
HAVING COUNT(*) > 1;

-- Duplicate nomor faktur penjualan
SELECT no_faktur, COUNT(*) total
FROM tbso_faktur_penjualan
GROUP BY no_faktur
HAVING COUNT(*) > 1;

-- Duplicate nomor LPB
SELECT no_po, nosj, no_invoice, COUNT(*) total
FROM tb_lpb
GROUP BY no_po, nosj, no_invoice
HAVING COUNT(*) > 1;

-- Orphan faktur detail
SELECT d.*
FROM tbso_faktur_detail d
LEFT JOIN tbso_faktur_penjualan h ON h.id_faktur = d.id_faktur
WHERE h.id_faktur IS NULL;

-- Orphan Sales Order detail
SELECT d.*
FROM tbso_sales_order_detail d
LEFT JOIN tbso_sales_order h ON h.id_so = d.id_so
WHERE h.id_so IS NULL;

-- Orphan LPB detail
SELECT d.*
FROM tb_lpb_detail d
LEFT JOIN tb_lpb h ON h.id_lpb = d.id_lpb
WHERE h.id_lpb IS NULL;

-- Orphan LPB batch
SELECT b.*
FROM tb_lpb_batch b
LEFT JOIN tb_lpb_detail d ON d.id_detail_lpb = b.id_detail_lpb
WHERE d.id_detail_lpb IS NULL;

-- Orphan pembayaran customer existing
SELECT p.*
FROM tbkeu_pembayaran_faktur p
LEFT JOIN tbso_faktur_penjualan f ON f.id_faktur = p.id_faktur
WHERE f.id_faktur IS NULL;

-- HPP nol pada faktur/SO/master
SELECT d.id, d.no_faktur, d.kd_barang, d.qty, d.hrg_pokok
FROM tbso_faktur_detail d
WHERE COALESCE(d.hrg_pokok, 0) <= 0;

SELECT d.id, d.no_so, d.kd_barang, d.qty, d.hrg_pokok
FROM tbso_sales_order_detail d
WHERE COALESCE(d.hrg_pokok, 0) <= 0;

SELECT id, kd_barang, nama_barang, hpp
FROM tb_master_barang_all
WHERE COALESCE(hpp, 0) <= 0;

-- Harga jual nol
SELECT id, no_faktur, kd_barang, hrg_satuan, total_harga
FROM tbso_faktur_detail
WHERE COALESCE(hrg_satuan, 0) <= 0 OR COALESCE(total_harga, 0) <= 0;

-- Nominal negatif source penjualan
SELECT id, no_faktur, kd_barang, qty, subtotal_after_disc, total_harga
FROM tbso_faktur_detail
WHERE qty < 0 OR subtotal_after_disc < 0 OR total_harga < 0;

-- Tanggal masih TEXT
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME LIKE '%tgl%'
  AND DATA_TYPE IN ('text','varchar','char');

-- Tanggal text tidak dapat dikonversi pada mutasi
SELECT id, noreff, tgl_transaksi
FROM tb_mutasi
WHERE tgl_transaksi IS NOT NULL
  AND tgl_transaksi <> ''
  AND STR_TO_DATE(tgl_transaksi, '%Y-%m-%d') IS NULL
  AND STR_TO_DATE(tgl_transaksi, '%d/%m/%Y') IS NULL;

-- Perbedaan tipe data gudang_id
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME IN ('gudang_id','id_gudang','gudang_asal','gudang_mutasi')
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Perbedaan supplier antara tb_suplier dan tbpo_suplier
SELECT s.kd_suplier, s.nama_suplier, ps.nama_suplier AS nama_suplier_po
FROM tb_suplier s
LEFT JOIN tbpo_suplier ps ON ps.kd_suplier = s.kd_suplier
WHERE ps.kd_suplier IS NULL
   OR COALESCE(ps.nama_suplier, '') <> COALESCE(s.nama_suplier, '');

-- Faktur tanpa detail
SELECT h.*
FROM tbso_faktur_penjualan h
LEFT JOIN tbso_faktur_detail d ON d.id_faktur = h.id_faktur
WHERE d.id IS NULL;

-- LPB tanpa detail
SELECT h.*
FROM tb_lpb h
LEFT JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb
WHERE d.id_detail_lpb IS NULL;

-- Pembayaran melebihi nilai faktur
SELECT p.no_faktur, SUM(p.jumlah_pembayaran) total_bayar, SUM(d.total_harga) total_faktur
FROM tbkeu_pembayaran_faktur p
JOIN tbso_faktur_penjualan f ON f.id_faktur = p.id_faktur
JOIN tbso_faktur_detail d ON d.id_faktur = f.id_faktur
GROUP BY p.no_faktur
HAVING total_bayar > total_faktur;

-- Faktur tanpa customer valid
SELECT f.*
FROM tbso_faktur_penjualan f
LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
WHERE c.kd_customer IS NULL;

-- PO tanpa supplier valid
SELECT p.*
FROM tb_pre_po p
LEFT JOIN tb_suplier s ON s.kd_suplier = p.kd_suplier
LEFT JOIN tbpo_suplier ps ON ps.kd_suplier = p.kd_suplier
WHERE s.kd_suplier IS NULL AND ps.kd_suplier IS NULL;
```
