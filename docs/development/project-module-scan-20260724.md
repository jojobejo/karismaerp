# Laporan Scan Modul Sistem KARISMA ERP

Tanggal scan: 2026-07-24  
Workspace: `C:\xampp\htdocs\karismaerp`  
Mode: read-only; tidak ada perubahan kode atau data aplikasi.

## 1. Ringkasan eksekutif

KARISMA ERP adalah aplikasi CodeIgniter 3 dengan struktur utama:

- front controller: `index.php`;
- route utama: `application/config/routes.php`;
- controller dan model domain: `application/controllers` dan `application/models`;
- view domain: `application/views/content`;
- HMVC/legacy PO: `application/modules/kiupo`;
- SQL, migration, audit, dan dokumentasi: `database`, `db`, dan `docs`.

Hasil inventarisasi route menunjukkan domain terbesar adalah Logistik/ICS, Keuangan, Stock Opname, Sales Order, Retur, dan Checker. Total route yang terdefinisi di `application/config/routes.php` adalah sekitar 500 baris route, termasuk alias dan endpoint AJAX. Angka route bukan ukuran fitur unik karena beberapa route merupakan alias, print endpoint, atau AJAX endpoint.

Runtime lokal menggunakan PHP 7.4.33 dan MariaDB 10.4.27. Pada awal scan, konfigurasi aplikasi menunjuk database `u471548307_karismaerp`, tetapi database tersebut tidak tersedia pada MariaDB lokal. Saat review akhir, working tree menunjukkan perubahan belum di-commit yang mengalihkan konfigurasi ke `kiucoid_karismaerp_local`; perubahan itu bukan dibuat oleh scan ini dan sengaja dibiarkan. Data yang dipakai untuk laporan database adalah database lokal `kiucoid_karismaerp_local`; status environment dijelaskan detail pada laporan database terpisah.

## 2. Daftar modul aplikasi

