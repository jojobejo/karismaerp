# Scan Project dan Modul Accounting

Tanggal scan: 2026-07-13

Scope scan:

- Struktur project KARISMA ERP.
- Modul-modul yang sudah tersedia dari controller, model, view, route, dan dokumen.
- Status modul `Jurnal` sebagai Chart of Accounts.
- Batasan accounting yang harus dijaga sebelum masuk transaksi General Ledger penuh.

Dokumen ini dibuat terpisah dari user guide dan UAT supaya hasil scan tidak tercampur dengan tata cara penggunaan.

## Ringkasan Eksekutif

Project memakai struktur CodeIgniter 3 klasik. Modul bisnis utama berada di `application/controllers`, `application/models`, dan `application/views/content`. Modul accounting yang bisa digunakan saat ini adalah `Jurnal`, tetapi statusnya masih sebagai master Chart of Accounts, bukan transaksi accounting penuh.

Route aktif untuk modul ini:

- `jurnal`
- `keuangan/jurnal`

Implementasi `Jurnal` saat ini menempel pada modul keuangan legacy:

- Controller: `application/controllers/keuangan/C_Keuangan.php`
- Model: `application/models/M_Keuangan.php`
- View: `application/views/content/keuangan/jurnal.php`
- AJAX view: `application/views/content/keuangan/ajax/ajax_jurnal.php`
- Menu dashboard: `application/models/M_Dashboard.php`
- Sidebar: `application/views/partial/main/sidebar.php`

Belum ditemukan controller/model terpisah seperti `C_Jurnal`, `C_Akun`, `M_Jurnal`, atau service posting accounting khusus. Folder `application/libraries/Accounting` saat ini berisi dokumentasi, belum berisi service posting.

## Struktur Project

Area utama:

| Area | Path | Catatan |
| --- | --- | --- |
| Front controller | `index.php` | Entry point aplikasi. |
| Config | `application/config` | Route custom berada di `routes.php`. |
| Controller | `application/controllers` | Berisi modul root dan subfolder domain. |
| Model | `application/models` | Mayoritas model domain masih di root model. |
| View | `application/views/content` | View domain dipisah per folder. |
| Helper | `application/helpers` | Helper umum seperti stock, tanggal, login auth. |
| Library | `application/libraries` | Library umum dan folder `Accounting/docs`. |
| Database docs | `docs/database` | SQL migration/dump/dokumentasi database. |
| Accounting docs | `docs/accounting` | Spec, decisions, tasks, UAT, alur penggunaan. |
| Database audit | `database/audit` | SQL audit read-only accounting precheck. |

## Modul Yang Terlihat

Controller utama yang ditemukan:

| Modul | Controller | Model terkait | View utama |
| --- | --- | --- | --- |
| Portal/Auth/Dashboard | `Portal.php`, `Auth.php`, `Dashboard.php` | `M_Auth.php`, `M_Dashboard.php` | `portal`, `login`, `dashboard` |
| Keuangan legacy + Jurnal | `keuangan/C_Keuangan.php` | `M_Keuangan.php` | `content/keuangan/*` |
| Sales Order | `sales/C_SalesOrder.php` | `M_SalesOrder.php` | `content/sales/*` |
| Sales | `sales/C_Sales.php` | `M_Sales.php` | `content/sales/*` |
| Logistik/DO | `logistik/C_Logistik.php` | `M_Logistik.php` | `content/logistik/*` |
| ICS/Stock/LPB/Retur/Mutasi | `logistik/C_Ics.php` | `M_Ics.php` | `content/logistik/ics/*` |
| Distribusi | `logistik/C_Distribusi.php` | `M_Distribusi.php` | `content/logistik/distribusi/*` |
| Checker | `logistik/C_Checker.php` | `M_Checker.php` | `content/logistik/checker/*` |
| Stock opname admin | `admin/C_Stockopname.php` | `admin/M_Stockopname.php` | `content/admin/stockopname_*` |
| HRD | `hrd/C_Hrd.php` | `M_Hrd.php` | `content/hrd/*` |
| KPI | `kpi/C_Kpi.php` | `M_Kpi.php` | `content/kpi/*` |
| Master user/menu/jobdesk/access | `master/*` | `models/master/*` | `content/master/*` |
| Inventaris | `inventaris/C_Inventaris.php` | `M_Inventaris.php` | `content/inventaris/*` |
| Maintenance | `maintenance/C_Maintenance.php` | `M_Maintenance.php` | Tidak discan detail |
| Pelanggan | `pelanggan/C_Pelanggan.php` | `M_Pelanggan.php` | `content/pelanggan/*` |
| Schedule | `schedule/C_Schedule.php` | Tidak discan detail | `content/schedule/*` |
| API | `api/C_Api.php` | `Api/M_Api.php`, `M_api.php` | API only |

Catatan: daftar ini berdasarkan scan file, bukan validasi seluruh route business process satu per satu.

## Route Jurnal

Route `Jurnal` didefinisikan eksplisit pada `application/config/routes.php`.

Route publik:

