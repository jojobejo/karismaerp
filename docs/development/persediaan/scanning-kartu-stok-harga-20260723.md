# Scanning Development - Modul Kartu Stok Berbasis Harga

Tanggal scan: 2026-07-23
Repo: `C:\xampp\htdocs\karismaerp`
Database lokal terbaca: `kiucoid_karismaerp_local`
Status pekerjaan: analisa read-only, tidak ada perubahan code aplikasi.

## Tujuan Modul

Modul kartu stok yang direncanakan bukan hanya kartu stok kuantitas. Modul ini harus menjadi buku audit persediaan yang menyatukan:

- kuantitas masuk, keluar, retur, mutasi, adjustment, dan saldo awal;
- harga beli dari LPB/PO;
- harga jual dari SO/Faktur Penjualan;
- harga pokok pembelian;
- harga pokok penjualan;
- DPP, PPN, dan grand total yang sudah dipakai pada alur LPB;
- saldo akhir kuantitas dan saldo akhir nilai persediaan per barang, gudang, lot, dan expired date.

## Peta Modul Existing

### Stock

Route stock sudah tersedia:

- `stock`
- `stock/detail`
- `stock/summary`
- `stock/gudangs`
- `stock/available`
- `stock/items`
- `stock/batches`
- `stock/ledger`
- `stock/reconciliation`
- `stock/sync`

File utama:

- `application/controllers/stock/C_Stock.php`
- `application/models/M_Stock.php`
- `application/views/content/stock/stock_control.php`
- `application/views/content/stock/stock_detail.php`

Kondisi saat ini:

- Dashboard dan detail stock sudah berbasis `tberp_stock_ledger` dan `tberp_stock_batch`.
- Yang ditampilkan baru qty, qty box, qty pcs, lot, expired, reserved, movement, dan rekonsiliasi batch vs ledger.
- Belum ada kolom nilai rupiah, harga masuk, harga keluar, HPP movement, DPP movement, saldo nilai, margin, atau laba kotor.

### Pembelian/LPB

File utama:

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/models/M_LpbPriceAdjustment.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

