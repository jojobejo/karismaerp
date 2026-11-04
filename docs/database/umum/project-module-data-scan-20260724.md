# Laporan Scan Database per Modul KARISMA ERP

Tanggal scan: 2026-07-24  
Mode: read-only; hanya query metadata dan `SELECT` agregat.  
Database utama untuk angka data: `kiucoid_karismaerp_local` pada MariaDB lokal.

## 1. Status sumber database

| Sumber | Status saat scan | Keterangan |
|---|---|---|
| Baseline Git `application/config/database.php` -> `u471548307_karismaerp` | Tidak tersedia di MariaDB lokal | Database tidak muncul pada `SHOW DATABASES`. Saat review akhir, working tree memiliki perubahan belum di-commit yang mengarah ke `kiucoid_karismaerp_local`; perubahan tersebut bukan dibuat oleh scan ini. |
| Working tree `application/config/database.php` -> `kiucoid_karismaerp_local` | Tersedia | Konfigurasi lokal saat review akhir sudah menunjuk database yang dipakai untuk agregat, tetapi route aplikasi belum diuji end-to-end. |
| `kiucoid_karismaerp_local` | Tersedia dan dipakai untuk agregat | 259 object: 258 tabel InnoDB dan 1 view; 2.987 kolom; 35 foreign key. |
| `kiucoid_karismaerp` | Tersedia sebagai database lokal lain | 162 object: 154 tabel InnoDB dan 8 view; diperlakukan sebagai pembanding, bukan sumber angka utama. |
| `kiucoid_karismaerp_dev` | Tersedia | 16 object; diperlakukan sebagai database pengembangan/eksperimen. |
| Dump repo `kiucoid_karismaerp_local_17072026.sql` | Snapshot file | Header dump menyatakan dibuat 2026-07-17 10:52, MariaDB 10.4.27, PHP 7.4.33. |
| Dump repo `kiucoid_karismaerp_local (13).sql` | Snapshot file | Header dump menyatakan dibuat 2026-07-18 10:02, MariaDB 10.4.27, PHP 7.4.33. |

Catatan penting: angka pada laporan ini adalah keadaan database lokal saat scan, bukan klaim keadaan database production/server. `information_schema.table_rows` InnoDB bersifat estimasi; untuk tabel yang digunakan dalam tabel modul di bawah, jumlah row dihitung dengan `COUNT(*)` langsung.

## 2. Ringkasan struktur database berdasarkan keluarga tabel

| Keluarga | Jumlah tabel | Estimasi row | Peran |
|---|---:|---:|---|
| `tbkeu_*` | 24 | 1.746 | Accounting, COA, jurnal, periode, mapping, exception, saldo awal, pembayaran. |
| `tbpo_*` | 44 | 20.076 | PO komersil/nonkomersil, request, master barang PO, supplier, setting, dan temporary flow. |
| `tbso_*` | 7 | 216 | Sales Order, faktur penjualan, detail dan reservasi stock. |
| `tbhrd_*` | 7 | 38 | Isu lingkungan, lokasi, status, rating, evidence, dan log. |
| `stockopname_*` | 9 | 5.493 | Master opname, item, opname, pending, manual, log, dan recycle bin. |
| `tberp_*` | 2 | 20.120 | Stock batch dan stock ledger. |
| `tbar_*` | 8 | 329 | KPI/AR lama, indikator What/How, dan arsip. |
| `tbsim_*` | 6 | 1.681 | Master KPI/indikator SIM. |
| `tbrp_*` | 5 | 0 | Retur penjualan modern; belum ada row pada snapshot lokal. |
| `tb_*` legacy/operasional lain | 143 | 100.902 | Auth, master, ICS, DO, loading, distribusi, retur lama, stock lama, HRD lama, dan tabel pendukung. |

## 3. Data start dan end per modul

