# Analisis Gap Database Modul Cut-Off / Tutup Buku

Tanggal analisis: 5 Oktober 2026  
Acuan database: `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`  
Acuan rancangan: `docs/accounting/BLUEPRINT_MIGRASI_ZAHIR_DAN_MODUL_TUTUP_BUKU.md`

## 1. Kesimpulan Utama

Database KarismaERP sudah mempunyai fondasi General Ledger (GL) yang cukup baik untuk memulai modul tutup buku, tetapi **belum siap menjalankan tutup buku akuntansi secara lengkap**.

Yang tersedia sekarang pada dasarnya adalah **penguncian periode jurnal**. Proses `CLOSE` hanya mengubah status periode menjadi `CLOSED` setelah memeriksa jurnal draft, posting exception, pembayaran yang belum dialokasikan, dan keseimbangan jurnal. Belum ada jurnal penutup tahunan, snapshot saldo, rekonsiliasi GL terhadap AR/AP/persediaan, approval berjenjang, atau carry-forward yang dapat diaudit.

Pemahaman akuntansinya perlu diluruskan:

- Tutup buku **bulanan** tidak mereset akun pendapatan dan beban. Bulan dikunci, tetapi laporan laba rugi bulanan dibentuk dengan filter tanggal bulan tersebut.
- Tutup buku **tahunan** menutup akun nominal (pendapatan, HPP, dan beban) ke laba/rugi tahun berjalan lalu ke laba ditahan sesuai kebijakan perusahaan.
- Bukan hanya aset yang dilanjutkan. **Seluruh akun neraca**—aset, kewajiban, dan ekuitas—menjadi saldo awal periode/tahun berikutnya.
- Aset perusahaan tidak berubah menjadi “modal awal”. Aset tetap tetap berada di akun aset, akumulasi penyusutan tetap dilanjutkan, hutang tetap menjadi hutang, dan hasil laba bersih menambah atau mengurangi ekuitas/laba ditahan.

## 2. Komponen yang Sudah Tersedia

| Komponen | Kondisi | Catatan |
|---|---|---|
| COA | Ada | `tbkeu_akun` memiliki akun header/posting, saldo normal, dan klasifikasi. |
| Klasifikasi laporan | Ada | `tbkeu_klasifikasi_akun.jenis_laporan` membedakan `NERACA` dan `LABA_RUGI`; ini dapat menjadi dasar pemilihan akun yang ditutup tahunan. |
| Jurnal berimbang | Ada | Header/detail menggunakan `DECIMAL(19,4)`, status jurnal, idempotency key, reversal, dan lock version. |
| Periode fiskal | Ada | `tbkeu_periode_fiskal` sudah mempunyai rentang tanggal, `OPEN/CLOSED`, serta metadata close/reopen. |
| Log periode | Ada, minimal | `tbkeu_periode_fiskal_log` mencatat `OPEN/CLOSE/REOPEN`, alasan, dan approval. |
| Proteksi posting jurnal | Ada pada service accounting | Pembuatan dan posting jurnal mencari periode yang aktif dan `OPEN`. |
| Saldo awal GL | Ada | `tbkeu_saldo_awal_akun` dapat dimigrasikan menjadi jurnal saldo awal yang balance. |
| Dimensi jurnal | Ada | Detail jurnal menyediakan customer, supplier, barang, gudang, departemen, jatuh tempo, dan nomor dokumen. |
| Alokasi pembayaran | Ada | `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi` menjadi fondasi AR/AP. |
| Kuantitas stok/lot | Ada | `tberp_stock_ledger` dan `tberp_stock_batch` menyimpan kuantitas, gudang, lot, dan expired date. |

## 3. Gap Kritis yang Harus Diselesaikan

### 3.1 Mesin closing belum ada

Belum ada tabel header closing, versi closing, snapshot saldo, status proses, hasil validasi, jurnal penutup, dan hash/checksum hasil closing. Status `CLOSED` saat ini hanya flag pada periode.

Minimal diperlukan:

1. `tbkeu_closing_period` sebagai satu eksekusi closing yang berversi.
2. `tbkeu_closing_balance_account` sebagai snapshot GL.
3. Snapshot AR, AP, persediaan, kas/bank, dan bila sudah tersedia, aset tetap.
4. `tbkeu_closing_validation_run` dan detail hasil validasi.
5. Relasi jurnal penutup/reversal terhadap closing.
6. Status workflow seperti `DRAFT`, `VALIDATING`, `READY_TO_APPROVE`, `APPROVED`, `EXECUTING`, `COMPLETED`, `REOPENED`, dan `FAILED`.

