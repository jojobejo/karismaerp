# Development - ICS ICSPo Department Panel

Tanggal: 2026-07-18

## Scope

Route `ics/icspo` kini menampilkan panel data sesuai departemen login:

- Departemen/jobdesk Purchasing melihat data `Purchasing` saja.
- Departemen/jobdesk Logistik melihat data `Logistik` saja.
- Role lain yang tidak terdeteksi sebagai Purchasing atau Logistik tetap memakai dua tab seperti sebelumnya.

## File yang Berubah

- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/icspo.php`

## Detail Implementasi

Controller `C_Ics::ics_po()` menambahkan mode panel:

- `purchasing`: hanya mengambil `M_Logistik::get_lpb_purchasing_view()`.
- `logistik`: hanya mengambil `M_Logistik::get_lpb()` atau `M_Logistik::get_lpb_admin_po()`.
- `both`: mempertahankan dua dataset dan dua tab untuk akses umum/admin khusus.

Resolver mode membaca session `departemen`, `jobdesk`, dan `username` agar kompatibel dengan data login lama:

- Purchasing: `departemen` berisi `PURCHASING`, `jobdesk` `ADMINPURCHASING` / `ADMIN PO`, atau username `admpo`.
- Logistik: `departemen` berisi `LOGISTIK`, atau `jobdesk` `LOGISTIK`, `ADMINLOGLPB`, `ADMLPB2`, `ADMINICS`.

View `icspo.php` hanya merender tabel yang diizinkan. Jika hanya satu panel yang tampil, navigasi tab `Logistik` / `Purchasing` tidak dirender sehingga user tidak melihat pilihan panel lain.

## Tata Cara Penggunaan

1. Login sebagai user departemen Purchasing.
2. Buka `ics/icspo`.
3. Sistem langsung menampilkan tabel `Data LPB Purchasing` tanpa tab Logistik/Purchasing.
4. Login sebagai user departemen Logistik.
5. Buka `ics/icspo`.
6. Sistem langsung menampilkan tabel Logistik tanpa tab Purchasing.

## Catatan QA

- Pastikan session user memiliki nilai `departemen` atau `jobdesk` yang benar dari proses login.
- Pastikan fitur pencarian, pagination, dan sorting DataTables tetap aktif pada tabel yang tampil.
- Role selain Purchasing/Logistik masih dapat memakai tampilan dua tab jika memang tidak masuk rule departemen.
