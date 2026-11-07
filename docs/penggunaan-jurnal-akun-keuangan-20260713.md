# Guide Book Module Keuangan - Akun Jurnal

Tanggal: 2026-07-13  
Module: `Jurnal` / Akun Jurnal / Chart of Accounts  
Route: `jurnal` atau `keuangan/jurnal`

## 1. Pengantar Module

Module `Jurnal` adalah fondasi awal accounting pada KARISMA ERP. Pada tahap ini module dipakai untuk membuat, melihat, mengubah, menonaktifkan, dan menghapus akun jurnal yang akan menjadi dasar proses General Ledger.

Akun jurnal adalah daftar akun resmi perusahaan. Semua transaksi keuangan pada tahap berikutnya akan mengarah ke akun ini, misalnya:

- Kas;
- Bank;
- Piutang Usaha;
- Persediaan Barang;
- Hutang Usaha;
- Penjualan;
- Harga Pokok Penjualan;
- Beban Operasional.

Dengan bahasa bisnis: module ini adalah "peta besar keuangan". Jika peta akun rapi, laporan keuangan akan lebih mudah dikendalikan, diaudit, dan dikembangkan ke jurnal otomatis.

## 2. Hak Akses User

Module ini hanya dapat digunakan oleh user tertentu:

- username `admin`;
- user dengan session admin dashboard;
- level `1` dengan jobdesk `ADMIN`;
- level `1` dengan jobdesk `ADMINKEU`;
- level `1` dengan jobdesk `ADMINKEUTC`.

User selain daftar di atas akan mendapat pesan akses ditolak.

Tujuan pembatasan akses:

1. Akun jurnal adalah struktur inti laporan keuangan.
2. Perubahan akun dapat memengaruhi posting dan laporan pada tahap berikutnya.
3. Hanya admin dan tim keuangan yang boleh mengatur struktur akun.

## 3. Cara Membuka Module

### Dari Dashboard

1. Login ke aplikasi KARISMA ERP.
2. Buka route `dashboard`.
3. Pilih tab `KEUANGAN`.
4. Klik kartu module `Jurnal`.

### Dari Tab Admin

1. Login sebagai admin.
2. Buka route `dashboard`.
3. Pilih tab `ADMIN`.
4. Klik kartu module `Jurnal`.

### Dari URL Langsung

User yang memiliki akses dapat membuka:

```text
jurnal
```

atau:

```text
keuangan/jurnal
```

## 4. Syarat Sebelum Digunakan

Database accounting harus sudah dimigrasikan.

File migration:

```text
docs/database/accounting_jurnal_accounts_20260713.sql
```

Migration untuk tabel jurnal General Ledger:

```text
docs/database/accounting_general_ledger_journal_20260713.sql
```

Jika migration belum dijalankan, halaman tetap bisa dibuka tetapi akan menampilkan peringatan:

```text
Schema accounting belum tersedia.
```

Artinya database belum memiliki tabel:

- `tbkeu_klasifikasi_akun`;
- `tbkeu_akun`.

Jika panel kanan menampilkan pesan:

```text
Tabel jurnal belum tersedia. Data akan tampil setelah schema General Ledger dimigrasikan.
```

Artinya migration `accounting_jurnal_accounts_20260713.sql` sudah ada, tetapi migration `accounting_general_ledger_journal_20260713.sql` belum dijalankan.

## 5. Struktur Halaman

Halaman `Jurnal` terdiri dari empat bagian utama:

1. Tombol kembali ke dashboard.
2. Card button master pendukung.
3. Daftar akun dengan filter klasifikasi.
4. Form Jurnal yang menampilkan data jurnal sesuai akun yang dipilih.

Form tambah/edit akun jurnal berada di modal pop-out. Modal dibuka melalui tombol `Tambah Akun Jurnal`, ikon plus pada daftar akun, ikon edit pada item akun, atau tombol `Detail Akun`.

## 6. Card Button Master Pendukung

Bagian setelah title `Jurnal - Akun Jurnal` menampilkan empat card button. Card ini dipakai untuk mengelola data pendukung akun jurnal tanpa menulis pilihan secara hardcode di view.

### Klasifikasi

Card `Klasifikasi` membuka CRUD master klasifikasi akun.

