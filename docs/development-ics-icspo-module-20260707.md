# Development Aplikasi - Modul ICS PO / LPB

Tanggal: 2026-07-07

## Ringkasan Eksekutif

Route `ics/icspo` adalah pintu kerja untuk pengendalian PO masuk ke proses LPB (Laporan Penerimaan Barang). Secara bisnis, halaman ini menyatukan tiga kepentingan utama:

- Logistik/LPB melihat daftar PO yang perlu diterima barangnya.
- Operator memilih barang PO, memecah penerimaan berdasarkan lot dan expired date, lalu memfinalkan LPB.
- Admin PO melihat progress penerimaan, record LPB, invoice, history diskon, dan adjustment harga.

Modul ini sudah bergerak dari pola lama import `tb_ics_po` menuju pola PRE PO yang lebih rapi: sumber PO utama berasal dari `tb_pre_po`, penerimaan final disimpan ke `tb_lpb`, `tb_lpb_detail`, dan `tb_lpb_batch`, lalu stok batch/ledger ERP ikut diperbarui bila tabelnya tersedia.

## Route Utama

Route utama:

```php
$route['ics/icspo'] = 'logistik/C_Ics/ics_po';
```

File yang terlibat:

- Controller: `application/controllers/logistik/C_Ics.php`
- Model utama: `application/models/M_Logistik.php`
- Model sync API: `application/models/Api/M_Api.php`
- View utama: `application/views/content/logistik/ics/icspo.php`
- View detail PO: `application/views/content/logistik/ics/detail_po.php`
- View record LPB: `application/views/content/logistik/ics/detail_record_lpb.php`
- View print LPB: `application/views/content/logistik/ics/print_record_lpb.php`

## Cara Kerja `ics_po()`

Method `C_Ics::ics_po()` membaca filter tanggal `date1` dan `date2`, menentukan role user, lalu memilih dataset:

- Jika user adalah `ADMIN PO` atau username `admpo`, data diambil dari `M_Logistik::get_lpb_admin_po()`.
- Selain itu, data diambil dari `M_Logistik::get_lpb()`.
- Tombol sync PO aktif untuk level `1` non-`ADMINICS`, untuk `ADMIN PO`, dan untuk username `admpo`.
- Informasi sync terakhir dan 100 row PRE PO terakhir diambil dari `Api/M_Api`.

## Fitur Yang Ada

### 1. Daftar PO / LPB

Halaman `icspo.php` menampilkan tabel Data LPB atau Data PO Invoice Pending, tergantung role.

Kolom mode LPB:

- No PO
- Tanggal transaksi
- Kode supplier
- Nama supplier
- Total barang order
- Total barang diterima
- Progress
- Input terakhir
- Status
- Tombol detail

Kolom mode Admin PO:

- No PO
- Tanggal transaksi
- Nama supplier
- Progress
- Status
- Tombol record LPB

Status dihitung dari total qty order dibanding total qty diterima:

- `belum`: belum ada penerimaan.
- `partial`: sudah ada penerimaan tetapi belum penuh.
- `done`: total diterima sudah sama atau lebih dari total order.

### 2. Filter Tanggal

Form filter `date1` dan `date2` mengirim POST kembali ke `ics/icspo`. Query model memfilter `tb_pre_po.tgl_transaksi` dengan `STR_TO_DATE(pp.tgl_transaksi, '%d/%m/%Y')` setelah input diformat menjadi `Y-m-d`.

Catatan teknis: dump database menunjukkan `tb_pre_po.tgl_transaksi` bertipe `text`, sehingga filter bergantung pada format tanggal yang konsisten.

### 3. Sync PO dari KIU_PO

Tombol `Sync PO` di view memanggil URL `sync_pre_po_erp` lewat AJAX. Route tersebut menuju API sync dan menyimpan data ke:

- `tb_pre_po`
- `tb_pre_po_diskon_history`
- `tb_pre_po_invoice_adjustment`
- Cache file sync terakhir: `application/cache/pre_po_sync_last.json`

