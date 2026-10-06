# PANDUAN LENGKAP PENGGUNAAN & TEKNIS: MODUL TUTUP BUKU & CUT-OFF (CLOSING PERIOD)

Tanggal Implementasi: 5 Oktober 2026  
Versi Modul: v1.0.0  
Acuan Dokumen:
- `docs/accounting/ANALISIS_GAP_DATABASE_MODUL_TUTUP_BUKU_20261005.md`
- `docs/accounting/BLUEPRINT_MIGRASI_ZAHIR_DAN_MODUL_TUTUP_BUKU.md`
- Database Migration: `db/migrations/20261005_modul_tutup_buku.sql`

---

## 1. Latar Belakang & Konsep Akuntansi

Modul Tutup Buku & Cut-Off (Closing Period) dibangun untuk melengkapi arsitektur General Ledger (GL) KarismaERP agar mandiri dan tidak lagi bergantung pada sistem eksternal (Zahir Accounting).

### Prinsip Utama Akuntansi Tutup Buku:
1. **Tidak Ada Penghapusan / Pengosongan Data**:
   - Tutup buku dalam KarismaERP **BUKAN** berarti menghapus transaksi lama. Seluruh mutasi stok, jurnal, faktur penjualan, dan LPB tetap tersimpan abadi di database MariaDB.
   - Tutup buku adalah proses **mengunci periode menjadi `CLOSED` (Immutable)** agar tidak ada perubahan atau transaksi tanggal mundur (*backdating*) yang merusak laporan keuangan yang sudah diaudit.
2. **Tutup Buku Bulanan (Monthly Closing)**:
   - Dijalankan pada akhir bulan kalender.
   - **TIDAK mereset akun pendapatan dan beban**. Laporan laba rugi bulanan dihitung berdasarkan filter rentang tanggal bulan tersebut.
   - **Seluruh akun neraca** (Aset, Kewajiban, dan Ekuitas) berlanjut (*carry-forward*) ke bulan berikutnya secara logis.
   - Menghasilkan snapshot saldo buku besar (GL), kartu persediaan per gudang/batch, daftar piutang faktur terbuka (AR), dan daftar hutang LPB terbuka (AP).
3. **Tutup Buku Tahunan (Year-End Closing)**:
   - Dijalankan pada tanggal 31 Desember setelah 12 periode bulanan berstatus `CLOSED`.
   - Menjalankan **Jurnal Penutup Otomatis (Closing Journal)**:
     - Akun nominal pendapatan didebit untuk menjadi Rp 0.
     - Akun nominal beban dan HPP dikredit untuk menjadi Rp 0.
     - Selisih laba/rugi bersih dipindahkan ke Akun **Laba Ditahan / Retained Earnings** (`32010`).
   - Seluruh akun riil (Neraca) membawa saldo akhir tahun sebelumnya ke 1 Januari tahun berikutnya.

---

## 2. Arsitektur Komponen yang Dibangun

```
                         ┌────────────────────────────────────────┐
                         │   Interface Pengguna (View)            │
                         │   `content/keuangan/tutup_buku/`       │
                         └───────────────────┬────────────────────┘
                                             │ AJAX / HTTP Requests
                                             ▼
                         ┌────────────────────────────────────────┐
                         │   Controller: `C_TutupBuku`            │
                         │   `application/controllers/keuangan/`  │
                         └───────┬────────────────────────┬───────┘
                                 │                        │
               Validasi & Eksekusi                        Query & Statistik
                                 ▼                        ▼
      ┌────────────────────────────────────┐   ┌────────────────────────┐
      │ Service:                           │   │ Model:                 │
      │ `Accounting_closing_service`       │   │ `M_TutupBuku`          │
      │ - 8 Aturan Validasi Pre-Closing    │   │ - Metrics Dashboard    │
      │ - Multi-Version Snapshot Generator │   │ - Period & History Joins│
      │ - Checksum Audit (SHA-256)         │   └────────────────────────┘
      │ - Auto Year-End Closing Journal    │
      │ - Workflow Reopen Berjenjang       │
      └──────────────────┬─────────────────┘
                         │ Transaksi Atomik Database
                         ▼
      ┌─────────────────────────────────────────────────────────────────┐
      │ Basis Data MariaDB (Tabel Terpadu `tbkeu_`):                    │
      │ - Header Closing: `tbkeu_closing_period`                        │
      │ - Snapshot GL: `tbkeu_closing_balance_account`                  │
      │ - Snapshot Persediaan: `tbkeu_closing_balance_inventory`        │
      │ - Snapshot AR: `tbkeu_closing_balance_ar`                       │
      │ - Snapshot AP: `tbkeu_closing_balance_ap`                       │
      │ - Sesi & Detail Validasi: `tbkeu_closing_validation_run & log`  │
      │ - Otorisasi Reopen: `tbkeu_reopen_request`                      │
      └─────────────────────────────────────────────────────────────────┘
```

