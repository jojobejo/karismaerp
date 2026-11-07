# Tampilan Dashboard Awal Aplikasi

Tanggal: 2026-07-08

## Konsep Tampilan

Dashboard dibuat seperti launcher modul operasional: cepat dibaca, memakai tab panel divisi, tiap menu menjadi kotak besar, dan tidak memakai sidebar kiri. Pendekatan ini membuat user langsung melihat pilihan kerja utama dari satu halaman awal setelah login.

## Elemen UI

1. Topbar
   - Menampilkan nama dashboard.
   - Menampilkan nama user dari session.
   - Tombol logout memakai icon.

2. Hero Ringkas
   - Judul `Dashboard Awal KARISMA ERP`.
   - Deskripsi singkat fungsi dashboard.
   - Badge akses menampilkan `jobdesk` dan `lv` dari session.

3. Tab Panel
   - Tab modul awal: `KEUANGAN`, `HRD`, `LOGISTIK`, `PURCHASING`, dan `SALES`.
   - Klik tab akan mengganti panel menu tanpa reload halaman.
   - Tab aktif default mengikuti mapping `jobdesk` pada `M_Dashboard`.

4. Kotak Menu
   - Ukuran kotak dibuat rata untuk semua menu.
   - Container dashboard dibuat hampir full width agar margin kanan dan kiri tidak terlalu besar.
   - Teks, tab panel, icon, dan kotak menu diperbesar supaya area desktop lebih terisi.
   - Grid tidak memakai kotak `wide` atau `tall` agar tidak muncul kolom kosong.
   - Icon memakai Font Awesome sesuai karakter menu.

5. Hover Deskripsi
   - Deskripsi tersembunyi saat normal.
   - Saat mouse diarahkan ke kotak, deskripsi muncul dengan animasi halus.
   - jQuery menambahkan class aktif agar hover dan focus keyboard konsisten.

## Responsif

1. Desktop lebar memakai grid 5 kolom.
2. Tablet turun menjadi 2 kolom.
3. Mobile menjadi 1 kolom agar teks dan tombol tetap terbaca.

## Warna

Warna dibuat bervariasi antar menu agar user lebih cepat mengenali area kerja. Palet tidak dibuat satu warna dominan supaya dashboard tidak monoton dan setiap modul punya sinyal visual sendiri.
