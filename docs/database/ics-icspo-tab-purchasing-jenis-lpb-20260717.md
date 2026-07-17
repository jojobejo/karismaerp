# Database ICS - Tab Purchasing dan Jenis LPB Saat Finalisasi

Tanggal: 2026-07-17

## Ringkasan

Tidak ada perubahan struktur database untuk development ini.

## Tabel yang Digunakan

- `tb_lpb`
  - Membaca `kd_po`, `no_po`, `nomor_lpb`, `jenis_lpb`, `nosj`, `tgl_sj`, `no_invoice`, dan `input_at`.
  - Menyimpan `jenis_lpb` dan `nomor_lpb` saat LPB dibuat dari route `ics/detail_po`.
- `tb_lpb_detail`
  - Menghitung progress verifikasi harga dari `harga_verified_at`.
- `tb_lpb_log`
  - Mengambil tanggal invoice dari `dilakukan_pada` berdasarkan `kd_po` dan `no_invoice`.
- `tbpo_po`
  - Mengambil kode supplier berdasarkan `kd_po`.
- `tbpo_suplier`
  - Mengambil nama supplier.

## Catatan Kolom

Kolom `jenis_lpb`, `nomor_lpb`, dan kolom verifikasi harga LPB sudah berasal dari migration development sebelumnya.

Tidak ada kolom resmi untuk `tgl_faktur` pada `tb_lpb`. Karena itu panel Purchasing menampilkan nilai `-` untuk `tgl_faktur` sampai ada keputusan database baru untuk menyimpan tanggal faktur.

## SQL Migration

Tidak ada SQL migration baru.
