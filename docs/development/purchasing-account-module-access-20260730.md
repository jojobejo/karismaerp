# Analisa Development Akses Akun Purchasing

Tanggal: 2026-07-30

## Tujuan

Mendata module dan fitur yang dapat digunakan oleh akun:

- Username: `purchasing`
- Source user aktif: `tb_karyawan`
- Nama: `PURCHASING`
- Level: `1`
- Jobdesk: `ADMIN PO`
- Departemen: `purchasing`
- Status: `1` / aktif

Analisa ini dibuat dari kode aplikasi, konfigurasi route, session login, dashboard module, guard controller, dan validasi HTTP lokal.

## Sumber Akses

### Auth

Login diproses melalui:

- Route: `process`
- Controller: `Auth::process()`
- Model: `M_Auth::get_auth_candidates()`
- Form field: `user_isi`, `pass_isi`

Setelah login berhasil, session utama yang dipakai modul:

- `username = purchasing`
- `lv = 1`
- `akses_lv = 1`
- `akses_lv_id = 1`
- `jobdesk = ADMIN PO`
- `departemen = purchasing`
- `logged_in = true`

Route redirect setelah login tidak punya cabang khusus untuk `ADMIN PO`, sehingga jatuh ke redirect default `dashboard`.

### Dashboard Module

Dashboard module berasal dari:

- Controller: `application/controllers/Dashboard.php`
- Model: `application/models/M_Dashboard.php`
- View: `application/views/content/dashboard/index.php`

`M_Dashboard::default_active_section()` memetakan `ADMIN PO` ke section `purchasing`. Section ini berisi module:

1. `Data PO` -> `ics/icspo`
2. `Input LPB Manual` -> `ics/lpb_manual`
3. `Laporan LPB` -> `ics/lpb_report`
4. `Retur` -> `ics/retur`
5. `Pending PO` -> `pendingpo`
6. `Master Barang` -> `master_barang`

### Sidebar

Sidebar membaca `tb_menu` bila ada isi menu dinamis. Pada database lokal saat analisa:

- `tb_menu` ada, tetapi jumlah data menu aktif/isi menu adalah `0`.
- Tidak ada cabang sidebar statis khusus `ADMIN PO`.

Dampaknya: akun `purchasing` dapat membuka module dari dashboard atau URL langsung, tetapi sidebar statis dapat hanya menampilkan `Log Out`.

## Validasi Runtime Lokal

Login HTTP lokal dengan akun `purchasing / 1234` berhasil dan mengarah ke dashboard.

Route yang terverifikasi HTTP 200 setelah login:

| Route | Status | Title |
| --- | --- | --- |
| `dashboard` | 200 | `Dashboard` |
| `keuangan` | 200 | `KARISMA - KEUANGAN` |
| `ics/icspo` | 200 | `KARISMA - LOGISTIK` |
| `ics/lpb_manual` | 200 | `KARISMA - Input LPB Manual` |
| `ics/lpb_report` | 200 | `KARISMA - Laporan LPB` |
| `ics/retur` | 200 | `KARISMA - LOGISTIK` |
| `ics/retur/pembelian` | 200 | `KARISMA - LOGISTIK` |
| `ics/retur/pembelian/adjustment` | 200 | `KARISMA - LOGISTIK` |
| `pendingpo` | 200 | `KARISMA` |
| `master_barang` | 200 | `KARISMA` |

## Detail Module Purchasing

### 1. Dashboard

Route: `dashboard`

Fungsi:

- Menjadi landing page akun `purchasing`.
- Menampilkan section `PURCHASING`.
- Menjadi pintu masuk ke module pembelian, LPB, retur, pending PO, dan master barang.

### 2. Data PO / Data LPB Purchasing

Route utama:

- `ics/icspo`
- `ics/detail_record_lpb`

Controller:

- `logistik/C_Ics::ics_po()`
- `logistik/C_Ics::detail_record_lpb()`

Untuk session `departemen = purchasing` atau `jobdesk = ADMIN PO`, method `resolve_ics_po_panel_mode()` mengembalikan mode `purchasing`. Artinya akun ini melihat panel LPB Purchasing, bukan panel Logistik.

Fitur utama:

- Melihat daftar LPB Purchasing.
- Filter status data: semua, belum invoice, belum pajak, belum afirmasi harga.
- Membuka detail LPB.
- Update invoice.
- Pecah invoice.
- Update faktur pajak.
- Update jenis LPB.
- Update harga dan qty LPB detail saat LPB masih `UNPOST`.
- Accept / verifikasi harga detail.
- Bulk accept harga detail.
- Post / Rekam LPB menjadi `POST`.
- Unpost LPB dengan alasan.

Catatan kontrol:

- Edit harga detail dibatasi ketika `status_lpb = UNPOST`.
- Verifikasi harga dan lifecycle `POST/UNPOST` adalah konsep terpisah.
- Perubahan penting disimpan melalui log LPB.

### 3. Input LPB Manual

Route:

- `ics/lpb_manual`
- `ics/lpb_manual/store`
- `ics/lpb_manual/barang`

Controller:

- `logistik/C_Ics::lpb_manual()`
- `logistik/C_Ics::ajax_lpb_manual_store()`
- `logistik/C_Ics::ajax_lpb_manual_barang()`

