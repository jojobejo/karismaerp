# Penggunaan - Split Detail LPB Flexible Harga

Tanggal: 2026-08-05

Route: `ics/detail_record_lpb`

## Cara Pakai

1. Buka `ics/detail_record_lpb` dari detail PO/LPB.
2. Pilih LPB yang statusnya `UNPOST`.
3. Klik tombol split dengan icon cabang pada baris barang.
4. Isi komposisi qty pada baris `Data Sekarang` dan baris split tambahan.
5. Pastikan `Total Input` qty sama dengan `Qty In Awal` dan `Selisih` qty menjadi `0`.
6. Isi harga satuan sesuai kebutuhan. Total harga boleh berbeda dari total awal.
7. Isi `Keterangan` jika ada alasan bisnis yang perlu dicatat.
8. Klik `Simpan Split`.
9. Setelah berhasil, baris yang terlibat split akan tampil dengan badge `Split`.
10. Buka `Log Aktivitas` untuk meninjau data awal, hasil split, dan selisih harga.

## Catatan

- Qty tidak boleh lebih atau kurang dari data acuan.
- Harga satuan fleksibel selama tidak minus.
- Perubahan total harga akibat split tercatat untuk tinjauan Admin dan Purchasing.
