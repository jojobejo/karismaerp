# Dokumentasi Teknis: Penambahan Master Gudang Baru (tb_gudang)

## Tanggal Pelaksanaan
31 Oktober 2026

## Deskripsi
Sinkronisasi dan penambahan daftar master gudang pada tabel `tb_gudang` sesuai dengan daftar gudang acuan operasional sistem. Gudang yang sudah terdaftar sebelumnya tetap dipertahankan, sedangkan gudang baru yang belum ada ditambahkan ke dalam database.

## Analisis Data

### Gudang yang Sudah Ada Sebelumnya (Dipertahankan)
1. `Gdg. Induk` (ID: 2)
2. `Gdg. Rusak` (ID: 5)
3. `Gdg. Expired` (ID: 6)
4. `Gdg. Titipan` (ID: 10)
*(Termasuk gudang sistem pendukung lainnya: Gdg. Bundling, Gdg. Konsiyasi, Gdg. Titipan di Kios, Gdg. Konsinyasi Lunas, Gdg. Test Market di Kios)*

### Gudang Baru yang Ditambahkan
Sesuai dengan daftar kode referensi:
1. `Gdg. Ajung` (Ref: C)
2. `Gdg. Ajung 148` (Ref: O)
3. `Gdg. Belakang1` (Ref: I)
4. `Gdg. Belakang2` (Ref: J)
5. `Gdg. Belakang3` (Ref: N)
6. `Gdg. IT` (Ref: R)
7. `Gdg. Imam Bonjol` (Ref: G)
8. `Gdg. Petugas MIA` (Ref: H)
9. `Gdg. Sample` (Ref: Q)
10. `Gdg. Supplier` (Ref: E)
11. `Gudang Titipan Direksi` (Ref: K)
12. `Gudang Titipan Kanvas` (Ref: P)
13. `Gudang Titipan Purchasing` (Ref: L)
14. `Gudang Titipan Sales` (Ref: M)

## Skrip Migrasi
File migrasi tersimpan di:
- `docs/database/20261031_tambah_master_gudang.sql`

Database yang telah dimutakhirkan:
- `kiucoid_karismaerp_local`
- `kiucoid_karismaerp_admkeu`

