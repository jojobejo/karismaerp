# Dokumentasi Fitur: Kartu Stok Barang Konsinyasi (Metode FIFO)

## Ringkasan Fitur
Fitur **Kartu Stok Barang Konsinyasi (FIFO)** dibuat secara terpisah dari Kartu Stok Umum Gudang (Moving Average) untuk mengakomodasi kebutuhan pencatatan mutasi dan Harga Pokok Penjualan (HPP) barang konsinyasi berbasis **FIFO riil per Lot/Batch LPB**.

Tautan Halaman:
- **Menu Navigasi**: `Laporan` $\rightarrow$ `Laporan Barang` $\rightarrow$ `Kartu Stok Barang Konsinyasi`
- **URL Laporan**: `/laporan/barang/kartu-stock-konsinyasi`
- **URL Data Endpoint**: `/laporan/barang/kartu-stock-konsinyasi-data`

---

## 1. Latar Belakang & Perbedaan dengan Kartu Stok Umum

| Parameter | Kartu Stok Gudang Umum | Kartu Stok Barang Konsinyasi |
|---|---|---|
| **Metode HPP** | Moving Average (Rata-Rata Bergerak) | **FIFO (First-In, First-Out)** per Lot/Batch LPB |
| **Kepemilikan Persediaan** | Milik sendiri sejak LPB masuk | Bertahap: Titipan Supplier $\rightarrow$ Titip Kios $\rightarrow$ Beli/Laku $\rightarrow$ Lunas |
| **Identitas Lot/Batch** | Agregat per tanggal/faktur | Menampilkan **No. Lot, No. LPB Asal, dan Expired Date** |
| **Pihak Rekanan** | Umum | Spesifik: **Supplier Konsinyasi** dan **Nama Kios** |
| **Sinkronisasi Jurnal** | Rata-rata persediaan | Sinkron 100% dengan Jurnal HPP Penjualan Konsinyasi (`GOODS_ISSUE`) |

---

## 2. Struktur Alur 3 Gudang Konsinyasi pada Kartu Stok FIFO

```
1. GUDANG KONSINYASI (ID 13)
   ├─ IN : [LPB-IN] Penerimaan barang titipan dari Supplier (Harga Beli PO Konsinyasi)
   └─ OUT: [TITIP-OUT] Pengiriman barang titipan ke Kios via Faktur Penjualan (KINV)

2. GUDANG TITIPAN DI KIOS (ID 14)
   ├─ IN : [TITIP-IN] Barang titipan tiba di Kios
   └─ OUT: [LAKU-OUT] Kios membayar/membeli barang konsinyasi di Keuangan

3. GUDANG KONSINYASI LUNAS (ID 15)
   ├─ IN : [LUNAS-IN] Purchasing melunasi ke Supplier (Terbit Jurnal Pembelian)
   └─ OUT: [LUNAS-OUT] Pengeluaran persediaan karena barang fisik telah terjual ke Kios
```

---

## 3. Fitur & Filter Laporan

1. **Filter Produk Konsinyasi**: Pilihan dropdown seluruh barang yang pernah tercatat dalam transaksi konsinyasi.
2. **Filter Supplier Konsinyasi**: Pilihan dropdown supplier konsinyasi (misal: Pupuk Karya Polowijo, PT).
3. **Filter Posisi Gudang**:
   - *Semua Gudang Konsinyasi*
   - *Gdg. Konsinyasi (ID 13)*
   - *Gdg. Titipan di Kios (ID 14)*
   - *Gdg. Konsinyasi Lunas (ID 15)*
4. **Filter Periode**: Dari Tanggal s/d Sampai Tanggal.
5. **Kolom Informasi**:
   - Tanggal & Referensi
   - No. Lot / Batch & No. LPB Asal
   - Supplier / Kios Rekanan
   - Mutasi Masuk (Qty, Harga FIFO, Nilai Rp)
   - Mutasi Keluar (Qty, Harga FIFO, Nilai Rp)
   - Saldo Berjalan (Qty, Nilai Saldo Rp)
   - Badge Status Alur (`LPB`, `Titip`, `Laku`, `Lunas`)
6. **Ekspor & Cetak**: Dilengkapi tombol Print yang terformat rapi (*print stylesheet*).

---

## 4. Rincian Pembaruan Kode Program

1. **`application/views/content/keuangan/laporan/laporan_barang/index.php`**:
   - Menambahkan menu baru `Kartu Stok Barang Konsinyasi` dengan icon `fas fa-handshake`.
2. **`application/config/routes.php`**:
   - Mendaftarkan rute `laporan/barang/kartu-stock-konsinyasi` dan `laporan/barang/kartu-stock-konsinyasi-data`.
3. **`application/controllers/keuangan/C_Laporan.php`**:
   - Menambahkan method `kartu_stock_konsinyasi_report()` untuk memuat form filter dan master data.
   - Menambahkan method `kartu_stock_konsinyasi_data()` untuk kalkulasi mutasi FIFO dari 3 gudang konsinyasi.
4. **`application/views/content/keuangan/laporan/laporan_barang/kartu_stock_konsinyasi.php`**:
   - Tampilan visual laporan kartu stok konsinyasi modern, interaktif AJAX, dan siap cetak.