### 3.2 Tidak ada jurnal penutup tahunan

Tidak ditemukan struktur yang mencatat jurnal penutupan akun laba-rugi. Untuk year-end closing dibutuhkan jurnal otomatis dan idempotent:

1. Menolkan seluruh akun dengan klasifikasi `LABA_RUGI`.
2. Memindahkan selisih bersih ke akun hasil usaha/laba rugi tahun berjalan.
3. Memindahkan hasil usaha ke akun laba ditahan sesuai konfigurasi dan approval.

Akun tujuan tidak boleh di-hardcode. Tambahkan konfigurasi per entitas/buku, misalnya role `CURRENT_YEAR_EARNINGS`, `RETAINED_EARNINGS`, dan bila digunakan `INCOME_SUMMARY` pada `tbkeu_mapping_akun` atau tabel konfigurasi closing khusus.

### 3.3 Ledger persediaan belum menyimpan nilai

`tberp_stock_ledger` hanya menyimpan `qty` dan `created_at`. Tidak ada:

- tanggal efektif transaksi yang terpisah dari waktu input;
- unit cost/HPP;
- nilai mutasi;
- nilai saldo setelah mutasi;
- metode costing dan layer biaya;
- referensi jurnal GL;
- status posting/void/reversal.

Akibatnya, sistem belum dapat menghitung nilai persediaan historis per tanggal cut-off secara andal. Kolom HPP pada master atau detail faktur tidak menggantikan inventory valuation ledger.

Prioritas: buat value ledger yang immutable, misalnya `tberp_inventory_value_ledger`, dengan `transaction_date`, `qty_in/out`, `unit_cost`, `value_in/out`, `running_qty`, `running_value`, `cost_method`, identitas barang/gudang/lot, sumber transaksi, dan `id_jurnal`.

### 3.4 Subledger AR dan AP belum menjadi sumber saldo yang tegas

Database memiliki faktur penjualan, LPB, pembayaran, dan alokasi, tetapi belum ada tabel subledger tunggal yang secara eksplisit menyimpan nilai awal, debit/kredit, pembayaran/retur, dan saldo berjalan per dokumen. `tbso_faktur_penjualan` bahkan tidak menyimpan total nilai header; nilai harus dihitung dari detail. LPB belum merupakan AP bill ledger yang lengkap.

Diperlukan salah satu desain berikut:

- document balance table per invoice/bill plus immutable transaction ledger; atau
- view terkontrol yang berasal dari dokumen posted dan alokasi, disertai snapshot closing.

Setiap baris harus mempunyai identitas customer/supplier yang konsisten, tanggal dokumen, jatuh tempo, mata uang bila relevan, nilai dokumen, retur/kredit nota, pembayaran, saldo terbuka, status, dan tautan jurnal.

### 3.5 Rekonsiliasi GL vs subledger belum ada

Closing harus diblokir jika tidak sama:

- akun kontrol piutang GL vs total open AR;
- akun kontrol hutang GL vs total open AP;
- akun persediaan GL vs inventory value ledger;
- kas/bank GL vs rekonsiliasi rekening/kas;
- aset tetap GL vs register aset dan akumulasi penyusutan;
- pajak GL vs subledger pajak.

Hasil pemeriksaan perlu menyimpan nilai GL, nilai subledger, selisih, jumlah anomali, sampel/detail referensi, severity, waktu, dan rule version—bukan hanya pesan teks.

### 3.6 Proteksi periode belum meliputi tabel operasional

Proteksi periode ditemukan pada jalur pembuatan/posting jurnal melalui `Accounting_service`. Tabel operasional penjualan, pembelian, stok, kas, retur, dan adjustment masih dapat berpotensi diubah melalui jalur lain setelah periode ditutup. Perubahan itu dapat membuat dokumen sumber berbeda dari jurnal yang sudah dikunci.

Semua operasi create/update/cancel/post/reversal pada modul sumber wajib memanggil satu `PeriodLockService`. Untuk pertahanan tambahan, gunakan stored procedure/trigger secara selektif pada titik posting kritis atau batasi hak database agar aplikasi tidak dapat melewati service.

### 3.7 Workflow approval dan reopen belum memadai

`tbkeu_periode_fiskal_log` belum mewakili permohonan dan approval bertingkat. Tombol reopen dapat langsung mengubah status ketika service dipanggil dengan alasan.

