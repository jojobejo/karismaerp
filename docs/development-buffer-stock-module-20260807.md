# Dokumentasi Pengembangan Modul Buffer Stock Control - KARISMA ERP

**Tanggal**: 7 Agustus 2026  
**Modul**: Buffer Stock Control & Restock Warning (`stock/C_Stock`)  
**Arsitektur**: Opsi 1 (Native Stock Module Integration dengan ERP Ledger Real-time)  

---

## 1. Latar Belakang & Fitur Nesting Auto-Reload
Modul **Buffer Stock Control** dikembangkan untuk memantau ambang batas stok minimum secara real-time. Berdasarkan penyesuaian kebutuhan pengguna, filter toolbar dibuat secara **Nesting Live Auto-Reload** tanpa memerlukan tombol manual "Terapkan Filter":
- **Live Filtering**: Perubahan pada dropdown Gudang, dropdown Status Alert, maupun input Pencarian (Search) secara otomatis memicu pembaruan data summary dan tabel.
- **Card Shortcut Click**: Mengeklik widget KPI Summary (Critical, Under Buffer, Warning, Reorder) secara otomatis mengatur filter status alert dan memuat data yang sesuai.

---

## 2. Struktur Route (`application/config/routes.php`)
Di bawah grup rute `stock/`:
- `GET /stock/buffer` -> `stock/C_Stock/buffer`
- `GET /stock/buffer/summary` -> `stock/C_Stock/buffer_summary`
- `GET /stock/buffer/items` -> `stock/C_Stock/buffer_items`
- `GET /stock/buffer/export` -> `stock/C_Stock/buffer_export`
- `POST /stock/buffer/update-minimum` -> `stock/C_Stock/buffer_update_min`

---

## 3. Logika Perhitungan & Rumus Data
Data dihitung secara dinamis dari tabel master barang (`tbpo_barang`) dan ledger pergerakan stok (`tberp_stock_ledger`):

1. **Stok Available (Bebas Siap Jual)**:
   $$\text{qty\_available} = \text{GREATEST}(\text{qty\_on\_hand} - \text{qty\_reserved}, 0)$$
2. **Defisit Buffer (Pcs)**:
   $$\text{defisit} = \max(0, \text{stock\_minimum} - \text{qty\_available})$$
3. **Rekomendasi Reorder (Box & Pcs)**:
   $$\text{reorder\_box} = \lceil \frac{\text{defisit}}{\text{isi\_per\_box}} \rceil$$
   $$\text{reorder\_total\_pcs} = \text{reorder\_box} \times \text{isi\_per\_box}$$
4. **Status Alert Level**:
   - 🔴 `CRITICAL`: `qty_available <= 0`
   - 🟠 `UNDER_BUFFER`: `qty_available <= stock_minimum`
   - 🟡 `WARNING`: `qty_available <= (stock_minimum * 1.2)`
   - 🟢 `SAFE`: `qty_available > (stock_minimum * 1.2)`

---

## 4. Perubahan Source Code

### A. Route Config ([routes.php](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/config/routes.php))
Menambahkan rute URL `/stock/buffer`, `/stock/buffer/summary`, `/stock/buffer/items`, `/stock/buffer/export`, dan `/stock/buffer/update-minimum`.

### B. Model ERP Stock ([M_Stock.php](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/models/M_Stock.php))
- `get_buffer_summary($filters)`: Menghitung KPI agregasi barang Critical, Under Buffer, Warning, Safe, serta Total Defisit Pcs & Rekomendasi Box.
- `get_buffer_rows($filters)`: Query data terpadu `tbpo_barang`, `tb_suplier`, dan snapshot `tberp_stock_ledger` ter-filter gudang, supplier, dan status alert.
- `update_stock_minimum($kdBarang, $stockMinimum)`: Fungsi update target buffer minimum pada tabel `tbpo_barang`.

### C. Controller ([C_Stock.php](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/controllers/stock/C_Stock.php))
- `buffer()`: Menampilkan view utama Buffer Stock.
- `buffer_summary()`: Menyuplai data JSON KPI Summary.
- `buffer_items()`: Menyuplai data JSON Datatables dengan pagination.
- `buffer_update_min()`: Handler AJAX untuk memperbarui nilai `stock_minimum`.
- `buffer_export()`: Membuat dan mengunduh laporan Buffer Stock dalam format CSV/Excel.

### D. View UI ([stock_buffer.php](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/views/content/stock/stock_buffer.php))
- Layout filter nesting live (3 kolom sejajar) tanpa tombol Terapkan Filter.
- Debounced search input & event change otomatis.
- Interactive KPI cards untuk pintasan filter.
- Modal dialog untuk edit langsung target minimum buffer stock.

---

## 5. Hasil Verifikasi & Uji Coba
- **Rute URL**: Berhasil diakses melalui `base_url('stock/buffer')`.
- **Live Filtering Test**: Filter merespons secara seketika (*instant auto-reload*) saat opsi diubah.
- **Navigasi**: Ditambahkan tombol akses cepat "Buffer Stock Control" di halaman utama [stock_control.php](file:///Applications/XAMPP/xamppfiles/htdocs/karismaerp/application/views/content/stock/stock_control.php).
