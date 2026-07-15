# Development Database Master Barang Komersil

## Sumber Data
Modul `master_barang` sekarang membaca dan menulis ke tabel `tbpo_barang`.

## Tabel Utama
`tbpo_barang`

## Field yang Dipakai Modul
- `id_barang`
- `kode_barang`
- `kd_suplier`
- `nama_barang`
- `satuan`
- `panjang`
- `lebar`
- `tinggi`
- `berat`
- `isi`
- `kemasan`
- `stock_minimum`
- `merk_barang`
- `kelompok_barang`
- `kategori_barang`
- `bhn_aktif`
- `produk_fokus`
- `is_active`
- `is_lot`

## Relasi Referensi
- `tbpo_barang.kd_suplier` direlasikan ke `tb_suplier.kd_suplier` untuk menampilkan `nama_suplier`.

## Dampak Database
- Tidak ada perubahan schema.
- Tidak ada penambahan tabel.
- Tidak ada alter column.
- Tidak ada index baru.
- Penambahan tab `Kode Akun dan HPP` hanya tampilan read-only; belum ada kolom baru di `tbpo_barang`.

## Validasi Data
- `kode_barang` divalidasi unik di level aplikasi saat proses simpan dan update.
- `kd_suplier` wajib terisi untuk proses tambah dan edit penuh.
- Status barang tetap memakai field lama `tbpo_barang.is_active`, dengan nilai `T` untuk `Aktif` dan `F` untuk `Tidak Aktif`.
- Untuk user `LOGISTIK`, update dibatasi hanya ke field:
  - `panjang`
  - `lebar`
  - `tinggi`
  - `berat`
  - `isi`
  - `kemasan`