Kegunaan:

- membuat kelompok Harta, Kewajiban, Modal, Pendapatan, dan Beban;
- menentukan jenis laporan `NERACA` atau `LABA_RUGI`;
- menentukan saldo normal default klasifikasi;
- mengatur urutan tampil klasifikasi.

Field yang dikelola:

- ID Klasifikasi;
- Kode;
- Nama;
- Alias;
- Jenis Laporan;
- Saldo Normal;
- Urutan;
- Status Aktif.

### Saldo Normal

Card `Saldo Normal` membuka CRUD master saldo normal.

Kegunaan:

- menjadi sumber pilihan field `Saldo Normal`;
- menjaga pilihan debit/kredit berasal dari database;
- memungkinkan admin mengelola label dan status saldo normal.

Field yang dikelola:

- Kode Saldo;
- Nama Saldo;
- Keterangan;
- Urutan;
- Status Aktif.

Seed awal:

- `DEBIT`;
- `KREDIT`.

### Tipe Kontrol

Card `Tipe Kontrol` membuka CRUD master tipe kontrol akun.

Kegunaan:

- mengelola kategori fungsi akun seperti kas, bank, piutang, hutang, persediaan, dan GRNI;
- menjadi sumber pilihan field `Tipe Kontrol`;
- mempersiapkan mapping accounting tahap berikutnya.

Field yang dikelola:

- Kode Tipe Kontrol;
- Nama Tipe Kontrol;
- Keterangan;
- Urutan;
- Status Aktif.

### Parent / Subclass

Card `Parent / Subclass` membuka CRUD akun `HEADER`.

Kegunaan:

- membuat akun induk atau subclass;
- mengelola struktur pohon akun;
- memastikan parent yang dipilih di form akun berasal dari akun `HEADER`.

Field yang dikelola:

- Kode Akun;
- Nama Akun;
- Klasifikasi;
- Parent;
- Saldo Normal;
- Tipe Kontrol;
- Status Aktif.

Catatan:

Card ini menyimpan data sebagai akun bertipe `HEADER`, bukan `POSTING`.

## 7. CRUD Master Pendukung

Setiap card membuka modal dengan dua area:

1. Daftar data master di kiri.
2. Form tambah/edit di kanan.

### Alur Tambah Data Master

1. Klik salah satu card button.
2. Klik tombol `Baru`.
3. Isi form.
4. Klik `Simpan`.
5. Data master akan muncul di daftar.
6. Pilihan pada form akun akan ikut diperbarui.

### Alur Edit Data Master

1. Klik salah satu card button.
2. Pilih data master pada daftar kiri.
3. Ubah field yang diperlukan.
4. Klik `Simpan`.

Catatan:

Kode utama seperti kode saldo normal dan kode tipe kontrol tidak dapat diubah saat edit. Jika butuh kode baru, buat data baru.

### Alur Hapus Data Master

1. Klik salah satu card button.
2. Pilih data master.
3. Klik `Hapus`.

Data master yang sudah digunakan akun tidak boleh dihapus. Gunakan status nonaktif agar histori tetap aman.

## 8. Daftar Akun

Panel kiri menampilkan daftar akun yang sudah dibuat.

Setiap item akun menampilkan:

- kode akun;
- status aktif/nonaktif;
- nama akun;
- klasifikasi;
- tipe akun;
- saldo normal.

### Search Akun

Field pencarian dapat digunakan untuk mencari akun berdasarkan:

- kode akun;
- nama akun;
- nama klasifikasi.

Contoh penggunaan:

- ketik `Kas` untuk mencari akun kas;
- ketik `1100` untuk mencari kode akun;
- ketik `Harta` untuk melihat akun dalam klasifikasi Harta.

### Filter Klasifikasi

Select `Semua Klasifikasi` dipakai untuk membatasi daftar akun berdasarkan klasifikasi yang tersimpan di database.

Contoh:

- pilih `Harta` untuk menampilkan akun harta saja;
- pilih `Pendapatan` untuk menampilkan akun pendapatan saja;
- pilih `Semua Klasifikasi` untuk mengembalikan seluruh akun.

Filter ini berjalan bersama search. Jika filter `Harta` aktif lalu search berisi `Kas`, daftar hanya menampilkan akun kas dalam klasifikasi Harta.

