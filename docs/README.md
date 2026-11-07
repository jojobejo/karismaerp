# Dokumentasi Sistem KARISMA ERP

Selamat datang di repositori dokumentasi **KARISMA ERP**. Direktori ini berfungsi sebagai pusat referensi teknis, skema database, alur bisnis, dan panduan operasional per modul untuk seluruh pengembang, analis data, dan tim operasional.

---

## 📌 Aturan & Konvensi Dokumentasi

Sesuai dengan panduan utama pada [`AGENTS.md`](file:///c:/laragon/www/karismaerp/AGENTS.md) dan [`docs/AGENT.md`](file:///c:/laragon/www/karismaerp/docs/AGENT.md):
1. **Pemisahan Per Fitur / Modul**: Setiap dokumen teknis dan operasional disimpan secara terpisah ke dalam subfolder modul terkait.
2. **Standar Bahasa**: Seluruh dokumentasi disusun menggunakan **Bahasa Indonesia**.
3. **Format Tiga Lapisan (Three-Layer Architecture Docs)**: Setiap penambahan fitur atau perubahan alur bisnis terbagi atas:
   - **Database Layer** (`docs/database/`): Skrip migrasi SQL dan dokumen perubahan skema/tabel.
   - **Development Layer** (`docs/development/`): Catatan teknis arsitektur MVC (Controller, Model, View, Route) dan changelog logic.
   - **Penggunaan / User Guide** (`docs/<modul>/`): Petunjuk langkah demi langkah bagi user/operator lapangan.

---

## 📁 Struktur Direktori Dokumen

```text
docs/
├── README.md                               # Dokumen indeks utama ini
├── AGENT.md                                # Panduan kerja dan workflow khusus developer/agent
├── daftar-modul-sistem-karismaerp.md       # Katalog lengkap modul dan rute aplikasi KARISMA ERP
├── daftar_modul_karismaerp.csv             # Data tabel master daftar modul
├── template_agent.md                       # Template prompt/konteks acuan agent
│
├── accounting/                             # Modul Akuntansi, Jurnal Umum, Buku Besar, Tutup Buku
├── admin-transaksi/                        # Modul Admin Panel Transaksi & Pengaturan Global
├── database/                               # Skrip SQL migration & dokumentasi skema database
├── development/                            # Dokumentasi teknis development & log perbaikan fitur
│   └── BSC/                                # Laporan Balanced Scorecard & bahan presentasi
├── keuangan/                               # Modul Finance: Pembayaran Hutang/Piutang, Kasbon, Bank
├── konsinyasi/                             # Modul Konsinyasi: LPB Konsinyasi, HPP FIFO, Penyelesaian
├── laporan/                                # Dokumentasi modul Laporan & Analitik Kartu Stok
├── logistik/                               # Modul Logistik: Pengiriman, Checker Loading, Armada/Trip, Gudang
├── lpb-manual/                             # Modul Penerimaan Barang Manual & Posting Jurnalnya
├── purchasing/                             # Modul Pengadaan (PO), LPB, Verifikasi Harga & Bundling
├── retur-pembelian/                        # Modul Retur Pembelian ke Supplier & Pemotongan Hutang
├── retur-penjualan/                        # Modul Retur Penjualan dari Customer
├── sales/                                  # Modul Penjualan, Sales Order (SO), Admin SC, Plafon Kredit
└── stockopname/                            # Modul Stock Opname: Scan Fisik, Supervisi, Barang Pending
```

---

## 🧭 Panduan Navigasi Direktori per Modul

| Folder | Deskripsi & Cakupan Fitur | Tipe Dokumen Utama |
| :--- | :--- | :--- |
| [`accounting/`](file:///c:/laragon/www/karismaerp/docs/accounting/) | Akuntansi, Chart of Accounts (COA), jurnal umum, migrasi Zahir, modul tutup buku, dan UAT akuntansi. | Blueprint, Alur Jurnal, Spec, UAT |
| [`admin-transaksi/`](file:///c:/laragon/www/karismaerp/docs/admin-transaksi/) | Manajemen seluruh transaksi admin, dashboard awal, log akses hostinger, topbar navigasi. | Petunjuk Penggunaan, Review Panel |
| [`database/`](file:///c:/laragon/www/karismaerp/docs/database/) | Skrip migrasi DDL/DML SQL, skema relasi tabel, penyesuaian index, dan audit struktur database. | Script `.sql`, Spesifikasi Skema `.md` |
| [`development/`](file:///c:/laragon/www/karismaerp/docs/development/) | Catatan arsitektur kode MVC, investigasi bug, penyesuaian controller/model, git push harian, dan sprint. | Dokumen Teknis Development `.md` |
| [`development/BSC/`](file:///c:/laragon/www/karismaerp/docs/development/BSC/) | Bahan presentasi eksekutif Karisma ERP, slide kemajuan, dan spreadsheet Balanced Scorecard. | Presentasi `.pptx`, Dashboard `.xlsx`, `.html` |
| [`keuangan/`](file:///c:/laragon/www/karismaerp/docs/keuangan/) | Pembayaran hutang supplier, pelunasan faktur customer, approval workflow kasbon, dan analisa cashflow. | Alur Pembayaran, Workflow Kasbon |
| [`konsinyasi/`](file:///c:/laragon/www/karismaerp/docs/konsinyasi/) | Pencatatan barang konsinyasi, kalkulasi HPP konsinyasi FIFO, faktur mandiri konsinyasi, rekonsiliasi gudang. | Alur Konsinyasi, FIFO HPP |
| [`laporan/`](file:///c:/laragon/www/karismaerp/docs/laporan/) | Format laporan kartu stok, laporan laba rugi, dan rekonsiliasi persediaan. | Format Laporan, Panduan Cetak |
| [`logistik/`](file:///c:/laragon/www/karismaerp/docs/logistik/) | Alur muat barang (loading), checker gudang, penomoran lambung armada, trip rute, dan buffer stock. | SOP Gudang, Checker Loading |
| [`lpb-manual/`](file:///c:/laragon/www/karismaerp/docs/lpb-manual/) | Tata cara input penerimaan barang non-PO / manual beserta mekanisme penjurnalan otomatisnya. | Panduan LPB Manual & Jurnal |
| [`purchasing/`](file:///c:/laragon/www/karismaerp/docs/purchasing/) | Purchase Order (PO), verifikasi harga LPB, integrasi ICS, master kelompok dagang, dan paket bundling item. | SOP Purchasing, Verifikasi LPB |
| [`retur-pembelian/`](file:///c:/laragon/www/karismaerp/docs/retur-pembelian/) | Alur retur barang ke supplier, nota retur, dan mekanisme potong hutang supplier otomatis. | Panduan Retur Pembelian |
| [`retur-penjualan/`](file:///c:/laragon/www/karismaerp/docs/retur-penjualan/) | Pengembalian barang dari customer, unpost faktur retur, pemulihan kartu stok, dan revisi faktur. | Panduan Retur Penjualan |
| [`sales/`](file:///c:/laragon/www/karismaerp/docs/sales/) | Sales Order (SO), verifikasi Admin SC, manajemen plafon pelanggan, navigasi keyboard SO, dan faktur Z. | Panduan Sales Order & Admin SC |
| [`stockopname/`](file:///c:/laragon/www/karismaerp/docs/stockopname/) | Pelaksanaan stock opname gudang, aplikasi barcode scanner, barang pending, supervisi manual, dan rekonsiliasi. | SOP Stock Opname & Panduan Mobile |

---

## 🛠️ Pedoman Pembuatan Dokumen Baru

Saat membuat fitur baru, letakkan dokumen pada direktori yang tepat dengan konvensi penamaan:
- **Untuk Panduan Operasional / Fitur**: Letakkan di `docs/<nama-modul>/<nama-fitur>.md`
- **Untuk Skema & Query Database**: Letakkan di `docs/database/<nama-fitur>.md` (sertakan file `.sql` jika ada alter table)
- **Untuk Catatan Implementasi Kode / MVC**: Letakkan di `docs/development/<nama-fitur>.md`