“Data mulai” dan “data akhir” menggunakan tanggal bisnis paling representatif yang tersedia. Jika kolom tanggal berbentuk `TEXT`, nilainya diparse dari format `DD/MM/YYYY`, `D/M/YYYY`, atau `YYYY-MM-DD`; nilai placeholder `01/01/1970` diabaikan. Jika tabel master tidak mempunyai kolom tanggal, kolom periode ditulis `N/A` dan jumlah row tetap dicantumkan.

| Modul | Tabel sumber utama | Row snapshot | Data mulai | Data akhir | Interpretasi |
|---|---|---:|---|---|---|
| Auth / user | `tb_users` | 152 | 2026-06-30 11:48:12 | 2026-07-07 10:38:38 | Berdasarkan `created_at`; legacy `tb_user` memiliki 151 row, 2023-08-22 12:58:36 s.d. 2026-01-29 09:39:56. |
| Master barang | `tb_master_barang` | 5.739 | N/A | N/A | Tidak ada kolom tanggal pembuatan; master lama lain: `tb_master_barang_all` sekitar 5.982 row dan `tb_barangv2` sekitar 5.742 row. |
| Customer | `tb_customer` | 7.942 | N/A | N/A | Tidak ada kolom create date pada tabel yang dipakai. |
| Supplier | `tb_suplier` | 258 | N/A | N/A | Tidak ada kolom create date pada tabel legacy supplier. |
| Gudang/satuan/pajak | `tb_gudang`, `tb_satuan`, `tb_set_tax` | 4 / 23 / 4 | Gudang: N/A | Gudang: N/A | `tb_gudang` mempunyai 4 row; `created_at` tidak dijadikan tanggal bisnis modul. |
| PO komersil | `tbpo_po` + `tbpo_detail_po` | 3 + 5 | 2026-07-18 | 2026-07-20 | Berdasarkan `tgl_transaksi`; tabel PO komersil snapshot masih sangat kecil. |
| PO nonkomersil | `tbpo_po_nk` | 1.135 | 2024-10-17 | 2026-06-12 | Berdasarkan `tgl_transaksi` yang tersimpan sebagai TEXT dengan format campuran. |
| Request PO nonkomersil | `tbpo_req_nk` | 3.510 | 2024-10-17 | 2026-07-02 | Berdasarkan `tgl_transaksi`; terpisah dari PO nonkomersil yang sudah terbentuk. |
| ICS stock masuk/stock lama | `tb_ics` | 2.257 | 2026-01-29 | 2026-02-20 | Berdasarkan `input_at`; kolom TEXT berformat tanggal slash. |
| ICS DO | `tb_ics_do` | 5.743 | 2025-12-03 | 2026-06-04 | Berdasarkan `tgl_transaksi`; placeholder 1970 dikeluarkan. |
| ICS PO | `tb_ics_po` | 65 | 2026-01-30 | 2026-02-02 | Berdasarkan `tgl_transaksi`; tabel legacy penerimaan/PO ICS. |
| LPB header | `tb_lpb` | 8 | 2026-07-20 | 2026-07-23 | Berdasarkan `tgl_sj`; status snapshot: 4 `0` dan 4 `1`. |
| LPB detail | `tb_lpb_detail` | 9 | 2026-07-20 12:08:56 | 2026-07-23 13:56:01 | Berdasarkan `input_at`; detail harga/qty/lot. |
| LPB price adjustment | `tb_lpb_price_adjustment` | 1 | 2026-07-22 | 2026-07-22 | Berdasarkan `tanggal_adjustment`. |
| Mutasi gudang | `tb_mutasi` + `tb_detail_mutasi` | 2 + 2 | 2026-07-20 | 2026-07-20 | Berdasarkan `tgl_transaksi`; snapshot hanya berisi dua mutasi. |
| Stock batch dan ledger | `tberp_stock_batch` + `tberp_stock_ledger` | 10.079 + 10.317 | 2026-05-21 13:58:55 | 2026-07-23 14:05:49 | Berdasarkan `created_at`; ini sumber stock engine modern. |
| Saldo awal | `tb_saldo_awal` | 2.258 | N/A | N/A | Tabel tidak mempunyai tanggal transaksi yang dapat dipakai sebagai periode data. |
| Sales Order | `tbso_sales_order` + detail | 19 + 37 | 2026-06-26 | 2026-07-06 | Header berdasarkan `tanggal_transaksi`; detail berdasarkan `create_at`. Status header: 1 open, 3 siap_faktur, 2 partial, 13 completed. |
| Faktur penjualan | `tbso_faktur_penjualan` + detail | 23 + 31 | 2026-04-29 | 2026-07-07 | Header berdasarkan `tanggal_faktur`; detail berdasarkan `create_at`. |
| Delivery Order | `tb_do` + `tb_detail_do` | 9 + 5.343 | 2025-12-03 | 2026-07-07 | Header berdasarkan `tgl_pengiriman`; detail legacy mempunyai data lebih awal dan format tanggal TEXT. |
| Loading | `tb_loading_kk` + `tb_loading_lk` | 62 + 67 | 2026-05-21 | 2026-06-25 | Berdasarkan kolom `tgl`; KK/LK adalah dua jalur loading. |
| Distribusi | `tb_lap_distribusi` | 3.107 | 2025-01-03 17:05:49 | 2026-02-26 14:50:54 | Berdasarkan `create_at`; laporan perjalanan dan distribusi. |
| Retur penjualan / SPR | `tbrp_spr_header` + `tbrp_retur_penjualan_header` | 0 + 0 | Tidak ada data | Tidak ada data | Schema modern tersedia, tetapi snapshot lokal tidak berisi transaksi retur penjualan final. |
| Retur pembelian | `tb_retur_pembelian` + detail | 3 + 3 | 2026-07-22 | 2026-07-23 | Berdasarkan `tanggal_retur`; seluruh header snapshot berstatus `POSTED`. |
| Stock Opname | `stockopname_master`, `stockopname_master_item`, `stockopname_opname`, `stockopname_pending` | 670 / 3.444 / 747 / 31 | 2025-10-25 | 2026-07-13 | Master/opname lama mulai 2025-10-25; item/pending terbaru memperluas periode sampai 2026-07-13. |
| HRD penilaian lingkungan | `tbhrd_environment_issues` | 6 | 2026-05-07 18:48:38 | 2026-07-11 09:55:18 | Berdasarkan `report_datetime`; evidence 7 row, log 10 row, rating 5 row. |
| KPI | `tb_kpi_history` | 1.630 | 2026-03-06 15:28:00 | 2026-06-02 07:32:12 | Berdasarkan `created_at`; master `tb_kpi` sekitar 584 row tanpa tanggal bisnis. |
| Accounting jurnal | `tbkeu_jurnal` + detail | 9 + 27 | 2026-07-20 | 2026-07-23 | Header berdasarkan `tanggal_transaksi`, detail berdasarkan `created_at`; semua jurnal snapshot berstatus `POSTED`. |
| Accounting payment | `tbkeu_pembayaran_faktur` | 0 | Tidak ada data | Tidak ada data | Schema tersedia, tetapi tabel pembayaran faktur belum berisi row. |
| Schedule direktur | `tb_schedule_dirut` | 18 | 2025-03-18 | 2025-03-24 | Berdasarkan `tanggal`; snapshot berisi data historis lama. |
| Vehicle checklist | `tb_checklist_kendaraan` | 991 | 2026-01-05 | 2026-02-26 | Berdasarkan `tanggal_check`; detail checklist sekitar 23.952 row. |
| Maintenance truck | `tb_service_truk` | 24 | N/A | N/A | Kolom yang tersedia berupa histori kilometer/update, tetapi tidak ada kolom tanggal bisnis standar yang dipakai untuk agregat ini. |
| Feedback | `tb_feedback` | 3 | 2026-02-18 12:12:00 | 2026-02-20 02:20:33 | Berdasarkan `tanggal_buat`. |
| Extravaganza/undian | `tb_customer_list_undian`, `tb_pemenang` | 99 / 43 | N/A | N/A | Tabel tidak memiliki tanggal transaksi yang dapat dipakai konsisten. |

