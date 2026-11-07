# Dokumentasi Modul ICS LPB: Penerimaan PO, Checker By, Workflow Posting Data & Kalkulasi DPP

**Modul**: ICS (Inventory & Control System) - Penerimaan Barang (LPB)  
**Versi Modul**: 2.4.0  
**Tanggal Update**: 6 Agustus 2026  

---

## 1. Ikhtisar Modul

Modul LPB (Laporan Penerimaan Barang) merupakan komponen inti pada sistem KarismaERP yang menghubungkan proses **Logistik (Gudang)** dengan **Purchasing (Pengadaan)** dan **Finance/Accounting (Keuangan)**.

Modul ini bertanggung jawab untuk:
1. Merekam transaksi fisik penerimaan barang dari supplier berdasarkan Purchase Order (PO).
2. Mencatat identitas petugas pemeriksa fisik (**Checker By**).
3. Mengontrol alur persetujuan status LPB (**UNPOST** vs **POST**).
4. Menghitung Dasar Pengenaan Pajak (**DPP**), **DPP Nilai Lain-Lain**, dan **PPN Terutang** sesuai standar akuntansi perpajakan Indonesia.

---

## 2. Peta Alur Bisnis (Business Workflow)

```
[ Purchase Order (PO) ]
          │
          ▼
[ Form Penerimaan PO (ics/detail_po) ] ◄── Admin / Admin LPB
  • Input Qty Diterima, No Lot, Expired Date
  • Input Gudang Tujuan & Jenis LPB
  • Input Field: Checker By (Nama / Kode Checker Gudang)
          │
          ▼
[ Database LPB (tb_lpb & tb_lpb_detail) ] ── Status: UNPOST (0)
          │
          ▼
[ Verifikasi Purchasing & Admin (ics/detail_record_lpb) ]
  • Cek Kesesuaian Harga, Invoice, & Faktur Pajak
  • Tampilan Header: Menyajikan Data Checker By
  • Aksi: Klik Tombol "Posting Data"
          │
          ▼
[ Database LPB (tb_lpb) ] ── Status: POST (1)
          │
          ├──> Jurnal Otomatis Pembelian & Hutang Dagang
          └──> Pembaruan Stok Persediaan Gudang
```

---

## 3. Matriks Peran dan Hak Akses (RBAC Matrix)

| Fitur / Halaman | Admin | Admin LPB (ADMLPB) | Purchasing | Logistik |
| :--- | :---: | :---: | :---: | :---: |
| Input Form `Checker By` (`ics/detail_po`) | **Ya** | **Ya** | Tidak | Tidak |
| Simpan Draft Penerimaan PO | **Ya** | **Ya** | Ya (jika panel mode) | Ya |
| Lihat Informasi `Checker By` (`ics/detail_record_lpb`) | **Ya** | **Ya** | **Ya** | **Ya** |
| Tombol `Posting Data` (`ics/detail_record_lpb`) | **Ya** | Tidak | **Ya** | Ya (pada view logistik) |
| UNPOST LPB (`ics/detail_record_lpb`) | **Ya** | Tidak | **Ya** | Ya (pada view logistik) |

---

## 4. Standar Kalkulasi Keuangan & Akuntansi DPP

1. **DPP (Dasar Pengenaan Pajak)**:
   $$\text{DPP} = \text{Qty Diterima} \times \text{Harga Netto Satuan (Exclude PPN)}$$
2. **DPP Nilai Lain-Lain**:
   $$\text{DPP Nilai Lain} = \text{DPP} \times \left(\frac{11}{12}\right)$$
3. **PPN (Pajak Pertambahan Nilai 12%)**:
   $$\text{PPN} = \text{DPP Nilai Lain} \times 12\% = \text{DPP} \times 11\%$$
4. **Grand Total**:
   $$\text{Grand Total} = \text{DPP} + \text{PPN}$$

> [!NOTE]
> Ketentuan perhitungan di atas menjamin kesesuaian nilai faktur pajak elektronik (e-Faktur DJP) dengan pembukuan akuntansi persediaan dan hutang dagang pada KarismaERP.
