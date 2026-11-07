# FASE 0 - Implementation Tasks

Status: produksi bertahap. Core runtime accounting, route produksi, periode fiskal, payment allocation, opening balance, exception workflow, dan laporan dasar sudah tersedia.

Update 2026-07-15:

- `Accounting_service` naik menjadi service produksi melalui route `accounting` dan `keuangan/accounting`.
- UI produksi tersedia untuk jurnal manual, posting, reversal, detail jurnal, daftar jurnal, periode fiskal, payment allocation, opening balance, exception dashboard, dan laporan.
- Hook non-blocking membaca faktur final untuk `SALES_INVOICE` + `GOODS_ISSUE`; LPB final memakai `GOODS_RECEIPT` atau exception jika harga belum lengkap.
- Nomor jurnal memakai counter terkunci, period close memiliki checklist, reversal mempertahankan jurnal sumber di laporan, dan payment allocation memvalidasi outstanding per invoice.
- Migration hardening, rollback, SQL UAT DB-01–DB-19, flow UAT, dan panduan operasional tersedia.
- Payment customer/supplier memakai tabel accounting baru agar alokasi AR/AP tidak bergantung pada tabel legacy yang belum jelas.
- Source final untuk purchase invoice, supplier payment legacy, retur bernilai nominal, stock adjustment, dan mutasi gudang masih perlu validasi bisnis jika akan di-hook langsung ke controller legacy.

## Prinsip Eksekusi

- Semua task kecil, bisa diverifikasi, dan tidak menyentuh tabel terlarang.
- Accounting berdiri sebagai domain baru `tbkeu_*`.
- Source transaction tetap dibaca sebagai sumber event, bukan dijadikan general ledger.
- Semua implementasi setelah fase 0 wajib didahului SQL audit read-only.

## Task Breakdown

