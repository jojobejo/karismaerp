# Reset Data Konsinyasi 14 Oktober 2026

## Tujuan

Mengosongkan seluruh transaksi dan persediaan konsinyasi pada database lokal agar proses input pembelian, distribusi, penjualan, pembayaran, dan retur konsinyasi dapat dimulai kembali dari awal.

## Data yang Dibersihkan

- PO dan LPB berjenis konsinyasi.
- SO dan faktur dari gudang konsinyasi.
- Pembayaran serta faktur realisasi konsinyasi.
- SPR dan retur penjualan bertipe konsinyasi.
- Settlement, batch FIFO, dan alokasi HPP konsinyasi.
- Jurnal yang bersumber dari transaksi konsinyasi.
- Stok batch dan ledger Gudang Konsinyasi, Gudang Titipan di Kios, serta Gudang Konsinyasi Lunas.

## Data yang Dipertahankan

- Master barang.
- Master supplier.
- Master customer.
- Master akun.
- Definisi Gudang Konsinyasi ID 13, Gudang Titipan di Kios ID 14, dan Gudang Konsinyasi Lunas ID 15.
- Seluruh transaksi non-konsinyasi.

## Backup

Backup penuh sebelum reset tersimpan di:

`outputs/backup_sebelum_reset_konsinyasi_20261014_karismaerp.sql`

## Hasil Validasi

Setelah reset, jumlah data pada PO/LPB konsinyasi, SO/faktur konsinyasi, pembayaran konsinyasi, SPR/retur konsinyasi, settlement, faktur konsinyasi, batch FIFO, alokasi HPP, stok gudang konsinyasi, ledger, dan jurnal konsinyasi adalah nol.
