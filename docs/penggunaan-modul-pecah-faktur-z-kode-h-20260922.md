# Dokumentasi Modul: Pecah Faktur Z & Pemisahan Tabel Faktur Pecahan (Kode H)

**Tanggal Pembaruan**: 22 September 2026  
**Modul**: Keuangan & Penjualan (Pecah Faktur Z)  
**Tautan Modul**: `https://karismaerp.test/sales_order/pecah_faktur`  
**Lokasi Menu**: Dashboard (`/dashboard`) -> Bagian **KEUANGAN**  

---

## 1. Latar Belakang & Konsep Bisnis

Dalam operasional bisnis KARISMA ERP, terdapat transaksi khusus **Faktur Z** di mana pengiriman barang dalam satu Sales Order (SO) dapat didistribusikan ke beberapa kios/customer yang berbeda.

### Ketentuan Akuntansi & Persediaan (Stock & Journal Isolation):
1. **Faktur Z Induk**:
   - Berpengaruh terhadap **pemotongan stok barang di gudang** (kartu stok & DO).
   - Berpengaruh terhadap **pencatatan jurnal keuangan/akuntansi** (piutang, penjualan, PPN).
   - Menggunakan kode faktur berawalan **`Z`** (misal: `ZINV26090001`).
   - Tersimpan di tabel utama sistem: `tbso_faktur_penjualan` dan `tbso_faktur_detail`.

2. **Faktur Pecahan (Turunan dari Faktur Z)**:
   - **TIDAK TERJURNAL** ke modul akuntansi (mencegah pencatatan omzet ganda).
   - **TIDAK MEMOTONG STOK ULANG** (mencegah pengurangan stok barang dua kali).
   - Murni bersifat **administratif distribusi** untuk tanda terima/tagihan fisik ke masing-masing customer/kios penerima.
   - Menggunakan kode faktur berawalan **`H`** (misal: `HINV26090001`).
   - Tersimpan di **TABEL TERPISAH**: `tbso_faktur_z_pecah` dan `tbso_faktur_z_pecah_detail`.

---

## 2. Struktur Database Baru

### A. Tabel Header: `tbso_faktur_z_pecah`
Menyimpan ringkasan faktur turunan berkode H:
- `id_pecah` (INT, Auto Increment, Primary Key)
- `no_faktur` (VARCHAR, Kode awalan H, contoh: `HINV26090001`)
- `parent_id_faktur` (INT, ID Faktur Z Induk pada `tbso_faktur_penjualan`)
- `parent_no_faktur` (VARCHAR, Nomor Faktur Z Induk)
- `id_so`, `no_so` (Relasi ke Sales Order)
- `kd_customer`, `customer_name` (Customer tujuan/kios penerima)
- `gudang_id`, `tanggal_faktur`, `tanggal_jatuh_tempo`, `salesman`, `cara_pembayaran`
- `status` (Default: `confirmed`)
- `create_by`, `create_at`, `update_by`, `update_at`

### B. Tabel Detail: `tbso_faktur_z_pecah_detail`
Menyimpan rincian barang, kuantitas, lot/batch, dan harga untuk faktur pecahan H:
- `id` (INT, Auto Increment, Primary Key)
- `id_pecah` (INT, Relasi ke `tbso_faktur_z_pecah.id_pecah`)
- `no_faktur` (VARCHAR, Nomor Faktur H)
- `parent_id_faktur`, `parent_no_faktur`
- `id_so_detail`, `kd_barang`, `nama_barang`, `no_lot`, `expired_date`
- `qty`, `qty_box`, `qty_satuan`, `isi_per_box`, `satuan`
- `hrg_satuan`, `hrg_pokok`, `disc`, `pajak`, `total_harga`
- `create_by`, `create_at`

---

## 3. Alur Penggunaan & Antarmuka Pengguna (UI)

### 1. Mengakses Modul
- Pengguna login ke sistem, lalu membuka **Dashboard** (`/dashboard`).
- Pada kategori menu **Keuangan**, klik tile **Pecah Faktur** (ikon gunting oranye).
- Pada sidebar, menu **Pecah Faktur** juga tersedia untuk role Keuangan (`ADMINKEU`, `KIUKEU`) dan role Sales (`SC`, `ADMINSC`).

### 2. Navigasi Bebas Ketergantungan Sales Order
- Pada halaman Pecah Faktur (`/sales_order/pecah_faktur`), tombol navigasi cepat ke halaman Sales Order **telah dihilangkan** sesuai permintaan. Pengguna hanya diarahkan kembali ke Dashboard atau Log Aktivitas.

### 3. Tampilan Tab Terpadu
- **Tab 1: Faktur Z Induk (Stok & Jurnal)**:
  - Menampilkan semua Faktur Z induk.
  - Menampilkan status pemecahan (*Belum Dipecah*, *Dipecah Sebagian*, *Selesai Dipecah*).
  - Terdapat tombol **Pecah** untuk memproses pemecahan faktur.
  - Link nomor turunan (berkode H) langsung mengarah ke detail rincian faktur pecahan.
- **Tab 2: Faktur Pecahan Kode H (Tabel Terpisah)**:
  - Menampilkan seluruh faktur pecahan berkode awalan `H`.
  - Dilengkapi fitur pencarian dan tombol **Detail** untuk melihat dokumen dan mencetak faktur pecahan.

