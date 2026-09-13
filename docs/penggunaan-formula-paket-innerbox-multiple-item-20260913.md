# Dokumentasi Teknis & Penggunaan Formula Paket Bundling (Innerbox Multi-Item) — KarismaERP

**Tanggal:** 13 September 2026  
**Aplikasi:** KarismaERP (CodeIgniter 3)  
**Status:** Selesai Diimplementasikan & Lolos Uji  

---

## 1. Latar Belakang & Masalah
Sebelumnya, sistem pembuatan Formula Paket Bundling belum mampu merepresentasikan paket yang memiliki kemasan bertingkat (*Outer/Master Box* $\rightarrow$ *Innerbox* $\rightarrow$ *Beragam Barang Berbeda di Dalam 1 Innerbox*).  
Hal ini dikarenakan konfigurasi innerbox diletakkan per baris komponen individu, bukan pada level konfigurasi paket (header formula).

Akibatnya:
- Ketika membuat paket seperti **Paket Jitu (20 Innerbox x 1 Box)** yang berisi 1 Ltr Roundup + 1 Ltr Spontas + 1 Pcs Kaos Jitu di setiap innerbox, pengguna harus mencentang dan mengetik angka 20 berulang-ulang di tiap barang.
- Kalkulasi kebutuhan pada saat request 250 paket hanya mencatat 250 pcs (seharusnya $250 \times 20 = 5.000\text{ pcs}$).

---

## 2. Struktur Data & Konsep Hierarki Baru

```text
1 PAKET BUNDLING (Master Box / Outer Box)
  ├── Kode: PKT-JITU-01
  ├── Nama: Paket Jitu
  ├── Kemasan Innerbox: AKTIF
  ├── Jumlah Innerbox: 20 Box
  │
  └── KOMPOSISI ISI DALAM 1 INNERBOX:
        ├── Roundup 1 Ltr = 1 pcs   ==> (Otomatis: 1 × 20 = 20 pcs per Paket)
        ├── Spontas 1 Ltr = 1 pcs   ==> (Otomatis: 1 × 20 = 20 pcs per Paket)
        └── Kaos Jitu     = 1 pcs   ==> (Otomatis: 1 × 20 = 20 pcs per Paket)
```

### Rumus Perhitungan:
1. **Total Isi Barang per 1 Paket**:
   $$\text{Qty per Paket} = \text{Jumlah Innerbox} \times \text{Isi dalam 1 Innerbox}$$
2. **Total Kebutuhan Request**:
   $$\text{Total Innerbox} = \text{Qty Request Paket} \times \text{Jumlah Innerbox}$$
   $$\text{Total Kebutuhan Komponen} = \text{Qty Request Paket} \times \text{Qty per Paket}$$

*Contoh Kasus*:  
Membuat **250 Paket Jitu** (20 Innerbox):
- Total Kemasan Innerbox = $250 \times 20 = 5.000\text{ Innerbox}$
- Total Kebutuhan Roundup = $250 \times 20 = 5.000\text{ Ltr}$
- Total Kebutuhan Spontas = $250 \times 20 = 5.000\text{ Ltr}$
- Total Kebutuhan Kaos Jitu = $250 \times 20 = 5.000\text{ Pcs}$

---

## 3. Perubahan Kode & Database

### A. Database
Kolom baru ditambahkan pada tabel header secara non-destructive:
1. `tberp_bundling_formula`:
   - `is_innerbox TINYINT(1) DEFAULT 0`
   - `jumlah_innerbox DECIMAL(15,3) DEFAULT 0.000`
   - `satuan_innerbox VARCHAR(50) DEFAULT 'Innerbox'`
2. `tberp_bundling_request`:
   - `is_innerbox TINYINT(1) DEFAULT 0`
   - `jumlah_innerbox DECIMAL(15,3) DEFAULT 0.000`
   - `total_innerbox DECIMAL(15,3) DEFAULT 0.000`
   - `satuan_innerbox VARCHAR(50) DEFAULT 'Innerbox'`

### B. Model `M_Bundling.php`
- `ensure_bundling_schema()`: Otomatis memastikan kolom header formula dan request tersedia.
- `save_formula($data, $details)`: Menyimpan konfigurasi innerbox header dan menghitung `qty_komponen = jumlah_innerbox * isi_per_innerbox` untuk tiap detail.
- `create_request($data, $details, $user)`: Menyimpan konfigurasi innerbox pada header request dan menghitung total kebutuhan fisik serta total innerbox secara akurat.