Diperlukan:

- request terpisah dari approval;
- pemohon tidak boleh menyetujui permohonannya sendiri;
- approval bulanan dan tahunan dapat berbeda;
- batas waktu reopen;
- periode setelah periode yang dibuka harus ikut ditandai terdampak/invalid;
- setelah koreksi, closing baru dibuat sebagai versi berikutnya;
- snapshot lama tidak dihapus dan diberi status superseded/reopened;
- jurnal penutup lama direversal, bukan dihapus.

### 3.8 Fixed asset dan penyusutan belum tersedia

Tidak ditemukan register aset tetap, kategori aset, lokasi/custodian, harga perolehan, tanggal kapitalisasi, nilai residu, masa manfaat, metode depresiasi, disposal, revaluation/impairment, maupun depreciation schedule.

Tanpa modul ini, nilai aset dan beban penyusutan akhir bulan tidak dapat menjadi bagian validasi closing otomatis.

### 3.9 Rekonsiliasi bank belum tersedia

Belum ada rekening bank master, statement import, statement line, matching, outstanding deposit/payment, dan bank reconciliation per tanggal. Saldo kas/bank GL saja belum cukup untuk menyatakan periode siap ditutup.

### 3.10 Pajak dan multi-currency belum lengkap

Field PPN ditemukan pada transaksi LPB, tetapi belum ada tax ledger terpadu untuk PPN masukan/keluaran dan withholding tax. Tidak ditemukan fondasi kurs transaksi, kurs pelunasan, realized/unrealized gain/loss, dan revaluasi saldo mata uang asing. Jika perusahaan hanya memakai IDR, multi-currency dapat ditunda tetapi keputusan tersebut harus terdokumentasi.

## 4. Koreksi terhadap DDL pada Blueprint

DDL blueprint belum aman untuk langsung dimigrasikan. Koreksi yang diperlukan:

1. `UNIQUE (id_periode)` pada `tbkeu_closing_period` menghalangi re-closing setelah reopen. Gunakan `UNIQUE (id_periode, closing_version)` dan simpan status versi.
2. Jangan menggunakan `ON DELETE CASCADE` pada snapshot closing. Bukti closing/audit tidak boleh hilang saat header terhapus; idealnya closing tidak dapat dihapus sama sekali.
3. Tambahkan snapshot AR dan AP yang sudah digambar pada ERD tetapi belum mempunyai DDL dalam blueprint.
4. Snapshot inventory harus memuat lot/batch, expired date, cost method, dan unique key dimensi. Saat ini dua baris barang-gudang yang sama dapat duplikat.
5. Tipe identifier harus mengikuti master aktual. Ledger stok saat ini memakai `kd_barang VARCHAR(50)` dan `gudang_id VARCHAR(30)`, sedangkan rancangan memakai `BIGINT`; diperlukan canonical item/warehouse ID atau tabel mapping sebelum membuat FK.
6. `tbkeu_closing_validation_log` perlu `id_closing`/`id_validation_run`, rule code/version, expected amount, actual amount, difference amount, dan FK.
7. Header closing perlu `id_jurnal_penutup`, checksum, cutoff timestamp, accounting basis, source data watermark, approval status, dan failure information.
8. `total_debit_gl` dan `total_kredit_gl` saja tidak membuktikan closing; snapshot detail dan reconciliation evidence wajib disimpan.
9. `is_closing_locked` menduplikasi makna `status`. Lebih aman menggunakan state machine yang tegas daripada dua flag yang dapat bertentangan.
10. `tipe_periode` pada periode bulanan tidak tepat untuk merepresentasikan year-end sebagai periode tambahan yang overlap. Lebih aman periode tetap bulanan dan sebuah closing ditandai `MONTH_END` atau `YEAR_END`; Desember dapat mempunyai kedua tahap tanpa membuat dua rentang periode tumpang tindih.
11. Tabel saldo awal AR/AP/persediaan perlu `migration_batch_id`, source row key, checksum, validation status, applied journal/reference, currency, dan constraints nilai agar import ulang idempotent serta dapat diaudit.
12. `is_applied_to_gl` per baris tidak cukup karena GL lazimnya diposting per batch; simpan hubungan batch-jurnal dan status batch secara atomik.

## 5. Rancangan Perilaku Tutup Buku yang Disarankan

### 5.1 Tutup buku bulanan

