# Penggunaan - Pecah LPB Multiple Invoice

Tanggal: 2026-07-21

## Lokasi

Buka:

```text
ics/detail_record_lpb
```

Pilih LPB dari data PO Purchasing.

## Syarat

- User harus ADMIN PO/Purchasing.
- LPB harus berstatus `UNPOST`.
- Detail LPB sudah tersedia.

## Langkah Penggunaan

1. Buka detail LPB dari menu Data PO Purchasing.
2. Pastikan LPB yang dipilih berstatus `UNPOST`.
3. Klik tombol `Pecah Invoice`.
4. Isi minimal 2 nomor invoice dan tanggal invoice.
5. Atur qty barang pada matrix split invoice.
6. Pastikan kolom selisih bernilai `0`.
7. Klik `Simpan Split Invoice`.

## Contoh

LPB `00123`, barang `abacel`, qty awal `100`.

Input:

- Invoice `01`, qty `50`
- Invoice `02`, qty `50`

Hasil:

- LPB asal tetap memakai nomor LPB `00123` dengan invoice `01`, qty `50`.
- Sistem membuat LPB baru dengan nomor LPB `00123`, invoice `02`, qty `50`.

## Setelah Split

- Kedua LPB berada pada status `UNPOST`.
- Purchasing dapat update faktur pajak masing-masing invoice jika diperlukan.
- Setelah data benar, lakukan `Rekam` seperti alur LPB Purchasing yang sudah ada.