### C. Controller `C_BundlingRequest.php`
- `formula_save()`: Menangkap konfigurasi `is_innerbox`, `jumlah_innerbox`, `satuan_innerbox` dan menyusun mapping detail.
- `save()`: Menangkap konfigurasi innerbox saat membuat request bundling baru.

### D. View `formula_list.php`
- Modal form dilengkapi Switch Kemasan Innerbox yang interaktif.
- Input Jumlah Innerbox per 1 Paket dengan kalkulasi reaktif instan ke semua baris barang.
- Kolom "Isi dalam 1 Innerbox" dan preview "Total Isi / 1 Paket" yang otomatis ter-update saat angka diubah.
- Live pratinjau kartu ringkasan struktur paket.
- Modal Edit Formula otomatis memuat kembali konfigurasi innerbox tersimpan.

### E. View `request_create.php`
- Saat memilih Formula yang menggunakan innerbox, otomatis menampilkan banner info kemasan:  
  `20 Innerbox / Paket`.
- Saat mengisi Qty Request (misal 250 Box), otomatis mengkalkulasi dan menampilkan:  
  `Total Kebutuhan Kemasan: 5.000 Box` dan total fisik masing-masing komponen 5.000 unit.

---

## 5. Penyajian Informasi Hierarki Kemasan pada Logistik & Purchasing

Untuk menyajikan informasi kemasan yang elegan dan transparan bagi Logistik dan Purchasing:
1. **Banner Visual Hierarki Kemasan (`monitoring_detail.php`, `request_detail.php`, `assembly_create.php`)**:
   - Menampilkan 3 kartu alur pembungkusan fisik yang bersih:
     - **Langkah 1 (Kardus Kecil / Innerbox)**: Rincian barang racikan campuran yang dibungkus per innerbox (misal: 1 Roundup + 1 Spontas + 1 Kaos).
     - **Langkah 2 (Kardus Luar / Master Box)**: Jumlah kardus kecil yang dimasukkan ke dalam 1 master box (misal: 20 kardus kecil / master box).
     - **Langkah 3 (Total Sasaran Perakitan)**: Total sasaran permintaan (misal: 10 Master Box = setara 200 Kardus Kecil).
   - Tampilan bersih tanpa kotak alert peringatan teks panjang yang mengganggu estetika antarmuka.

2. **Pemisahan Kolom Tabel Transparan**:
   - Kolom **Isi / 1 Innerbox (Kardus Kecil)**: `1,00 Ltr` / `1,00 Pcs`.
   - Kolom **Isi / 1 Master Box (Kardus Luar)**: `20,00 Ltr` ($20 \times 1\text{ Ltr}$).
   - Kolom **Total Kebutuhan Target**: `200,00 Ltr` (untuk 10 Master Box / 200 Innerbox).

3. **Kalkulator Dinamis pada Perakitan Logistik (`assembly_create.php`)**:
   - Saat petugas logistik memasukkan kuantitas perakitan parsial (misal 5 Master Box), indikator langsung menghitung: *"Setara dengan perakitan 100 kardus kecil (innerbox)"* secara otomatis dan *real-time*.

---

## 6. Metode Estimasi Modal HPP: LIFO (Last In, First Out)

Sesuai kebutuhan penetapan harga pokok paket bundling di KarismaERP, estimasi modal dihitung menggunakan metode **LIFO (Last In First Out)** yang mengambil alokasi dari riwayat pembelian / penerimaan barang (**LPB / PO**) yang **terakhir dibeli**:

### Alur Kerja Algoritma LIFO:
1. Sistem mengambil daftar riwayat pembelian barang dari `tb_lpb_detail` & `tbpo_detail_po` yang diurutkan secara terbalik kronologis (`ORDER BY id_detail_lpb DESC` / tanggal terbaru).
2. Kuantitas kebutuhan dialokasikan mulai dari transaksi pembelian yang paling baru (terakhir dibeli).
3. Jika kuantitas pembelian terakhir telah habis teralokasi, sisa kebutuhan dialokasikan ke transaksi pembelian sebelumnya secara bertahap.
4. Total modal dihitung dari akumulasi harga beli aktual setiap batch yang teralokasi.
5. HPP LIFO per unit dihitung dari:
   $$\text{HPP Satuan LIFO} = \frac{\text{Total Modal Terhitung}}{\text{Total Kuantitas Kebutuhan}}$$

