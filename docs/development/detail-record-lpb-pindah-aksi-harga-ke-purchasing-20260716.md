# Development Detail Record LPB - Pindah Aksi Harga ke Purchasing

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

Tombol update harga detail LPB dipindahkan dari view `Data LPB` ke view `Purchasing`.

## Perubahan

- View `Data LPB` tidak lagi menampilkan kolom `#`.
- Icon uang rupiah hijau tidak lagi tampil pada tabel `Data LPB`.
- View `Purchasing` sekarang menampilkan kolom `#`.
- Tombol icon uang rupiah hijau pada view `Purchasing` membuka modal update harga detail LPB.

## Catatan Teknis

Query purchasing menambahkan `id_detail_lpb`, `harga_satuan`, dan `total_harga` agar tombol update harga tetap memakai detail LPB yang tepat.

Tidak ada perubahan pada proses rekam atau finalisasi draft temporary penerimaan.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`