Kegunaan search:

1. Mempercepat pencarian akun saat jumlah akun besar.
2. Mengurangi risiko memilih akun yang salah.
3. Membantu audit master akun.

### Klik Akun

Saat user klik salah satu akun pada daftar, panel kanan `Form Jurnal` akan menampilkan data jurnal untuk akun tersebut.

Kegunaan:

- melihat transaksi jurnal akun terpilih;
- membaca tanggal, nomor referensi, catatan, debit, dan kredit;
- memastikan data kanan selalu mengikuti akun yang dipilih.

Jika tabel jurnal General Ledger belum dimigrasikan, panel kanan menampilkan pesan bahwa data posting belum tersedia.

## 9. Form Akun Jurnal

Form akun jurnal berada di modal pop-out. Modal ini dipakai untuk membuat dan mengelola akun.

Cara membuka modal:

1. Klik `Tambah Akun Jurnal` di header halaman untuk membuat akun baru.
2. Klik ikon plus pada panel daftar akun untuk membuat akun baru.
3. Klik ikon edit pada item daftar akun untuk melihat atau mengubah akun.
4. Klik tombol `Detail Akun` di panel kanan untuk mengubah akun yang sedang dipilih.

### Kode Akun

Kode unik untuk akun.

Contoh:

```text
1000
1100
1200
4100
5100
```

Aturan:

1. Wajib diisi.
2. Tidak boleh sama dengan kode akun lain.
3. Maksimal 30 karakter.
4. Sebaiknya memakai format angka bertingkat agar mudah dibaca.

Rekomendasi struktur kode:

| Rentang | Kelompok |
| --- | --- |
| 1000-1999 | Harta |
| 2000-2999 | Kewajiban |
| 3000-3999 | Modal |
| 4000-4999 | Pendapatan |
| 5000-5999 | Beban Atas Pendapatan |
| 6000-6999 | Beban Operasional |
| 7000-7999 | Beban Non Operasional |
| 8000-8999 | Pendapatan Lain |
| 9000-9999 | Beban Lain |

### Nama Akun

Nama akun yang akan tampil di daftar dan laporan.

Contoh:

```text
Kas
Bank BCA
Piutang Usaha
Persediaan Barang
Hutang Usaha
Penjualan
Harga Pokok Penjualan
```

Aturan:

1. Wajib diisi.
2. Gunakan nama jelas dan konsisten.
3. Hindari nama terlalu umum jika akun butuh detail, misalnya `Bank` bisa dibuat lebih spesifik menjadi `Bank BCA`, `Bank Mandiri`, atau `Bank BRI`.

### Klasifikasi

Klasifikasi menentukan kelompok laporan dan saldo normal default akun.

Pilihan awal:

1. Harta;
2. Kewajiban;
3. Modal;
4. Pendapatan;
5. Beban Atas Pendapatan;
6. Beban Operasional;
7. Beban Non Operasional;
8. Pendapatan Lain;
9. Beban Lain.

Kegunaan:

- menentukan posisi akun pada laporan;
- membantu laporan Neraca dan Laba Rugi;
- menentukan saldo normal akun.

### Parent

Parent adalah akun induk.

Contoh:

```text
1000 - Harta
  1100 - Kas
  1200 - Bank
```

Aturan:

1. Parent boleh kosong jika akun berada di level utama.
2. Parent hanya bisa akun bertipe `HEADER`.
3. Akun tidak boleh menjadi parent untuk dirinya sendiri.

Kegunaan:

- membuat struktur pohon akun;
- mengelompokkan akun agar rapi;
- mempermudah laporan per kelompok.

### Saldo Normal

Saldo normal menentukan sisi normal akun:

- `DEBIT`;
- `KREDIT`.

Aturan umum:

| Klasifikasi | Saldo Normal |
| --- | --- |
| Harta | DEBIT |
| Kewajiban | KREDIT |
| Modal | KREDIT |
| Pendapatan | KREDIT |
| Beban Atas Pendapatan | DEBIT |
| Beban Operasional | DEBIT |
| Beban Non Operasional | DEBIT |
| Pendapatan Lain | KREDIT |
| Beban Lain | DEBIT |

