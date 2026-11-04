# Development ICS - Compact Header Draft Temporary Penerimaan

Tanggal: 2026-07-17

## Route `ics/detail_po`

Card `Draft Temporary Penerimaan` dibuat lebih ringkas pada bagian header form.

Field berikut sekarang disusun dalam satu baris pada layar desktop:

- `Nomor SJ`
- `Tanggal SJ`
- `Jenis PO`
- `Gudang`
- `Keterangan`

Perubahan dilakukan dengan layout kolom khusus untuk header draft. Ukuran teks label, input, dan select tetap mengikuti ukuran form normal; yang diperkecil hanya lebar kolom input/select.

Update 2026-07-17:

- Setelah field `Nomor LPB` ditambahkan, lebar kolom header disesuaikan ulang agar total row pas 100% dan tidak menyisakan ruang kosong di kanan.

## File Aplikasi

- `application/views/content/logistik/ics/detail_po.php`

## Cara Penggunaan

1. Buka `ics/detail_po`.
2. Lihat card `Draft Temporary Penerimaan`.
3. Isi `Nomor SJ`, `Tanggal SJ`, `Jenis PO`, `Gudang`, dan `Keterangan` dari satu baris header form.
