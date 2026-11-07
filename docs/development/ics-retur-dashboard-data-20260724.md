# Development Aplikasi - Dashboard Retur ICS

Tanggal: 2026-07-24

## Scope

Perubahan dilakukan untuk route `ics/retur` agar sajian data pada tabel dashboard mengikuti data retur yang benar-benar ada di sistem.

## File Aplikasi

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Ics.php`
- `application/views/content/logistik/ics/dashretur.php`

## Perubahan

1. `C_Ics::dash_retur()` memastikan schema retur pembelian tersedia sebelum dashboard mengambil data.
2. `M_Ics::get_retur_dashboard()` tidak lagi hanya membaca tabel lama `tb_retur_barang`.
3. Dashboard sekarang menggabungkan sumber data:
   - retur pembelian LPB final dari `tb_retur_pembelian`;
   - retur penjualan baru dari `tbrp_retur_penjualan_header` jika ada;
   - data retur legacy ICS dari `tb_retur_barang`.
4. Data dinormalisasi menjadi kolom dashboard yang seragam:
   - tanggal retur;
   - jenis retur;
   - nomor retur;
   - LPB;
   - referensi PO/SPR;
   - supplier/customer;
   - note retur;
   - jumlah item;
   - DPP;
   - PPN;
   - total;
   - status;
   - aksi.
5. View `dashretur.php` menampilkan status baru seperti `POSTED`, `VOID`, `DRAFT`, `SUBMITTED`, dan status retur penjualan baru dengan badge yang sesuai.

## Validasi

- PHP lint:
  - `application/models/M_Ics.php`
  - `application/controllers/logistik/C_Ics.php`
  - `application/views/content/logistik/ics/dashretur.php`
- Route check unauthenticated `https://localhost/karismaerp/ics/retur` mengembalikan HTTP 200 dan diarahkan ke login karena tidak ada sesi aktif.
- Query database lokal menunjukkan data retur pembelian `RBELI/PRPP` tersedia beserta LPB, PO, supplier, DPP, PPN, total, dan status.

## Catatan

UAT visual dashboard setelah login belum dilakukan pada sesi browser terautentikasi. Validasi saat ini memastikan code tidak syntax error, route tidak 500, dan query data retur pembelian lokal tersedia.
