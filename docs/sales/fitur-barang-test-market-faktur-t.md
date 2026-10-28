# Fitur Barang Test Market (Faktur T) — KARISMA ERP

## 1. Latar Belakang & Konsep Bisnis
Barang **Test Market** adalah barang dagangan milik Karisma sendiri (pembelian sudah diakui dan fisik barang bersumber dari **Gudang Induk**, bukan gudang titipan konsinyasi supplier) yang dititipkan ke kios untuk keperluan uji pasar (*market test*).

### Karakteristik & Perbedaan Utama:
| Parameter | Faktur Reguler (INV) | Faktur Konsinyasi (KINV) | Faktur Test Market (TINV) |
| :--- | :--- | :--- | :--- |
| **Sumber Fisik Barang** | Gudang Induk / Umum | Gudang Titipan Kios (15) | **Gudang Induk $\rightarrow$ Gudang Test Market** |
| **Gudang Transit Saat SO** | Tetap di Gudang Induk (Reserve) | Tetap di Gudang Konsinyasi | **Dimutasi ke Gudang Test Market (Transit)** |
| **Gudang Titipan di Kios** | Tidak Ada | Gdg. Titipan di Kios (14) | **Gdg. Test Market di Kios (16)** |
| **Kepemilikan Barang** | Milik Karisma | Milik Supplier Konsinyasi | **Milik Karisma** |
| **Pengakuan Jurnal Penjualan** | Saat Faktur Terbit | Saat Pembayaran di Keuangan | **Saat Pembayaran di Keuangan** |
| **Pengakuan Jurnal HPP & Persediaan** | Saat Faktur Terbit | Saat Pembayaran di Keuangan (FIFO LPB Supplier) | **Saat Pembayaran di Keuangan (Harga Pokok Persediaan Karisma)** |
| **Pelunasan ke Supplier Konsinyasi** | Tidak Ada | Ada (Masuk ke antrean settlement Purchasing Konsinyasi) | **TIDAK ADA (Tidak masuk antrean settlement purchasing konsinyasi)** |
| **Awalan Nomor Faktur** | `[UserPrefix]INV...` | `KINVddmmyyxxxx` | **`TINVddmmyyxxxx`** |

---

## 2. Struktur Gudang & Skema Database

Untuk mengakomodasi alur uji pasar secara terisolasi dan tertib audit, terdapat **dua entitas gudang** yang bekerja bersama:

1. **`Gudang Test Market` (Gudang Transit Karisma)**:
   - Tipe: `INDUK` (Gudang Internal).
   - Fungsi: Menampung barang yang sudah disiapkan untuk pesanan Test Market saat Sales Order (SO) selesai dibuat & direkam. Memastikan stok fisik di Gudang Induk berkurang secara riil dan teralokasi khusus ke gudang uji pasar.

2. **`Gdg. Test Market di Kios` (Gudang Virtual Titipan)**:
   - Tipe: `INDUK` (Gudang Virtual Kios).
   - Fungsi: Menampung posisi titipan barang di masing-masing kios setelah Faktur T resmi diterbitkan oleh Admin SC / Logistik hingga kios melakukan pembayaran/pembelian.

### Skema & Migrasi Database:
- Migrasi Gudang Transit: `database/migrations/20261028_add_gudang_test_market.sql`
- Migrasi Flag Test Market: `db/migrations/20261015_add_test_market_sales.sql`
- Migrasi Tracking Posisi Kios: `db/migrations/20261015_test_market_tracking.sql`

Kolom penanda:
- `tbso_sales_order.is_test_market` (TINYINT 1)
- `tbso_faktur_penjualan.is_test_market` (TINYINT 1)

---

## 3. Rantai Pergerakan Barang (Multi-Stage Stock Movement)

```mermaid
graph TD
    A["1. Form Sales Order<br/>Pilih Gudang Induk &amp; Ceklis [✓] Test Market"] -->|Simpan SO (Draft)| B["Stok di Gudang Induk di-RESERVE"]
    B -->|Klik 'Rekam SO' (Status Jadi Open)| C["2. MUTASI OTOMATIS<br/>Gudang Induk (OUT) ➔ Gudang Test Market (IN + RESERVE)<br/>Lokasi SO beralih ke Gudang Test Market"]
    C -->|Verifikasi &amp; Terbitkan Faktur T| D["3. FAKTUR PENJUALAN (Prefix T)<br/>Gudang Test Market (OUT)<br/>➔ Masuk ke Gdg. Test Market di Kios (IN Titipan)"]
    D -->|Kios Membayar / Beli di Keuangan| E["4. REALISASI PEMBAYARAN<br/>Gdg. Test Market di Kios (OUT Terjual)<br/>Terbit Jurnal Penjualan (SJ) &amp; HPP (GI)"]
    C -.->|Jika SO Dibatalkan / Cancel| F["MUTASI ROLLBACK<br/>Gudang Test Market (OUT) ➔ Gudang Induk (IN)<br/>Stok fisik kembali utuh ke Gudang Induk"]
```

### Tahapan Mutasi & Pengaruh Stok:

