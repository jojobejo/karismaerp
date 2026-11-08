# Panduan Lengkap & Rincian Aktivitas Modul Sistem KARISMA ERP

Dokumen ini memuat rincian aktivitas, kontrol sistem, dan alur kerja detail dari 20 modul utama sistem KARISMA ERP.

---

## 1. Modul Sales Order (SO)
**Tujuan & Deskripsi**: Mengelola seluruh siklus pemesanan barang dari customer/kios sebelum barang diproses oleh gudang dan logistik.

**Aktivitas & Tindakan Operasional Detail**:
- **Membuat Pesanan Pelanggan (Create SO)**: Input pemesanan baru berdasarkan salesman, customer/toko, tanggal transaksi, dan gudang pemenuhan.
- **Menentukan Rute Pengiriman**: Memilih rute pengiriman armada (misal: MLG, SDA, PAS) agar pesanan terkelompokkan dalam satu jadwal logistik.
- **Validasi Batas Tonase & Kubikasi Armada**: Memastikan total berat (ton) dan volume kubikasi ($m^3$) barang pesanan tidak melebihi kapasitas muat maksimal armada kendaraan.
- **Reservasi Stok Otomatis**: Sistem secara otomatis mengunci stok barang (`qty_reserved`) di gudang asal agar stok tidak terambil oleh pesanan lain.
- **Proteksi Plafon Kredit Pelanggan**: Pengecekan limit piutang customer; metode pembayaran Transfer dan BG memotong plafon aktif, sedangkan pembayaran Cash tidak memotong plafon.
- **Dukungan Transaksi Khusus**: Menyediakan opsi penanda **Faktur Z** (faktur konsolidasi untuk dipecah) dan **Barang Test Market** (uji pasar).
- **Rekam SO (Draft $\rightarrow$ Open)**: Mengonfirmasi pesanan dari status Draft menjadi Open agar siap diproses loading oleh tim warehouse/checker.
- **Permohonan & Persetujuan Pembatalan SO**: Pembatalan SO harus mengajukan alasan pembatalan dan mewajibkan approval resmi dari atasan (**MNGSC / Manager SC**). Setelah disetujui, reservasi stok dilepas dan plafon customer dipulihkan.

---

## 2. Modul Digital Plafon
**Tujuan & Deskripsi**: Mengatur, memonitor, dan membatasi risiko kredit piutang pelanggan secara realtime.

**Aktivitas & Tindakan Operasional Detail**:
- **Pemberian Limit Plafon Customer**: Menentukan plafon maksimal kredit yang dapat digunakan oleh masing-masing toko/kios.
- **Monitoring Sisa Plafon Realtime**: Memantau pergerakan plafon aktif, piutang yang sedang berjalan, dan riwayat pelunasan.
- **Pemotongan Plafon Otomatis**: Memotong plafon saat Sales Order / Faktur bertempo/kredit (Transfer, BG) diterbitkan.
- **Restorasi Plafon saat Pelunasan**: Menambah kembali saldo plafon aktif secara otomatis saat bagian Keuangan mencatat pelunasan faktur.
- **Pencegahan Transaksi Over-Plafon**: Mengunci proses pembuatan SO jika sisa plafon customer tidak mencukupi untuk nominal pesanan baru.

---

## 3. Modul Pengajuan Overdue (OD)
**Tujuan & Deskripsi**: Mengelola permohonan dispensasi pembukaan kunci transaksi bagi pelanggan yang memiliki tunggakan/jatuh tempo.

**Aktivitas & Tindakan Operasional Detail**:
- **Deteksi Otomatis Faktur Jatuh Tempo**: Sistem mendeteksi faktur-faktur customer yang telah melewati tanggal jatuh tempo pembayaran (*overdue*).
- **Penguncian Otomatis Order Overdue**: Sistem memblokir customer yang memiliki tagihan overdue agar tidak bisa order kembali tanpa persetujuan.
- **Input Permohonan Pengajuan OD**: Salesman/Admin mengajukan dispensasi pembukaan kunci dengan menyertakan komitmen tanggal pembayaran dan alasan riil.
- **Approval Berjenjang Atasan**: Memproses approval berjenjang oleh Manajer Keuangan / Direksi untuk memberikan izin transaksi sementara.
- **Pelepasan Kunci Transaksi Sementara**: Membuka kunci customer untuk sejumlah transaksi tertentu sesuai batas dispensasi yang disetujui.