Saat klasifikasi dipilih, sistem otomatis mengarahkan saldo normal sesuai master klasifikasi. User tetap bisa melihat field ini untuk memastikan akun berada pada sisi yang benar.

### Tipe Akun

Ada dua tipe akun:

#### HEADER

Akun kelompok atau induk.

Kegunaan:

- menjadi parent akun;
- mengelompokkan akun;
- bukan akun transaksi.

Contoh:

```text
1000 - Harta
2000 - Kewajiban
4000 - Pendapatan
```

#### POSTING

Akun transaksi.

Kegunaan:

- dipakai untuk jurnal;
- dipakai untuk laporan;
- dipakai untuk mapping akun pada tahap berikutnya.

Contoh:

```text
1100 - Kas
1200 - Bank
4100 - Penjualan
5100 - Harga Pokok Penjualan
```

Aturan penting:

1. Akun `HEADER` tidak boleh dipakai jurnal manual.
2. Akun yang memiliki child account harus tetap `HEADER`.
3. Akun transaksi harus dibuat sebagai `POSTING`.

### Tipe Kontrol

Tipe kontrol menjelaskan fungsi bisnis khusus akun.

Pilihan:

| Tipe Kontrol | Fungsi |
| --- | --- |
| `NONE` | Akun biasa tanpa kontrol khusus. |
| `KAS` | Akun kas tunai. |
| `BANK` | Akun rekening bank. |
| `PIUTANG` | Akun piutang customer. |
| `HUTANG` | Akun hutang supplier. |
| `PERSEDIAAN` | Akun persediaan barang. |
| `GRNI` | Barang diterima belum ditagih supplier. |
| `PAJAK_MASUKAN` | Akun PPN masukan. |
| `PAJAK_KELUARAN` | Akun PPN keluaran. |
| `UANG_MUKA_CUSTOMER` | Akun uang muka penjualan/customer. |
| `UANG_MUKA_SUPPLIER` | Akun uang muka pembelian/supplier. |
| `LABA_DITAHAN` | Akun laba ditahan. |

Kegunaan:

1. Menandai akun penting untuk proses accounting berikutnya.
2. Membantu mapping otomatis.
3. Mempermudah validasi laporan.

### Status Aktif

Checkbox `Aktif` menentukan apakah akun masih digunakan.

Jika dicentang:

- akun aktif;
- akun dapat dipakai untuk proses berikutnya.

Jika tidak dicentang:

- akun nonaktif;
- akun tetap tersimpan sebagai histori.

### Boleh Jurnal Manual

Checkbox ini menentukan apakah akun boleh dipakai untuk jurnal manual pada tahap berikutnya.

Aturan:

1. Hanya berlaku untuk akun `POSTING`.
2. Jika tipe akun `HEADER`, sistem otomatis mematikan opsi ini.
3. Tidak semua akun POSTING harus boleh jurnal manual.

Contoh akun yang biasanya boleh jurnal manual:

- Beban Administrasi;
- Beban Bank;
- Pendapatan Lain.

Contoh akun yang sebaiknya dikontrol mapping, bukan manual:

- Piutang Usaha;
- Hutang Usaha;
- Persediaan Barang;
- GRNI.

## 10. Fungsi Tombol

### Tombol Home

Ikon rumah di kiri judul halaman.

Fungsi:

- kembali ke dashboard utama.

### Tombol Baru

Fungsi:

- mengosongkan form;
- menyiapkan input akun baru;
- menghapus pilihan akun yang sedang aktif.

Kapan dipakai:

- saat user ingin membuat akun baru.

### Tombol Simpan

Fungsi:

- menyimpan akun baru;
- mengupdate akun lama jika form sedang memuat akun existing.

Validasi saat simpan:

1. Kode akun wajib diisi.
2. Nama akun wajib diisi.
3. Klasifikasi wajib dipilih.
4. Kode akun tidak boleh duplikat.
5. Parent harus akun `HEADER`.
6. Akun tidak boleh menjadi parent dirinya sendiri.
7. Akun yang punya child tidak boleh diubah menjadi `POSTING`.

### Tombol Nonaktifkan

Fungsi:

- mengubah akun menjadi nonaktif.

