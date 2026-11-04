# Development Retur Pembelian Potong Hutang

Tanggal: 20 Agustus 2026

## Ruang Lingkup

Pengembangan ini menambahkan rules jurnal retur pembelian untuk route `ics/retur/pembelian` khusus jenis penyelesaian `POTONG_HUTANG`, sinkronisasi ke pembayaran supplier, dan menu daftar jurnal tambahan pada route `keuangan/pembelian`.

## Rules Jurnal Retur Pembelian

Rules berlaku saat retur pembelian sudah `ACCOUNTING_VERIFIED` dan diposting.

Kode rule `RBELI-PH-BKPS` untuk barang BKPS / kelompok dagang `3`:

- Debit `13013` Piutang Non Dagang Retur Pembelian Belum Dipotong.
- Kredit `14011` Persediaan Brg Dagangan BKPS.

Kode rule `RBELI-PH-BKP` untuk barang BKP / kelompok dagang `2`:

- Debit `13013` Piutang Non Dagang Retur Pembelian Belum Dipotong.
- Kredit `14010` Persediaan # 1.
- Kredit `13017` PPN Masukan / PPN M Ymh Diterima.

Dengan rules ini, retur tidak langsung mengurangi akun hutang usaha. Retur dicatat terlebih dahulu sebagai saldo `13013`, lalu dipakai pada proses potong hutang supplier.
Jika satu retur berisi BKP dan BKPS, baris debit `13013` dipisahkan per kode rule agar audit akun dapat dibaca per jenis barang.

## Sinkronisasi Pembayaran Supplier

Module pembayaran supplier ditambahkan alur `Potong Hutang Retur`:

1. Sistem membaca dokumen retur supplier yang masih memiliki saldo `13013`.
2. User memilih dokumen hutang terbuka yang akan dipotong.
3. User memilih dokumen retur yang dipakai sebagai sumber potongan.
4. Sistem memvalidasi total hutang yang dipotong harus sama dengan total retur yang dipakai.
5. Sistem membuat jurnal:
   - Debit akun hutang dokumen terbuka.
   - Kredit `13013`.
6. Sistem menyimpan header transaksi ke `tbkeu_pembayaran` dengan `source_type = SUPPLIER_RETURN_DEDUCTION`.
7. Sistem menyimpan jejak alokasi ke `tbkeu_pembayaran_alokasi`.

## File Yang Diubah

- `application/models/M_ReturPembelian.php`
  - Rules akun retur pembelian `POTONG_HUTANG` diganti dari debit `21098` menjadi debit `13013`.

- `application/libraries/Accounting_service.php`
  - Menambahkan `create_supplier_return_deduction()` untuk posting jurnal dan penyimpanan pembayaran/alokasi dalam satu transaksi database.

- `application/models/M_PembayaranSupplier.php`
  - Menambahkan daftar retur siap potong berbasis saldo `13013`.
  - Menambahkan validasi dan posting potong hutang retur.

- `application/controllers/keuangan/C_PembayaranSupplier.php`
  - Menambahkan halaman form potong hutang retur.
  - Menambahkan handler posting potong hutang retur.

- `application/views/content/keuangan/pembayaran_supplier/detail.php`
  - Menampilkan card dokumen retur siap potong.

- `application/views/content/keuangan/pembayaran_supplier/form_potong_retur.php`
  - Form alokasi hutang terbuka dan retur pembelian.

- `application/controllers/keuangan/C_Keuangan.php`
  - Menambahkan halaman dan endpoint daftar jurnal retur pembelian.
  - Menambahkan halaman dan endpoint daftar jurnal pelunasan utang perusahaan.

- `application/models/M_Journal.php`
  - Menambahkan query daftar jurnal retur pembelian dan pelunasan utang supplier.

- `application/views/content/keuangan/menu_pembelian.php`
  - Menambahkan card `Daftar Jurnal Retur`.
  - Menambahkan card `Daftar Jurnal Pelunasan Utang Perusahaan`.

- `application/views/content/keuangan/jurnal_pembelian_related.php`
  - View reusable untuk daftar jurnal pembelian terkait.

- `application/config/routes.php`
  - Menambahkan route potong hutang retur dan route daftar jurnal baru.

## Route Baru

- `keuangan/pembayaran-supplier/potong-retur/(:num)`
- `keuangan/pembayaran-supplier/post-potong-retur`
- `jurnal/retur-pembelian`
- `jurnal/pelunasan-utang`
- `jurnal/purchase-return-list`
- `jurnal/supplier-payment-list`
- `jurnal/general-detail`
- Alias `keuangan/jurnal/*` untuk route jurnal yang sama.

## Catatan Audit

- Jurnal retur pembelian memakai nomor retur sebagai `nomor_dokumen` pada akun `13013`.
- Jurnal potong hutang memakai nomor dokumen hutang pada baris debit hutang dan nomor retur pada baris kredit `13013`.
- Outstanding hutang supplier tetap dihitung dari akun bertipe kontrol `HUTANG` atau akun historis `21098`.
- Saldo retur siap potong dihitung dari `13013` dengan rumus debit dikurangi kredit.