1. Tetapkan cut-off timestamp dan hentikan posting ke periode.
2. Jalankan pre-closing checks.
3. Posting adjustment: accrual, penyusutan, pajak, selisih kurs, stock adjustment, dan koreksi yang disetujui.
4. Rekonsiliasi GL dengan seluruh subledger.
5. Pastikan tidak ada transaksi source yang belum diposting atau jurnal draft/error.
6. Simpan snapshot dan bukti rekonsiliasi.
7. Approval closing.
8. Ubah periode menjadi `CLOSED` secara atomik.

Tidak perlu membuat jurnal reset pendapatan/beban setiap bulan. Laporan laba rugi bulanan menggunakan `date_from` dan `date_to`; laporan year-to-date memakai awal tahun sampai akhir bulan.

### 5.2 Tutup buku tahunan

Setelah closing Desember selesai:

1. pastikan 12 periode bulanan sudah closed;
2. hitung laba bersih dari akun berklasifikasi `LABA_RUGI`;
3. buat jurnal penutup otomatis dan idempotent;
4. verifikasi saldo seluruh akun laba-rugi menjadi nol setelah jurnal penutup;
5. pindahkan hasil usaha ke akun ekuitas yang dikonfigurasi;
6. snapshot final dan approval direktur;
7. buka periode Januari tahun berikutnya dengan seluruh saldo akun neraca tetap terbawa secara logis dari GL.

Sistem saat ini menghitung neraca secara kumulatif sampai `date_to`, sehingga secara teknis tidak wajib membuat jurnal saldo awal neraca setiap bulan. Snapshot dibutuhkan untuk audit dan performa, bukan untuk menggandakan saldo GL. Jika dibuat jurnal carry-forward bulanan tanpa batasan query yang tepat, saldo berisiko terhitung dua kali.

## 6. Prioritas Implementasi

### P0 — wajib sebelum closing pertama

- Finalisasi canonical master ID barang, gudang, customer, dan supplier.
- Tambahkan state machine closing dan versioned snapshot.
- Buat pre-closing validation engine dan rekonsiliasi GL/subledger.
- Buat inventory value ledger historis.
- Bentuk AR/AP document ledger yang dapat direkonsiliasi.
- Terapkan period lock pada seluruh modul sumber.
- Konfigurasikan akun laba ditahan/hasil usaha dan jurnal penutup tahunan.
- Buat workflow approval/reopen yang tidak menghapus histori.

### P1 — wajib untuk laporan keuangan lengkap

- Fixed asset register dan depresiasi.
- Bank reconciliation.
- Tax ledger.
- Accrual/prepayment schedule.
- Trial balance freeze, closing checklist, dan evidence attachment.

### P2 — sesuai kebutuhan bisnis

- Multi-currency dan revaluation.
- Consolidation/multi-entity bila ada lebih dari satu entitas legal.
- Performance summary/materialized balance setelah akurasi ledger teruji.

## 7. Acceptance Criteria Minimum

Modul belum boleh dinyatakan siap produksi sebelum seluruh kondisi berikut lulus:

- Tidak ada jurnal DRAFT/invalid dan source transaction unposted sampai tanggal cut-off.
- Debit GL sama dengan kredit GL.
- AR GL sama dengan open AR per invoice dengan selisih Rp0.
- AP GL sama dengan open AP per bill dengan selisih Rp0.
- Persediaan GL sama dengan inventory value ledger; toleransi hanya mengikuti kebijakan pembulatan tertulis.
- Saldo kas/bank direkonsiliasi dan outstanding item terdokumentasi.
- Penyusutan serta pajak periode telah diposting.
- Semua jurnal dan dokumen bertanggal periode closed ditolak oleh seluruh entry point.
- Closing dapat dijalankan ulang secara idempotent tanpa jurnal ganda.
- Reopen menghasilkan approval dan audit trail, serta re-closing membuat versi baru.
- Year-end closing menolkan akun laba-rugi dan memindahkan laba/rugi bersih ke ekuitas tanpa mengubah saldo aset, kewajiban, atau ekuitas lainnya secara keliru.

## 8. Batasan Analisis

Analisis ini dilakukan terhadap struktur dump acuan dan implementasi service accounting di codebase. Dump acuan tidak menyediakan bukti data produksi aktual yang cukup untuk menilai kualitas saldo, jumlah akun yang belum terklasifikasi, selisih GL-subledger saat ini, atau kelengkapan mapping per transaksi. Audit data berikutnya harus dijalankan terhadap salinan database produksi/read-only dengan query profiling dan rekonsiliasi nominal.
