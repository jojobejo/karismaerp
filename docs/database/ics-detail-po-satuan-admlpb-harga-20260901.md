# Database - ICS Detail PO Satuan dan Hak Akses Harga

Tanggal: 2026-09-01

## Ruang Lingkup

- Route: `ics/detail_po`
- Tabel sumber PO: `tbpo_detail_po`, `tbpo_po`, `tbpo_barang`
- Tabel penerimaan: `tb_lpb`, `tb_lpb_detail`, `tb_tmp_po_received`

## Perubahan Database

Tidak ada perubahan struktur database.

## Migrasi

Tidak ada migrasi SQL yang perlu dijalankan.

## Alasan Tidak Ada Schema Change

Koreksi dilakukan pada ekspresi query dan tampilan:

- Nilai `qty_order_box` dan `qty_diterima_box` ditentukan dari satuan baris PO.
- Kolom harga disembunyikan berdasarkan hak akses session dan facility `lpb.view_nominal`.

Semua data acuan yang dibutuhkan sudah tersedia dari kolom dan tabel yang ada.

## Validasi Data

Untuk validasi produksi, cukup bandingkan hasil route `ics/detail_po` dengan baris sumber `tbpo_detail_po` pada PO yang sama:

- Satuan `Btl` tidak boleh muncul sebagai qty box.
- Satuan `Box` tetap boleh menampilkan qty box berdasarkan konversi `isi` atau `qty_kecil / qty`.
- Akun LPB restricted tidak melihat nominal harga satuan.
