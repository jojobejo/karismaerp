# Database - ICS Detail PO Konversi Qty dan Draft Temporary

Tanggal: 2026-07-20

## Scope Database

Perubahan ini tidak menambah tabel dan tidak menambah kolom baru.

Tabel yang dipakai:

- `tbpo_detail_po`
- `tbpo_barang`
- `tb_tmp_po_received`
- `tb_lpb`
- `tb_lpb_detail`

## Kontrak Data Konversi

Konversi qty pada route `ics/detail_po` memakai master `tbpo_barang` berdasarkan:

```sql
tbpo_barang.kode_barang = tbpo_detail_po.kd_barang
```

Kolom acuan:

- `tbpo_barang.isi` untuk konversi `Box`
- `tbpo_barang.kemasan` untuk konversi `Ltr` dan `Kg`
- `tbpo_detail_po.qty` sebagai qty besar/source order
- `tbpo_detail_po.qty_kecil` hanya menjadi fallback bila data master konversi tidak lengkap

## Rumus

- `Box`: `qty_kecil = qty * isi`
- `Ltr`: `qty_kecil = qty / (kemasan / 1000)`
- `Kg`: `qty_kecil = qty / (kemasan / 1000)`
- `Pcs`: `qty_kecil = qty`

## Draft Temporary ID

Pada database lokal ditemukan `tb_tmp_po_received.id_tmp_recieved` bukan auto increment, sehingga insert draft dapat menghasilkan ID `0`. Kondisi ini membuat tombol hapus gagal karena browser membaca ID draft sebagai tidak valid.

Perbaikan aplikasi:

- Baris draft baru diberi `id_tmp_recieved` dari aplikasi dengan pola `MAX(id_tmp_recieved) + 1`.
- Baris draft lama dengan `id_tmp_recieved <= 0` dinormalisasi saat summary/item draft dibuka.

Validasi lokal setelah endpoint summary dipanggil:

```sql
SELECT COUNT(*) AS total,
       SUM(CASE WHEN id_tmp_recieved IS NULL OR id_tmp_recieved <= 0 THEN 1 ELSE 0 END) AS invalid_id
FROM tb_tmp_po_received;
```

Hasil validasi: `invalid_id = 0`.

## Qty In, Qty Sisa, dan Qty Diterima

- `Qty In` berasal dari total `tb_lpb_detail.qty_diterima` yang sudah menjadi LPB final ditambah `tb_tmp_po_received.qty_diterima` yang masih menjadi draft temporary aktif.
- `Qty Sisa` berasal dari perhitungan `tbpo_detail_po.qty` yang dikonversi ke qty kecil dikurangi qty final pada `tb_lpb_detail`.
- `Qty Diterima` berasal dari `tb_lpb_detail.qty_diterima` yang sudah masuk record LPB final.
- Kolom `Qty` pada grup `Qty Diterima` adalah qty kecil/PCS dan diposisikan sebelum `Box`, `Kg`, dan `Ltr`.
- `Qty Sisa` diposisikan sebelum `Status`.

Kontrak ini membuat barang yang sudah mempunyai LPB final tidak lagi tampil `Qty In = 0` hanya karena draft temporary kosong, sementara draft baru tetap langsung terlihat pada `Qty In` sebelum finalisasi.

## Migration

Tidak ada file migration SQL baru untuk perubahan ini. Perubahan bersifat aplikasi dan normalisasi data runtime pada tabel temporary.
