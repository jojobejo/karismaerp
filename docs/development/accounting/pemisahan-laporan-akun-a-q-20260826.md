# Dokumentasi Pemisahan Akun A dan Akun Q pada Laporan Neraca & Laba Rugi

**Tanggal**: 26 Agustus 2026  
**Modul**: Keuangan / Jurnal & Laporan Finansial (`jurnal/neraca` dan `jurnal/laba-rugi`)  
**Penulis**: Tim Pengembang Karisma ERP  

---

## 1. Latar Belakang & Kebutuhan
Sebelumnya, halaman Laporan Neraca (`jurnal/neraca`) dan Laporan Laba Rugi (`jurnal/laba-rugi`) menampilkan seluruh akun secara terkonsolidasi tanpa opsi pemisahan antara akun entitas/kelompok **Q** dan akun entitas/kelompok **A**. 

Untuk memudahkan audit, pemantauan performa operasional divisi/entitas masing-masing, serta penyusunan laporan keuangan terpisah, dibutuhkan tombol pemisah yang memungkinkan pengguna memfilter:
1. **Semua Akun (Gabungan)**: Menampilkan seluruh data akun seperti sebelumnya (konsolidasi).
2. **Akun Q**: Hanya menampilkan akun-akun kelompok Q (akun berawalan `Q `, `Q-`, atau prefix kode `Q`).
3. **Akun A**: Hanya menampilkan akun-akun kelompok A (akun berawalan `A `, `A-`, atau prefix kode `A`).

---

## 2. Rincian Perubahan Teknis

### A. Library `Accounting_service.php`
- **File**: `application/libraries/Accounting_service.php`
- Menambahkan parameter `$accountGroup = ''` pada method:
  - `public function reports($report, $dateFrom, $dateTo, $accountId = 0, $accountGroup = '')`
  - `private function report_by_statement($dateFrom, $dateTo, $statement, $accountGroup = '')`
- Pada method `report_by_statement`:
  - Jika `$accountGroup === 'A'`: Query SQL menambahkan filter `(a.nama_akun LIKE 'A %' OR a.nama_akun LIKE 'A-%' OR a.nama_akun LIKE 'A/%' OR a.nama_akun LIKE 'A.%' OR a.kode_akun LIKE 'A%')`.
  - Jika `$accountGroup === 'Q'`: Query SQL menambahkan filter `(a.nama_akun LIKE 'Q %' OR a.nama_akun LIKE 'Q-%' OR a.nama_akun LIKE 'Q/%' OR a.nama_akun LIKE 'Q.%' OR a.kode_akun LIKE 'Q%')`.
  - Jika `$accountGroup === 'ALL'` atau kosong `''`: Query menampilkan seluruh akun tanpa batasan kelompok.

### B. Controller `C_Keuangan.php`
- **File**: `application/controllers/keuangan/C_Keuangan.php`
- Pada method `jurnal_financial_report($type)`:
  - Menangkap parameter `account_group` dari query string GET.
  - Memvalidasi nilai input (hanya menerima `ALL`, `A`, `Q`; default ke `ALL`).
  - Meneruskan nilai filter ke pemanggilan `reports($type, $dateFrom, $dateTo, 0, $filterGroup)` dan `reports('laba_rugi', $dateFrom, $dateTo, 0, $filterGroup)`.
  - Mengirimkan variabel `account_group` ke view untuk rendering tombol aktif dan navigasi.
  - Menambahkan catatan audit dinamis ketika filter kelompok akun sedang aktif.

### C. View `jurnal_laporan.php`
- **File**: `application/views/content/keuangan/jurnal_laporan.php`
- Menempatkan filter kelompok akun (**Pilih Kelompok Akun**) secara sejajar di samping **Awal Laba/Rugi Berjalan** (atau Tanggal Awal) dan **Cut-off Neraca** (atau Tanggal Akhir) dalam grid form filter.
- Menggunakan elemen `<select>` dropdown (`Semua Akun (Gabungan)`, `Akun Q`, `Akun A`) yang terintegrasi langsung dalam `<form method="get">` sehingga filter tanggal dan kelompok akun tersubmit secara serentak via tombol **Tampilkan**.
- Menyesuaikan tautan tombol perpindahan tab Neraca <-> Laba Rugi di bagian kanan atas agar membawa parameter `date_from`, `date_to`, dan `account_group` yang sedang aktif.

---

## 3. Cara Penggunaan
1. Buka menu **Keuangan** -> **Laporan Neraca** (`jurnal/neraca`) atau **Laporan Laba Rugi** (`jurnal/laba-rugi`).
2. Pada panel **Filter Laporan**, pilih opsi pada **Pilih Kelompok Akun**:
   - **Semua Akun (Gabungan)** untuk melihat keseluruhan akun.
   - **Akun Q** untuk menyaring akun-akun entitas/divisi Q.
   - **Akun A** untuk menyaring akun-akun entitas/divisi A.
3. Atur rentang tanggal pada input **Awal Laba/Rugi Berjalan** / **Tanggal Awal** dan **Cut-off Neraca** / **Tanggal Akhir**.
4. Klik tombol **Tampilkan** untuk memuat data laporan sesuai kombinasi kelompok akun dan rentang tanggal yang dipilih.
5. Saat berpindah antara tab **Neraca** dan **Laba Rugi**, status kelompok akun yang dipilih tetap tersinkronisasi.
