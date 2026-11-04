# Development - ICS Detail Record LPB Faktur Card Visible

Tanggal: 2026-07-21

## Scope

Route: `ics/detail_record_lpb`

Perubahan ini mengembalikan tampilan card informasi faktur pajak pada header detail LPB.

## File Aplikasi

- `application/views/content/logistik/ics/detail_record_lpb.php`

## Detail Implementasi

- Menampilkan kembali card `Faktur Pajak`.
- Menampilkan kembali card `Tanggal Terbit Faktur`.
- Data diambil dari payload header LPB yang sudah tersedia:
  - `kode_faktur_pajak`
  - `tanggal_faktur_pajak`
- Tidak ada perubahan pada modal update faktur pajak.
- Tidak ada perubahan pada endpoint `ics/ajax_update_faktur_pajak`.

## Dampak UI

Saat user memilih LPB di route `ics/detail_record_lpb`, header detail kembali menampilkan:

```text
Faktur Pajak
Tanggal Terbit Faktur
```

Jika data belum diisi, nilai tampil sebagai `-`.
