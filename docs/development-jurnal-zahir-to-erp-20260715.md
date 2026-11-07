# Development Module Jurnal - Analisa Zahir to ERP

Tanggal: 2026-07-15

## Tujuan

Dokumen ini mencatat hasil scanning file Excel Zahir, database lokal KARISMA ERP, dump SQL, dan kode aplikasi untuk menentukan step development lanjutan module jurnal.

Sumber scan:

- Excel: `C:/Users/bram/Documents/data_akun_zahir_to_erp.xlsx`
- Dump SQL: `C:/Users/bram/Downloads/kiucoid_karismaerp_local (10).sql`
- Database lokal aktif: `kiucoid_karismaerp_local`
- Repo: `C:/xampp/htdocs/karismaerp`

## Status Aplikasi Saat Ini

Module akun dan accounting sudah memiliki fondasi berikut:

- Route COA: `jurnal` dan `keuangan/jurnal`.
- Route runtime accounting: `accounting`, `keuangan/accounting`, dan route UAT `accounting-test`.
- Controller COA: `application/controllers/keuangan/C_Keuangan.php`.
- Controller runtime accounting: `application/controllers/keuangan/C_Accounting.php`.
- Model COA: `application/models/M_Keuangan.php`.
- Service jurnal: `application/libraries/Accounting_service.php`.
- Adapter sumber transaksi: `application/libraries/Accounting_source_service.php`.
- View COA: `application/views/content/keuangan/jurnal.php`.
- View runtime accounting: `application/views/content/keuangan/accounting_runtime_test.php`.

Fitur yang sudah tersedia:

- CRUD Chart of Accounts.
- CRUD master pendukung klasifikasi, saldo normal, tipe kontrol.
- Input jurnal manual draft.
- Posting jurnal.
- Reversal jurnal.
- Periode fiskal.
- Opening balance.
- Payment customer/supplier dasar.
- Posting exception.
- Mapping akun untuk auto-posting.
- Report dasar: buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, kas/bank.
- Adapter auto-posting untuk sales invoice dan goods receipt.

## Hasil Scan Excel Zahir

Workbook memiliki 6 sheet:

| Sheet | Baris data aktual | Fungsi |
| --- | ---: | --- |
| `KELOMPOK` | 23 | Kelompok/jenis transaksi jurnal Zahir seperti `GJ`, `CD`, `CR`, `SJ`, `PJ`, `IJ`. |
| `KLASIFIKASI` | 9 | Klasifikasi utama laporan. |
| `SUB CLASS` | 36 | Sub klasifikasi akun di bawah klasifikasi. |
| `KIRAAN` | 501 | Master akun/kiraan. |
| `AKUN LABA RUGI` | 267 | Rule laporan laba rugi per kode rekening. |
| `AKUN NERACA` | 230 | Rule laporan neraca per kode rekening. |

Distribusi rule laporan:

| Laporan | Debit bertambah | Kredit bertambah | Total rule |
| --- | ---: | ---: | ---: |
| Laba rugi | 235 | 32 | 267 |
| Neraca | 149 | 81 | 230 |

Distribusi akun rule per klasifikasi:

| Klasifikasi | Rule |
| --- | ---: |
| Harta | 160 |
| Kewajiban | 54 |
| Modal | 16 |
| Pendapatan | 21 |
| Beban Atas Pendapatan | 42 |
| Beban Operasional | 167 |
| Beban Non Operasional | 4 |
| Pendapatan Lain | 14 |
| Beban Lain | 19 |

Catatan penting:

- `KIRAAN` memakai kode master tanpa tanda hubung, contoh `41010`.
- Sheet laporan memakai kode rekening dengan tanda hubung, contoh `410-10`.
- Normalisasi join harus menghapus tanda hubung dari kode laporan agar bisa dicocokkan ke `KIRAAN`.
- Total akun pada rule laporan adalah 497, sedangkan master `KIRAAN` berisi 501 akun. Ada minimal 4 akun master yang perlu diaudit karena belum muncul di rule laba rugi/neraca.

