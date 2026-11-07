# Konsep Development Module Pembayaran Hutang Supplier

Tanggal: 2026-07-27  
Scope: konsep dan scanning awal untuk module pembayaran hutang perusahaan ke supplier/pemasok berdasarkan referensi Zahir Desktop dan kondisi aktual `karismaerp`.

## Ringkasan Eksekutif

Istilah bisnis yang tepat untuk kebutuhan ini adalah **pembayaran hutang perusahaan ke supplier / pemasok** atau **Accounts Payable payment**. Kata "piutang" biasanya dipakai untuk tagihan perusahaan kepada customer; untuk supplier, posisi perusahaan adalah pihak yang berhutang setelah barang diterima/invoice supplier diakui.

Konsep yang direkomendasikan adalah membuat menu operasional baru di Keuangan, misalnya:

- `keuangan/pembayaran-supplier`
- `keuangan/pembayaran-supplier/detail/{id_supplier}`
- `keuangan/pembayaran-supplier/form`
- `keuangan/pembayaran-supplier/post`
- `keuangan/pembayaran-supplier/void`

Module ini tidak perlu membuat engine jurnal baru dari nol. Project sudah memiliki `Accounting_service::create_payment()` yang mendukung `SUPPLIER_PAYMENT`, tabel `tbkeu_pembayaran`, tabel `tbkeu_pembayaran_alokasi`, event jurnal `SUPPLIER_PAYMENT`, dan mapping akun minimal. Yang perlu dikembangkan adalah layar kerja, query outstanding supplier, validasi alokasi terhadap PO/LPB/Retur/Invoice, serta perapihan titik integrasi hutang agar pembacaan saldo supplier stabil.

## Referensi Gambar Zahir

Gambar pertama memperlihatkan daftar penerimaan barang dengan kolom:

- Referensi
- Tanggal
- No. PO
- Supplier
- Kurs
- Nilai
- Status/posting
- Aksi: Baru, Hapus, Unpost, Perincian, Cetak, Tutup

Gambar kedua memperlihatkan detail pembelian/invoice supplier:

- Nama pemasok
- No. Pembelian
- Nomor PO
- Tanggal faktur
- Masuk ke gudang
- Keterangan
- Detail barang: kode barang, deskripsi, jumlah, satuan, harga, diskon, total, pajak
- Total pajak, total setelah pajak, dibayar/uang muka, saldo terhutang
- Tombol rekam/draft/cetak

Untuk `karismaerp`, konsep UI sebaiknya tidak meniru layout 1:1, tetapi mengambil pola bisnisnya:

- layar daftar tagihan supplier seperti daftar penerimaan Zahir;
- layar detail tagihan yang menggabungkan PO, LPB, invoice, dan retur;
- layar pembayaran yang bisa memilih beberapa dokumen dan mengalokasikan nilai pembayaran.

## Hasil Scanning Project

### Route dan Module yang Sudah Ada

Route terkait Keuangan:

- `keuangan/pembayaran` saat ini mengarah ke `keuangan/C_pembayaran` dan fokusnya pembayaran faktur customer.
- `accounting` dan `keuangan/accounting` mengarah ke `keuangan/C_Accounting`.
- Route jurnal tersedia untuk pembelian, pembayaran, list jurnal, detail jurnal, dan report.

Route terkait LPB/PO/Retur:

- `ics/icspo` untuk dashboard PO/LPB.
- `ics/detail_record_lpb` untuk detail LPB.
- `ics/ajax_update_invoice` untuk update invoice LPB.
- `ics/ajax_post_lpb` dan `ics/ajax_unpost_lpb` untuk status LPB.
- `ics/retur/pembelian/*` untuk workflow retur pembelian.

### Controller, Model, dan Service Relevan

Yang dapat dipertahankan:

- `application/controllers/keuangan/C_Accounting.php`
  - sudah punya `payment_store()` yang memanggil `Accounting_service::create_payment()`.
- `application/libraries/Accounting_service.php`
  - sudah punya `create_payment()`, `post_payment()`, event `SUPPLIER_PAYMENT`, alokasi invoice, validasi outstanding, dan posting jurnal.
