# Penggunaan - ICS LPB Purchasing Invoice Split Status

Tanggal: 2026-07-22

## Route

- `ics/icspo`
- `ics/detail_record_lpb`
- `ics/detail_po`

## Cara Pakai Purchasing

1. Buka `ics/icspo`.
2. Pada tabel Purchasing, cek kolom `Invoice` untuk melihat nomor invoice LPB.
3. Klik tombol detail pada kolom `#`.
4. Di `ics/detail_record_lpb`, lihat panel `List Invoice LPB` sebelum card `Detail LPB`.
5. Klik salah satu baris invoice untuk membuka detail LPB invoice tersebut.
6. Status LPB tidak tampil sebagai kolom pada list invoice dan badge status setelah title `Detail LPB` disembunyikan.
7. Gunakan tombol `Update Invoice`, `Pecah Invoice`, atau `Update Faktur` sesuai kebutuhan.

## Catatan Proses

- Tombol `Rekam` Purchasing sudah dihilangkan dari detail LPB.
- Update invoice, faktur pajak, dan pecah invoice tidak mengubah status LPB.
- LPB yang dibuat dari draft temporary penerimaan oleh ADMLPB langsung berstatus `POST`.
- Invoice hasil pecah akan tampil pada `List Invoice LPB`.