---

## 4. Modul Penerbitan Faktur (Admin SC & Invoicing)
**Tujuan & Deskripsi**: Memproses Sales Order yang telah siap kirim menjadi dokumen legal Faktur Komersial Penjualan.

**Aktivitas & Tindakan Operasional Detail**:
- **Pemeriksaan Kesiapan SO**: Memilih SO berstatus Open yang barangnya sudah selesai diperiksa dan dimuat oleh Checker.
- **Penerbitan Nomor Faktur Resmi**: Menghasilkan nomor faktur resmi dengan kode awalan sesuai tipe transaksi:
  - Kode Reguler: Faktur komersial umum.
  - Kode `Z`: Faktur gabungan untuk dipecah.
  - Kode `T`: Faktur titipan uji pasar (Test Market).
  - Kode `K`: Faktur penjualan barang konsinyasi.
- **Alokasi Batch/Lot & Tanggal Kedaluwarsa**: Memastikan nomor lot dan tanggal expired barang tercantum presisi pada faktur penjualan.
- **Integrasi Cetak Faktur Dot-Matrix**: Fasilitas cetak massal faktur per rute pengiriman logistik menggunakan format cetak dot-matrix (LX-310).
- **Sinkronisasi Jurnal Penjualan**: Memicu terbentuknya Jurnal Penjualan (Piutang Usaha vs Penjualan & PPN) dan Jurnal Pengeluaran Barang / HPP.

---

## 5. Modul Pecah Faktur Z (Faktur Turunan Kode H)
**Tujuan & Deskripsi**: Memecah satu Faktur Z konsolidasi menjadi beberapa faktur pecahan berawalan `H` untuk tanda terima ke toko/kontak person berbeda.

**Aktivitas & Tindakan Operasional Detail**:
- **Monitoring Faktur Z Belum Dipecah**: Menampilkan daftar Faktur Z yang terbit beserta sisa kuantitas barang yang belum dialokasikan.
- **Pemecahan Tunggal & Pemecahan Batch (Multi-Faktur)**: Memilih satu atau beberapa Faktur Z sekaligus untuk dipecah ke sejumlah $N$ faktur turunan.
- **Penentuan Kontak Person / Customer Penerima**: Mengarahkan tiap slot pecahan ke kontak person acak atau toko penerima barang.
- **Alokasi Kuantitas & Skema Potongan 20%**: Membagi kuantitas barang per slot dengan penerapan otomatis skema potongan harga 20%.
- **Penerbitan Faktur H**: Menghasilkan dokumen faktur pecahan berkode awalan `H` ke tabel terpisah tanpa memotong stok fisik ulang.
- **Pencatatan Jurnal Pelunasan Faktur H (Q & A)**: Ketika Faktur H dilunasi via import/kasir, sistem membentuk Jurnal Pembayaran Acak (Q) dan Jurnal Kas Masuk Potongan (A).
- **Reset / Batal Pecah Faktur**: Fitur untuk mereset seluruh pecahan dan mengembalikan status Faktur Z menjadi belum dipecah.

---

## 6. Modul Activity Warehouse & Checker Loading
**Tujuan & Deskripsi**: Mengontrol proses fisik penyiapan dan pemuatan barang pesanan ke dalam armada pengiriman.

**Aktivitas & Tindakan Operasional Detail**:
- **Pemilihan Jadwal Loading Rute**: Checker memilih rute yang akan dimuat berdasarkan trip logistik hari berjalan.
- **Start / Mulai Loading**: Mencatat waktu mulai loading, nama checker, dan armada kendaraan.
- **Proteksi Salah Input Qty Dimuat**:
  - Jika pesanan adalah **box utuh saja**, kolom pcs dinonaktifkan (`readonly`).
  - Jika pesanan adalah **eceran/pcs saja**, kolom box dinonaktifkan (`readonly`).
