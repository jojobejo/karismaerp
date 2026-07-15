# Database - Edit Identitas Master Barang Stockopname

Tanggal: 2026-07-03

## Tabel Terkait

- `tb_master_barang_all`

## Field Yang Diubah Oleh Modul

Endpoint update `admin/stockopname/master_barang/update` memperbarui field berikut pada `tb_master_barang_all` berdasarkan `id`:

- `kd_barang`
- `nama_barang`
- `p`
- `l`
- `t`
- `kubikasi`

`kubikasi` dihitung ulang dari `p * l * t` dan disimpan sebagai string seperti perilaku sebelumnya.

## Validasi Database/Model

- Data target dicari berdasarkan `id`.
- `kd_barang` tidak boleh kosong.
- `kd_barang` tidak boleh sama dengan baris lain pada `tb_master_barang_all`.
- `nama_barang` tidak boleh kosong.
- `p`, `l`, dan `t` harus bernilai lebih dari 0.

## Perubahan Skema

Tidak ada perubahan struktur database dan tidak ada migration baru. Perubahan hanya menambah kemampuan update pada kolom existing.
