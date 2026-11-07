# Development Aplikasi - LPB Print, Detail PO, dan Urutan Laporan

Tanggal: 2026-08-07

## Scope

Perubahan dilakukan pada modul ICS/LPB KarismaERP untuk route:

- `ics/print_lpb_record`
- `ics/print_lpb_records_all`
- `ics/detail_po`
- `ics/ajax_get_detail_po_rows`
- `ics/lpb_report`

Project `kiu_po` discan sebagai acuan alur harga Include/Exclude PPN dan konversi barang PO. Implementasi di KarismaERP tetap memakai tabel `tbpo_*` yang sudah tersinkron di database KarismaERP.

## Perubahan Aplikasi

1. Print LPB
   - Template `print_record_lpb.php` menampilkan:
     - Nama Checker
     - Nama Inputer
     - Nama Purchasing
     - Waktu Checker
   - `print_lpb_records_all()` memakai header lengkap per LPB agar field audit sama dengan print single record.

2. Detail PO
   - Query `detail_po_received()` menambahkan:
     - `harga_satuan_include`
     - `harga_satuan_exclude`
     - `keterangan_harga_ppn`
   - Harga exclude diprioritaskan dari `tbpo_detail_po.harga_satuan_kecil_exclude`, lalu `harga_satuan_exclude`, lalu `hrg_satuan`.
   - Harga include mengikuti mode tersimpan `keterangan_harga_ppn`; jika mode exclude, nilai include dihitung dari exclude + tax PO.
   - Konversi Kg dan Ltr dipisahkan berdasarkan satuan barang yang terekam di PO, bukan digabung menjadi satu angka yang sama.
   - Nilai Kg/Ltr mengikuti kontrak detail PO KarismaERP: `qty_kecil / (kemasan / 1000)`.
   - View `detail_po.php` menambahkan kolom:
     - Qty Order: Box, Kg, Ltr
     - Harga Satuan: Include, Exclude
     - Qty Diterima: Box, Kg, Ltr, Qty Kecil
   - AJAX refresh `ics/ajax_get_detail_po_rows` ikut mengupdate harga include/exclude dan Qty Ltr.

3. Laporan LPB
   - Urutan header sajian tabel pada `ics/lpb_report` diubah menjadi:
     - Data LPB & Surat Jalan
     - Data Purchase Order (PO)
     - Invoice & Pembayaran
     - Data Supplier
     - Data Barang, Konversi & Batch
     - Finansial, Diskon, Pajak & Hutang
     - Faktur Pajak
     - Lead Time & Aging
   - Urutan kolom JavaScript DataTables disejajarkan dengan header baru agar data tidak bergeser.

## Catatan Teknis

- Sumber nama checker berasal dari `tb_lpb.checker_name`.
- Sumber nama inputer diambil dari `tb_lpb_log.dilakukan_oleh` untuk action `CREATE_LPB` / `CREATE_LPB_MANUAL`, dengan fallback ke `checker_by`.
- Sumber nama purchasing diambil dari `tb_lpb_detail.harga_verified_by` yang menandai verifikasi harga Purchasing.
- Perubahan tidak mengubah alur simpan LPB, tidak mengubah status lifecycle LPB, dan tidak menggabungkan edit harga dengan verifikasi.

## Verifikasi

- `php -l application/models/M_Logistik.php`
- `php -l application/controllers/logistik/C_Ics.php`
- `php -l application/views/content/logistik/ics/detail_po.php`
- `php -l application/views/content/logistik/ics/print_record_lpb.php`
- `php -l application/views/content/logistik/ics/lpb_report.php`

Semua file lolos syntax check.
