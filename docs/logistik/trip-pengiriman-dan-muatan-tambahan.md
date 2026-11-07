# Trip Pengiriman dan Muatan Tambahan

## Tujuan

Fitur Trip Pengiriman mengikat Sales Order, faktur, aktivitas loading, kendaraan, dan Delivery Order ke satu keberangkatan. Fitur ini memungkinkan truk yang masih mempunyai ruang menerima SO baru tanpa mengubah DO yang sudah diterbitkan.

## Alur penggunaan

1. Sales membuat SO dan menentukan rute seperti biasa.
2. Saat **Siap Loading** diklik, sistem membuat Trip Pengiriman atau memakai trip rute yang sedang menunggu tambahan muatan.
3. Logistik mengisi rencana pengiriman. Tanggal, driver, dan nomor lambung disinkronkan ke trip.
4. Checker memproses barang dan menyelesaikan loading.
5. Sistem membuat DO pertama untuk faktur trip tersebut.
6. Jika kapasitas masih tersedia, Checker atau Admin Logistik memilih **Buka Tambahan Muatan**.
7. Sales membuat SO baru pada rute yang sama dan menekan **Siap Loading**. SO ditandai sebagai muatan tambahan serta masuk ke trip yang sama.
8. Faktur dan loading tambahan diproses seperti biasa. Selama DO awal masih berstatus **On Delivery**, detail faktur tambahan digabung ke DO awal.
9. Setelah kendaraan siap berangkat, pilih **Tutup Trip / Berangkat**.

## Aturan bisnis

- Rute saja tidak digunakan sebagai identitas keberangkatan; identitas utamanya adalah `id_trip`.
- Satu trip dapat memiliki beberapa SO, faktur, sesi loading, dan DO.
- Activity Warehouse tetap memakai satu baris rute untuk trip yang sama. Saat ada tambahan, baris tersebut ditampilkan sebagai **Muatan Tambahan**, bukan membuat rute kedua.
- Muatan tambahan otomatis mewarisi tanggal pengiriman, jenis pengiriman, driver, dan kendaraan dari SO awal; plan tidak perlu diisi kembali.
- Satu trip menggunakan satu DO selama DO tersebut masih berstatus **On Delivery**. Sistem hanya membuat DO baru jika trip belum mempunyai DO aktif.
- Saat Checker membuka tambahan muatan, status DO berubah menjadi **Menunggu Tambahan Muatan** sehingga belum dianggap On Delivery. Setelah tambahan selesai digabung atau trip ditutup untuk berangkat, status kembali menjadi **On Delivery**.
- Trip berstatus `DITUTUP`, `BERANGKAT`, atau `SELESAI` tidak dapat menerima SO tambahan.
- SO tambahan ditolak jika jumlah aktual dimuat dan alokasi yang belum diproses melebihi kapasitas tonase atau kubikasi trip.
- Kapasitas aktual hanya menghitung detail dengan `checker_loaded = 1`. Detail belum diproses dihitung sebagai alokasi kapasitas.

## Database

Migrasi berada di `db/migrations/20261003_delivery_trip.sql`. Migrasi membuat `tb_delivery_trip`, menambah relasi `id_trip`, menambah penanda muatan/DO tambahan, serta melakukan backfill aktivitas loading aktif.

Sebelum menjalankan migrasi pada database lain, buat backup database. Migrasi ini dijalankan satu kali per database.

## Hak akses

Tombol membuka dan menutup tambahan muatan tersedia untuk Checker, Manager Checker, dan Admin Logistik. Sales dapat melihat notifikasi trip tambahan pada halaman SO per Rute dan cukup mengikuti alur Siap Loading yang sudah ada.