- `application/libraries/Accounting_source_service.php`
  - sudah punya `post_goods_receipt($idLpb)` untuk LPB final.
- `application/models/M_ReturPembelian.php`
  - sudah punya workflow draft, submit, verify purchasing, verify accounting, post, void, dan jurnal `PURCHASE_RETURN`.
- `application/models/M_Logistik.php` dan `application/controllers/logistik/C_Ics.php`
  - tetap menjadi source PO/LPB/invoice/retur operasional.

Yang tidak cocok dijadikan fondasi supplier payment:

- `application/controllers/keuangan/C_pembayaran.php`
- `application/models/M_pembayaran.php`
- `tbkeu_pembayaran_faktur`

Alasannya: flow ini spesifik ke faktur penjualan/customer dan memakai jurnal lama `M_Journal::post_jurnal_pembayaran()`. Untuk supplier payment, jangan menambah percabangan AP di flow customer agar AR dan AP tidak tercampur.

## Konsep Menu

### 1. Dashboard Pembayaran Supplier

Tujuan: Finance melihat daftar supplier yang masih memiliki hutang terbuka.

Kolom rekomendasi:

- Supplier
- Total dokumen
- Total tagihan
- Total retur potong hutang
- Total sudah dibayar
- Sisa hutang
- Overdue tertua
- Aksi: Detail, Bayar

Filter:

- supplier
- tanggal invoice / tanggal LPB
- status: semua, belum lunas, sebagian, lunas, exception
- gudang
- PO
- invoice supplier

### 2. Detail Supplier

Tujuan: Finance melihat daftar dokumen yang membentuk hutang supplier.

Kolom rekomendasi:

- No. LPB
- No. PO
- No. Invoice Supplier
- Tanggal LPB
- Tanggal Invoice
- Kurs
- DPP
- PPN
- Total tagihan
- Retur potong hutang
- Sudah dibayar
- Sisa hutang
- Status jurnal LPB
- Status dokumen

Validasi tampilan:

- LPB tanpa jurnal `GOODS_RECEIPT` diberi status "Belum siap bayar".
- LPB tanpa supplier valid diberi status "Supplier belum terbaca".
- Invoice kosong tetap boleh tampil sebagai "Belum lengkap invoice", tetapi tidak direkomendasikan untuk posting pembayaran final.
- Retur `POTONG_HUTANG` status `POSTED` mengurangi outstanding.

### 3. Form Pembayaran

Tujuan: Finance memasukkan pembayaran ke satu atau banyak invoice/LPB supplier.

Field header:

- nomor pembayaran
- tanggal pembayaran
- supplier
- akun kas/bank
- metode pembayaran: kas, bank transfer, giro/BG, kompensasi, lain-lain
- jumlah pembayaran
- keterangan
- upload bukti pembayaran, optional fase lanjut

Grid alokasi:

- pilih invoice / LPB
- total tagihan
- retur potong hutang
- sisa hutang
- nominal dialokasikan
- catatan

Aturan:

- total alokasi tidak boleh melebihi nominal pembayaran;
- alokasi per invoice tidak boleh melebihi outstanding;
- pembayaran dapat partial;
- sisa pembayaran boleh menjadi `unapplied_amount`, tetapi saat tutup periode harus nol atau dijelaskan;
- posting membuat jurnal `SUPPLIER_PAYMENT`;
- jurnal posted tidak boleh diedit/hapus, koreksi melalui void/reversal.

### 4. History Pembayaran

Tujuan: audit trail pembayaran supplier.

Kolom:

- nomor pembayaran
- tanggal
- supplier
- amount
- allocated amount
- unapplied amount
- status
- nomor jurnal
- created by
- aksi detail / void

## Alur Bisnis Rekomendasi

