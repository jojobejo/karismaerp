# Development - ICS Detail Record LPB Invoice by Nomor LPB

Tanggal: 2026-07-31

## Route Terdampak

- `ics/detail_record_lpb?kd_po=...`
- `ics/ajax_get_lpb_records_by_kd_po`

## Ringkasan

Card `List Invoice LPB` pada mode Purchasing sekarang menyajikan data invoice berdasarkan `nomor_lpb` yang sudah terekam di `tb_lpb`.

Sebelumnya card mengambil daftar yang sama dengan panel daftar LPB, yaitu seluruh header LPB untuk `kd_po` terkait. Dengan perubahan ini, endpoint tetap mengirim `rows` untuk kebutuhan detail/panel LPB, tetapi juga menambahkan `invoice_rows` khusus untuk card invoice.

## Detail Implementasi

1. `M_Logistik::get_lpb_records_by_kd_po()`
   - Ditambahkan parameter opsional `$recordedNomorLpbOnly`.
   - Jika aktif, query hanya mengambil header LPB dengan `nomor_lpb` tidak kosong.
   - Urutan data invoice dibuat berdasarkan `nomor_lpb`, waktu input, lalu `id_lpb`.
   - Ditambahkan parameter opsional `$selectedIdLpb`.
   - Jika route/endpoint menerima `id_lpb`, sistem mencari `nomor_lpb` dari header tersebut, lalu membatasi hasil ke semua header dengan `nomor_lpb` yang sama. Jika nomor LPB belum ada, fallback dibatasi ke `id_lpb` tersebut.

2. `M_Logistik::get_lpb_invoice_records_by_kd_po()`
   - Wrapper khusus untuk mengambil list invoice berdasarkan nomor LPB terekam.
   - Meneruskan scope `id_lpb` agar card invoice dari klik LPB tertentu hanya menampilkan nomor LPB tersebut.

3. `C_Ics::ajax_get_lpb_records_by_kd_po()`
   - Response JSON sekarang berisi:
     - `rows`: daftar LPB penuh untuk detail existing.
     - `invoice_rows`: daftar untuk card `List Invoice LPB per Nomor LPB`.
   - Membaca parameter GET `id_lpb` dari halaman `detail_record_lpb` dan meneruskannya ke model.

4. `detail_record_lpb.php`
   - Card Purchasing memakai `invoice_rows`.
   - Label card menjadi `List Invoice LPB per Nomor LPB`.
   - Badge ringkasan menampilkan jumlah invoice aktual dan jumlah nomor LPB.
   - Empty state diperjelas menjadi belum ada nomor LPB terekam.
   - Menyimpan `initial_id_lpb` sebagai `scopedIdLpb` sehingga daftar tetap terfilter pada nomor LPB yang diklik dari `ics/detail_po`.

## File Diubah

- `application/models/M_Logistik.php`
- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Catatan Teknis

- Tidak mengubah proses update invoice, update faktur, pecah invoice, POST/UNPOST, atau verifikasi harga.
- LPB tanpa `nomor_lpb` tetap tersedia untuk flow detail existing melalui `rows`, tetapi tidak ditampilkan pada card invoice.
- Invoice hasil split yang memakai `nomor_lpb` sama tetap tampil sebagai baris invoice tersendiri di bawah nomor LPB tersebut.
- Contoh: jika pengguna klik `72600003` dari `ics/detail_po`, halaman `ics/detail_record_lpb` hanya memuat baris dengan `nomor_lpb = 72600003`; invoice lain seperti `72600001`, `72600002`, atau `72600002A` tidak ikut tampil.
