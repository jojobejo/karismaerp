# Database - ICS Detail PO Header Qty Order dan Qty Diterima

Tanggal: 2026-07-21

## Ringkasan

Tidak ada perubahan struktur database untuk update ini.

## Alasan

Perubahan hanya menyederhanakan tampilan tabel `Detail Barang PO` pada route `ics/detail_po`.

Tidak ada tabel baru, kolom baru, index baru, trigger baru, atau perubahan tipe data.

## Tabel Terkait

Flow tetap membaca dan menggunakan tabel yang sama seperti sebelumnya, antara lain:

- `tbpo_po`
- `tbpo_detail_po`
- `tbpo_suplier`
- `tb_tmp_po_received`
- `tb_lpb`
- `tb_lpb_detail`

## SQL Migration

Tidak ada SQL migration baru.
