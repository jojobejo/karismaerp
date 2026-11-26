# Dokumentasi Perbaikan Bug: Duplikasi Jurnal Pelunasan & Akun A pada Pecah Faktur (Kode H)

## 1. Ringkasan Permasalahan (Root Cause)
Berdasarkan pengecekan pada halaman `sales_order/pecah_faktur?tab=hasil_pecah` ketika melakukan **Import Pelunasan Faktur Z** dan **Export Pembayaran & Jurnal**, ditemukan dua akar permasalahan:

1. **Kasus Faktur `H26000033` (dan `H26000466`) — Muncul Akun `A BCA (Annelia)` & Jurnal Terpecah Dua**:
   - Sebelum proses Import Pelunasan dilakukan, Faktur Z Induk (`Z26002941` untuk `H26000033`, dan `Z26002959` untuk `H26000466`) pernah diinput pembayarannya pada modul Pembayaran Faktur Keuangan (`tbkeu_pembayaran_faktur`) menggunakan rekening **`A BCA (Annelia)`** (`120-32`).
   - Fungsi lama `_post_jurnal_pecahan_faktur_z()` meneruskan akun `A BCA (Annelia)` langsung ke dalam **Jurnal Acak (Q) Pecahan**, padahal Jurnal Acak (Q) wajib menggunakan akun berawalan **Q**.
   - Ketika dilakukan **Import Pelunasan Faktur Z**, fungsi `import_pelunasan_faktur_z_pecah()` hanya menghitung sisa tagihan setelah dikurangi pembayaran lama tersebut tanpa mereset jurnal pembayaran pecah sebelumnya, sehingga muncul dua set jurnal yang terpisah pada satu faktur.

2. **Kasus Faktur `H26000375` — Muncul 2 Jurnal Pelunasan (Meriyanto & Iguh Firmansyah)**:
   - Faktur `H26000375` yang aktif di sistem (`tbso_faktur_z_pecah`, `id_pecah = 375`) adalah milik **Meriyanto (Sahala/Syaiful)** dari Faktur Z `Z26003159` yang dilunasi via `Q Kas` sebesar `Rp 2.820.000`.
   - Namun di tabel `tbkeu_jurnal`, terdapat jurnal yatim (*orphan journal*) lama dengan `idempotency_key = PAYMENT_FH_IMPORT_Q_20` & `PAYMENT_FH_IMPORT_A_20` dari modul **Faktur H Import** (`tbso_faktur_h_import`) yang datanya sudah pernah dihapus sebelumnya, tetapi kebetulan memiliki nomor faktur yang sama (`H26000375` atas nama **Iguh Firmansyah**).
   - Pada saat **Export Pembayaran & Jurnal** (`export_faktur_z_pecah_lunas`), kueri sebelumnya menarik semua jurnal dengan `WHERE source_no IN ($hNos)` dan `source_type IN ('PEMBAYARAN_FAKTUR', 'PEMBAYARAN_FAKTUR_PECAH')` hanya berdasarkan kecocokan teks nomor faktur (`source_no`), sehingga jurnal yatim milik Firmansyah ikut terekspor.

---

## 2. Solusi & Perbaikan Kode

### A. Controller Sales Order (`application/controllers/sales/C_SalesOrder.php`)
1. **Isolasi Query Jurnal pada `export_faktur_z_pecah_lunas()`**:
   - Penarikan jurnal pelunasan Faktur Pecahan H kini diisolasi ketat menggunakan `source_type = 'PEMBAYARAN_FAKTUR_PECAH'` dan `source_id IN ($pecahIds)` (merujuk langsung ke `id_pecah` masing-masing faktur).
   - Fallback ke `PEMBAYARAN_FAKTUR` hanya dijalankan apabila faktur H belum memiliki jurnal pelunasan pecah langsung, serta wajib tervalidasi `INNER JOIN tbkeu_pembayaran_faktur` dengan `id_faktur = parent_id_faktur` dan mengecualikan `idempotency_key LIKE 'PAYMENT_FH_IMPORT_%'`.
   - Menghapus kolom duplikat `No. Bukti` kedua pada Sheet 1 agar seragam dengan format Report Jurnal Keuangan.
2. **Isolasi Query Jurnal pada `export_faktur_h_lunas()` (Modul Faktur H Import)**:
   - Membatasi pengambilan jurnal ekstra hanya yang memiliki `source_id IN ($importIds)` dan `idempotency_key LIKE 'PAYMENT_FH_IMPORT_%'`.
3. **Reset Riwayat Bayar Lama Saat `import_pelunasan_faktur_z_pecah()`**:
   - Sebelum memproses baris pembayaran Faktur Z dari file Excel Import Pelunasan, sistem otomatis membersihkan jurnal pelunasan lama pada child Faktur H milik Faktur Z tersebut agar file Excel Import Pelunasan menjadi satu-satunya sumber kebenaran (*single source of truth*) dan tidak terjadi tumpang tindih dengan pembayaran uji coba sebelumnya.
4. **Pembersihan Jurnal Saat Unpost / Reset Faktur Pecahan H**:
   - Menambahkan pemanggilan `_deleteJurnalPelunasanFakturHPecah()` pada `hapus_faktur_pecah()`, `unpost_faktur_pecah_batch()`, dan `reset_pecah_faktur_induk()` agar tidak pernah menyisakan jurnal pelunasan yatim ketika faktur pecahan dibatalkan atau di-reset.

