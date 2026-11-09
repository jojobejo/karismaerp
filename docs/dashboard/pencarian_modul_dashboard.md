# Dokumentasi Fitur: Pencarian Cepat Modul Dashboard (Dashboard Module Search)

## 1. Ringkasan Fitur
Fitur **Pencarian Cepat Modul Dashboard** diletakkan pada baris navigasi utama Dashboard KARISMA ERP (`https://localhost/karismaerp/dashboard`), **sejajar dengan tombol tab kategori departemen** (`ADMIN`, `KEUANGAN`, `HRD`, `LOGISTIK`, `PURCHASING`, `IT`, `SALES`) di sisi kanan.

---

## 2. Tata Letak & Desain (Layout & Design)
- **Baris Navigasi (`.dashboard-nav-bar`)**:
  - **Sisi Kiri**: Tombol tab departemen (`.dashboard-tabs`).
  - **Sisi Kanan**: Kotak pencarian modul (`.dashboard-search-wrapper`) dengan tinggi serasi `50px`, border radius `8px`, placeholder bersih (`Cari modul...`), dan efek focus glowing warna biru khas Karisma (`#127fad`).
- **Responsif**:
  - Pada layar besar (desktop): Tabs dan search box berada di satu baris sejajar.
  - Pada tablet/ponsel: Layout beradaptasi secara fleksibel (*stacked vertical*) agar tetap nyaman digunakan di perangkat layar sentuh.

---

## 3. Fitur Utama & Keunggulan
1. **Real-time / Instant Client-Side Search**:
   - Pencarian berjalan instan saat pengguna mengetik kata kunci tanpa reload halaman.
2. **Pencarian Lintas Modul & Lintas Departemen**:
   - Mencocokkan:
     - Judul modul (e.g. *Kas Bon*, *Faktur Penjualan*, *Data LPB*).
     - Deskripsi modul.
     - Rute/URL modul (e.g. *sales_order*, *lpb_manual*).
     - Label kategori / departemen asal (e.g. *Keuangan*, *Logistik*, *Purchasing*).
3. **Deduplikasi Cerdas**:
   - Modul yang ada di beberapa departemen (seperti *Kas Bon*) dideduplikasi menjadi satu tile dengan badge kategori multi-departemen (`KEUANGAN · HRD · LOGISTIK · ...`).
4. **Dukungan Shortcut Keyboard (Power-User)**:
   - **`Ctrl + K`** atau **`/`**: Otomatis fokus ke kolom pencarian modul.
   - **`Enter`**: Membuka langsung modul pertama dari hasil pencarian tanpa sentuh mouse.
   - **`Escape` (Esc)**: Mengosongkan pencarian dan kembali ke panel tab aktif.
5. **Navigasi Terintegrasi**:
   - Saat pengguna mengetik di kolom pencarian, hasil pencarian muncul di panel bawah.
   - Jika pengguna mengklik salah satu tab departemen di sebelah kiri, sistem otomatis mereset pencarian dan langsung menampilkan modul-modul di tab yang dipilih.

---

## 4. File Terkait
- **View**: `application/views/content/dashboard/index.php`
- **Dokumentasi**: `docs/dashboard/pencarian_modul_dashboard.md`
