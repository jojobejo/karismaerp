# Modul Workflow Approval Kasbon Dinamis

Dokumentasi ini menjelaskan implementasi sistem persetujuan (approval) kasbon yang dinamis dan berjenjang berdasarkan departemen dan level jabatan pemohon pada Karisma ERP.

## 1. Alur Workflow Approval

Sistem akan memetakan user login (`tb_karyawan`) ke data atasan/penilai di (`tb_users`) untuk menentukan siapa yang berwenang memberikan persetujuan sebelum kasbon dapat dicairkan oleh kasir.

### A. Departemen IT (Level = 1)
* **Alur**: Pemohon -> Atasan -> Kasir
* **Status Transisi**:
  1. Pengajuan baru dibuat -> Status: `pending_atasan` (Menunggu Atasan).
  2. Atasan (nama di kolom `atasan` pada `tb_users`) melakukan approve -> Status: `approved` (Disetujui / Siap Cair).
  3. Kasir melakukan pencairan -> Status: `cair` (Sudah Dicairkan).

### B. Departemen Keuangan & Sales (Level = 1)
* **Alur**: Pemohon -> Penilai (Atasan) -> Penilai Tambahan (Penilai) -> Kasir
* **Status Transisi**:
  1. Pengajuan baru dibuat -> Status: `pending_penilai` (Menunggu Penilai 1).
  2. Penilai Pertama/Atasan (nama di kolom `atasan` pada `tb_users`) melakukan approve -> Status: `pending_penilai_tambahan` (Menunggu Penilai 2).
  3. Penilai Tambahan/Penilai (nama di kolom `penilai` pada `tb_users`) melakukan approve -> Status: `approved` (Disetujui / Siap Cair).
  4. Kasir melakukan pencairan -> Status: `cair` (Sudah Dicairkan).

### C. Departemen / Level Lain (Default)
* Jika pemohon memiliki atasan di `tb_users`:
  * Pengajuan baru dibuat -> Status: `pending_atasan` -> Atasan Approve -> Status: `approved` -> Kasir Cairkan -> Status: `cair`.
* Jika pemohon tidak memiliki atasan:
  * Pengajuan baru dibuat -> Status: `approved` (Otomatis disetujui / Siap Cair) -> Kasir Cairkan -> Status: `cair`.

---

## 2. Struktur Migrasi Database (`tb_kasbon`)

Tabel `tb_kasbon` telah diperluas dengan menambahkan kolom-kolom baru berikut untuk mendukung pencatatan status dan log approval secara permanen:

```sql
ALTER TABLE tb_kasbon ADD COLUMN workflow_type VARCHAR(50) NULL AFTER tanggal_pengajuan;
ALTER TABLE tb_kasbon ADD COLUMN atasan_nama VARCHAR(100) NULL AFTER workflow_type;
ALTER TABLE tb_kasbon ADD COLUMN penilai_nama VARCHAR(100) NULL AFTER atasan_nama;
ALTER TABLE tb_kasbon ADD COLUMN approved_atasan_by VARCHAR(100) NULL AFTER status;
ALTER TABLE tb_kasbon ADD COLUMN approved_atasan_at DATETIME NULL AFTER approved_atasan_by;
ALTER TABLE tb_kasbon ADD COLUMN approved_penilai_by VARCHAR(100) NULL AFTER approved_atasan_at;
ALTER TABLE tb_kasbon ADD COLUMN approved_penilai_at DATETIME NULL AFTER approved_penilai_by;
ALTER TABLE tb_kasbon ADD COLUMN approved_penilai_tambahan_by VARCHAR(100) NULL AFTER approved_penilai_at;
ALTER TABLE tb_kasbon ADD COLUMN approved_penilai_tambahan_at DATETIME NULL AFTER approved_penilai_tambahan_by;
ALTER TABLE tb_kasbon ADD COLUMN rejected_by VARCHAR(100) NULL AFTER approved_penilai_tambahan_at;
ALTER TABLE tb_kasbon ADD COLUMN rejected_at DATETIME NULL AFTER rejected_by;
ALTER TABLE tb_kasbon ADD COLUMN rejected_reason TEXT NULL AFTER rejected_at;
ALTER TABLE tb_kasbon ADD COLUMN cair_by VARCHAR(100) NULL AFTER rejected_reason;
ALTER TABLE tb_kasbon ADD COLUMN cair_at DATETIME NULL AFTER cair_by;
```

---

## 3. Integrasi Kasir Otomatis

Ketika Kasir menekan tombol **Cairkan** pada pengajuan kasbon yang berstatus `approved`:
1. Status kasbon akan berubah menjadi `cair`.
2. Sistem akan otomatis menyisipkan record transaksi baru di tabel `tbkeu_transaksi_kasir` sebagai **Kas Keluar** dengan kategori **"Kas Bon"** agar keuangan kasir tercatat secara riil dan mutasi harian sinkron tanpa perlu input manual.

---

## 4. Cara Penggunaan bagi Pengguna

1. **Pemohon**:
   * Masuk ke menu **Kas Bon**, klik **Buat Pengajuan Kas Bon**.
   * Isi Nominal dan Keterangan Keperluan, kemudian simpan.
   * Sistem akan otomatis menentukan alur persetujuan berdasarkan departemen Anda.
2. **Atasan / Penilai**:
   * Ketika login ke Karisma ERP, masuk ke menu **Kas Bon**.
   * Sistem hanya akan menampilkan pengajuan kasbon milik sendiri dan pengajuan bawahan yang memerlukan persetujuan Anda saat ini.
   * Klik tombol **Setuju** untuk menyetujui, atau **Tolak** (dan isi alasan penolakan) jika ingin menolak pengajuan tersebut.
3. **Kasir**:
   * Masuk ke menu **Kas Bon**. Kasir akan melihat semua kasbon yang berstatus **Disetujui (Siap Cair)**.
   * Klik tombol **Cairkan** untuk memberikan uang tunai kepada pemohon. Sistem akan otomatis membuat transaksi **Kas Keluar** di buku kasir.
