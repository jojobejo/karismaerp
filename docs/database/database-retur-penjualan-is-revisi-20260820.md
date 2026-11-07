# Migrasi Database: Penambahan Kolom is_revisi pada tbrp_retur_penjualan_header

**Tanggal:** 20 Agustus 2026  
**Modul:** Retur Penjualan (`sales/C_ReturPenjualan`)  
**Tujuan:** Mendukung fitur penandaan retur revisi administrasi / non-revisi (potong saldo faktur) oleh Admin Retur.

---

### SQL Migration
```sql
ALTER TABLE `kiucoid_karismaerp_local`.`tbrp_retur_penjualan_header` 
ADD COLUMN `is_revisi` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status_retur`;
```

---

### Deskripsi Field:
- **`is_revisi` = 0**: Retur Penjualan reguler (memiliki saldo retur untuk memotong tagihan faktur dan memposting jurnal otomatis).
- **`is_revisi` = 1**: Retur Penjualan revisi (hanya perbaikan administratif, saldo retur di-set 0 dan tidak memotong faktur/saldo kas).