---

## 3. Checklist Aturan Validasi Pre-Closing (8-9 Aturan Kritis)

Sebelum tutup buku dieksekusi, sistem menjalankan mesin validasi pre-closing otomatis. Tutup buku **DITOLAK** jika salah satu kondisi berkategori `BLOCKING` gagal (*Failed*):

| No | Kode Aturan | Judul Pemeriksaan | Kategori | Kondisi Lulus (Passed) |
|:--:|:---|:---|:---:|:---|
| 1 | `GL_BALANCE` | Keseimbangan GL | `BLOCKING` | Total Debit GL = Total Kredit GL (Selisih Rp 0) dan tidak ada baris jurnal yang tidak imbang. |
| 2 | `NO_DRAFT_JOURNAL` | Bebas Jurnal DRAFT | `BLOCKING` | Tidak ada jurnal berstatus `DRAFT` pada rentang tanggal periode fiskal. |
| 3 | `NO_OPEN_EXCEPTION` | Bebas Exception | `BLOCKING` | Tidak ada antrean `tbkeu_posting_exception` yang berstatus `OPEN`. |
| 4 | `UNAPPLIED_PAYMENTS`| Alokasi Pembayaran | `BLOCKING` | Seluruh voucher pembayaran customer/supplier telah dialokasikan penuh ke invoice/tagihan. |
| 5 | `AR_RECONCILIATION` | Rekonsiliasi Piutang | `BLOCKING` | Saldo akun kontrol Piutang di GL = Total sisa piutang faktur penjualan terbuka di subledger AR. |
| 6 | `AP_RECONCILIATION` | Rekonsiliasi Hutang | `BLOCKING` | Saldo akun kontrol Hutang di GL = Total sisa tagihan LPB supplier terbuka di subledger AP. |
| 7 | `INVENTORY_RECONCILIATION` | Rekonsiliasi Stok | `WARNING` | Membandingkan saldo akun persediaan di GL dengan total valuasi stok fisik ($Qty \times HPP$). |
| 8 | `NO_NEGATIVE_STOCK` | Pemeriksaan Stok Minus | `BLOCKING` | Tidak ditemukan kuantitas stok bernilai negatif pada seluruh barang dan gudang. |
| 9 | `YEAR_END_PREREQUISITES` | Prasyarat Tahunan | `BLOCKING` | *(Khusus TAHUNAN)* Seluruh periode bulanan dalam tahun fiskal berstatus `CLOSED` dan mapping akun Laba Ditahan tersedia. |

---

## 4. Prosedur Operasional Penggunaan

### A. Menjalankan Validasi Kelayakan Pre-Closing
1. Buka menu **Keuangan / Jurnal** lalu klik kartu **"Tutup Buku & Cut-Off (Closing Period)"** atau akses URL: `http://localhost/karismaerp/keuangan/tutup-buku`.
2. Pada tabel **Daftar Periode Fiskal**, temukan periode yang ingin ditutup (misalnya `2026-08`).
3. Klik tombol **"Cek Kelayakan"**.
4. Sistem akan memunculkan modal checklist hasil pemeriksaan integritas 8 aturan.
5. Jika ada anomali `BLOCKING`, selesaikan transaksi operasional yang bersangkutan (misal: posting jurnal draft, alokasikan sisa pembayaran, atau koreksi selisih).

