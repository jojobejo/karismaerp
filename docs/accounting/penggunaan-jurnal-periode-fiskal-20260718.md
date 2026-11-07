# Panduan Penggunaan - Periode Fiskal di Jurnal

Tanggal: 2026-07-18

## Lokasi Menu

Buka route:

- `jurnal`

Bagian `Periode Fiskal` sekarang tampil di kolom kanan, tepat di bawah `Daftar Jurnal Penjualan`.

## Membuat Periode Fiskal

1. Isi `Kode Periode`, contoh `2026-07`.
2. Isi `Nama Periode`, contoh `Juli 2026`.
3. Isi `Tanggal Mulai`, contoh `2026-07-01`.
4. Isi `Tanggal Selesai`, contoh `2026-07-31`.
5. Isi `Alasan/approval open`.
6. Klik `Open`.

## Menutup Periode

1. Cari baris periode.
2. Klik `Close`.
3. Isi alasan approval.
4. Sistem akan menolak close jika masih ada kondisi accounting yang belum selesai, seperti jurnal draft atau exception open.

## Membuka Ulang Periode

1. Cari baris periode.
2. Klik `Reopen`.
3. Isi alasan approval.
4. Periode kembali dapat menerima posting jurnal sesuai kontrol service accounting.

## Catatan Operasional

- Auto-jurnal LPB, sales, payment, dan transaksi lain membutuhkan periode fiskal yang mencakup tanggal transaksi dan berstatus `OPEN`.
- Jika jurnal gagal karena periode belum ada, buat periode fiskalnya lebih dulu, lalu retry posting exception terkait.
