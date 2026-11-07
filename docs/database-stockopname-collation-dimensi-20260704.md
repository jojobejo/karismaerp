# Database - Collation Kode Barang Stockopname

Tanggal: 2026-07-04

## Masalah

Error yang muncul:

`Illegal mix of collations (utf8mb4_general_ci,IMPLICIT) and (utf8mb4_uca1400_ai_ci,IMPLICIT) for operation '='`

Perbandingan yang memicu error:

- `tb_master_barang_all.kd_barang`
- `stockopname_master_item.kode_barang`

Keduanya dibandingkan untuk mengambil `dimensi` master barang.

## Dampak

Jika collation kedua kolom berbeda, MySQL/MariaDB dapat menolak operasi `=` pada query agregasi master stockopname. Dampaknya halaman yang memuat opsi master/pending bisa gagal sebelum data tampil.

## Fix Aplikasi

Query aplikasi sekarang menambahkan collation eksplisit:

```sql
mba.kd_barang COLLATE utf8mb4_general_ci = stockopname_master_item.kode_barang COLLATE utf8mb4_general_ci
```

Dengan ini, perbandingan string berjalan dengan aturan collation yang sama walaupun metadata kolom berbeda.

## Rekomendasi Database Jangka Panjang

Samakan collation kolom kode barang yang sering dibandingkan. Contoh:

```sql
ALTER TABLE `stockopname_master_item`
  MODIFY `kode_barang` VARCHAR(50)
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

ALTER TABLE `tb_master_barang_all`
  MODIFY `kd_barang` VARCHAR(25)
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
```

Sebelum menjalankan ALTER di server produksi, cek tipe dan nullability aktual:

```sql
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
       CHARACTER_SET_NAME, COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('stockopname_master_item', 'tb_master_barang_all')
  AND COLUMN_NAME IN ('kode_barang', 'kd_barang');
```

Sesuaikan `VARCHAR`, `NULL/NOT NULL`, dan default value dengan hasil metadata aktual agar tidak merusak struktur yang sedang dipakai.
