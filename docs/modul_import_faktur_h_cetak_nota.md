# Dokumentasi Modul Import Faktur H (Khusus Kebutuhan Cetak Nota LX-310)

## 1. Latar Belakang & Tujuan
Pada sistem Karisma ERP, pengguna memiliki arsip data Faktur H historis (misalnya dari bulan Januari sampai September) dalam bentuk file Excel yang perlu dicetak ulang ke nota fisik printer continuous form / dot-matrix Epson LX-310 (ukuran kertas 21 cm x 14 cm) secara cepat.

Agar proses ini **100% aman dan tidak menimbulkan efek samping** pada operasional ERP yang sedang berjalan, modul ini mengimplementasikan konsep **Isolasi Data Penuh**:
- Menggunakan 2 tabel khusus baru: `tbso_faktur_h_import` dan `tbso_faktur_h_import_detail`.
- **TIDAK mempengaruhi** master customer acak (`tb_customer_acak`), customer master (`tb_customer`), stok gudang (`tb_stock`), piutang pelanggan, maupun jurnal akuntansi (`tb_jurnal`).
- Data terpisah secara fisik dari Faktur H pecahan yang dihasilkan melalui sistem pemecahan reguler (`tbso_faktur_z_pecah`).
- **Memiliki halaman khusus (dedicated page)** yang dapat diakses langsung dari **Sidebar**.

---

## 2. Navigasi Sidebar & Halaman Khusus Baru

1. **Akses Sidebar**:
   - Menu **Faktur H Import** telah ditambahkan di sidebar menu navigasi utama (di bawah menu *Pecah Faktur*).
   - Ditampilkan untuk seluruh role pengguna terkait (`admkeu`, `KIUKEU`, level admin, dan sales management).
2. **URL Halaman Khusus**:
   - `https://karismaerp.test/sales_order/faktur_h_import`
   - `https://karismaerp.test/faktur_h_import`
3. **Fitur di Halaman Khusus**:
   - Ringkasan 4 Kartu Info Box: Total Faktur Terimport, Total Kuantitas Barang (pcs), Total Jenis Item, dan Total Nilai Penjualan (Rp).
   - Panel Filter Periode Tanggal Faktur & Kotak Pencarian Instan.
   - Tombol **Import Excel Faktur H** (upload file Excel `.xlsx` / `.xls`).
   - Tombol **Download Template Excel (.xlsx)**.
   - Tombol **Pilih Semua** & **Hapus Terpilih**.
   - Tombol **Cetak Faktur (LX-310)** untuk cetak satuan maupun cetak massal (batch continuous form).
   - Modal AJAX Rincian Barang Faktur untuk pratinjau cepat tanpa reload halaman.

---

## 3. Struktur Tabel Database Baru

### A. Tabel Header: `tbso_faktur_h_import`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_import_h` | INT (PK, Auto Increment) | ID unik faktur import |
| `no_faktur` | VARCHAR(50) | Nomor Faktur H (contoh: `H2601001`) |
| `parent_no_faktur` | VARCHAR(50) | Nomor Faktur Z Induk jika ada |
| `no_so` | VARCHAR(50) | Nomor SO jika ada |
| `kd_customer` | VARCHAR(50) | Kode customer jika ada |
| `customer_name` | VARCHAR(255) | Nama customer / toko |
| `kontak_person` | VARCHAR(255) | Nama pemilik / penerima |
| `nama_toko` | VARCHAR(255) | Nama toko |
| `alamat` | TEXT | Alamat pengantaran |
| `kota` | VARCHAR(100) | Kota / regional pengiriman |
| `kd_rute` | VARCHAR(50) | Kode rute pengantaran (default: `SBY`) |
| `tanggal_faktur` | DATE | Tanggal terbit faktur |
| `tanggal_jatuh_tempo`| DATE | Tanggal jatuh tempo |
| `tempo` | INT | Tempo pembayaran (default: 30 hari) |
| `salesman` | VARCHAR(100) | Nama salesman |
| `catatan` | TEXT | Catatan / keterangan faktur |
| `total_item` | INT | Total jenis item barang |
| `total_qty` | DECIMAL(15,3) | Total kuantitas barang |
| `total_tonase` | DECIMAL(12,4) | Estimasi berat faktur (ton) |
| `total_kubikasi` | DECIMAL(12,6) | Estimasi kubikasi faktur |
| `grand_total` | DECIMAL(18,2) | Total nilai faktur (Rp) |
| `status` | VARCHAR(20) | Status data (`imported`) |
| `import_batch` | VARCHAR(50) | Kode batch import |
| `create_by` | VARCHAR(100) | Pengguna pembuat / pengimpor |
| `create_at` | DATETIME | Waktu import dilakukan |