## Hasil Scan Database Lokal

Database lokal aktif sudah memiliki tabel runtime accounting dan datanya masih seed/minimal:

| Tabel | Row |
| --- | ---: |
| `tbkeu_klasifikasi_akun` | 9 |
| `tbkeu_akun` | 22 |
| `tbkeu_jurnal` | 2 |
| `tbkeu_jurnal_detail` | 10 |
| `tbkeu_mapping_akun` | 30 |
| `tbkeu_saldo_awal_akun` | 0 |

Audit integrity lokal:

| Audit | Issue |
| --- | ---: |
| Duplicate `kode_akun` | 0 |
| Orphan parent account | 0 |
| HEADER masih boleh jurnal manual | 0 |
| Jurnal tidak balance | 0 |
| Total header beda dengan detail | 0 |
| Posted tanpa detail | 0 |

Status seed akun lokal:

- Akun neraca: 11 akun.
- Akun laba rugi: 11 akun.
- Jurnal lokal: 2 jurnal `POSTED`, total debit dan kredit sama-sama `32.001.600,0000`.

## Gap Utama

1. COA ERP masih seed minimal 22 akun, sedangkan Zahir membawa 501 master akun.
2. ERP belum punya master `SUB CLASS` tersendiri untuk menyimpan 36 sub klasifikasi Zahir.
3. Report laba rugi dan neraca saat ini menghitung berdasarkan `tbkeu_klasifikasi_akun.jenis_laporan` dan saldo normal akun, belum memakai rule eksplisit `BERTAMBAH`/`BERKURANG` dari Excel Zahir.
4. Kode akun ERP seed seperti `4100`, sedangkan Zahir memakai leaf code seperti `41010` dan display report `410-10`.
5. Belum ada fitur import-preview-review untuk Excel Zahir.
6. Belum ada tabel staging untuk mencatat batch import, error validasi, duplicate, atau akun yang tidak punya rule laporan.
7. Auto-posting mapping saat ini menunjuk akun seed. Setelah COA Zahir masuk, mapping harus di-review agar menunjuk akun leaf yang benar.
8. UI runtime accounting masih berupa halaman kerja produksi/UAT gabungan. Untuk pemakaian accounting harian, perlu pemisahan layar yang lebih operasional: import akun, jurnal umum, posting, mapping, laporan.

## Prinsip Development Lanjutan

1. Jangan overwrite akun yang sudah dipakai jurnal tanpa audit dan persetujuan cutover.
2. Gunakan staging import untuk seluruh data Excel Zahir.
3. Gunakan `KIRAAN.KODE` sebagai kode akun canonical karena itu master akun Zahir.
4. Simpan kode laporan bertanda hubung dari sheet laba rugi/neraca sebagai kode sumber/report, lalu normalisasi untuk join.
5. Report laba rugi dan neraca harus membaca jurnal `POSTED`.
6. Formula laporan harus mengikuti rule Excel:
   - Jika `BERTAMBAH = Debit`, nilai laporan = total debit - total kredit.
   - Jika `BERTAMBAH = Kredit`, nilai laporan = total kredit - total debit.
7. Akun `HEADER` dan sub klasifikasi tidak boleh dipakai transaksi.
8. Kode sub klasifikasi 3 digit tidak dibuat sebagai akun COA agar daftar akun tidak menampilkan data ganda.
9. Akun leaf dari `KIRAAN` menjadi akun `POSTING`.
10. Semua auto-posting tetap lewat `tbkeu_mapping_akun`, bukan hardcode controller.

## Step Development Module Jurnal

### Step 1 - Database Staging dan Master Zahir

Tambahkan migration untuk:

