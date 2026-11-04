# Database - ICS Detail Record LPB DPP Nilai Lain dan PPN

Tanggal: 2026-07-21

## Scope Database

Perubahan ini tidak menambah tabel, tidak menambah kolom, dan tidak membutuhkan migration SQL.

## Tabel yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_detail_po`
- `tbpo_po`
- `tb_master_barang_all`
- tabel hasil helper `po_barang_conversion_join()`

## Kontrak Data

- `dpp` tetap dihitung dari qty LPB dikali harga satuan kecil exclude.
- Harga satuan kecil exclude diprioritaskan dari:
  1. `tbpo_detail_po.harga_satuan_kecil_exclude`
  2. `tbpo_detail_po.harga_satuan_exclude`
  3. `tb_lpb_detail.harga_satuan`
- `dpp_nilai_lain` adalah field kalkulasi query, bukan kolom database.
- Rumus `dpp_nilai_lain`:

```text
dpp * (11 / 12)
```

- `ppn` adalah field kalkulasi query, bukan kolom database.
- Rumus `ppn`:

```text
dpp_nilai_lain * (12 / 100)
```

## Migration

Tidak ada migration SQL baru.

## Persistensi

Tidak ada perubahan persistensi pada:

- `tb_lpb.status_lpb`
- `tb_lpb_detail.harga_satuan`
- `tb_lpb_detail.total_harga`
- metadata update harga
- metadata verifikasi harga