## 4. Tabel accounting dan relasi

Tabel accounting yang ditemukan pada database lokal antara lain:

- `tbkeu_klasifikasi_akun`, `tbkeu_sub_klasifikasi_akun`, `tbkeu_akun`;
- `tbkeu_saldo_normal`, `tbkeu_tipe_kontrol`, `tbkeu_jenis_jurnal`;
- `tbkeu_periode_fiskal`, `tbkeu_jurnal`, `tbkeu_jurnal_detail`, `tbkeu_jurnal_log`;
- `tbkeu_mapping_akun`, `tbkeu_report_rule_akun`, `tbkeu_posting_exception`;
- `tbkeu_pembayaran`, `tbkeu_pembayaran_alokasi`, `tbkeu_pembayaran_faktur`;
- `tbkeu_saldo_awal_akun`, `tbkeu_nomor_dokumen`, dan tabel import/reference.

Database lokal memiliki 35 foreign key. Relasi accounting utama sudah terlihat pada `tbkeu_akun`, jurnal, detail jurnal, periode, jenis jurnal, mapping, exception, dan payment. Sebagian besar tabel legacy operasional tetap bergantung pada relasi aplikasi/query, bukan foreign key database; hal ini penting untuk audit orphan dan konsistensi data.

## 5. Temuan kualitas data dan risiko

