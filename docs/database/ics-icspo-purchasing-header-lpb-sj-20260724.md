# Database - ICS ICSPo Header Purchasing LPB/SJ

Tanggal: 2026-07-24

## Ringkasan

Tidak ada perubahan struktur database dan tidak ada SQL migration baru.

## Tabel yang Dibaca

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_po`
- `tbpo_suplier`
- `tb_lpb_log`

## Mapping Data

- `Tgl LPB`: `tb_lpb.input_at`
- `NO LPB`: `tb_lpb.nomor_lpb`
- `Tgl PO`: `tbpo_po.tgl_transaksi`
- `No PO`: `tb_lpb.no_po`
- `Tgl SJ`: `tb_lpb.tgl_sj`
- `No SJ`: `tb_lpb.nosj`
- `No Invoice`: `tb_lpb.no_invoice`
- `No FP`: `tb_lpb.kode_faktur_pajak`
- `Suplier`: `tbpo_suplier.nama_suplier`
- `Grand Total`: `SUM(tb_lpb_detail.total_harga)` per `tb_lpb.id_lpb`
- `Status Data`: indikator invoice, faktur pajak, dan afirmasi harga
- `Satatus Barang`: `tb_lpb.status_lpb` serta indikator sales/jurnal existing

## Catatan

Perubahan ini hanya menata ulang tampilan data Purchasing pada route `ics/icspo`. Data sumber tetap memakai kontrak query existing dari `M_Logistik::get_lpb_purchasing_view()`.

