# Dokumentasi Database Stockopname

Periode: 2026-07-04 sampai 2026-07-05  
Zona acuan: Asia/Jakarta

## Ringkasan

Dokumen ini hanya mencatat sisi database. Catatan penggunaan dan development aplikasi dipisahkan pada dokumen lain.

## Database 2026-07-04

### Fix Collation Lookup Dimensi

Masalah yang ditangani:

```text
Illegal mix of collations (utf8mb4_general_ci,IMPLICIT) and (utf8mb4_uca1400_ai_ci,IMPLICIT) for operation '='
```

Tabel/kolom yang dibandingkan:

- `tb_master_barang_all.kd_barang`
- `stockopname_master_item.kode_barang`

Solusi aplikasi:

- Query lookup dimensi memakai `COLLATE utf8mb4_general_ci` secara eksplisit.
- Tidak ada tabel atau kolom baru khusus untuk fix ini.

Rekomendasi database jangka panjang:

```sql
ALTER TABLE `stockopname_master_item`
  MODIFY `kode_barang` VARCHAR(50)
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `tb_master_barang_all`
  MODIFY `kd_barang` VARCHAR(25)
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
```

Sebelum menjalankan ALTER, cek struktur aktual:

```sql
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
       CHARACTER_SET_NAME, COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('stockopname_master_item', 'tb_master_barang_all')
  AND COLUMN_NAME IN ('kode_barang', 'kd_barang');
```

### Supervisi Scan dan Manual

Perubahan 2026-07-04 pada panel supervisor tidak membutuhkan schema baru.

Tabel yang dibaca:

- `stockopname_master_item`
- `tb_wilayah`
- `stockopname_master_manual_item`

Tabel hasil opname tidak ditulis oleh panel cek supervisor:

- `stockopname_opname`

## Database 2026-07-05

Status: perubahan SQL masih berada pada file lokal yang belum commit.

File SQL yang berubah:

- `database/sql/stockopname_detail_input_enhancement.sql`
- `database/sql/stockopname_manual_tables.sql`

### Perubahan Tabel Manual Input

Nama tabel manual input disesuaikan menjadi:

```sql
stockopname_manual_input
```

Sebelumnya referensi file SQL memakai:

```sql
stockopname_opname_manual
```

Struktur utama `stockopname_manual_input`:

| Kolom | Fungsi |
| --- | --- |
| `id` | Primary key |
| `manual_master_id` | Relasi ke `stockopname_master_manual_item.id` |
| `source_id` | Relasi sumber master opname |
| `kode_barang` | Kode barang |
| `nama_barang` | Nama barang |
| `expired_date` | Expired date |
| `no_lot` | Nomor lot |
| `qty`, `qty_pcs`, `qty_box` | Qty input manual |
| `input_by`, `input_at` | User dan waktu input |
| `wilayah`, `tim_opname` | Wilayah dan tim opname |
| `input_source` | Sumber input, default `manual_input` |

Index pada `stockopname_manual_input`:

- `idx_manual_opname_master`
- `idx_manual_opname_source`
- `idx_manual_opname_barang`
- `idx_manual_opname_source_type`

### Perubahan Kolom Input Source

Pada `database/sql/stockopname_detail_input_enhancement.sql`, alter terbaru:

```sql
ALTER TABLE `stockopname_manual_input`
  MODIFY `input_source` VARCHAR(30) NOT NULL DEFAULT 'manual_input';
```

Pada aplikasi, `stockopname_opname` juga diproteksi oleh method model agar memiliki kolom:

```sql
input_source VARCHAR(50) NULL DEFAULT NULL
```

Nilai yang digunakan aplikasi:

- `scan_qrcode`
- `manual_input`
- `manual opname request`
- `master data request opname`
- `adjustment`
- `repost`
- `system`

### Tabel Master Manual Item

Tabel:

```sql
stockopname_master_manual_item
```

Fungsi:

- Menyimpan request item user.
- Menyimpan data master manual item.
- Menyimpan status proses afirmasi.

Kolom penting:

- `source_id`
- `kode_barang`
- `nama_barang`
- `expired_date`
- `no_lot`
- `dimensi`
- `qty`, `qty_pcs`, `qty_box`
- `status`
- `requested_by`, `requested_at`
- `wilayah`, `tim_opname`
- `reviewed_by`, `reviewed_at`, `review_note`

Nilai status yang didukung:

- `PENDING`
- `APPROVED`
- `REJECTED`
- `ADDED`
- `DONE`
- `Manual Input`
- `Request Master Item`

### Tabel Pending

Tabel:

```sql
stockopname_pending
```

Fungsi:

- Menyimpan barang pending yang perlu memengaruhi stock buku opname.
- Sinkron ke `stockopname_master_item` memakai kunci `nama_barang + expired_date`.

Kolom penting:

- `id`
- `kd_do`
- `kode_barang`
- `nama_barang`
- `expired_date`
- `no_lot`
- `qty`, `qty_pcs`, `qty_box`
- `created_by`, `updated_by`
- `created_at`, `updated_at`

Kolom marker pada `stockopname_master_item`:

- `pending_qty`
- `pending_qty_pcs`
- `pending_qty_box`

Mode hitung pending:

- `add`: `qty baru = qty dasar + total pending`
- `subtract`: `qty baru = qty dasar - total pending`

Catatan teknis:

- Mode hitung pending disimpan di session aplikasi, bukan di tabel database.
- Saat mode berubah, aplikasi menjalankan resync seluruh pending.
- Marker pending dapat bernilai negatif pada mode `subtract` agar base qty tetap dapat dihitung ulang.

### Tabel Recycle dan Log Input

Tabel audit/recycle dari enhancement detail input:

- `stockopname_recyclebin_input`
- `stockopname_opname_log`

Fungsi:

- `stockopname_recyclebin_input` menyimpan input opname yang dihapus agar bisa direpost.
- `stockopname_opname_log` mencatat update, delete, repost, adjustment, dan perubahan qty.

Kolom qty audit yang tersedia:

- `before_qty`, `after_qty`
- `before_qty_box`, `after_qty_box`
- `before_qty_pcs`, `after_qty_pcs`

## Urutan Eksekusi SQL Yang Disarankan

1. Backup database.
2. Jalankan `database/sql/stockopname_barang_pending.sql` bila struktur pending belum siap.
3. Jalankan `database/sql/stockopname_manual_tables.sql` untuk tabel manual/request.
4. Jalankan `database/sql/stockopname_detail_input_enhancement.sql` untuk recycle, log, dan penyesuaian `input_source`.
5. Cek struktur kolom kode barang bila masih ada error collation.

## Catatan Kompatibilitas

- Beberapa guard di `M_Stockopname` membuat atau menambah kolom otomatis saat modul dipakai.
- Walaupun ada guard aplikasi, SQL tetap sebaiknya dijalankan di environment target agar struktur database konsisten.
- Jangan rename tabel lama tanpa backup. Jika database sudah terlanjur memiliki `stockopname_opname_manual`, migrasikan datanya ke `stockopname_manual_input` sebelum menghapus tabel lama.

