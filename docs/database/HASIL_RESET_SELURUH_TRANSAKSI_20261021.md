# Hasil Reset Seluruh Transaksi KarismaERP

Tanggal pelaksanaan: 21 Oktober 2026  
Database: `kiucoid_karismaerp_local`

## Hasil

Reset transaksi selesai dan seluruh 130 tabel target terverifikasi memiliki jumlah baris `0`.

Cakupan reset meliputi:

- purchase order, LPB, dan seluruh detail serta log terkait;
- sales order, faktur penjualan, delivery order, dan loading;
- jurnal pembelian, jurnal penjualan, detail jurnal, dan log jurnal;
- retur pembelian dan retur penjualan;
- pembayaran, kas, periode fiskal, dan closing;
- ledger, batch, mutasi, saldo, dan jejak stok transaksional;
- konsinyasi, termasuk alokasi HPP dan stok batch LPB konsinyasi;
- bundling serta transaksi stock opname.

Skrip yang dijalankan:

`docs/database/reset_seluruh_transaksi_dan_periode_20261007.sql`

Skrip tersebut diperbarui agar turut mengosongkan:

- `tb_konsinyasi_cogs_allocation`;
- `tb_konsinyasi_lpb_batch_stock`.

## Backup Pemulihan

Backup penuh sebelum reset:

`outputs/database-backup/kiucoid_karismaerp_local_sebelum_reset_transaksi_20261021_144726.sql`

- Ukuran: 23.790.810 byte.
- SHA-256: `1426C404CD5049CE1C35D7C3466CFAB8B7852D63E3CFAD052C55D8A8856846E0`.
- Backup berhasil diuji dengan restore ke database sementara.
- Jumlah LPB, PO, SO, faktur, jurnal, dan alokasi konsinyasi pada hasil restore sama dengan sumber.
- Database sementara sudah dihapus setelah verifikasi.

## Data Master yang Dipertahankan

Jumlah berikut tidak berubah sebelum dan sesudah reset:

| Data master | Tabel | Jumlah |
|---|---|---:|
| Barang purchasing | `tbpo_barang` | 7.966 |
| Customer | `tb_customer` | 8.104 |
| Supplier purchasing | `tbpo_suplier` | 307 |
| Chart of Accounts | `tbkeu_akun` | 497 |
| Pengguna | `tb_users` | 215 |
| Gudang | `tb_gudang` | 9 |

Konfigurasi, formula, mapping akun, jenis jurnal, dan data master lain tidak dihapus.

## Verifikasi Teknis

- Seluruh 130 tabel target: kosong.
- `FOREIGN_KEY_CHECKS`: aktif (`1`).
- Data master pembanding: tidak berubah.
- `AUTO_INCREMENT` tabel target direset oleh perintah `TRUNCATE`.

## Cara Pemulihan

Hentikan input transaksi baru sebelum melakukan pemulihan. Jalankan dari PowerShell:

```powershell
mysql -uroot kiucoid_karismaerp_local -e "source C:/laragon/www/karismaerp/outputs/database-backup/kiucoid_karismaerp_local_sebelum_reset_transaksi_20261021_144726.sql"
```

Pemulihan akan menimpa struktur dan data tabel dengan kondisi tepat sebelum reset. Buat backup tambahan terlebih dahulu jika sudah ada transaksi baru setelah reset.
