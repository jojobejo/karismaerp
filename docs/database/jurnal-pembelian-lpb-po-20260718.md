# Database Jurnal Pembelian LPB PO

Tanggal: 2026-07-18

## Status

Tidak ada perubahan struktur database pada development ini.

Tidak ada migration SQL baru yang wajib dijalankan.

## Tabel Yang Dibaca

Flow posting pembelian membaca tabel berikut:

| Tabel | Fungsi |
| --- | --- |
| `tb_lpb` | Header LPB final, termasuk `nomor_lpb`, `tgl_sj`, `no_po`, `kd_po`, dan gudang |
| `tb_lpb_detail` | Detail barang dan nilai LPB |
| `tbpo_detail_po` | Fallback harga PO exclude jika nilai LPB belum lengkap |
| `tbpo_barang` | Rule kelompok dagang barang |
| `tbpo_po` | Relasi supplier PO |
| `tbpo_suplier` | Nama supplier untuk judul jurnal pembelian |
| `tbkeu_akun` | Validasi akun hardcode 14010, 13017, 14011, 21098 |
| `tbkeu_jurnal` | Header jurnal pembelian yang diposting |
| `tbkeu_jurnal_detail` | Detail debit/kredit jurnal pembelian |
| `tbkeu_posting_exception` | Catatan gagal posting jika data belum memenuhi rule |

## Rule Data

Jurnal pembelian hanya diposting jika:

1. `tb_lpb.nomor_lpb` sudah terisi.
2. Detail LPB memiliki nilai perolehan.
3. Kelompok barang termasuk rule hardcode awal:
   - `2` BKP;
   - `3` BKPS.
4. Akun `14010`, `13017`, `14011`, dan `21098` tersedia sebagai akun `POSTING`, aktif, dan eligible transaksi.

## Dampak Data

Saat LPB final berhasil diproses, aplikasi dapat menambah:

- 1 baris `tbkeu_jurnal` dengan `journal_type`/jenis jurnal `PJ`;
- 2 sampai 4 baris `tbkeu_jurnal_detail`, tergantung kelompok barang pada LPB;
- 1 baris `tbkeu_posting_exception` jika validasi gagal.

Idempotency key tetap:

```text
GOODS_RECEIPT-LPB-{id_lpb}
```

Artinya retry pada LPB yang sama tidak membuat jurnal ganda.

## Tidak Diubah

- Tidak menambah kolom pada `tb_lpb`.
- Tidak menambah kolom pada `tb_lpb_detail`.
- Tidak mengubah tabel master akun.
- Tidak mengubah isi `tbkeu_mapping_akun`.
