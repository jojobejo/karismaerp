# Dokumentasi Development Aplikasi Stockopname

Periode: 2026-07-04 sampai 2026-07-05  
Zona acuan: Asia/Jakarta

## Ringkasan

Dokumen ini memisahkan catatan development aplikasi dari catatan database. Berdasarkan `git log`, development pada 2026-07-04 sudah masuk commit. Perubahan 2026-07-05 masih berupa perubahan lokal yang belum commit saat dokumen ini dibuat.

## Development 2026-07-04

Commit terkait:

- `96b5476` pada 2026-07-04 12:57:52 +0700 - `updated`
- `214fd7a` pada 2026-07-04 20:29:15 +0700 - `updated`

Perubahan aplikasi:

- Menambahkan kemampuan edit identitas master barang pada `admin/stockopname/master_barang`.
- Field yang dapat diedit admin meliputi kode barang, nama barang, dan dimensi P/L/T.
- Menambahkan validasi duplikasi kode barang saat update master barang.
- Menambahkan fix query lookup dimensi agar perbandingan kode barang aman saat collation berbeda.
- Menambahkan panel cek QR dan cek manual pada halaman `supervisi-opname`.
- Membuka akses supervisor opname ke endpoint lookup scan/manual tanpa memberi akses simpan qty dari panel cek.
- Menambahkan normalisasi jobdesk supervisor opname untuk variasi penulisan `SUPERVISIOR_OPNAME`, `SUPERVISOR_OPNAME`, dan format dengan spasi/tanda hubung.
- Menambahkan dokumen teknis terpisah untuk collation, supervisi scan/manual, dan edit master barang.

File utama yang berubah pada 2026-07-04:

- `application/controllers/admin/C_Stockopname.php`
- `application/models/admin/M_Stockopname.php`
- `application/views/content/admin/stockopname_master_barang_catalog.php`
- `application/views/content/admin/stockopname_supervisor.php`
- `application/views/partial/main/sidebar.php`
- `application/views/content/admin/sql-stockopname_master.sql`
- `docs/development-stockopname-collation-dimensi-20260704.md`
- `docs/development-supervisi-opname-scan-manual-20260704.md`
- `docs/development-stockopname-master-barang-edit-identitas-20260703.md`

## Development 2026-07-05

Status: perubahan lokal belum commit.

File aplikasi yang berubah:

- `application/config/routes.php`
- `application/controllers/admin/C_Stockopname.php`
- `application/models/admin/M_Stockopname.php`
- `application/views/content/admin/stockopname_barang_pending.php`
- `application/views/content/admin/stockopname_dashboard.php`
- `application/views/content/admin/stockopname_detail_input_opname.php`
- `application/views/content/admin/stockopname_input_mobile.php`
- `application/views/content/admin/stockopname_monitoring.php`
- `application/views/content/admin/stockopname_pending_opname_detail.php`
- `application/views/content/admin/stockopname_supervisor.php`
- `application/views/content/admin/stockopname_supervisor_tracking.php`

Perubahan route:

- Menambahkan route `admin/stockopname/pending-mode`.
- Menambahkan route `supervisi-opname/tracking/list`.

Perubahan pending opname:

- Menambahkan mode perhitungan pending pada session user:
  - `add`: qty master = qty dasar + pending
  - `subtract`: qty master = qty dasar - pending
- Menambahkan endpoint `ajax_set_pending_calculation_mode()`.
- Saat mode pending disimpan, sistem menjalankan resync seluruh data pending.
- Menambahkan pagination pada detail pending opname admin.
- Menambahkan pagination pada halaman kelola barang pending.
- Menambahkan pencarian pending berdasarkan kode DO/faktur, kode barang, nama barang, expired date, dan lot.
- Menampilkan `pending_totals` pada detail input opname agar admin bisa melihat qty pending per expired date.

Perubahan input user:

- Input scan QR sekarang menyimpan `input_source = scan_qrcode`.
- Input manual user sekarang disimpan lewat flow `save_manual_input_to_opname()`.
- Input manual user dicatat ke tabel manual input dan juga langsung masuk ke `stockopname_opname`.
- Input manual admin/detail membutuhkan `keterangan` untuk membentuk nilai `input_source` yang lebih informatif.

Perubahan request opname:

- Saat request item ditambahkan ke hasil opname, qty/tim/inputer diambil dari data request yang tersimpan, bukan dari payload tombol admin.
- `requested_by` dipertahankan sebagai `input_by` saat request masuk ke hasil opname.
- Request dengan status `Request Master Item` masuk sebagai `master data request opname`.
- Request manual masuk sebagai `manual_input`.

Perubahan supervisor:

- Halaman `supervisi-opname` mendapat pencarian nama barang pada daftar request opname.
- Pagination request supervisor mempertahankan filter wilayah dan keyword.
- Bagian `Daftar Request Opname` diposisikan sebelum chart `Stockopname Result`.
- Endpoint `supervisi-opname/tracking/list` menyiapkan DataTables server-side.
- Halaman `Tracking Inputer Wilayah` sekarang menggunakan ajax, pencarian, filter status `SAMA`/`RE-CHECK`, dan pagination DataTables.
- Guard controller menambahkan akses supervisor untuk `ajax_supervisor_tracking_list`.

Perubahan monitoring admin:

- Monitoring menampilkan summary input berdasarkan sumber: request, manual, dan pending.
- Detail pending opname mendukung mode pending, pencarian, pagination, lihat detail, edit, dan delete.
- Dashboard admin lama menghapus tabel rekonsiliasi besar dari halaman dashboard agar monitoring utama menjadi pusat rekonsiliasi.

## Endpoint Penting

| Endpoint | Fungsi |
| --- | --- |
| `stockopname/input` | Halaman input user stockopname |
| `stockopname/history-input` | Histori input user |
| `supervisi-opname` | Halaman supervisor |
| `supervisi-opname/afirmasi` | Afirmasi request oleh supervisor |
| `supervisi-opname/tracking` | Tracking inputer wilayah |
| `supervisi-opname/tracking/list` | DataTables tracking supervisor |
| `admin/stockopname/monitoring` | Monitoring admin |
| `admin/stockopname/pending-mode` | Simpan mode hitung pending dan resync |
| `admin/stockopname/monitoring/pending-opname` | Detail pending opname |
| `admin/stockopname/barang-pending` | Kelola barang pending |
| `admin/stockopname/detail_input_opname` | Detail/koreksi hasil input opname |
| `admin/stockopname/master_opname` | Master opname dan QR |
| `admin/stockopname/master_barang` | Master barang katalog |

## Catatan Validasi

Validasi otomatis belum dijalankan setelah pembuatan dokumen ini. Perubahan yang tercatat berasal dari inspeksi file, `git log`, dan `git diff` pada workspace lokal.

