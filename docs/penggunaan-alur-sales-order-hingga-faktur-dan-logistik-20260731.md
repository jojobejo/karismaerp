# DOKUMENTASI LENGKAP MODUL SALES ORDER, LOGISTIK, INVOICING & RETUR PENJUALAN

---

### 1. Modul Sales Order (SO)
Modul untuk pengelolaan pesanan penjualan dari customer oleh tim Sales / Sales Counter (SC).

* **Membuat Sales Order Baru**:
  * Input transaksi SO baru oleh Sales / SC.
  * Pemilihan Customer & Kios, Salesman, dan Alamat Pengiriman.
  * Skema Pembayaran: Cash, Transfer, Bilyet Giro (BG), dan Tempo.
  * Integrasi Plafon Kredit Customer: Pengecekan otomatis batas plafon kredit customer terhadap total belanja SO.
  * Pemilihan Barang Realtime: Pemilihan lot/batch, tanggal expired, dan stok fisik realtime per gudang.
  * Batas Tonase & Kubikasi: Perhitungan tonase otomatis (maks 7.5 Ton) dan kubikasi (maks 25 m³) per armada pengiriman.
  * Workflow Approval Harga Khusus: Pengajuan persetujuan ke Manager SC jika terdapat perubahan/potongan harga di bawah harga standar.
  * Dukungan opsi penandaan Faktur Z.

* **Daftar & Monitoring Sales Order**:
  * Tabel monitoring seluruh SO berdasarkan status (Draft, Open, Sedang Verifikasi, Siap Faktur, Partial, Completed, Cancelled).
  * Filter berdasarkan Rentang Tanggal, Salesman, Customer, dan Status Transaksi.

* **Detail Sales Order**:
  * Menampilkan informasi rincian SO, item barang, status kuantitas (Qty Order, Qty Faktur, Qty Outstanding, Qty Siap Faktur).
  * Riwayat Faktur Penjualan yang telah terbit dari SO tersebut.
  * Fitur aksi: Rekam SO (Draft → Open) dan Batal SO.

* **Penentuan Rute SO / SO Siap Loading**:
  * Pengelompokan (grouping) SO berstatus Open berdasarkan Rute Pengiriman / Rayon Customer.
  * Rekapitulasi total tonase, kubikasi, dan total order per rute.
  * Bulk Update Rute: Pemindahan masal SO ke rute pengiriman armada logistik yang siap loading.

* **Log Aktivitas SO**:
  * Audit trail pencatatan riwayat pembuatan, pengubahan, perekaman, dan pembatalan Sales Order.

---

### 2. Modul Logistik & Loading Armada
Modul pengolahan SO oleh tim Logistik dan Checker Gudang sebelum dijadikan faktur penjualan.

* **Plan Pengiriman & Urutan Muat**:
  * Tim Logistik menerima SO per rute pengiriman.
  * Menentukan **Plan Pengiriman**: Tanggal Pengiriman, Ekspedisi (Kantor / Luar), Driver, dan Nomor Plat Kendaraan/Truk.
  * Barcode Ring Kapasitas: Monitoring tonase terpakai vs batas maksimal armada (misal 7 ton) & kubikasi (misal 9 m³).
  * Mengatur **Urutan Muat (Loading Order)** SO ke dalam kendaraan (Urutan 1, 2, 3, dst.) agar barang yang diturunkan pertama oleh driver ditaruh di bagian paling belakang armada.
  * Fitur kembalikan SO ke status Open/Partial jika terdapat kendala pengiriman di logistik.

* **Verifikasi Barang Loading Gudang & Checker**:
  * Petugas Checker / Gudang memeriksa fisik barang yang dimuat ke truk.
  * Input verifikasi kuantitas fisik (Qty Siap Faktur vs Qty Tidak Terkirim jika ada barang kurang atau rusak).
  * Aplikasi checklist digital bagi petugas checker gudang saat penataan barang fisik ke atas truk.
  * Mengubah status SO menjadi **Siap Faktur**.

---