- **Fitur Pause & Lanjutkan Loading**: Menjeda penghitungan durasi loading jika ada kendala operasional gudang dan melanjutkannya kembali.
- **Fitur Batalkan Start Loading**: Memungkinkan pembatalan status mulai loading jika user salah klik, mengembalikan rute ke status *Siap Loading*.
- **Konfirmasi Selesai Loading (Done)**: Mengunci jumlah barang aktual yang dimuat ke truk dan mengirim status selesai ke bagian faktur.

---

## 7. Modul Delivery Order (DO) / Surat Jalan
**Tujuan & Deskripsi**: Menerbitkan dokumen jalan pengangkutan dan pengawalan distribusi barang dari gudang hingga ke tangan penerima.

**Aktivitas & Tindakan Operasional Detail**:
- **Pembuatan Delivery Order (DO)**: Menghasilkan dokumen Surat Jalan pengiriman berdasarkan hasil pemuatan barang yang telah dikonfirmasi Checker.
- **Pencantuman Data Supir, Kernet, dan Plat Armada**: Menugaskan personil pengantar dan armada kendaraan yang membawa barang.
- **Cetak Surat Jalan / Bukti Terima**: Mencetak dokumen fisik rangkap untuk ditandatangani oleh penerima di kios/toko tujuan.
- **Monitoring Status Pengiriman (In Transit $\rightarrow$ Selesai)**: Memantau armada yang sedang jalan dan memvalidasi DO yang telah kembali (*DO Balik*).
- **Pengurangan Stok Fisik Akhir**: Mengesahkan pengeluaran fisik barang dari saldo gudang asal berdasarkan realisasi Surat Jalan.

---

## 8. Modul Inventory Control System (ICS)
**Tujuan & Deskripsi**: Pusat kendali persediaan, kartu stok, batch/lot tracking, dan pengawasan batas minimum persediaan.

**Aktivitas & Tindakan Operasional Detail**:
- **Kartu Stok Realtime (Stock Ledger)**: Mencatat setiap riwayat mutasi masuk (`IN`), keluar (`OUT`), pesanan dikunci (`RESERVE`), dan pelepasan kunci (`RELEASE`).
- **Pelacakan Nomor Lot & Expired Date**: Memantau umur simpan barang (*First Expired, First Out* / FEFO) untuk mencegah kedaluwarsa di gudang.
- **Monitoring Multi-Gudang**: Memantau stok di berbagai gudang (Gudang Induk, Gudang Konsinyasi, Gudang Test Market, Gudang Bundling, dll).
- **Stock Opname & Penyesuaian**: Melakukan pencocokan stok fisik berkala dan mencatat selisih (*adjustment*) ke laporan persediaan.
- **Audit Mutasi Barang**: Rekonsiliasi antara mutasi fisik pergudangan dengan saldo akun persediaan di neraca akuntansi.

---

## 9. Modul Bufferstock
**Tujuan & Deskripsi**: Menjaga ketersediaan stok pengaman (*safety stock*) untuk mengantisipasi lonjakan permintaan atau keterlambatan supplier.

**Aktivitas & Tindakan Operasional Detail**:
- **Pengaturan Batas Minimum & Maksimum Stok**: Menentukan ambang batas minimum (*reorder point*) untuk setiap SKU produk.
- **Notifikasi / Early Warning Alert**: Memberikan peringatan otomatis saat persediaan barang mendekati batas kritis pengaman.
- **Analisis Kebutuhan Pengadaan**: Menghitung estimasi rata-rata konsumsi harian/bulanan untuk rekomendasi kuantitas pembelian berikutnya.
- **Integrasi ke Rencana Pembelian (PO)**: Mempermudah staf purchasing menyusun Purchase Order berdasarkan item-item yang mencapai status buffer stock.

---

## 10. Modul Paket Bundling
**Tujuan & Deskripsi**: Mengelola perakitan (*kitting*) beberapa barang satuan menjadi satu paket komersial baru dengan harga khusus.

