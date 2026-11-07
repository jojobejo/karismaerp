# Panduan Lengkap Alur Operasional Sales Order & Katalog Seluruh Modul KARISMA ERP

Dokumen ini berisi:
1. **Analisis Mendalam Alur Operasional Sales Order (SO) End-to-End**: Mulai dari pembuatan Sales Order oleh tim Sales, grouping rute SO Lobby, perencanaan pengiriman oleh ADMLOG, pemeriksaan fisik oleh Checker Gudang, penerbitan Faktur & Delivery Order (DO) oleh Admin SC, hingga penagihan dan pencairan di Keuangan.
2. **Katalog & Panduan Operasional Seluruh Modul KARISMA ERP**: Rincian tindakan yang dilakukan oleh pengguna (*user journey*) di setiap modul sistem.

---

# BAGIAN 1: ALUR OPERASIONAL SALES ORDER (SO) END-TO-END

Proses bisnis pesanan penjualan di KARISMA ERP terintegrasi lintas departemen dengan alur berkesinambungan sebagai berikut:

```
[1. SALES / SALES COUNTER]
       │
       ├─► Input Sales Order Baru (Draft)
       ├─► Validasi Plafon Kredit & Batas Overdue (OD) Customer
       ├─► Pemilihan Lot/Batch & Stok Fisik Realtime
       └─► Rekam Sales Order ➔ Status Berubah: "Draft" ➔ "Open"
              │
[2. SO LOBBY / PENENTUAN RUTE KIRIM]
       │
       ├─► Grouping SO Open berdasarkan Rute Kirim / Rayon
       ├─► Monitoring Kapasitas Armada (Tonase maks 7.5 Ton & Kubikasi maks 25 m³)
       └─► Pindahkan Masal SO ke Rute Siap Kirim ➔ Status: "Siap Loading"
              │
[3. ADMIN LOGISTIK (ADMLOG)]
       │
       ├─► Akses Panel SO Siap Loading per Rute
       ├─► Buat Plan Pengiriman (Pilih Tanggal, Armada/Plat Nomor, Supir, Ekspedisi)
       └─► Atur Loading Order (Urutan Muat Barang 1, 2, 3... Menggunakan Prinsip LIFO)
              │
[4. PETUGAS CHECKER GUDANG]
       │
       ├─► Inspeksi & Ceklis Muat Barang ke Armada (Keranjang Kecil / Keranjang Besar)
       ├─► Verifikasi Kuantitas Fisik: Qty Muat vs Qty Kurang/Batal
       └─► Konfirmasi Selesai Loading ➔ Status SO Berubah: "Siap Faktur"
              │
[5. ADMIN SC (SALES COUNTER ADMIN & INVOICING)]
       │
       ├─► Buka Tab SO Siap Faktur ➔ Pemilihan Barang Faktur
       ├─► Terbitkan Faktur Penjualan (Kode Pajak Q, Non-Pajak A, Promosi, Faktur Z)
       ├─► Eksekusi Pecah Faktur Z (Jika ada alokasi faktur induk ke beberapa kios)
       ├─► Buat & Cetak Delivery Order (Surat Jalan / DO Logistik)
       └─► Cetak Faktur Massal per Rute
              │
[6. PENGIRIMAN & EKSPEDISI]
       │
       └─► Driver mengirimkan barang ke alamat toko customer ➔ Serah terima fisik & TTD DO
              │
[7. KEUANGAN / COLLECTION / KASIR]
       │
       ├─► Monitoring Piutang Customer (Aging Overdue 30, 60, 90 Hari)
       ├─► Input Pembayaran Faktur (Kas/Bank, Bilyet Giro/BG, atau Potongan Retur)
       ├─► Pencairan Bilyet Giro (Pending ➔ Cair)
       └─► Posting Otomatis Jurnal Akuntansi Keuangan ke Buku Besar
```

---

### Rincian Tindakan Pengguna di Setiap Tahapan SO

