# Development - ICS ICSPo Purchasing Status Filter

Tanggal: 2026-07-18

## Scope

Route `ics/icspo` pada panel Purchasing memiliki filter cepat berdasarkan kondisi status:

- `Semua`
- `Belum Invoice`
- `Belum Pajak`
- `Belum Uang`

## File yang Berubah

- `application/views/content/logistik/ics/icspo.php`

## Detail Implementasi

Filter ditempatkan di atas tabel Purchasing dan berjalan di sisi client melalui DataTables custom search.

Setiap baris tabel Purchasing membawa atribut status:

- `data-has-invoice`: `1` jika `no_invoice` terisi.
- `data-has-faktur`: `1` jika `kode_faktur_pajak` terisi.
- `data-is-verified`: `1` jika semua detail LPB sudah selesai diverifikasi harga.

Tombol filter memakai kondisi kebalikan dari icon status hijau, sehingga fokusnya adalah pekerjaan yang belum selesai:

- Tombol `Belum Invoice` menampilkan baris yang belum memiliki invoice.
- Tombol `Belum Pajak` menampilkan baris yang belum memiliki faktur pajak.
- Tombol `Belum Uang` menampilkan baris yang belum selesai diverifikasi harga.
- Tombol `Semua` mengembalikan seluruh data.

## Tata Cara Penggunaan

1. Login sebagai user departemen Purchasing.
2. Buka `ics/icspo`.
3. Pada tabel Purchasing, klik `Belum Invoice`, `Belum Pajak`, atau `Belum Uang` untuk memfilter data yang belum selesai berdasarkan status tersebut.
4. Klik `Semua` untuk menghapus filter status.

## Catatan QA

- Filter tidak reload halaman.
- Filter tetap bisa dikombinasikan dengan search DataTables.
- Filter hanya mempengaruhi tabel Purchasing, bukan tabel Logistik.
