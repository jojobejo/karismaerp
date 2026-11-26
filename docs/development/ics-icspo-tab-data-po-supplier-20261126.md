# Dokumentasi Development: Tab Data PO Supplier pada Modul LPB (`ics/icspo`)

## 1. Ringkasan Fitur
Menambahkan tab baru **"Data PO Supplier"** pada modul LPB (`ics/icspo` dan `ics/data_lpb`) untuk user Logistik dan Admin (mengikuti visibilitas tab **Data PO**). Tab ini menyajikan rincian barang dari Purchase Order (PO) berstatus `DONE` yang difilter berdasarkan pilihan **Supplier** menggunakan komponen pencarian **Select2** secara dinamis via **AJAX** tanpa reload halaman.

## 2. File yang Dimodifikasi
- **Routing**: `application/config/routes.php`
  - Menambahkan route `ics/ajax_get_po_supplier_items` -> `logistik/C_Ics/ajax_get_po_supplier_items`.
- **Controller**: `application/controllers/logistik/C_Ics.php`
  - Memuat daftar supplier PO (`$data['po_suppliers']`) pada `ics_po()` dan `data_lpb()`.
  - Menambahkan endpoint AJAX `ajax_get_po_supplier_items()` untuk mengambil rincian barang PO per supplier beserta ringkasan total PO, total item, total qty order, dan total qty diterima.
- **Model**: `application/models/M_Logistik.php`
  - `get_po_suppliers_for_lpb()`: Mengambil daftar supplier yang memiliki PO berstatus `DONE` beserta jumlah PO-nya.
  - `get_po_items_by_supplier($kd_suplier, $date1, $date2)`: Mengambil detail barang PO (`tbpo_po` & `tbpo_detail_po`) yang digabungkan dengan rekap penerimaan barang (`tb_lpb` & `tb_lpb_detail`) per `kd_po` dan `kd_barang`.
- **View**: `application/views/content/logistik/ics/icspo.php`
  - Menambahkan navigasi tab **Data PO Supplier** (`#po-supplier-panel`).
  - Menambahkan dropdown filter supplier berbasis **Select2** (`#filterPoSupplierSelect`), tombol **Muat Data** & **Reset**, badge ringkasan, dan tombol filter status (**Semua**, **Belum**, **Partial**, **Done**).
  - Menampilkan DataTable `#idtb_ics_po_supplier` dengan kolom:
    1. **No PO**
    2. **Tanggal Transaksi**
    3. **Nama Barang** (beserta kode barang)
    4. **Qty Barang** (satuan kecil/PCS)
    5. **Qty Barang Diterima** (satuan kecil/PCS)
    6. **Progress** (progress bar persentase & rasio `qty_diterima / qty_barang`)
    7. **Status Input Terakhir** (timestamp waktu input LPB terakhir barang tersebut)
    8. **Status** (`Belum`, `Partial`, `Done`)
    9. **Aksi (`#`)** (Tombol **Detail** menuju `ics/detail_po`)

## 3. Skema Database & Migrasi
- **Tidak ada perubahan struktur tabel (DDL/migrasi)**.
- Query memanfaatkan tabel eksisting:
  - `tbpo_po` (header PO)
  - `tbpo_detail_po` (rincian barang PO)
  - `tbpo_suplier` (master supplier)
  - `tbpo_barang` (master barang PO)
  - `tb_lpb` & `tb_lpb_detail` (data penerimaan barang LPB)

## 4. Cara Penggunaan
1. Buka halaman **Data LPB** melalui menu atau URL `ics/icspo` (atau `ics/data_lpb`).
2. Klik tab **Data PO Supplier**.
3. Pada dropdown **Pilih Supplier (PO)**, ketik nama atau kode supplier menggunakan pencarian Select2.
4. Tabel otomatis memuat daftar barang PO dari supplier tersebut via AJAX.
5. Gunakan tombol filter status (**Semua**, **Belum**, **Partial**, **Done**) atau kotak pencarian DataTable untuk menyaring barang tertentu.
6. Klik tombol **Detail** pada baris barang untuk membuka halaman **Detail Penerimaan PO** (`ics/detail_po`).
