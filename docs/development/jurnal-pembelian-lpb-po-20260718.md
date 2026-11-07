# Development Jurnal Pembelian LPB PO

Tanggal: 2026-07-18

## Tujuan

Menambahkan jurnal pembelian untuk flow LPB dan PO pada route `jurnal`, dengan tahap awal rule akun masih hardcode agar tim accounting dapat memvalidasi tampilan dan proses posting.

## Scope Aplikasi

File yang diubah:

- `application/libraries/Accounting_service.php`
- `application/libraries/Accounting_source_service.php`
- `application/models/M_Keuangan.php`
- `application/controllers/keuangan/C_Keuangan.php`
- `application/config/routes.php`
- `application/views/content/keuangan/jurnal.php`
- `application/views/content/keuangan/ajax/ajax_jurnal.php`

## Rule Hardcode

Sumber kelompok barang dibaca dari `tbpo_barang.kelompok_dagang`. Jika data lama menyimpan angka pada `kelompok_barang`, query masih bisa membaca fallback tersebut.

Mapping tahap validasi:

| Kelompok dagang | Nama | Jurnal |
| --- | --- | --- |
| 2 | Barang Dagangan BKP | Debit 14010, Debit 13017, Kredit 21098 |
| 3 | Barang Dagangan BKPS | Debit 14011, Kredit 21098 |
| 4 | Barang Dagangan | Belum diposting hardcode |
| 5 | Barang Promosi | Belum diposting hardcode |

PPN masukan BKP sementara di-hardcode 11% dari nilai DPP LPB. Nilai DPP dibaca dari `tb_lpb_detail.total_harga`, fallback ke `qty_diterima * harga_satuan` atau harga PO exclude.

## Alur Posting

1. LPB final dibuat melalui flow `ics/detail_po`.
2. `M_Logistik::create_lpb_from_tmp()` menghasilkan `tb_lpb` dan `tb_lpb_detail`.
3. Setelah commit LPB sukses, `C_Ics` memanggil `Accounting_source_service::post_goods_receipt($idLpb)`.
4. Adapter accounting hanya melanjutkan posting jika `tb_lpb.nomor_lpb` sudah terisi.
5. Jurnal dibuat sebagai jenis `PJ` dengan `source_module=LOGISTIK`, `source_type=LPB_FINAL`, dan `posting_event=GOODS_RECEIPT`.

## Tampilan Route Jurnal

Pada route `jurnal` / `keuangan/jurnal` ditambahkan panel `Daftar Jurnal Pembelian` sebelum `Daftar Jurnal Penjualan`.

Endpoint AJAX baru:

- `jurnal/purchase-list`
- `jurnal/purchase-detail`
- `keuangan/jurnal/purchase-list`
- `keuangan/jurnal/purchase-detail`

Detail jurnal memakai modal yang sama dengan daftar jurnal penjualan, tetapi baris pembelian menampilkan:

- nomor dokumen LPB;
- kode rekening display, misalnya `140-10`;
- nama akun;
- debit;
- kredit.

## Catatan Teknis

`Accounting_service::post_auto()` kini tetap membangun mapping otomatis lama jika `lines` kosong, tetapi menerima `journal_type` dan `lines` custom jika adapter sumber mengirimkannya. Ini dipakai untuk hardcode LPB pembelian tanpa mengubah mapping global `tbkeu_mapping_akun`.

Kelompok dagang selain 2 dan 3 akan masuk posting exception, bukan diposting dengan akun tebakan. Ini menjaga akurasi laporan sampai rule kelompok 4 dan 5 ditentukan.
