# Development - ICS Detail Record LPB DPP Nilai Lain dan PPN

Tanggal: 2026-07-21

## Scope

Route: `ics/detail_record_lpb`

Perubahan berfokus pada tabel detail LPB di panel Purchasing dan Logistik.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Detail Implementasi

- Menghapus kolom `Pcs` dari group `Qty Satuan`.
- Group `Qty Satuan` sekarang hanya berisi:
  - `BOX`
  - `Kg/Ltr`
- Menambahkan kolom `DPP Nilai Lain` setelah kolom `DPP`.
- Menambahkan kolom `PPN` setelah kolom `DPP Nilai Lain`.
- Urutan kolom harga panel Purchasing menjadi:

```text
Harga Satuan | DPP | DPP Nilai Lain | PPN | Total Harga
```

- `M_Logistik::get_lpb_record_detail_rows()` dan `M_Logistik::get_purchasing_lpb_detail_rows()` sekarang mengirim field tambahan:
  - `dpp_nilai_lain`
  - `ppn`
- Rumus `dpp_nilai_lain`:

```text
dpp * (11 / 12)
```

- Rumus `ppn`:

```text
dpp_nilai_lain * (12 / 100)
```

## Dampak UI

- Mode Logistik tetap tidak menampilkan kolom harga.
- Mode Logistik hanya menampilkan `BOX` dan `Kg/Ltr` pada group `Qty Satuan`.
- Mode Purchasing menampilkan kolom harga lengkap dengan `DPP Nilai Lain` dan `PPN`.
- Jumlah kolom kosong disesuaikan agar pesan `Detail LPB kosong` tetap memenuhi lebar tabel.

## Validasi

- PHP lint:
  - `C:\xampp\php\php.exe -l application/models/M_Logistik.php`
  - `C:\xampp\php\php.exe -l application/views/content/logistik/ics/detail_record_lpb.php`
- `git diff --check`