| ID | Task | Dependency | Acceptance criteria |
| --- | --- | --- | --- |
| A0-01 | Rapikan dokumen sumber accounting | Keputusan nama file spec | `docs/AGENTS.md` dan `docs/accounting/MASTER_SPEC.md` tersedia atau keputusan resmi memakai nama existing dicatat. |
| A0-02 | Jalankan `database/audit/accounting_precheck.sql` di database lokal | DB lokal siap read-only | Output audit duplicate/orphan/type/status tersedia dan disimpan sebagai bukti manual. |
| A0-03 | Validasi status final penjualan | A0-02, diskusi bisnis | Satu trigger final dipilih: DO confirm sales, status DO tertentu, atau tabel faktur baru. |
| A0-04 | Validasi tabel faktur penjualan resmi | A0-03 | Diputuskan apakah source invoice adalah `tb_detail_do` atau tabel baru `tbso_faktur_penjualan`. |
| A0-05 | Validasi source pembayaran customer | A0-02 | Tabel pembayaran bernominal ditemukan atau desain `tbkeu_pembayaran` menjadi source baru. |
| A0-06 | Validasi source pembayaran supplier | A0-02 | Trigger dan source nominal pembayaran supplier diputuskan. |
| A0-07 | Validasi harga LPB/GRNI | A0-02 | Urutan resolusi harga PO/LPB disetujui dan exception jika harga nol ditentukan. |
| A0-08 | Validasi master supplier authoritative | A0-02 | Aturan pemilihan `tb_suplier` vs `tbpo_suplier` disetujui. |
| A0-09 | Validasi master customer/barang/gudang | A0-02 | Key dan tipe data yang dipakai accounting ditentukan. |
| A0-10 | Validasi retur source table | A0-02 | Tabel header/detail retur aktual, status final, nominal, dan stock effect terpetakan. |
| A0-11 | Validasi stock adjustment final event | A0-02 | Event approval final opname/adjustment ditentukan. |
| A0-12 | Validasi mutasi gudang accounting treatment | A0-09 | Diputuskan mutasi antar gudang membuat jurnal atau hanya stock ledger kuantitas. |
| A1-01 | Desain migration `tbkeu_klasifikasi_akun` dan `tbkeu_akun` | A0-01 | Migration up/down draft tersedia, DECIMAL tidak relevan, InnoDB utf8mb4, rollback aman. |
| A1-02 | Desain migration periode, jenis jurnal, nomor dokumen | A1-01 | Constraint unique dan concurrency `SELECT ... FOR UPDATE` dirancang. |
| A1-03 | Desain migration jurnal header/detail/log/exception | A1-02 | Idempotency key, source unique, reversal, immutable POSTED dirancang. |
| A1-04 | Desain migration mapping akun | A1-01 | Event/role/scope/priority dan no hardcode policy tersedia. |
| A1-05 | Desain faktur pembelian dan pembayaran baru | A0-05, A0-06, A0-07 | Struktur mendukung AP/AR, alokasi multi faktur, BG, status final. |
| A1-06 | Seed klasifikasi, jenis jurnal, COA template | A1-01, A1-02 | Seed tidak membaca `tbpo_akun_tr`. |
| A1-07 | Seed mapping akun template | A1-04 | Semua event wajib punya role minimal, tetapi akun bisa dikonfigurasi. |
| A2-01 | Buat Accounting service skeleton | A1-03, A1-04 | Service/library tersedia tanpa auto-hook ke source transaction. |
| A2-02 | Implement journal validation | A2-01 | Validasi balance, akun posting aktif, periode open, nominal positif. |
| A2-03 | Implement account mapping resolver | A2-01, A1-04 | Resolusi scope spesifik ke global, gagal tanpa fallback hardcode. |
| A2-04 | Implement journal number service | A2-01, A1-02 | Nomor unik per jenis/tahun/bulan, aman concurrency. |
| A2-05 | Implement exception service | A2-01, A1-03 | Gagal posting tersimpan tanpa jurnal parsial. |
| A3-01 | Implement manual journal CRUD | A2-* | DRAFT bisa diedit; POSTED immutable; reversal tersedia. |
| A3-02 | Implement posting sales invoice | A0-03, A0-04, A2-* | Posting idempotent, HPP valid, revenue/VAT/COGS balance. |
| A3-03 | Implement posting customer receipt | A0-05, A2-* | Alokasi tidak melebihi outstanding; BG handled. |
| A3-04 | Implement posting goods receipt | A0-07, A2-* | LPB menghasilkan Inventory/GRNI; harga nol jadi exception. |
| A3-05 | Implement purchase invoice | A1-05, A2-* | GRNI/AP/VAT/variance balance. |
| A3-06 | Implement supplier payment | A1-05, A2-* | AP turun, kas/bank turun, alokasi valid. |
| A3-07 | Implement sales/purchase return | A0-10, A2-* | Retur memproses AR/AP/kas/VAT/Inventory/COGS sesuai tipe. |
| A3-08 | Implement stock adjustment | A0-11, A2-* | Selisih positif/negatif sesuai mapping dan HPP. |
| A3-09 | Implement mutasi gudang accounting | A0-12, A2-* | Jika akun gudang sama tidak posting; jika beda akun posting transfer. |
| A4-01 | Build UI Chart of Accounts | A1-01, permissions | Tree akun, active/deactivate, no delete used account. |
| A4-02 | Build UI Journal | A3-01 | Total debit/kredit/selisih/status periode terlihat. |
| A4-03 | Build UI Mapping Akun | A1-04 | Warning mapping event wajib belum lengkap. |
| A4-04 | Build exception dashboard | A2-05 | OPEN/RESOLVED/IGNORED, retry setelah mapping diperbaiki. |
| A4-05 | Build purchase invoice/payment UI | A1-05 | Faktur pembelian dan pembayaran bisa dialokasikan. |
| A5-01 | Build reports from posted journals only | A3-* | Jurnal umum, buku besar, neraca saldo, laba rugi, neraca, aging, kas/bank. |
| A5-02 | Export/print reports | A5-01 | Excel/PDF/print dari backend, bukan hitung ulang JS. |
| A6-01 | Test matrix accounting | A3-*, A4-*, A5-* | Minimal 40 test dari spec lulus, termasuk tabel terlarang tidak disentuh. |
| A6-02 | Deployment and rollback docs | Semua task | Backup, migration up/down, seed, verification, rollback terdokumentasi. |

## Dependency Chain Minimum

1. Fase keputusan: A0-01 sampai A0-12.
2. Fase schema: A1-01 sampai A1-07.
3. Fase service core: A2-01 sampai A2-05.
4. Fase transaksi: A3-01 sampai A3-09.
5. Fase UI: A4-01 sampai A4-05.
6. Fase laporan: A5-01 sampai A5-02.
7. Fase hardening: A6-01 sampai A6-02.

## Acceptance Criteria Global

- Tidak ada query accounting ke `tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, `tbpo_akun_tr`.
- Tidak ada kode akun hardcoded pada controller/model/view/library/helper/JavaScript.
- Semua nominal baru memakai `DECIMAL(19,4)`.
- Semua posting idempotent dan atomic dalam database transaction.
- Jurnal POSTED immutable.
- Reversal tidak menghapus jurnal asli.
- Laporan hanya membaca jurnal POSTED.
- Exception dashboard menampung gagal posting tanpa jurnal parsial.