### 3. Modul Admin SC (Sales Counter Admin & Invoicing)
Modul khusus bagi Admin SC dan Manager SC untuk verifikasi, pembuatan faktur penjualan, dan pecah faktur.

* **Admin SC — SO Siap Faktur**:
  * Ringkasan dan daftar SO yang telah lolos verifikasi loading/checker gudang dan siap diterbitkan faktur.
  * Grouping SO Siap Faktur per Rute Pengiriman dan Tanggal Transaksi.

* **Pemilihan Barang Faktur**:
  * Memilih barang dari SO terverifikasi yang akan dimasukkan ke dalam Faktur Penjualan.
  * Penyesuaian harga satuan tingkat Admin SC dengan proteksi persetujuan Manager SC.

* **Pembuatan Faktur Penjualan**:
  * Diterbitkan dari SO untuk Faktur Standar maupun Faktur Z.
  * Pengelompokan jenis faktur: BKP Pajak 11% (Kode Q), Non-Pajak, Barang Promosi (Kode Z), Dagangan (Kode A), BKPS.
  * Penentuan Jatuh Tempo (0, 30, 60, 90 hari).
  * Penomoran Faktur Otomatis dengan Prefix Kode Salesman (A, B, C, dst. atau Z untuk Faktur Z).

* **Pecah Faktur Z / Split Faktur**:
  * Memecah Faktur Z (Faktur Induk) menjadi beberapa Faktur Turunan ke beberapa Customer / Kios penerima.
  * Alokasi kuantitas barang per customer tanpa melebihi batas kuantitas faktur induk.

* **Admin SC — Faktur Selesai**:
  * Monitoring faktur penjualan yang telah terbit.
  * Detail Faktur Penjualan dan tracking faktur induk/turunan hasil split.
  * Cetak Faktur Massal per Rute: Cetak batch semua faktur dalam satu rute pengiriman sekaligus.

* **Faktur per Rute**:
  * Monitoring faktur penjualan yang terkait dengan Delivery Order (DO) / pengiriman hari ini per rute pengiriman.

* **Delivery Order (Surat Jalan)**:
  * Pembuatan dan pencetakan Surat Jalan / Delivery Order (DO) resmi logistik setelah faktur terbit.
  * Tracking Surat Jalan / DO Logistik dari sudut pandang Sales & Driver.

---

### 4. Modul Retur Penjualan & SPR (Surat Permohonan Retur)
Alur lengkap pengajuan, persetujuan berjenjang, penerimaan fisik gudang, hingga pengolahan keuangan retur penjualan.

* **Pengajuan Surat Permohonan Retur / SPR**:
  * Form pengajuan awal retur barang oleh Sales / SC.
  * Penentuan Tipe Retur (Biasa / Jagung), Detail Barang, No Faktur, No Batch, dan Tanggal Expired.
  * Pengisian Alasan Retur: Barang Bermasalah, Expired, Tidak Laku, Tes Market, Bad Debt, Harga Tidak Sesuai, SPR Intern.

* **Workflow Approval SPR Berjenjang**:
  * **SC**: Membuat & mengajukan SPR (Draft → Diajukan).
  * **Manager SC**: Verifikasi / Persetujuan awal (Diajukan → Diverifikasi Koor).
  * **Kadep Unit Bisnis (Kadep UB)**: Verifikasi khusus untuk retur komoditas Jagung.
  * **Admin Penjualan / Admin Retur**: Cek dokumen & penyesuaian rincian barang (Diverifikasi Koor → Dicek Admretur).
  * **Kadep SC**: Persetujuan Kepala Departemen (Dicek Admretur → Disetujui Kadep).
  * **Logistik / LPB2**: Verifikasi barang fisik masuk gudang dan pemprosesan Nota Retur Penjualan (Disetujui Kadep → Selesai).

* **Nota Retur Penjualan**:
  * Pembuatan Nota Retur Penjualan oleh Admin LPB2 berdasarkan SPR yang telah disetujui.
  * Otomatis meng-update Stok Gudang dan Ledger Stok.