### B. Model Jurnal (`application/models/M_Journal.php`)
1. **Proteksi Akun Q pada Jurnal Acak (Q)**:
   - Pada `_post_jurnal_pelunasan_faktur_h_core()` dan `_post_jurnal_pecahan_faktur_z()`, ditambahkan validasi agar Jurnal Acak (Q) tidak pernah menggunakan akun berawalan **`A `** (seperti `A BCA (Annelia)`). Jika input metode pembayaran berawalan `A `, sistem otomatis memetakannya ke akun **`Q`** yang sesuai (`Q BCA`, `Q BRI`, `Q Mandiri`, `Q Kas Ayat Silang`, atau `Q Kas`).
2. **Validasi Sinkronisasi Induk (`sync_status_bayar_faktur_h_by_induk()`)**:
   - Memperketat pengambilan jurnal pembayaran induk dengan `INNER JOIN tbkeu_pembayaran_faktur` (`pf.id_faktur = parent_id_faktur` dan status `POSTED`) serta mengecualikan jurnal dari modul `PAYMENT_FH_IMPORT_`.

---

## 3. Pembersihan & Penyelarasan Data Database (`kiucoid_karismaerp_admkeu`)
1. Menghapus jurnal yatim (*orphan*) sisa `PAYMENT_FH_IMPORT_*` yang tidak lagi memiliki data induk di `tbso_faktur_h_import` (`id_jurnal`: `58, 59, 60, 61, 62, 63, 64, 65` — termasuk jurnal atas nama **Iguh Firmansyah** pada `H26000375`).
2. Meng-unpost (`DRAFT`) pembayaran uji coba lama `A BCA (Annelia)` beserta jurnal pecahannya (`id_jurnal`: `5` dan `43`), lalu menyelaraskan jurnal hasil Import Pelunasan menjadi 100% utuh sesuai metode di Excel:
   - **`H26000033` (Lasenan - Z26002941)**:
     - Jurnal Acak (Q) `KM-26112600121`: Debit `Q Kas Ayat Silang` `Rp 3.750.000`, Kredit `Piutang Usaha` `Rp 3.000.000`, Kredit `Q Kas` `Rp 750.000`.
     - Jurnal (A) Kas Masuk `KM-26112600122`: Debit `A Kas` `Rp 750.000`, Kredit `A PND atas Pendapatan Pot Penj KIU (Tax)` `Rp 750.000`.
   - **`H26000375` (Meriyanto - Z26003159)**:
     - Jurnal Acak (Q) `KM-26112600600`: Debit `Q Kas` `Rp 2.820.000`, Kredit `Piutang Usaha` `Rp 2.820.000`.
     - Jurnal (A) Kas Masuk `KM-26112600601`: Debit `A Kas` `Rp 705.000`, Kredit `A PND atas Pendapatan Pot Penj KIU (Tax)` `Rp 705.000`.
   - **`H26000466` (Zaeniah - Z26002959)**:
     - Jurnal Acak (Q) `BR-26112600041`: Debit `Q BRI 300300` `Rp 31.234.000`, Kredit `Piutang Usaha` `Rp 24.987.200`, Kredit `Q Kas` `Rp 6.246.800`.
     - Jurnal (A) Kas Masuk `KM-26112600151`: Debit `A Kas` `Rp 6.246.800`, Kredit `A PND atas Pendapatan Pot Penj KIU (Tax)` `Rp 6.246.800`.

---

## 4. Perbaikan Tambahan: Skema Tanpa Potongan (0%) & Prefix Jenis Jurnal (`BC`/`BR` -> `CR`)

1. **Penghormatan Skema Tanpa Potongan (`0%`) pada Pelunasan & Sinkronisasi**:
   - Pada `import_pelunasan_faktur_z_pecah()` dan `import_pelunasan_faktur_h_import()`, apabila kolom `skema_potongan` di Excel dikosongkan/tidak ada, sistem kini mewarisi nilai `skema_potongan` asli masing-masing Faktur H dari database (`0`, `20`, atau `40`) — tidak lagi memaksa ke default `20%`.
   - Pada `M_Journal::_post_jurnal_pecahan_faktur_z()` dan `M_Journal::sync_status_bayar_faktur_h_by_induk()`, pengecekan `!empty($pecahans[0]['skema_potongan'])` (yang di PHP menganggap angka `0` sebagai kosong lalu kembali ke `20%`) diperbaiki menjadi pengecekan eksplisit `!== null && !== ''`.
   - Pada modal pelunasan satuan & batch di `pecah_faktur_list.php`, pilihan skema potongan kini otomatis menyesuaikan `skema_potongan` milik Faktur Pecahan H terkait.
   - Pada `simpan_split_faktur()` (pemecahan 1 faktur), ditambahkan dukungan parameter `pakai_potongan_20` (`0`, `20`, `40`).

2. **Perbaikan Kode Ref. `REV` pada Jurnal Bank `BC` & `BR`**:
   - Sebelumnya kode jenis jurnal `BC` (BCA) dan `BR` (BRI) belum terdaftar di `tbkeu_jenis_jurnal`, sehingga fallback ke `id_jenis_jurnal = 3` (`REV` / Jurnal Reversal) pada saat export.
   - Master jenis jurnal `BC` dan `BR` kini otomatis didaftarkan, seluruh jurnal `BC-*` dan `BR-*` di database telah diperbarui dari `REV` ke `BC`/`BR`, dan pemetaan kolom `Ref.` di `C_SalesOrder` serta `C_ReportJurnal` telah memetakan `BC`/`BR` menjadi `CR` (Cash Receipt).
