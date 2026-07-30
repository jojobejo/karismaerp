# Development - ICS ICSPo Filter PO DONE

Tanggal: 2026-07-30

## Scope

Route `ics/icspo` sekarang hanya menyajikan data PO dengan status sumber `tbpo_po.status = DONE`.

Status selain `DONE`, contoh `ACC DIREKTUR`, tidak ditampilkan pada daftar PO/LPB di halaman ini.

## File yang Berubah

- `application/models/M_Logistik.php`

## Detail Implementasi

Filter diterapkan pada query model, bukan di view, agar data selain `DONE` tidak ikut dikirim ke halaman.

Query yang dibatasi:

- `M_Logistik::get_lpb()`
- `M_Logistik::get_lpb_admin_po()`
- `M_Logistik::get_lpb_purchasing_view()`

Kondisi filter memakai:

```sql
UPPER(TRIM(COALESCE(p.status, ''))) = 'DONE'
```

Dengan pendekatan ini, variasi spasi atau huruf kecil/besar pada nilai status tetap dibaca sebagai `DONE`.

## Tata Cara Penggunaan

1. Login ke aplikasi.
2. Buka route `ics/icspo`.
3. Daftar PO/LPB yang tampil hanya berasal dari PO dengan status `DONE`.
4. Jika PO masih berstatus selain `DONE`, selesaikan dahulu status PO pada workflow sumber sebelum membuka LPB dari halaman ini.

## Catatan QA

- Database lokal `kiucoid_karismaerp_local` memiliki status `tbpo_po`: `DONE` dan `ACC DIREKTUR`.
- Filter sengaja diterapkan di model supaya berlaku untuk panel Logistik dan Purchasing.
