# Dokumentasi Database - LPB Print, Detail PO, dan Urutan Laporan

Tanggal: 2026-08-07

## Status Migrasi

Tidak ada migrasi database baru.

Perubahan aplikasi memakai kolom dan tabel yang sudah tersedia pada alur LPB/PO saat ini.

## Tabel dan Kolom yang Dipakai

1. `tb_lpb`
   - `id_lpb`
   - `kd_po`
   - `no_po`
   - `nosj`
   - `tgl_sj`
   - `no_invoice`
   - `checker_name`
   - `checker_by`
   - `checker_at`
   - `input_at`
   - `keterangan`

2. `tb_lpb_detail`
   - `id_detail_lpb`
   - `id_lpb`
   - `kd_barang`
   - `qty_diterima`
   - `harga_satuan`
   - `total_harga`
   - `harga_verified_by`
   - `harga_verified_at`

3. `tb_lpb_log`
   - `id_lpb`
   - `action_type`
   - `dilakukan_oleh`

4. `tbpo_detail_po`
   - `no_po`
   - `kd_po`
   - `kd_barang`
   - `qty`
   - `qty_kecil`
   - `isi`
   - `kemasan`
   - `satuan`
   - `hrg_satuan`
   - `harga_satuan_kecil`
   - `harga_satuan_exclude`
   - `harga_satuan_kecil_exclude`
   - `keterangan_harga_ppn`

5. `tbpo_po`
   - `no_po`
   - `kd_po`
   - `tax`
   - `keterangan_harga_ppn`

6. `tbpo_barang`
   - `kode_barang`
   - `nama_barang`
   - `isi`
   - `kemasan`

## Aturan Data

- Harga satuan exclude diprioritaskan dari field exclude PO yang sudah tersimpan agar konsisten dengan alur `kiu_po`.
- Harga satuan include ditampilkan dari harga include tersimpan saat mode PO adalah include. Jika mode PO exclude, include dihitung dari exclude + tax PO.
- Qty Kg hanya ditampilkan pada baris dengan satuan PO Kg.
- Qty Ltr hanya ditampilkan pada baris dengan satuan PO Ltr.
- Nilai Kg/Ltr dihitung dari `qty_kecil / (kemasan / 1000)` sesuai kontrak detail PO yang sudah berjalan.
- Nama Purchasing pada print LPB bersumber dari verifikasi harga LPB detail, bukan dari status lifecycle LPB.

## Dampak Database

- Tidak ada `ALTER TABLE`.
- Tidak ada perubahan tipe data.
- Tidak ada perubahan index.
- Tidak ada perubahan data existing.