#### Contoh Simulasi Nyata:
- Total kebutuhan Roundup: 450 unit.
- Riwayat beli:
  - Batch 1 (Awal): 200 unit @ Rp 5.000
  - Batch 2 (Terakhir): 300 unit @ Rp 6.000
- Alokasi LIFO:
  - 300 unit diambil dari Batch 2 @ Rp 6.000 = Rp 1.800.000
  - 150 unit diambil dari Batch 1 @ Rp 5.000 = Rp 750.000
  - Total Modal = Rp 2.550.000
  - HPP Satuan Rata-rata LIFO = Rp 5.666,67 / unit.

---

## 7. Fitur Input Manual Biaya Kemasan & Modal Printilan (HPP Komprehensif)

Untuk mengetahui berapa nilai **Estimasi Modal / HPP per 1 Paket yang sesungguhnya** (tidak hanya HPP bahan baku barang, namun juga termasuk biaya kardus pembungkus dan printilan lainnya), sistem kini dilengkapi dengan fitur input manual biaya kemasan:

### Komponen Biaya Kemasan & Printilan:
1. **Biaya Kardus Innerbox (`biaya_innerbox`)**:
   - Biaya kardus kecil per 1 pcs innerbox (misal: Rp 1.500 / innerbox).
   - Dikalikan otomatis dengan jumlah innerbox per paket:
     $$\text{Biaya Innerbox per 1 Paket} = \text{Jumlah Innerbox} \times \text{Biaya per Innerbox}$$
     *(Contoh: $20 \times \text{Rp } 1.500 = \text{Rp } 30.000$)*.
2. **Biaya Kardus Luar / Master Box (`biaya_outerbox`)**:
   - Biaya kardus master box utama pembungkus luar per paket (misal: Rp 12.000 / paket).
3. **Modal Printilan Lain (`biaya_kemasan_lain`)**:
   - Biaya perlengkapan tambahan per paket seperti: stiker hologram garansi, lakban segel, plastik wrapping, dsb (misal: Rp 8.000 / paket).
   - Dilengkapi catatan rincian deskripsi printilan (`keterangan_biaya_kemasan`).

### Rumus Grand Total HPP Lengkap per 1 Paket:
$$\text{Total Biaya Kemasan per Paket} = \text{Biaya Innerbox per Paket} + \text{Biaya Outerbox} + \text{Biaya Kemasan Lain}$$
$$\text{Grand Total HPP Lengkap / 1 Paket} = \text{HPP Bahan Baku (LIFO)} + \text{Total Biaya Kemasan per Paket}$$
$$\text{Grand Total Modal Seluruh Request} = \text{Grand Total HPP Lengkap / 1 Paket} \times \text{Qty Request Paket}$$

#### Contoh Kasus Nyata (Request #3 - Paket Jitu 20 x 1 Box, Qty: 10 Box):
- **HPP Bahan Baku (LIFO)**: Rp 3.087.387,39 / paket
- **Kardus Innerbox**: 20 pcs $\times$ Rp 1.500 = Rp 30.000,00
- **Kardus Outerbox**: Rp 12.000,00
- **Printilan Lain (Stiker Hologram & Lakban)**: Rp 8.000,00
- **Total Biaya Kemasan per Paket**: Rp 30.000 + Rp 12.000 + Rp 8.000 = **Rp 50.000,00 / paket**
- **Grand Total HPP Lengkap / 1 Paket**: Rp 3.087.387,39 + Rp 50.000,00 = **Rp 3.137.387,39 / paket**
- **Grand Total Modal 10 Paket**: $10 \times \text{Rp } 3.137.387,39 = \mathbf{\text{Rp } 31.373.873,88}$

### Titik Akses Fitur:
1. **Master Formula (`/purchasing/bundling/formula`)**:
   - Form Tambah / Edit Formula memiliki tabel repeater biaya kemasan dinamis: dimulai dengan 1 baris inputan awal dan tombol **"+ Tambah Biaya / Printilan"** untuk menambah baris tanpa batas.
