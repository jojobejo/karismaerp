# Fitur Barang Test Market (Faktur T) — KARISMA ERP

## 1. Latar Belakang & Konsep Bisnis
Barang **Test Market** adalah barang dagangan milik Karisma sendiri (pembelian sudah diakui dan fisik barang bersumber dari **Gudang Induk**, bukan gudang titipan konsinyasi supplier) yang dititipkan ke kios untuk keperluan uji pasar (*market test*).

### Karakteristik & Perbedaan Utama:
| Parameter | Faktur Reguler (INV) | Faktur Konsinyasi (KINV) | Faktur Test Market (TINV) |
| :--- | :--- | :--- | :--- |
| **Sumber Fisik Barang** | Gudang Induk / Umum | Gudang Titipan Kios (15) | **Gudang Induk / Umum** |
| **Kepemilikan Barang** | Milik Karisma | Milik Supplier Konsinyasi | **Milik Karisma** |
| **Pengakuan Jurnal Penjualan** | Saat Faktur Terbit | Saat Pembayaran di Keuangan | **Saat Pembayaran di Keuangan** |
| **Pengakuan Jurnal HPP & Persediaan** | Saat Faktur Terbit | Saat Pembayaran di Keuangan (FIFO LPB Supplier) | **Saat Pembayaran di Keuangan (Harga Pokok Persediaan Karisma)** |
| **Pelunasan ke Supplier Konsinyasi** | Tidak Ada | Ada (Masuk ke antrean settlement Purchasing Konsinyasi) | **TIDAK ADA (Tidak masuk antrean settlement purchasing konsinyasi)** |
| **Awalan Nomor Faktur** | `[UserPrefix]INV...` | `KINVddmmyyxxxx` | **`TINVddmmyyxxxx`** |

---

## 2. Skema & Perubahan Database
Telah ditambahkan kolom flag `is_test_market` pada dua tabel utama:

1. **`tbso_sales_order`**:
   - Kolom: `is_test_market TINYINT(1) NOT NULL DEFAULT 0` (setelah `catatan`).
   - Berisi status penanda apakah Sales Order tersebut merupakan pesanan barang test market.

2. **`tbso_faktur_penjualan`**:
   - Kolom: `is_test_market TINYINT(1) NOT NULL DEFAULT 0` (setelah `cara_pembayaran`).
   - Berisi status penanda faktur test market sehingga saat pembayaran di Keuangan dikenali secara tepat.

Migrasi database tersedia pada `db/migrations/20261015_add_test_market_sales.sql`. Controller juga memeriksa keberadaan kedua kolom sebelum form SO atau faktur digunakan agar instalasi yang belum menjalankan migrasi tidak menghilangkan pilihan Test Market secara diam-diam.

### Tracking Titipan di Kios

Tracking operasional menggunakan gudang virtual terpisah bernama **Gdg. Test Market di Kios**. Gudang ini tidak digabung dengan **Gdg. Titipan di Kios** milik alur Faktur K karena Test Market merupakan persediaan milik Karisma dan tidak menimbulkan settlement atau hutang kepada supplier konsinyasi.

Migrasi `db/migrations/20261015_test_market_tracking.sql` menyediakan:

1. Master gudang virtual Test Market.
2. Tabel `tb_test_market_position` untuk saldo per faktur, kios, barang, lot, dan expired date.
3. Tabel `tb_test_market_realisasi` untuk barang dan qty yang dibeli kios pada setiap pembayaran.
4. Kolom referensi item dan qty realisasi pada `tbkeu_pembayaran_faktur`.

Alur stoknya adalah:

1. Saat Faktur T diterbitkan, stok keluar dari Gudang Induk dan masuk ke Gdg. Test Market di Kios.
2. Posisi awal dicatat sebagai `qty_titip` dan `qty_sisa`.
3. Saat pembayaran, petugas memilih barang dan mengisi qty yang dibeli kios.
4. Sistem mengurangi `qty_sisa`, menambah `qty_terjual`, dan mengeluarkan stok dari gudang virtual.
5. Jika pembayaran di-unpost, qty dan stok virtual dikembalikan ke posisi titipan.
6. Faktur T tidak pernah dimasukkan ke `tb_konsinyasi_settlement` atau alokasi FIFO konsinyasi supplier.

