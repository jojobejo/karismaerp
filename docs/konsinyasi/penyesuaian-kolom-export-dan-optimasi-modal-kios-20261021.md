# Penyesuaian Export Barang Laku & Optimasi Modal Rincian Kios Konsinyasi

**Tanggal**: 21 Oktober 2026  
**Modul**: Purchasing — Konsinyasi (`purchasing/konsinyasi`)  
**Status**: Implemented / Resolved  

---

## 1. Latar Belakang & Kebutuhan
1. **Penghilangan Kolom No. Faktur pada Export Excel**:
   - Pada halaman **Penyelesaian Konsinyasi** tab Barang Laku (`status=LAKU`), saat mengekspor data ke Excel, kolom `No. Faktur` (faktur penjualan kios) tidak diperlukan oleh tim Purchasing karena fokus pelunasan hutang konsinyasi adalah hubungan ke supplier (No. LPB, No. PO, Invoice Supplier, dsb).
2. **Penghilangan Kolom No. Faktur pada Modal Rincian Kios**:
   - Pada modal **Rincian Titipan di Kios** (`modalTrackingKios`), kolom `No. Faktur K` dihilangkan agar tabel lebih ringkas, fokus pada posisi stok kios dan PO asal.
3. **Optimasi Tampilan Modal Jika Data Kios Sangat Banyak**:
   - Jika jumlah kios bertambah banyak (puluhan hingga ratusan titik kios), tanpa pembatasan vertikal modal akan memanjang ke bawah tak terbatas sehingga merusak viewport dan menyulitkan navigasi.

---

## 2. Solusi yang Diterapkan

### A. Controller `C_Konsinyasi::export_settlement_excel`
- Kolom `No. Faktur` ditiadakan secara kondisional saat mengekspor data dengan status `LAKU`.
- Penghitungan koordinat kolom Excel (A s/d W) dan styling cell dilakukan secara dinamis menggunakan helper PhpSpreadsheet `Coordinate::stringFromColumnIndex()`.
- Kolom format angka (Qty dan Nilai Rupiah) diposisikan secara otomatis sesuai indeks kolom aktif.

### B. View `settlement_list.php` (Modal Rincian Titipan di Kios)
1. **Peniadaan Kolom No. Faktur K**:
   - Menghapus header kolom `No. Faktur K` dan baris kolomnya di tabel data.
   - Menyesuaikan `colspan` footer total menjadi 4 kolom.
2. **Internal Scroll & Sticky Positioning**:
   - Modal dialog ditambahkan class `modal-dialog-scrollable`.
   - Tabel dibungkus dalam container `.kios-scroll-wrapper` dengan batasan `max-height: 480px` dan `overflow-y: auto`.
   - Header tabel (`thead th`) diberi properti `position: sticky; top: 0; z-index: 5;` sehingga tetap terlihat saat user men-scroll ke bawah.
   - Footer total (`tfoot td`) diberi properti `position: sticky; bottom: 0; z-index: 5;` agar akumulasi total titipan dan laku selalu terlihat tanpa harus men-scroll sampai akhir.
3. **Widget Ringkasan Cepat (Quick Stat Badges)**:
   - Menampilkan total titik kios, total titipan di kios, dan total laku di bagian atas tabel.
4. **Live Instant Search (Client-Side Search)**:
   - Disediakan kolom input pencarian instan `#kiosModalFilterInput`.
   - Pengguna dapat mengetik nama kios, nomor SO, nomor LPB, atau lot, dan tabel akan menyaring baris secara realtime tanpa request ulang ke server.
