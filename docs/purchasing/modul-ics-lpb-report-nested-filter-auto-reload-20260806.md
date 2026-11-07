# Dokumentasi Modul ICS Laporan LPB: Real-time Live Filter, Nested Filter & Export Excel

**Modul**: ICS (Inventory & Control System) - Digital Purchasing & LPB Report  
**Route Utama**: `ics/lpb_report`  
**Route Export**: `ics/lpb_report/export_excel`  
**Versi Modul**: 2.6.0  
**Tanggal Update**: 6 Agustus 2026  
**Developer**: Senior Fullstack Developer & Data Analyst  

---

## 1. Ikhtisar Pembaharuan Modul

Halaman **Laporan Digital Purchasing & LPB** (`ics/lpb_report`) telah diperbarui dengan fitur-fitur utama berikut:

1. **Header Layout & Tombol Akses (Gap 5px)**: Menyejajarkan teks header `Laporan LPB` dengan tombol **Input LPB Manual**, **Data LPB Logistik**, dan **Export Excel** dengan jarak konsisten 5px.
2. **Penerapan Combined Single-Row Filter**: Seluruh opsi filter disajikan dalam 1 baris grid teratur tanpa teks badge pemisah (*Filter Utama* / *Nested Filter*).
3. **Fitur Auto Live Reload (Zero Page Reload)**: Memperbarui data DataTables secara otomatis begitu ada input filter yang berubah tanpa refresh browser.
4. **Fungsi Export Excel Presisi**: Mengeksport seluruh kolom data Laporan LPB ke format `.xlsx` berdasarkan kriteria filter yang sedang aktif di layar.

---

## 2. Spesifikasi Endpoint Export Excel

- **URL Endpoint**: `GET base_url('ics/lpb_report/export_excel')`
- **Method**: `GET` (Menerima Query Parameters)
- **Controller Method**: `C_Ics::export_lpb_report_excel()`
- **Model Method**: `M_LaporanPurchasing::get_report_data_for_export()`
- **Output**: File Spreadsheet `.xlsx` (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`) dengan fallback stream HTML Spreadsheet.

---

## 3. Matriks Peran & Hak Akses (RBAC Matrix)

| Peran (Role) | Akses Route `ics/lpb_report` | Auto-Reload Live Filter | Export Excel (`ics/lpb_report/export_excel`) |
| :--- | :---: | :---: | :---: |
| **Admin ERP** | **Ya** | **Ya** | **Ya** |
| **Purchasing** | **Ya** | **Ya** | **Ya** |
| **Admin LPB (ADMLPB)** | **Ya** | **Ya** | **Ya** |
| **Logistik** | **Ya** | **Ya** | **Ya** |
| **User Biasa / Non-Auth** | Ditolak (403) | Tidak | Ditolak (403) |