Halaman monitoring tersedia melalui menu **Test Market** di dashboard atau URL `/test-market`. Ringkasan utama hanya menampilkan jumlah di kios, jumlah dibeli kios, dan sisa di kios. Informasi operasional dipisahkan menjadi dua tab: **Barang Test Market di Kios** untuk rekap posisi per barang dan **Barang yang Sudah Dibeli Kios** untuk riwayat realisasi pembelian. Kedua tab menggunakan pagination server-side sebanyak 20 data per halaman dengan parameter halaman terpisah. Daftar posisi dikelompokkan berdasarkan barang serta menampilkan jumlah kios dan faktur. Nama barang dapat diklik untuk menuju halaman detail tersendiri. Halaman detail menampilkan ringkasan jumlah dititipkan, laku/dibeli, sisa dan nilai laku; posisi barang per kios; rincian faktur, lot, expired; serta riwayat transaksi barang yang sudah dibeli kios. Filter kios serta status dari halaman rekap ikut diterapkan pada halaman detail.

---

## 3. Alur Operasional (Step-by-Step)

```mermaid
graph TD
    A["1. Sales membuat SO<br/>Checklist: [✓] Barang Test Market"] --> B["2. Logistik / Admin SC Verifikasi &amp; Buat Faktur"]
    B --> C["3. Terbit Faktur Prefix 'T'<br/>(TINVddmmyyxxxx)"]
    C --> D["4. Stok Gudang Induk berkurang<br/>(Jurnal Penjualan DIPENDING)"]
    D --> E["5. Kios Membeli / Membayar di Keuangan<br/>(/keuangan/pembayaran/bayar/{id})"]
    E --> F["6. Terbit Jurnal SJ (Penjualan Test Market)<br/>&amp; Jurnal GI Persediaan / HPP"]
    E --> G["7. Bersih dari Modul Purchasing Konsinyasi<br/>(Tanpa pelunasan hutang supplier)"]
```

### Langkah 1: Input Sales Order
- Buka menu **Sales Order $\rightarrow$ Buat Sales Order Baru**.
- Pilih Gudang Induk (non-konsinyasi) dan Customer.
- Beri tanda centang pada checkbox:
  `[✓] Barang Test Market (Faktur T)`
  *(Stok fisik bersumber dari Gudang Induk. Penjualan & HPP diakui saat dibeli/dibayar kios via Faktur T).*
- Simpan SO. Nomor SO akan memiliki badge penanda `Test Market`.

### Langkah 2: Pembuatan Faktur (Admin SC)
- Masuk ke menu **Admin SC $\rightarrow$ SO Siap Faktur** (atau Pilih Barang Faktur).
- Saat membuka form faktur, sistem mendeteksi `is_test_market = 1` dan otomatis mengarahkan prefix nomor faktur menjadi **`T`** (`TINVddmmyyxxxx`).
- Pada form faktur terdapat notifikasi:
  *Faktur Barang Test Market (Kode Awalan T • Jurnal Penjualan diakui saat dibayar oleh kios).*
- Saat faktur disimpan:
  - Stok fisik di batch Gudang Induk berkurang.
  - Jurnal penjualan **ditunda/dipending** (tidak langsung posting ke general ledger).

### Langkah 3: Penerimaan Pembayaran (Keuangan)
- Saat kios membayar/membeli barang test market tersebut:
  Buka halaman pembayaran nota di `/keuangan/pembayaran/bayar/{id_faktur}`.