- batch import Excel Zahir;
- staging per sheet;
- master sub klasifikasi;
- kolom referensi Zahir pada akun atau tabel relasi akun;
- rule laporan laba rugi/neraca.

Output yang wajib ada:

- data mentah Excel tersimpan untuk audit;
- validasi jumlah row per sheet;
- validasi join `AKUN LABA RUGI`/`AKUN NERACA` ke `KIRAAN`;
- daftar 4 akun lebih di `KIRAAN` yang tidak punya rule laporan.

### Step 2 - Importer Excel Zahir

Buat service import yang:

- membaca workbook `.xlsx`;
- mapping nama sheet aktual: `KELOMPOK`, `KLASIFIKASI`, `SUB CLASS`, `KIRAAN`, `AKUN LABA RUGI`, `AKUN NERACA`;
- normalisasi kode rekening `410-10` menjadi `41010`;
- menyimpan batch dan staging;
- menghasilkan preview sebelum apply;
- menolak apply jika ada duplicate fatal atau rule mengarah ke akun yang tidak ada.

### Step 3 - Sinkronisasi COA

Apply hasil import ke COA dengan urutan:

1. Upsert klasifikasi utama.
2. Upsert sub klasifikasi sebagai tabel master khusus, bukan sebagai akun COA.
3. Upsert akun `KIRAAN` sebagai akun `POSTING`.
4. Isi parent akun langsung ke header klasifikasi 4 digit berdasarkan `NOKLASIFIKASI`.
5. Set `saldo_normal` dari rule utama akun:
   - Debit bertambah menjadi saldo normal `DEBIT`.
   - Kredit bertambah menjadi saldo normal `KREDIT`.
6. Tandai akun tanpa rule sebagai perlu review, jangan otomatis dipakai laporan.

Catatan cutover:

- Akun seed yang sudah dipakai jurnal jangan dihapus.
- Setelah akun Zahir masuk, mapping auto-posting perlu diarahkan ulang dari akun seed ke akun leaf Zahir.

### Step 4 - Rule Engine Laba Rugi dan Neraca

Buat service laporan yang membaca rule eksplisit, bukan hanya klasifikasi:

- `LABA_RUGI`: filter tanggal periode.
- `NERACA`: akumulasi sampai tanggal akhir.
- Ambil jurnal `POSTED` saja.
- Hitung nilai akun berdasarkan `bertambah_side`.
- Sajikan subtotal per klasifikasi dan sub klasifikasi.

### Step 5 - UI Accounting

Tambahkan atau rapikan layar:

- Import Zahir: upload, preview, error, apply.
- Review COA: klasifikasi, sub klasifikasi, akun leaf, status rule.
- Mapping akun: event auto-posting ke akun Zahir.
- Jurnal umum: input, draft, posting, reversal.
- Laporan: buku besar, neraca saldo, laba rugi, neraca.

### Step 6 - Auto-posting dan Cutover

Setelah akun dan rule Zahir valid:

- update `tbkeu_mapping_akun` untuk sales invoice, goods issue, goods receipt, purchase invoice, payment, retur, stock adjustment;
- jalankan UAT auto-posting dengan data sales/LPB existing;
- pastikan jurnal balance dan muncul di laporan;
- lock periode sebelum deployment produksi.

### Step 7 - UAT dan Validasi

Minimal UAT:

- Import Excel Zahir tanpa kehilangan row.
- 9 klasifikasi, 36 sub class, 501 kiraan terbaca.
- 267 rule laba rugi dan 230 rule neraca terbaca.
- Semua rule laporan berhasil join ke akun canonical atau masuk daftar exception.
- Jurnal manual debit/kredit balance.
- Reversal membalik jurnal awal.
- Laba rugi mengikuti rule debit/kredit Zahir.
- Neraca mengikuti rule debit/kredit Zahir.
- Mapping auto-posting tidak menunjuk akun inactive/header.

## Perbaikan yang Dilakukan Pada Task Ini

