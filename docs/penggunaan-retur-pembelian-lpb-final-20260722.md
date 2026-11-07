# Alur Penggunaan Retur Pembelian LPB Final

Tanggal: 2026-07-22

## Membuat Draft

1. Buka menu `ics/retur/pembelian`.
2. Cari supplier, nomor PO, atau nomor LPB.
3. Pilih LPB final.
4. Sistem menampilkan item LPB, lot, expired, qty LPB, retur sebelumnya, stok fisik, dan harga.
5. Isi `Qty Retur` pada item yang akan dikembalikan.
6. Isi alasan item jika diperlukan.
7. Pilih jenis penyelesaian.
8. Isi alasan retur.
9. Klik `Buat Draft Retur`.

## Approval

1. Klik `Submit` pada draft.
2. Purchasing klik `Purchasing` setelah mengecek supplier, harga, alasan, dan penyelesaian.
3. Accounting klik `Accounting` setelah mengecek dampak Hutang Usaha dan PPN.
4. Setelah status `ACCOUNTING_VERIFIED`, klik `Post`.

## Setelah Posting

Posting akan:

- mengurangi stok fisik batch;
- menulis kartu stok `RBELI`;
- membuat jurnal `PURCHASE_RETURN`;
- mengunci dokumen menjadi `POSTED`.

## Void Dokumen Posted

Jika retur yang sudah diposting harus dibatalkan:

1. Klik `Void`.
2. Isi alasan void.
3. Sistem membuat jurnal reversal dan ledger pembalik.
4. Status dokumen menjadi `VOID`.

Data posted tidak dihapus agar audit trail tetap lengkap.
