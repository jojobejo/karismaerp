# Dokumentasi Modul: Semua Transaksi & Sinkronisasi Jurnal (Admin Hub)

## 1. Ringkasan Eksekutif
Modul **Semua Transaksi** (`admin/transaksi`) adalah panel kendali terpusat khusus untuk pengguna level **Administrator** di Karisma ERP. Modul ini dirancang untuk memantau, mengaudit, mengedit, memposting ulang (*repost*), dan menghapus seluruh transaksi yang ada di sistem sekaligus memastikan **sinkronisasi 100% dengan jurnal akuntansi / Buku Besar (`tbkeu_jurnal` & `tbkeu_jurnal_detail`)**.

---

## 2. Hak Akses & Keamanan
- **Level Akses**: Administrator (`is_admin = true`, `username = admin`, atau `lv = 1` dengan jobdesk administrator).
- **Proteksi Akses**:
  - Validasi sesi dan verifikasi otorisasi pada constructor `C_Transaksi.php`.
  - Percobaan akses oleh pengguna non-admin akan ditolak dengan respon HTTP `403 Forbidden` (pada request AJAX) atau dialihkan ke halaman dashboard dengan notifikasi peringatan.
- **Navigasi Menu**:
  - Menu **Semua Transaksi** muncul pada panel Dashboard Admin (`M_Dashboard.php`) dan menu navigasi sidebar khusus Admin.

---

## 3. Cakupan Kategori Transaksi (Otomatis Jurnal)
Modul ini mengintegrasikan 6 kategori transaksi ERP yang memiliki dampak dan mekanisme posting jurnal akuntansi secara otomatis:

| Kategori Transaksi | Tabel Dokumen Sumber | Sumber / Event Jurnal | Tabel Jurnal Akuntansi |
|---|---|---|---|
| **Penjualan (Faktur)** | `tbso_faktur_penjualan`, `tbso_faktur_detail` | `SALES` / `FAKTUR_PENJUALAN` (`SALES_INVOICE`) | `tbkeu_jurnal`, `tbkeu_jurnal_detail`, `tbso_faktur_jurnal` |
| **Pembelian (LPB)** | `tb_lpb`, `tb_lpb_detail` | `LOGISTIK` / `LPB_FINAL` (`GOODS_RECEIPT`) | `tbkeu_jurnal`, `tbkeu_jurnal_detail` |
| **Pembayaran Customer** | `tbkeu_pembayaran_faktur` | `KEUANGAN` / `PEMBAYARAN_FAKTUR` | `tbkeu_jurnal`, `tbkeu_jurnal_detail` |
| **Pembayaran Supplier** | `tbkeu_pembayaran`, `tbkeu_pembayaran_alokasi` | `KEUANGAN` / `SUPPLIER_PAYMENT` | `tbkeu_jurnal`, `tbkeu_jurnal_detail` |
| **Retur Penjualan** | `tbrp_retur_penjualan_header`, `tbrp_retur_penjualan_detail` | `SALES` / `RETUR_PENJUALAN` (`SALES_RETURN`) | `tbkeu_jurnal`, `tbkeu_jurnal_detail` |
| **Retur Pembelian** | `tb_retur_pembelian`, `tb_retur_pembelian_detail` | `LOGISTIK` / `RETUR_PEMBELIAN` (`PURCHASE_RETURN`) | `tbkeu_jurnal`, `tbkeu_jurnal_detail` |

---

## 4. Mekanisme Fitur Utama

### A. Preview Jurnal & Rincian Item (Detail Modal)
- Tombol **Lihat Detail** menampilkan popup interaktif dengan 2 tab:
  1. **Rincian Item Transaksi**: Kuantiti, satuan, harga per unit, diskon, subtotal, nomor batch, dan tanggal kadaluarsa.
  2. **Jurnal Akuntansi**: Menampilkan baris jurnal Debit & Kredit, kode akun, nama akun COA, keterangan transaksi, dan validasi *balance* total debit vs kredit.

### B. Edit Transaksi & Auto-Sync Jurnal
- Admin dapat mengedit nilai-nilai penting pada transaksi:
  - **Faktur Penjualan**: Harga satuan, kuantiti, diskon persen, diskon rupiah, tanggal transaksi, catatan.
  - **Pembelian (LPB)**: Harga perolehan/satuan, kuantiti diterima, tanggal surat jalan.
  - **Pembayaran Customer / Supplier**: Nominal pembayaran, potongan diskon, metode pembayaran (Kas/Bank), tanggal.
  - **Retur Penjualan / Pembelian**: Kuantiti retur, harga barang retur, tanggal retur.
  - **Kas Masuk / Keluar**: Nominal baris akun, keterangan, tanggal.
- **Proses Sinkronisasi Otomatis**:
  1. Sistem memperbarui data pada tabel transaksi sumber.
  2. Menghitung ulang total dokumen, PPN/pajak, HPP (*COGS*), dan tonase/kubikasi jika ada.
  3. Menghapus entri jurnal lama yang tidak lagi sinkron.
  4. Memicu modul posting resmi (`Accounting_source_service`, `M_Journal`, `M_SalesOrder`, dll.) untuk meregenerasi jurnal baru dengan nilai yang sudah diperbarui secara presisi.

### C. Repost Transaksi
- Jika suatu transaksi mengalami masalah sinkronisasi, terhapus jurnalnya, atau berstatus *unposted*, Admin dapat menekan tombol **Repost**.
- Sistem akan:
  1. Membersihkan sisa jurnal lama/ganda pada transaksi tersebut.
  2. Membaca ulang nominal riil dari detail transaksi.
  3. Memposting ulang jurnal akuntansi ke `tbkeu_jurnal` & `tbkeu_jurnal_detail`.
  4. Memperbarui status transaksi menjadi `POSTED` atau `selesai`.

### D. Delete Transaksi & Pembersihan Jurnal
- Admin dapat menghapus transaksi yang salah input atau tidak valid.
- Tindakan ini berjalan secara atomik (*database transaction*):
  1. Menghapus jurnal terkait dari `tbkeu_jurnal` dan `tbkeu_jurnal_detail` (sehingga tidak ada jurnal piatu / *orphan record*).
  2. Menghapus data detail dan header transaksi.
  3. Mencatat audit alasan penghapusan.

---

## 5. Struktur Berkas Kode
1. **Controller**: [`application/controllers/admin/C_Transaksi.php`](file:///c:/laragon/www/karismaerp/application/controllers/admin/C_Transaksi.php)
2. **Model**: [`application/models/admin/M_Transaksi.php`](file:///c:/laragon/www/karismaerp/application/models/admin/M_Transaksi.php)
3. **View**: [`application/views/content/admin/transaksi/index.php`](file:///c:/laragon/www/karismaerp/application/views/content/admin/transaksi/index.php)
4. **Model Dashboard**: [`application/models/M_Dashboard.php`](file:///c:/laragon/www/karismaerp/application/models/M_Dashboard.php)
5. **Konfigurasi Routing**: [`application/config/routes.php`](file:///c:/laragon/www/karismaerp/application/config/routes.php)
6. **Sidebar Navigation**: [`application/views/partial/main/sidebar.php`](file:///c:/laragon/www/karismaerp/application/views/partial/main/sidebar.php)
