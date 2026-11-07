# Development Modul Jurnal - Akun Jurnal

Tanggal: 2026-07-13

## Tujuan

Membuat module baru bernama `Jurnal` untuk tahap awal accounting KARISMA ERP. Fokus implementasi saat ini adalah membuat dan mengelola Chart of Accounts/akun jurnal sesuai `docs/accounting/MASTER_SPECS.md`.

## Scope Implementasi

1. Route baru:
   - `dasboard` sebagai alias dashboard sesuai penulisan instruksi user
   - `jurnal`
   - `jurnal/list`
   - `jurnal/detail`
   - `jurnal/store`
   - `jurnal/update`
   - `jurnal/deactivate`
   - `jurnal/delete`
   - Alias domain: `keuangan/jurnal/*`
2. Controller tetap memakai `application/controllers/keuangan/C_Keuangan.php`.
3. Model tetap memakai `application/models/M_Keuangan.php`.
4. View baru:
   - `application/views/content/keuangan/jurnal.php`
   - `application/views/content/keuangan/ajax/ajax_jurnal.php`
5. Tombol dashboard:
   - Module `Jurnal` ditambahkan pada tab `KEUANGAN`.
   - Karena tab `ADMIN` menggabungkan menu semua module, admin juga melihat `Jurnal` pada tab `ADMIN`.
6. Sidebar fallback:
   - Jobdesk `ADMINKEU` mendapat menu `Jurnal`.
7. Card button master pendukung pada route `jurnal/`:
   - `Klasifikasi`
   - `Saldo Normal`
   - `Tipe Kontrol`
   - `Parent / Subclass`
8. CRUD master pendukung:
   - `Klasifikasi` memakai tabel `tbkeu_klasifikasi_akun`.
   - `Saldo Normal` memakai tabel `tbkeu_saldo_normal`.
   - `Tipe Kontrol` memakai tabel `tbkeu_tipe_kontrol`.
   - `Parent / Subclass` memakai akun `HEADER` dari `tbkeu_akun`.
9. Opsi select pada form akun tidak ditulis hardcode di view. Opsi diambil dari controller/model dan, setelah migration master pendukung dijalankan, berasal dari database.

## Hak Akses

Controller membatasi akses module jurnal untuk:

- username `admin`;
- session `is_admin_dashboard`;
- level `1` dengan jobdesk `ADMIN`, `ADMINKEU`, atau `ADMINKEUTC`.

User di luar daftar tersebut mendapat HTTP 403. AJAX memakai response standar accounting:

```json
{
  "success": false,
  "message": "Akses modul jurnal hanya untuk admin dan keuangan.",
  "data": null,
  "errors": {
    "code": "FORBIDDEN",
    "details": []
  },
  "meta": {
    "request_id": "acct_*",
    "timestamp": "ISO-8601"
  }
}
```

## Fitur Akun Jurnal

Halaman `jurnal` menyediakan:

- ringkasan total akun, HEADER, POSTING, aktif, nonaktif;
- card button master pendukung setelah title halaman;
- modal CRUD untuk master klasifikasi, saldo normal, tipe kontrol, dan parent/subclass;
- daftar akun dengan pencarian kode/nama/klasifikasi;
- filter select daftar akun berdasarkan klasifikasi dari master `tbkeu_klasifikasi_akun`;
- area `Form Jurnal` yang menampilkan data jurnal sesuai akun yang dipilih;
- detail form akun jurnal lama dipindahkan menjadi modal pop-out `Form Akun Jurnal`;
- tombol `Tambah Akun Jurnal` tersedia pada header halaman dan tombol ikon plus di header daftar akun;
- tombol detail/edit akun tersedia pada setiap item daftar akun;
- tambah akun baru melalui modal;
- edit akun melalui modal;
- nonaktifkan akun;
- hapus akun hanya bila belum dipakai jurnal dan tidak memiliki child account;
- pilihan parent hanya akun bertipe `HEADER`;
- akun bertipe `HEADER` otomatis tidak boleh dipakai untuk jurnal manual;
- guard jika schema accounting belum dimigrasikan.

## Update UI Daftar Akun dan Form Jurnal

Perubahan terbaru membuat halaman mengikuti pola kerja pada referensi gambar user:

1. Panel kiri menjadi `Daftar Akun` dengan title header di sisi kanan panel.
2. Select option `Semua Klasifikasi` memfilter daftar akun dari server berdasarkan `id_klasifikasi`.
3. Search tetap berjalan bersama filter klasifikasi.
4. Klik akun pada daftar memuat identitas akun dan tabel `Form Jurnal` di panel kanan.
5. Jika schema `tbkeu_jurnal` dan `tbkeu_jurnal_detail` belum tersedia, tabel kanan tetap tampil dengan pesan bahwa data posting akan muncul setelah schema General Ledger dimigrasikan.
6. Form tambah/edit akun tidak lagi permanen di kanan; form lama menjadi modal pop-out agar halaman utama fokus pada daftar akun dan data jurnal.
7. Endpoint baru `jurnal/account-journal` dan alias `keuangan/jurnal/account-journal` dipakai untuk mengambil jurnal berdasarkan akun terpilih.
8. Migration General Ledger executable tersedia di `docs/database/accounting_general_ledger_journal_20260713.sql`.

## Aturan Bisnis yang Diterapkan

1. `kode_akun` wajib unik.
2. Akun wajib memiliki klasifikasi.
3. `saldo_normal` hanya `DEBIT` atau `KREDIT`.
4. `tipe_akun` hanya `HEADER` atau `POSTING`.
5. Parent akun tidak boleh dirinya sendiri.
6. Parent akun harus bertipe `HEADER`.
7. Akun yang memiliki child account tidak boleh diubah menjadi `POSTING`.
8. Akun yang sudah dipakai jurnal atau memiliki child tidak dihapus, tetapi dinonaktifkan.

## Batasan Tahap Ini

Tahap ini belum membuat posting jurnal umum, jurnal detail, periode fiskal, mapping akun, laporan, reversal, atau auto-posting transaksi ERP. Struktur UI dan CRUD akun disiapkan sebagai fondasi tahap accounting berikutnya.

## Cara Pakai

1. Jalankan migration SQL: `docs/database/accounting_jurnal_accounts_20260713.sql`.
2. Jalankan migration SQL: `docs/database/accounting_general_ledger_journal_20260713.sql` jika panel `Form Jurnal` sudah harus membaca tabel jurnal.
3. Login sebagai admin atau user keuangan yang diizinkan.
4. Buka `dashboard`.
5. Klik tab `KEUANGAN` lalu module `Jurnal`.
6. Admin juga dapat membuka module dari tab `ADMIN`.
7. Gunakan filter `Semua Klasifikasi` untuk membatasi daftar akun.
8. Klik akun untuk melihat data jurnal akun tersebut di panel kanan.
9. Klik `Tambah Akun Jurnal` atau ikon plus pada panel daftar untuk membuat akun baru.
10. Klik ikon edit pada item akun atau tombol `Detail Akun` di panel kanan untuk membuka modal detail/edit akun.
11. Tambah akun `HEADER` untuk kelompok, lalu akun `POSTING` sebagai akun yang dapat dipakai jurnal.

## File Baru

- `application/views/content/keuangan/jurnal.php`
- `application/views/content/keuangan/ajax/ajax_jurnal.php`
- `docs/database/accounting_jurnal_accounts_20260713.sql`
- `docs/database/accounting_jurnal_master_options_20260713.sql`
- `docs/database/accounting_general_ledger_journal_20260713.sql`
- `docs/development-jurnal-accounting-akun-20260713.md`
- `docs/database-jurnal-accounting-akun-20260713.md`
- `docs/penggunaan-jurnal-akun-keuangan-20260713.md`
- `application/libraries/Accounting/docs/README.md`
- `application/libraries/Accounting/docs/database-schema.md`
- `application/libraries/Accounting/docs/chart-of-accounts.md`
- `application/libraries/Accounting/docs/user-guide-chart-of-accounts.md`

## File Diubah

- `application/config/routes.php`
- `application/controllers/keuangan/C_Keuangan.php`
- `application/models/M_Keuangan.php`
- `application/models/M_Dashboard.php`
- `application/views/partial/main/sidebar.php`

## Validasi Teknis

Validasi yang dilakukan:

- lint PHP controller, model, route, dashboard model, sidebar, dan view jurnal;
- scan source memastikan tidak ada referensi ke `tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, atau `tbpo_akun_tr` pada file implementasi baru.
