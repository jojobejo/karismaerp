# Database Guard UNPOST LPB Jika Barang Sudah Terjual

## Ringkasan

Perubahan ini tidak membutuhkan migrasi database dan tidak menambah kolom atau tabel baru.

## Tabel Yang Dibaca

- `tb_lpb`
- `tb_lpb_detail`
- `tbso_faktur_penjualan`
- `tbso_faktur_detail`
- `tb_detail_do`

## Relasi Validasi

Guard UNPOST LPB membaca detail LPB berdasarkan `id_lpb`, lalu mencocokkan batch dengan transaksi penjualan aktif berdasarkan:

- `kd_barang`
- `no_lot`
- `expired_date`
- `gudang_id` bila tersedia pada faktur penjualan modern

Faktur modern dianggap aktif jika `tbso_faktur_penjualan.status` bukan `cancelled`.

Untuk alur lama, `tb_detail_do.status = 4` dipakai sebagai penanda barang sudah keluar/terjual.

## Dampak Schema

Tidak ada perubahan schema database.

Kesimpulan: tidak perlu menjalankan SQL migration untuk development ini.