1. Purchasing/Logistik menyelesaikan PO dan LPB.
2. Purchasing mengisi nomor invoice supplier di detail LPB.
3. Accounting memastikan LPB sudah `POST`, harga detail lengkap, dan jurnal `GOODS_RECEIPT` berhasil.
4. Jika ada retur pembelian, workflow retur berjalan sampai `POSTED` dengan `jenis_penyelesaian = POTONG_HUTANG`.
5. Finance membuka dashboard pembayaran supplier.
6. Finance memilih supplier dan invoice/LPB yang akan dibayar.
7. Finance mengisi nominal pembayaran dan alokasi.
8. Sistem memvalidasi outstanding berdasarkan jurnal hutang dan alokasi pembayaran.
9. Sistem menyimpan `tbkeu_pembayaran`, `tbkeu_pembayaran_alokasi`, dan membuat jurnal `SUPPLIER_PAYMENT`.
10. Jika ada koreksi, Finance melakukan void/reversal, bukan edit jurnal posted.

## Jurnal Yang Terlibat

### Saat LPB / Penerimaan Barang

Konsep sehat:

- Debit Persediaan
- Debit PPN Masukan jika pajak
- Kredit Hutang Usaha atau GRNI, tergantung kebijakan akuntansi final

Project saat ini sudah mem-posting `GOODS_RECEIPT` dari LPB. Namun ditemukan implementasi LPB/retur memakai akun `21098` yang pada database lokal memiliki `tipe_kontrol = NONE` dan nama akun kosong. Ini harus dibereskan sebelum outstanding hutang dihitung berbasis `tipe_kontrol = HUTANG`.

### Saat Retur Pembelian Potong Hutang

Untuk `jenis_penyelesaian = POTONG_HUTANG`:

- Debit Hutang Usaha
- Kredit Persediaan
- Kredit PPN Masukan jika ada

Project sudah memiliki `M_ReturPembelian` untuk workflow ini dan jurnal `PURCHASE_RETURN`.

### Saat Pembayaran Supplier

Event: `SUPPLIER_PAYMENT`

- Debit Hutang Usaha
- Kredit Kas/Bank

Jika pembayaran dialokasikan ke beberapa invoice/LPB, baris hutang harus dipecah per `nomor_dokumen` agar aging dan outstanding per dokumen dapat dibaca.

## Yang Dipertahankan

- Struktur accounting utama: `tbkeu_jurnal`, `tbkeu_jurnal_detail`, `tbkeu_mapping_akun`, `tbkeu_posting_exception`, `tbkeu_pembayaran`, `tbkeu_pembayaran_alokasi`.
- Service `Accounting_service::create_payment()` sebagai engine posting payment.
- LPB final sebagai sumber kewajiban supplier.
- Retur pembelian posted sebagai pengurang hutang supplier.
- Prinsip jurnal immutable: posted tidak diedit/hapus, koreksi lewat reversal.
- Role Keuangan/Accounting sebagai pemilik posting pembayaran.

## Yang Perlu Ditambahkan

### Aplikasi/MVC

- Controller baru: `application/controllers/keuangan/C_PembayaranSupplier.php`.
- Model baru: `application/models/M_PembayaranSupplier.php`.
- View baru:
  - `application/views/content/keuangan/pembayaran_supplier/index.php`
  - `application/views/content/keuangan/pembayaran_supplier/detail.php`
  - `application/views/content/keuangan/pembayaran_supplier/form.php`
  - optional `print.php`
- Routes baru di `application/config/routes.php` dan, bila perlu konsisten module, `application/modules/kiupo/routes.php`.
- AJAX endpoint untuk:
  - list supplier outstanding;
  - list invoice/LPB outstanding per supplier;
  - preview jurnal;
  - post pembayaran;
  - void pembayaran.
- Menu/sidebar untuk role Finance/Accounting.

### Service dan Query

- Query outstanding supplier berbasis jurnal `POSTED`, supplier, dan `nomor_dokumen`.
- Resolver dokumen hutang dari LPB:
  - `tb_lpb`
  - `tb_lpb_detail`
  - `tbpo_po`
  - `tbpo_suplier`
  - `tb_retur_pembelian`
  - `tbkeu_pembayaran_alokasi`
- Guard agar pembayaran hanya bisa untuk dokumen yang:
  - supplier valid;
  - total hutang > 0;
  - belum lunas;
  - jurnal penerimaan/retur tidak exception;
  - periode fiskal terbuka.

## Risiko Yang Harus Diselesaikan Sebelum Development

