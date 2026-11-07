# Database - Supervisi Opname Scan dan Input Manual

Tanggal: 2026-07-04

## Ringkasan

Perubahan ini tidak menambahkan tabel atau kolom baru. Panel supervisor hanya membaca data QR dan data manual stockopname untuk kebutuhan pengecekan.

## Tabel Terkait

- `stockopname_master_item`
  - Sumber data master untuk scan QR dan pilihan expired date.
  - Lookup scan membaca data master berdasarkan nilai QR/barcode/master id yang sudah tersedia.
- `stockopname_opname`
  - Tidak ditulis oleh panel cek supervisor.
  - Tetap menjadi tabel hasil opname untuk flow input operator yang sudah ada.
- `stockopname_master_manual_item`
  - Tetap digunakan untuk flow `Opname Request`.
  - Tidak menjadi tujuan utama input manual supervisor pada perubahan ini.
- `tb_wilayah`
  - Digunakan untuk menampilkan nama wilayah pada header input.

## Kontrak Baca Data

Panel supervisor membaca data dengan kontrak berikut:

- Scan QR membaca master melalui `M_Stockopname::find_master_barang_for_opname()`.
- Input manual mencari barang melalui `M_Stockopname::manual_barang_options()`.
- Pilihan expired date manual membaca data melalui `M_Stockopname::manual_expired_options()`.
- Data yang ditampilkan meliputi `kode_barang`, `nama_barang`, `expired_date`, dan `dimensi`.

## Dampak Database

- Tidak ada migration baru.
- Tidak ada perubahan schema.
- Tidak ada perubahan enum status.
- Tidak ada backfill data.
- Tidak ada insert/update/delete dari panel cek supervisor.

## Validasi Operasional

Untuk memastikan fungsi berjalan, scan QR atau pilih barang manual dari halaman `supervisi-opname`. Jika data master tersedia, panel menampilkan identitas barang tanpa membuat baris baru pada tabel hasil opname.
