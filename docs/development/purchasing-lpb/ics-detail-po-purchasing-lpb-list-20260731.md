# Development - ICS Detail PO Purchasing LPB List

Tanggal: 2026-07-31

## Route Terdampak

- `ics/detail_po`
- `ics/detail_record_lpb`

## Ringkasan

Route `ics/detail_po` sekarang membedakan sajian data berdasarkan role.

1. Admin dan ADMLPB
   - Tampilan lama dipertahankan.
   - Card `Draft Temporary Penerimaan`, modal draft penerimaan, tombol tambah draft, dan proses finalisasi LPB tidak berubah.

2. Purchasing
   - Card `Detail Barang PO` tetap tampil, tetapi kolom `Status`, `Draft Temp`, dan aksi `#` disembunyikan.
   - Card `Draft Temporary Penerimaan` diganti menjadi `List Data LPB Yang Telah Direkam`.
   - List LPB difilter berdasarkan `no_po` dan `kd_po` dari route detail PO.
   - List LPB pada detail PO sekarang di-group berdasarkan `NO LPB`.
   - Kolom `No Invoice` disembunyikan dan diganti kolom `Invoice`.
   - Kolom `Invoice` menampilkan jumlah invoice distinct berdasarkan `NO LPB`.
   - Kolom `Suplier` disembunyikan dari tabel detail PO Purchasing.
   - Header tabel mengikuti kebutuhan Purchasing:
     `Tgl LPB | NO LPB | Tgl PO | No PO | Tgl SJ | No SJ | Invoice | No FP | Grand Total | Status | Status Data | Satatus Barang`.
   - `NO LPB` menjadi link ke `ics/detail_record_lpb` dengan parameter `id_lpb`, sehingga LPB yang diklik langsung diprioritaskan saat halaman detail record terbuka.

## Status dan Filter

- Filter `Status Data`:
  - `Semua`
  - `Belum Invoice`
  - `Belum Pajak`
  - `Belum Afirmasi Harga`
- Filter `Status Barang`:
  - `Semua Status Barang`
  - `Sudah Transaksi`
  - `Sudah Jurnal`
  - `Belum Transaksi/Jurnal`
- Kolom `Status` menampilkan lifecycle LPB:
  - `TERPOSTING` untuk `tb_lpb.status_lpb` nonzero.
  - `UNPOST` untuk `tb_lpb.status_lpb = 0`.
  - `DRAFT` untuk status kosong atau `NULL`.

## File Diubah

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_po.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Catatan Teknis

- Query `M_Logistik::get_lpb_purchasing_view()` diberi parameter opsional `noPo`, `kdPo`, dan `requireDonePo`.
- `M_Logistik::group_lpb_purchasing_records_by_nomor_lpb()` menggabungkan baris LPB untuk detail PO Purchasing menjadi satu baris per `nomor_lpb`.
- Pemanggilan lama dari dashboard/list Purchasing tetap memakai default lama dengan filter PO `DONE`.
- Pemanggilan dari `ics/detail_po` memakai `requireDonePo = FALSE` agar list mengikuti LPB yang sudah direkam untuk nomor PO aktif.
- Status transaksi barang dan status jurnal memakai helper operasional yang sudah ada:
  - `append_lpb_operational_alerts()`
  - `get_lpb_sales_usage_summary_map()`
  - `get_lpb_goods_receipt_journal_summary_map()`