#### 1. Divisi Sales / Sales Counter (SC)
* **File Terkait**: `application/controllers/sales/C_SalesOrder.php` | `application/controllers/sales/C_SalesOrderLoby.php`
* **Langkah Kerja User**:
  1. **Membuat SO Baru**: User membuka menu **Sales Order -> Buat SO**, memilih Customer, Kios tujuan, Salesman penanggung jawab, serta menentukan metode pembayaran (*Cash, Transfer, Bilyet Giro, atau Tempo*).
  2. **Pemilihan Barang & Lot Batch**: Memilih barang dagangan, menentukan nomor batch/lot, memeriksa tanggal expired, serta memastikan kuantitas tidak melebihi stok fisik gudang.
  3. **Validasi Plafon Kredit & Overdue**: Sistem melakukan pengecekan limit piutang customer secara otomatis. Jika customer memiliki nota tertunggak melewati batas toleransi (*Overdue*), transaksi terkunci dan user harus mengajukan dispensasi persetujuan Overdue melalui modul `C_PengajuanOD.php` ke Direksi.
  4. **Perekaman Dokumen**: User menekan tombol **Rekam SO**. Dokumen berpindah status dari `Draft` menjadi `Open`. Pada status `Open`, kuantitas stok barang sudah teralokasi (*reserved*).

#### 2. SO Lobby & Manajemen Rute
* **File Terkait**: `application/controllers/sales/C_SalesOrderLoby.php`
* **Langkah Kerja User**:
  1. **Filtering & Monitoring Rute**: Koordinator penjualan membuka menu **SO Lobby** untuk melihat seluruh SO berstatus `Open` yang dikelompokkan berdasarkan Rayon / Rute Pengiriman.
  2. **Kontrol Tonase & Kubikasi**: Memantau kapasitas muatan rute agar tidak melebihi batas armada (tonase armada rata-rata 7.5 Ton dan kubikasi 25 m³).
  3. **Pengesahan Siap Muat**: Memilih baris SO yang siap dikirim, lalu mengklik tombol pemindahan status menjadi **Siap Loading**.

#### 3. Divisi Logistik (ADMLOG)
* **File Terkait**: `application/controllers/logistik/C_Logistik.php`
* **Langkah Kerja User**:
  1. **Buka Menu Plan Pengiriman**: User ADMLOG membuka modul Logistik dan memfilter SO yang berstatus **Siap Loading**.
  2. **Atur Plan Pengiriman**: Menentukan tanggal jadwal kirim, memilih jenis armada (Armada Kantor atau Sewa Ekspedisi Luar), memasukkan nomor plat polisi, supir (driver), dan kernet.
  3. **Atur Urutan Muat (Loading Order)**: Mengatur urutan naik barang ke truk (nomor urut 1, 2, 3, dst.). Barang untuk customer yang berada di tujuan akhir dimasukkan lebih dahulu ke bagian dalam truk, sedangkan barang tujuan pertama diletakkan paling belakang/luar pintu truk (*Prinsip LIFO*).

#### 4. Petugas Checker Gudang
* **File Terkait**: `application/controllers/logistik/C_Checker.php` | `application/views/content/logistik/checker/index.php`
* **Langkah Kerja User**:
  1. **Checklist Fisik Saat Muat**: Petugas checker membawa lembar checklist digital / fisik saat barang dinaikkan dari area gudang ke bak truk armada.
  2. **Input Verifikasi Qty Fisik**: Checker memasukkan jumlah fisik yang benar-benar termuat (*Qty Siap Faktur*). Jika terdapat barang rusak, tercecer, atau stok fisik tidak cukup, checker memasukkannya ke dalam *Qty Tidak Terkirim*.
  3. **Konfirmasi Final Checker**: Checker menekan tombol verifikasi selesai. Status SO di sistem secara otomatis berpindah menjadi **Siap Faktur**.

