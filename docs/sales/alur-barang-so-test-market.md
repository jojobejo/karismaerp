# Dokumentasi Alur: Sales Order Barang Test Market

## 1. Konsep & Alur Transaksi
Modul: **Sales Order (SO) & Test Market**
Checklist: **Barang Test Market** (`is_test_market = 1`)

### Rangkaian Alur Barang:
1. **Pembuatan SO (`/sales_order/create`)**:
   - Status SO: `Draft`.
   - Stok fisik masih berada di **Gudang Asal (Gudang Induk)**.
   - Kuantitas pesanan berstatus **direservasi** (`qty_reserved`) agar tidak dapat dipesan oleh transaksi lain.

2. **Rekam SO (`/sales_order/rekam_so`)**:
   - Status SO berubah dari `Draft` $\rightarrow$ `Open`.
   - **Mutasi Barang ke Gudang Test Market**:
     - Sistem secara otomatis memanggil `M_TestMarket::transfer_so_to_test_market()`.
     - Stok fisik berkurang dari Gudang Asal (`tberp_stock_ledger` tipe `OUT` dengan referensi `MUTASI TEST MARKET`).
     - Stok fisik bertambah dan direservasi di **`Gudang Test Market`** (ID: 17) (`tberp_stock_ledger` tipe `IN` & `RESERVE`).
     - Lokasi gudang SO dialihkan ke Gudang Test Market sehingga proses pembuatan faktur bersumber dari Gudang Test Market.

3. **Penerbitan Faktur Penjualan (Faktur T)**:
   - Nomor Faktur otomatis berawalan **`T`** (contoh: `TINV...`).
   - Stok dikeluarkan dari Gudang Test Market.
   - Posisi barang berpindah ke **`Gdg. Test Market di Kios`** (ID: 16) dan dicatat pada `tb_test_market_position` dengan status `DI_KIOS`.
   - Tidak menghasilkan jurnal penjualan komersial maupun piutang dagang (plafon pelanggan tidak terpotong).

4. **Pembatalan SO (Jika Terjadi Pembatalan)**:
   - Jika SO Test Market yang sudah direkam dibatalkan (`cancelled`), sistem memanggil `M_TestMarket::reverse_transfer_so_test_market()` untuk mengembalikan mutasi stok dari Gudang Test Market kembali ke Gudang Induk.