### B. Mengeksekusi Tutup Buku (Closing Execution)
1. Setelah validasi berstatus **LOLOS (PASSED)**, klik tombol **"Lanjutkan ke Tutup Buku"** atau tombol **"Tutup Buku"** pada baris tabel periode.
2. Pilih Tipe Tutup Buku:
   - **Tutup Buku Bulanan (Monthly Closing)** untuk akhir bulan kalender.
   - **Tutup Buku Tahunan (Year-End Closing)** untuk akhir tahun fiskal (31 Desember).
3. Masukkan catatan atau referensi Berita Acara Tutup Buku.
4. Klik tombol **"Jalankan Tutup Buku Sekarang"**.
5. Sistem secara atomik akan:
   - Menghasilkan snapshot saldo buku besar akun (GL) lengkap posisi Debet/Kredit.
   - Menghasilkan snapshot rincian stok persediaan per lot dan gudang.
   - Menghasilkan snapshot sisa faktur piutang customer yang belum lunas.
   - Menghasilkan snapshot sisa tagihan hutang supplier yang belum lunas.
   - Menghitung **Checksum Digital SHA-256** sebagai segel audit (*tamper-evident audit seal*).
   - Membentuk **Jurnal Penutup Otomatis** (khusus tahunan).
   - Mengubah status periode menjadi `CLOSED`.

### C. Melihat Snapshot & Ekspor Data Audit
1. Masuk ke tab **"Riwayat Snapshot Closing (Audit Trail)"**.
2. Klik tombol **"Snapshot"** pada baris closing yang diinginkan.
3. Tinjau rincian melalui sub-tab: Buku Besar GL, Persediaan Stok, Piutang Usaha (AR), Hutang Usaha (AP), dan Jurnal Penutup.
4. Klik tombol **CSV GL / CSV Stok / CSV AR / CSV AP** di pojok kanan atas modal untuk mengunduh berkas rekonsiliasi yang siap diperiksa auditor eksternal.

### D. Mengajukan dan Menyetujui Pembukaan Kembali Periode (Reopen Workflow)
1. Jika terdapat temuan audit materiil pada periode yang sudah ditutup:
   - Klik tombol **"Ajukan Reopen"** pada periode yang terkunci.
   - Isi formulir alasan pembukaan kembali secara jelas dan formal.
2. Manager Keuangan / Direktur login dan masuk ke tab **"Permohonan Buka Periode (Reopen)"**.
3. Manager mereview permohonan dan mengklik **"Setujui"** atau **"Tolak"**.
4. Saat disetujui:
   - Status periode kembali menjadi `OPEN`.
   - Header closing sebelumnya ditandai `REOPENED` (data snapshot tidak dihapus, tetap ada untuk jejak audit).
   - Jika periode tersebut adalah Tutup Buku Tahunan, sistem secara otomatis menerbitkan **Jurnal Reversal Pembalik Jurnal Penutup** agar buku besar kembali normal untuk dikoreksi.
   - Setelah koreksi selesai, tutup buku dapat dieksekusi ulang menghasilkan **Versi Baru (v2, v3, dst.)**.

---

## 5. Ringkasan Endpoint & Hak Akses

| URL Endpoint | Metode | Keterangan | Wewenang Akses |
|:---|:---:|:---|:---|
| `/keuangan/tutup-buku` | `GET` | Halaman utama dashboard Tutup Buku & Cut-Off | Admin, Finance, Manajerial |
| `/keuangan/tutup-buku/check` | `POST` | Eksekusi mesin validasi pre-closing otomatis | Admin, Finance |
| `/keuangan/tutup-buku/execute` | `POST` | Eksekusi penguncian periode & pembuatan snapshot | Admin, Finance |
| `/keuangan/tutup-buku/detail/{id}` | `GET` | Rincian lengkap snapshot saldo GL, Stok, AR, AP | Admin, Finance, Auditor |
| `/keuangan/tutup-buku/reopen-request` | `POST` | Pengajuan buka periode oleh staf accounting | Admin, Finance |
| `/keuangan/tutup-buku/reopen-action` | `POST` | Persetujuan / Penolakan buka periode | Manager Keuangan, Direktur |
| `/keuangan/tutup-buku/export/{id}/{type}` | `GET` | Unduh berkas CSV audit trail snapshot | Admin, Finance, Auditor |