### B. Tabel Detail Barang: `tbso_faktur_h_import_detail`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, Auto Increment) | ID unik baris rincian |
| `id_import_h` | INT (FK) | Relasi ke `tbso_faktur_h_import.id_import_h` |
| `no_faktur` | VARCHAR(50) | Nomor Faktur H |
| `kd_barang` | VARCHAR(50) | Kode barang jika ada |
| `nama_barang` | VARCHAR(255) | Nama barang |
| `no_lot` | VARCHAR(50) | Nomor lot / batch produksi |
| `expired_date` | DATE | Tanggal kadaluarsa barang |
| `qty` | DECIMAL(15,3) | Total qty barang |
| `qty_box` | DECIMAL(15,3) | Qty dalam dus / karton |
| `qty_pcs` | DECIMAL(15,3) | Qty sisa dalam pcs |
| `isi_per_box` | INT | Isi barang per box |
| `satuan` | VARCHAR(20) | Satuan barang (e.g. `BTL`, `PCS`, `DRUM`) |
| `hrg_satuan` | DECIMAL(18,2) | Harga jual satuan |
| `disc` | DECIMAL(5,2) | Diskon persentase (%) |
| `pajak` | DECIMAL(5,2) | Pajak persentase (%) |
| `subtotal_before_disc` | DECIMAL(15,2) | Subtotal kotor |
| `subtotal_after_disc` | DECIMAL(15,2) | Subtotal setelah diskon |
| `total_harga` | DECIMAL(18,2) | Total harga baris barang |
| `berat_gram` | DECIMAL(12,4) | Berat barang (gram) |

---

## 4. Panduan Penggunaan

1. **Buka Halaman dari Sidebar**:
   - Klik menu **Faktur H Import** pada sidebar sebelah kiri, atau buka URL:  
     `https://karismaerp.test/sales_order/faktur_h_import`.
2. **Unduh Template**:
   - Klik tombol **Download Template Excel (.xlsx)**.
3. **Isi Data Excel**:
   - Masukkan daftar faktur H historis Anda (bulan Januari - September atau periode lainnya).
   - Satu nomor faktur dapat memiliki lebih dari satu baris item barang (multi-item).
4. **Unggah File**:
   - Klik tombol **Import Excel Faktur H**, pilih file template yang telah diisi, lalu klik **Proses Import**.
5. **Cetak Nota Dot-Matrix Epson LX-310 (21 cm x 14 cm)**:
   - **Cetak 1 Faktur**: Klik tombol **Cetak** pada baris faktur.
   - **Cetak Banyak Faktur Sekaligus (Batch)**: Centang kotak pada faktur yang ingin dicetak, lalu klik tombol **Cetak Terpilih (x Faktur)**.
   - Pada jendela print browser:
     - Printer: **Epson LX-310** (atau driver dot-matrix sejenis).
     - Layout: **Landscape**.
     - Paper Size: **Custom (21x14 cm / 8.5x5.5")**.

---

## 5. Catatan Pemeliharaan Layout UI

- **Sidebar Default State**: Halaman menggunakan kelas layout `<body class="hold-transition sidebar-mini sidebar-collapse">` agar sidebar dalam posisi tertutup / collapse secara default saat dibuka, memaksimalkan lebar area kerja tabel data faktur.
- **PushMenu Toggle Handler**: Dilengkapi delegated event listener responsif pada tombol `[data-widget="pushmenu"]` dan `.karisma-topbar-toggle` untuk memastikan sidebar dapat dibuka dan ditutup (toggle) secara mulus di seluruh resolusi layar (desktop maupun tablet/mobile).
