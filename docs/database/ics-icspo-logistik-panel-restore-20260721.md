# Database - ICS ICSPo Restore Tab Logistik

Tanggal: 2026-07-21

## Ringkasan

Tidak ada perubahan struktur database untuk update ini.

## Alasan

Perubahan hanya mengembalikan tampilan dan sumber data tab Logistik pada route `ics/icspo`.

Tab Logistik kembali memakai query aplikasi `M_Logistik::get_lpb()` yang membaca data PO dan LPB seperti flow sebelumnya.

## SQL Migration

Tidak ada SQL migration baru.
