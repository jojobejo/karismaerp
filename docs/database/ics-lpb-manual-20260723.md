# Database - LPB Manual Purchasing

Tanggal: 2026-07-23

## Perubahan Struktur

Perubahan ini menambah penanda sumber pada tabel LPB utama dan tabel log sistem khusus LPB Manual.

### `tb_lpb`

Kolom baru:

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `source_type` | `varchar(20) NOT NULL DEFAULT 'PO'` | Pembeda LPB dari PO Logistik dan LPB manual Purchasing. |
| `manual_ref_no` | `varchar(50) NULL` | Nomor referensi manual seperti `LPBM2607230001`. |

### `tb_lpb_manual_log`

Tabel baru untuk log sistem LPB Manual yang disajikan ke modul IT.

Kolom penting:

| Kolom | Keterangan |
| --- | --- |
| `id_lpb` | Relasi ke header `tb_lpb` jika transaksi sudah terbentuk. |
| `manual_ref_no` | Referensi manual. |
| `action_type` | Jenis aksi sistem, misalnya `CREATE_MANUAL_LPB`. |
| `status` | `SUCCESS`, `FAILED`, atau `INFO`. |
| `message` | Pesan ringkas untuk dashboard IT. |
| `payload` | Snapshot JSON header/detail atau validasi. |
| `created_by`, `created_at`, `ip_address`, `user_agent` | Audit teknis. |

## Tabel Existing Yang Dipakai

- `tbpo_barang`: sumber list barang untuk inputer.
- `tb_lpb`: header LPB manual.
- `tb_lpb_detail`: detail item LPB manual.
- `tb_lpb_batch`: batch/lot/expired dari input manual.
- `tberp_stock_batch`: stok on hand per gudang, barang, lot, expired.
- `tberp_stock_ledger`: jejak transaksi stok masuk dengan `ref_type = LPB_MANUAL`.
- `tb_lpb_log`: log aktivitas LPB operasional.

## File Migrasi

SQL manual:

```text
docs/database/ics-lpb-manual-20260723.sql
```

Jalankan di database target sebelum atau sesudah deploy. Kode aplikasi juga memiliki helper `ensure_lpb_manual_schema()` untuk menyiapkan struktur saat modul dibuka, tetapi SQL tetap disediakan untuk deployment terkontrol.

