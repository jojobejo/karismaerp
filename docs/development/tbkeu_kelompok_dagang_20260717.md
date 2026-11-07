# Development tbkeu_kelompok_dagang

Tanggal: 2026-07-17

## Ringkasan

Dibuat master tabel database baru bernama `tbkeu_kelompok_dagang` berdasarkan struktur pada screenshot dari user. Tabel ini kemudian dipakai sebagai sumber dropdown `Kelompok Dagang` pada route `master_barang` dan alias `purchase/listBarang`.

## File yang Ditambahkan

- `docs/database/tbkeu_kelompok_dagang_20260717.sql`
- `docs/database/tbkeu_kelompok_dagang_20260717.md`
- `docs/development/tbkeu_kelompok_dagang_20260717.md`
- `docs/penggunaan-tbkeu-kelompok-dagang-20260717.md`

## Keputusan Teknis

1. Nama kolom dibuat persis seperti screenshot agar kompatibel dengan sumber data: `NOINDEX`, `DESKRIPSI`, `KODESALES`, dan seterusnya.
2. `NOINDEX` dijadikan primary key tanpa auto increment karena data contoh berisi nilai `0` dan urutan yang tidak kontinu.
3. Kode akun disimpan sebagai `varchar(20)` agar aman untuk kode numerik yang perlu diperlakukan sebagai label/kode, bukan angka hitung.
4. Flag `INVENTORI`, `BELI`, dan `JUAL` disimpan sebagai `char(1)` karena nilai contoh memakai `T`.
5. Tabel memakai `InnoDB`, `utf8mb4`, dan `utf8mb4_general_ci`, mengikuti pola tabel `tbkeu_*` yang sudah ada.
6. Seed data dibuat idempotent dengan `ON DUPLICATE KEY UPDATE`, sehingga SQL dapat dijalankan ulang tanpa menggandakan baris.

## Validasi

Validasi yang perlu dilakukan setelah migration:

```sql
SHOW CREATE TABLE `tbkeu_kelompok_dagang`;
SELECT * FROM `tbkeu_kelompok_dagang` ORDER BY `NOINDEX`;
```

## Catatan Lanjutan

Jika nanti field `Kelompok Dagang` di master barang perlu memakai data ini sebagai dropdown, langkah berikutnya adalah menambahkan model accessor, endpoint/list option, dan mengganti input bebas menjadi pilihan database-backed.
Field `Kelompok Dagang` pada master barang sudah memakai tabel ini sebagai dropdown. Nilai yang tersimpan di `tbpo_barang.kelompok_dagang` adalah `NOINDEX`, sedangkan label yang ditampilkan ke user adalah `DESKRIPSI`.
