# Database - ICS ICSPo Filter PO DONE

Tanggal: 2026-07-30

## Scope

Perubahan ini tidak membutuhkan perubahan struktur database.

## Tabel Terkait

- `tbpo_po`
- `tb_lpb`
- `tb_lpb_detail`

## Kolom Terkait

- `tbpo_po.status`

## Dampak Database

Tidak ada migration, DDL, index, atau perubahan data.

Filter aplikasi membaca status PO dari `tbpo_po.status` dan hanya menampilkan baris dengan nilai `DONE`.

## Validasi Lokal

DB lokal `kiucoid_karismaerp_local` dicek dengan ringkasan status:

```sql
SELECT status, COUNT(*) AS total
FROM tbpo_po
GROUP BY status;
```

Hasil validasi menunjukkan ada PO berstatus `DONE` dan `ACC DIREKTUR`; status selain `DONE` sekarang tidak ikut tampil di route `ics/icspo`.
