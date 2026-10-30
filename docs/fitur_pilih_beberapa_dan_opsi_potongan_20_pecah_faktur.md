# Dokumentasi Fitur: Pemilihan Beberapa Faktur Z & Opsi Skema Potongan (20% vs Tanpa Potongan) pada Pecah Faktur Kios

## 1. Ringkasan Fitur
Fitur ini memperbarui modul **Pemecahan Faktur Kios** (`sales_order/pecah_faktur_kios/{kd_customer}`), menambahkan kemampuan:
1. **Multi-Select / Pilih Beberapa Faktur Z**:
   - Pengguna dapat memilih satu, beberapa, atau semua Faktur Z yang belum dipecah sekaligus menggunakan checkbox atau tombol **"Pilih Semua"**.
   - Muncul tombol aksi **"Pecah Terpilih (N)"** ketika ada faktur yang dicentang, memungkinkan pemecahan faktur Z terpilih secara serentak ke dalam modal batch.
2. **Opsi Skema Potongan Harga (Pakai Potongan 20% vs Tanpa Potongan)**:
   - Beberapa faktur tidak memerlukan potongan margin/pajak 20% (misalkan faktur tertentu yang dijual dengan nilai penuh 100%).
   - Disediakan radio button pilihan:
     - **Pakai Potongan 20% (Default)**: Harga satuan barang dikalikan 0.8 (potongan margin/pajak 20%).
     - **Tanpa Potongan (100% Penuh)**: Harga satuan barang utuh (faktor 1.0) sama dengan Faktur Z induknya tanpa pemotongan margin 20%.
   - Opsi ini tersedia pada:
     - Modal Pemecahan Faktur Satuan & Batch (`#modalBatchSplit`).
     - Modal Otomatis Pecahkan Semua Faktur Kios (`#modalAutoSplitKios`).
     - Form Review & Distribusi Batch Split (`faktur_split_batch_form.php`).
     - Backend Controller & Model (`C_SalesOrder::split_faktur_batch`, `C_SalesOrder::simpan_split_faktur_batch`, `C_SalesOrder::auto_split_kios`, dan `M_SalesOrder::auto_split_faktur_kios`).

---

## 2. Rincian Teknis Implementasi

### A. View Detail Kios (`application/views/content/sales/pecah_faktur_kios_detail.php`)
1. **Toolbar Atas Tab Faktur Z Belum Dipecah**:
   - Menambahkan tombol `#btnToggleSelectAllFakturZ` ("Pilih Semua").
   - Menambahkan tombol dinamis `#btnPecahSelectedFakturZ` ("Pecah Terpilih (N)") yang muncul saat minimal 1 faktur dicentang.
2. **Tabel Faktur Z Belum Dipecah (`#tableFakturZKiosBelum`)**:
   - Menambahkan kolom ke-0 berupa checkbox master `#checkAllFakturZKios` dan checkbox item `.check-item-faktur-z`.
   - Mengatur DataTables agar kolom ke-0 tidak dapat diurutkan (`class="no-sort"`), dan default sorting menggunakan kolom ke-1 (No Urut).
   - Menjaga sinkronisasi state checkbox saat tabel di-draw ulang (pencarian / pagination).
3. **Modal Pemecahan Faktur Batch (`#modalBatchSplit`)**:
   - Menambahkan radio pilihan `name="pakai_potongan_20"` (1: Potong 20%, 0: Tanpa Potongan).
   - Menghubungkan event listener radio ke JavaScript `recalculateModalValues()`, sehingga perubahan potongan langsung memperbarui nominal potongan, total netto, dan estimasi nilai per faktur pecahan secara real-time.
4. **Modal Pecahkan Semua Kios (`#modalAutoSplitKios`)**:
   - Menambahkan radio pilihan `name="pakai_potongan_20"` (1: Potong 20%, 0: Tanpa Potongan) sebelum sistem melakukan kalkulasi otomatis batas Rp 25 juta.

### B. Controller (`application/controllers/sales/C_SalesOrder.php`)
1. **`split_faktur_batch()`**:
   - Menangkap parameter input `pakai_potongan_20 = ($this->input->post('pakai_potongan_20') !== '0');`.
   - Menghitung harga satuan per barang:
     ```php
     $disc_factor = $pakai_potongan_20 ? 0.8 : 1.0;
     $hrg_jual_h  = round((float)$d['harga_satuan'] * $disc_factor, 2);
     ```
   - Mengirim `$pakai_potongan_20` ke view form review `faktur_split_batch_form`.
2. **`simpan_split_faktur_batch()`**:
   - Menangkap `pakai_potongan_20` dari form hidden POST, menjaga integritas harga netto saat menyimpan faktur H ke database.
3. **`auto_split_kios()`**:
   - Menangkap `pakai_potongan_20` dan meneruskannya ke model `M_SalesOrder::auto_split_faktur_kios()`.

### C. Model (`application/models/M_SalesOrder.php`)
1. **`auto_split_faktur_kios($kd_customer, $max_nominal_per_faktur, $username, $pakai_potongan_20)`**:
   - Menentukan `$disc_factor = $pakai_potongan_20 ? 0.8 : 1.0;`.
   - Menghitung estimasi grand total netto faktur dengan `$disc_factor`.
   - Menghitung harga satuan item faktur H pecahan dengan `$disc_factor`.

### D. View Review & Distribusi Batch (`application/views/content/sales/faktur_split_batch_form.php`)
- Menyimpan nilai pilihan ke hidden input:
  `<input type="hidden" name="pakai_potongan_20" value="<?= !empty($pakai_potongan_20) ? 1 : 0 ?>">`.
- Menyesuaikan badge keterangan harga di tabel dan ringkasan header (menampilkan "Setelah Potong 20%" jika aktif, atau "Tanpa Potongan / 100%" jika non-aktif).

---

## 3. Cara Penggunaan oleh User

### Skenario A: Memilih Beberapa Faktur Z Sekaligus
1. Buka menu **Pecah Faktur Kios**, pilih kios yang dituju (misal: `ABAT01`).
2. Pada tab **Faktur Z Belum Dipecah**:
   - Centang checkbox pada baris Faktur Z yang ingin dipecah, **ATAU** klik tombol **"Pilih Semua"** di bagian atas untuk mencentang semua faktur.
3. Tombol berwarna kuning **"Pecah Terpilih (N)"** akan otomatis muncul.
4. Klik tombol **"Pecah Terpilih (N)"**.
5. Modal pemecahan akan terbuka menampilkan seluruh faktur yang dipilih beserta total kuantitas dan total nominalnya.

### Skenario B: Memilih Pemecahan dengan Potongan 20% atau Tanpa Potongan
1. Pada modal pemecahan faktur (baik untuk 1 faktur, beberapa faktur terpilih, atau pecahkan semua):
   - Pilih skema potongan yang diinginkan pada bagian **Skema Potongan Harga**:
     - **Pakai Potongan 20%**: Standar pemecahan (harga satuan dipotong 20%).
     - **Tanpa Potongan (100% Penuh)**: Harga satuan barang utuh 100% sama dengan Faktur Z asli.
2. Nominal Potongan Pajak/Margin dan Total Netto Pecahan akan berubah otomatis secara real-time mengikuti pilihan Anda.
3. Masukkan jumlah faktur pecahan yang diinginkan, lalu klik **"Lanjut Distribusi Penerima"**.
4. Periksa ringkasan dan alokasi penerima, lalu klik **"Simpan & Terbitkan Faktur Pecahan"**.
