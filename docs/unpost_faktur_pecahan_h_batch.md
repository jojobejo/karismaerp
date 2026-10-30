# Dokumentasi Fitur Unpost Faktur Pecahan (Kode H) — Single & Batch (Massal)

## 1. Masalah Awal & Penyelesaian
1. **Error 404 pada URL `sales_order/hapus_faktur_pecah/{id}`**:
   - **Penyebab**: Belum adanya mapping routing pada `application/config/routes.php` untuk Controller yang berada di subfolder `sales/C_SalesOrder.php`.
   - **Solusi**: Telah ditambahkan route:
     - `$route['sales_order/hapus_faktur_pecah/(:any)'] = 'sales/C_SalesOrder/hapus_faktur_pecah/$1';`
     - `$route['pecah_faktur/hapus/(:any)'] = 'sales/C_SalesOrder/hapus_faktur_pecah/$1';`
     - `$route['sales_order/unpost_faktur_pecah_batch'] = 'sales/C_SalesOrder/unpost_faktur_pecah_batch';`
     - `$route['pecah_faktur/unpost_batch'] = 'sales/C_SalesOrder/unpost_faktur_pecah_batch';`

2. **Dukungan Role Khusus `admkeu`**:
   - Method `unpost_faktur_pecah_batch` telah didaftarkan ke whitelist method yang diizinkan untuk pengguna dengan role `admkeu`.

---

## 2. Fitur Unpost Massal (Batch) & Pilih Semua
Pada tab **Faktur Pecahan Kode H** (`#content-faktur-h`), pengguna kini dapat:
1. **Unpost 1 Faktur Saja**:
   - Klik tombol **Unpost** pada kolom Aksi pada baris faktur pecahan yang diinginkan.
   - Konfirmasi dialog pembatalan.
   - Kuantitas barang otomatis dikembalikan ke Faktur Z Induk.
2. **Pilih Beberapa Faktur Tertentu**:
   - Centang checkbox pada baris faktur pecahan yang ingin di-unpost.
   - Tombol **Unpost Terpilih (N)** otomatis muncul di toolbar bagian atas.
3. **Pilih Semua Faktur Sekaligus**:
   - Klik checkbox master pada header tabel, atau klik tombol **Pilih Semua** di toolbar.
   - Seluruh faktur pecahan yang tampil di halaman (termasuk yang tersaring lewat kolom Search) akan otomatis tercentang.
4. **Eksekusi Unpost Massal**:
   - Klik tombol **Unpost Terpilih (N)**.
   - Sistem menampilkan pesan konfirmasi.
   - Seluruh detail barang dan header faktur pecahan yang dipilih dihapus secara transaksional (`DB Transaction`).
   - Apabila seluruh pecahan dari Faktur Z Induk telah di-unpost, status `is_split_parent` pada Faktur Z Induk otomatis dikembalikan ke `0` (Belum Dipecah).
   - Aktivitas unpost dicatat ke tabel log aktivitas (`tb_log_activity`).

---

## 3. File yang Dimodifikasi
1. [application/config/routes.php](file:///c:/laragon/www/karismaerp/application/config/routes.php)
2. [application/controllers/sales/C_SalesOrder.php](file:///c:/laragon/www/karismaerp/application/controllers/sales/C_SalesOrder.php)
3. [application/views/content/sales/pecah_faktur_list.php](file:///c:/laragon/www/karismaerp/application/views/content/sales/pecah_faktur_list.php)