**Aktivitas & Tindakan Operasional Detail**:
- **Definisi Formula / BOM Paket**: Menentukan item-item komponen pembentuk paket bundling beserta kuantitas masing-masing.
- **Eksekusi Perakitan (Bundling Production)**:
  - Mengurangi stok item komponen dari gudang asal.
  - Menambah stok barang jadi paket bundling ke gudang bundling.
- **Perhitungan HPP Rata-rata Paket**: Menghitung akumulasi HPP per unit paket berdasarkan total biaya komponen yang digunakan.
- **Dukungan Pembongkaran (Unbundling)**: Mengembalikan paket bundling yang tidak laku menjadi item komponen satuan asalnya.

---

## 11. Modul LPB Penerimaan Barang Masuk (Laporan Penerimaan Barang)
**Tujuan & Deskripsi**: Memverifikasi fisik barang yang datang dari supplier/pabrik sebelum dimasukkan ke dalam stok gudang.

**Aktivitas & Tindakan Operasional Detail**:
- **Pencocokan Fisik vs Surat Jalan Supplier & PO**: Memverifikasi kesesuaian jenis barang, kuantitas, kemasan, lot number, dan tanggal kedaluwarsa.
- **Pencatatan Selisih & Kerusakan Fisik**: Menandai barang yang rusak atau kurang kirim sebelum diterima secara legal.
- **Konfirmasi Penerimaan Resmi (Posting LPB)**:
  - Menambah stok fisik barang ke saldo gudang induk dan batch aktif.
  - Membentuk Jurnal Akrual Penerimaan Barang / GRNI (*Goods Receipt Not Invoiced*).
- **Penerbitan Bukti LPB**: Mencetak tanda terima LPB resmi untuk diserahkan ke bagian Finance/Purchasing guna proses penagihan faktur.

---

## 12. Modul Purchase Order (PO) Komersil & Non-Komersil
**Tujuan & Deskripsi**: Mengatur pengadaan barang dagang (komersil) maupun perlengkapan operasional kantor/pabrik (non-komersil).

**Aktivitas & Tindakan Operasional Detail**:
- **Input Surat Pesanan (PO)**: Membuat pesanan resmi ke prinsipal/supplier dengan ketentuan harga beli, diskon prinsipal, termin pembayaran, dan PPN.
- **Pemisahan Jalur PO Komersil vs Non-Komersil**:
  - PO Komersil: Menambah persediaan barang dagang yang akan dijual kembali.
  - PO Non-Komersil: Pengadaan aktiva, ATK, pemeliharaan armada, atau biaya operasional.
- **Approval Otoritas Pembelian**: Mengirim notifikasi pengajuan persetujuan ke Manajer Purchasing / Direksi sebelum PO dikirim ke supplier.
- **Pencatatan & Pengiriman ke Supplier**: Mengirim salinan PO resmi (PDF/Print) ke vendor dan memantau estimasi tanggal kedatangan (*lead time*).
- **Monitoring Outstanding Pemenuhan PO**: Memantau persentase barang yang sudah tiba (via LPB) terhadap sisa barang yang masih dalam pesanan.

---

## 13. Modul Pelunasan LPB (Purchase Invoice & Hutang Usaha)
**Tujuan & Deskripsi**: Mencocokkan tagihan faktur pembelian dari supplier dengan bukti penerimaan barang fisik serta melakukan pembayaran hutang.

**Aktivitas & Tindakan Operasional Detail**:
- **Verifikasi 3-Way Matching**: Mencocokkan kesesuaian antara dokumen Purchase Order, LPB Penerimaan Barang, dan Faktur Pajak/Tagihan dari Supplier.
- **Pencatatan Faktur Pembelian (Purchase Invoice)**: Mengakui Hutang Usaha resmi dan mengeliminasi saldo sementara akun GRNI.
- **Penjadwalan Pembayaran Hutang**: Mengatur jadwal pembayaran tagihan supplier sesuai termin jatuh tempo guna menjaga arus kas.
- **Eksekusi Pelunasan Hutang**: Mencatat pembayaran hutang dagang via Kas atau Bank, disertai potongan pelunasan atau biaya administrasi.
- **Penerbitan Bukti Pembayaran**: Mencetak bukti kas keluar / voucher pelunasan hutang supplier.

