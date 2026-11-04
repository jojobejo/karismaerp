# Development Aplikasi - Edit Identitas Master Barang Stockopname

Tanggal: 2026-07-03

## Modul

- Route: `admin/stockopname/master_barang`
- Controller: `application/controllers/admin/C_Stockopname.php`
- Model: `application/models/admin/M_Stockopname.php`
- View: `application/views/content/admin/stockopname_master_barang_catalog.php`

## Perubahan

- Modal edit pada halaman Master Barang sekarang membuka field `Kode barang` dan `Nama barang` agar dapat diubah oleh admin.
- Endpoint update `admin/stockopname/master_barang/update` sekarang menerima `kd_barang`, `nama_barang`, `p`, `l`, dan `t`.
- Validasi update disamakan dengan create:
  - ID master barang wajib valid.
  - Kode barang wajib diisi dan maksimal 25 karakter.
  - Nama barang wajib diisi.
  - Panjang, lebar, dan tinggi wajib bilangan bulat lebih dari 0.
- Model menolak perubahan kode barang jika `kd_barang` sudah digunakan oleh master barang lain.
- Pesan sukses update diubah menjadi `Master barang berhasil diperbarui.` karena update tidak lagi hanya untuk dimensi.

## Tata Cara Penggunaan

1. Buka menu `admin/stockopname/master_barang`.
2. Pada tabel `Data Master Barang Terbaru`, klik tombol `Edit` pada barang yang akan diperbaiki.
3. Ubah `Kode barang`, `Nama barang`, atau dimensi `P/L/T` sesuai kebutuhan.
4. Klik `Simpan`.
5. Sistem akan memuat ulang tabel dan menampilkan data terbaru.

## Catatan Operasional

- `Kode barang system` tetap tidak diedit dari modal ini karena dibuat otomatis oleh sistem.
- Jika kode barang yang diinput sudah dipakai data lain, sistem menolak update untuk mencegah identitas barang ganda.
