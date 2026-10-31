# Reset Transaksi Database 31 Oktober 2026

## Tujuan

Mengosongkan seluruh transaksi operasional agar pencatatan dapat dimulai kembali dari awal, tanpa menghapus data master.

## Database

- Database: `kiucoid_karismaerp_local`
- Lingkungan: MySQL lokal Laragon
- Skrip: `db/reset_transaksi.sql`

## Cakupan yang dikosongkan

- Purchase order, pembelian, dan permintaan pembelian.
- LPB, revisi LPB, retur pembelian, dan penyesuaian harga LPB.
- Sales order, faktur, faktur pecah, delivery order, retur penjualan, dan distribusi.
- Pembayaran, kas masuk, kas keluar, kasir, kasbon, dan saldo awal akun.
- Jurnal, detail jurnal, log jurnal, periode/closing, dan proses reopen.
- Seluruh ledger, batch, saldo awal, opname, mutasi, hold, dan cache stok.
- Konsinyasi, test market, serta transaksi perakitan/pembongkaran bundling.

## Data yang dipertahankan

- Pengguna, karyawan, hak akses, dan menu.
- Pelanggan, pemasok, barang, gudang, satuan, dan lokasi.
- Chart of accounts, pemetaan akun, klasifikasi akun, dan konfigurasi akuntansi.
- Formula bundling dan konfigurasi operasional lainnya.
- Log akses dan data modul nontransaksi di luar cakupan reset.

## Pemulihan

Sebelum reset dijalankan, seluruh database dicadangkan ke:

`outputs/database-backup/kiucoid_karismaerp_local_sebelum_reset_final_20261031_144435.sql`

Ukuran dump final adalah 28.136.314 byte. Untuk memulihkan, impor berkas tersebut ke database `kiucoid_karismaerp_local` setelah memastikan database tujuan sudah benar.

## Hasil verifikasi

- Seluruh 164 tabel transaksi yang menjadi sasaran reset memiliki 0 baris.
- Tampilan stok menghasilkan total `qty_in = 0`, `qty_out = 0`, dan `qty_ready = 0`.
- Pemeriksaan foreign key kembali aktif setelah reset.
- Jumlah data master utama tetap sama dengan kondisi sebelum reset.
