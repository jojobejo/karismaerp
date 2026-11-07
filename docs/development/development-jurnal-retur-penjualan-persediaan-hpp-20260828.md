# Dokumentasi Teknis — Pembuatan Jurnal Persediaan dan HPP pada Retur Penjualan

## 1. Masalah yang Ditemukan
Pada modul Retur Penjualan (RJP) di halaman Laporan Jurnal Transaksi (`/laporan/keuangan/jurnal-transaksi`), transaksi jurnal retur penjualan (misal `RJP-28082600001`) hanya memunculkan 3 baris akun:
1. `41014` — Q Retur Penjualan BKP (Debit)
2. `21024` — Q PPN K (Debit)
3. `21017` — Q Hutang Non Dagang / Piutang Usaha (Kredit)

Sedangkan baris reversal persediaan:
- `14010` — Persediaan # 1 (Debit)
- `51010` — Harga Pokok Penjualan # 1 (Kredit)
tidak muncul.

## 2. Akar Masalah (Root Cause)
1. **Whitespace pada Referensi Nomor Faktur**:
   - Kolom `no_faktur` pada tabel `tbrp_retur_penjualan_detail` tersimpan dengan leading whitespace / karakter tab (`\tDINV2808260002`).
   - Pada saat pembuatan jurnal di `M_Journal::post_jurnal_retur_penjualan()`, JOIN ke `tbso_faktur_detail` dilakukan secara literal tanpa sanitasi `trim()` sehingga query JOIN gagal mencocokkan data faktur asal dan nilai `fd.hrg_pokok` menjadi `NULL` (0).
2. **Fallback HPP**:
   - Belum adanya fallback bertingkat untuk mengambil harga pokok (`hrg_pokok`) dari faktur penjualan terakhir atau sales order terakhir jika relasi join faktur langsung tidak ditemukan.
   - Karena harga pokok bernilai `0`, kalkulasi nilai persediaan (`$cost_total`) menjadi 0, sehingga baris jurnal Persediaan dan HPP dilewati (`skipped`).

## 3. Solusi dan Perbaikan
1. **Sanitasi Data di Controller & Model**:
   - Menambahkan `trim()` pada kolom `no_faktur`, `kd_barang`, `nama_barang`, `satuan`, dan `no_batch` pada saat penyimpanan detail retur di [`C_ReturPenjualan.php`](file:///c:/laragon/www/karismaerp/application/controllers/sales/C_ReturPenjualan.php).
   - Menambahkan sanitasi `trim()` saat query join di [`M_Journal.php`](file:///c:/laragon/www/karismaerp/application/models/M_Journal.php).
2. **Perbaikan Pengambilan HPP & Mapping Akun**:
   - Menambahkan mekanisme fallback HPP dari faktur detail terakhir (`tbso_faktur_detail`) dan sales order detail (`tbso_sales_order_detail`).
   - Memastikan pengambilan `kode_akun_persediaan` dan `kode_akun_harga_pokok` dari master barang (`tbpo_barang`) atau mapping default per kategori barang.
   - Melakukan insert baris jurnal:
     - **Debit**: Akun Persediaan (misal `14010` Persediaan # 1)
     - **Kredit**: Akun HPP (misal `51010` Harga Pokok Penjualan # 1)
   - Mengupdate total debit dan total kredit header jurnal agar balance.
3. **Pembaruan Data Eksisting**:
   - Menjalankan re-posting jurnal untuk data retur yang sudah berstatus selesai sehingga jurnal `RJP-28082600001` kini memuat 5 baris lengkap secara balance.