Implementasi terbaru membaca database sumber `kiu_po` langsung dari database lokal `kiucoid_po` melalui `Api/M_Api::sync_pre_po_from_kiu_po()`. Jika database sumber tidak tersedia pada environment tertentu, sistem fallback ke endpoint lama:

```text
http://localhost/kiu_po/get_data_pre_po_erp
```

Data yang diambil dari `kiu_po`:

- Header/detail PO dari `tb_detail_po` dan `tb_po`.
- Tax dari `tb_po.tax`.
- Histori diskon dari `tb_diskon`, dengan nama supplier dari `tb_suplier` bila tabel tersedia.

Sync memakai dedup key `kd_po + kd_barang + satuan`. Jika qty atau harga berubah di `kiu_po`, baris staging `tb_pre_po` diperbarui, bukan dibuat ganda. Jika data sudah final/invoice status `2`, row tidak ditimpa.

### 4. Detail PO Untuk Input Penerimaan

Tombol detail pada mode LPB membuka:

```text
ics/detail_po?no_po=...&kd_suplier=...
```

Data detail diambil dari `M_Logistik::detail_po_received()`, dengan sumber:

- `tb_pre_po` sebagai order PO.
- `tb_master_barang_all` sebagai nama barang dan dimensi.
- `tb_lpb` + `tb_lpb_detail` sebagai penerimaan yang sudah dibuat.

Detail menampilkan qty besar, konversi qty kecil berdasarkan dimensi `p*l*t`, qty diterima, qty sisa, dan status barang.

### 5. Draft Penerimaan Barang

Penerimaan tidak langsung masuk final. Operator menyimpan draft per barang ke `tb_tmp_po_received` lewat endpoint:

- `ics/ajax_get_tmp_po_received_item`
- `ics/ajax_get_tmp_po_received_summary`
- `ics/ajax_save_tmp_po_received`

Validasi utama:

- `kd_po`, `kd_suplier`, dan `kd_barang` wajib ada.
- Setiap baris dengan qty wajib punya satuan.
- Total draft tidak boleh melebihi qty kecil sisa PO.

Draft dapat berisi beberapa baris lot dan expired date untuk satu barang.

### 6. Finalisasi LPB

Endpoint `ics/ajax_finalize_tmp_po_received` memfinalkan draft menjadi LPB.

Header yang wajib:

- No PO
- Kode supplier
- Nomor invoice
- Gudang

Detail yang wajib:

- Minimal satu row draft di `tb_tmp_po_received`.

Saat final berhasil:

- Insert header ke `tb_lpb`.
- Insert detail ke `tb_lpb_detail`.
- Insert batch/lot ke `tb_lpb_batch`.
- Update `tberp_stock_batch` jika tabel tersedia.
- Insert `tberp_stock_ledger` jika tabel tersedia.
- Hapus draft dari `tb_tmp_po_received`.
- Update status semua row `tb_pre_po` untuk `kd_po` tersebut menjadi `2`.

### 7. Record LPB dan Print

Admin PO membuka record LPB lewat:

```text
ics/detail_record_lpb?kd_po=...&no_po=...&kd_suplier=...
```

Endpoint pendukung:

- `ics/ajax_get_lpb_records_by_kd_po`
- `ics/ajax_get_lpb_record_detail`
- `ics/print_lpb_record/{id_lpb}`
- `ics/print_lpb_records_all`

Fungsi ini menampilkan histori penerimaan per `kd_po`, total item, total baris, total qty, gudang, invoice, dan detail barang.

### 8. Invoice dan Adjustment Harga Admin PO

Fitur khusus Admin PO:

- `ics/ajax_get_pre_po_adjustment`
- `ics/ajax_submit_adjustment`
- `ics/ajax_history_adjustment`
- `ics/ajax_history_invoice`
- `ics/ajax_history_diskon`
- `ics/ajax_update_invoice`

Adjustment harga memperbarui `tb_pre_po.hrg_satuan` dan `tb_pre_po.harga_total`, lalu mencatat log ke `tb_pre_po_adjustment_log`. Update invoice memperbarui `tb_lpb` dan mencatat log ke `tb_lpb_log`.

### 9. Import CSV Lama

