# Development ICS - Hide Kolom Harga Draft Temporary Penerimaan

Tanggal: 2026-07-16
Route: `ics/detail_po`

## Ringkasan

Pada card `Draft Temporary Penerimaan`, kolom `Harga Satuan` dan `Total Harga` disembunyikan dari tabel tampilan.

## Catatan Teknis

Perubahan hanya dilakukan pada view tabel draft temporary penerimaan.

Proses rekam dan simpan data tidak diubah. Data `harga_satuan`, `harga_satuan_kecil`, dan `total_harga` tetap diproses oleh alur simpan yang sudah ada.

## File Aplikasi

- `application/views/content/logistik/ics/detail_po.php`
