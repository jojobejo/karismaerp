# Hasil Reset Seluruh Transaksi KarismaERP

Tanggal pelaksanaan: 7 Oktober 2026  
Database: `kiucoid_karismaerp_local`

## Backup Pemulihan

Sebelum reset dibuat backup penuh:

`outputs/database-backup/kiucoid_karismaerp_local_sebelum_reset_transaksi_20261007.sql`

- Ukuran: 13.516.126 byte.
- SHA-256: `45DF1E5E2501AF66BDC6319D8D3AAE040FF2FA6D65F1283E9731F130D894DEC0`.
- Backup sudah diuji dengan restore ke database sementara.
- Hasil restore uji sesuai: 5 jurnal, 3 LPB, 1 faktur penjualan, dan 1 closing.
- Database sementara sudah dihapus setelah verifikasi.

## Cakupan Reset

Skrip yang dijalankan:

`docs/database/reset_seluruh_transaksi_dan_periode_20261007.sql`

Sebanyak 128 tabel transaksi dikosongkan, meliputi:

- sales order, faktur, delivery order, loading, dan log penjualan;
- purchase order, transaksi purchasing, serta data temporary purchasing;
- LPB, batch, detail, adjustment, revisi, log, dan faktur pajak LPB;
- retur penjualan dan retur pembelian;
- pembayaran customer/supplier, kas masuk, kas keluar, dan kasir;
- jurnal, detail jurnal, log, posting exception, dan nomor dokumen;
- closing, snapshot, validation run, reopen, log periode, dan periode fiskal;
- stock ledger, stock batch, mutasi, stock hold, saldo awal, dan daily stock;
- transaksi stock opname;
- transaksi konsinyasi dan transaksi assembly/request bundling.

## Data yang Dipertahankan

- Master barang: 5.739 baris.
- Master customer: 8.104 baris.
- Master supplier: 258 baris.
- Chart of Accounts: 497 akun.
- User: 216 baris pada `tb_users`.
- Gudang: 6 baris.
- Master satuan, formula, mapping akun, klasifikasi akun, dan konfigurasi aplikasi.

## Hasil Verifikasi

- Seluruh 128 tabel target mempunyai jumlah baris `0`.
- `FOREIGN_KEY_CHECKS` sudah kembali aktif (`1`).
- Pemeriksaan tabel inti LPB, PO, SO, faktur, jurnal, periode fiskal, dan stock ledger menghasilkan status `OK`.
- Tidak dibuat periode fiskal baru. Pengguna harus membuat periode awal melalui menu Jurnal sebelum memulai transaksi.

## Cara Pemulihan

Jika reset perlu dibatalkan, hentikan input transaksi baru lalu restore backup:

```powershell
mysql -uroot kiucoid_karismaerp_local < outputs\database-backup\kiucoid_karismaerp_local_sebelum_reset_transaksi_20261007.sql
```

Restore akan mengembalikan kondisi database tepat sebelum reset dan akan menimpa data pada tabel yang terdapat di backup. Lakukan hanya setelah membuat backup tambahan atas kondisi terbaru.
