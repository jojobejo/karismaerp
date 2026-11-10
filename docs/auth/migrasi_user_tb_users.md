# Dokumentasi Migrasi Sistem User dari `tb_karyawan` ke `tb_users`

## 1. Ringkasan Perubahan
Sistem autentikasi, sesi pengguna (`session`), manajemen pengguna (`User Management`), dan hak akses modul di seluruh aplikasi **KARISMA ERP** telah dimigrasikan sepenuhnya dari tabel lama `tb_karyawan` ke tabel `tb_users`.

### Prinsip Utama Migrasi:
1. **Tabel Utama Autentikasi & Manajemen User**: Seluruh proses login (`M_Auth`), manajemen user (`M_Usermanagement`), fasilitas user (`M_Userfacility`), dan pengecekan sesi menggunakan `tb_users` (`status_karyawan = 'AKTIF'`). Tabel `tb_karyawan` tetap dipertahankan sebagai arsip/referensi historis.
2. **Standarisasi Jobdesk (`bagian`)**: Nilai `session('jobdesk')` kini mengikuti kolom `bagian` pada `tb_users` (dengan tetap mendukung nilai jobdesk lama untuk kompatibilitas mundur).
3. **Hierarki Level Akses (`level` Naik / Ascending)**:
   - Di `tb_users`, semakin tinggi angka `level`, semakin tinggi jabatan dan cakupan aksesnya:
     - `1` = Karyawan / Staff / Admin Operasional
     - `2` = Koordinator
     - `3` = Manager
     - `4` = Kepala Departemen (Kadep)
     - `5` = Direktur / Direksi
     - `99` = Administrator / Superadmin
4. **Konsolidasi Multi-Akun menjadi Satu Akun Tunggal (`1 Orang = 1 Akun`)**:
   - Karyawan yang sebelumnya memiliki 2 atau lebih akun di `tb_karyawan` kini cukup menggunakan **1 akun di `tb_users`** dan otomatis mendapatkan gabungan seluruh akses dari akun-akun lamanya (`vita`, `alif`, `evi`, `dewi`, `direktur1`).

---

## 2. Tabel Mapping `tb_karyawan` ke `tb_users`

Berikut adalah daftar 35 akun pada `tb_karyawan` yang telah dipetakan ke 29 akun unik di `tb_users`:

