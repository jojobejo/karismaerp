# Dokumentasi Teknis: Perbaikan Reversal Stok dan Jurnal Penyesuaian Barang

## Informasi
- **Fitur / Modul**: Persediaan > Pemakaian / Penyesuaian Barang (`/persediaan/penyesuaian_barang`)
- **File Terkait**: 
  - `application/models/M_PenyesuaianBarang.php`
  - `application/controllers/keuangan/C_PenyesuaianBarang.php`
- **Tanggal**: 2026-08-22

## Deskripsi Masalah
Sebelumnya, ketika transaksi penyesuaian barang dihapus (`delete`) atau di-unpost (`unpost`), stok fisik dan batch tidak kembali ke keadaan semula. Hal ini terjadi karena:
1. Validasi penghapusan di `delete()` dan `unpost()` mensyaratkan `status === 'POSTED' && !empty(id_jurnal)`. Jika status berstatus DRAFT atau id_jurnal tidak terisi, proses pembalikan stok dilewati.
2. Logika pembalikan stok sebelumnya tidak memeriksa riwayat mutasi di `tberp_stock_ledger`, sehingga jika batch atau lot tidak cocok persis, pembalikan stok gagal diterapkan.
3. Saat mengedit transaksi existing, mutasi lama tidak di-reverse terlebih dahulu sebelum mutasi baru diposting.

## Perbaikan yang Dilakukan
1. **Penerapan Fungsi `reverse_stock_and_journal($data)`**:
   - Memeriksa catatan `tberp_stock_ledger` untuk nomor referensi transaksi tersebut (`ref_type = 'PENYESUAIAN'`).
   - Membalikkan stok batch secara presisi (`ADJIN` dibalik menjadi pengurangan stok, `ADJOUT` dibalik menjadi penambahan stok).
   - Menghapus record mutasi pada `tberp_stock_ledger`.
   - Menghapus jurnal terkait pada `tbkeu_jurnal_log`, `tbkeu_jurnal_detail`, dan `tbkeu_jurnal`.
2. **Penerapan Fungsi `apply_batch_qty_change(...)`**:
   - Menangani update stok batch dengan pencarian cerdas (mencocokkan lot & expired date, fallback jika lot kosong).
   - Melakukan insert batch baru jika batch belum ada.
3. **Penyelarasan pada Method `save()`, `delete()`, dan `unpost()`**:
   - Method `delete()` dan `unpost()` kini secara otomatis memanggil `reverse_stock_and_journal($data)` untuk memastikan stok dan jurnal selalu dibersihkan dan dipulihkan.
   - Method `save()` pada saat edit transaksi existing akan membatalkan (reverse) transaksi lama terlebih dahulu sebelum menerapkan perubahan baru.