- Lakukan pembayaran sesuai nominal laku kios.
- Sistem secara otomatis menerbitkan:
  1. **Jurnal Penjualan (`SALES_INVOICE`)**:
     - *Keterangan*: `Penjualan Test Market: TINV... - [Nama Customer] (Realisasi Pembelian Kios)`
     - *Debit*: Piutang Usaha (13099)
     - *Kredit*: Penjualan Barang Dagang (41011/41010)
     - *Kredit*: PPN Keluaran (23011) jika barang kena pajak
  2. **Jurnal HPP & Persediaan (`GOODS_ISSUE`)**:
     - *Keterangan*: `Penyesuaian persediaan test market, untuk SJ...`
     - *Debit*: Harga Pokok Penjualan Test Market (51010)
     - *Kredit*: Persediaan Barang Dagang (Test Market) (14010)
  3. **Jurnal Pembayaran Kas/Bank (`RECEIPT`)**:
     - *Debit*: Kas / Bank
     - *Kredit*: Piutang Usaha
- **Penerbitan Faktur Realisasi Mandiri (`TINV...-1`, `TINV...-2`)**:
  - Saat pembayaran di Keuangan dicatat, sistem menerbitkan nomor faktur realisasi mandiri bertanda strip (contoh `TINV2010260001-1`).
  - Dokumen ini tersimpan secara mandiri dan dapat dilihat di halaman **Admin Transaksi** (`admin/transaksi`) pada tab **Faktur Test Market**.
  - Dilengkapi tombol cetak faktur penjualan resmi serta audit jurnal akuntansi (Jurnal Penjualan SJ dan Jurnal HPP GI).
- **Modul Purchasing Konsinyasi**:
  Faktur T sama sekali **tidak disinkronkan** ke tabel `tb_konsinyasi_settlement`, sehingga halaman pelunasan hutang supplier konsinyasi tetap bersih dari transaksi test market.

---

## 4. Integrasi Admin Transaksi Hub (`admin/transaksi`)
Tersedia tab khusus **Faktur Test Market** berdampingan dengan **Faktur Konsinyasi**:
- Menampilkan nomor faktur anak (`TINV...-1`), nomor faktur induk / SO, tanggal transaksi, kios/customer, status realisasi (`LAKU`), nominal bayar, status jurnal (`POSTED`), serta jurnal debit/kredit.
- Tombol **Detail** menampilkan rincian barang yang laku, lot, kuantitas, harga, dan multi-jurnal akuntansi terkait.
- Tombol **Cetak Faktur Realisasi** pada modal detail untuk mencetak nota penjualan test market resmi.
- Mendukung repost, unpost (rollback), dan delete transaksi secara sinkron dengan saldo stok virtual kios.

---

## 5. File-File Yang Dimodifikasi
1. `application/views/content/sales/so_form.php` (penambahan checkbox Test Market).
2. `application/views/content/sales/so_list.php` (badge visual Test Market pada tabel SO).
3. `application/views/content/sales/so_detail.php` (rincian jenis SO Test Market).
4. `application/views/content/sales/faktur_form.php` (notifikasi faktur Test Market prefix T).
5. `application/controllers/sales/C_SalesOrder.php` (handling parameter `is_test_market` pada create/update SO dan generate nomor faktur prefix T).
6. `application/models/M_SalesOrder.php` (persistensi header SO & faktur, serta penundaan jurnal otomatis untuk faktur T).
7. `application/models/M_Journal.php` (realisasi jurnal penjualan, pengakuan HPP dari harga pokok barang dagang induk, dan penerbitan faktur turunan T).
8. `application/controllers/keuangan/C_pembayaran.php` (penomoran otomatis termin `T...-1`, `T...-2` saat pembayaran dan dukungan cetak faktur test market).
9. `application/models/M_Konsinyasi.php` (penerbitan record faktur realisasi mandiri untuk Test Market dengan entitas Gudang Induk).
10. `application/models/M_TestMarket.php` (penambahan kolom `no_faktur_realisasi` pada `tb_test_market_realisasi`).
11. `application/models/admin/M_Transaksi.php` (subquery filter tab `faktur_test_market`, detail breakdown jurnal, unpost/repost/delete).
12. `application/views/content/admin/transaksi/index.php` (navigasi tab Faktur Test Market, styling badge, dan tombol Cetak Faktur Realisasi).

