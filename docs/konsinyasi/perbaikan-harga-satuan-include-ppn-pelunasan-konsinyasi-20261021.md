# Perbaikan Penanganan Harga Satuan PO (Include PPN) pada Pelunasan Konsinyasi

**Tanggal**: 21 Oktober 2026  
**Modul**: Purchasing — Konsinyasi (`purchasing/konsinyasi`)  
**Status**: Resolved / Fixed  

---

## 1. Masalah yang Ditemukan
Pada halaman **Penyelesaian Konsinyasi** (`purchasing/konsinyasi?tab=penyelesaian&status=LAKU&kd_suplier=PUPUK03`), saat pengguna hendak melunasi tagihan supplier untuk barang konsinyasi yang berasal dari PO dengan status **Include PPN** (contoh: PO `Q001/KIU/X/2026` dengan harga satuan include PPN Rp 100.000,00), modal pelunasan menampilkan harga include PPN sebesar **Rp 90.090,09**.

Akibatnya:
- Sistem menganggap nilai Rp 90.090,09 adalah harga Include PPN, sehingga saat live calculation berjalan, sistem membaginya lagi dengan 1,11 menjadi DPP Rp 81.162,24 dan PPN Rp 8.927,85, dengan total bayar hanya Rp 90.090,09 (seharusnya Rp 100.000,00 per zak).

---

## 2. Penyebab Akar Masalah (Root Cause Analysis)
1. **Pemisahan Harga Input PO vs DPP LPB/FIFO**:
   - Di tabel `tbpo_detail_po`, kolom `hrg_satuan` menyimpan harga input yang diisikan user saat membuat PO (Rp 100.000,00 dengan `keterangan_harga_ppn = 'include'`), sedangkan `harga_satuan_exclude` menyimpan nilai DPP (Rp 90.090,09).
   - Saat LPB dan alokasi HPP FIFO (`tb_konsinyasi_cogs_allocation`) terbentuk, harga satuan yang disimpan adalah nilai DPP murni (Rp 90.090,0901) untuk pencatatan nilai persediaan.
2. **Prioritas Subquery & Penimpaan Harga di Settlement**:
   - Di `M_Konsinyasi::get_settlement_list()` dan `M_Konsinyasi::get_settlement_by_id()`, penentuan `po_hrg_satuan` sebelumnya mengutamakan `s.hrg_beli_satuan` terlebih dahulu:
     `COALESCE(NULLIF(s.hrg_beli_satuan, 0), NULLIF(pod.hrg_satuan, 0), ...)`
     Karena `s.hrg_beli_satuan` menyimpan nilai DPP (Rp 90.090,09), nilai ini menimpa harga PO asli (`pod.hrg_satuan` = Rp 100.000,00).
   - Di fungsi `sync_settlements_with_fifo_allocation()`, nilai DPP langsung disimpan ke `hrg_satuan_input` tanpa memeriksa apakah PO asalnya bertipe Pajak `INCLUDE`.
   - Di view JavaScript `openModalInputTagihan` (`settlement_list.php`), input harga pelunasan mengambil `d.po_hrg_satuan || d.hrg_satuan_input`. Karena kedua nilai tersebut berisi nilai DPP, modal secara salah memperlakukan DPP tersebut sebagai harga Include PPN.

---

## 3. Solusi dan Perubahan Kode
1. **Helper Penentu Harga PO (`M_Konsinyasi::resolve_po_pricing_for_lpb`)**:
   - Menambahkan helper untuk mengecek status PPN PO asal (`tbpo_po.keterangan_harga_ppn`), harga input (`pod.hrg_satuan`), dan harga exclude (`pod.harga_satuan_exclude`).
2. **Sinkronisasi Otomatis FIFO (`M_Konsinyasi::sync_settlements_with_fifo_allocation`)**:
   - Jika PO asal bertipe `INCLUDE`, `tb_konsinyasi_settlement.tipe_pajak` diset `'INCLUDE'` dan `hrg_satuan_input` diset ke harga input PO (misal Rp 100.000,00), sementara `hrg_beli_satuan` tetap menyimpan nilai DPP (Rp 90.090,09).
3. **Query Konsisten di `M_Konsinyasi`**:
   - Memperbaiki ekspresi `po_hrg_satuan` di `get_settlement_list()`, `get_settlement_by_id()`, `get_tracking_kios_by_barang()`, dan `get_laku_suppliers_summary()`:
     - Jika PO `INCLUDE`: `po_hrg_satuan` mengambil harga Include (`pod.hrg_satuan`), dan `po_hrg_satuan_exclude` mengambil DPP (`pod.harga_satuan_exclude`).
     - Jika PO `EXCLUDE`: `po_hrg_satuan` dan `po_hrg_satuan_exclude` disesuaikan dengan benar.
4. **Antarmuka Modal Pelunasan (`settlement_list.php`)**:
   - Memperbaiki JavaScript `openModalInputTagihan` agar mengidentifikasi tipe pajak PO secara presisi (`INCLUDE`, `EXCLUDE`, `NON_PPN`) dan memasukkan nilai awal `hrg_satuan_input` yang tepat.
   - Live breakdown menampilkan:
     - **Harga Satuan DPP**: Rp 90.090,09
     - **Harga Satuan Include PPN**: Rp 100.000,00
     - **Total Tagihan**: Sesuai nilai include PPN Rp 100.000,00 / satuan.
