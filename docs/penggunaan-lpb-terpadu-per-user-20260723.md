# Panduan Penggunaan dan Aturan Per-User Modul LPB Terpadu

Tanggal: 2026-07-23

Dokumen ini menjadi panduan operasional untuk modul `LPB`, `LPB Manual`, `Jurnal LPB`, `Adjustment LPB`, dan `Retur Pembelian`. Tujuannya sederhana: setiap user tahu kapan boleh membuat transaksi, kapan hanya boleh memeriksa, dan kapan harus menyerahkan proses ke bagian lain.

## Route Modul

| Modul | Route utama | Fungsi |
| --- | --- | --- |
| LPB dari PO | `ics/icspo`, `ics/detail_po`, `ics/detail_record_lpb` | Penerimaan barang dari PO, draft temporary, final LPB, invoice, faktur, split invoice, detail LPB |
| LPB Manual | `ics/lpb_manual`, `ics/lpb_report`, `ics/lpb_manual_log` | Input LPB tanpa PO, laporan LPB manual/logistik, log teknis LPB manual |
| Jurnal LPB | `jurnal`, `keuangan/jurnal` | Monitoring jurnal pembelian otomatis dari LPB final |
| Adjustment LPB | `ics/retur/pembelian/adjustment` | Koreksi harga invoice LPB setelah barang/lot sudah bergerak |
| Retur Pembelian | `ics/retur/pembelian` | Draft, verifikasi, posting, dan void retur pembelian dari LPB final |

## Matrix Aturan Per-User

| User / Role | Boleh dilakukan | Tidak boleh dilakukan tanpa otorisasi |
| --- | --- | --- |
| Logistik / `ADMINLOGLPB` / `ADMLPB2` / `ADMINICS` | Membuka panel LPB Logistik, membuat draft penerimaan dari PO, mengisi qty, lot, expired, gudang, SJ, dan menyimpan final LPB. Melihat laporan LPB sumber logistik. | Mengubah invoice/faktur sebagai keputusan Purchasing. Membuat LPB Manual jika bukan role yang diizinkan. Mengubah harga setelah LPB berjalan tanpa workflow Purchasing/Adjustment. |
| Purchasing / `ADMINPURCHASING` / `ADMIN PO` / `admpo` | Membuka panel LPB Purchasing, memilih/menetapkan jenis LPB, update invoice, pecah invoice, update faktur pajak, input LPB Manual, melihat laporan LPB, membuat/mengecek adjustment harga, dan verifikasi Purchasing untuk retur pembelian. | Menghapus jejak LPB, mengubah stok fisik langsung, atau mem-posting jurnal manual untuk menggantikan jurnal otomatis. |
| Accounting / `ADMINKEU` / `ADMINKEUTC` | Membuka jurnal, memeriksa `Daftar Jurnal Pembelian`, memeriksa detail jurnal LPB, memeriksa posting exception, dan verifikasi Accounting pada retur pembelian. | Membuat LPB operasional atau adjustment tanpa dokumen pendukung dari Purchasing/Logistik. Menggunakan akun tebakan untuk kelompok dagang yang belum punya rule. |
| IT / Admin Support / `lv = 1` | Membuka log LPB Manual, membantu investigasi error, validasi route, validasi schema, dan support deploy/migrasi. | Melakukan posting operasional atas nama user bisnis kecuali untuk testing yang disetujui dan tercatat. |
| Supervisor / Management | Memantau laporan, meminta koreksi melalui workflow resmi, dan menyetujui kebijakan rule akun atau koreksi harga. | Menginstruksikan perubahan data langsung di database tanpa audit trail aplikasi. |

## 1. Penggunaan LPB dari PO

### Langkah Logistik

1. Login sebagai user Logistik atau Admin LPB.
2. Buka `ics/icspo`.
3. Pilih PO yang akan diterima lalu masuk ke `ics/detail_po`.
4. Klik tombol tambah draft pada barang yang diterima.
5. Isi qty diterima, satuan, nomor lot, expired date, dan data penerimaan sesuai fisik barang.
6. Klik `Simpan Draft`.
7. Ulangi sampai seluruh barang/lot yang diterima masuk ke `Draft Temporary Penerimaan`.
8. Pastikan `Nomor LPB`, `Nomor SJ`, tanggal, jenis LPB, gudang, dan keterangan sudah benar.
9. Klik `Simpan` untuk membuat final LPB.
10. Cek hasilnya di `ics/detail_record_lpb` atau `ics/lpb_report?source=logistik`.

### Langkah Purchasing