* **Approval Berjenjang Nota Retur**:
  * Approval berjenjang Nota Retur Penjualan oleh Manager Account, Manager SC, Kadep UB, Manager SE, Kadep SC, Direktur Operasional, hingga Direktur Utama.

* **Penanganan Keuangan Retur**:
  * **Collection**: Pemotongan Piutang Customer berdasarkan Nota Retur Penjualan.
  * **Kasir**: Pengembalian Uang Tunai / Transfer ke Customer.
  * Otomatis pencatatan Jurnal Akuntansi Retur Penjualan (Potongan Penjualan / Kas / Piutang).

* **Cetak & Riwayat Retur**:
  * Cetak fisik SPR dan Nota Retur Penjualan dilengkapi stempel Tanda Tangan Digital (QR Code).
  * Audit trail riwayat persetujuan dan aktivitas retur penjualan.

---

### 5. Modul Keuangan & Pembayaran Faktur
Modul penatausahaan piutang dan penerimaan pembayaran faktur penjualan customer oleh tim Keuangan (KIU KEU / Kasir / Collection).

* **Dashboard Pembayaran & Monitoring Customer**:
  * Pencarian dan monitoring daftar customer yang memiliki faktur belum lunas.
  * Fitur pencarian kata kunci (`q`) berdasarkan nama atau kode customer.
  * System Alert / Notifikasi Bilyet Giro (BG) jatuh tempo yang siap dicairkan hari ini.

* **Modul Piutang Customer (Collection)**:
  * Monitoring menyeluruh seluruh faktur penjualan berstatus `selesai_do` yang belum lunas.
  * Pemantauan status keterlambatan (Overdue 30, Overdue 60, Overdue 90 hari) dan sisa hari jatuh tempo.
  * Tracking statistik piutang: Total Piutang, Total Pembayaran Masuk, Total BG Pending, dan Sisa Piutang.
  * Fitur **Export Excel**: Mengunduh laporan piutang customer lengkap dengan frekuensi bayar dan riwayat metode pembayaran secara realtime.

* **Input Pembayaran Faktur**:
  * Pemilihan faktur aktif customer berstatus `selesai_do`.
  * **Pilihan Metode Pembayaran**:
    * **Kas / Bank / Transfer**: Pilihan akun kas atau rekening bank resmi (Q Kas, A Kas, Q BCA, Q Mandiri, Q BRI, A BCA, A Mandiri, DLL).
    * **Potongan Retur (`Q Hutang Non Dagang`)**: Pembayaran menggunakan saldo retur penjualan customer yang telah disetujui Direksi.
    * **Bilyet Giro (BG)**: Pembayaran tunda menggunakan Bilyet Giro dengan pengisian tanggal estimasi cair. Status BG otomatis berstatus `pending` dan tidak langsung memotong sisa piutang sebelum dicairkan.
  * Input Potongan Diskon Pembayaran per faktur.
  * **Proteksi Sistem**:
    * Penguncian jumlah bayar + diskon tidak boleh melebihi sisa tagihan faktur.
    * Pengecekan saldo retur customer jika menggunakan metode `Q Hutang Non Dagang`.
    * Blokir pembukaan pembayaran BG baru jika faktur masih memiliki pembayaran BG berstatus `pending`.

* **Pencairan Bilyet Giro (BG)**:
  * Pengubahan status pembayaran BG dari `pending` menjadi `cair`.
  * Pencatatan audit trail petugas yang mencairkan (`bg_cair_by`) dan waktu pencairan (`bg_cair_at`).
  * Otomatis menambahkan nilai pembayaran BG ke Total Pembayaran dan memotong Sisa Tagihan Faktur secara permanen.

* **Integrasi Jurnal Keuangan Otomatis**:
  * Otomatis posting ke Jurnal Akuntansi Keuangan (`M_Journal`) saat transaksi pembayaran tunai/transfer disimpan atau saat status BG berubah menjadi `cair`.

* **Histori & Tracking Pembayaran**:
  * Logging riwayat transaksi pembayaran per faktur (Tanggal, Jumlah, Diskon, Metode, Audit User).
  * API JSON endpoint untuk penarikan data histori pembayaran faktur secara fleksibel.

