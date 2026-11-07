# Panduan Penggunaan: Header Akses & Export Excel Laporan LPB

**Target Pengguna**: Purchasing, Admin LPB, Logistik, Accounting, Admin Sistem  
**Modul**: ICS (Inventory & Control System) - Laporan Digital Purchasing & LPB  
**Route**: `ics/lpb_report`  
**Tanggal Update**: 6 Agustus 2026  

---

## 1. Tata Letak Tombol Header

Pada header card Laporan LPB, tombol-tombol navigasi dan aksi utama diposisikan sejajar di sebelah judul **Laporan LPB** dengan jarak rapi 5px:
- **Input LPB Manual**: Membuka form pencatatan LPB tanpa PO (`ics/lpb_manual`).
- **Data LPB Logistik**: Membuka daftar data LPB dari PO (`ics/icspo`).
- **Export Excel**: Mengunduh seluruh data laporan LPB ke format Excel sesuai kriteria filter yang aktif.

---

## 2. Cara Mengunduh Laporan Excel

1. Masuk ke halaman **Laporan LPB** (`ics/lpb_report`).
2. Atur kriteria pencarian pada **Filter Data Sajian** (misalnya: Tanggal, Sumber Transaksi, Aging Faktur Pajak, Jenis LPB, atau Quick Filter Badges).
3. Klik tombol hijau **Export Excel** yang terletak di header card.
4. Sistem akan secara otomatis mengunduh file `.xlsx` berjudul `Laporan_Digital_Purchasing_LPB_YYYYMMDD_HHMMSS.xlsx` yang berisi seluruh data lengkap yang telah disaring.