Masih ada tombol Import CSV untuk kondisi tertentu dan route:

```php
$route['ics/import_csv'] = 'logistik/C_Ics/import_csv_po';
```

Flow ini membaca CSV lalu insert batch ke `tb_ics_po`. Ini adalah jalur lama/legacy untuk data PO ICS, berbeda dari flow utama PRE PO + LPB.

## Hak Akses dan Tampilan

Logika akses yang terlihat:

- `ADMIN PO` atau `admpo`: mode Admin PO, melihat Data PO Invoice Pending, record LPB, invoice, diskon, adjustment.
- Level `1` non-`ADMINICS`: dapat melihat tombol sync dan action LPB sesuai kondisi view.
- Level `2`: disediakan tombol Import CSV dan Retur.
- `ADMINICS`: sebagian action LPB disembunyikan.
- `ADMINLOGLPB`: tombol navigasi tertentu disembunyikan.

## Tata Cara Penggunaan Modul

### Untuk Logistik / LPB

1. Buka menu atau URL `ics/icspo`.
2. Gunakan filter tanggal jika ingin membatasi daftar PO.
3. Klik tombol Detail pada PO yang akan diterima.
4. Pilih barang yang diterima.
5. Isi qty diterima, satuan, no lot, dan expired date.
6. Simpan draft penerimaan.
7. Pastikan ringkasan draft sudah benar.
8. Isi nomor invoice, gudang, nomor surat jalan, tanggal surat jalan, dan keterangan.
9. Finalisasi penerimaan.
10. Sistem membuat record LPB dan memperbarui status PO.

### Untuk Admin PO

1. Login sebagai user dengan jobdesk `ADMIN PO` atau username `admpo`.
2. Buka `ics/icspo`.
3. Cek progress PO: belum, partial, atau done.
4. Klik tombol record untuk melihat LPB per PO.
5. Gunakan fitur history invoice, history diskon, dan adjustment harga jika perlu.
6. Update invoice LPB jika data invoice/surat jalan perlu disesuaikan.
7. Print satu record LPB atau semua record LPB dari halaman record.

## Catatan Risiko Teknis

- Nama kolom `id_tmp_recieved` dan `crete_at` di `tb_tmp_po_received` mengandung typo historis; aplikasi saat ini mengikuti nama tersebut.
- Status `tb_pre_po.status = 2` dipakai sebagai tanda PO sudah diproses/final sehingga sync tidak menimpa row final.
- Perhitungan progress memakai qty kecil `qty * (p*l*t)` dari `tb_master_barang_all`; kualitas data dimensi sangat menentukan akurasi progress.
- Ada dua generasi flow: legacy `tb_ics_po` dan flow baru `tb_pre_po` + `tb_lpb`. Untuk development baru, prioritaskan flow baru kecuali request eksplisit menyebut import CSV lama.

## Validasi Yang Dilakukan

Validasi berbasis pembacaan kode dan dump lokal:

- Route `ics/icspo` ditemukan di `application/config/routes.php`.
- Controller `C_Ics::ics_po()` ditemukan dan ditelusuri ke view `icspo.php`.
- Query utama ditemukan di `M_Logistik::get_lpb()` dan `M_Logistik::get_lpb_admin_po()`.
- Struktur tabel diverifikasi dari `db/kiucoid_karismaerp.sql`.
- Database aktif lokal diverifikasi dari `application/config/database.php`: `kiucoid_karismaerp_local`.
- Database sumber `kiu_po` lokal diverifikasi: `kiucoid_po.tb_detail_po` berisi 53 baris.
- Sebelum sync, `kiucoid_karismaerp_local.tb_pre_po` berisi 0 baris.
- Setelah sync lewat `POST /karismaerp/sync_pre_po_erp`, tersimpan 53 baris PRE PO, 13 baris histori diskon, dan 51 baris invoice adjustment.
- Daftar `ics/icspo` memiliki 24 grup PO dari hasil staging `tb_pre_po`.
- Nilai harga besar seperti `2381280000.0000` sudah tersimpan penuh setelah perubahan presisi numerik `tb_pre_po`.
