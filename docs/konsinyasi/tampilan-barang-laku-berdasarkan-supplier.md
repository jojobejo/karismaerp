# Tampilan Pengelompokan Barang Konsinyasi Laku Berdasarkan Supplier

Dokumentasi ini menjelaskan pembaruan pada modul **Purchasing > Konsinyasi** pada tab **Penyelesaian Tagihan (Barang Laku)** (`purchasing/konsinyasi?tab=penyelesaian&status=LAKU`).

---

## 1. Latar Belakang & Kebutuhan

Sebelumnya, pada tab barang laku (`status=LAKU`), seluruh baris transaksi penjualan konsinyasi dari berbagai supplier ditampilkan langsung bercampur dalam satu tabel flat transaksi settlement.

Ketika terdapat banyak supplier dan masing-masing supplier memiliki beberapa barang yang laku, pengguna membutuhkan alur kerja yang lebih terstruktur:
1. **Melihat daftar supplier yang memiliki barang laku terlebih dahulu** (memanjang ke bawah per supplier).
2. Mengetahui ringkasan jumlah barang/transaksi laku, total kuantitas, dan estimasi tagihan pembelian untuk masing-masing supplier.
3. Saat salah satu supplier diklik atau tombol detail dibuka, sistem akan membuka rincian barang apa saja yang laku khusus untuk supplier tersebut (beserta opsi pelunasan, input nomor invoice supplier, dan cetak faktur konsinyasi).

---

## 2. Alur Penggunaan (User Flow)

### Langkah 1: Membuka Tab Barang Laku
- **URL**: `https://localhost/karismaerp/purchasing/konsinyasi?tab=penyelesaian&status=LAKU&kd_suplier=SEMUA`
- Tampilan menampilkan tabel **Daftar Supplier Pemilik Barang Konsinyasi Laku** dengan kolom:
  - **No.**
  - **Supplier Konsinyasi** (Nama Supplier & Kode Supplier)
  - **Jumlah Barang Laku** (Total transaksi & jenis barang)
  - **Daftar Barang Laku** (Daftar nama produk yang laku)
  - **Total Qty Laku** (Total kuantitas barang yang telah terjual)
  - **Estimasi Tagihan Beli** (Total perkiraan kewajiban hutang ke supplier)
  - **Periode Laku** (Rentang tanggal penjualan)
  - **Aksi**:
    - Tombol **Buka Detail** (membuka halaman detail di tab aktif)
    - Tombol **Buka di Tab Baru** (`target="_blank"`)

### Langkah 2: Mengklik Supplier
- Saat pengguna mengklik baris supplier atau tombol **Buka Detail**, halaman akan memuat rincian transaksi barang laku untuk supplier tersebut:
  - **URL**: `purchasing/konsinyasi?tab=penyelesaian&status=LAKU&kd_suplier=[KODE_SUPPLIER]`
- Di bagian atas tabel detail muncul banner navigasi:
  - Informasi nama supplier dan kode supplier yang sedang dibuka.
  - Tombol **← Kembali ke Daftar Supplier** untuk kembali ke daftar ringkasan semua supplier.
- Tabel menampilkan rincian barang laku yang telah diakumulasikan per produk & lot:
  - Pada tab **Barang Laku** (`status=LAKU`): Kolom aksi disederhanakan dan **hanya memuat tombol Lunasi** untuk langsung membuka form pelunasan tagihan supplier.
  - Pada tab **Riwayat Lunas** (`status=BILLED`): Kolom aksi memuat tombol riwayat lengkap seperti **Input/Edit Inv**, **Cetak Faktur**, **Detail Settlement**, dan **Lihat Jurnal Pembelian**.

---

## 3. Komponen Teknis yang Dimodifikasi

