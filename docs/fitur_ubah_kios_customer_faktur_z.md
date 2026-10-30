# Dokumentasi Fitur: Ubah Kios / Customer Faktur Z (Koreksi Salah Input Kode Customer Saat Import)

## 1. Latar Belakang Masalah
Ketika pengguna mengimpor Faktur Z via file Excel (`sales_order/import_faktur_z`), terdapat kemungkinan kesalahan pengetikan kolom `kd_customer` (kode customer/kios). Akibatnya:
- Faktur Z tersebut salah masuk ke daftar kios/toko lain.
- Sebelumnya, jika hal ini terjadi, pengguna harus menghapus data atau mengimpor ulang keseluruhan file Excel yang memakan waktu dan berisiko duplikasi data.

## 2. Solusi yang Diterapkan
Sistem kini dilengkapi fitur **"Ubah Kios / Customer Faktur Z" (Pindahkan Kios)** tanpa perlu menghapus atau mengimpor ulang data dari awal.

Fitur ini dapat digunakan dalam 3 cara:
1. **Pindah Kios Satuan (Per Faktur Z)**:
   - Pada halaman detail kios (`sales_order/pecah_faktur_kios/{kd_customer}`), di tabel **Faktur Z Belum Dipecah**, terdapat tombol aksi berwarna biru bertanda ikon tukar (**<i class="fas fa-exchange-alt"></i>**).
   - Klik tombol tersebut untuk membuka modal pemindahan faktur.
2. **Pindah Kios Sekaligus / Batch (Beberapa Faktur Z Terpilih)**:
   - Pengguna dapat mencentang satu, beberapa, atau seluruh faktur Z yang salah masuk menggunakan checkbox multi-select atau tombol **"Pilih Semua"**.
   - Klik tombol **"Pindah Kios Terpilih (N)"** pada toolbar atas.
   - Seluruh faktur yang dicentang akan langsung dialihkan ke kios baru secara serentak.
3. **Dari Halaman Detail Faktur Z (`sales_order/detail_faktur/{id_faktur}`)**:
   - Jika faktur Z belum pernah dipecah, pada toolbar atas terdapat tombol **"Ubah Kios / Customer"** dan di samping nama customer terdapat tombol kecil **"Ubah Kios"**.

---

## 3. Rincian Teknis Implementasi

### A. Routing (`application/config/routes.php`)
- `$route['sales_order/ubah_customer_faktur_z'] = 'sales/C_SalesOrder/ubah_customer_faktur_z';`
- `$route['pecah_faktur/ubah_customer'] = 'sales/C_SalesOrder/ubah_customer_faktur_z';`

### B. Controller (`application/controllers/sales/C_SalesOrder.php`)
1. **Whitelist Role `admkeu`**:
   - Menambahkan `'ubah_customer_faktur_z'` pada array `$allowedAdmkeuMethods`.
2. **Data Passing Master Customer**:
   - Method `pecah_faktur_kios()` dan `detail_faktur()` mengirim `$data['all_customers'] = $this->M_SalesOrder->get_customers();` untuk mengisi opsi dropdown pemilihan kios tujuan.
3. **Method `ubah_customer_faktur_z()`**:
   - Menerima POST parameter:
     - `id_faktur` (array `id_faktur[]` atau single string).
     - `kd_customer_baru`: kode customer tujuan baru yang dipilih dari `tb_customer`.
   - **Validasi Integritas**:
     - Memastikan faktur berstatus belum dipecah (`is_split_parent == 0`). Jika faktur sudah pernah dipecah, sistem menolak pemindahan dan menginstruksikan pengguna untuk meng-unpost faktur pecahannya terlebih dahulu.
     - Memastikan customer tujuan terdaftar aktif di `tb_customer`.
   - **Update Database (Atomic Transaction)**:
     - Update tabel `tbso_faktur_penjualan`: `kd_customer` dan `customer_name`.
     - Update tabel `tbso_sales_order`: `kd_customer`, `customer_name`, dan `kd_rute`.
     - Pencatatan riwayat perubahan ke tabel `tb_activity_log` melalui `M_ActivityLog::log()` untuk audit trail lengkap.
   - **Feedback Pengguna**:
     - Menampilkan flashdata pesan sukses dan otomatis me-redirect pengguna ke halaman kios tujuan baru sehingga faktur dapat langsung diperiksa di kios yang tepat.

---

## 4. Panduan Penggunaan
1. Buka menu **Pecah Faktur Kios**, lalu pilih kios tempat faktur Z yang salah masuk (misal: `ABAT01`).
2. Cari Faktur Z yang salah masuk ke kios tersebut.
3. **Jika hanya 1 faktur**:
   - Klik tombol biru **<i class="fas fa-exchange-alt"></i>** pada kolom Aksi faktur tersebut.
4. **Jika beberapa faktur sekaligus**:
   - Centang checkbox pada faktur-faktur yang salah masuk, lalu klik tombol **"Pindah Kios Terpilih (N)"** di toolbar atas.
5. Pada modal yang muncul, cari dan pilih **Kios / Customer Tujuan (Baru)** dari dropdown.
6. Klik tombol **"Simpan & Pindahkan Faktur"**.
7. Sistem akan memindahkan faktur ke kios yang baru, dan halaman otomatis dialihkan ke kios tujuan tersebut.
