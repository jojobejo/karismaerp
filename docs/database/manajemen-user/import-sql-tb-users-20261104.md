# Dampak database fitur Import SQL User

Target tunggal: tabel `tb_users` pada database yang digunakan aplikasi. Fitur hanya menyisipkan akun baru dari data `INSERT INTO tb_users`; isi file tidak dijalankan sebagai perintah SQL mentah. Pembungkus phpMyAdmin untuk skema dan indeks dibaca tetapi tidak diterapkan. Akun dengan username yang sudah ada dilewati. ID asal dipertahankan bila tersedia dan belum dipakai akun berbeda; benturan ID ditolak. Tidak ada `UPDATE`, `DELETE`, atau perubahan tabel lain dari impor.

Pratinjau menghitung NIK ganda dan departemen kosong dari dump tanpa mengubah nilai sumber. Skema `tb_users` saat ini tidak memiliki unique constraint pada username atau NIK. Pemeriksaan username dan ID dilakukan oleh aplikasi, sehingga operasi lain yang berjalan bersamaan tetap perlu dikendalikan secara operasional saat impor besar.

Uji lokal pada `kiucoid_karismaerp_dev` sempat menambahkan satu akun uji lalu menghapusnya. Setelah uji, `tb_users` kembali berisi satu akun `admin`. File SQL berisi `DROP TABLE` ditolak sebelum ada perubahan data. Dump `tb_users (1).sql` yang diberikan pengguna telah lolos pratinjau, tetapi **belum diimpor** ke database.

Sebelum mengimpor data operasional, buat backup `tb_users`, periksa jumlah baris baru dan duplikat pada tahap pratinjau, lalu simpan hasil impor. Pemulihan untuk data yang salah dapat dilakukan dengan mengidentifikasi ID baru dari hasil impor dan memulihkan backup sesuai kondisi terbaru; jangan menghapus akun lama berdasarkan username yang kebetulan sama.

**Tidak ada perubahan skema database dan tidak diperlukan migrasi.**
