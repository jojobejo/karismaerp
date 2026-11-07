# Dokumentasi Fitur: Filter Tanggal Modul Penyelesaian Konsinyasi

## 1. Ringkasan Fitur
Penambahan filter periode tanggal (`date_from` dan `date_to`) pada modul **Penyelesaian & Tagihan Supplier Barang Konsinyasi** (`https://karismaerp.test/purchasing/konsinyasi?tab=penyelesaian`).

Fitur ini memungkinkan bagian Purchasing untuk:
- Memfilter daftar barang konsinyasi laku berdasarkan rentang tanggal penjualan / settlement.
- Mempermudah rekonsiliasi dan verifikasi transaksi harian, bulanan, maupun periode custom.
- Menggunakan shortcut tombol filter cepat (*Hari Ini*, *Bulan Ini*, *Bulan Lalu*, *Semua Tanggal*).
- Menjaga persistensi filter tanggal saat berpindah sub-tab status (*Menunggu Tagihan Supplier*, *Riwayat Barang Lunas*, *Semua*).

---

## 2. Komponen yang Dimodifikasi

### A. View: [`application/views/content/purchasing/konsinyasi/settlement_list.php`](file:///c:/laragon/www/karismaerp/application/views/content/purchasing/konsinyasi/settlement_list.php)
1. **Input Filter Tanggal**:
   - Menambahkan input field HTML5 `<input type="date" name="date_from">` (Dari Tanggal) dan `<input type="date" name="date_to">` (Sampai Tanggal).
   - Menambahkan tombol shortcut periode cepat: *Hari Ini*, *Bulan Ini*, *Bulan Lalu*, dan *Semua Tanggal*.
2. **Navigasi & Sub-Tab**:
   - Mempertahankan query parameter `date_from` dan `date_to` pada navigasi tab utama dan sub-tab filter status (`LAKU`, `BILLED`, `SEMUA`).
3. **Card Header**:
   - Menampilkan badge info rentang tanggal aktif (`Awal s/d Sekarang` atau tanggal spesifik terpilih) pada header tabel penyelesaian.
4. **JavaScript**:
   - Fungsi `applyPresetDate(preset)` untuk mengisi nilai input tanggal secara otomatis dan melakukan submit filter.

### B. Controller: [`application/controllers/purchasing/C_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/controllers/purchasing/C_Konsinyasi.php)
- Meneruskan array `$filters` (termasuk `date_from` dan `date_to`) ke `$this->M_Konsinyasi->get_summary_stats($filters)` agar counter badge pada tab sesuai dengan filter yang aktif.

### C. Model: [`application/models/M_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/models/M_Konsinyasi.php)
- Method `get_settlement_list(array $filters = [])` telah memfilter `s.tanggal_settlement >= $filters['date_from']` dan `s.tanggal_settlement <= $filters['date_to']`.
- Method `get_summary_stats(array $filters = [])` diperbarui agar mendukung filter `date_from`, `date_to`, dan `kd_suplier`.

---

## 3. Cara Penggunaan
1. Buka URL: `https://karismaerp.test/purchasing/konsinyasi?tab=penyelesaian&status=LAKU&kd_suplier=SEMUA&search=`
2. Pada bagian filter bar di atas tabel:
   - Pilih **Dari Tanggal** dan **Sampai Tanggal**, lalu klik tombol **Filter**.
   - Atau klik salah satu **Shortcut Periode** (*Hari Ini*, *Bulan Ini*, *Bulan Lalu*).
3. Tabel akan otomatis menampilkan transaksi barang konsinyasi laku dalam rentang tanggal tersebut.
4. Untuk menghapus filter tanggal, klik shortcut **Semua Tanggal** atau tombol **Reset**.