Kapan dipakai:

- akun sudah tidak digunakan untuk transaksi baru;
- akun perlu dipertahankan karena mungkin sudah pernah dipakai;
- perusahaan ingin merapikan daftar akun tanpa menghapus histori.

Catatan:

Menonaktifkan lebih aman daripada menghapus.

### Tombol Hapus

Fungsi:

- menghapus akun dari database.

Aturan:

1. Tombol hanya aktif jika akun belum dipakai transaksi jurnal.
2. Tombol hanya aktif jika akun tidak memiliki child account.
3. Jika akun sudah dipakai atau memiliki child, gunakan `Nonaktifkan`.

Kapan dipakai:

- akun baru salah input;
- akun belum pernah digunakan;
- akun bukan parent dari akun lain.

## 11. Alur Proses Membuat Akun

### Contoh 1: Membuat Akun HEADER

Tujuan: membuat kelompok `Bank`.

1. Klik `Baru`.
2. Isi `Kode Akun`: `1200`.
3. Isi `Nama Akun`: `Bank`.
4. Pilih `Klasifikasi`: `Harta`.
5. Pilih `Parent`: `1000 - Harta` jika tersedia.
6. Pastikan `Saldo Normal`: `DEBIT`.
7. Pilih `Tipe Akun`: `HEADER`.
8. Pilih `Tipe Kontrol`: `NONE`.
9. Pastikan `Aktif` dicentang.
10. Klik `Simpan`.

Hasil:

`1200 - Bank` menjadi akun kelompok yang bisa menjadi parent rekening bank.

### Contoh 2: Membuat Akun POSTING

Tujuan: membuat rekening `Bank BCA`.

1. Klik `Baru`.
2. Isi `Kode Akun`: `1210`.
3. Isi `Nama Akun`: `Bank BCA`.
4. Pilih `Klasifikasi`: `Harta`.
5. Pilih `Parent`: `1200 - Bank`.
6. Pastikan `Saldo Normal`: `DEBIT`.
7. Pilih `Tipe Akun`: `POSTING`.
8. Pilih `Tipe Kontrol`: `BANK`.
9. Centang `Aktif`.
10. Centang `Boleh Jurnal Manual` hanya jika akun ini boleh dipakai input jurnal manual.
11. Klik `Simpan`.

Hasil:

`1210 - Bank BCA` menjadi akun transaksi.

## 12. Alur Mengubah Akun

1. Cari akun melalui daftar kiri.
2. Klik akun yang ingin diubah.
3. Ubah field yang diperlukan.
4. Klik `Simpan`.

Contoh perubahan:

- memperbaiki nama akun;
- mengubah tipe kontrol;
- memindahkan parent;
- mengubah status aktif.

Catatan:

Jangan mengubah makna akun yang sudah dipakai transaksi. Jika akun sudah salah secara konsep, lebih aman nonaktifkan akun lama dan buat akun baru.

## 13. Alur Menonaktifkan Akun

1. Cari akun.
2. Klik akun.
3. Klik `Nonaktifkan`.
4. Tunggu notifikasi berhasil.

Setelah nonaktif:

- akun tetap terlihat dalam daftar;
- status berubah menjadi `Nonaktif`;
- akun tidak disarankan untuk transaksi baru.

## 14. Alur Menghapus Akun

1. Cari akun.
2. Klik akun.
3. Pastikan tombol `Hapus` aktif.
4. Klik `Hapus`.
5. Konfirmasi penghapusan.

Jika tombol `Hapus` tidak aktif, kemungkinan:

- akun sudah pernah dipakai jurnal;
- akun memiliki child account;
- tidak ada akun yang sedang dipilih.

Solusi:

- gunakan `Nonaktifkan`;
- hapus atau pindahkan child account terlebih dahulu jika akun benar-benar belum dipakai.

## 15. Konsep Hierarki Akun

Hierarki akun membuat laporan lebih rapi.

Contoh struktur:

```text
1000 Harta
  1100 Kas
  1200 Bank
    1210 Bank BCA
    1220 Bank Mandiri
  1300 Piutang Usaha
  1400 Persediaan Barang

2000 Kewajiban
  2100 Hutang Usaha
  2200 GRNI/Barang Diterima Belum Ditagih

4000 Pendapatan
  4100 Penjualan

5000 Beban Atas Pendapatan
  5100 Harga Pokok Penjualan
```