---

## 14. Modul Retur Pembelian
**Tujuan & Deskripsi**: Mengelola pengembalian barang dagang yang rusak, cacat, atau tidak sesuai spesifikasi kembali ke supplier.

**Aktivitas & Tindakan Operasional Detail**:
- **Identifikasi Barang Cacat / Rusak**: Menyeleksi barang yang akan diretur dari gudang karantina atau gudang induk.
- **Pembuatan Nota Retur Pembelian**: Menerbitkan dokumen retur resmi dengan referensi nomor LPB / Faktur Pembelian asal.
- **Pemotongan Stok Barang**: Mengeluarkan fisik barang dari kartu stok gudang (`OUT Retur`).
- **Penerbitan Nota Debet (Debit Note)**: Memotong saldo hutang dagang kepada supplier atau mencatat piutang klaim retur.
- **Jurnal Retur Pembelian**: Menyesuaikan saldo Hutang Dagang (Debit) terhadap Persediaan dan PPN Masukan (Kredit).

---

## 15. Modul Retur Penjualan
**Tujuan & Deskripsi**: Menerima kembali produk yang dikembalikan oleh pelanggan/kios karena rusak, mendekati expired, atau kelebihan kirim.

**Aktivitas & Tindakan Operasional Detail**:
- **Pengajuan Klaim Retur oleh Customer**: Mencatat permohonan retur dari salesman/kios dengan nomor referensi faktur penjualan asal.
- **Pemeriksaan Fisik oleh QC / Logistik**: Memeriksa kelayakan barang (apakah barang dapat dijual kembali, harus dibundling ulang, atau dimusnahkan).
- **Penerbitan Nota Retur Penjualan (Credit Note)**:
  - Memotong saldo piutang customer yang bersangkutan atau menerbitkan nota kredit.
  - Memasukkan kembali barang ke gudang (Gudang Induk atau Gudang BS/Rusak).
- **Jurnal Retur Penjualan**: Menyesuaikan omzet Penjualan (Debit) dan memotong Piutang Usaha pelanggan (Kredit).

---

## 16. Modul Kas Masuk & Kas Keluar
**Tujuan & Deskripsi**: Mencatat seluruh pergerakan uang tunai dan rekening bank di luar transaksi langsung faktur dagang.

**Aktivitas & Tindakan Operasional Detail**:
- **Pencatatan Kas Masuk (KM)**: Menerima pendapatan lain-lain, penerimaan setoran salesman, pengembalian panjar/kas bon, atau pendapatan non-operasional.
- **Pencatatan Kas Keluar (KK)**: Membayar beban operasional harian, biaya BBM/sopir armada, tagihan listrik/air, gaji karyawan, dan kas bon.
- **Validasi Saldo Kas/Bank Realtime**: Memastikan saldo kas fisik tidak menjadi minus saat pengeluaran dicatat.
- **Penerbitan Voucher Kas/Bank**: Mencetak bukti kuitansi resmi bertanda tangan kasir dan penerima uang.
- **Integrasi Jurnal Kas Otomatis**: Membentuk ayat jurnal secara otomatis ke buku besar kas/bank lawan akun beban/pendapatan terkait.

---

## 17. Modul Settingan Kode Akun & Jurnal (Chart of Accounts & Mapping)
**Tujuan & Deskripsi**: Pondasi master akuntansi untuk menyusun bagan akun (*COA*) dan aturan jurnal otomatis ERP.

**Aktivitas & Tindakan Operasional Detail**:
- **Manajemen Chart of Accounts (COA)**: Menambah, mengubah, mengaktifkan/menonaktifkan akun akuntansi (Aset, Kewajiban, Ekuitas, Pendapatan, HPP, Beban).
- **Penetapan Klasifikasi & Saldo Normal**: Mengatur klasifikasi laporan (Neraca atau Laba Rugi) dan saldo normal akun (Debit/Kredit).
- **Konfigurasi Akun Default / Mapping Otomatis**:
  - Akun Piutang Usaha default.
  - Akun Penjualan & PPN Keluaran default.
  - Akun Persediaan & HPP default per kelompok barang.
  - Akun Kasir & Rekening Bank transaksi.
