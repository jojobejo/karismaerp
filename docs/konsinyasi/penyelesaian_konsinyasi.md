# Dokumentasi Modul Penyelesaian Barang Konsinyasi (Consignment Settlement)

## 1. Latar Belakang & Konsep Bisnis
Barang konsinyasi adalah barang titipan supplier (konsinyor) kepada distributor:
- **Saat Barang Masuk (Penerimaan Fisik)**:
  - Diinput melalui form **Input LPB Manual** dengan Jenis `LPB Konsinyasi` ke **Gudang Konsinyasi** (ID: 13).
  - Harga diinput `0` (atau belum ada harga) dan **tidak terjurnal** ke akuntansi (Off-balance sheet) karena bukan aset persediaan milik perusahaan dan belum ada kewajiban Hutang Usaha (*Accounts Payable*).
- **Saat Barang Terjual ke Customer**:
  - Diterbitkan melalui **Sales Order (SO)** / **Faktur Penjualan** yang mengambil stok dari Gudang Konsinyasi.
  - Sisi penjualan terjurnal ke customer (Piutang & Pendapatan Penjualan).
  - Sistem otomatis merekam transaksi barang konsinyasi yang telah terjual tersebut ke daftar antrean penyelesaian (`tb_konsinyasi_settlement`) dengan status `PENDING`.
- **Saat Tagihan / Invoice Resmi Diterima dari Supplier**:
  - Bagian Purchasing membuka menu **Penyelesaian Konsinyasi**.
  - Menginput Nomor Invoice Supplier, Tanggal Invoice, Harga Beli Satuan Resmi, dan PPN.
  - Sistem otomatis memposting **Jurnal Pembelian**:
    - **[Debit]** HPP / Beban Pokok Penjualan Konsinyasi (Akun `51010` atau sesuai master barang).
    - **[Debit]** PPN Masukan (Akun `13017`, jika ada PPN).
    - **[Kredit]** Utang Konsinyasi (Akun `21920` / `21019` / `21098`).
  - Status berubah menjadi `BILLED` (Selesai).

---

## 2. Struktur Database
Tabel: `tb_konsinyasi_settlement`
- `id_settlement` (Primary Key, Auto Increment)
- `no_settlement` (Nomor referensi unik, e.g. `KONS-SET-YYMMDD-XXXX`)
- `tanggal_settlement` (Tanggal transaksi penjualan SO/Faktur)
- `kd_suplier`, `nama_suplier` (Supplier pemilik barang asal yang diambil dari LPB Konsinyasi)
- `gudang_id` (ID Gudang Konsinyasi, default: 13)
- `id_so`, `no_so`, `id_faktur`, `no_faktur`, `customer_name` (Data penjualan customer)
- `id_lpb_asal`, `nomor_lpb_asal` (Referensi nomor LPB penerimaan fisik)
- `kd_barang`, `nama_barang`, `no_lot`, `expired_date` (Data batch barang fisik)
- `qty_terjual`, `qty_retur`, `qty_net`, `satuan` (Jumlah kuantitas barang)
- `hrg_jual`, `subtotal_jual` (Data omzet ke customer)
- `hrg_beli_satuan`, `subtotal_beli`, `ppn_persen`, `nilai_ppn`, `total_tagihan_beli` (Data tagihan supplier)
- `no_invoice_supplier`, `tgl_invoice_supplier` (Dokumen tagihan resmi supplier)
- `status` (`PENDING`, `BILLED`, `CANCELLED`)
- `id_jurnal_pembelian` (Foreign Key ke `tbkeu_jurnal.id_jurnal`)
- `settled_at`, `settled_by`, `catatan`

---

## 3. Komponen Source Code
1. **Model**:
   - [`application/models/M_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/models/M_Konsinyasi.php)
   - Fungsi utama: `sync_pending_consignment_sales()`, `get_settlement_list()`, `process_settlement()`.
2. **Library Akuntansi**:
   - [`application/libraries/Accounting_source_service.php`](file:///c:/laragon/www/karismaerp/application/libraries/Accounting_source_service.php)
   - Fungsi baru: `post_consignment_settlement($idSettlement, $userId)`.
3. **Controller**:
   - [`application/controllers/purchasing/C_Konsinyasi.php`](file:///c:/laragon/www/karismaerp/application/controllers/purchasing/C_Konsinyasi.php)
   - Route URL: `purchasing/konsinyasi_settlement`.
4. **View**:
   - [`application/views/content/purchasing/konsinyasi/settlement_list.php`](file:///c:/laragon/www/karismaerp/application/views/content/purchasing/konsinyasi/settlement_list.php)

---

## 4. Fitur Otomatisasi Pajak (Include PPN, Exclude PPN, Non-PPN)
User tidak perlu menghitung manual di kalkulator saat menerima tagihan dari supplier:
1. **Opsi Exclude PPN**:
   - Jika harga dari faktur supplier adalah harga DPP sebelum PPN.
   - PPN dihitung dari DPP × Tarif PPN (11% atau 12%).
   - Total Hutang = DPP + PPN.
2. **Opsi Include PPN**:
   - Jika harga yang tertera di faktur supplier sudah termasuk PPN (misal Rp 55.500 include PPN 11%).
   - User cukup mengetik angka `55.500` apa adanya.
   - Sistem otomatis mengekstrak:
     - `DPP = Total Tagihan / (1 + (PPN% / 100))`
     - `Nilai PPN = Total Tagihan - DPP`
   - Jurnal yang terbentuk tetap presisi: HPP didebit sebesar DPP murni, PPN Masukan didebit sebesar PPN, dan Utang dikredit sebesar Total Tagihan kotor.
3. **Opsi Non-PPN**:
   - Untuk supplier non-PKP atau barang bebas PPN (tarif 0%).

---

## 5. Cara Penggunaan oleh User
1. Buka menu **Purchasing -> Penyelesaian Konsinyasi** atau URL: `https://karismaerp.test/purchasing/konsinyasi_settlement`.
2. Klik tombol **"Input Tagihan"** pada baris barang konsinyasi yang ingin diproses.
3. Masukkan:
   - Nomor Invoice / Faktur dari Supplier.
   - Tanggal Invoice Supplier.
   - Pilih **Tipe Harga Supplier**: `Include PPN`, `Exclude PPN`, atau `Non-PPN`.
   - Ketik **Harga Satuan Supplier (Rp)** persis seperti yang tertulis di invoice supplier.
   - Pilih tarif PPN (11%, 12%, atau 0%).
4. Kotak **Rincian Otomatis Sistem** di bawahnya akan menghitung secara live:
   - Subtotal DPP (yang masuk ke HPP)
   - Nilai PPN Masukan
   - Total Hutang ke Supplier
5. Klik **"Posting Jurnal Pembelian"**.
6. Sistem otomatis menjurnal hutang dan HPP tanpa selisih sen.

