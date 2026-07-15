# Development Aplikasi - Supervisi Opname Scan dan Input Manual

Tanggal: 2026-07-04

## Ringkasan

Module `supervisi-opname` sekarang menyediakan panel cek QR dan cek data manual untuk jobdesk supervisor opname. Panel berada langsung di halaman supervisi dan hanya muncul setelah tombol `Scan` atau `Input Manual` diklik.

## Perubahan Aplikasi

- Membuka akses guard controller untuk jobdesk supervisor opname pada handler cek data:
  - `ajax_input_lookup`
  - `ajax_manual_barang`
  - `ajax_manual_expired`
- Menormalisasi variasi jobdesk supervisor opname:
  - `SUPERVISIOR_OPNAME`
  - `SUPERVISOR_OPNAME`
  - `SUPERVISOR-OPNAME`
- Menambahkan tombol toggle pada halaman `supervisi-opname`:
  - `Scan` menampilkan panel cek QR.
  - `Input Manual` menampilkan panel cek data manual.
- Panel cek diletakkan sebelum card `Stockopname Result`.
- Panel scan/manual dalam kondisi tersembunyi saat halaman pertama dibuka.
- Tidak ada form qty dan tidak ada tombol simpan pada panel supervisor.

## Cara Penggunaan

1. Login dengan user jobdesk supervisor opname.
2. Sistem mengarahkan user ke `supervisi-opname`.
3. Klik `Scan` untuk menampilkan panel cek QR.
4. Klik `Buka Scan`, lalu arahkan kamera ke QR stockopname.
5. Jika QR valid, sistem menampilkan nama barang, kode barang, expired date, dan dimensi.
6. Klik `Input Manual` untuk menampilkan panel cek data manual.
7. Pilih `Nama Barang`, lalu pilih `Expired Date`.
8. Sistem menampilkan data manual yang dipilih tanpa menyimpan hasil opname.

## Catatan Teknis

- Flow scan memakai endpoint `admin/stockopname/input/lookup`.
- Flow manual memakai endpoint `admin/stockopname/input/manual/barang` dan `admin/stockopname/input/manual/expired`.
- Supervisor tidak diberi akses simpan dari panel ini karena kebutuhan module hanya cek QR dan cek data manual.