- **Manajemen Periode Fiskal & Tutup Buku**: Mengatur status periode akuntansi (Open / Closed) untuk mencegah manipulasi data historis.

---

## 18. Modul Jurnal Umum (Memorial Journal)
**Tujuan & Deskripsi**: Wadah pencatatan transaksi akuntansi non-operasional dan ayat jurnal penyesuaian (*adjustment*).

**Aktivitas & Tindakan Operasional Detail**:
- **Input Ayat Jurnal Manual**: Menginput transaksi memorial dengan memilih akun debit dan kredit secara berimbang (*balance*).
- **Jurnal Penyesuaian Akhir Periode (Adjustment)**: Mencatat beban penyusutan aktiva tetap, amortisasi, akrual biaya, dan koreksi pembukuan.
- **Validasi Keseimbangan (Debit = Kredit)**: Sistem menolak penyimpanan transaksi jurnal jika total debit tidak sama dengan total kredit.
- **Audit Trail & Persetujuan Jurnal**: Menyimpan data pembuat, waktu posting, serta status jurnal (`DRAFT`, `POSTED`, `VOID`, `REVERSED`).
- **Fasilitas Reversal Jurnal**: Membatalkan atau membalikkan ayat jurnal secara tertib audit tanpa menghapus jejak pembukuan lama.

---

## 19. Modul Buku Besar (General Ledger)
**Tujuan & Deskripsi**: Rekapitulasi pergerakan mutasi debit, kredit, dan saldo berjalan untuk setiap akun akuntansi.

**Aktivitas & Tindakan Operasional Detail**:
- **Filter Akun & Rentang Tanggal**: Menampilkan riwayat transaksi per kode akun dalam periode harian, bulanan, atau tahunan.
- **Perhitungan Saldo Awal, Mutasi, dan Saldo Akhir**: Menghitung saldo secara realtime berdasarkan saldo normal klasifikasi akun.
- **Fasilitas Drill-Down / Penelusuran Transaksi**: Mengklik nomor referensi jurnal pada baris buku besar untuk langsung melihat dokumen sumber aslinya (faktur, kas keluar, LPB, dll).
- **Pemeriksaan Selisih & Rekonsiliasi**: Mempermudah akuntan dalam mendeteksi ketidaksesuaian saldo akun dengan data operasional fisik.
- **Export & Cetak Buku Besar**: Mengunduh buku besar ke format Excel atau mencetaknya untuk kebutuhan audit eksternal.

---

## 20. Modul Neraca & Laba Rugi (Financial Statements)
**Tujuan & Deskripsi**: Laporan keuangan puncak yang menggambarkan posisi kekayaan, kewajiban, dan kinerja profitabilitas perusahaan.

**Aktivitas & Tindakan Operasional Detail**:
- **Laporan Laba Rugi (Income Statement)**:
  - Menyajikan akumulasi Pendapatan Penjualan dikurangi Potongan dan Retur.
  - Menghitung Beban Pokok Penjualan (HPP) untuk menghasilkan Laba Kotor (*Gross Profit*).
  - Mengurangi Beban Operasional, Pemasaran, dan Umum untuk menyajikan Laba Bersih Usaha (*Net Income*).
- **Laporan Neraca (Balance Sheet)**:
  - Menyajikan posisi Aset Lancar (Kas, Bank, Piutang Usaha, Persediaan Barang) dan Aset Tetap.
  - Menyajikan posisi Kewajiban (Hutang Usaha, Hutang Pajak, Biaya Akrual).
  - Menyajikan posisi Ekuitas Modal dan Laba Berjalan tahun/bulan aktif.
  - Memastikan validitas rumus dasar akuntansi: $\text{Aset} = \text{Kewajiban} + \text{Ekuitas}$ (indikator selisih seimbang / 0,00).
- **Filter Periode Laporan & Segmentasi Akun**: Fleksibilitas memilih tanggal cut-off neraca dan periode laba rugi.

