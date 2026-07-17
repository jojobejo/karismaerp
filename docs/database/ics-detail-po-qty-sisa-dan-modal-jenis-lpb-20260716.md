# Database ICS - Qty Sisa Detail PO dan Modal Jenis LPB

Tanggal: 2026-07-16

## Ringkasan

Tidak ada perubahan struktur database untuk update ini.

## Alasan

- Kolom `Qty Sisa` pada route `ics/detail_po` memakai data hasil query yang sudah tersedia, yaitu `qty_kecil_sisa`.
- Perubahan modal `Edit Jenis PO / LPB` pada route `ics/detail_record_lpb` hanya mengubah perilaku tampilan saat `nomor_lpb` kosong.
- Proses update jenis LPB tetap memakai endpoint dan kolom yang sudah tersedia dari development sebelumnya.
