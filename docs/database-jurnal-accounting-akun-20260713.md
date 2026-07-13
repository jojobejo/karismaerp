# Database Modul Jurnal - Akun Jurnal

Tanggal: 2026-07-13

## Status Perubahan Database

Perubahan database disiapkan sebagai SQL migration manual pada:

`docs/database/accounting_jurnal_accounts_20260713.sql`

File tersebut berisi:

- migration up;
- seed klasifikasi akun;
- seed Chart of Accounts dasar;
- seed master saldo normal;
- seed master tipe kontrol;
- SQL audit read-only;
- SQL verification;
- migration down sebagai komentar rollback.

Upgrade tambahan untuk instalasi yang sudah telanjur menjalankan migration awal tersedia pada:

`docs/database/accounting_jurnal_master_options_20260713.sql`

File upgrade ini membuat tabel master pendukung dan mengubah kolom `saldo_normal` serta `tipe_kontrol` dari enum menjadi `VARCHAR`, supaya data dapat dikelola melalui CRUD module.

Update tampilan daftar akun dan form jurnal pada 2026-07-13 tidak menambah migration database baru. Perubahan tersebut memakai tabel yang sudah disiapkan:

- filter klasifikasi membaca `tbkeu_klasifikasi_akun`;
- daftar akun membaca `tbkeu_akun`;
- panel jurnal kanan membaca `tbkeu_jurnal` dan `tbkeu_jurnal_detail` hanya jika kedua tabel tersebut sudah tersedia pada tahap General Ledger berikutnya.

Jika `tbkeu_jurnal` dan `tbkeu_jurnal_detail` belum ada, aplikasi menampilkan pesan kosong yang aman dan tidak melakukan DDL otomatis.

Migration executable untuk menghilangkan pesan `Tabel jurnal belum tersedia` sudah ditambahkan pada:

`docs/database/accounting_general_ledger_journal_20260713.sql`

Jalankan file tersebut setelah migration akun selesai. File ini membuat:

- `tbkeu_periode_fiskal`;
- `tbkeu_jenis_jurnal`;
- `tbkeu_jurnal`;
- `tbkeu_jurnal_detail`.

File ini belum mengaktifkan sample transaksi jurnal secara default. Bagian sample tersedia sebagai komentar di dalam SQL dan dapat dibuka jika hanya ingin mengetes tampilan panel `Form Jurnal`.

## Tabel Baru

### `tbkeu_klasifikasi_akun`

Fungsi: master klasifikasi laporan dan saldo normal akun.

Kolom utama:

- `id_klasifikasi`
- `kode_klasifikasi`
- `nama_klasifikasi`
- `alias_klasifikasi`
- `jenis_laporan`
- `saldo_normal`
- `urutan`
- `is_active`
- `created_at`
- `updated_at`

Seed awal:

1. Harta
2. Kewajiban
3. Modal
4. Pendapatan
5. Beban Atas Pendapatan
6. Beban Operasional
7. Beban Non Operasional
8. Pendapatan Lain
9. Beban Lain

### `tbkeu_akun`

Fungsi: Chart of Accounts dengan hierarki `HEADER` dan `POSTING`.

Kolom utama:

- `id_akun`
- `kode_akun`
- `nama_akun`
- `id_klasifikasi`
- `parent_id`
- `level_akun`
- `saldo_normal`
- `tipe_akun`
- `tipe_kontrol`
- `allow_manual_journal`
- `is_active`
- `created_by`
- `created_at`
- `updated_by`
- `updated_at`

Constraint dan index:

- unique `kode_akun`;
- foreign key `id_klasifikasi` ke `tbkeu_klasifikasi_akun`;
- self foreign key `parent_id` ke `tbkeu_akun`;
- index klasifikasi, parent, tipe akun, dan status aktif.

Catatan MariaDB 10.4: aturan `parent_id` tidak boleh sama dengan `id_akun` tidak dibuat sebagai `CHECK` karena `id_akun` adalah `AUTO_INCREMENT` dan MariaDB menolak ekspresi tersebut pada check clause. Validasi ini diterapkan di controller dan disediakan sebagai SQL audit read-only.

### `tbkeu_saldo_normal`

Fungsi: master saldo normal yang menjadi sumber pilihan field `Saldo Normal` pada akun dan klasifikasi.

Kolom utama:

- `kode_saldo`
- `nama_saldo`
- `keterangan`
- `urutan`
- `is_active`
- `created_at`
- `updated_at`

Seed awal:

- `DEBIT`
- `KREDIT`

### `tbkeu_tipe_kontrol`

Fungsi: master tipe kontrol akun yang menjadi sumber pilihan field `Tipe Kontrol`.

Kolom utama:

- `kode_tipe_kontrol`
- `nama_tipe_kontrol`
- `keterangan`
- `urutan`
- `is_active`
- `created_at`
- `updated_at`

Seed awal:

- `NONE`
- `KAS`
- `BANK`
- `PIUTANG`
- `HUTANG`
- `PERSEDIAAN`
- `GRNI`
- `PAJAK_MASUKAN`
- `PAJAK_KELUARAN`
- `UANG_MUKA_CUSTOMER`
- `UANG_MUKA_SUPPLIER`
- `LABA_DITAHAN`

## Seed Akun Dasar

Seed awal membuat akun HEADER:

- `1000` Harta
- `2000` Kewajiban
- `3000` Modal
- `4000` Pendapatan
- `5000` Beban Atas Pendapatan
- `6000` Beban Operasional
- `7000` Beban Non Operasional
- `8000` Pendapatan Lain
- `9000` Beban Lain

Seed awal juga membuat akun POSTING dasar:

- Kas
- Bank
- Piutang Usaha
- Persediaan Barang
- Hutang Usaha
- GRNI/Barang Diterima Belum Ditagih
- Penjualan
- Harga Pokok Penjualan

Kode akun seed berada di file SQL dan dapat disesuaikan sebelum deployment produksi. Kode akun tidak di-hardcode pada controller, model, view, helper, library, atau JavaScript.

## SQL Audit

File SQL menyediakan audit read-only untuk:

- duplicate `kode_akun`;
- akun tanpa klasifikasi valid;
- orphan parent account;
- akun menjadi parent untuk dirinya sendiri;
- akun HEADER yang masih `allow_manual_journal = 1`.

## SQL Verification

File SQL menyediakan query verifikasi untuk:

- jumlah row `tbkeu_klasifikasi_akun`;
- jumlah row `tbkeu_akun`;
- total akun per klasifikasi.

## Rollback

Rollback tersedia sebagai komentar pada bagian `MIGRATION DOWN`:

```sql
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `tbkeu_akun`;
DROP TABLE IF EXISTS `tbkeu_klasifikasi_akun`;
SET FOREIGN_KEY_CHECKS = 1;
```

Jalankan hanya jika modul awal jurnal perlu dibatalkan dan belum ada dependency accounting lain.

## Bukti Tabel Purchase Order Dikecualikan

`tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, dan `tbpo_akun_tr` merupakan domain aplikasi Purchase Order tersendiri.

Modul accounting KARISMA ERP tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency terhadap tabel tersebut.

Migration tahap ini hanya membuat dan mengisi tabel baru berprefix `tbkeu_`. Tidak ada DDL, DML, join, select, insert, update, delete, foreign key, atau dependency terhadap empat tabel Purchase Order yang dikecualikan.