1. **Model**: [`M_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/models/M_Konsinyasi.php)
   - Method `get_laku_suppliers_summary($filters)`: Mengelompokkan transaksi settlement (`GROUP BY kd_suplier, nama_suplier`) dengan status `LAKU` / `PENDING`, menghitung total transaksi, total jenis barang, total kuantitas, estimasi tagihan, dan daftar barang.

2. **Controller**: [`C_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/controllers/purchasing/C_Konsinyasi.php)
   - Menambahkan pengiriman `$data['laku_suppliers']` ke view.
   - Mengambil `$data['selected_supplier_name']` untuk banner navigasi saat supplier tertentu dipilih.

3. **View**: [`settlement_list.php`](file:///c:/laragon/www/karismaerp/application/views/content/purchasing/konsinyasi/settlement_list.php)
   - Logika percabangan `$isTabLakuAllSuppliers = (($filters['status'] === 'LAKU' || $filters['status'] === 'PENDING') && $filters['kd_suplier'] === 'SEMUA')`:
     - Jika `true`: Menampilkan tabel ringkasan per supplier (memanjang ke bawah).
     - Jika `false`: Menampilkan banner supplier terpilih + tabel detail transaksi barang laku supplier tersebut.

---

## 4. Akumulasi Barang Laku & Integrasi FIFO LPB Penerimaan

Pada tampilan **Rincian Barang Laku Supplier** (`status=LAKU&kd_suplier=...`):
- Barang yang sama dengan nomor lot yang sama dan LPB penerimaan yang sama (misal `Round Up 486 SL 12 X 1 ltr`, `LPB: 2600001K`, `Lot: 1111`) **tidak lagi menampilkan baris terpisah** untuk setiap termin penjualan kios (misal 20 pcs lalu 20 pcs).
- Sistem secara otomatis mengelompokkan dan mengakumulasikan kuantitasnya menjadi **1 baris tunggal**:
  - **Qty Laku (Dibeli Kios)**: Menampilkan total akumulasi (misal $20 + 20 = 40.00\text{ PCS}$).
  - **Badge Informasi**: Menampilkan badge `2x Penjualan Laku` pada kolom No. Settlement.
  - **Pelunasan Sekali Klik**: Ketika tombol **Lunasi** ditekan, modal langsung menampilkan total kuantitas gabungan (40 PCS) dan harga beli LPB FIFO. Saat diposting, sistem melunasi seluruh transaksi di dalam grup tersebut (`process_settlement_batch`) dalam satu kali proses tanpa perlu input berulang.

### 5. Aturan FIFO Pelunasan Supplier (LPB Terlama Dilunasi Duluan)
1. **Prioritas LPB Terlama**:
   - Jika terdapat penerimaan barang yang berasal dari beberapa LPB berbeda (misal LPB Batch A sisa 10 pcs dan LPB Batch B sisa 100 pcs), lalu terjadi penjualan laku sebanyak 12 pcs:
   - Sistem membaca alokasi FIFO LPB aktual dari `tb_konsinyasi_cogs_allocation` dan memecah transaksi pelunasan menjadi 2 baris terpisah sesuai porsi LPB-nya:
     - Baris 1: LPB Batch A sebanyak 10 pcs @ harga beli Batch A.
     - Baris 2: LPB Batch B sebanyak 2 pcs @ harga beli Batch B.
2. **Urutan Tampilan FIFO**:
   - Baris tabel diurutkan dengan `COALESCE(s.id_lpb_asal, 999999999) ASC`, sehingga LPB yang masuk lebih awal (LPB Batch A) **selalu tampil paling atas** untuk diselesaikan terlebih dahulu oleh bagian Purchasing.
3. **Penyelarasan Otomatis (`sync_settlements_with_fifo_allocation`)**:
   - Setiap kali halaman Purchasing Konsinyasi dimuat atau sinkronisasi background berjalan, sistem otomatis menyelaraskan record settlement dengan alokasi FIFO LPB aktual di database, memastikan `id_lpb_asal`, `nomor_lpb_asal`, `no_lot`, `expired_date`, dan `hrg_beli_satuan` selalu sinkron dan konsisten dengan jurnal HPP akuntansi.

