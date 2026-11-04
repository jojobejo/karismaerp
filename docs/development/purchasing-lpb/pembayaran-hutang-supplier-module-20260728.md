# Development Module Pembayaran Hutang Supplier

Tanggal: 2026-07-28  
Scope: implementasi module pembayaran hutang supplier / pemasok berdasarkan konsep `konsep-pembayaran-hutang-supplier-20260727`.

## Ringkasan

Module baru dibuat untuk pembayaran kewajiban perusahaan ke supplier. Secara akuntansi ini adalah `SUPPLIER_PAYMENT`, bukan piutang customer. Flow customer lama di `keuangan/pembayaran` tetap dipertahankan dan tidak dicampur dengan AP supplier.

## File Baru

- `application/controllers/keuangan/C_PembayaranSupplier.php`
- `application/models/M_PembayaranSupplier.php`
- `application/views/content/keuangan/pembayaran_supplier/index.php`
- `application/views/content/keuangan/pembayaran_supplier/detail.php`
- `application/views/content/keuangan/pembayaran_supplier/form.php`
- `application/views/content/keuangan/pembayaran_supplier/history.php`

## File Diubah

- `application/config/routes.php`
- `application/libraries/Accounting_service.php`

## Route Baru

| Route | Controller | Fungsi |
| --- | --- | --- |
| `keuangan/pembayaran-supplier` | `C_PembayaranSupplier::index` | Dashboard supplier outstanding |
| `keuangan/pembayaran-supplier/supplier/(:num)` | `supplier` | Detail dokumen hutang per supplier |
| `keuangan/pembayaran-supplier/form/(:num)` | `form` | Form pembayaran dan alokasi |
| `keuangan/pembayaran-supplier/post` | `post` | Posting pembayaran supplier |
| `keuangan/pembayaran-supplier/history` | `history` | Histori pembayaran supplier |
| `keuangan/pembayaran-supplier/void/(:num)` | `void` | Void payment dengan reversal jurnal |

## Controller

`C_PembayaranSupplier` menangani:

- validasi akses user Keuangan/Admin;
- dashboard supplier dengan hutang terbuka;
- detail dokumen hutang supplier;
- form pembayaran supplier;
- posting payment ke accounting service;
- histori payment;
- void payment dengan alasan wajib.

Role yang diizinkan:

- username `admin`;
- `is_admin_dashboard`;
- jobdesk `KIUKEU`, `ADMINKEU`, `ADMINKEUTC`, `ACCOUNTING`, `FINANCE`;
- level 1 dengan jobdesk admin/keuangan.

## Model

`M_PembayaranSupplier` menangani:

- pengecekan schema minimal;
- summary outstanding;
- daftar supplier outstanding;
- daftar dokumen hutang per supplier;
- daftar akun kas/bank;
- histori payment supplier;
- posting supplier payment melalui `Accounting_service::create_payment()`;
- void payment melalui `Accounting_service::reversal_journal()`.

Outstanding dihitung dari jurnal `POSTED`:

- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tbkeu_akun`

Filter yang dipakai:

- `j.status = POSTED`;
- `j.reversed_at IS NULL`;
- `d.nomor_dokumen` tidak kosong;
- akun hutang dibaca dari `a.tipe_kontrol = HUTANG` atau akun historis `a.kode_akun = 21098`.

## View

Folder baru:

`application/views/content/keuangan/pembayaran_supplier/`

Halaman:

- `index.php`: ringkasan supplier, total dokumen, total outstanding, histori singkat.
- `detail.php`: daftar dokumen hutang terbuka per supplier dengan pilihan dokumen.
- `form.php`: header pembayaran, akun kas/bank, nominal, dan alokasi per dokumen.
- `history.php`: daftar pembayaran supplier dan tombol void.

## Integrasi Accounting Service

`Accounting_service.php` diperluas agar:

- `create_payment()` dapat memakai akun kas/bank yang dipilih dari form melalui `cash_bank_account_id`;
- alokasi `SUPPLIER_PAYMENT` dapat mengurangi akun hutang historis yang sama dengan LPB, termasuk akun `21098`;
- validasi outstanding supplier mengabaikan jurnal sumber yang sudah `reversed_at`.

Perubahan ini menjaga payment tetap masuk ke tabel standar:

- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`

## Aturan MVP

- Payment supplier harus dialokasikan penuh.
- Total nominal pembayaran harus sama dengan total alokasi.
- Down payment/unapplied supplier belum dibuka pada fase ini.
- Payment hanya memotong dokumen yang outstanding-nya masih positif.
- Void wajib memakai alasan dan membuat reversal jurnal.
- Jurnal posted tidak diedit langsung.

## Validasi Teknis

Lint PHP berhasil untuk:

- controller baru;
- model baru;
- semua view baru;
- `Accounting_service.php`.

`git diff --check` hanya menampilkan warning line-ending LF/CRLF pada file lama, tidak ada whitespace error.