Tabel sumber:

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tbpo_po`
- `tbpo_detail_po`

Kondisi saat ini:

- LPB sudah menyimpan `harga_satuan`, `total_harga`, harga sebelumnya, dan verifikasi harga.
- Query detail LPB sudah menghitung DPP dari qty LPB dikali harga satuan kecil exclude.
- DPP Nilai Lain dan PPN di detail LPB masih berupa kalkulasi query, bukan kolom permanen.
- Alur adjustment harga LPB sudah ada, tetapi belum menjadi histori nilai persediaan per layer stok.

### Penjualan

File utama:

- `application/controllers/sales/C_SalesOrder.php`
- `application/models/M_SalesOrder.php`

Tabel sumber:

- `tbso_sales_order`
- `tbso_sales_order_detail`
- `tbso_faktur_penjualan`
- `tbso_faktur_detail`

Kondisi saat ini:

- Faktur penjualan detail sudah menyimpan `hrg_satuan`, `hrg_pokok`, subtotal, pajak, dan total harga.
- Stok keluar dari faktur sudah menulis movement `OUT` ke `tberp_stock_ledger`.
- HPP penjualan saat ini mengambil dari master barang atau nilai detail yang tersedia, belum dari layer pembelian/LPB yang benar-benar dipakai oleh lot tersebut.

### Accounting/Jurnal

File terkait:

- `application/models/M_Keuangan.php`
- `application/models/M_Journal.php`
- service accounting yang dipakai LPB purchase journal

Kondisi saat ini:

- Posting jurnal pembelian LPB sudah pernah dibuat untuk goods receipt.
- Akun HPP barang sudah disiapkan di master barang.
- Kartu stok harga harus disambungkan ke accounting hanya setelah nilai movement stok valid, karena salah nilai HPP akan langsung memengaruhi persediaan, HPP, dan laba rugi.

## Hasil Validasi Database Lokal

Query read-only dijalankan ke `kiucoid_karismaerp_local`.

Jumlah data utama:

| Tabel | Jumlah Baris |
| --- | ---: |
| `tberp_stock_ledger` | 10.315 |
| `tberp_stock_batch` | 10.078 |
| `tb_lpb` | 7 |
| `tb_lpb_detail` | 8 |
| `tbso_faktur_detail` | 31 |
| `tbpo_detail_po` | 5 |

Temuan data:

- `tberp_stock_ledger` belum memiliki field harga, DPP, HPP, nilai masuk, nilai keluar, atau saldo nilai.
- Semua `tbso_faktur_detail` lokal memiliki `hrg_pokok`, tetapi nilainya seragam `20000.00`.
- Semua `tb_master_barang_all` lokal memiliki `hpp` dengan nilai seragam `20000.00`.
- `tb_lpb_detail` sudah memiliki harga dan total harga, tetapi ada baris adjustment dengan qty dan nilai sangat besar sehingga harus diberi klasifikasi movement yang jelas sebelum masuk kartu stok finansial.
- Ada satu ledger `SALES_ORDER_CANCEL` dengan `tipe` kosong. Ini harus dibersihkan atau dimapping sebelum laporan kartu stok dipercaya.

## Gap Utama

1. Ledger kuantitas belum ledger nilai.

`tberp_stock_ledger` adalah sumber terbaik untuk kartu stok kuantitas, tetapi belum cukup untuk kartu stok harga. Kalau laporan harga dipaksa join langsung ke LPB/faktur tanpa menyimpan snapshot harga movement, hasil historis bisa berubah saat harga LPB, PO, atau master barang dikoreksi.

2. HPP penjualan belum terikat ke layer pembelian.

Saat faktur mengambil barang per `kd_barang`, `gudang_id`, `no_lot`, dan `expired_date`, sistem belum menyimpan referensi layer masuk mana yang dikonsumsi. Tanpa layer ini, HPP penjualan hanya bisa ditebak dari master HPP atau harga rata-rata global.

3. DPP LPB sudah dihitung, tetapi belum distandardisasi sebagai source cost.

DPP pada LPB dipakai dari harga exclude. Ini cocok menjadi dasar harga pokok pembelian. Namun perlu aturan final:

- apakah cost per unit memakai DPP saja;
- apakah PPN masuk dikeluarkan dari cost;
- apakah diskon pembelian mengurangi DPP;
- apakah biaya lain, retur pembelian, dan adjustment harga mengubah layer cost.

4. Banyak sumber stok lama dan baru berjalan berdampingan.

Ada `tb_saldo_awal`, view `v_stock_per_gudang`, `tb_dailystock`, `tb_qty_lot`, `tberp_stock_batch`, dan `tberp_stock_ledger`. Modul baru harus memilih satu sumber utama. Rekomendasi: `tberp_stock_ledger` sebagai event ledger dan `tberp_stock_batch` sebagai snapshot operasional.

5. Retur dan adjustment belum menjadi cost event yang lengkap.

Movement `RBELI`, `RJUAL`, `ADJIN`, `ADJOUT`, dan `LPB_PRICE_ADJUSTMENT_*` sudah muncul, tetapi belum memiliki nilai financial movement yang eksplisit.

## Rekomendasi Scope Modul

### Nama Modul

Rekomendasi nama: `Kartu Stok Nilai` atau `Kartu Stok Harga`.

### Role Awal

Minimal role yang perlu akses:

- Accounting/Keuangan: melihat nilai persediaan, HPP, DPP, margin.
- Logistik/ICS: melihat qty, lot, sumber transaksi, tanpa wajib melihat margin.
- Purchasing: melihat harga pembelian, DPP LPB, adjustment harga.
- Admin: rekonsiliasi dan koreksi.

### Filter Wajib

- Periode tanggal movement.
- Kode barang/nama barang.
- Gudang.
- No lot.
- Expired date.
- Tipe movement.
- Ref no/ref type.
- Supplier/customer jika source tersedia.

### Kolom Laporan Wajib

Kolom identitas:

- tanggal movement;
- tipe movement;
- ref type;
- ref no;
- kd barang;
- nama barang;
- gudang;
- no lot;
- expired date.

Kolom qty:

- qty masuk;
- qty keluar;
- saldo qty;
- qty reserved bila ingin ditampilkan sebagai komitmen.

Kolom pembelian:

- harga beli satuan;
- DPP pembelian;
- PPN pembelian;
- total pembelian;
- harga pokok pembelian per unit.

Kolom penjualan:

- harga jual satuan;
- DPP penjualan;
- PPN penjualan;
- total penjualan;
- harga pokok penjualan per unit;
- total HPP penjualan;
- margin kotor.

Kolom saldo nilai:

- saldo nilai persediaan;
- average cost berjalan;
- sumber cost: LPB, saldo awal, adjustment, retur, fallback master.

## Rekomendasi Arsitektur

### Prinsip CEO/Produk

Modul ini sebaiknya tidak sekadar "tambah kolom harga ke laporan stok". Nilai strategisnya adalah membuat persediaan dapat diaudit dari tiga sudut sekaligus:

- operasional: barang mana, lot mana, gudang mana;
- finance: berapa nilai persediaan dan HPP;
- bisnis: margin per barang, per batch, dan per transaksi.

### Prinsip Teknis

1. Jangan ubah `tberp_stock_ledger` langsung menjadi kalkulator yang kompleks tanpa desain.

Lebih aman menambah layer financial ledger terpisah yang mengacu ke stock ledger.

2. Simpan snapshot harga pada saat movement dibuat.

Harga historis tidak boleh bergantung pada join live ke `tbpo_detail_po`, `tb_lpb_detail`, atau master barang karena data tersebut bisa berubah.

3. Jadikan LPB POST/verified sebagai sumber cost pembelian.

Harga pembelian belum final sebaiknya tetap tampil sebagai draft/unverified, tetapi tidak boleh memengaruhi kartu stok final accounting.

4. Penjualan harus mengambil cost dari layer stok.

Untuk barang lot-based, cost harus mengikuti lot yang keluar. Jika satu lot punya lebih dari satu layer pembelian, perlu aturan FIFO/Average sesuai master barang.

5. Pisahkan laporan operasional dan laporan financial.

`stock/detail` existing bisa tetap menjadi kartu stok operasional. Modul baru sebaiknya punya endpoint/report baru supaya tidak merusak alur logistik yang sudah berjalan.

## Yang Perlu Ditambahkan

### Aplikasi

- Menu/route baru untuk kartu stok nilai, misalnya `stock/kartu-nilai`.
- Model/service kalkulasi cost movement.
- Endpoint DataTables atau server-side list untuk periode dan filter besar.
- Detail drilldown per barang/lot yang menampilkan movement dan saldo berjalan.
- Export Excel/PDF untuk accounting.
- Guard akses agar kolom margin/HPP tidak terbuka ke role yang tidak perlu.
- Label status nilai: `FINAL`, `UNVERIFIED_PRICE`, `FALLBACK_MASTER_HPP`, `MISSING_COST`, `ADJUSTED`.

### Business Rule

- Rumus harga pokok pembelian:
  - default: DPP LPB / qty diterima;
  - PPN tidak masuk cost jika PPN masukan dikreditkan;
  - diskon pembelian harus mengurangi DPP;
  - retur pembelian mengurangi qty dan nilai layer;
  - adjustment harga mengubah nilai layer, bukan membuat qty fisik palsu.

- Rumus HPP penjualan:
  - FIFO: konsumsi layer pembelian paling lama sesuai barang/gudang/lot;
  - Average: saldo nilai / saldo qty sebelum transaksi keluar;
  - LIFO hanya bila benar-benar dibutuhkan karena lebih sulit diaudit.

### Validasi

- Qty keluar tidak boleh menghasilkan saldo layer negatif tanpa status exception.
- HPP penjualan tidak boleh nol kecuali barang bonus/sample dengan aturan eksplisit.
- Movement dengan `tipe` kosong harus ditolak dari laporan final.
- Selisih `tberp_stock_batch` vs `tberp_stock_ledger` harus diselesaikan sebelum closing kartu stok.

## Yang Perlu Dikurangi/Dihindari

- Jangan menjadikan `tb_master_barang_all.hpp` sebagai satu-satunya HPP penjualan.
- Jangan menghitung ulang harga historis murni dari join live ke PO/LPB/faktur.
- Jangan mencampur qty adjustment harga dengan qty fisik. Adjustment harga harus financial adjustment, bukan stok masuk/keluar fisik kecuali memang barang fisik berubah.
- Jangan membuat logic harga hardcode di view.
- Jangan memakai view lama `v_stock_per_gudang` sebagai sumber final kartu stok nilai jika `tberp_stock_ledger` sudah menjadi sumber event.
- Jangan menampilkan margin/HPP ke semua role tanpa pembatasan.

## Rekomendasi Tahapan Development

### Tahap 1 - Fondasi Read-only

- Bangun query kartu stok nilai read-only dari transaksi existing.
- Tampilkan status data yang belum siap, bukan memaksa angka terlihat final.
- Tidak menulis jurnal atau mengubah stok.

### Tahap 2 - Financial Ledger

- Tambah tabel ledger nilai yang menyimpan snapshot harga movement.
- Backfill dari LPB, faktur, retur, dan adjustment dengan status `MIGRATED`.
- Cocokkan saldo qty dengan `tberp_stock_ledger`.

### Tahap 3 - Cost Layer

- Tambah layer pembelian untuk FIFO/Average.
- Faktur penjualan mengonsumsi layer dan menyimpan HPP final.
- Retur penjualan mengembalikan layer dengan referensi faktur asal.

### Tahap 4 - Closing dan Accounting

- Closing period persediaan.
- Reconcile kartu stok nilai dengan jurnal persediaan/HPP.
- Lock perubahan harga setelah period closing, kecuali lewat adjustment resmi.

## Pertanyaan Bisnis yang Harus Diputuskan

- HPP perusahaan ingin default Average, FIFO, atau per barang mengikuti flag `tbpo_barang.hpp_average/hpp_fifo/hpp_lifo`?
- PPN masukan untuk BKP selalu dikeluarkan dari cost, atau ada kasus PPN menjadi bagian biaya?
- Biaya tambahan pembelian seperti ongkir, bea, atau biaya lain sudah ada sumber datanya atau belum?
- Bonus pembelian dan bonus penjualan memengaruhi average cost atau dicatat sebagai harga nol dengan treatment khusus?
- Adjustment harga LPB yang sudah terjadi setelah barang terjual harus membentuk jurnal koreksi HPP atau hanya koreksi persediaan tersisa?

## Kesimpulan

Project sudah punya bahan baku yang kuat untuk modul kartu stok harga: ledger stok, batch, LPB dengan DPP, faktur penjualan, HPP master, dan jurnal pembelian. Tetapi modul belum siap dieksekusi sebagai laporan financial final karena belum ada ledger nilai dan cost layer.

Rekomendasi terbaik adalah membangun modul baru sebagai `Kartu Stok Nilai` yang mengambil `tberp_stock_ledger` sebagai urutan movement, lalu menambahkan financial ledger/cost layer agar harga beli, harga jual, HPP pembelian, HPP penjualan, dan DPP dapat diaudit secara historis.
