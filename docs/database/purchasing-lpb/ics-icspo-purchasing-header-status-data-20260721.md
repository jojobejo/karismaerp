# Database - ICS ICSPo Header Purchasing dan Status Data

Tanggal: 2026-07-21

## Ringkasan

Tidak ada perubahan struktur database untuk update ini.

## Tabel yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_po`
- `tbpo_suplier`
- `tb_lpb_log`

## Kontrak Data

Tabel Purchasing route `ics/icspo` membaca:

- `Tgl LPB` dari `tb_lpb.input_at`
- `No LPB` dari `tb_lpb.nomor_lpb`
- `No PO` dari `tb_lpb.no_po`
- `Nama Supplier` dari join `tbpo_po.kd_suplier` ke `tbpo_suplier.kd_suplier`
- `Grand Total LPB` dari `SUM(tb_lpb_detail.total_harga)` per `id_lpb`
- `LPB Status` dari `tb_lpb.status_lpb`
- Status invoice dari `tb_lpb.no_invoice`
- Status pajak dari `tb_lpb.kode_faktur_pajak`
- Status afirmasi harga dari `tb_lpb_detail.harga_verified_at`

## Mapping Status LPB

- `NULL` atau kosong: `DRAFT`
- `0`: `UNPOST`
- `1`: `POST`

## SQL Migration

Tidak ada SQL migration baru.
