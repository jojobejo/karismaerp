# Development ICS - Tab Purchasing dan Jenis LPB Saat Finalisasi

Tanggal: 2026-07-17

## Route `ics/icspo`

Halaman Data PO sekarang memiliki tab panel:

- `Logistik`: berisi dataview default yang sebelumnya sudah berjalan.
- `Purchasing`: berisi daftar LPB untuk kebutuhan invoice dan verifikasi harga.

Header tab `Purchasing`:

- `No PO`
- `No LPB`
- `Jenis PO`
- `Nomor SJ`
- `Tanggal SJ`
- `Nama Suplier`
- `Progress`
- `Invoice`
- `Tanggal Invoice`
- `tgl_faktur`
- `#`

Data Purchasing diambil dari `tb_lpb`, diperkaya dengan `tbpo_po`, `tbpo_suplier`, dan ringkasan `tb_lpb_detail`.

Catatan data:

- `No LPB` memakai `tb_lpb.nomor_lpb`; fallback ke `LPB-{id_lpb}` bila nomor belum terbentuk.
- `Jenis PO` memakai `tb_lpb.jenis_lpb`.
- `Progress` menghitung jumlah detail LPB yang sudah diverifikasi harga (`harga_verified_at`) dibanding total detail LPB.
- `Tanggal Invoice` memakai log invoice terbaru dari `tb_lpb_log.dilakukan_pada`; bila log tidak tersedia, fallback ke tanggal `tb_lpb.input_at`.
- `tgl_faktur` ditampilkan `-` karena belum ada kolom sumber yang valid di tabel LPB saat development ini dibuat.

## Route `ics/detail_record_lpb`

Tombol `Setting Jenis PO` di header `Detail LPB` dihilangkan. Modal dan endpoint lama tidak dihapus agar kompatibilitas backend tetap aman bila masih ada caller lama.

## Route `ics/detail_po`

Form header draft penerimaan menambahkan field `Jenis PO` sejajar dengan field `Gudang`.

Pilihan `Jenis PO` menggunakan sumber yang sama dengan tombol `Setting Jenis PO` lama, yaitu mapping `M_Logistik::get_lpb_type_options()`:

- `LPB CP`
- `LPB Benih`
- `LPB Konsinyasi`
- `LPB Barang Non Pajak (A)`
- `LPB Promosi`
- `LPB Barang Pengganti Retur (RA)`

Saat LPB disimpan dari route `ics/detail_po`, nilai `jenis_lpb` ikut dikirim ke `ics/ajax_finalize_tmp_po_received`, lalu dipakai oleh `M_Logistik::create_lpb_from_tmp()` untuk menyimpan `tb_lpb.jenis_lpb` dan membentuk `tb_lpb.nomor_lpb`.

## File Aplikasi

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/icspo.php`
- `application/views/content/logistik/ics/detail_po.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Cara Penggunaan

1. Buka `ics/icspo`.
2. Gunakan tab `Logistik` untuk alur default penerimaan barang.
3. Gunakan tab `Purchasing` untuk melihat LPB, jenis PO, invoice, dan progress verifikasi harga.
4. Buka `ics/detail_po` dari tab Logistik, input draft penerimaan, pilih `Jenis PO`, pilih `Gudang`, lalu klik `Simpan`.
5. Buka `ics/detail_record_lpb` untuk update invoice atau verifikasi harga tanpa tombol setting jenis PO.