| Tahapan | Lokasi Keluar (OUT) | Lokasi Masuk (IN) | Keterangan / Ledger Ref Type | Dampak Finansial / Jurnal |
| :--- | :--- | :--- | :--- | :--- |
| **1. Simpan SO (Draft)** | - | Gudang Induk | `SALES_ORDER` (`RESERVE`) | Belum ada jurnal (hanya reservasi stok) |
| **2. Rekam SO (Open / Jadi)** | **Gudang Induk** | **Gudang Test Market** | `MUTASI TEST MARKET` | Mutasi antar gudang internal Karisma |
| **3. Terbit Faktur (TINV...)** | **Gudang Test Market** | **Gdg. Test Market di Kios** | `FAKTUR PENJUALAN` $\rightarrow$ `TEST MARKET TITIP` | Jurnal penjualan & HPP dipending |
| **4. Pembayaran Kios** | **Gdg. Test Market di Kios** | - | `TEST MARKET TERJUAL` | Pengakuan Penjualan (`SJ`) & HPP (`GI`) |
| **5. Cancel SO (Batal)** | **Gudang Test Market** | **Gudang Induk** | `MUTASI TEST MARKET BATAL` | Stok fisik dikembalikan utuh ke Gudang Induk |

---

## 4. Alur Operasional Pengguna (Step-by-Step)

### Langkah 1: Input Sales Order (Sales)
1. Buka menu **Sales Order $\rightarrow$ Buat Sales Order Baru**.
2. Pilih Customer dan Gudang (default **Gdg. Induk**).
3. Beri tanda centang pada checkbox:
   `[✓] Barang Test Market (Faktur T)`
4. Simpan SO (status awal: `Draft`).

### Langkah 2: Rekam Sales Order (Sales / Admin)
1. Buka halaman detail SO.
2. Klik tombol **Rekam SO**.
3. Sistem secara otomatis:
   - Mengubah status SO dari `Draft` menjadi `Open`.
   - Menjalankan mutasi stok otomatis dari **Gdg. Induk** ke **Gudang Test Market** (mengurangi stok fisik & reserved di Gudang Induk, dan menambah stok fisik & reserved di Gudang Test Market).
   - Memperbarui entitas gudang SO menjadi **Gudang Test Market**.

### Langkah 3: Verifikasi Logistik & Cetak Faktur (Logistik / Admin SC)
1. SO diproses verifikasi rute dan loading seperti biasa.
2. Masuk ke menu **Admin SC $\rightarrow$ SO Siap Faktur** (atau Pilih Barang Faktur).
3. Saat membuka form faktur, sistem mendeteksi penanda Test Market dan memberikan nomor faktur awalan **`T`** (`TINVddmmyyxxxx`).
4. Saat Faktur disimpan:
   - Stok fisik & reserved berkurang dari **Gudang Test Market**.
   - Stok virtual otomatis dicatat masuk ke **Gdg. Test Market di Kios**.
   - Data posisi titipan tercatat di `tb_test_market_position`.
   - Jurnal piutang dan penjualan **ditunda/dipending** sampai kios membayar.

### Langkah 4: Realisasi Pembayaran di Keuangan (Kasir / Keuangan)
1. Saat kios membayar/membeli barang test market, buka halaman pembayaran di `/keuangan/pembayaran/bayar/{id_faktur}`.
2. Input qty yang dibeli oleh kios.
3. Sistem otomatis:
   - Mengurangi stok virtual di **Gdg. Test Market di Kios**.
   - Memperbarui status posisi di kios (`SEBAGIAN` atau `HABIS`).
   - Menerbitkan **Jurnal Penjualan** (`SALES_INVOICE`) dan **Jurnal HPP & Persediaan** (`GOODS_ISSUE`).
   - Menerbitkan nomor faktur realisasi mandiri (`TINV...-1`, `TINV...-2`) pada **Admin Transaksi** tab **Faktur Test Market**.

---

## 5. File & Modul Terkait
1. `database/migrations/20261028_add_gudang_test_market.sql` (Master Gudang Test Market).
2. `application/models/M_TestMarket.php`:
   - `get_transit_warehouse_id()`: Pengelolaan ID Gudang Test Market.
   - `transfer_so_to_test_market()`: Eksekutor mutasi stok saat SO direkam.
   - `reverse_transfer_so_test_market()`: Rollback mutasi stok jika SO dibatalkan.
   - `register_invoice()`: Perpindahan stok ke Gdg. Test Market di Kios saat Faktur T terbit.
   - `realize_payment()`: Pengurangan stok virtual saat pembayaran di Keuangan.
3. `application/models/M_SalesOrder.php`:
   - `rekam_so()`: Trigger mutasi otomatis dari Gudang Induk ke Gudang Test Market saat SO Open.
   - `update_status()`: Trigger rollback stok ke Gudang Induk saat SO dibatalkan (`cancelled`).
   - `buat_faktur()`: Pemotongan stok dari Gudang Test Market dan delegasi ke `register_invoice()`.
   - `get_so()`: Join ke `tb_gudang` untuk menampilkan nama gudang secara jelas.
4. `application/controllers/keuangan/C_Laporan.php`:
   - `kartu_stock_gudang_data()`: Sinkronisasi arus mutasi SO Test Market (`IN` ke Gudang Test Market dan `OUT` dari Gudang Induk) agar kartu stok per gudang tetap seimbang (*balance*).
5. `application/views/content/sales/so_form.php` (Checkbox Test Market).
6. `application/views/content/sales/so_detail.php` (Tampilan nama gudang dan badge Test Market).
7. `application/views/content/keuangan/laporan/laporan_barang/kartu_stock_gudang.php` (Laporan Kartu Stok Per Gudang).