| No | NIK (`tb_karyawan`) | Nama di `tb_karyawan` | Username `tb_users` | Nama Lengkap (`tb_users`) | Bagian / Jobdesk (`tb_users`) | Level | Status |
|---:|---|---|---|---|---|---:|---|
| 1 | `QIUK00000001` | Admin Gudang | `gudang` | Admin Gudang | `ADMIN GUDANG` | 1 | Baru Dibuat |
| 2 | `QIUK00010000` | Annelia | `direktur1` | Direktur 1 | `DIREKSI` | 5 | Konsolidasi (1 Akun) |
| 3 | `QIUK11111` | admin | `admin` | Administrator | `ADMIN` | 99 | Diperbarui (`level=99`, `prefix=A`) |
| 4 | `QIUK00001211` | Admin Distribusi | `alif` | Alif | `ADMIN DISTRIBUSI & MANAGER WAREHOUSE` | 3 | Baru Dibuat & Konsolidasi |
| 5 | `QIUK1122` | admlpb | `yunika` | Yunika Dewi Wulaningtyas | `ADMIN LPB & GUDANG BENIH` | 1 | Terpetakan |
| 6 | `QIUK1123` | nando | `nando` | Fernando Ihza Aprianto | `MT ADMIN LOGISTIK` | 1 | Terpetakan |
| 7 | `QIUK1124` | sholeh | `sholeh` | Muhammad Sholeh | `CHECKER` | 1 | Terpetakan |
| 8 | `QIUK1122` | Alif | `alif` | Alif | `ADMIN DISTRIBUSI & MANAGER WAREHOUSE` | 3 | Konsolidasi dengan #4 |
| 9 | `QIUK1122` | amar | `amar` | Mu'amar Kadafi | `MT MANAGER DISTRIBUSI` | 3 | Terpetakan |
| 10 | `QIUK1127` | kiusl | `sales` | Sales | `SALES` | 1 | Baru Dibuat |
| 11 | `QIUK11111` | kiudir | `direktur1` | Direktur 1 | `DIREKSI` | 5 | Konsolidasi dengan #2 & #29 |
| 12 | `KIUKEU` | deva | `deva` | Deva Sari Violandani | `ADMIN JURNAL PAJAK` | 1 | Terpetakan |
| 13 | `QIUK11111` | kiuck | `fauzan` | Ahmad Fauzan | `MT MANAGER WAREHOUSE` | 3 | Terpetakan |
| 14 | `QIUK11111` | kiuks | `vita` | Vita Ari Puspita | `MT KADEP KEUANGAN & SALES` | 4 | Konsolidasi (1 Akun) |
| 15 | `QIUK11111` | kiuabm | `acep` | Acep Andriyanto | `MT ABM SEED JATIM TIMUR` | 3 | Terpetakan |
| 16 | `QIUK11111` | Reni | `reni` | Reni Icha Astari | `MT SALES COUNTER` | 1 | Terpetakan (`prefix=B`) |
| 17 | `QIUK11111` | kiuhard | `dhanny` | Dhany Rifaldi Febriansah | `IT HARDWARE` | 1 | Baru Dibuat |
| 18 | `QIUK111112` | Ariyani | `ariyani` | Ariyani | `MT SALES COUNTER` | 1 | Terpetakan (`prefix=C`) |
| 19 | `ADMINSC` | Admin SC | `nina` | Nina Putri Agustiningsih | `ADMIN SALES COUNTER` | 1 | Terpetakan (`prefix=D`) |
| 20 | `Q1234124` | evi | `evi` | Evi Yulia Purnamasari | `MANAGER SALES COUNTER` | 3 | Konsolidasi (1 Akun) |
| 21 | `Q12341241` | dewi | `dewi` | Dewi Sundari | `ADMIN PRICELIST & RETUR PENJUALAN` | 1 | Konsolidasi (1 Akun) |
| 22 | `Q1234124` | kadepsc | `vita` | Vita Ari Puspita | `MT KADEP KEUANGAN & SALES` | 4 | Konsolidasi dengan #14 |
| 23 | `Q1234124` | logistik | `kurniawan` | Kurniawan Pratama Arifin | `MT KADEP LOGISTIK` | 4 | Terpetakan |
| 24 | `COL001` | Team Collection | `tiya` | Muyassarotul Athiyah | `TEAM COLLECTION` | 1 | Terpetakan |
| 25 | `KSR001` | Kasir | `tri` | Tri Sulistiyo Artanto Kurniawan | `KASIR` | 1 | Terpetakan |
| 26 | `QIUK_MNGACC` | Manager ACC | `ahmad` | Ahmad Syaiti | `MT MANAGER ACCOUNTING & TAX` | 3 | Terpetakan |
| 27 | `QIUK_MNGSE` | Manager SE | `ibnu` | Ibnu Sutoro | `MANAGER SALES EXECUTIVE` | 3 | Terpetakan |
| 28 | `QIUK_DIROP` | Direktur Operasional | `diana` | Diana Wulandari | `MT DIREKTUR OPERASIONAL` | 5 | Terpetakan |
| 29 | `QIUK_DIRUT` | Direktur Utama | `direktur1` | Direktur 1 | `DIREKSI` | 5 | Konsolidasi dengan #2 & #11 |
| 30 | `QIUK_ADMPNJ` | Admin Penjualan | `dewi` | Dewi Sundari | `ADMIN PRICELIST & RETUR PENJUALAN` | 1 | Konsolidasi dengan #21 |
| 31 | `QIUK_KADEPUB` | Kadep Unit Bisnis | `arfin` | Arfin Indra Cahyadi | `KEPALA UNIT BISNIS` | 4 | Terpetakan |
| 32 | `QIUK_PUR` | purchasing (admin pitang) | `tarizqa` | Tarizqa Putri Aurelia | `ADMIN PURCHASING` | 1 | Terpetakan |
| 33 | `QIUK_PUR` | purchasing | `arini` | Arini Dina Yasmin | `MT KADEP PURCHASING` | 4 | Terpetakan |
| 34 | `QIUK_PUR` | manager_sc | `evi` | Evi Yulia Purnamasari | `MANAGER SALES COUNTER` | 3 | Konsolidasi dengan #20 |
| 35 | `QIUK_PUR` | manager_tc | `fairin` | Ifa Istifarini | `MT MANAGER KEUANGAN` | 3 | Terpetakan |