2. **Buat Request Baru (`/purchasing/bundling/request/create`)**:
   - Form pembuatan request memiliki tabel repeater biaya kemasan dinamis dengan live kalkulasi ke total paket request.
   - Jika memilih formula tersimpan, rincian biaya kemasan otomatis terisi dari master formula.
3. **Detail Request Purchasing (`/purchasing/bundling/detail/3`)**:
   - Menampilkan 4 kartu ringkasan biaya (HPP Bahan LIFO, Biaya Kemasan & Printilan, Total HPP Lengkap 1 Paket, Total Modal Request).
   - Menampilkan panel rincian biaya kemasan beserta tabel detail item kemasan dan tombol **"Input / Ubah Biaya Kemasan & Printilan"**.
   - Modal form menggunakan tabel repeater dinamis (1 baris awal default + tombol tambah baris baru + tombol hapus per baris + live preview kalkulasi HPP dan Total Modal).
4. **Monitoring Detail Logistik (`/logistik/bundling/detail/3`)**:
   - Menampilkan panel informasi kebutuhan fisik kemasan dari rincian dinamis yang telah diinput (innerbox, master outerbox, stiker hologram, dll) yang harus disiapkan tim perakitan gudang.

---

## 8. Fitur Input Biaya Kemasan & Printilan Kustom (Dynamic Repeater)
Untuk memberikan fleksibilitas maksimal kepada tim Purchasing dan Logistik:
- **Input Fleksibel & Kustom**: Tidak lagi dibatasi hanya 3 kolom statis. Sistem kini menyediakan **1 baris inputan awal** (Default: Nama Kemasan/Printilan & Nominal Biaya per 1 Paket Rp).
- **Tombol Tambah Baris**: Pengguna dapat menambahkan baris baru tanpa batas kapan saja sesuai kebutuhan (misal: Kardus Innerbox, Kardus Outerbox, Stiker Hologram Garansi, Lakban Segel QC, Plastik Wrapping, Bubble Wrap, dll).
- **Penyimpanan Terstruktur**: Rincian baris disimpan dalam format JSON pada kolom `rincian_biaya_kemasan` di tabel `tberp_bundling_request` dan `tberp_bundling_formula`.
- **Kalkulasi Live & Realtime**: Perubahan nominal langsung mengupdate live preview total biaya kemasan, estimasi HPP lengkap 1 paket, dan estimasi total modal seluruh paket.

---

## 9. Hasil Validasi & Pengujian
Semua skenario pengujian berhasil 100%:
1. Paket 20 innerbox x 3 barang @ 1 pcs: 1 Paket = 20 pcs tiap barang.
2. Ubah jumlah innerbox menjadi 50: Otomatis 1 Paket = 50 pcs tiap barang.
3. Ubah kuantitas barang dalam innerbox (Roundup 2, Spontas 1, Kaos 1): Roundup = 40 pcs, Spontas = 20 pcs, Kaos = 20 pcs.
4. Simulasi 250 Paket Jitu: 5.000 Innerbox & 5.000 pcs kebutuhan bahan baku.
5. Perhitungan LIFO teruji presisi 100% pada simulasi 450 unit (total Rp 2.550.000, HPP Rp 5.666,67).
6. Request #3 (`RPB-20260913-0001`): Total modal LIFO bahan baku terhitung Rp 30.873.873,88 (HPP Bahan Rp 3.087.387,39).
7. Input manual biaya kemasan & printilan dinamis (4 item: Kardus Innerbox Rp 30.000, Outerbox Rp 12.000, Stiker Hologram Rp 5.000, Lakban Segel Rp 3.000 = Total Rp 50.000 / paket) terintegrasi sempurna: Total HPP Lengkap per 1 Paket menjadi **Rp 3.137.387,39** dan Total Modal Request 10 Box menjadi **Rp 31.373.873,88**.
8. Pengujian menyisakan 1 baris inputan saja berhasil tersimpan dan terhitung akurat.
9. Alert peringatan teks panjang berhasil dihilangkan dari seluruh tampilan, menghasilkan antarmuka yang bersih, elegan, dan profesional.



