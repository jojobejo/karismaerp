# Dokumentasi Fitur: Pengelolaan 3 Gudang Konsinyasi & Alur Pelunasan

## Ringkasan Perubahan
Modul barang konsinyasi pada Karisma ERP diperbarui untuk memfasilitasi pelacakan (*tracking*) mutasi persediaan secara komprehensif mulai dari penerimaan barang, penitipan ke kios rekanan, hingga barang laku dan dilunasi ke pihak supplier.

Tautan Halaman Terkait:
- **Tracking & Penyelesaian Konsinyasi**: `/purchasing/konsinyasi`
- **Kartu Stok Per Gudang**: `/laporan/barang/kartu-stock-gudang`

---

## 1. Konsep & Struktur 3 Gudang Konsinyasi

| No | Nama Gudang | ID Default | Keterangan & Peran Alur |
|---|---|---|---|
| 1 | **Gudang Konsinyasi** | `13` | Menampung stok fisik titipan dari supplier saat barang pertama kali tiba melalui LPB (Laporan Penerimaan Barang). Barang belum diakui sebagai hutang dagang. |
| 2 | **Gudang Titipan di Kios** | `14` | Menampung barang saat difakturkan dan dikirim ke kios rekanan. Status kepemilikan masih titipan (kios belum membeli dan kita belum melunasi supplier). |
| 3 | **Gudang Konsinyasi Lunas** | `15` | Menampung barang konsinyasi yang telah dibeli kios dan telah **dilunasi** ke pihak supplier (terbit invoice supplier & voucher jurnal pembelian resmi). |

---

## 2. Alur Mutasi Barang & Kartu Stok Gudang

### Tahap 1: Penerimaan Barang dari Supplier (LPB)
- **Aksi**: Logistik menginput penerimaan barang konsinyasi via modul LPB ke Gudang Konsinyasi (`gudang_id = 13`).
- **Kartu Stok Gudang Konsinyasi**:
  - Mutasi: **IN** (Masuk)
  - Referensi: `PJ [Nomor LPB]`
  - Saldo Gudang Konsinyasi bertambah.

### Tahap 2: Pengiriman / Penjualan ke Kios (Faktur Penjualan K / KINV)
- **Aksi**: Penjualan diterbitkan sampai menjadi Faktur Penjualan (`tbso_faktur_penjualan`) bersumber dari Gudang Konsinyasi dengan awalan nomor faktur **`K`** (contoh: `KINVddmmyyxxxx`).
- **Kartu Stok**:
  - **Gudang Konsinyasi**: Mutasi **OUT** (Keluar), Referensi `SJ [Nomor Faktur]`.
  - **Gudang Titipan di Kios**: Mutasi **IN** (Masuk), Referensi `TITIP-IN [Nomor Faktur] (Nama Kios)`.
  - Status di sistem konsinyasi tercatat sebagai `DI_KIOS` / `PENDING`.

### Tahap 3: Kios Membeli Barang (Barang Laku)
- **Aksi**: Kios melunasi/membayar transaksi penjualannya di bagian Keuangan (`keuangan/pembayaran/bayar/{id_faktur}`).
- **Status Konsinyasi**:
  - Status berpindah dari `DI_KIOS` menjadi **`LAKU`**.
  - Nilai Qty muncul pada kolom **Barang Laku (Siap Dilunasi)** di halaman purchasing konsinyasi.
- **Kartu Stok Gudang**:
  - **Gudang Titipan di Kios (ID 14)**: Mutasi **OUT** (Keluar), Referensi `TITIP-OUT [No Faktur Konsinyasi] ([Nama Kios])`. Stok fisik titipan di kios berkurang karena telah dibeli oleh kios.

### Tahap 4: Pelunasan ke Supplier (Tombol "Lunasi" & Posting Jurnal)
- **Aksi**: User purchasing membuka tab Penyelesaian Konsinyasi, lalu mengklik tombol **"Lunasi"** pada baris barang laku.
- **Input Data**:
  - Nomor Faktur / Invoice Supplier
  - Tanggal Invoice Supplier
  - Konfirmasi harga beli satuan (terkunci sesuai PO konsinyasi) & tipe pajak.
