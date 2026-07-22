# Development Adjustment Harga LPB

Tanggal: 2026-07-22

## Tujuan

Membuat workflow khusus untuk koreksi harga LPB saat harga invoice berbeda dari harga LPB, sementara barang/lot sudah bergerak sehingga harga LPB asal tidak boleh diedit langsung.

## File MVC

- Controller: `application/controllers/logistik/C_Ics.php`
- Model baru: `application/models/M_LpbPriceAdjustment.php`
- View form/list: `application/views/content/logistik/ics/returform_pembelian_adjustment.php`
- View JavaScript: `application/views/content/logistik/ics/ajax_retur_pembelian_adjustment.php`
- Route utama: `application/config/routes.php`
- Mirror route: `application/modules/kiupo/routes.php`

## Route Baru

- `ics/retur/pembelian/adjustment`
- `ics/retur/pembelian/adjustment/lpb_select2`
- `ics/retur/pembelian/adjustment/lpb_detail`
- `ics/retur/pembelian/adjustment/post`

## Alur Teknis

1. User memilih LPB salah.
2. Sistem memuat seluruh detail LPB salah dan harga aktif dari `tb_lpb_detail`.
3. User mengisi harga invoice benar per detail.
4. Sistem membuat header kontrol `tb_lpb_price_adjustment`.
5. Sistem membuat LPB adjustment dengan nomor LPB asal ditambah `A`.
6. Detail LPB adjustment memakai:
   - no lot `Adj. Harga Beli`;
   - expired date `1000-01-01`;
   - qty sama dengan LPB salah;
   - harga satuan sesuai harga invoice benar.
7. Sistem menambah stok batch dummy dan ledger `LPB_PRICE_ADJUSTMENT_IN`.
8. Sistem posting jurnal LPB adjustment lewat `Accounting_source_service::post_goods_receipt()`.
9. Sistem membuat PRPP otomatis di `tb_retur_pembelian` dengan harga LPB salah.
10. Sistem mengurangi stok batch dummy dan ledger `LPB_PRICE_ADJUSTMENT_PRPP`.
11. Sistem posting jurnal PRPP lewat `Accounting_service::post_retur()`.
12. Sistem memvalidasi stok batch dummy kembali sama dengan saldo sebelum adjustment.

## Catatan Desain

- LPB adjustment menyimpan `kd_po/no_po` asal sebagai referensi supplier dan trace dokumen, tetapi tidak menyentuh sisa PO, draft PO, atau status PO.
- PRPP dibuat langsung `POSTED` karena workflow adjustment harus menghasilkan pasangan LPB adjustment dan PRPP dalam satu aksi.
- Kelompok dagang yang diposting otomatis tetap mengikuti rule existing: `2` BKP dan `3` BKPS.
- LPB asal wajib memiliki supplier dari referensi PO agar PRPP dan jurnal tidak kehilangan identitas supplier.
- Jika harga invoice benar sama dengan harga LPB salah, sistem menolak posting adjustment.
- Jika nomor LPB `A` sudah dipakai, sistem membuat variasi berikutnya seperti `A2`.

## File Existing Yang Disentuh

- `application/controllers/logistik/C_Ics.php`
- `application/config/routes.php`
- `application/modules/kiupo/routes.php`
- `application/views/content/logistik/ics/dashretur.php`
- `application/views/content/logistik/ics/returform_pembelian.php`