Task ini belum melakukan perubahan kode aplikasi dan belum menjalankan DDL/DML ke database.

Perbaikan yang dilakukan adalah dokumentasi hasil analisa dan roadmap development berdasarkan scan aktual:

- scanning workbook Zahir;
- scanning dump SQL;
- query database lokal aktif;
- tracing route/controller/model/service accounting;
- dokumentasi gap dan step development.

## Update 2026-07-15 - Migration Data Excel Zahir

File migration database sudah dibuat pada:

`docs/database/accounting_karismaerp_master_migration_20260715.sql`

Isi migration:

- membuat struktur baru dengan prefix `tbkeu_`;
- seed 23 kelompok jurnal Karismaerp;
- seed/update 9 klasifikasi akun;
- seed 36 sub klasifikasi akun sebagai master pendukung;
- membuat header COA level 1 saja;
- seed 501 akun posting dari sheet `KIRAAN` sebagai level 2;
- menyimpan referensi akun Karismaerp pada `tbkeu_akun_karismaerp_ref`;
- seed 267 rule laba rugi dan 230 rule neraca pada `tbkeu_report_rule_akun`;
- resolve `id_akun` berdasarkan kode canonical Karismaerp;
- menyediakan query verification dan rollback manual sebagai komentar.

Validasi dilakukan pada database sementara `codex_karismaerp_migration_test`, bukan database kerja. Hasil validasi:

| Validasi | Hasil |
| --- | ---: |
| `tbkeu_karismaerp_kelompok_jurnal` | 23 |
| `tbkeu_sub_klasifikasi_akun` | 36 |
| `tbkeu_akun_karismaerp_ref` | 501 |
| `tbkeu_report_rule_akun` | 497 |
| Rule laba rugi | 267 |
| Rule neraca | 230 |
| Referensi Karismaerp tanpa akun | 0 |
| Rule laporan tanpa akun | 0 |
| Akun posting Karismaerp tanpa parent | 0 |
| Akun COA kode 3 digit | 0 |

Migration juga diuji rerun pada database sementara yang sama dan tidak menggandakan data.

## Update 2026-07-15 - Cleanup Akun 3 Digit

Berdasarkan temuan UI daftar akun, kode sub klasifikasi 3 digit seperti `110 Kas` muncul berdampingan dengan akun posting seperti `11010 Q Kas`. Ini membuat daftar akun terlihat ganda karena `110` sebenarnya adalah sub klasifikasi, bukan akun jurnal.

Perbaikan yang dilakukan:

- migration `docs/database/accounting_karismaerp_master_migration_20260715.sql` tidak lagi membuat akun COA 3 digit;
- `tbkeu_sub_klasifikasi_akun.id_akun_header` diset `NULL` untuk batch Karismaerp;
- 501 akun posting dibuat sebagai `level_akun = 2`;
- parent akun posting diarahkan langsung ke header klasifikasi 4 digit, contoh `11010 Q Kas` ke `1000 Harta`;
- database lokal aktif `kiucoid_karismaerp_local` sudah dibersihkan dari akun 3 digit setelah dipastikan tidak dipakai di `tbkeu_jurnal_detail`.

Hasil validasi database lokal setelah cleanup:

| Validasi | Hasil |
| --- | ---: |
| Total akun `tbkeu_akun` | 510 |
| Akun kode 3 digit tersisa | 0 |
| Referensi Karismaerp | 501 |
| Rule laporan | 497 |
| Referensi tanpa akun | 0 |
| Rule tanpa akun | 0 |
| Akun posting import tanpa parent | 0 |

Contoh validasi parent:

| Akun posting | Parent baru | Level |
| --- | --- | ---: |
| `11010 Q Kas` | `1000 Harta` | 2 |
| `11011 Q Kas (PBGM)` | `1000 Harta` | 2 |
| `11030 A Kas` | `1000 Harta` | 2 |