1. Login sebagai Purchasing atau Admin PO.
2. Buka `ics/icspo`.
3. Buka detail LPB dari tabel Purchasing.
4. Di `ics/detail_record_lpb`, pilih invoice pada panel `List Invoice LPB`.
5. Gunakan `Update Invoice` untuk memperbarui nomor/tanggal invoice.
6. Gunakan `Pecah Invoice` jika satu LPB harus dibagi menjadi beberapa invoice.
7. Gunakan `Update Faktur` untuk nomor/tanggal faktur pajak.
8. Gunakan tombol print bila dokumen LPB perlu dicetak.

### Aturan LPB dari PO

- LPB dari PO harus berdasarkan barang yang benar-benar diterima fisik.
- Qty draft tidak boleh melebihi sisa PO atau batas validasi sistem.
- Lot dan expired wajib mengikuti barang fisik.
- Perubahan invoice, faktur pajak, dan split invoice tidak boleh dipakai untuk mengubah status stok.
- Setelah LPB final dibuat, koreksi harga tidak dilakukan dengan edit langsung bila barang/lot sudah bergerak. Gunakan `Adjustment LPB`.
- Semua perubahan penting harus meninggalkan log aktivitas LPB.

## 2. Penggunaan LPB Manual

LPB Manual dipakai ketika penerimaan harus dicatat tanpa data PO. Ini harus diperlakukan sebagai jalur khusus, bukan jalan pintas untuk menghindari PO.

### Langkah Input

1. Login sebagai Purchasing, Admin PO, Admin, atau IT/Admin support yang diberi mandat.
2. Buka `ics/icspo`.
3. Klik `Input LPB Manual` atau langsung buka `ics/lpb_manual`.
4. Isi tanggal LPB, jenis LPB, gudang, nomor SJ, nomor invoice, dan keterangan.
5. Klik `Tambah Barang`.
6. Pilih barang dari list `tbpo_barang`.
7. Isi qty, satuan, nomor lot, expired date, dan harga satuan bila diperlukan.
8. Pastikan minimal ada satu baris barang valid.
9. Klik `Simpan LPB Manual`.
10. Setelah berhasil, cek di `ics/lpb_report?source=manual`.

### Aturan LPB Manual

- Wajib ada tanggal LPB, jenis LPB, gudang, minimal satu barang, qty lebih dari 0, nomor lot, dan expired date.
- Barang harus dipilih dari master `tbpo_barang`, bukan diketik bebas.
- LPB Manual langsung mencatat stok batch dan stock ledger, sehingga input harus dianggap final secara stok.
- Nomor referensi manual disimpan sebagai `kd_po`, `no_po`, dan `manual_ref_no`.
- IT/Admin hanya membuka `ics/lpb_manual_log` untuk audit teknis, bukan untuk mengganti pemeriksaan bisnis.
- Jika LPB Manual dipakai untuk koreksi historis, tulis alasan di keterangan agar bisa diaudit.

## 3. Penggunaan Jurnal LPB

Jurnal LPB dibuat otomatis dari LPB final. Accounting memeriksa hasilnya, bukan membuat ulang jurnal secara manual.

### Langkah Accounting

1. Login sebagai user yang memiliki akses modul jurnal.
2. Buka `jurnal` atau `keuangan/jurnal`.
3. Buka panel `Daftar Jurnal Pembelian`.
4. Cari nomor LPB, nomor PO, atau supplier.
5. Klik baris jurnal untuk melihat detail.
6. Cocokkan debit, kredit, tanggal, referensi LPB, supplier, dan user input.
7. Jika jurnal tidak muncul, cek apakah LPB sudah final dan memiliki `nomor_lpb`.
8. Jika muncul posting exception, tentukan penyebabnya sebelum retry posting.

### Rule Jurnal LPB Saat Ini

- Kelompok dagang `2` / BKP: Debit `14010`, Debit `13017`, Kredit `21098`.
- Kelompok dagang `3` / BKPS: Debit `14011`, Kredit `21098`.
- Kelompok dagang `4` dan `5` tidak diposting otomatis sampai rule akun ditentukan.
- Posting memakai source `LOGISTIK`, source type `LPB_FINAL`, dan event `GOODS_RECEIPT`.
- Jurnal yang tidak balance atau tidak punya akun valid harus dianggap belum sah untuk laporan.

## 4. Penggunaan Adjustment LPB

Adjustment LPB dipakai saat harga invoice benar berbeda dari harga LPB yang sudah berjalan, sementara stok/lot tidak boleh diedit langsung.

### Langkah Purchasing / Admin PO