- **Dampak Sistem**:
  1. Status settlement diupdate menjadi **`BILLED`**.
  2. Jurnal Pembelian otomatis diterbitkan:
     - `[Debit]` Persediaan Barang Konsinyasi (DPP)
     - `[Debit]` PPN Masukan
     - `[Kredit]` Hutang Usaha (Supplier)
  3. **Kartu Stok Gudang**:
     - **Gudang Konsinyasi Lunas (ID 15)**:
       - Mutasi **IN** (Masuk): Referensi `LUNAS-IN [No Settlement] ([Nama Supplier] - INV: [No Invoice])` (pengakuan pembelian resmi dari supplier).
       - Mutasi **OUT** (Keluar): Referensi `LUNAS-OUT [No Faktur] (Penjualan ke [Nama Kios] - Ref: [No Settlement])` (pengakuan penyerahan/penjualan ke kios).
       - **Saldo Akhir Gudang Konsinyasi Lunas = 0**, karena barang fisik telah laku terjual ke pihak kios dan tidak tersisa di gudang perusahaan.
  4. Di halaman konsinyasi (`/purchasing/konsinyasi`), stok tersebut kini tampil pada kolom **4. Konsinyasi Lunas** (sebagai jejak total riwayat yang telah dilunasi).

---

## 3. Rincian Pembaruan File Source Code

1. **`application/models/M_Konsinyasi.php`**:
   - `ensure_schema()`: Menjamin master gudang `Gdg. Titipan di Kios` dan `Gdg. Konsinyasi Lunas` selalu dibuat jika belum tersedia di tabel `tb_gudang`.
   - `get_warehouse_konsinyasi_id()`, `get_warehouse_titipan_kios_id()`, `get_warehouse_konsinyasi_lunas_id()`: Helper untuk mengambil ID ketiga gudang secara dinamis.
   - `get_tracking_summary()`: Menambahkan perhitungan kartu ringkasan untuk `total_lunas`, `item_lunas`, dan `total_nominal_lunas`.
   - `get_tracking_barang_list()`: Menambahkan subquery kolom `stok_lunas` (`status = 'BILLED'`) dan memperbarui perhitungan `total_stok`.
   - `process_settlement()`: Menambahkan pencatatan/pembaruan batch persediaan ke Gudang Konsinyasi Lunas di tabel `tberp_stock_batch`.

2. **`application/controllers/keuangan/C_Laporan.php`**:
   - `kartu_stock_gudang_data()`:
     - Mendeteksi ID ketiga gudang konsinyasi.
     - Mengintegrasikan transaksi IN/OUT untuk Gudang Titipan di Kios (IN saat Faktur Penjualan, OUT saat Pelunasan Konsinyasi).
     - Mengintegrasikan transaksi IN untuk Gudang Konsinyasi Lunas saat Pelunasan Konsinyasi.

3. **`application/views/content/purchasing/konsinyasi/settlement_list.php`**:
   - Mengubah 3 Summary Cards menjadi 4 Summary Cards di bagian atas halaman (1. Gdg. Konsinyasi, 2. Titipan di Kios, 3. Barang Laku, 4. Konsinyasi Lunas).
   - Tabel inventaris tracking posisi barang berfokus pada stok aktif: **1. Gdg. Konsinyasi**, **2. Titipan di Kios**, dan **3. Barang Laku**, dengan total stok aktif.
   - Mengubah seluruh label dan tombol aksi **"Input Tagihan"** / **"Tagihan"** menjadi **"Lunasi"** (`<i class="fas fa-check-circle"></i> Lunasi`).
   - Memperbarui judul modal pelunasan menjadi **"Pelunasan Barang Konsinyasi ke Supplier"** dan tombol submit menjadi **"Lunasi & Posting Jurnal Pembelian"**.
   - Memperbarui teks opsi klik kanan baris tabel menjadi **"Lunasi Tagihan Supplier"**.
   - **Modal Rincian Kios**:
     - Menggabungkan baris pengiriman per faktur dan nomor lot agar tidak memecah baris ke bawah saat terjadi pembelian parsial oleh kios.
     - Menambahkan kolom **Qty Laku** di samping kolom **Qty di Kios**.
     - Menghilangkan kolom **Aksi** pada modal Rincian Kios (proses pelunasan difokuskan di tab Barang Laku).
     - Menambahkan baris total di bagian bawah tabel modal (`<tfoot>`).
