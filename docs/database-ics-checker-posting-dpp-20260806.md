# Dokumentasi Development Database: Skema & Query Modul ICS LPB

**Tanggal Development**: 6 Agustus 2026  
**DBMS**: MySQL / MariaDB  
**Tabel Utama**: `tb_lpb`, `tb_lpb_detail`, `tb_lpb_log`  

---

## 1. Skema Kolom Database terkait

### Tabel `tb_lpb` (Header LPB)
| Nama Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id_lpb` | INT(11) AUTO_INCREMENT | NO | Primary key LPB |
| `kd_po` | VARCHAR(50) | NO | Kode PO referensi |
| `no_po` | VARCHAR(50) | NO | Nomor PO |
| `nomor_lpb` | VARCHAR(100) | YES | Nomor seri unik LPB |
| `jenis_lpb` | VARCHAR(50) | YES | Jenis LPB (misal: LPB CP, LPB LOGISTIK) |
| `status_lpb` | TINYINT(1) | NO | `0` = UNPOST (Draft), `1` = POST |
| `nosj` | VARCHAR(100) | YES | Nomor Surat Jalan |
| `tgl_sj` | DATE | YES | Tanggal Surat Jalan |
| `no_invoice` | VARCHAR(100) | YES | Nomor Invoice |
| `tanggal_invoice` | DATE | YES | Tanggal Terbit Invoice |
| `kode_faktur_pajak` | VARCHAR(100) | YES | Kode/Nomor Faktur Pajak |
| `tanggal_faktur_pajak` | DATE | YES | Tanggal Terbit Faktur Pajak |
| `checker_by` | VARCHAR(100) | YES | Kode/Nama Checker yang di-input Admin LPB |
| `checker_name` | VARCHAR(100) | YES | Nama Checker / User pemeriksa penerimaan |
| `checker_at` | DATETIME | YES | Waktu pemeriksaan / pencatatan checker |
| `input_by` | VARCHAR(100) | YES | Username pembuat record |
| `input_at` | DATETIME | YES | Waktu pembuat record |

---

## 2. Definisi Rumus & Query Akuntansi DPP & DPP Nilai Lain

### Ekspresi Query SQL pada Model (`M_Logistik.php`)

```sql
-- 1. Dasar Pengenaan Pajak (DPP Normal / Exclude PPN)
dpp = CASE 
    WHEN is_split_detail = 1 THEN COALESCE(d.total_harga, d.qty_diterima * COALESCE(d.harga_satuan, 0))
    ELSE d.qty_diterima * COALESCE(pp.harga_satuan_exclude, d.harga_satuan, 0)
END

-- 2. DPP Nilai Lain-Lain (Penyesuaian Rasio PMK PPN 11/12)
dpp_nilai_lain = (dpp * (11 / 12))

-- 3. PPN Terutang (Tarif 12%)
ppn = (dpp_nilai_lain * (12 / 100))

-- 4. Grand Total Transaksi
grand_total = dpp + ppn
```

### Penjelasan Analitis Rumus Akuntansi Perpajakan

1. **DPP (Dasar Pengenaan Pajak)**:
   - Menghitung akumulasi nilai bersih transaksi barang sebelum pajak PPN.
   - Apabila harga barang bersifat Exclude PPN, maka $\text{DPP} = \text{Qty Diterima} \times \text{Harga Netto Satuan}$.
2. **DPP Nilai Lain (DPP Nilai Lain-Lain)**:
   - Berdasarkan aturan **PMK 71/PMK.03/2022 & PMK 131/PMK.03/2024**, DPP Nilai Lain dihitung dengan rasio fiksi $11/12$ dari DPP Normal.
   - Hasil pengalian DPP Nilai Lain dengan tarif $12\%$ menghasilkan PPN efektif sebesar $11\%$ dari DPP Normal:
     $$\text{PPN} = \left(\text{DPP} \times \frac{11}{12}\right) \times 12\% = \text{DPP} \times 11\%$$
3. **Rekomendasi Database**:
   - Untuk mencegah perbedaan nilai historis jika terjadi perubahan tarif pajak pemerintah di masa depan, direkomendasikan untuk membekukan (persist/snapshot) nilai `dpp`, `dpp_nilai_lain`, dan `ppn` pada kolom fisik tabel `tb_lpb_detail` saat transaksi di-POST.

---

## 3. Log Audit & Histori Perubahan Status (`tb_lpb_log`)

Setiap perubahan status dari `UNPOST` (0) ke `POST` (1) atau sebaliknya mencatat log ke tabel `tb_lpb_log` dengan kolom:
- `id_lpb`, `nomor_lpb`, `action` (`POST` / `UNPOST`), `user`, `keterangan`, `created_at`.
