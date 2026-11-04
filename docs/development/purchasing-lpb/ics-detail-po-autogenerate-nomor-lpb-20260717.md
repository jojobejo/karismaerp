# Development ICS - Autogenerate Nomor LPB di Detail PO

Tanggal: 2026-07-17

## Route `ics/detail_po`

Card `Draft Temporary Penerimaan` menambahkan field readonly dengan header `Nomor LPB`.

Field `Nomor LPB` melakukan preview auto-generate berdasarkan pilihan `Jenis PO`.

Alur tampilan:

- Saat halaman dibuka, `Nomor LPB` otomatis mengambil nomor dari jenis default `LPB CP`.
- Saat user mengganti `Jenis PO`, field `Nomor LPB` otomatis di-refresh.
- Field `Nomor LPB` bersifat readonly karena nomor final tetap dibuat oleh backend saat LPB disimpan.

## Route AJAX Baru

- `ics/ajax_generate_lpb_number`

Endpoint ini menerima parameter `jenis_lpb` melalui GET dan mengembalikan:

- `status`
- `jenis_lpb`
- `nomor_lpb`

Nomor dibuat memakai `M_Logistik::generate_lpb_number()`, yaitu rules yang sama dengan proses setting jenis LPB sebelumnya.

## File Aplikasi

- `application/config/routes.php`
- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/detail_po.php`

## Cara Penggunaan

1. Buka `ics/detail_po`.
2. Pada card `Draft Temporary Penerimaan`, lihat field `Nomor LPB`.
3. Pilih `Jenis PO`.
4. Field `Nomor LPB` akan menampilkan preview nomor berdasarkan rules jenis PO tersebut.
5. Klik `Simpan` untuk membuat LPB; nomor final tetap disimpan oleh backend.
