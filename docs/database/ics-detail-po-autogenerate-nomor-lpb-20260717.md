# Database ICS - Autogenerate Nomor LPB di Detail PO

Tanggal: 2026-07-17

## Ringkasan

Tidak ada perubahan struktur database untuk update ini.

## Tabel yang Digunakan

- `tb_lpb`
  - Dibaca oleh generator nomor untuk mencari nomor terakhir berdasarkan `jenis_lpb`.
  - Tetap menjadi tempat penyimpanan final `jenis_lpb` dan `nomor_lpb` saat LPB dibuat.

## Catatan

Field `Nomor LPB` di route `ics/detail_po` hanya preview. Nomor final tetap dibuat ulang saat proses simpan agar mengikuti data terakhir di `tb_lpb`.

## SQL Migration

Tidak ada SQL migration baru.
