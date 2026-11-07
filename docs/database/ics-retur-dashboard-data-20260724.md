# Database - Dashboard Retur ICS

Tanggal: 2026-07-24

## Scope

Dokumentasi database untuk perubahan sajian tabel route `ics/retur`.

## Perubahan Schema

Tidak ada perubahan struktur database dan tidak ada migrasi baru.

## Tabel Yang Dibaca

1. `tb_retur_pembelian`
   - Header retur pembelian LPB final.
   - Field utama: `id_retur_pembelian`, `no_retur_pembelian`, `id_lpb`, `kd_po`, `no_po`, `kd_supplier`, `tanggal_retur`, `status`, `total_dpp`, `total_ppn`, `grand_total`, `created_at`.

2. `tb_retur_pembelian_detail`
   - Detail item retur pembelian.
   - Dipakai untuk menghitung jumlah item per header.

3. `tb_lpb`
   - Dipakai untuk mengambil `nomor_lpb` berdasarkan `id_lpb`.

4. `tbpo_suplier`
   - Dipakai untuk menampilkan nama supplier berdasarkan `kd_supplier`.

5. `tbrp_retur_penjualan_header`
   - Header retur penjualan baru jika tabel berisi data.
   - Field utama: `id_retur`, `no_retur`, `tanggal_retur`, `no_spr`, `no_faktur_potong`, `nama_customer`, `kd_customer`, `status_retur`, `create_at_retur`.

6. `tbrp_retur_penjualan_detail`
   - Detail retur penjualan baru.
   - Dipakai untuk jumlah item dan estimasi total nilai dari `qty_retur * harga_satuan`.

7. `tb_retur_barang`
   - Header retur legacy ICS.
   - Tetap dibaca agar data lama tidak hilang dari dashboard.

8. `tb_detail_retur_barang`
   - Detail retur legacy ICS.
   - Dipakai untuk menghitung jumlah item data lama.

## Data Lokal Saat Validasi

- `tb_retur_barang`: 8 header.
- `tb_detail_retur_barang`: 21 detail.
- `tb_retur_pembelian`: 4 header.
- `tb_retur_pembelian_detail`: 4 detail.
- `tbrp_retur_penjualan_header`: 0 header.

## Catatan

Karena tidak ada schema baru, deployment hanya membutuhkan update code aplikasi. Jika environment tujuan belum memiliki tabel retur pembelian, `C_Ics::dash_retur()` akan memanggil `M_ReturPembelian::ensure_schema()` sebelum query dashboard berjalan.
