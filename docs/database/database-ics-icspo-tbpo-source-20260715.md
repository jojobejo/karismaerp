# Database ICS ICSPo Source tbpo_po

Tanggal: 2026-07-15

## Tujuan

Dokumen ini mencatat perubahan sumber data route `ics/icspo`. Tidak ada migrasi struktur database pada perubahan ini; perubahan berada pada query aplikasi.

## Tabel Yang Dipakai

1. `tbpo_po`
   - Menjadi sumber utama header PO untuk daftar `ics/icspo`.
   - Kolom yang dipakai antara lain `kd_po`, `no_po`, `tgl_transaksi`, `kd_suplier`, `jml_item`, dan `status`.

2. `tbpo_suplier`
   - Menjadi sumber nama supplier.
   - Relasi: `tbpo_suplier.kd_suplier = tbpo_po.kd_suplier`.

3. `tbpo_detail_po`
   - Menjadi sumber detail barang PO.
   - Dipakai untuk total qty order, halaman Detail PO, validasi sisa qty, dan draft penerimaan.
   - Relasi utama: `tbpo_detail_po.kd_po = tbpo_po.kd_po`.

4. `tb_lpb` dan `tb_lpb_detail`
   - Tetap menjadi sumber data penerimaan yang sudah diposting.
   - Dipakai untuk menghitung qty diterima, progress, status `belum`, `partial`, atau `done`.

5. `tb_pre_po`
   - Tidak lagi menjadi sumber daftar dan detail untuk route `ics/icspo`.
   - Masih dijaga sebagai fallback update status legacy karena beberapa fungsi lama masih memakai nama method `update_pre_po_status_by_kd_po()`.

## Dampak Schema

Tidak ada perubahan schema.

Tidak ada tabel baru, tidak ada kolom baru, dan tidak ada index baru yang wajib ditambahkan untuk perubahan ini.

## Query Inti

Daftar PO memakai pola:

```sql
SELECT
    p.kd_po,
    p.no_po,
    p.tgl_transaksi,
    p.kd_suplier,
    s.nama_suplier
FROM tbpo_po p
LEFT JOIN tbpo_suplier s
    ON s.kd_suplier = p.kd_suplier;
```

Detail barang memakai pola:

```sql
SELECT
    d.kd_po,
    d.no_po,
    d.kd_suplier,
    d.kd_barang,
    d.nama_barang,
    d.satuan,
    d.qty,
    d.qty_kecil
FROM tbpo_detail_po d;
```

## Catatan Validasi Lokal

Database aktif: `kiucoid_karismaerp_local`.

Hasil cek lokal menunjukkan tabel berikut tersedia:

- `tbpo_po`
- `tbpo_detail_po`
- `tbpo_suplier`

Query daftar PO lokal berhasil menampilkan PO dengan supplier dari `tbpo_suplier`.
