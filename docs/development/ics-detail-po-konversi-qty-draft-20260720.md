# Development - ICS Detail PO Konversi Qty dan Draft Temporary

Tanggal: 2026-07-20

## Scope

Route: `ics/detail_po`

Perubahan berfokus pada halaman detail PO ICS untuk:

- Menampilkan `Qty Order` sebagai qty kecil/PCS.
- Menambahkan breakdown konversi `Qty Order` dan `Qty Diterima` ke kolom `Box`, `Kg`, `Ltr`, dan `Pcs`.
- Menggunakan data acuan konversi dari `tbpo_barang` berdasarkan `kode_barang`.
- Memperbaiki hapus draft temporary yang gagal dengan pesan `ID draft tidak valid`.
- Memperbaiki `Qty In` agar menghitung LPB final dan draft temporary aktif.
- Memuat ulang angka detail PO secara AJAX setelah draft temporary berubah, tanpa reload halaman.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/controllers/logistik/C_Ics.php`
- `application/config/routes.php`
- `application/modules/kiupo/routes.php`
- `application/views/content/logistik/ics/detail_po.php`

## Rumus Konversi

Rumus mengikuti pola aplikasi `kiu_po`:

- `Qty Order`: `qty_kecil`
- `Pcs`: `qty_kecil`
- `Box`: `qty * isi`
- `Kg`: `qty_kecil / (kemasan / 1000)`
- `Ltr`: `qty_kecil / (kemasan / 1000)`

`Pcs` diperlakukan sebagai satuan kecil. Nilai `isi` dan `kemasan` diprioritaskan dari `tbpo_barang` melalui `kode_barang`, dengan fallback ke nilai yang tersimpan di `tbpo_detail_po` apabila master belum lengkap.

## Detail Implementasi

- `M_Logistik::detail_po_received()` sekarang menghitung `qty_kecil`, `qty_order_box`, `qty_order_kg`, `qty_order_ltr`, `qty_order_pcs`, `qty_diterima_box`, `qty_diterima_kg`, `qty_diterima_ltr`, dan `qty_diterima_pcs`.
- Normalisasi satuan dibuat toleran untuk alias umum: `Kg/Kilogram`, `Ltr/LT/Liter`, dan `Box`.
- Query detail PO memakai helper konversi yang sama dengan query remaining qty dan query draft temporary, supaya angka display dan validasi tidak berbeda.
- Kolom `Qty In` di halaman detail PO menampilkan total qty final LPB ditambah draft temporary aktif per barang dalam satuan kecil.
- Kolom `Qty Sisa` di halaman detail PO menampilkan sisa qty kecil/PCS dari order dikurangi penerimaan final LPB dan diposisikan sebelum `Status`.
- Grup `Qty Diterima` hanya membaca qty final dari `tb_lpb_detail`; kolom `Qty` pada grup ini adalah qty kecil yang telah masuk dan diposisikan sebelum `Box`.
- View `detail_po.php` diubah mengikuti format kolom: `Qty Order`, grup `Qty Order` (`Pcs`, `Box`, `Kg`, `Ltr`), `Qty In`, grup `Qty Diterima` (`Qty`, `Box`, `Kg`, `Ltr`), `Qty Sisa`, status, draft temp, dan aksi.
- Endpoint `ics/ajax_get_detail_po_rows` ditambahkan untuk mengambil ulang baris detail PO setelah draft temporary disimpan/dihapus/finalisasi, lalu JavaScript memperbarui `Qty In`, `Qty Diterima`, `Qty Sisa`, status, dan data sisa pada tombol draft tanpa page reload.
- Handler delete draft di browser sekarang membaca beberapa kemungkinan field ID: `id_tmp_recieved`, `id_tmp_received`, atau `id`.
- Controller `ajax_delete_tmp_po_received_row()` menerima parameter lama `id_tmp_recieved` dan parameter kompatibel `id_tmp_received`/`id`.
- Route `ics/ajax_get_detail_po_rows` dan `ics/ajax_delete_tmp_po_received_row` didaftarkan pada route utama dan route module `kiupo`.
- `replace_tmp_po_received_item()` sekarang mengisi `id_tmp_recieved` untuk baris draft baru karena tabel lokal tidak memakai auto increment.
- Fetch draft summary/item akan menormalisasi baris lama dengan `id_tmp_recieved <= 0` agar tombol hapus memiliki ID valid.

## Validasi

- PHP lint:
  - `C:\xampp\php\php.exe -l application/models/M_Logistik.php`
  - `C:\xampp\php\php.exe -l application/controllers/logistik/C_Ics.php`
  - `C:\xampp\php\php.exe -l application/views/content/logistik/ics/detail_po.php`
- `git diff --check` untuk tiga file di atas.
- Endpoint lokal `http://localhost/karismaerp/ics/detail_po?no_po=Q001%2FKIU%2FVII%2F2026B&kd_suplier=AGRIC02` mengembalikan HTTP 200.
- Endpoint AJAX summary draft mengembalikan ID draft valid untuk baris yang sebelumnya `0`.
- Endpoint delete draft dengan ID tidak ditemukan mengembalikan JSON terkendali, bukan Ajax transport error.
