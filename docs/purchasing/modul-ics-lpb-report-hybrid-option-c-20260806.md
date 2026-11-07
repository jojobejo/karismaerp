# Dokumentasi Modul: Laporan Digital Purchasing, LPB & Master Barang (Opsi C - Hybrid Reporting)

**Tanggal Implementasi**: 06 Agustus 2026  
**Modul**: Logistik & Purchasing (`ICS`) & Keuangan (`Master Barang`)  
**Penulis**: Senior Fullstack Developer & Senior Data Analyst - KARISMA ERP Team  

---

## 1. Ikhtisar & Tujuan Pengembangan

Berdasarkan analisis komprehensif terhadap file Excel dari divisi Purchasing (`developmnet-purchasing.xlsx` - 71 kolom) dan schema database acuan (`kiucoid_karismaerp_local _acuan_2026_agustus.sql`), diimplementasikan **Opsi C (Hybrid Reporting)** serta **pengembangan modul Master Barang** untuk menyajikan data penerimaan barang, kewajiban hutang purchasing, dan master data barang secara **valid, akurat, dan consumable**.

Pendekatan Hybrid dan Upgrade Master Barang menggabungkan:
1. **Laporan Detail LPB Per-Item (`ics/lpb_report`)**: Laporan komprehensif hingga level item barang dengan 50+ kolom, konversi satuan, status LPB, dan tracking perubahan harga.
2. **Dashboard Summary Hutang Purchasing (`ics/summary_hutang`)**: Dashboard agregasi khusus level **faktur/invoice** untuk memantau total kewajiban hutang dagang (`Jumlah Per Faktur = SUM(DPP Nilai Lain + PPN 12%)`).
3. **Import Excel Purchasing (`ics/import_lpb`)**: Fitur upload & validasi otomatis 3 tahap (format, master data, kalkulasi perpajakan) untuk menyinkronkan data administrasi invoice & faktur pajak dari Purchasing.
4. **Upgrade Master Barang (`master_barang`)**: Penambahan field baru (`produsen`, `spesifikasi_merk`, `golongan`, `kelompok`, `komposisi`, `grup`) pada sajian data, pencarian, dan form input/edit master barang.

---

## 2. Struktur Routing & Endpoints

| URL Path | Controller & Method | Deskripsi | Hak Akses |
|----------|-------------------|-----------|-----------|
| `/master_barang` | `keuangan/C_Keuangan::master_barang` | Halaman Master Barang & Persediaan | Full Edit / Readonly sesuai Jobdesk |
| `/master_barang/list` | `keuangan/C_Keuangan::master_barang_list` | AJAX Data List Master Barang | Full Edit / Readonly sesuai Jobdesk |
| `/master_barang/detail` | `keuangan/C_Keuangan::master_barang_detail` | AJAX Detail Single Master Barang | Full Edit / Readonly sesuai Jobdesk |
| `/master_barang/store` | `keuangan/C_Keuangan::master_barang_store` | Simpan Data Master Barang Baru | Full Edit |
| `/master_barang/update` | `keuangan/C_Keuangan::master_barang_update` | Update Data Master Barang | Full Edit / Info Lain Edit |
| `/ics/lpb_report` | `logistik/C_Ics::lpb_report` | Halaman Laporan Detail LPB | Purchasing, Admin ERP, ADMLPB, Logistik |
| `/ics/summary_hutang` | `logistik/C_Ics::summary_hutang` | Halaman Dashboard Summary Hutang Per Faktur | Purchasing, Admin ERP, Keuangan, ADMLPB |
| `/ics/import_lpb` | `logistik/C_ImportLpb::index` | Halaman Drag & Drop Import Excel Purchasing | Purchasing, Admin ERP, ADMLPB |

---

## 3. Spesifikasi Kolom Baru pada Master Barang (`tbpo_barang`)

| Field | Tipe Data | Deskripsi | Status di UI Master Barang |
|-------|-----------|-----------|----------------------------|
| `produsen` | VARCHAR(150) | Produsen / Pemegang Pendaftaran | ✅ Tampil di Form, Dihitung di Search, Dapat Di-edit |
| `spesifikasi_merk` | VARCHAR(255) | Spesifikasi / Merk Barang | ✅ Tampil di Form, Dihitung di Search, Dapat Di-edit |
| `golongan` | VARCHAR(100) | Golongan (misal: Obat, Kimia, Pupuk) | ✅ Tampil di Form, Dihitung di Search, Dapat Di-edit |
| `kelompok` | VARCHAR(100) | Kelompok (misal: Fungisida, Herbisida, ZPT) | ✅ Tampil di Form, Dihitung di Search, Dapat Di-edit |
| `komposisi` | TEXT | Komposisi Bahan Aktif | ✅ Tampil di Form, Dihitung di Search, Dapat Di-edit |
| `grup` | VARCHAR(100) | Grup Barang (misal: 0, Other) | ✅ Tampil di Form, Dihitung di Search, Dapat Di-edit |
| `bhn_aktif` | TEXT | Nama Bahan Aktif | ✅ Tampil di Form & Search |
| `satuan` | VARCHAR(50) | Satuan Dasar | ✅ Tampil di Form & Search |
| `isi` | DECIMAL(15,2) | Qty per Box/Kemasan | ✅ Tampil di Form |
| `kemasan` | DECIMAL(15,2) | Kemasan (mL / Gram / Ltr) | ✅ Tampil di Form |

---

## 4. Pemeliharaan & Kode Acuan

- **Controller Keuangan & Master Barang**: [`application/controllers/keuangan/C_Keuangan.php`](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/controllers/keuangan/C_Keuangan.php)
- **Model Keuangan & Master Barang**: [`application/models/M_Keuangan.php`](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/models/M_Keuangan.php)
- **Controller ICS & LPB**: [`application/controllers/logistik/C_Ics.php`](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/controllers/logistik/C_Ics.php)
- **Model Laporan Purchasing**: [`application/models/M_LaporanPurchasing.php`](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/models/M_LaporanPurchasing.php)
- **View Master Barang**: [`application/views/content/keuangan/master_barang.php`](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/views/content/keuangan/master_barang.php)
- **JS View Master Barang**: [`application/views/content/keuangan/ajax/ajax_master_barang.php`](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/views/content/keuangan/ajax/ajax_master_barang.php)