| Modul | Fungsi bisnis singkat | Komponen kode utama | Bukti route / status scan |
|---|---|---|---|
| Portal, Auth, Dashboard | Landing page, login, proses autentikasi, logout, dashboard awal, dan redirect berbasis user. | `Portal.php`, `Auth.php`, `Dashboard.php`, `M_Auth.php`, `M_Dashboard.php`, `content/portal`, `content/login`, `content/dashboard` | Route `portal`, `auth`, `process`, `logout`, `dashboard`. **Core aktif**. |
| Master user dan otorisasi | Pengelolaan user, jobdesk, level akses, menu, permission matrix, dan sidebar dinamis. | `master/C_Usermanagement.php`, `C_Jobdesk.php`, `C_Akseslevel.php`, `C_Menu.php`, model `models/master/*` | Route prefix `master/*`. **Aktif**, dengan menu dinamis bergantung pada `tb_menu` dan hak akses. |
| Master barang, supplier, customer, gudang, satuan, dan pajak | Menyediakan master referensi untuk pembelian, penjualan, stock, dan logistik. | `keuangan/C_Keuangan.php`, `M_Keuangan.php`, `C_Ics.php`, view `content/keuangan`, `content/logistik/ics`, HMVC `kiupo/master_barang`, `kiupo/suplier` | Route `master_barang`, `master_customer`, `ics/master_barang`, `ics/gudang`, serta endpoint PO. **Aktif namun memiliki duplikasi legacy**. |
| Purchasing PO komersil | Pembuatan dan pengelolaan PO supplier komersil, item, harga, diskon, tax, dan penerimaan. | `logistik/C_Ics.php`, `M_Ics.php`, `C_Keuangan.php`, `M_Keuangan.php`, `tbpo_*`, `tb_lpb*` | Route `ics/icspo`, `ics/detail_po`, `ics/detail_record_lpb`, `logistik/lpb`. **Aktif; flow penerimaan terhubung ke LPB**. |
| Purchasing PO nonkomersil / PONK | Request barang nonkomersil, PIC, approval, status PO, revisi, print internal, dan histori. | `application/modules/kiupo/controllers/purchaseorder/*`, `postatus/*`, `master_barang/*`, view `kiupo/views/content/po/*`, `postatus/*` | Route PO utama tersebar pada registry utama dan `application/modules/kiupo/routes.php`. **Legacy/HMVC aktif secara kode; perlu validasi runtime terhadap database target**. |
| Stock dan kartu stock | Ketersediaan stock per gudang, batch/lot/expired, saldo awal, stock ledger, minimum stock, dan rekonsiliasi. | `stock/C_Stock.php`, `M_Stock.php`, `M_Stockbuffer.php`, `M_Keuangan.php`, `M_Ics.php` | Route `stock/*`, `keuangan/*`, `ics/*`, `logistik/stock`. **Aktif secara kode dan memiliki data stock**. |
| ICS operasional | Inventory Control System untuk stock masuk/keluar, lot, expired, histori ICS, dan sinkronisasi PO/DO. | `logistik/C_Ics.php` (166 method), `M_Ics.php`, view `content/logistik/ics/*` | Sekitar 143 route target `logistik/C_Ics`, termasuk AJAX. **Modul terbesar dan aktif**. |
| LPB / penerimaan barang | Mencatat penerimaan barang dari PO, detail qty dan lot, nomor LPB, invoice/faktur pajak, harga, verifikasi, post/unpost, print, dan log. | `C_Ics.php`, `M_Logistik.php`, `M_LpbPriceAdjustment.php`, view `detail_po`, `detail_record_lpb`, `lpb_manual` | Route `ics/detail_po`, `ics/detail_record_lpb`, `ics/lpb_manual`, `ics/ajax_*lpb*`. **Aktif; status LPB dan verifikasi harga adalah konsep berbeda**. |
| Mutasi antar gudang | Draft, pemilihan lot/expired, validasi stock, rekam, rollback, delete, dan unpost mutasi gudang. | `C_Ics.php`, `M_Ics.php`, view `content/logistik/ics/mutasi_barang*` | Route `ics/mutasi_barang/*` dan AJAX mutasi. **Aktif secara kode; data snapshot baru berjumlah kecil**. |
| Sales Order | Pembuatan SO, approval, stock check, plafon customer, pembuatan faktur, split faktur, rute, dan aktivitas SO. | `sales/C_SalesOrder.php` (74 method), `M_SalesOrder.php`, `M_Stock.php`, `M_Logistik.php`, view `content/sales/*` | Route prefix `sales_order/*`. **Aktif dan memiliki transaksi**. |
| Delivery Order dan loading | Pembentukan DO dari SO, detail pengiriman, loading, verifikasi loading, rute, driver, serta status faktur/DO. | `logistik/C_Logistik.php`, `C_Checker.php`, `C_Distribusi.php`, `M_Logistik.php`, `M_Checker.php` | Route `logistik/*`, `sales_order/list_do`, `so_siap_loading/*`. **Aktif dengan beberapa flow legacy**. |
| Distribusi dan checker | Penugasan driver/helper, rute, tonase, produktivitas, bongkaran, checklist kendaraan, dan monitoring proses loading. | `C_Distribusi.php`, `C_Checker.php`, `M_Distribusi.php`, `M_Checker.php`, `content/logistik/distribusi`, `checker` | Sekitar 17 route distribusi dan 75 route checker. **Aktif secara kode dan memiliki data operasional**. |
| Retur penjualan | Surat Pengajuan Retur, approval berjenjang SC sampai Logistik, cek fisik, proses retur, collection, kasir, history, dan print. | `sales/C_ReturPenjualan.php` (58 method), `M_ReturPenjualan.php`, view `content/sales/retur*` | Route prefix `retur_penjualan/*` dan ICS retur penjualan. **Aktif secara kode; snapshot penjualan belum memiliki baris retur final**. |
| Retur pembelian | Draft retur berbasis LPB, detail barang/lot, verifikasi Purchasing dan Accounting, posting, void, reversal, dan adjustment. | `C_Ics.php`, `M_ReturPembelian.php`, `M_LpbPriceAdjustment.php`, tabel `tb_retur_pembelian*` | Route `ics/retur/pembelian/*`. **Aktif dan memiliki data POSTED pada snapshot**. |
| Stock Opname | Master item opname, input manual/scan, QR/barcode, pending item, supervisor, monitoring, compare, repost, recycle bin, dan export. | `admin/C_Stockopname.php` (117 method), `admin/M_Stockopname.php`, view `content/admin/*stockopname*` | 89 route `admin/stockopname/*` plus alias `stockopname/*` dan `supervisi-opname`. **Aktif dan kompleks**. |
| Accounting dan Jurnal | Chart of Accounts, master klasifikasi, jurnal, detail jurnal, periode fiskal, posting/reversal, exception, laporan, dan integrasi sumber transaksi. | `keuangan/C_Accounting.php`, `C_Keuangan.php`, `M_Keuangan.php`, `M_Journal.php`, `Accounting_service.php`, view `content/keuangan/*` | Route `accounting/*`, `jurnal/*`, `keuangan/jurnal/*`. **Aktif secara kode; data snapshot masih kecil dan harus diuji terhadap database target**. |
| Pembayaran faktur | Daftar pembayaran, customer/supplier payment, pencairan BG, alokasi, dan jurnal pembayaran. | `keuangan/C_pembayaran.php`, `M_pembayaran.php`, `M_Journal.php` | Route `keuangan/pembayaran/*`, `jurnal/payment-list`. **Kode tersedia; tabel pembayaran faktur snapshot kosong**. |
| Laporan dan import/export | Laporan penjualan, pembelian, barang, kartu stock, distribusi, CSV import, Excel export, dan API stock. | `keuangan/C_Laporan.php`, `keuangan/CsvController.php`, `logistik/C_ExportStock.php`, `api/C_Api.php`, `zahir/C_Zahir.php` | Route `laporan/*`, `csv_import*`, `api/*`, export pada Logistik/Sales. **Utility layer; mengambil data modul lain**. |
| HRD / penilaian lingkungan | Pelaporan isu lingkungan, evidence upload, rating, status, lokasi, monitoring, statistik, mobile ERP, tamu, laporan distribusi HRD, karyawan, dan service kendaraan. | `hrd/C_Hrd.php` (64 method), `M_Hrd.php`, view `content/hrd`, `mobile_erp` | Route `penilaian_lingkungan`, `hrd/penilaian_lingkungan/*`, `mobile-erp/*`. **Aktif dan memiliki data isu**. |
| KPI / penilaian kinerja | Dashboard KPI dan indikator What/How. | `kpi/C_Kpi.php`, `M_Kpi.php`, view `content/kpi/*`, tabel `tb_kpi*`, `tb_whats*`, `tb_hows*` | Controller dan view tersedia, tetapi route KPI eksplisit tidak terlihat pada registry utama saat scan. **Partial/orphan legacy**. |
| Schedule direktur dan tamu | Jadwal tamu/agenda direktur dan aksi status jadwal. | `schedule/C_Schedule.php`, `M_Hrd.php`, view `content/schedule` | Route `schedule_direktur`, `act_schedule/*`. **Aktif dengan data historis**. |
| Extravaganza / undian | Registrasi tamu/customer undian dan penyimpanan pemenang. | `extravaganza/C_Extravaganza.php`, `M_Extravaganza.php`, tabel `tb_customer_list_undian`, `tb_pemenang` | Route `extravaganza*`. **Aktif secara kode dan memiliki data**. |
| Inventaris | CRUD inventaris aset dan pemilik/departemen. | `inventaris/C_Inventaris.php`, `M_Inventaris.php`, view `content/inventaris` | Controller/view tersedia, tetapi route `inventaris` tidak terlihat pada `routes.php` saat scan. **Partial/orphan**. |
| Maintenance | Halaman placeholder maintenance. | `maintenance/C_Maintenance.php` | Controller hanya menyediakan `index()` dan belum memuat model valid. **Belum operasional**. |
| Request Design | Terlihat sebagai salinan/varian layar inventaris untuk request design. | `requestdesign/C_requestdesign.php`, `M_Requestdesign.php`, view `content/requestdesign` | Tidak ditemukan route aktif yang jelas; controller masih memanggil `$this->M_Inventaris`. **Belum operasional; perlu refactor sebelum dianggap modul bisnis**. |
| API dan integrasi eksternal | Endpoint API internal/stock, sinkronisasi Zahir, import data, dan sumber data eksternal seperti plafon customer. | `api/C_Api.php`, `M_api.php`, `zahir/C_Zahir.php`, konfigurasi API di Sales Order | Route `api/*`, `api/v1/stock/*`, `data_lpb_zahir`, import/export. **Terdistribusi; runtime integrasi belum diuji pada scan ini**. |

