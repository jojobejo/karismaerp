# UAT Accounting Produksi

Tanggal: 2026-07-14

## Hasil Verifikasi Otomatis Saat Development

| Area | Verifikasi | Status |
| --- | --- | --- |
| PHP syntax service | `php -l application/libraries/Accounting_service.php` | PASS |
| PHP syntax controller accounting | `php -l application/controllers/keuangan/C_Accounting.php` | PASS |
| PHP syntax controller DO/LPB hook | `php -l application/controllers/logistik/C_Logistik.php`, `php -l application/controllers/logistik/C_Ics.php` | PASS |
| PHP syntax route/view | `php -l application/config/routes.php`, `php -l application/views/content/keuangan/accounting_runtime_test.php` | PASS |
| Tabel terlarang | Scan file aktif yang diubah | PASS, hanya komentar SQL safety yang menyebut tabel terlarang |

## UAT Database Yang Harus Dijalankan Setelah Migration

| ID | Acceptance Criteria | Expected |
| --- | --- | --- |
| UAT-P01 | Route `accounting` terbuka untuk user admin/keuangan | Halaman Accounting Produksi tampil |
| UAT-P02 | Schema ready | Panel validasi menampilkan `READY` |
| UAT-P03 | Open periode dengan reason | Periode tersimpan `OPEN`, log masuk `tbkeu_periode_fiskal_log` |
| UAT-P04 | Close periode dengan reason | Status menjadi `CLOSED`, posting tanggal periode tersebut ditolak |
| UAT-P05 | Reopen periode dengan reason | Status kembali `OPEN`, log reopen tersimpan |
| UAT-P06 | Draft jurnal balance | Draft jurnal tersimpan |
| UAT-P07 | Posting draft | Status jurnal menjadi `POSTED`, `posted_by` dan `posted_at` terisi |
| UAT-P08 | Reversal jurnal posted | Jurnal reversal terbentuk dan jurnal awal menjadi `REVERSED` |
| UAT-P09 | Payment customer dengan alokasi | `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi` terisi, jurnal payment `POSTED` |
| UAT-P10 | Supplier payment dengan alokasi | AP turun dan kas/bank kredit sesuai mapping |
| UAT-P11 | Saldo awal balance dimigrasikan | Jurnal `OPENING_BALANCE` `POSTED`, saldo awal terkunci |
| UAT-P12 | Exception retry | Payload gagal bisa di-retry dan berubah `RESOLVED` bila sukses |
| UAT-P13 | Exception resolve/ignore | Status berubah dengan catatan |
| UAT-P14 | DO confirm sales `siap` | Event `SALES_INVOICE` idempotent dibuat atau exception tercatat |
| UAT-P15 | LPB final | Event `GOODS_RECEIPT` idempotent dibuat atau exception tercatat |
| UAT-P16 | Laporan | Buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, kas/bank membaca jurnal `POSTED` |

## Catatan

SQL migration tidak dijalankan otomatis pada development ini untuk menghindari risiko menulis ke database yang tidak terkonfirmasi sebagai lokal/staging disposable.

