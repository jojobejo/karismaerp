# Pemeriksaan CRUD User Management

## Hasil pemeriksaan

- `tb_karyawan.id` pada database lokal adalah primary key `AUTO_INCREMENT`. Operasi edit, detail, dan hapus diarahkan oleh ID baris. Penambahan menggunakan ID hasil `insert_id()`.
- Validasi edit sebelumnya memeriksa NIK dan username terhadap semua baris lain. Jika database sudah berisi duplikat historis, edit nilai yang sama dapat ditolak. Edit sekarang mengizinkan NIK/username tetap sama untuk baris itu, tetapi tetap memeriksa keunikan jika nilainya diubah.
- Payload edit sebelumnya mengirim `foto = NULL` ketika tidak ada file baru. Sekarang kolom foto hanya ikut diperbarui saat file diunggah.
- Form edit mempertahankan opsi departemen, jobdesk, akses, dan jabatan tersimpan yang mungkin sudah tidak ada pada daftar master aktif.
- Delete memvalidasi ID dan melaporkan sukses hanya jika baris benar-benar terhapus.

## Cara penggunaan

- **Tambah User**: isi data wajib, termasuk password. Server memeriksa NIK dan username terhadap akun lain sebelum insert.
- **Ubah User**: gunakan ikon pensil. NIK dan username boleh dipertahankan; password tidak diubah lewat form ini, dan foto lama dipertahankan bila tidak memilih foto baru.
- **Hapus User**: gunakan ikon hapus pada baris akun yang dipilih. Endpoint menghapus berdasarkan ID baris.

## Database dan validasi

Tidak ada perubahan skema atau data database pada pekerjaan ini. Struktur aktif menunjukkan `id` sebagai primary key auto-increment dan indeks NIK biasa (non-unique). Pemeriksaan menemukan beberapa pasangan data historis dengan kombinasi NIK/username yang sama; tidak ada baris yang diubah atau dihapus. PHP lint dan `git diff --check` dijalankan; UAT CRUD pada browser belum dilakukan.
