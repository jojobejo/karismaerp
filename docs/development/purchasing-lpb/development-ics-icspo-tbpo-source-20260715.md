# Development ICS ICSPo Source tbpo_po

Tanggal: 2026-07-15

## Tujuan

Route `ics/icspo` disesuaikan agar sajian data Purchase Order memakai sumber utama `tbpo_po`. Informasi supplier pada daftar PO diambil dari master `tbpo_suplier`, sesuai arahan bahwa supplier untuk route ini mengikuti data purchasing.

## Perubahan Aplikasi

1. `application/config/routes.php`
   - Route existing tetap dipakai: `ics/icspo` mengarah ke `logistik/C_Ics/ics_po`.

2. `application/controllers/logistik/C_Ics.php`
   - Method `detail_po()` sekarang mengirim `kd_po` dari detail PO pertama ke view.
   - Nilai ini dibutuhkan saat proses draft dan finalisasi LPB dari halaman detail.

3. `application/models/M_Logistik.php`
   - `get_lpb()` sekarang membaca header PO dari `tbpo_po`.
   - `get_lpb_admin_po()` sekarang membaca header PO dari `tbpo_po`.
   - Nama supplier pada daftar PO diambil dari `tbpo_suplier.nama_suplier` berdasarkan `kd_suplier`.
   - Total item memakai `tbpo_po.jml_item` bila tersedia, dengan fallback ke jumlah barang pada `tbpo_detail_po`.
   - Total qty order memakai `tbpo_detail_po.qty_kecil` bila tersedia, dengan fallback ke `tbpo_detail_po.qty`.
   - Detail PO, validasi sisa qty, draft temporary, dan posting LPB disambungkan ke `tbpo_detail_po` supaya tombol Detail tetap bisa dipakai setelah daftar utama pindah ke `tbpo_po`.
   - Update status PO menulis ke `tbpo_po.status` dan tetap menjaga update legacy ke `tb_pre_po` bila tabel tersebut masih ada.

## Cara Pakai

1. Buka menu Logistik lalu pilih `Data PO`, atau akses route `ics/icspo`.
2. Gunakan filter tanggal bila ingin membatasi periode PO.
3. Tabel akan menampilkan PO dari `tbpo_po` dengan nama supplier dari `tbpo_suplier`.
4. Klik tombol Detail pada baris PO untuk membuka detail barang dari `tbpo_detail_po`.
5. Input draft penerimaan seperti biasa, lalu simpan final LPB bila barang sudah diterima.

## Catatan Validasi

- Lint PHP berhasil untuk `application/models/M_Logistik.php`.
- Lint PHP berhasil untuk `application/controllers/logistik/C_Ics.php`.
- Query lokal ke database `kiucoid_karismaerp_local` mengembalikan baris PO dari `tbpo_po` dan nama supplier dari `tbpo_suplier`.
- Query detail lokal untuk PO `Q001/KIU/VII/2026` mengembalikan 3 item dari `tbpo_detail_po`.
