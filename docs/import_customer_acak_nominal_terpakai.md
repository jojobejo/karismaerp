# Import Master Customer Acak + Nominal Terpakai Awal

## Tujuan
Menyimpan nominal historis yang **sudah terpakai** per kontak person customer acak (dari Excel), sehingga sisa limit Rp 250.000.000 di halaman `sales_order/customer_acak` dan proses alokasi Faktur H menjadi akurat.

## Migrasi Database (DB `kiucoid_karismaerp_admkeu`)
```sql
ALTER TABLE tb_customer_acak
  ADD COLUMN nominal_terpakai_awal DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER npwp;
```

## Rumus Pemakaian
`total terpakai = nominal_terpakai_awal + SUM(Faktur H non-cancelled)`
Diterapkan di `M_SalesOrder::get_customers_acak_by_kios()`, `get_all_customers_acak()`, dan `check_kontak_person_limit()`.

## Cara Import
```
php db/import_customer_acak.php "path/CUST_ACAK.xlsx" --dry-run   # simulasi
php db/import_customer_acak.php "path/CUST_ACAK.xlsx"             # eksekusi
```
Format kolom: No | Kode | Nama Toko | Kontak Person | Alamat | Kota | NIK | NPWP | Nominal Terpakai.
- Kode sudah ada → `nominal_terpakai_awal` di-update (ditimpa, bukan ditambah).
- Kode baru → di-insert; `kd_customer_induk` diambil dari toko yang sama di master acak, atau fallback `tb_customer.nama_kios`.

## Hasil Import 29-10-2026 (CUST_ACAK_2.xlsx)
- 1.609 customer diperbarui nominalnya, 9 customer baru (ALAM05AC, ALME08AC, FATH27–31AC, TOPA01–02AC).
- Total nominal terpakai: Rp 101.539.761.408; 31 kontak sudah mencapai limit 250 juta.