1. Buka `ics/retur/pembelian/adjustment`.
2. Pilih `LPB Salah`.
3. Isi `Tanggal Adjustment`.
4. Cek detail barang, qty, harga lama, supplier, dan nomor LPB.
5. Isi `Harga Invoice Benar` pada semua baris detail.
6. Isi `Alasan Adjustment`.
7. Klik `Posting Adjustment`.
8. Catat nomor adjustment, nomor LPB salah, nomor LPB adjustment, nomor PRPP, dan selisih nilai.
9. Informasikan hasilnya ke Accounting untuk pemeriksaan jurnal.

### Aturan Adjustment LPB

- Dipakai hanya untuk koreksi harga, bukan koreksi qty fisik.
- Harga invoice benar wajib berbeda minimal pada satu detail.
- Semua detail wajib memiliki harga invoice benar.
- LPB asal wajib memiliki supplier yang valid.
- Sistem membuat LPB adjustment dengan lot `Adj. Harga Beli` dan expired `1000-01-01`.
- Sistem membuat PRPP otomatis sebagai pasangan koreksi.
- Setelah posting, stok lot dummy adjustment harus kembali ke saldo awal.
- Jika jurnal adjustment gagal, jangan ulang manual di database. Cek posting exception atau minta IT/Accounting melakukan investigasi.

## 5. Penggunaan Retur Pembelian

Retur Pembelian dipakai untuk mengembalikan barang dari LPB final dan menjaga stok batch, kartu stok, jurnal, serta audit approval.

### Langkah Membuat Draft

1. Buka `ics/retur/pembelian`.
2. Cari supplier, nomor PO, atau nomor LPB.
3. Pilih LPB final.
4. Cek detail barang, lot, expired, qty LPB, qty retur sebelumnya, stok fisik, dan harga.
5. Isi `Qty Retur` pada item yang dikembalikan.
6. Isi alasan item bila diperlukan.
7. Pilih jenis penyelesaian.
8. Isi alasan retur.
9. Klik `Buat Draft Retur`.

### Langkah Approval dan Posting

1. Klik `Submit` pada draft.
2. Purchasing klik `Purchasing` setelah mengecek supplier, harga, alasan, item, dan jenis penyelesaian.
3. Accounting klik `Accounting` setelah mengecek dampak Hutang Usaha, PPN, dan jurnal.
4. Setelah status `ACCOUNTING_VERIFIED`, klik `Post`.
5. Sistem mengurangi stok batch, menulis ledger `RBELI`, membuat jurnal `PURCHASE_RETURN`, dan mengunci dokumen menjadi `POSTED`.

### Void Retur Posted

1. Gunakan `Void` hanya untuk retur yang sudah `POSTED` dan memang harus dibatalkan.
2. Isi alasan void dengan jelas.
3. Sistem membuat jurnal reversal, mengembalikan stok batch, dan mengubah status menjadi `VOID`.
4. Dokumen posted tidak dihapus agar audit trail tetap lengkap.

### Aturan Retur Pembelian

- LPB wajib final dan memiliki `nomor_lpb`.
- Qty retur tidak boleh melebihi qty LPB dikurangi retur sebelumnya.
- Qty retur tidak boleh melebihi stok fisik batch.
- Lot dan expired harus sesuai batch LPB.
- Posting otomatis saat ini hanya aman untuk `jenis_penyelesaian = POTONG_HUTANG`.
- Kelompok dagang selain `2` dan `3` harus dipantau melalui posting exception.
- Void membutuhkan alasan bisnis yang bisa dipertanggungjawabkan.

## Checklist Harian

| Bagian | Checklist |
| --- | --- |
| Logistik | LPB dari PO sudah sesuai barang fisik, lot, expired, gudang, dan qty. |
| Purchasing | Invoice, faktur pajak, split invoice, LPB Manual, retur, dan adjustment memiliki alasan/dokumen pendukung. |
| Accounting | Jurnal LPB, PRPP, dan retur sudah balance; exception ditindaklanjuti; akun kelompok dagang sudah sesuai rule. |
| IT/Admin | Log error LPB Manual dan posting exception dipantau tanpa mengubah transaksi bisnis langsung. |

## Prinsip Kontrol

1. Transaksi LPB adalah pintu masuk stok dan hutang, jadi akurasi fisik dan akurasi harga harus berjalan bersama.
2. Koreksi setelah barang bergerak harus memakai workflow yang meninggalkan audit trail.
3. User menjalankan tugas sesuai peran. Bila satu orang memegang akses admin, aktivitas tetap harus mengikuti peran bisnis yang sedang dilakukan.
4. Data yang sudah posted tidak dihapus. Koreksi dilakukan melalui adjustment, retur, reversal, atau void resmi.
5. Jika sistem menolak posting, perlakukan sebagai sinyal kontrol, bukan hambatan yang boleh dilewati lewat edit database.