1. Akun `21098` pada database lokal masih `tipe_kontrol = NONE` dan nama kosong, padahal digunakan sebagai Hutang Usaha pada jurnal LPB/retur. Ini membuat laporan hutang berbasis tipe kontrol tidak membaca saldo.
2. `Accounting_service::create_payment()` sudah validasi outstanding via `tipe_kontrol = HUTANG`. Jika jurnal LPB/retur tidak memakai akun bertipe kontrol HUTANG, pembayaran supplier akan gagal karena outstanding terbaca nol.
3. Beberapa LPB manual belum punya supplier dari PO (`id_supplier = 0`). Dokumen seperti ini harus dipisahkan sebagai "Belum siap bayar" atau diberi mapping supplier manual yang diaudit.
4. `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi` ada tetapi belum memiliki data lokal; UAT harus membuat transaksi riil/sandbox.
5. BG/giro supplier belum final di decision log. Untuk fase pertama disarankan hanya pembayaran kas/bank transfer yang langsung posted.

## Opsi Implementasi

### Opsi A - Direkomendasikan: AP Payment di Atas Engine Accounting Existing

Bangun module baru `Pembayaran Supplier` yang memakai `tbkeu_pembayaran`, `tbkeu_pembayaran_alokasi`, dan `Accounting_service::create_payment(SUPPLIER_PAYMENT)`.

Kelebihan:

- paling konsisten dengan desain accounting project;
- tidak mencampur pembayaran customer dan supplier;
- siap untuk multi-invoice allocation;
- jurnal bisa otomatis dan idempotent.

Konsekuensi:

- perlu bereskan akun hutang/control account terlebih dahulu;
- perlu query outstanding yang rapi.

### Opsi B - Clone Flow Pembayaran Customer

Meniru `C_pembayaran` dan `M_pembayaran`, lalu mengganti customer menjadi supplier.

Tidak direkomendasikan, karena flow lama memakai `tbkeu_pembayaran_faktur`, jurnal hardcode, dan tidak cocok untuk AP allocation.

### Opsi C - Buat Tabel Baru Khusus Supplier Payment

Buat tabel `tbkeu_pembayaran_supplier` dan detail sendiri.

Tidak direkomendasikan untuk fase pertama karena `tbkeu_pembayaran` sudah dibuat untuk AR/AP. Tabel baru akan menambah duplikasi dan memperlemah konsolidasi laporan.

## Roadmap Development

### Fase 1 - Foundation

- Validasi COA hutang dan mapping `SUPPLIER_PAYMENT`.
- Tentukan apakah akun LPB/retur tetap `21098` dengan perbaikan master akun, atau dipindahkan ke akun mapping `ACCOUNT_PAYABLE` yang sudah bertipe kontrol HUTANG.
- Buat query outstanding supplier.
- Buat dashboard read-only outstanding.

### Fase 2 - Posting Payment

- Buat form pembayaran supplier.
- Integrasikan ke `Accounting_service::create_payment()`.
- Simpan alokasi per invoice/LPB.
- Tampilkan jurnal hasil posting.

### Fase 3 - Audit dan Koreksi

- Buat void payment dengan reversal jurnal.
- Tambah history dan cetak bukti pembayaran.
- Tambah permission role dan log aktivitas.

### Fase 4 - Advanced

- Giro/BG payable clearing/bounce.
- Upload bukti pembayaran.
- Aging hutang supplier.
- Export Excel/PDF.
- Rekonsiliasi bank.

## Rekomendasi Go/No-Go

Rekomendasi: **Go untuk Opsi A**, tetapi development code sebaiknya dimulai setelah keputusan akun hutang disepakati.

Keputusan minimum yang harus dikunci:

1. Apakah akun hutang LPB/retur memakai `21098` yang diperbaiki, atau diganti ke akun mapping `ACCOUNT_PAYABLE`.
2. Apakah invoice supplier wajib sebelum pembayaran.
3. Apakah LPB manual tanpa supplier boleh dibayar, dan jika iya dari mana supplier authoritative diambil.
4. Fase pertama hanya kas/bank transfer atau langsung mendukung giro/BG.