#### 5. Admin SC & Invoicing (Faktur & Surat Jalan)
* **File Terkait**: `application/controllers/sales/C_SalesOrder.php`
* **Langkah Kerja User**:
  1. **Buka Menu SO Siap Faktur**: Admin SC memfilter daftar SO yang sudah diverifikasi oleh checker gudang.
  2. **Pemilihan Barang Faktur**: Menentukan item barang yang lolos muat untuk diterbitkan faktur resminya.
  3. **Penerbitan Faktur Penjualan**: Menentukan kategori faktur:
     * **Kode Q**: Faktur BKP Pajak (PPN 11%/12%).
     * **Kode A**: Faktur Dagangan Non-Pajak.
     * **Faktur Promosi / Hadiah**.
     * **Faktur Z**: Faktur dagang konsinyasi/induk khusus.
  4. **Pecah Faktur Z (Split Faktur)**: Jika menggunakan Faktur Z induk, Admin SC memecah faktur ke beberapa kios turunan sesuai kuota tanpa mengubah total kuantitas induk.
  5. **Cetak Dokumen Pengiriman**:
     * Menerbitkan dan mencetak **Delivery Order (DO) / Surat Jalan** resmi logistik.
     * Menjalankan fungsi cetak faktur massal per rute pengiriman.

#### 6. Divisi Keuangan (Collection & Kasir)
* **File Terkait**: `application/controllers/keuangan/C_pembayaran.php`
* **Langkah Kerja User**:
  1. **Monitoring Piutang Customer**: Memantau faktur berstatus `selesai_do` yang belum lunas, memeriksa tabel aging piutang (Overdue 30, 60, dan 90 hari).
  2. **Input Pembayaran Faktur**:
     * Memilih faktur aktif.
     * Memilih metode pembayaran: Kas Tunai, Transfer Bank (BCA, Mandiri, BRI), Bilyet Giro (BG), atau Potongan Saldo Retur Penjualan (*Q Hutang Non Dagang*).
     * Memasukkan potongan diskon pembayaran jika ada.
  3. **Pencairan Bilyet Giro**: Saat jatuh tempo warkat BG tiba, user mengubah status pembayaran dari `pending` menjadi `cair`.
  4. **Posting Jurnal Otomatis**: Sistem secara otomatis mencatat posting jurnal akuntansi Kas/Bank vs Piutang Usaha ke modul Buku Besar.

---

# BAGIAN 2: KATALOG LENGKAP SELURUH MODUL KARISMA ERP

