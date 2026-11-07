# Database - Panduan Penggunaan dan Aturan Per-User LPB Terpadu

Tanggal: 2026-07-23

## Status Perubahan Database

Tidak ada perubahan struktur database pada pekerjaan dokumentasi ini.

Dokumen penggunaan baru hanya merangkum cara pakai dan aturan per-user untuk modul LPB terpadu:

```text
docs/penggunaan-lpb-terpadu-per-user-20260723.md
```

## Tabel yang Disebut dalam Panduan

Panduan mengacu pada tabel existing dan tabel yang sudah terdokumentasi pada pekerjaan sebelumnya:

| Modul | Tabel utama |
| --- | --- |
| LPB dari PO | `tb_lpb`, `tb_lpb_detail`, `tb_lpb_batch`, `tb_lpb_log`, `tbpo_po`, `tbpo_detail_po`, `tbpo_barang`, `tbpo_suplier`, `tberp_stock_batch`, `tberp_stock_ledger` |
| LPB Manual | `tb_lpb`, `tb_lpb_detail`, `tb_lpb_batch`, `tb_lpb_manual_log`, `tb_lpb_log`, `tbpo_barang`, `tberp_stock_batch`, `tberp_stock_ledger` |
| Jurnal LPB | `tbkeu_jurnal`, `tbkeu_jurnal_detail`, `tbkeu_posting_exception`, `tbkeu_akun` |
| Adjustment LPB | `tb_lpb_price_adjustment`, `tb_lpb_price_adjustment_detail`, `tb_lpb`, `tb_lpb_detail`, `tb_retur_pembelian`, `tb_retur_pembelian_detail`, `tbkeu_jurnal`, `tbkeu_jurnal_detail` |
| Retur Pembelian | `tb_retur_pembelian`, `tb_retur_pembelian_detail`, `tb_retur_pembelian_log`, `tb_lpb`, `tb_lpb_detail`, `tb_lpb_batch`, `tberp_stock_batch`, `tberp_stock_ledger`, `tbkeu_jurnal`, `tbkeu_jurnal_detail`, `tbkeu_posting_exception` |

## File SQL Terkait yang Sudah Ada

Tidak ada SQL baru dibuat untuk panduan ini. Bila environment target belum memiliki struktur modul terkait, gunakan file SQL existing berikut sesuai kebutuhan deployment:

- `docs/database/ics-lpb-manual-20260723.sql`
- `docs/database/adjustment-harga-lpb-20260722.sql`
- `docs/database/retur-pembelian-lpb-final-20260722.sql`
- `docs/database/ics-detail-record-lpb-invoice-faktur-20260717.sql`
- `docs/database/ics_lpb_status_lpb_20260716.sql`

## Catatan Validasi Data

- LPB dari PO dan LPB Manual harus memiliki detail barang valid sebelum stok dicatat.
- LPB Manual membutuhkan `source_type = MANUAL` dan `manual_ref_no` untuk membedakan dari LPB PO.
- Jurnal LPB memakai source `LOGISTIK`, source type `LPB_FINAL`, dan posting event `GOODS_RECEIPT`.
- Adjustment LPB memakai lot khusus `Adj. Harga Beli` dan expired `1000-01-01` untuk pasangan koreksi.
- Retur Pembelian posted mengurangi stock batch dan menulis ledger `RBELI`.

## Rekomendasi Kontrol Database

1. Jangan melakukan edit langsung pada tabel transaksi LPB, retur, adjustment, stok batch, ledger, atau jurnal kecuali ada prosedur recovery resmi.
2. Jika posting gagal, prioritaskan pemeriksaan `tbkeu_posting_exception`.
3. Jika ada selisih stok, cocokkan `tb_lpb_batch`, `tberp_stock_batch`, dan `tberp_stock_ledger` berdasarkan barang, gudang, lot, dan expired.
4. Jika ada selisih nilai hutang/PPN, cocokkan `tb_lpb_detail`, `tbkeu_jurnal_detail`, adjustment, dan retur pembelian.
5. Semua koreksi data posted harus meninggalkan dokumen aplikasi: adjustment, retur, reversal, atau void.
