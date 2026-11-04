# FASE 0 - Decisions

Status: decision log awal. Item `PENDING` wajib dikunci sebelum implementasi fitur accounting.

## Keputusan Terkunci

| ID | Keputusan | Status | Dampak |
| --- | --- | --- | --- |
| D-001 | Empat tabel `tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, `tbpo_akun_tr` berada di luar scope accounting. | APPROVED | Accounting tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency ke tabel tersebut. |
| D-002 | `tberp_stock_ledger` adalah ledger kuantitas stock, bukan general ledger finansial. | APPROVED | Laporan keuangan tidak boleh bersumber dari `tberp_stock_ledger`. |
| D-003 | Semua tabel accounting baru memakai prefix `tbkeu_`. | APPROVED | Migration accounting dipisah dari tabel operasional. |
| D-004 | Semua nominal accounting baru memakai `DECIMAL(19,4)`. | APPROVED | Source yang bertipe double/int harus dikonversi saat posting, bukan disalin sebagai schema accounting. |
| D-005 | Account resolution wajib melalui `tbkeu_mapping_akun`. | APPROVED | Tidak boleh hardcode kode akun di code. |
| D-006 | Jurnal POSTED immutable dan koreksi lewat reversal. | APPROVED | Tidak ada edit/delete jurnal posted. |
| D-007 | Route produksi accounting adalah `accounting` dan `keuangan/accounting`; simulator hanya tersedia di `accounting-test`. | APPROVED | Payload nominal buatan tidak dapat diposting dari route produksi. |
| D-008 | DO confirm sales action `siap` membaca faktur final dan membuat event `SALES_INVOICE` serta `GOODS_ISSUE`. | APPROVED | Revenue/VAT dan HPP/inventory terpisah, idempotent, dan kegagalan masuk exception. |
| D-009 | Payment AR/AP baru memakai `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi`. | APPROVED | Piutang/hutang ditutup dari tabel accounting yang punya nominal dan alokasi benar. |
| D-010 | Periode fiskal open, close, dan reopen wajib mencatat reason dan approval user di `tbkeu_periode_fiskal_log`. | APPROVED | Audit periode tersedia tanpa mengubah tabel legacy. |

## Keputusan Pending

| ID | Keputusan yang dibutuhkan | Status | Opsi | Risiko jika belum diputuskan |
| --- | --- | --- | --- | --- |
| D-101 | Nama dokumen canonical accounting | PENDING | Rename/duplikasi ke `AGENTS.md` dan `MASTER_SPEC.md`, atau resmi gunakan `AGENT.md` dan `MASTER_SPECS.md` | Agent/developer bisa membaca spec yang salah atau menganggap spec hilang. |
| D-102 | Source invoice penjualan resmi final setelah DO | APPROVED | Hook memakai `do/confirm_sales action=siap` dan membaca ulang tabel faktur final | Posting tidak bergantung pada detail DO orphan. |
| D-103 | Source invoice penjualan resmi | APPROVED | `tbso_faktur_penjualan` dan `tbso_faktur_detail` | Piutang, revenue, VAT, dan HPP dihitung dari satu sumber final. |
| D-104 | Source nominal pembayaran customer | APPROVED | Gunakan `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi` | Pembayaran customer dapat mengurangi piutang dengan alokasi. |
| D-105 | Source pembayaran supplier | APPROVED | Gunakan `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi` | Pembayaran supplier dapat mengurangi hutang dengan alokasi. |
| D-106 | Harga LPB untuk GRNI | APPROVED | Prioritas `tb_pre_po_invoice_adjustment.harga_satuan`, fallback `tb_pre_po.hrg_satuan`; harga nol menjadi exception | Persediaan/GRNI tidak pernah diposting nol. |
| D-107 | Master supplier authoritative | PENDING | `tb_suplier`, `tbpo_suplier`, atau mapping keduanya | AP dan aging supplier bisa terpecah/duplikat. |
| D-108 | Master customer authoritative | PENDING | `tb_customer.kd_customer` dengan dedupe, atau tabel baru snapshot | AR bisa salah relasi jika duplicate customer. |
| D-109 | Treatment mutasi gudang | PENDING | Tidak posting jika akun inventory sama, posting transfer jika mapping gudang beda | Jurnal inventory bisa dobel atau tidak tercatat. |
| D-110 | Final event stock adjustment | PENDING | Approval opname final, admin adjustment manual, atau event ledger `ADJIN/ADJOUT` | Selisih stock bisa diposting tanpa otorisasi. |
| D-111 | Reversal event DO/LPB/retur/mutasi | PENDING | Buat event cancel/reverse eksplisit per source | Jurnal koreksi tidak terlacak. |
| D-112 | Retur source table final | PENDING | Tabel retur existing setelah audit, atau tabel baru | Retur tidak bisa memulihkan AR/AP/inventory/COGS secara benar. |
| D-113 | Period close authority | PENDING | Role permission di `tb_menu`/`tb_akses_*` | Periode bisa dibuka/tutup tanpa kontrol. |
| D-114 | BG clearing/bounced flow | PENDING | Status di `tbkeu_pembayaran` baru dan event CLEAR/BOUNCE | Kas bank bisa diakui sebelum BG cair. |

## Keputusan Teknis yang Direkomendasikan

1. Buat alias/copy dokumen spec ke nama yang diminta agar instruksi dan repo konsisten.
2. Jangan pakai `tbkeu_pembayaran_faktur` existing sebagai source pembayaran final karena tidak ada nominal.
3. Jadikan LPB sukses sebagai source `GOODS_RECEIPT`, tetapi nilai posting harus fail jika harga PO tidak valid.
4. Jangan auto-post stock adjustment sampai ada approval final.
5. Jangan integrasikan auto-post langsung ke controller legacy sebelum service accounting selesai dan diuji idempotency.