Berikut adalah daftar seluruh modul dalam sistem KARISMA ERP beserta rincian operasional yang dilakukan oleh user:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        MODUL UTAMA KARISMA ERP                         │
├────────────────────────┬───────────────────────┬───────────────────────┤
│ 1. Sales & Distribusi  │ 2. Purchasing & ICS   │ 3. Retur Penjualan    │
│ 4. Retur Pembelian     │ 5. Keuangan & Kasir   │ 6. Akuntansi & Jurnal │
│ 7. Gudang & Mutasi     │ 8. Stock Opname       │ 9. HRD & Kasbon       │
│ 10. Admin Hub          │ 11. Master Data       │ 12. Layanan Pelanggan │
└────────────────────────┴───────────────────────┴───────────────────────┘
```

---

### 1. Modul Sales Order, Lobby & Invoicing
* **Controller**: `sales/C_SalesOrder.php`, `sales/C_SalesOrderLoby.php`, `sales/C_PengajuanOD.php`
* **Peran Pengguna**: Salesman, Sales Counter (SC), Supervisor Sales, Admin SC.
* **Aktivitas Operasional User**:
  * Menginput transaksi Sales Order (SO) baru lengkap dengan detail customer, kios, salesman, dan term pembayaran.
  * Memilih barang dengan lot/batch dan expired date yang valid.
  * Mengajukan dispensasi Overdue (OD) ke Direksi jika limit plafon customer terlampaui.
  * Merekam dokumen SO (Draft -> Open).
  * Mengelompokkan rute kirim di SO Lobby, memantau batas tonase/kubikasi armada, dan memindahkan ke status Siap Loading.
  * Menerbitkan Faktur Penjualan (Kode Pajak Q, Non-Pajak A, Promosi, atau Faktur Z).
  * Memproses Pecah Faktur Z (Split Faktur induk ke multi-kios).
  * Mencetak Faktur Penjualan dan Delivery Order (DO) / Surat Jalan.

---

### 2. Modul Logistik & Checker Pengiriman
* **Controller**: `logistik/C_Logistik.php`, `logistik/C_Checker.php`, `logistik/C_Distribusi.php`
* **Peran Pengguna**: Admin Logistik (ADMLOG), Supervisor Logistik, Checker Gudang.
* **Aktivitas Operasional User**:
  * Menerima daftar SO Siap Loading per rute rayon.
  * Membuat **Plan Pengiriman**: menentukan tanggal jadwal kirim, armada kendaraan, nomor plat polisi, supir (driver), dan ekspedisi.
  * Mengatur **Urutan Muat (Loading Order)** barang ke atas kendaraan berdasarkan urutan rute toko.
  * Melakukan pemeriksaan fisik muat barang menggunakan checklist digital Checker Gudang.
  * Mengonfirmasi verifikasi jumlah fisik muat barang vs barang tidak terkirim.
  * Mengubah status dokumen menjadi Siap Faktur untuk diproses oleh Admin SC.

---

### 3. Modul Purchasing & Penerimaan Barang (ICS / LPB)
* **Controller**: `logistik/C_Ics.php`, `purchasing/C_BundlingRequest.php`, `purchasing/C_Konsinyasi.php`
* **Peran Pengguna**: Staff Purchasing, Supervisor Pengadaan, Admin LPB.
* **Aktivitas Operasional User**:
  * Memantau daftar Laporan Penerimaan Barang (LPB) yang diterbitkan gudang dari PO supplier.
  * Mengupdate data administrasi LPB: nomor Invoice supplier, nomor Faktur Pajak (PPN 11%/12%), dan DPP Nilai Lain.
  * Memverifikasi kecocokan harga satuan detail LPB dengan invoice (*Accept Harga*).
  * Melakukan *Pecah Invoice* jika 1 LPB ditagih menggunakan beberapa invoice vendor.
  * Mengubah status LPB dari `UNPOST` ke `POST` (Rekam).
  * Menginput **LPB Manual** untuk transaksi penerimaan barang non-PO (khusus/darurat).
  * Melakukan **Adjustment Harga LPB** untuk mengoreksi harga faktur tanpa merusak stok fisik.
  * Mengajukan dan mengelola formula Request Paket Bundling barang.
  * Memonitor laporan LPB harian dengan filter nested (aging faktur pajak > 60 hari).

---

### 4. Modul Retur Pembelian (Purchase Return)
* **Controller**: `logistik/C_Ics.php` (Sub-modul Retur Pembelian)
* **Peran Pengguna**: Staff Purchasing, Gudang Bahan/Barang Masuk, Accounting.
* **Aktivitas Operasional User**:
  * Memilih LPB final terverifikasi sebagai referensi retur.
  * Membuat Draft Retur Pembelian dengan mengisi kuantitas retur, nomor batch/lot, dan alasan retur.
  * Memilih metode penyelesaian (*Potong Hutang Dagang AP*, *Cash Refund*, atau *Tukar Barang Baru*).
  * Verifikasi harga retur oleh Purchasing.
  * Mengonfirmasi pengeluaran fisik barang dari gudang, memotong stok master, dan mencatat mutasi `RBELI`.
  * Memotong saldo hutang supplier pada Modul Pembayaran Hutang dan membentuk jurnal pembalik persediaan.

---

### 5. Modul Retur Penjualan (Sales Return) & SPR
* **Controller**: `sales/C_ReturPenjualan.php`
* **Peran Pengguna**: Sales / SC, Manager SC, Kadep UB/Jagung, Admin Retur, Kadep SC, Admin LPB2, Keuangan/Collection, Kasir.
* **Aktivitas Operasional User**:
  * **Pengajuan SPR**: Tim Sales membuat Surat Permohonan Retur (SPR) berdasarkan nomor faktur asli, detail batch, expired date, dan alasan retur (*Barang Rusak, Expired, Tidak Laku, Tes Market, Bad Debt, Salah Harga*).
  * **Approval Berjenjang**:
    1. Manager SC melakukan verifikasi awal.
    2. Kadep UB melakukan otorisasi (khusus retur komoditas seperti Jagung).
    3. Admin Retur melakukan pemeriksaan administrasi dokumen.
    4. Kadep SC menyetujui di tingkat departemen.
  * **Penerimaan Fisik di Gudang**: Admin LPB2 memeriksa fisik barang yang kembali, menerbitkan **Nota Retur Penjualan**, dan mengalokasikan stok ke gudang retur.
  * **Approval Nota Retur Direksi**: Otorisasi bertingkat hingga Direktur Operasional & Direktur Utama (stempel digital QR Code).
  * **Penyelesaian Keuangan**: Tim Collection memotong saldo piutang toko, atau Kasir mengembalikan uang tunai jika tipe retur cash refund.

---

### 6. Modul Keuangan & Kasir (Finance)
* **Controller**: `keuangan/C_pembayaran.php`, `keuangan/C_PembayaranSupplier.php`, `keuangan/C_KasMasuk.php`, `keuangan/C_KasKeluar.php`, `keuangan/C_Kasir.php`
* **Peran Pengguna**: Admin Keuangan, Staff AR/Collection, Staff AP, Kasir.
* **Aktivitas Operasional User**:
  * **Pembayaran Faktur Customer (AR)**: Memilih customer, memilih faktur aktif, memproses pelunasan via Kas/Bank, Bilyet Giro (BG), atau Potongan Retur (*Q Hutang Non Dagang*).
  * **Pencairan Bilyet Giro**: Memantau jatuh tempo BG dan mengubah status pembayaran dari `pending` ke `cair`.
  * **Pembayaran Hutang Supplier (AP)**: Mengalokasikan pembayaran ke nomor invoice/LPB supplier, menerapkan potongan retur pembelian, dan memproses voucher pengeluaran bank.
  * **Kas Masuk & Kas Keluar**: Menginput voucher kas operasional dengan nomor bukti resmi, teks terbilang otomatis, dan akun lawan COA.
  * **Meja Kasir Harian**: Mengelola penerimaan kas meja kasir, serah terima pergantian shift, dan penutupan kas harian.

---

### 7. Modul Akuntansi & Buku Besar (Accounting)
* **Controller**: `keuangan/C_Accounting.php`, `keuangan/C_BukuBesar.php`, `keuangan/C_Laporan.php`
* **Peran Pengguna**: Accounting Officer, Supervisor Akuntansi, Finance Manager.
* **Aktivitas Operasional User**:
  * **Jurnal Umum**: Memeriksa seluruh transaksi jurnal yang terbentuk secara otomatis dari transaksi operasional (Penjualan, Pembelian, Kas, Retur).
  * **Input Jurnal Memorial**: Menginput entri penyesuaian/jurnal memorial akuntansi secara manual.
  * **Buku Besar (General Ledger)**: Memantau histori debit, kredit, dan saldo berjalan per akun COA.
  * **Laporan Keuangan**: Menghasilkan laporan Neraca Saldo (*Trial Balance*), Laporan Laba Rugi (*Income Statement*), dan Neraca Keuangan (*Balance Sheet*).
  * **Pricelist & Margin**: Mengontrol analisis margin laba kotor per kategori produk dagang.

---

### 8. Modul Gudang, Mutasi & Manajemen Stok
* **Controller**: `stock/C_Stock.php`, `keuangan/C_PenyesuaianBarang.php`, `logistik/C_BundlingLogistik.php`, `logistik/C_ExportStock.php`
* **Peran Pengguna**: Kepala Gudang, Staff Gudang, Admin Persediaan.
* **Aktivitas Operasional User**:
  * **Monitoring Kartu Stok**: Memeriksa stok fisik, stok alokasi SO, dan sisa stok bebas per gudang dan per batch lot.
  * **Mutasi Antar Gudang**: Membuat surat transfer stok antar gudang (misal dari Gudang Pusat ke Gudang Transit / Gudang Bad Stock).
  * **Perakitan Bundling (Assembly/Disassembly)**: Mengeksekusi perakitan barang paket (memotong stok barang bahan/komponen eceran dan menambah stok barang jadi kemasan paket).
  * **Penyesuaian Barang (Stock Adjustment)**: Menginput penyesuaian selisih barang akibat kerusakan atau kemasan bocor disertai alasan resmi.
  * **Buffer Stock Control**: Memantau alert barang yang mendekati batas minimum persediaan (*re-order point*).

---

### 9. Modul Stock Opname Terpadu
* **Controller**: `admin/C_Stockopname.php`
* **Peran Pengguna**: Tim Auditor Stock Opname, Petugas Scan, Supervisor Opname, Admin Sistem.
* **Aktivitas Operasional User**:
  * **Buka Sesi Opname**: Menginisiasi sesi stock opname baru untuk gudang tertentu.
  * **Input Hitungan Fisik & Scan Barcode**: Petugas lapangan memindai barcode/QR code barang dan menginput angka hitungan fisik riil.
  * **Supervisi & Rekonsiliasi**: Memeriksa tabel selisih (Stok Sistem vs Fisik Riil).
  * **Handling Barang Pending & Afirmasi**: Menindaklanjuti barang tanpa identitas lot atau item yang memerlukan konfirmasi ulang.
  * **Posting Hasil Opname**: Mengesahkan hasil opname sehingga sistem secara otomatis menyesuaikan stok master dan membentuk jurnal penyesuaian selisih persediaan.

---

### 10. Modul HRD, Kasbon & Personalia
* **Controller**: `hrd/C_Hrd.php`, `C_Kasbon.php`, `kmt/Gaji.php`, `kpi/C_Kpi.php`
* **Peran Pengguna**: Seluruh Karyawan, Atasan Departemen, HRD, GA, Kasir Kasbon.
* **Aktivitas Operasional User**:
  * **Kasbon Karyawan**: Karyawan mengajukan pinjaman kasbon secara mandiri, menentukan jumlah dan tenor cicilan potong gaji.
  * **Approval Berjenjang Kasbon**: Otorisasi berjenjang dari Atasan Langsung -> HRD -> Keuangan -> Direksi.
  * **Pencairan & Pelunasan Kasbon**: Kasir memproses penyerahan uang dan sistem memotong cicilan saat proses payroll.
  * **Buku Tamu Digital**: Resepsionis/Security mendata tamu perusahaan, tujuan kunjungan, dan foto identitas.
  * **Penilaian Lingkungan Kerja (5S)**: Tim HRD/GA melakukan inspeksi kebersihan dan kerapian lingkungan kantor dengan upload foto bukti serta pemberian rating bintang.
  * **Penggajian (Payroll)**: Memproses rekapitulasi gaji, tunjangan, lembur, dan potongan kasbon.

---

### 11. Modul Admin Hub: Semua Transaksi & Sinkronisasi Jurnal
* **Controller**: `admin/C_Transaksi.php`, `admin/C_Access_log.php`
* **Peran Pengguna**: Super Administrator, IT Internal Audit.
* **Aktivitas Operasional User**:
  * **Audit Lintas Modul**: Memantau 6 transaksi inti (*Faktur Penjualan, LPB Pembelian, Pembayaran Customer, Pembayaran Supplier, Retur Penjualan, Retur Pembelian*) dalam satu dashboard terpadu.
  * **Preview Transaksi & Jurnal**: Melihat rincian barang sekaligus mengecek keseimbangan debit dan kredit pada jurnal akuntansi terkait.
  * **Koreksi Data Transaksi**: Mengoreksi harga atau data transaksi yang salah input secara aman.
  * **Fitur Repost Transaksi**: Memposting ulang jurnal akuntansi yang sempat gagal terbentuk agar sinkron 100% dengan Buku Besar.
  * **Audit Log Akses**: Memantau riwayat login, perangkat, dan alamat IP pengguna sistem.

---

### 12. Modul Master Data & Hak Akses
* **Controller**: `master/C_Usermanagement.php`, `master/C_Menu.php`, `master/C_Akseslevel.php`, `master/C_Jobdesk.php`, `C_Pricelist.php`
* **Peran Pengguna**: Administrator Sistem.
* **Aktivitas Operasional User**:
  * **User Management**: Menambah, mengubah profil, mereset password, dan menonaktifkan akun pengguna.
  * **Akses Level & Jobdesk**: Mengatur struktur jabatan dan hak otorisasi level pengguna.
  * **Manajemen Menu Dinamis**: Mengatur susunan menu sidebar yang dapat diakses oleh masing-masing level pengguna.
  * **Master Barang, Customer, & Supplier**: Mengelola database induk barang dagangan, satuan, kelompok dagang, data kios customer, serta kontak vendor supplier.

---

### 13. Modul Layanan Pelanggan & Pendukung
* **Controller**: `pelanggan/C_Pelanggan.php`, `inventaris/C_Inventaris.php`, `requestdesign/C_requestdesign.php`
* **Peran Pengguna**: Customer Care, Marketing / Sales, Tim Desain, Umum/Asset.
* **Aktivitas Operasional User**:
  * **Indeks Kepuasan Pelanggan (IPKP)**: Menginput dan merekap survey kepuasan pelanggan terhadap layanan pengiriman dan kinerja tim penjualan.
  * **Request Design**: Tim sales/marketing mengajukan permintaan desain banner promosi, spanduk kios, atau materi iklan toko.
  * **Inventaris Aset Kantor**: Mendata peralatan kantor, nomor seri aset, kondisi fisik aset, dan penanggung jawab pemegang barang.

---

# MATRIKS RINGKASAN OUTPUT DOKUMEN & DAMPAK TRANSAKSI

| No | Modul | Input Dokumen | Output Dokumen | Dampak Finansial / Stok |
|---|---|---|---|---|
| 1 | **Sales Order & Logistik** | Pesanan Sales, Verifikasi Checker Gudang | Sales Order, Surat Jalan (DO), Faktur Penjualan | Mengurangi stok barang & menerbitkan Piutang Dagang |
| 2 | **Purchasing & LPB** | PO Supplier, Penerimaan Fisik Gudang | LPB, Faktur Pajak Masukan | Menambah stok barang & menerbitkan Hutang Dagang |
| 3 | **Retur Penjualan** | Keluhan/Barang Rusak dari Toko | SPR, Nota Retur Penjualan | Menambah stok gudang retur & memotong Piutang Dagang |
| 4 | **Retur Pembelian** | Barang Rusak ke Supplier | Nota Retur Pembelian | Memotong stok barang & memotong Hutang Dagang |
| 5 | **Keuangan (AR/AP/Kas)** | Bukti Transfer, Setoran Kasir, BG | Voucher Kas, Kuitansi Pelunasan | Memperbarui saldo Kas/Bank & Jurnal Buku Besar |
| 6 | **Stock Opname** | Scan Hitungan Fisik Gudang | Berita Acara Selisih Opname | Mengoreksi selisih stok & Jurnal Selisih Persediaan |
| 7 | **Kasbon Karyawan** | Pengajuan Mandiri Karyawan | Form Kasbon, Bukti Kas Keluar | Pengeluaran Kas & Piutang Karyawan |
