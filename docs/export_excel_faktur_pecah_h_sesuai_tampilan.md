# Dokumentasi Fitur Export Excel Faktur Pecahan (Kode H) Sesuai Tampilan

## 1. Ringkasan Fitur
Fitur ini memungkinkan pengguna mengekspor daftar **Faktur Pecahan (Kode H)** ke file Excel (.xlsx) dengan data yang **presisi sesuai dengan baris yang sedang tampil di halaman** web pada tab `Faktur Pecahan Kode H` (`#content-faktur-h`).

Fitur ini menjawab kebutuhan operasional berikut:
1. **Penyesuaian Jumlah Baris (Page Length)**: Ketika pengguna memilih opsi tampilan data (misalnya: 100 baris atau Semua), maka file Excel yang diunduh berisi persis 100 faktur pecahan tersebut.
2. **Penyaringan / Pencarian (Search)**: Ketika pengguna mencari kata kunci tertentu di kotak pencarian tabel (misalnya mencari No. Faktur Induk `ZINV2810260001` yang menghasilkan 10 faktur pecahan), maka hasil export Excel hanya akan memuat 10 faktur pecahan tersebut.
3. **Penyelarasan Cetak Dot-Matrix LX-310**: Tombol cetak semua faktur H juga diselaraskan agar dapat mencetak faktur pecahan yang sedang tersaring pada tabel.

---

## 2. File yang Dimodifikasi

1. **View**: [pecah_faktur_list.php](file:///c:/laragon/www/karismaerp/application/views/content/sales/pecah_faktur_list.php)
   - Menambahkan atribut `data-id-pecah` pada setiap baris `<tr>` tabel `#tableFakturH`.
   - Mengubah tombol Export Excel menjadi tombol interaktif ber-ID `#btnExportFakturH`.
   - Menambahkan opsi pagination lengkap pada DataTables: `[10, 25, 50, 100, 250, 500, "Semua"]`.
   - Mengimplementasikan fungsi `getVisibleFakturHIds()` yang mengambil ID faktur pecahan yang berada pada halaman aktif (`dt.rows({ page: 'current' }).nodes()`).
   - Menyertakan auto-activation tab berdasarkan hash URL (misal: `#content-faktur-h`).
   - Menambahkan indikator visual loading pada tombol saat proses unduh sedang berlangsung.

2. **Controller**: [C_SalesOrder.php](file:///c:/laragon/www/karismaerp/application/controllers/sales/C_SalesOrder.php) (Method `export_faktur_pecah()`)
   - Mendukung penerimaan parameter `id_pecah_list` atau `id_pecah` via POST maupun GET (baik berupa array maupun comma-separated string).
   - Menambahkan klausa query `$this->db->where_in('p.id_pecah', $ids)` jika daftar ID dikirimkan.
   - Menyelaraskan urutan baris di Excel menggunakan MariaDB `FIELD(p.id_pecah, ...)` agar urutannya konsisten dengan urutan baris di tabel web pengguna.
   - Mengoptimalkan perhitungan periode tanggal laporan berdasarkan data faktur yang diexport.

---

## 3. Cara Penggunaan

1. Buka modul Pecah Faktur di URL:
   `https://localhost/karismaerp/sales_order/pecah_faktur#content-faktur-h`
2. Klik tab **Faktur Pecahan Kode H**.
3. **Skenario A (Ubah Jumlah Baris)**:
   - Pada dropdown *Tampilkan data*, pilih opsi **100**.
   - Klik tombol **Export Excel (.xlsx)**.
   - File Excel akan terunduh berisi data 100 faktur pecahan yang tampil di halaman tersebut.
4. **Skenario B (Pencarian Faktur Induk / Kata Kunci)**:
   - Pada kolom pencarian tabel, ketik nomor faktur induk (contoh: `ZINV2810260001`).
   - Tabel akan menyaring dan menampilkan faktur pecahan yang berkaitan (misal: 10 faktur).
   - Klik tombol **Export Excel (.xlsx)**.
   - File Excel yang terunduh hanya memuat rincian barang dari 10 faktur pecahan hasil pencarian tersebut.