---

## 3. Perubahan Struktur Database (`tb_users`)
Penambahan kolom pendukung operasional pada `tb_users` agar seluruh fitur ERP berjalan tanpa ketergantungan pada `tb_karyawan`:
- `faktur_prefix` (`VARCHAR(5) NULL`): Menyimpan kode prefix faktur Sales Counter (`A` untuk `admin`, `B` untuk `reni`, `C` untuk `ariyani`, `D` untuk `nina`).
- `tim` (`VARCHAR(20) NULL`): Menyimpan kode tim opname/ICS.
- `wilayah` (`VARCHAR(100) NULL`) & `wilayah_id` (`INT(11) NULL`): Menyimpan cakupan wilayah sales/user.
- `jobdesk_id` (`INT(11) NULL`) & `akses_lv_id` (`INT(11) NULL`): Menyimpan relasi ke tabel master jobdesk dan akses level.
- `foto` (`VARCHAR(255) NULL`): Menyimpan nama file foto profil user.

---

## 4. Daftar Modul & File yang Diperbarui
1. **Autentikasi & Sesi**:
   - `application/models/M_Auth.php`
   - `application/controllers/auth.php`
   - `application/libraries/Permission.php`
2. **Manajemen User & Master Akses**:
   - `application/controllers/master/C_Usermanagement.php`
   - `application/models/master/M_Usermanagement.php`
   - `application/models/master/M_Userfacility.php`
   - `application/models/master/M_Jobdesk.php`
   - `application/models/master/M_Akseslevel.php`
   - `application/views/content/master/usermanagement/index.php`
3. **Dashboard & Navigasi Utama**:
   - `application/controllers/Dashboard.php`
   - `application/models/M_Dashboard.php`
   - `application/views/partial/main/sidebar.php`
4. **Modul Sales (Sales Order, Faktur, Retur Penjualan, Pengajuan OD, Kasbon)**:
   - `application/controllers/sales/C_SalesOrder.php`
   - `application/controllers/sales/C_SalesOrderLoby.php`
   - `application/controllers/sales/C_ReturPenjualan.php`
   - `application/controllers/sales/C_PengajuanOD.php`
   - `application/models/M_SalesOrder.php`
   - `application/models/M_ReturPenjualan.php`
   - `application/models/M_PengajuanOD.php`
   - `application/models/M_Kasbon.php`
   - Seluruh view di `application/views/content/sales/`
5. **Modul Logistik, Checker, ICS, LPB, & Stock Opname**:
   - `application/controllers/logistik/C_Checker.php`
   - `application/controllers/logistik/C_Ics.php`
   - `application/controllers/logistik/C_ImportLpb.php`
   - `application/controllers/admin/C_Stockopname.php`
   - `application/models/M_Checker.php`
   - `application/models/M_Ics.php`
   - `application/models/admin/M_Stockopname.php`
   - Seluruh view di `application/views/content/logistik/`
6. **Modul Keuangan, Accounting, Pembayaran, HRD, & Schedule**:
   - `application/controllers/keuangan/C_Accounting.php`
   - `application/controllers/keuangan/C_Keuangan.php`
   - `application/controllers/keuangan/C_pembayaran.php`
   - `application/controllers/keuangan/C_PembayaranSupplier.php`
   - `application/controllers/keuangan/C_PenyesuaianBarang.php`
   - `application/controllers/hrd/C_Hrd.php`
   - `application/models/M_Keuangan.php`
   - `application/models/M_Journal.php`
   - `application/models/M_Hrd.php`
   - Seluruh view di `application/views/content/keuangan/` & `application/views/content/schedule/body.php`

