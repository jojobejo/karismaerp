# Development - Tab Panel Route ics/data_lpb (Data PO & Data LPB)

Tanggal: 2026-08-13

## Scope

Route:
- `ics/data_lpb`
- `ics/icspo`

File yang dimodifikasi:
- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/icspo.php`

## Deskripsi Fitur

Halaman `ics/data_lpb` kini menyajikan antarmuka **Tab Panel** terpadu dengan 2 header tab utama:
1. **Data PO** (Monitoring Progress Penerimaan Barang PO):
   - Menampilkan tabel daftar PO yang berstatus `DONE` untuk pemantauan penerimaan barang di gudang.
   - Quick Filter: `Semua`, `Belum` (merah), `Partial` (kuning), `Done` (hijau).
   - Kolom: `No PO`, `Tgl Transaksi`, `Nama Supplier`, `Total Barang Order`, `Total Barang Diterima`, `Progress` (progress bar %, jumlah order vs diterima, teks `Berdasarkan qty diterima`), `Status` (Badge Belum/Partial/Done), `#` (Tombol `Detail`).
   - Pewarnaan baris otomatis:
     - Baris `Partial` berlatar belakang kuning muda (`table-warning`).
     - Baris `Done` berlatar belakang hijau muda (`table-success`).
     - Baris `Belum` berlatar belakang putih standar.
2. **Data LPB** (Daftar Transaksi LPB & Status Berkas):
   - Menampilkan tabel seluruh LPB yang sudah masuk dengan nomor LPB, tanggal LPB, tanggal PO, nomor PO, tanggal & nomor Surat Jalan (SJ), nomor Invoice, dan Faktur Pajak (FP).
   - Quick Filter: `Semua`, `Belum Invoice`, `Belum Pajak`, `Belum Afirmasi Harga`.
   - Kolom: `Tgl LPB`, `NO LPB` (link detail LPB), `Tgl PO`, `No PO`, `Tgl SJ`, `No SJ`, `No Invoice`, `No FP`, `Suplier`, `Grand Total` (Rupiah), `Status Data` (3 icon: Invoice, Faktur Pajak, Afirmasi Harga), `Satatus Barang` (Badge POST/UNPOST, icon transaksi penjualan kasir, icon jurnal neraca).

## Detail Teknis

1. **Controller `C_Ics::data_lpb()`**:
   - Mengaktifkan kedua panel (`show_logistik_panel = TRUE`, `show_purchasing_panel = TRUE`, `lpb_panel_mode = 'both'`).
   - Memuat dataset `lpb` dari `M_Logistik::get_lpb($date1, $date2)` untuk tab **Data PO**.
   - Memuat dataset `lpb_purchasing` dari `M_Logistik::get_lpb_purchasing_view($date1, $date2)` untuk tab **Data LPB**.
   - Menyembunyikan kolom teknis logistik internal (`hide_lpb_supplier_code = TRUE`, `hide_lpb_last_input = TRUE`).

2. **View `icspo.php`**:
   - Header Nav Tabs diatur menjadi:
     - `<i class="fas fa-file-invoice mr-1"></i> Data PO`
     - `<i class="fas fa-clipboard-list mr-1"></i> Data LPB`
   - Ditambahkan listener perpindahan tab Bootstrap DataTables `shown.bs.tab` untuk auto-recalculate lebar kolom agar responsif dan presisi:
     ```javascript
     $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
         $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
     });
     ```
   - Filter toolbar independen untuk kedua tab (`#lpbStatusFilter` dan `#purchasingStatusFilter`).
   - Format tanggal pada kedua tab diseragamkan menjadi `dd/mm/yyyy` (contoh: `2026-07-30` ditampilkan menjadi `30/07/2026`):
     - Data PO: `Tgl Transaksi`
     - Data LPB: `Tgl LPB`, `Tgl PO`, `Tgl SJ`
     - Setiap cell tanggal dilengkapi atribut `data-order="yyyy-mm-dd"` agar pengurutan (sorting) DataTables tetap akurat secara kronologis.

## Cara Penggunaan

1. Buka menu atau akses route `ics/data_lpb`.
2. Klik tab **Data PO** untuk memantau status barang yang dipesan apakah sudah diterima seluruhnya, sebagian (partial), atau belum diterima.
3. Gunakan tombol filter status `Semua`, `Belum`, `Partial`, atau `Done` untuk menyaring data PO dengan cepat.
4. Klik tab **Data LPB** untuk melihat daftar transaksi penerimaan barang LPB, kelengkapan berkas faktur/invoice/pajak, serta status posting dan keterkaitan jurnal penjualan.
5. Gunakan tombol filter status `Semua`, `Belum Invoice`, `Belum Pajak`, `Belum Afirmasi Harga` untuk menyaring transaksi LPB yang belum lengkap administrasinya.