Prinsip:

1. Level atas biasanya `HEADER`.
2. Akun detail transaksi adalah `POSTING`.
3. Laporan bisa membaca kelompok berdasarkan parent dan klasifikasi.

## 16. Hubungan Dengan Proses Accounting Berikutnya

Tahap ini belum melakukan posting jurnal, tetapi akun yang dibuat akan menjadi fondasi untuk:

- jurnal umum;
- mapping akun;
- auto-posting transaksi ERP;
- buku besar;
- neraca saldo;
- laba rugi;
- neraca;
- audit trail;
- exception dashboard.

Jika Chart of Accounts salah, proses accounting berikutnya akan ikut lemah. Karena itu akun perlu dirancang dengan hati-hati sebelum dipakai transaksi.

## 17. Kesalahan Umum dan Cara Mengatasinya

### Schema Accounting Belum Tersedia

Penyebab:

- migration SQL belum dijalankan.

Solusi:

- jalankan `docs/database/accounting_jurnal_accounts_20260713.sql`.

### Kode Akun Sudah Digunakan

Penyebab:

- user membuat kode akun yang sudah ada.

Solusi:

- cari kode akun tersebut di daftar kiri;
- gunakan kode lain;
- edit akun existing jika memang akun yang sama.

### Parent Akun Tidak Ditemukan

Penyebab:

- parent sudah dihapus;
- data form lama;
- pilihan parent tidak valid.

Solusi:

- refresh halaman;
- pilih parent dari dropdown yang tersedia.

### Parent Harus HEADER

Penyebab:

- user mencoba menjadikan akun `POSTING` sebagai parent.

Solusi:

- gunakan akun `HEADER` sebagai parent;
- jika perlu, buat akun HEADER baru.

### Akun Memiliki Child Account

Penyebab:

- akun yang ingin diubah menjadi POSTING atau dihapus masih memiliki akun anak.

Solusi:

- pertahankan sebagai HEADER;
- pindahkan child account ke parent lain;
- nonaktifkan akun jika tidak dipakai.

## 18. Rekomendasi Best Practice

1. Buat struktur akun sebelum transaksi mulai berjalan.
2. Gunakan kode akun konsisten.
3. Jangan menghapus akun yang sudah masuk proses bisnis.
4. Gunakan `Nonaktifkan` untuk akun lama.
5. Pisahkan akun HEADER dan POSTING dengan disiplin.
6. Gunakan tipe kontrol untuk akun penting seperti kas, bank, piutang, hutang, persediaan, dan GRNI.
7. Jangan membuat akun terlalu umum jika laporan butuh detail.
8. Jangan membuat akun terlalu detail jika menyulitkan operasional.
9. Review Chart of Accounts bersama finance sebelum auto-posting diaktifkan.

## 19. Batasan Module Saat Ini

Module ini belum mencakup:

- input jurnal umum debit/kredit;
- posting jurnal;
- reversal;
- periode fiskal;
- mapping akun;
- laporan keuangan;
- export laporan;
- audit trail jurnal;
- auto-posting dari penjualan, pembelian, LPB, stock, atau pembayaran.

Fokus module saat ini adalah master akun jurnal.

## 20. Catatan Scope Purchase Order

Tabel berikut berada di luar scope module accounting:

- `tbpo_transaksi`;
- `tbpo_transaksi_tmp`;
- `tbpo_transaksi_trashbin`;
- `tbpo_akun_tr`.

Module accounting KARISMA ERP tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency terhadap tabel tersebut.

## 21. Checklist User Sebelum Go Live

Sebelum module dipakai serius, pastikan:

- migration sudah berhasil;
- klasifikasi akun sudah lengkap;
- akun HEADER utama tersedia;
- akun POSTING dasar tersedia;
- kode akun disepakati finance;
- akun kas dan bank sudah diberi tipe kontrol yang benar;
- akun piutang, hutang, persediaan, GRNI, penjualan, dan HPP tersedia;
- user yang mendapat akses sudah tepat;
- akun yang salah sudah diperbaiki sebelum transaksi dimulai.
