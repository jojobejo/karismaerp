# Hasil Reset Seluruh Transaksi Database KarismaERP — 25 November 2026

## Tujuan

Mengosongkan seluruh data transaksi operasional, akuntansi, dan persediaan pada database lokal agar pencatatan transaksi dapat dimulai kembali dari awal (`0`), dengan tetap mempertahankan seluruh data master serta periode fiskal akuntansi yang sedang aktif.

## Informasi Pelaksanaan

- **Tanggal Pelaksanaan**: 25 November 2026
- **Database**: `kiucoid_karismaerp_local` (MySQL Lokal Laragon)
- **Skrip SQL Reset**: `docs/database/reset_seluruh_transaksi_20261125.sql`
- **Jumlah Tabel Dikosongkan**: `165` tabel transaksional (`TRUNCATE TABLE`, `AUTO_INCREMENT` kembali ke awal)

## Cakupan Modul yang Dikosongkan

1. **Pembelian (Purchasing)**:
   - Purchase Order dagang & non-komersil (`tbpo_po`, `tbpo_detail_po`, `tbpo_po_nk`, `tbpo_detail_po_nk`, `tbpo_realisasi_*`), Pre-PO, tracking PO, catatan PO direktur/pembelian, request pembelian, dan tabel temporary pembelian.
2. **Penerimaan Barang (LPB)**:
   - Header, detail, dan batch LPB (`tb_lpb`, `tb_lpb_detail`, `tb_lpb_batch`), invoice LPB (`tb_lpb_invoice`, `tb_lpb_invoice_detail`), pengajuan revisi LPB, penyesuaian harga LPB, log LPB, serta faktur pajak LPB (`tblpb_faktur_pajak`).
3. **Retur Pembelian**:
   - Header, detail, dan log retur pembelian (`tb_retur_pembelian`, `tb_retur_pembelian_detail`, `tb_retur_pembelian_log`).
4. **Penjualan, Logistik & Distribusi**:
   - Sales Order & approval (`tbso_sales_order`, `tbso_sales_order_detail`, `tbso_so_approval`, `tbso_approval_harga`, `tbso_cancel_*`, `tbso_stock_reservation`).
   - Faktur penjualan, faktur pecah Z/H, import faktur H, dan log faktur (`tbso_faktur_penjualan`, `tbso_faktur_detail`, `tbso_faktur_z_pecah*`, `tbso_faktur_h_import*`, `tbso_faktur_jurnal`, `tbso_faktur_log`).
   - Delivery Order (DO), Delivery Trip, Loading Dalam/Luar Kota, Bongkaran Checker, Tracking Driver, Pengajuan OD, dan Test Market (`tb_test_market_position`, `tb_test_market_realisasi`).
5. **Retur Penjualan**:
   - Retur penjualan & SPR baru maupun legacy (`tbrp_retur_penjualan_header`, `tbrp_retur_penjualan_detail`, `tbrp_spr_header`, `tbrp_spr_detail`, `tbrp_activity_log`, `tb_retur_barang`, `tb_detail_retur_barang`).
6. **Pembayaran, Kas & Jurnal Akuntansi**:
   - Pembayaran piutang/hutang & alokasi (`tbkeu_pembayaran`, `tbkeu_pembayaran_faktur`, `tbkeu_pembayaran_alokasi`).
   - Kas masuk, kas keluar, transaksi kasir, saldo kasir, dan kasbon.
   - Jurnal umum/otomatis, detail jurnal, log jurnal, penomoran dokumen, saldo awal akun, serta riwayat tutup buku/reopen (`tbkeu_jurnal`, `tbkeu_jurnal_detail`, `tbkeu_jurnal_log`, `tbkeu_closing_*`, `tbkeu_reopen_*`).
7. **Persediaan / Stok, Konsinyasi & Bundling**:
   - Seluruh stok batch & kartu stok (`tberp_stock_batch`, `tberp_stock_ledger`), mutasi gudang, saldo awal stok, hold stok, stock opname, konsinyasi masuk/settlement/alokasi HPP, serta request & perakitan/pembongkaran paket bundling.

## Data Master & Konfigurasi yang Dipertahankan

Seluruh data master dan pengaturan sistem tidak dihapus dan siap digunakan langsung:

| Kategori Master / Konfigurasi | Tabel Utama | Jumlah Baris |
|---|---|---:|
| Master Barang Purchasing & Akun Barang | `tbpo_barang` / `tbpo_barang_akun` | 7.966 / 7.978 |
| Master Barang Gudang / Katalog | `tb_master_barang` / `tb_barangv2` | 5.739 / 5.738 |
| Master Pelanggan (Customer) | `tb_customer` | 8.104 |
| Master Pemasok (Supplier) | `tbpo_suplier` / `tb_suplier` | 307 / 258 |
| Bagan Akun (Chart of Accounts) | `tbkeu_akun` | 497 |
| Mapping Akun & Jenis Jurnal | `tbkeu_mapping_akun` / `tbkeu_jenis_jurnal` | 30 / 26 |
| Periode Fiskal Aktif (`2026-07` Semester 2) | `tbkeu_periode_fiskal` | 1 (`OPEN`) |
| Pengguna, Gudang & Satuan | `tb_users` / `tb_gudang` / `tb_satuan` | 221 / 24 / 23 |
| Formula Bundling | `tberp_bundling_formula` / `detail` | 2 / 6 |
| Template Cetak Faktur & Setting Approval Retur | `tb_faktur_print_template` / `tb_retur_approval_setting` | 1 / 1 |

## Backup & Cara Pemulihan (Rollback)

Sebelum pengosongan dijalankan, backup penuh database (termasuk routines, triggers, dan events) telah disimpan pada:

- **Lokasi File**: `outputs/database-backup/kiucoid_karismaerp_local_sebelum_reset_transaksi_20261125.sql`
- **Ukuran File**: `27.821.403` byte (~26,5 MB)
- **SHA-256**: `27DD2F59729DB3DF37A7EC8BCA4D9B7354629888F91B87AA7DFD1AFB304C7361`

Apabila sewaktu-waktu diperlukan pemulihan ke kondisi sebelum reset, jalankan perintah berikut melalui PowerShell:

```powershell
Get-Content "c:\laragon\www\karismaerp\outputs\database-backup\kiucoid_karismaerp_local_sebelum_reset_transaksi_20261125.sql" -Raw | & "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root kiucoid_karismaerp_local
```

## Hasil Verifikasi Akhir

- `165` tabel target transaksi & stok terverifikasi memiliki `0` baris.
- View stok komersial (`tberp_stok_batch`) memiliki `qty_on_hand = 0` dan `qty_reserved = 0`.
- View stok non-komersial (`v_stockbarangnk`) memiliki `qty_in = 0`, `qty_out = 0`, dan `qty_ready = 0`.
- `FOREIGN_KEY_CHECKS` kembali aktif (`1`).