- `jurnal`
- `keuangan/jurnal`

Endpoint AJAX:

- `jurnal/list`
- `jurnal/detail`
- `jurnal/account-journal`
- `jurnal/store`
- `jurnal/update`
- `jurnal/deactivate`
- `jurnal/delete`
- `jurnal/master/(:any)/list`
- `jurnal/master/(:any)/detail`
- `jurnal/master/(:any)/store`
- `jurnal/master/(:any)/update`
- `jurnal/master/(:any)/delete`

Alias endpoint yang sama juga tersedia di bawah prefix `keuangan/jurnal/...`.

## Status Modul Jurnal Saat Ini

Fungsi yang sudah siap dipakai:

- Membuka daftar akun.
- Filter akun berdasarkan klasifikasi.
- Search akun berdasarkan kode, nama, atau klasifikasi.
- Menambah akun `HEADER`.
- Menambah akun `POSTING`.
- Mengubah akun.
- Menonaktifkan akun.
- Menghapus akun jika belum dipakai jurnal dan tidak memiliki child.
- Mengelola master pendukung:
  - klasifikasi;
  - saldo normal;
  - tipe kontrol;
  - parent/subclass.
- Melihat baris jurnal per akun jika tabel `tbkeu_jurnal` dan `tbkeu_jurnal_detail` sudah tersedia.
- Menampilkan pesan aman jika schema accounting atau schema jurnal belum tersedia.

Fungsi yang belum siap sebagai transaksi accounting penuh:

- Input jurnal debit/kredit manual lengkap.
- Validasi dan posting jurnal.
- Reversal jurnal.
- Mapping akun.
- Auto-posting dari sales, purchase, LPB, payment, retur, mutasi, dan stock adjustment.
- Exception dashboard posting.
- Laporan buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, dan kas/bank.

## Validasi Accounting Yang Sudah Dijaga

Di controller/model:

- Akses modul dibatasi untuk admin/keuangan.
- Schema readiness dicek sebelum CRUD akun.
- Kode akun dan nama akun wajib.
- Kode akun harus unik.
- Klasifikasi wajib valid.
- Saldo normal wajib valid.
- Tipe akun hanya `HEADER` atau `POSTING`.
- Tipe kontrol wajib valid.
- Parent akun harus ada dan harus bertipe `HEADER`.
- Akun tidak boleh menjadi parent bagi dirinya sendiri.
- Akun yang memiliki child tidak boleh diubah menjadi `POSTING`.
- Akun `HEADER` otomatis tidak boleh manual journal.
- Akun yang sudah dipakai jurnal tidak boleh dihapus.
- Akun yang memiliki child tidak boleh dihapus.
- Master pendukung yang sudah dipakai tidak boleh dihapus.

Di database migration:

- `tbkeu_akun.kode_akun` unik.
- `tbkeu_akun.id_klasifikasi` memiliki foreign key ke `tbkeu_klasifikasi_akun`.
- `tbkeu_akun.parent_id` memiliki self foreign key ke `tbkeu_akun`.
- Nominal jurnal di schema GL memakai `DECIMAL(19,4)`.
- `tbkeu_jurnal` memiliki unique `nomor_jurnal`.
- `tbkeu_jurnal` memiliki unique `idempotency_key`.
- `tbkeu_jurnal` memiliki unique source event: `source_module`, `source_type`, `source_id`, `posting_event`.
- `tbkeu_jurnal_detail.id_akun` foreign key ke `tbkeu_akun`.

## Prinsip Accounting Yang Belum Dijaga Oleh Runtime

Prinsip berikut sudah ada di dokumen/spec, tetapi belum ada service runtime transaksi penuh:

- Setiap jurnal harus double-entry dan total debit sama dengan total kredit.
- Akun transaksi baru wajib `POSTING`, aktif, dan eligible untuk transaksi.
- Jurnal `POSTED` immutable.
- Koreksi jurnal harus melalui reversal.
- Laporan hanya membaca jurnal `POSTED`.
- Auto-posting wajib memakai `tbkeu_mapping_akun`.
- Tidak boleh hardcode kode akun pada controller, model, view, helper, library, atau JavaScript.
- Posting harus atomic dalam database transaction.
- Gagal posting harus masuk exception tanpa jurnal parsial.

## Database Accounting Yang Ada Dalam Dokumen/Migration

Migration tahap Chart of Accounts:

- `docs/database/accounting_jurnal_accounts_20260713.sql`
- `docs/database/accounting_jurnal_master_options_20260713.sql`

Tabel yang dibuat:

- `tbkeu_klasifikasi_akun`
- `tbkeu_akun`
- `tbkeu_saldo_normal`
- `tbkeu_tipe_kontrol`

Migration tahap awal General Ledger:

- `docs/database/accounting_general_ledger_journal_20260713.sql`

Tabel yang dibuat:

- `tbkeu_periode_fiskal`
- `tbkeu_jenis_jurnal`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`

Tabel target accounting penuh yang belum ditemukan sebagai migration implementatif:

- `tbkeu_mapping_akun`
- `tbkeu_jurnal_log`
- `tbkeu_posting_exception`
- `tbkeu_nomor_dokumen`
- `tbkeu_saldo_awal_akun`
- `tbkeu_faktur_pembelian`
- `tbkeu_faktur_pembelian_detail`
- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`

## Catatan Hardcode Kode Akun

Scan tidak menemukan hardcode kode akun transaksi di controller/model/view accounting untuk proses posting karena proses posting memang belum ada.

Namun file SQL seed memiliki kode akun contoh/template, misalnya akun header dan akun awal seperti kas, bank, piutang, persediaan, hutang, penjualan, dan HPP. Ini masih berada di file seed migration, bukan di controller/model/view/helper/library/JavaScript.

Catatan penting:

- Untuk seed awal COA, kode akun di SQL seed masih dapat diterima selama dianggap template master awal.
- Untuk auto-posting dan runtime transaksi, kode akun tidak boleh di-hardcode. Wajib memakai `tbkeu_mapping_akun`.
- File sample jurnal di `accounting_general_ledger_journal_20260713.sql` masih komentar dan memakai kode akun sample. Jangan aktifkan sample tersebut untuk pola implementasi produksi.

## Keterkaitan Dengan Modul Operasional

Modul operasional yang menjadi kandidat source accounting penuh:

| Area | Kandidat source | Status keputusan |
| --- | --- | --- |
| Sales invoice | SO/DO/faktur penjualan | Belum final |
| Stock keluar/HPP | DO dan stock ledger | Belum final |
| Customer receipt | Payment customer | Belum ada nominal valid pada tabel existing yang discan |
| Goods receipt/LPB | LPB/PO receive | Perlu finalisasi harga dan event final |
| Purchase invoice | Tabel baru faktur pembelian | Belum ada runtime |
| Supplier payment | Tabel baru pembayaran/alokasi | Belum ada runtime |
| Retur | Flow retur ICS | Belum final source/status |
| Mutasi gudang | Mutasi ICS | Perlu keputusan apakah membuat jurnal finansial |
| Stock adjustment | Stock opname/adjustment | Perlu approval final event |

Empat tabel Purchase Order yang tetap di luar scope accounting:

- `tbpo_transaksi`
- `tbpo_transaksi_tmp`
- `tbpo_transaksi_trashbin`
- `tbpo_akun_tr`

Accounting tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency ke tabel tersebut.

## Temuan Teknis Kecil

1. `updateSummary()` di `application/views/content/keuangan/ajax/ajax_jurnal.php` mengisi elemen `#sumTotal`, `#sumHeader`, `#sumPosting`, `#sumActive`, dan `#sumInactive`, tetapi elemen tersebut tidak terlihat di `application/views/content/keuangan/jurnal.php` saat scan. Dampaknya rendah: data tetap jalan, hanya ringkasan tidak tampil jika elemen memang belum ada.
2. `application/libraries/Accounting` saat ini hanya berisi `docs`, belum ada library/service accounting executable.
3. `docs/accounting/DECISION.md`, `docs/accounting/TASK.md`, dan `docs/accounting/TEST_MATRIX.md` terlihat kosong. Dokumen aktif yang berisi konten adalah `DECISIONS.md`, `TASKS.md`, `ALUR_PENGGUNAAN_ACCOUNTING.md`, dan `UAT_ACCOUNTING_MODULE.md`.
4. Ada dokumen accounting pada dua lokasi: `docs/accounting` dan `application/libraries/Accounting/docs`. Perlu dijaga agar tidak saling bertentangan.

## Rekomendasi Urutan Lanjutan

1. Kunci keputusan pending sebelum transaksi penuh:
   - source final sales invoice;
   - source pembayaran customer/supplier;
   - harga LPB/GRNI;
   - source retur;
   - treatment mutasi gudang;
   - event final stock adjustment.
2. Buat migration `tbkeu_mapping_akun` sebelum auto-posting.
3. Buat service posting accounting terpisah di library/model domain accounting, jangan langsung menaruh logika double-entry di controller legacy.
4. Implement jurnal manual sebagai fase berikutnya:
   - draft;
   - validasi balance;
   - post;
   - immutable posted;
   - reversal.
5. Baru setelah service jurnal stabil, integrasikan auto-posting dari modul operasional.
6. Laporan keuangan dibuat terakhir dan hanya membaca `tbkeu_jurnal`/`tbkeu_jurnal_detail` berstatus `POSTED`.

## Kesimpulan

Modul `Jurnal` sudah layak dipakai sebagai pengelolaan Chart of Accounts. Fondasi schema dan UI awal sudah tersedia untuk akun, klasifikasi, saldo normal, tipe kontrol, dan panel histori jurnal per akun.

Modul ini belum boleh diperlakukan sebagai General Ledger penuh. Semua transaksi finansial, posting, reversal, mapping akun, auto-posting, dan laporan masih perlu implementasi service accounting yang menjaga double-entry, immutability, idempotency, dan mapping akun tanpa hardcode kode akun.