Akses:

- `ADMIN PO` lolos dari `can_access_lpb_manual()` karena dianggap admin PO / Purchasing.

Fitur utama:

- Input LPB tanpa PO.
- Pilih gudang.
- Pilih jenis LPB.
- Isi nomor SJ, invoice, tanggal LPB, dan keterangan.
- Tambah barang dari master `tbpo_barang`.
- Isi qty, satuan, nomor lot, expired date, dan harga satuan.
- Simpan LPB manual yang berdampak ke transaksi stok.

Catatan kontrol:

- LPB Manual bukan pengganti workflow PO normal.
- Harus dipakai untuk kasus khusus dan wajib diberi keterangan audit.

### 4. Laporan LPB

Route:

- `ics/lpb_report`

Controller:

- `logistik/C_Ics::lpb_report()`

Fitur utama:

- Filter sumber `Semua Sumber`.
- Filter `LPB Manual Purchasing`.
- Filter `LPB Logistik dari PO`.
- Filter tanggal.
- Melihat tanggal LPB, sumber, no LPB, referensi, no PO, gudang, item, qty, nilai, dan keterangan.

### 5. Retur Pembelian

Route utama:

- `ics/retur`
- `ics/retur/pembelian`
- `ics/retur/pembelian/create_draft`
- `ics/retur/pembelian/submit`
- `ics/retur/pembelian/verify_purchasing`
- `ics/retur/pembelian/verify_accounting`
- `ics/retur/pembelian/post`
- `ics/retur/pembelian/void`

Controller:

- `logistik/C_Ics::dash_retur()`
- `logistik/C_Ics::retur_pembelian()`
- Ajax retur pembelian di `C_Ics`

Akses:

- Guard `has_retur_access()` mengizinkan departemen yang mengandung `PURCHASING` dan jobdesk `ADMIN PO`.

Fitur utama:

- Membuat draft retur pembelian dari LPB final.
- Memilih LPB berdasarkan supplier, nomor PO, atau nomor LPB.
- Mengisi tanggal retur.
- Memilih jenis penyelesaian.
- Mengisi alasan retur.
- Mengisi qty retur per item.
- Submit draft.
- Verifikasi Purchasing.
- Alur lanjutan Accounting, Post, dan Void tersedia di UI sesuai status dokumen.

Catatan kontrol:

- Untuk praktik bisnis, akun purchasing fokus pada pembuatan draft dan verifikasi Purchasing.
- Posting final dan void perlu disiplin otorisasi karena berdampak stok dan jurnal.

### 6. Adjustment Harga LPB

Route:

- `ics/retur/pembelian/adjustment`
- `ics/retur/pembelian/adjustment/lpb_select2`
- `ics/retur/pembelian/adjustment/lpb_detail`
- `ics/retur/pembelian/adjustment/post`

Controller:

- `logistik/C_Ics::retur_pembelian_adjustment()`
- `logistik/C_Ics::ajax_retur_pembelian_adjustment_post()`

Fitur utama:

- Memilih LPB salah.
- Melihat detail barang dan harga.
- Mengisi harga invoice benar.
- Mengisi alasan adjustment.
- Posting adjustment harga LPB.

Catatan kontrol:

- Digunakan untuk koreksi harga, bukan koreksi qty fisik.
- Wajib ada alasan adjustment.
- Workflow membuat jejak transaksi koreksi.

### 7. Pending PO

Route:

- `pendingpo`

Controller:

- `keuangan/C_Keuangan::pendingpo()`

Fitur utama:

- Melihat PO yang masih pending.
- Kolom utama: tanggal, no PO, supplier, barang, qty PO, qty datang, qty sisa.

### 8. Master Barang

Route:

- `master_barang`
- `master_barang/list`
- `master_barang/detail`
- `master_barang/store`
- `master_barang/update`
- `master_barang/delete`

Controller:

- `keuangan/C_Keuangan::master_barang()`

Fitur utama:

- Melihat master barang.
- Mengelola data barang, supplier, satuan, kelompok dagang, dan akun terkait.
- Karena session `lv = 1`, fungsi `can_full_edit_master_barang()` memberi akses full edit.

Catatan risiko:

- Akses full edit muncul karena level `1`, bukan karena jobdesk `ADMIN PO` secara spesifik.
- Jika bisnis menghendaki purchasing hanya melihat atau edit field tertentu, perlu pengetatan rule.

## Temuan dan Rekomendasi

1. Akun `purchasing` aktif dan module utama Purchasing dapat dibuka.
2. Dashboard sudah tepat mengarahkan `ADMIN PO` ke section `PURCHASING`.
3. Sidebar belum punya cabang statis untuk `ADMIN PO`, sehingga pengalaman navigasi bisa tidak konsisten.
4. `tb_menu` kosong, sehingga hak akses menu dinamis belum dipakai untuk akun ini.
5. Akses `lv = 1` membuat beberapa module memiliki kemampuan terlalu luas, terutama Master Barang dan route keuangan yang tidak memakai guard ketat.
6. Rekomendasi berikutnya: buat matrix permission formal untuk `ADMIN PO`, isi `tb_menu/tb_akses_menu`, dan tambahkan sidebar statis atau perbaiki normalisasi kolom menu dinamis.
