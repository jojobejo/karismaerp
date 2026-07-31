# Penggunaan - Split Detail LPB

Tanggal: 2026-07-31

## Cara Pakai

1. Buka route `ics/detail_record_lpb`.
2. Pilih LPB yang ingin diproses.
3. Jika LPB masih `POST`, klik `UNPOST` dan isi keterangan.
4. Setelah status menjadi `UNPOST`, tombol split dengan icon cabang akan tampil di baris detail barang.
5. Klik tombol split pada barang yang ingin dipecah.
6. Modal akan menampilkan baris pertama `Data Sekarang`.
7. Isi `Keterangan` bila diperlukan.
8. Isi atau ubah qty dan harga satuan pada baris `Data Sekarang`.
9. Isi baris `Split 1` untuk pecahan baru.
10. Jika butuh lebih dari 2 baris, klik `Tambah Baris`.
11. Pastikan `Selisih` qty dan harga menjadi 0.
12. Klik `Simpan Split`.

## Contoh

Barang Amuron 70 EC 40 X 250 ml masuk dengan:

- Qty In: `1000`
- Harga satuan: `Rp 106.560`

User membuat 2 baris:

- Data Sekarang: `500 x Rp 163.120`
- Split 1: `500 x Rp 50.000`

Nilai total LPB tetap sama dengan nilai awal.

## Validasi

- Tombol split tidak tampil jika LPB masih `POST`.
- Setiap baris split harus memiliki qty lebih dari 0.
- Harga satuan tidak boleh minus.
- Total qty seluruh baris tidak boleh lebih besar atau kurang dari Qty In awal.
- Total harga seluruh baris tidak boleh lebih besar atau kurang dari total harga awal.
- Jika validasi gagal, sistem menampilkan pesan dan data tidak disimpan.