1. **Format tanggal tidak konsisten.** Beberapa tabel legacy menyimpan tanggal sebagai `TEXT`, misalnya `tb_ics_do.tgl_transaksi`, `tb_detail_do.tgl_transaksi`, `tb_ics_po.tgl_transaksi`, dan `tbpo_*_nk.tgl_transaksi`. Ada format slash campuran dan placeholder `01/01/1970`.
2. **Duplikasi master.** Terdapat lebih dari satu sumber master barang (`tb_master_barang`, `tb_master_barang_all`, `tb_barangv2`, `tbpo_barang`) dan lebih dari satu sumber user (`tb_user`, `tb_users`, `tb_auth`). Source of truth perlu diputuskan sebelum migrasi atau konsolidasi.
3. **Baseline dan working tree konfigurasi database berbeda.** Git baseline menunjuk `u471548307_karismaerp`, sedangkan working tree yang belum di-commit menunjuk `kiucoid_karismaerp_local`. Perubahan ini perlu diputuskan dan dikelola sebagai konfigurasi environment sebelum UAT atau demo runtime.
4. **Tabel transaksi modern masih kecil dibanding tabel legacy.** Contoh: LPB 8 header, jurnal 9 header, mutasi 2 header, dan PO komersil 3 header. Angka ini dapat berarti database UAT/snapshot parsial; jangan dianggap volume production.
5. **Retur penjualan modern kosong.** Kode flow tersedia, tetapi belum ada data untuk menguji laporan historis dari `tbrp_*`.
6. **Payment faktur kosong.** Route dan schema tersedia, namun belum ada row `tbkeu_pembayaran_faktur`; laporan pembayaran tidak dapat dianggap tervalidasi oleh data snapshot ini.
7. **Tidak ada perubahan database.** Scan tidak menjalankan migration, insert, update, delete, truncate, import dump, atau perubahan konfigurasi.

## 6. Rekomendasi tindak lanjut

1. Tetapkan database resmi untuk development/UAT, lalu samakan `application/config/database.php` dengan database tersebut melalui keputusan environment yang terdokumentasi.
2. Buat data dictionary resmi yang menandai source of truth untuk user, barang, supplier, customer, stock, PO, dan jurnal.
3. Standarkan tanggal transaksi ke tipe `DATE`/`DATETIME` pada jalur baru; buat staging/normalization khusus untuk tabel legacy TEXT dan audit nilai placeholder 1970.
4. Jalankan UAT terkontrol pada flow PO -> LPB -> stock -> jurnal, SO -> DO -> faktur, retur pembelian, mutasi, dan stock opname setelah database target tersedia.
5. Pisahkan laporan volume production dari laporan snapshot agar keputusan bisnis tidak menggunakan angka UAT sebagai kapasitas operasional.
