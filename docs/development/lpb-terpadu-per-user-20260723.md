# Development - Panduan Penggunaan dan Aturan Per-User LPB Terpadu

Tanggal: 2026-07-23

## Scope

Dokumen ini mencatat pekerjaan dokumentasi operasional untuk modul:

- LPB dari PO;
- LPB Manual;
- Jurnal LPB;
- Adjustment LPB;
- Retur Pembelian.

Tidak ada perubahan kode aplikasi pada pekerjaan ini. Output utama adalah panduan pemakaian terpadu:

```text
docs/penggunaan-lpb-terpadu-per-user-20260723.md
```

## Referensi Aplikasi yang Dipakai

Route LPB dan Retur:

- `ics/icspo`
- `ics/detail_po`
- `ics/detail_record_lpb`
- `ics/lpb_manual`
- `ics/lpb_manual/store`
- `ics/lpb_manual/barang`
- `ics/lpb_report`
- `ics/lpb_manual_log`
- `ics/retur/pembelian`
- `ics/retur/pembelian/adjustment`

Route Jurnal LPB:

- `jurnal`
- `keuangan/jurnal`
- `jurnal/purchase-list`
- `jurnal/purchase-detail`
- `keuangan/jurnal/purchase-list`
- `keuangan/jurnal/purchase-detail`

## File Aplikasi Terkait

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/models/M_ReturPembelian.php`
- `application/models/M_LpbPriceAdjustment.php`
- `application/models/M_Keuangan.php`
- `application/libraries/Accounting_source_service.php`
- `application/libraries/Accounting_service.php`
- `application/views/content/logistik/ics/icspo.php`
- `application/views/content/logistik/ics/detail_po.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`
- `application/views/content/logistik/ics/lpb_manual.php`
- `application/views/content/logistik/ics/lpb_report.php`
- `application/views/content/logistik/ics/lpb_manual_log.php`
- `application/views/content/logistik/ics/returform_pembelian.php`
- `application/views/content/logistik/ics/returform_pembelian_adjustment.php`
- `application/views/content/keuangan/jurnal.php`

## Aturan Akses yang Dicatat

Panduan operasional mengikuti guard dan resolver yang sudah ada:

- Purchasing: `departemen` berisi `PURCHASING`, `jobdesk` `ADMINPURCHASING` / `ADMIN PO`, username `admpo`, atau akses admin.
- Logistik: `departemen` berisi `LOGISTIK`, atau `jobdesk` `LOGISTIK`, `ADMINLOGLPB`, `ADMLPB2`, `ADMINICS`.
- LPB Manual: Purchasing/Admin PO/Admin dan IT/Admin support.
- Laporan LPB: Purchasing, Logistik, IT/Admin support, dan Admin.
- Log LPB Manual: IT/Admin support dan Admin.
- Jurnal: user keuangan/admin sesuai akses modul jurnal.

## Keputusan Dokumentasi

1. Dokumen baru dibuat sebagai panduan terpadu, bukan mengganti dokumen modul yang sudah ada.
2. Panduan memakai perilaku terbaru LPB Purchasing: update invoice, split invoice, dan update faktur tidak mengubah status LPB.
3. Panduan menegaskan bahwa koreksi harga setelah stok berjalan harus memakai `Adjustment LPB`.
4. Panduan menegaskan bahwa jurnal LPB bersifat otomatis dan Accounting memeriksa hasil/exception.
5. Panduan menegaskan bahwa data posted tidak dihapus langsung, tetapi dikoreksi melalui adjustment, retur, reversal, atau void resmi.

## Dokumen Existing yang Menjadi Dasar

- `docs/penggunaan-jurnal-pembelian-lpb-po-20260718.md`
- `docs/penggunaan-adjustment-harga-lpb-20260722.md`
- `docs/penggunaan-retur-pembelian-lpb-final-20260722.md`
- `docs/penggunaan-ics-lpb-purchasing-invoice-split-status-20260722.md`
- `docs/development/ics-lpb-manual-20260723.md`
- `docs/development/ics-icspo-department-panel-20260718.md`
- `docs/development/jurnal-pembelian-lpb-po-20260718.md`

## QA Dokumentasi

- Route utama dicocokkan dengan `application/config/routes.php`.
- Mirror route `application/modules/kiupo/routes.php` juga dicek untuk route LPB utama.
- Guard akses LPB Manual dan resolver panel LPB dicocokkan dengan `C_Ics`.
- Rule jurnal LPB dicocokkan dengan dokumen development jurnal dan catatan source posting existing.

## Catatan Lanjutan

Jika kebijakan akun kelompok dagang `4` dan `5` sudah diputuskan, update bagian `Rule Jurnal LPB Saat Ini` pada panduan penggunaan dan dokumen jurnal terkait.