## 3. Peta struktur teknis

| Layer | Lokasi | Temuan |
|---|---|---|
| Routing | `application/config/routes.php`, `application/modules/kiupo/routes.php` | Dua registry route dengan overlap; registry HMVC menduplikasi sebagian route legacy. |
| Controller | `application/controllers` | 38 file controller utama terdeteksi; domain Logistik/Keuangan/Sales paling besar. |
| Model | `application/models` | Model domain mayoritas berada di root, dengan subfolder `admin`, `master`, dan `Api`. |
| View | `application/views/content` | View dipisah per domain: admin, HRD, Keuangan, Logistik, Sales, KPI, Master, dan lainnya. |
| HMVC PO | `application/modules/kiupo` | Memiliki controller/model/view sendiri untuk PO, status PO, supplier, stock, laporan, setting, dan user. |
| Library | `application/libraries` | Terdapat library accounting dan helper/library umum; sebagian flow accounting terhubung ke service. |
| Database | `application/config/database.php`, `database`, `db`, `docs/database` | Konfigurasi menunjuk DB yang tidak tersedia secara lokal; banyak dump/migration tersimpan di repo. |

## 4. Temuan manajemen dan risiko

1. **Konfigurasi database berubah selama proses scan.** Baseline Git menunjuk `u471548307_karismaerp` yang tidak tersedia lokal, sedangkan working tree saat review menunjuk `kiucoid_karismaerp_local` melalui perubahan belum di-commit. Endpoint aplikasi tetap belum dianggap runtime-verified karena tidak dilakukan UAT route penuh.
2. **Ada dua jalur arsitektur PO.** PO baru/legacy berada pada `application/modules/kiupo`, sedangkan sebagian penerimaan dan LPB berada pada controller utama Logistik/ICS. Ownership flow perlu diputuskan agar tidak terjadi dua sumber kebenaran.
3. **Legacy table dan modern table hidup bersamaan.** Contohnya `tb_user`/`tb_users`, `tb_master_barang`/`tb_barangv2`/`tbpo_barang`, serta tabel stock lama dan `tberp_stock_*`.
4. **KPI, Inventaris, Maintenance, dan Request Design belum setara tingkat kematangannya dengan modul transaksi.** Modul-modul tersebut sebaiknya dipisahkan dalam backlog “stabilization” sebelum diklaim sebagai modul produksi penuh.
5. **Accounting sudah memiliki permukaan route dan tabel yang nyata, tetapi rentang data pada snapshot hanya 2026-07-20 sampai 2026-07-23.** Validasi terhadap database target dan transaksi produksi tetap diperlukan.
6. **Dokumentasi sudah luas tetapi tersebar.** Laporan ini menjadi indeks tingkat proyek; dokumentasi detail tiap perubahan tetap berada pada `docs/development`, `docs/database`, dan `docs/accounting`.

## 5. Batasan scan

- Scan ini bersifat inventarisasi kode dan database lokal, bukan UAT seluruh route.
- Tidak ada insert, update, delete, import, migration, atau perubahan konfigurasi yang dijalankan.
- Rentang tanggal database, sumber query, jumlah row, dan caveat format tanggal dicatat pada `docs/database/project-module-data-scan-20260724.md`.
- “Aktif” berarti terdapat route/controller/view dan/atau data yang mendukung; bukan jaminan seluruh role dan endpoint bebas error.