### 4. Proses Pemecahan Tunggal Faktur Z
1. Klik tombol **Pecah** pada Faktur Z yang ingin dipecah.
2. Tentukan customer penerima pecahan serta alokasi kuantitas barang per batch/lot.
3. Sistem memvalidasi agar jumlah kuantitas pecahan tidak melebihi sisa kuantitas Faktur Z induk.
4. Klik **Simpan Pecahan Faktur**.
5. Sistem secara otomatis membuat nomor faktur baru berawalan **`H`** (misal: `HINV...`) dan menyimpannya ke tabel `tbso_faktur_z_pecah`.
6. Sistem **TIDAK** memicu posting jurnal ataupun pengurangan kartu stok baru.

### 5. Fitur Multi-Select Pecah Faktur Z Sekaligus (Batch Split Berbasis QTY)
Fitur ini memungkinkan penggabungan beberapa Faktur Z sekaligus untuk dipecah ke sejumlah N faktur pecahan (Kode H) dengan ketentuan:
1. **Aturan Bisnis Utama (Pemecahan Berbasis QTY & Potongan 20% Faktur Z)**:
   - **Potongan 20% Faktur Z**: Setiap barang pada Faktur Z mendapatkan potongan sebesar **20%** dari harga dasarnya di Sales Order (misal dari harga semula Rp 1.000.000/pcs menjadi Rp 800.000/pcs, dan total nilai semula Rp 100.000.000 menjadi Rp 80.000.000).
   - **Pemecahan Berdasarkan QTY**: Pemecahan faktur dilakukan berdasarkan alokasi **kuantitas (Qty)** barang ke sejumlah Faktur Pecahan (Kode Awalan H), bukan didasarkan pada batasan nominal saldo/rupiah.
   - Contoh Kasus: Total barang sebanyak 7 pcs dipecah menjadi 4 faktur H, maka kuantitas didistribusikan secara seimbang ke 4 faktur (misal: 2 pcs, 2 pcs, 2 pcs, dan 1 pcs, atau sesuai penentuan Qty oleh pengguna).
2. **Centang Checkbox & Modal Konfirmasi QTY**:
   - Di tabel Faktur Z, centang satu atau beberapa Faktur Z yang ingin dipecah sekaligus.
   - Klik tombol **"Pecah Faktur Terpilih (X)"**.
   - Muncul modal popup interaktif yang menampilkan:
     - **Total Kuantitas (Qty)** dan **Total Nilai** faktur yang dipilih.
     - Form input: **"Mau dipecah menjadi berapa faktur?"**.
     - **Estimasi Rata-rata Qty & Nilai per Faktur Pecahan** yang otomatis terhitung saat angka jumlah pecahan dimasukkan.
3. **Halaman Builder Batch (Tata Letak Kompak Grid 3 Kolom)**:
   - **Tampilan Tabel Klasik ERP**: Menggunakan tata letak solid border rapat yang efisien dan padat data.
   - **Bagian Atas (Full Width)**: Rincian **Faktur Z Induk Dipilih** (No Faktur Z, Tanggal, No SO, Customer Asal, Total Qty, Grand Total).
   - **Bagian Tengah (Full Width)**: Rincian **Monitor Alokasi Stok Barang Item** (Nama Barang, Kode, Lot/Batch, Asal Faktur, Stok Asal, Total Dialokasikan, dan Sisa Belum Dibagi dengan indikator realtime).
   - **Panel Ringkasan & Aksi Cepat**:
     - Tombol **"Bagi Rata Qty Otomatis"**: Secara otomatis membagi rata kuantitas seluruh barang secara seimbang ke setiap slot pecahan aktif (misal 7 pcs dibagi ke 4 slot menjadi 2, 2, 2, 1 pcs).
     - Tombol **"Kosongkan Qty"**: Mengosongkan kuantitas ke 0 jika ingin melakukan alokasi manual.
   - **Bagian Bawah (Grid 3 Kolom per Baris)**: Slot-slot faktur pecahan tertata kompak dalam formasi 3 kartu per baris (`col-lg-4 col-md-6 col-12`) agar muat banyak dan nyaman dipantau.
4. **Validasi & Proteksi**:
   - Sistem memvalidasi agar total kuantitas barang yang dialokasikan tidak melebihi sisa stok barang Faktur Z induk yang tersedia.
   - Tombol **+ Tambah Slot**, **Hapus Slot**, **Buka Semua**, dan **Tutup Semua** rincian slot pecahan.
5. **Simpan & Terbitkan**: Klik tombol **"Simpan & Terbitkan Semua Faktur H"**. Sistem secara otomatis menghasilkan nomor faktur berawalan **`H`** (`HINV...`) untuk seluruh pecahan dan menyimpannya ke tabel terpisah `tbso_faktur_z_pecah` dan `tbso_faktur_z_pecah_detail` tanpa jurnal dan tanpa memotong stok ulang.

---

## 4. Validasi Teknis
- **Sintaks PHP**: Semua file model, controller, dan view lulus uji `php -l`.
- **Integritas DB**: Struktur tabel dibuat otomatis jika belum tersedia via migration helper `ensure_faktur_z_pecah_tables()`.

