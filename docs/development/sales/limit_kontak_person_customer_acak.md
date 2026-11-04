# Dokumentasi Fitur: Pembatasan Limit Transaksi Faktur H (Maksimal 250 Juta per Kontak Person) & Standar Cetak Faktur Pecahan

## 1. Latar Belakang & Tujuan
Pada transaksi penjualan Karisma ERP, Faktur Z induk dapat dipecah menjadi beberapa Faktur Pecahan bertanda awalan **H** yang didistribusikan ke kontak-kontak person (penerima) acak yang terdaftar di master Customer Acak.

Untuk mematuhi regulasi perpajakan dan batas transaksi per kontak person:
1. **Maksimal akumulasi nominal Faktur H per 1 Kontak Person adalah Rp 250.000.000 (Dua Ratus Lima Puluh Juta Rupiah)**.
2. Jika akumulasi nominal Faktur H seorang kontak person (misalkan Sopian Andiwana) telah mencapai Rp 250.000.000, maka kontak person tersebut **otomatis dinonaktifkan / tidak dapat dipilih lagi** untuk transaksi pecah faktur berikutnya.
3. **Penyembunyian Informasi Induk Saat Cetak Faktur**: Saat Faktur Pecahan (Kode H) dicetak (Print / PDF), seluruh informasi yang mengaitkan transaksi dengan Faktur Z Induk, Sales Order asal, Status Dokumen internal, maupun Kios/Toko Induk **otomatis disembunyikan** dari lembar cetak agar dokumen faktur tampak sebagai Faktur Penjualan mandiri yang sah kepada pihak pembeli/penerima.

---

## 2. Perubahan & Fitur yang Diterapkan

### A. Halaman Master Customer Acak (`sales_order/customer_acak`)
1. **Kolom Baru: Nominal Faktur H**
   - Menampilkan total nominal transaksi Faktur H yang tercatat untuk kontak person tersebut (`Rp xx.xxx.xxx`).
   - Menampilkan jumlah Faktur H yang telah diterbitkan (`x Faktur H`).
   - Menampilkan indikator status limit & progress bar pemakaian terhadap plafon Rp 250 Juta:
     - **Tersedia (250 Jt)**: Jika kontak person belum pernah digunakan (Rp 0).
     - **Progress Bar & Sisa Kuota**: Jika kontak person telah digunakan dan masih di bawah Rp 250 Juta (misal: *Sisa: Rp 230.000.000 (8%)*).
     - **Limit Penuh (250 Jt) - Tidak Dapat Digunakan**: Jika kontak person telah mencapai atau melebihi limit Rp 250 Juta. Baris tabel ditandai dengan highlight merah (`table-danger`).
2. **Statistik Ringkasan di Header**
   - Menampilkan ringkasan total kontak person, total nominal Faktur H yang sudah terfaktur di sistem, dan jumlah kontak person yang telah mencapai limit penuh.

### B. Form Pecah Faktur Manual & Massal (`sales_order/split_faktur` & `split_faktur_batch`)
- Dropdown **Customer Penerima**:
  - Kontak person yang telah mencapai limit Rp 250 Juta dinonaktifkan (`disabled`) dengan label `[LIMIT PENUH: Rp 250.000.000 (Maksimal 250 Jt - Tidak Dapat Digunakan)]`.
  - Kontak person yang belum penuh menampilkan informasi riwayat dan sisa kuota yang tersedia.
- Validasi Server-side:
  - Sebelum faktur dipecah dan disimpan, sistem mengecek nominal alokasi baru. Jika kontak person telah penuh atau penambahan alokasi melebihi sisa kuota limit 250 Juta, pemrosesan akan ditolak dengan notifikasi error yang informatif.

### C. Halaman Detail & Cetak Faktur Pecahan (`sales_order/detail_faktur_pecah/{id}`)
Saat tombol **Cetak Faktur Pecahan** diklik atau halaman dicetak ke printer / PDF (`@media print`):
1. **Informasi yang Dihilangkan / Disembunyikan**:
   - **Keterangan Faktur Z Induk**: Baris referensi Faktur Z disembunyikan.
   - **Dari Sales Order**: Baris referensi nomor Sales Order disembunyikan.
   - **Status Dokumen**: Status internal (misal: *confirmed*) disembunyikan.
   - **Kios / Toko Induk**: Baris nama Kios/Toko induk disembunyikan, dan nama customer penerima ditampilkan bersih tanpa tanda kurung nama toko induk.
   - **Catatan Internal**: Teks referensi otomatis seperti *"Pecahan dari Faktur Z..."* dibersihkan secara otomatis.
   - **Elemen Navigasi & Web**: Sidebar, navbar, footer website Karisma, tombol aksi, breadcrumb, dan alert sistem disembunyikan.
2. **Format Dokumen Cetak Standar**:
   - Kop resmi **PT. KARISMA INDOAGRO UNIVERSAL - FAKTUR PENJUALAN**.
   - No Faktur (Kode H), Tanggal Faktur, Jatuh Tempo, Cara Pembayaran, Salesman.
   - Customer Penerima (Nama Kontak Person, Alamat, Kota, NIK).
   - Tabel Rincian Barang (Barang, Lot, Exp Date, Qty, Harga Satuan, Diskon, Total Harga).
   - Kolom Tanda Tangan: Penerima / Customer, Salesman, dan Hormat Kami.

---

## 3. Detail Teknis
- **Konstanta Batas**:
  - `M_SalesOrder::LIMIT_KONTAK_PERSON_FAKTUR_H = 250000000;`
- **Class CSS Cetak**:
  - `.d-print-none` & `.no-print`: Digunakan untuk menyembunyikan elemen internal saat cetak.
  - `.print-only`: Digunakan untuk menampilkan kop surat dan tanda tangan khusus saat cetak.
- **Berkas Terkait**:
  - View Detail & Cetak: [`faktur_pecah_detail.php`](file:///c:/laragon/www/karismaerp/application/views/content/sales/faktur_pecah_detail.php)
  - Controller: [`C_SalesOrder.php`](file:///c:/laragon/www/karismaerp/application/controllers/sales/C_SalesOrder.php)
  - Model: [`M_SalesOrder.php`](file:///c:/laragon/www/karismaerp/application/models/M_SalesOrder.php)