# Penggunaan - ICS Detail PO Konversi Qty dan Draft Temporary

Tanggal: 2026-07-20

## Route

`ics/detail_po`

Contoh:

```text
http://localhost/karismaerp/ics/detail_po?no_po=Q001%2FKIU%2FVII%2F2026B&kd_suplier=AGRIC02
```

## Membaca Tabel Detail Barang PO

Kolom `Qty Order` utama menampilkan qty kecil atau PCS.

Grup `Qty Order` menampilkan breakdown:

- `Pcs`: qty kecil hasil konversi.
- `Box`: `qty * isi`.
- `Kg`: `qty_kecil / (kemasan / 1000)`.
- `Ltr`: `qty_kecil / (kemasan / 1000)`.

Kolom `Qty In` menampilkan total qty yang sudah menjadi LPB final ditambah draft temporary yang masih aktif.
Kolom `Qty Sisa` menampilkan sisa qty kecil/PCS yang belum diterima secara final LPB.

Grup `Qty Diterima` menampilkan breakdown penerimaan final LPB:

- `Qty`
- `Box`
- `Kg`
- `Ltr`

Kolom `Qty` pada grup `Qty Diterima` adalah qty kecil/PCS dan diposisikan sebelum `Box`.

## Input Draft Temporary

1. Klik tombol tambah pada baris barang.
2. Isi qty diterima, no lot, dan expired date.
3. Simpan draft.
4. Baris akan masuk ke tabel `Draft Temporary Penerimaan`.
5. Tabel detail PO akan otomatis memperbarui `Qty In`, `Qty Diterima`, `Qty Sisa`, dan `Status` tanpa reload halaman.

## Hapus Draft Temporary

1. Buka area `Draft Temporary Penerimaan`.
2. Klik tombol hapus pada baris draft.
3. Konfirmasi hapus.

Jika ada draft lama yang sebelumnya gagal dihapus karena `ID draft tidak valid`, buka ulang summary draft. Aplikasi akan menormalisasi ID temporary lama, lalu tombol hapus dapat digunakan kembali.
