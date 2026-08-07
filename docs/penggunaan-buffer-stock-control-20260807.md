# Panduan Penggunaan Modul Buffer Stock Control - KARISMA ERP

**Tanggal**: 7 Agustus 2026  
**Akses URL**: `/stock/buffer`  

---

## 1. Latar Belakang & Manfaat Fitur
Modul **Buffer Stock Control** dirancang untuk membantu tim Purchasing, Logistik, dan Manajer Gudang dalam memantau kesehatan stok barang di gudang secara real-time. Dengan modul ini, Anda dapat:
- Mengetahui barang yang stok fisiknya sudah menipis atau kosong.
- Mendapatkan **Rekomendasi Jumlah Pembelian Ulang (Reorder)** secara otomatis dalam satuan **Box** dan **Pcs**.
- Menggunakan sistem **Live Nesting Filter** tanpa perlu menekan tombol submit manual.

---

## 2. Cara Mengakses Modul
1. Buka aplikasi KARISMA ERP.
2. Buka menu **Stock Control** (`/stock`) atau langsung ketikkan rute `/stock/buffer` pada peramban web.
3. Klik tombol **Buffer Stock Control** bertanda ikon peringatan ⚠️ di bagian atas halaman Stock Control.

---

## 3. Fitur Live Nesting Filter (Tanpa Tombol Terapkan Filter)
Halaman ini menggunakan mekanisme **Nesting Live Reload**:
- **Filter Gudang**: Memilih gudang akan langsung memperbarui data secara otomatis.
- **Status Alert Buffer**: Memilih status (Critical, Under Buffer, Warning, Safe) langsung memfilter tabel seketika.
- **Pencarian Otomatis**: Mengetikkan kata kunci pada kolom pencarian akan langsung memfilter data saat Anda mengetik.
- **Pintasan Kartu KPI**: Anda juga dapat mengeklik kartu KPI di bagian atas (misal: kartu *Critical* atau *Under Buffer*) untuk langsung menerapkan filter terkait secara instan!

---

## 4. Cara Memperbarui Target Minimum Buffer Stock (Pcs)
1. Pada tabel data, temukan barang yang ingin diubah target minimumnya.
2. Klik ikon pensil ✏️ di sebelah kolom **Min Buffer**.
3. Masukkan angka target minimum stok baru (dalam satuan Pcs) pada jendela pop-up.
4. Klik **Simpan Perubahan**. Data status alert dan rekomendasi reorder akan langsung ter-update secara otomatis!

---

## 5. Cara Ekspor Laporan Reorder Buffer (CSV / Excel)
1. Atur filter gudang, status alert, atau pencarian sesuai kebutuhan laporan Anda.
2. Klik tombol **Export CSV** di pojok kanan atas halaman.
3. File laporan CSV/Excel akan terunduh secara otomatis dan siap dikirimkan ke bagian Purchasing atau Supplier.
