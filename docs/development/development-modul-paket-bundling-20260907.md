# Technical Documentation: Modul Paket Bundling (Purchasing & Logistik)

**Tanggal:** 07 September 2026  
**Status:** Production Ready  
**Framework:** CodeIgniter 3 (CI3), PHP 7/8, MariaDB/MySQL  

---

## 1. Skema Database

File SQL Migrasi: `database/sql/create_bundling_management_system.sql`

Tabel yang dibuat:
1. `tberp_bundling_formula`: Header formula bundling (`id_formula`, `kd_paket`, `nama_paket`, `keterangan`, `status_aktif`, dll).
2. `tberp_bundling_formula_detail`: Detail komponen formula (`id_detail`, `id_formula`, `kd_barang`, `qty_per_paket`, dll).
3. `tberp_bundling_request`: Header transaksi request dari Purchasing (`id_request`, `no_request`, `tgl_request`, `target_selesai`, `kd_paket`, `qty_request`, `qty_realisasi`, `status_request`, dll).
4. `tberp_bundling_request_detail`: Breakdown kebutuhan bahan komponen (`id_req_detail`, `id_request`, `kd_barang`, `qty_per_paket`, `total_kebutuhan`, dll).
5. `tberp_bundling_assembly`: Header realisasi perakitan oleh Logistik (`id_assembly`, `no_assembly`, `id_request`, `kd_paket`, `qty_assembly`, `id_gudang`, `total_hpp`, `hpp_per_paket`, dll).
6. `tberp_bundling_assembly_detail`: Rincian komponen yang dikonsumsi dalam perakitan.
7. `tberp_bundling_disassembly`: Header pembongkaran paket fisik untuk eceran bebas (`id_disassembly`, `no_disassembly`, `kd_paket`, `qty_disassembly`, `id_gudang`, dll).
8. `tberp_bundling_disassembly_detail`: Rincian pengembalian komponen ke stok bebas.
9. Gudang ID 12 di `tb_gudang`: `Gdg. Bundling` (untuk segregasi fisik stok bundling).

---

## 2. Arsitektur Kode (MVC)

### Model: `application/models/M_Bundling.php`
Fungsi utama:
- `get_formula_list()`, `get_formula_by_id()`, `save_formula()`, `get_formula_by_paket()`.
- `get_request_list()`, `get_request_by_id()`, `get_request_details()`, `create_request()`.
- `mutate_materials()`: Mutasi stok bahan dari Gudang Induk (ID 2) ke Gudang Bundling (ID 12) menggunakan helper `M_PenyesuaianBarang`.
- `create_assembly()`:
  - Memotong stok komponen di `tberp_stock_batch` & `tberp_stock_ledger` (`tipe_transaksi: ASSEMBLY_PAKET`).
  - Menambah stok paket jadi di `tberp_stock_batch` & `tberp_stock_ledger` dengan batch/lot unik.
  - Memperbarui `qty_realisasi` dan `status_request` di `tberp_bundling_request` (`PROSES_SEBAGIAN` atau `SELESAI`).
- `create_disassembly()`:
  - Memotong stok paket jadi di `tberp_stock_batch` & `tberp_stock_ledger` (`tipe_transaksi: DISASSEMBLY_PAKET`).
  - Menambah kembali stok komponen eceran bebas di `tberp_stock_batch` & `tberp_stock_ledger`.
- `get_component_stock_summary()`: Mengambil posisi stok komponen di Gudang Induk dan Gudang Bundling secara realtime.

### Controllers:
1. `application/controllers/purchasing/C_BundlingRequest.php`:
   - Prefix URL: `purchasing/bundling/...`
   - Mengelola pembuatan formula dan permohonan request pembuatan paket.
2. `application/controllers/logistik/C_BundlingLogistik.php`:
   - Prefix URL: `logistik/bundling/...`
   - Mengelola monitoring request, mutasi bahan, eksekusi assembly, pembongkaran disassembly, serta histori transaksi.

### Views:
- **Purchasing:**
  - `application/views/content/purchasing/bundling/formula_list.php`
  - `application/views/content/purchasing/bundling/request_list.php`
  - `application/views/content/purchasing/bundling/request_create.php`
  - `application/views/content/purchasing/bundling/request_detail.php`
- **Logistik:**
  - `application/views/content/logistik/bundling/monitoring_list.php`
  - `application/views/content/logistik/bundling/monitoring_detail.php`
  - `application/views/content/logistik/bundling/mutasi_bahan.php`
  - `application/views/content/logistik/bundling/assembly_create.php`
  - `application/views/content/logistik/bundling/assembly_detail.php`
  - `application/views/content/logistik/bundling/disassembly_create.php`
  - `application/views/content/logistik/bundling/disassembly_detail.php`
  - `application/views/content/logistik/bundling/history_list.php`

### Routing (`application/config/routes.php`):
- Tersambung rapi pada routing CI3 untuk Purchasing dan Logistik.

---

## 3. Hasil Validasi & Pengujian

Pengujian integrasi end-to-end telah dijalankan pada `scratch/test_bundling_flow.php` dengan hasil:
1. **Master Formula**: Tersimpan sukses.
2. **Request Purchasing**: 250 Box Paket Jitu dibuat, sistem menghitung otomatis kebutuhan 250 Spontas, 250 Round Up, 250 Kaos Jitu.
3. **Mutasi Bahan**: Mutasi antar-gudang sukses tercatat di kartu stok (`KIUMTSI...`).
4. **Assembly Partial**: Realisasi 100 Box sukses merubah status request menjadi `PROSES_SEBAGIAN`, stok paket bertambah 100, stok bahan terpotong 100.
5. **Disassembly Eceran**: Pembongkaran 10 Box sukses memotong stok paket menjadi 90 Box dan mengembalikan 10 unit komponen ke stok eceran bebas tanpa double counting.
6. **Kartu Stok**: Audit trail di `tberp_stock_ledger` tercatat akurat dengan tipe transaksi `ASSEMBLY_PAKET` dan `DISASSEMBLY_PAKET`.
