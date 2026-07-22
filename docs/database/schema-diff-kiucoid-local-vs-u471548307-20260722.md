# Full Scan Perbandingan Struktur Database KarismaERP - 2026-07-22

Dokumen ini membandingkan dua dump SQL tanpa import ke MySQL lokal. Fokus scan: tabel, kolom, definisi tipe data/default/null/enum, table option/collation, primary key/index, auto increment, foreign key, dan view.

## Sumber Dump

| Item | local_bram | Yoga |
| --- | --- | --- |
| File | C:\Users\bram\Downloads\kiucoid_karismaerp_local_bram.sql | C:\Users\bram\Documents\OMessenger\Received files\u471548307_karismaerp_yoga.sql |
| Database dump | kiucoid_karismaerp_local | u471548307_karismaerp |
| Waktu dump | 22 Jul 2026 pada 10.35 | 22 Jul 2026 pada 06.21 |
| Versi server | 10.4.27-MariaDB | 10.4.25-MariaDB |
| Tabel base | 257 | 251 |
| Kolom base | 2961 | 2803 |
| View real | 0 | 8 |

## Ringkasan Eksekutif

| Kategori diff | Jumlah baris diff |
| --- | --- |
| auto_increment_missing | 64 |
| column_collation_only | 6 |
| column_definition_diff | 3 |
| column_missing | 111 |
| fk_missing | 28 |
| index_missing | 87 |
| table_missing | 12 |
| table_option_diff | 247 |
| view_missing | 8 |

Catatan penting: ada 248 tabel yang sama-sama ada di kedua dump. Di antara tabel common tersebut, ditemukan 73 kolom yang hanya ada di `local_bram`, 38 kolom yang hanya ada di Yoga, 3 perbedaan tipe/default/enum yang berdampak schema, dan 6 perbedaan kolom yang hanya berupa `COLLATE` eksplisit. Ada juga 247 perbedaan table option yang hampir seluruhnya karena dump `local_bram` menulis `COLLATE=utf8mb4_general_ci`, sementara dump Yoga sering hanya menulis `DEFAULT CHARSET=utf8mb4`.

## Jika `local_bram` Dijadikan Acuan

Artinya target sinkronisasi adalah membuat Yoga sama dengan `kiucoid_karismaerp_local_bram`.

### Yang Perlu Ditambahkan ke Yoga

- Tabel baru: 9
- Kolom baru pada tabel yang sudah ada: 73
- Index/PK tambahan: 87
- Auto increment tambahan: 64
- Foreign key tambahan: 28
- Perubahan tipe/default/enum: 3 baris perlu `MODIFY`, tetapi harus ditinjau karena ada perubahan yang bisa mempersempit data.

### Yang Perlu Dikurangi dari Yoga

- Tabel hanya di Yoga: 3
- View hanya di Yoga: 8
- Kolom hanya di Yoga: 38

Catatan CEO/PM: bagian `DROP` jangan dijalankan otomatis. Objek Yoga-only bisa saja merupakan fitur production yang belum ikut ke dump local. Jadikan ini daftar audit, bukan instruksi hapus langsung.

## Jika Yoga Dijadikan Acuan

Artinya target sinkronisasi adalah membuat local sama dengan `u471548307_karismaerp`.

### Yang Perlu Ditambahkan ke Local

- Tabel hanya di Yoga: 3
- View hanya di Yoga: 8
- Kolom hanya di Yoga: 38

### Yang Perlu Dikurangi dari Local

- Tabel hanya di local: 9
- Kolom hanya di local: 73
- Index/PK local-only: 87
- Auto increment local-only: 64
- Foreign key local-only: 28

## Tabel Hanya Ada di `local_bram`

- `tb_lpb_price_adjustment`
- `tb_lpb_price_adjustment_detail`
- `tb_retur_pembelian`
- `tb_retur_pembelian_detail`
- `tb_retur_pembelian_log`
- `tb_spr_detail`
- `tb_spr_header`
- `tbso_so_approval`
- `tbso_stock_reservation`

## Tabel Hanya Ada di Yoga

- `tbpo_barang_akun`
- `tbpo_barang_bck`
- `tbso_faktur_jurnal`

## View Hanya Ada di Yoga

- `v_available_stock`
- `v_show_diff_ics`
- `v_stock_in`
- `v_stock_mutasi_in`
- `v_stock_mutasi_out`
- `v_stock_out`
- `v_stock_per_gudang`
- `v_stock_saldo_awal`

## Kolom Hanya Ada di `local_bram`

| Tabel | Kolom | Definisi |
| --- | --- | --- |
| stockopname_pending | created_at | datetime NOT NULL DEFAULT current_timestamp() |
| stockopname_pending | created_by | varchar(100) DEFAULT NULL |
| stockopname_pending | expired_date | date DEFAULT NULL |
| stockopname_pending | kode_barang | varchar(50) NOT NULL DEFAULT '' |
| stockopname_pending | qty_box | int(12) NOT NULL DEFAULT 0 |
| stockopname_pending | qty_pcs | int(12) NOT NULL DEFAULT 0 |
| stockopname_pending | updated_at | datetime DEFAULT NULL ON UPDATE current_timestamp() |
| stockopname_pending | updated_by | varchar(100) DEFAULT NULL |
| tb_detail_mutasi | no_lot | varchar(100) DEFAULT NULL |
| tb_do | sales_confirm_at | datetime DEFAULT NULL |
| tb_do | sales_confirm_by | varchar(100) DEFAULT NULL |
| tb_do | sales_confirm_note | text DEFAULT NULL |
| tb_do | sales_confirm_status | enum('pending','siap','belum_siap') DEFAULT NULL |
| tb_karyawan | akses_lv_id | int(11) DEFAULT NULL |
| tb_karyawan | created_at | datetime DEFAULT current_timestamp() |
| tb_karyawan | departemen_id | int(11) DEFAULT NULL |
| tb_karyawan | foto | varchar(255) DEFAULT NULL |
| tb_karyawan | jobdesk_id | int(11) DEFAULT NULL |
| tb_karyawan | last_login | datetime DEFAULT NULL |
| tb_karyawan | status | tinyint(1) NOT NULL DEFAULT 1 |
| tb_karyawan | status_karyawan | enum('AKTIF','NONAKTIF') DEFAULT 'AKTIF' |
| tb_karyawan | tim_id | int(11) DEFAULT NULL |
| tb_karyawan | updated_at | datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() |
| tb_karyawan | wilayah_id | varchar(225) DEFAULT NULL |
| tb_lpb | checker_at | datetime DEFAULT NULL |
| tb_lpb | checker_by | varchar(50) DEFAULT NULL |
| tb_lpb | checker_name | varchar(100) DEFAULT NULL |
| tb_lpb | jenis_lpb | varchar(80) DEFAULT NULL |
| tb_lpb | kode_faktur_pajak | varchar(100) DEFAULT NULL |
| tb_lpb | nomor_lpb | varchar(30) DEFAULT NULL |
| tb_lpb | status_lpb | tinyint(1) NOT NULL DEFAULT 1 |
| tb_lpb | tanggal_faktur_pajak | date DEFAULT NULL |
| tb_lpb | tanggal_invoice | date DEFAULT NULL |
| tb_lpb_detail | harga_satuan | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tb_lpb_detail | harga_satuan_sebelumnya | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tb_lpb_detail | harga_update_at | datetime DEFAULT NULL |
| tb_lpb_detail | harga_update_by | varchar(100) DEFAULT NULL |
| tb_lpb_detail | harga_verified_at | datetime DEFAULT NULL |
| tb_lpb_detail | harga_verified_by | varchar(100) DEFAULT NULL |
| tb_lpb_detail | total_harga | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tb_lpb_detail | total_harga_sebelumnya | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tb_lpb_log | checker_by | varchar(50) DEFAULT NULL |
| tb_lpb_log | checker_name | varchar(100) DEFAULT NULL |
| tb_lpb_log | data_after | text DEFAULT NULL |
| tb_lpb_log | data_before | text DEFAULT NULL |
| tb_lpb_log | id_lpb | int(11) DEFAULT NULL |
| tb_lpb_log | status_after | varchar(20) DEFAULT NULL |
| tb_lpb_log | status_before | varchar(20) DEFAULT NULL |
| tb_stock_hold | no_lot | varchar(100) DEFAULT NULL |
| tb_tmp_mutasi | gudang_asal | int(11) DEFAULT NULL |
| tb_tmp_mutasi | kode_barang | varchar(50) DEFAULT NULL |
| tb_tmp_mutasi | kode_barang_system | varchar(50) DEFAULT NULL |
| tb_tmp_mutasi | no_lot | varchar(100) DEFAULT NULL |
| tb_tmp_po_received | harga_satuan | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tb_tmp_po_received | harga_satuan_kecil | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tb_tmp_po_received | total_harga | decimal(18,4) NOT NULL DEFAULT 0.0000 |
| tbhrd_environment_issues | star_rating | tinyint(1) NOT NULL DEFAULT 0 |
| tbrp_retur_penjualan_header | admin_stock_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | admin_stock_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_admin_stock | text DEFAULT NULL |
| tbrp_spr_header | admin_stock_at | datetime DEFAULT NULL |
| tbrp_spr_header | admin_stock_by | varchar(150) DEFAULT NULL |
| tbrp_spr_header | admin_stock_catatan | text DEFAULT NULL |
| tbrp_spr_header | koor_sc_at | datetime DEFAULT NULL |
| tbrp_spr_header | koor_sc_by | varchar(150) DEFAULT NULL |
| tbrp_spr_header | koor_sc_catatan | text DEFAULT NULL |
| tbso_sales_order | approve_by | varchar(100) DEFAULT NULL |
| tbso_sales_order | no_faktur | varchar(30) DEFAULT NULL |
| tbso_sales_order_detail | approve_by | varchar(100) DEFAULT NULL |
| tbso_sales_order_detail | is_nego | tinyint(1) NOT NULL DEFAULT 0 |
| tbso_sales_order_detail | no_faktur | varchar(30) DEFAULT NULL |
| tbso_sales_order_detail | qty_delivered | decimal(15,3) NOT NULL DEFAULT 0.000 |
| tbso_sales_order_detail | ref_no | varchar(100) DEFAULT NULL |

## Kolom Hanya Ada di Yoga

| Tabel | Kolom | Definisi |
| --- | --- | --- |
| tb_karyawan | faktur_prefix | varchar(4) DEFAULT NULL |
| tbrp_retur_penjualan_header | admretur_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | admretur_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_admretur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_dirop_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_dirut_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_kadepsc_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_kadepub_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_koorsc_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_mngacc_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_mngsc_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | catatan_mngse_retur | text DEFAULT NULL |
| tbrp_retur_penjualan_header | dirop_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | dirop_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | dirut_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | dirut_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | gudang_id | int(11) DEFAULT NULL |
| tbrp_retur_penjualan_header | kadepsc_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | kadepsc_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | kadepub_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | kadepub_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | koorsc_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | koorsc_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | mngacc_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | mngacc_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | mngsc_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | mngsc_by_retur | varchar(150) DEFAULT NULL |
| tbrp_retur_penjualan_header | mngse_at_retur | datetime DEFAULT NULL |
| tbrp_retur_penjualan_header | mngse_by_retur | varchar(150) DEFAULT NULL |
| tbrp_spr_header | admretur_at | datetime DEFAULT NULL |
| tbrp_spr_header | admretur_by | varchar(150) DEFAULT NULL |
| tbrp_spr_header | admretur_catatan | text DEFAULT NULL |
| tbrp_spr_header | kadepub_at | datetime DEFAULT NULL |
| tbrp_spr_header | kadepub_by | varchar(150) DEFAULT NULL |
| tbrp_spr_header | kadepub_catatan | text DEFAULT NULL |
| tbrp_spr_header | mngsc_at | datetime DEFAULT NULL |
| tbrp_spr_header | mngsc_by | varchar(150) DEFAULT NULL |
| tbrp_spr_header | mngsc_catatan | text DEFAULT NULL |

## Perbedaan Tipe Data / Default / Enum

| Tabel | Nama | local_bram | Yoga | Catatan |
| --- | --- | --- | --- | --- |
| tb_lpb_log | action_type | varchar(50) NOT NULL | enum('CREATE_INVOICE','UPDATE_INVOICE') NOT NULL | Perbedaan tipe/default/null/enum/length yang berdampak schema |
| tbkeu_pembayaran_faktur | metode_pembayaran | varchar(30) DEFAULT NULL | varchar(100) DEFAULT NULL | Perbedaan tipe/default/null/enum/length yang berdampak schema |
| tbrp_retur_penjualan_header | status_retur | enum('menunggu_verifikasi','terverifikasi','menunggu_collection','menunggu_kasir','selesai','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi' | enum('menunggu_verifikasi','retur_menunggu_kadepub','retur_menunggu_mngacc','retur_menunggu_mngsc','retur_menunggu_mngse','retur_menunggu_kadepsc','retur_menunggu_dirop','retur_menunggu_dirut','menunggu_collection','menunggu_kasir','selesai','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi' | Perbedaan tipe/default/null/enum/length yang berdampak schema |

Rekomendasi awal:

- `tb_lpb_log.action_type`: `local_bram` memakai `varchar(50)`, Yoga memakai enum hanya untuk invoice. Bila modul log bertambah jenis aksi, `varchar(50)` lebih fleksibel.
- `tbkeu_pembayaran_faktur.metode_pembayaran`: Yoga `varchar(100)`, local `varchar(30)`. Menyamakan ke local berarti mempersempit field dan berisiko data terpotong.
- `tbrp_retur_penjualan_header.status_retur`: Yoga memiliki enum workflow approval lebih panjang. Mengurangi enum di Yoga berisiko membuat data status existing tidak valid.

## Perbedaan Kolom Hanya Karena Collation

| Tabel | Nama | local_bram | Yoga | Catatan |
| --- | --- | --- | --- | --- |
| cctv_tracking | ip_kamera | varchar(45) NOT NULL | varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL | Perbedaan hanya COLLATE eksplisit pada kolom |
| cctv_tracking | keterangan | text DEFAULT NULL | text COLLATE utf8mb4_unicode_ci DEFAULT NULL | Perbedaan hanya COLLATE eksplisit pada kolom |
| cctv_tracking | lokasi | varchar(100) NOT NULL | varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL | Perbedaan hanya COLLATE eksplisit pada kolom |
| cctv_tracking | nama_kamera | varchar(100) NOT NULL | varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL | Perbedaan hanya COLLATE eksplisit pada kolom |
| cctv_tracking | status | enum('Online','Offline') NOT NULL DEFAULT 'Offline' | enum('Online','Offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Offline' | Perbedaan hanya COLLATE eksplisit pada kolom |
| cctv_tracking | status_rekaman | enum('Terekam','Tidak') NOT NULL DEFAULT 'Tidak' | enum('Terekam','Tidak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tidak' | Perbedaan hanya COLLATE eksplisit pada kolom |

## Index / Primary Key / Unique Key Berbeda

| Tabel | Object | Definisi local_bram | Definisi Yoga |
| --- | --- | --- | --- |
| tb_do | ADD KEY idx_tb_do_sales_confirm (sales_confirm_status,status) | ADD KEY idx_tb_do_sales_confirm (sales_confirm_status,status) |  |
| tb_lpb_detail | ADD KEY idx_lpb_detail_lpb_barang (id_lpb,kd_barang) | ADD KEY idx_lpb_detail_lpb_barang (id_lpb,kd_barang) |  |
| tbhrd_environment_issues | ADD KEY idx_tbhrd_environment_issues_star_rating (star_rating) | ADD KEY idx_tbhrd_environment_issues_star_rating (star_rating) |  |
| tbkeu_periode_fiskal | ADD KEY idx_tbkeu_periode_status (status,is_active) | ADD KEY idx_tbkeu_periode_status (status,is_active) |  |
| tbkeu_periode_fiskal | ADD KEY idx_tbkeu_periode_tanggal (tanggal_mulai,tanggal_selesai) | ADD KEY idx_tbkeu_periode_tanggal (tanggal_mulai,tanggal_selesai) |  |
| tbkeu_periode_fiskal | ADD PRIMARY KEY (id_periode) | ADD PRIMARY KEY (id_periode) |  |
| tbkeu_periode_fiskal | ADD UNIQUE KEY uk_tbkeu_periode_kode (kode_periode) | ADD UNIQUE KEY uk_tbkeu_periode_kode (kode_periode) |  |
| tbkeu_periode_fiskal_log | ADD KEY idx_tbkeu_periode_log_action (action,approval_at) | ADD KEY idx_tbkeu_periode_log_action (action,approval_at) |  |
| tbkeu_periode_fiskal_log | ADD KEY idx_tbkeu_periode_log_periode (id_periode) | ADD KEY idx_tbkeu_periode_log_periode (id_periode) |  |
| tbkeu_periode_fiskal_log | ADD PRIMARY KEY (id_log) | ADD PRIMARY KEY (id_log) |  |
| tbkeu_posting_exception | ADD KEY idx_tbkeu_exception_jurnal (id_jurnal) | ADD KEY idx_tbkeu_exception_jurnal (id_jurnal) |  |
| tbkeu_posting_exception | ADD KEY idx_tbkeu_exception_source (source_module,source_type,source_id,posting_event) | ADD KEY idx_tbkeu_exception_source (source_module,source_type,source_id,posting_event) |  |
| tbkeu_posting_exception | ADD KEY idx_tbkeu_exception_status (status,created_at) | ADD KEY idx_tbkeu_exception_status (status,created_at) |  |
| tbkeu_posting_exception | ADD PRIMARY KEY (id_exception) | ADD PRIMARY KEY (id_exception) |  |
| tbkeu_report_rule_akun | ADD KEY fk_tbkeu_report_rule_akun (id_akun) | ADD KEY fk_tbkeu_report_rule_akun (id_akun) |  |
| tbkeu_report_rule_akun | ADD KEY idx_tbkeu_report_rule_batch (id_batch) | ADD KEY idx_tbkeu_report_rule_batch (id_batch) |  |
| tbkeu_report_rule_akun | ADD KEY idx_tbkeu_report_rule_code (kode_rekening_normalized) | ADD KEY idx_tbkeu_report_rule_code (kode_rekening_normalized) |  |
| tbkeu_report_rule_akun | ADD KEY idx_tbkeu_report_rule_statement_order (statement_type,urutan) | ADD KEY idx_tbkeu_report_rule_statement_order (statement_type,urutan) |  |
| tbkeu_report_rule_akun | ADD PRIMARY KEY (id_rule) | ADD PRIMARY KEY (id_rule) |  |
| tbkeu_report_rule_akun | ADD UNIQUE KEY uk_tbkeu_report_rule_statement_code (statement_type,kode_rekening_normalized) | ADD UNIQUE KEY uk_tbkeu_report_rule_statement_code (statement_type,kode_rekening_normalized) |  |
| tbkeu_saldo_awal_akun | ADD PRIMARY KEY (id_saldo_awal) | ADD PRIMARY KEY (id_saldo_awal) |  |
| tbkeu_saldo_awal_akun | ADD UNIQUE KEY uk_tbkeu_saldo_awal (id_akun,tanggal_saldo) | ADD UNIQUE KEY uk_tbkeu_saldo_awal (id_akun,tanggal_saldo) |  |
| tbkeu_saldo_normal | ADD PRIMARY KEY (kode_saldo) | ADD PRIMARY KEY (kode_saldo) |  |
| tbkeu_sub_klasifikasi_akun | ADD KEY idx_tbkeu_sub_klasifikasi_batch (id_batch) | ADD KEY idx_tbkeu_sub_klasifikasi_batch (id_batch) |  |
| tbkeu_sub_klasifikasi_akun | ADD KEY idx_tbkeu_sub_klasifikasi_header (id_akun_header) | ADD KEY idx_tbkeu_sub_klasifikasi_header (id_akun_header) |  |
| tbkeu_sub_klasifikasi_akun | ADD KEY idx_tbkeu_sub_klasifikasi_klasifikasi (id_klasifikasi) | ADD KEY idx_tbkeu_sub_klasifikasi_klasifikasi (id_klasifikasi) |  |
| tbkeu_sub_klasifikasi_akun | ADD PRIMARY KEY (id_sub_klasifikasi) | ADD PRIMARY KEY (id_sub_klasifikasi) |  |
| tbkeu_sub_klasifikasi_akun | ADD UNIQUE KEY uk_tbkeu_sub_klasifikasi_kode (kode_sub_klasifikasi) | ADD UNIQUE KEY uk_tbkeu_sub_klasifikasi_kode (kode_sub_klasifikasi) |  |
| tbkeu_tipe_kontrol | ADD PRIMARY KEY (kode_tipe_kontrol) | ADD PRIMARY KEY (kode_tipe_kontrol) |  |
| tbpo_akun_tr | ADD PRIMARY KEY (id_akun) | ADD PRIMARY KEY (id_akun) |  |
| tbpo_barang_nk | ADD KEY idx_barang_nk_kd_barang (kd_barang) | ADD KEY idx_barang_nk_kd_barang (kd_barang) |  |
| tbpo_barang_nk | ADD KEY idx_barang_nk_kd_lokasi (kd_lokasi) | ADD KEY idx_barang_nk_kd_lokasi (kd_lokasi) |  |
| tbpo_barang_nk | ADD PRIMARY KEY (id_brg_nk) | ADD PRIMARY KEY (id_brg_nk) |  |
| tbpo_barang_nk_lokasi | ADD PRIMARY KEY (id_lokasi) | ADD PRIMARY KEY (id_lokasi) |  |
| tbpo_barang_packaging | ADD PRIMARY KEY (id_packaging) | ADD PRIMARY KEY (id_packaging) |  |
| tbpo_detail_po | ADD PRIMARY KEY (id_det_po) | ADD PRIMARY KEY (id_det_po) |  |
| tbpo_detail_po_nk | ADD PRIMARY KEY (id_det_po_nk) | ADD PRIMARY KEY (id_det_po_nk) |  |
| tbpo_detail_req | ADD PRIMARY KEY (id_det_po_nk) | ADD PRIMARY KEY (id_det_po_nk) |  |
| tbpo_diskon | ADD PRIMARY KEY (id_diskon) | ADD PRIMARY KEY (id_diskon) |  |
| tbpo_diskon_merk | ADD KEY idx_merk_barang (merk_barang) | ADD KEY idx_merk_barang (merk_barang) |  |
| tbpo_diskon_merk | ADD KEY idx_no_po (no_po) | ADD KEY idx_no_po (no_po) |  |
| tbpo_diskon_merk | ADD PRIMARY KEY (id_diskon) | ADD PRIMARY KEY (id_diskon) |  |
| tbpo_file_bukti_beli | ADD PRIMARY KEY (id_fk_bukti) | ADD PRIMARY KEY (id_fk_bukti) |  |
| tbpo_file_nk | ADD PRIMARY KEY (id_file_nk) | ADD PRIMARY KEY (id_file_nk) |  |
| tbpo_formula | ADD PRIMARY KEY (id_formula) | ADD PRIMARY KEY (id_formula) |  |
| tbpo_formula | ADD UNIQUE KEY kode_formula (kode_formula) | ADD UNIQUE KEY kode_formula (kode_formula) |  |
| tbpo_formula_result | ADD KEY id_formula (id_formula) | ADD KEY id_formula (id_formula) |  |
| tbpo_formula_result | ADD PRIMARY KEY (id_result) | ADD PRIMARY KEY (id_result) |  |
| tbpo_formula_variable | ADD KEY id_formula (id_formula) | ADD KEY id_formula (id_formula) |  |
| tbpo_formula_variable | ADD PRIMARY KEY (id_variable) | ADD PRIMARY KEY (id_variable) |  |
| tbpo_generate_kd | ADD PRIMARY KEY (id) | ADD PRIMARY KEY (id) |  |
| tbpo_generate_kd_ponk | ADD PRIMARY KEY (id) | ADD PRIMARY KEY (id) |  |
| tbpo_generateqrcode | ADD PRIMARY KEY (id_gqrcode) | ADD PRIMARY KEY (id_gqrcode) |  |
| tbpo_kat_br | ADD PRIMARY KEY (id_kat_br) | ADD PRIMARY KEY (id_kat_br) |  |
| tbpo_note_barang | ADD PRIMARY KEY (id_nt_barang) | ADD PRIMARY KEY (id_nt_barang) |  |
| tbpo_note_direktur | ADD PRIMARY KEY (id_note) | ADD PRIMARY KEY (id_note) |  |
| tbpo_note_pembelian | ADD PRIMARY KEY (id_nt_pembelian) | ADD PRIMARY KEY (id_nt_pembelian) |  |
| tbpo_notetemplate | ADD PRIMARY KEY (id_nt_template) | ADD PRIMARY KEY (id_nt_template) |  |
| tbpo_nt_tmp_pembelian | ADD PRIMARY KEY (id_tmp_nt_pembelian) | ADD PRIMARY KEY (id_tmp_nt_pembelian) |  |
| tbpo_po | ADD PRIMARY KEY (id_po) | ADD PRIMARY KEY (id_po) |  |
| tbpo_po_nk | ADD PRIMARY KEY (id_po_nk) | ADD PRIMARY KEY (id_po_nk) |  |
| tbpo_ratings | ADD PRIMARY KEY (id_rating) | ADD PRIMARY KEY (id_rating) |  |
| tbpo_req_masterbarang | ADD PRIMARY KEY (id_reqmbarang) | ADD PRIMARY KEY (id_reqmbarang) |  |
| tbpo_req_nk | ADD PRIMARY KEY (id_po_nk) | ADD PRIMARY KEY (id_po_nk) |  |
| tbpo_satuan | ADD PRIMARY KEY (id_satuan) | ADD PRIMARY KEY (id_satuan) |  |
| tbpo_set_note | ADD PRIMARY KEY (id_set_note) | ADD PRIMARY KEY (id_set_note) |  |
| tbpo_set_tax | ADD PRIMARY KEY (id_tax) | ADD PRIMARY KEY (id_tax) |  |
| tbpo_sosialisasi | ADD PRIMARY KEY (id) | ADD PRIMARY KEY (id) |  |
| tbpo_suplier | ADD PRIMARY KEY (id_suplier) | ADD PRIMARY KEY (id_suplier) |  |
| tbpo_tmp_diskon | ADD PRIMARY KEY (id_tmp_diskon) | ADD PRIMARY KEY (id_tmp_diskon) |  |
| tbpo_tmp_item | ADD PRIMARY KEY (id_tmp) | ADD PRIMARY KEY (id_tmp) |  |
| tbpo_tmp_item_nk | ADD PRIMARY KEY (id_tmp_nk) | ADD PRIMARY KEY (id_tmp_nk) |  |
| tbpo_tmp_note_barang | ADD PRIMARY KEY (id_nt_tmp_barang) | ADD PRIMARY KEY (id_nt_tmp_barang) |  |
| tbpo_tmp_tax | ADD PRIMARY KEY (id_tmp_tax) | ADD PRIMARY KEY (id_tmp_tax) |  |
| tbpo_tracking_po | ADD PRIMARY KEY (id_po_tracking) | ADD PRIMARY KEY (id_po_tracking) |  |
| tbpo_transaksi | ADD KEY idx_transaksi_barang_akun (kd_barang,kd_akun) | ADD KEY idx_transaksi_barang_akun (kd_barang,kd_akun) |  |
| tbpo_transaksi | ADD KEY idx_transaksi_barang_tanggal (kd_barang,tgl_transaksi(10)) | ADD KEY idx_transaksi_barang_tanggal (kd_barang,tgl_transaksi(10)) |  |
| tbpo_transaksi | ADD PRIMARY KEY (id_transnk) | ADD PRIMARY KEY (id_transnk) |  |
| tbpo_transaksi_tmp | ADD PRIMARY KEY (id_transnk) | ADD PRIMARY KEY (id_transnk) |  |
| tbpo_transaksi_trashbin | ADD PRIMARY KEY (id_trashbin) | ADD PRIMARY KEY (id_trashbin) |  |
| tbpo_user | ADD PRIMARY KEY (id_user) | ADD PRIMARY KEY (id_user) |  |
| tbq_module | ADD PRIMARY KEY (id_qmodule) | ADD PRIMARY KEY (id_qmodule) |  |
| tbq_nilaim | ADD PRIMARY KEY (id) | ADD PRIMARY KEY (id) |  |
| tbq_review_pic | ADD PRIMARY KEY (id_review) | ADD PRIMARY KEY (id_review) |  |
| tbq_review_q | ADD PRIMARY KEY (id_reviewq) | ADD PRIMARY KEY (id_reviewq) |  |
| tbso_sales_order | ADD UNIQUE KEY uk_tbso_sales_order_no_faktur (no_faktur) | ADD UNIQUE KEY uk_tbso_sales_order_no_faktur (no_faktur) |  |
| tbso_sales_order_detail | ADD KEY idx_sod_no_faktur (no_faktur) | ADD KEY idx_sod_no_faktur (no_faktur) |  |

## Auto Increment Berbeda

| Tabel | Object | Definisi local_bram | Definisi Yoga |
| --- | --- | --- | --- |
| tbhrd_nilai_lingkungan | MODIFY id bigint(20) NOT NULL AUTO_INCREMENT | MODIFY id bigint(20) NOT NULL AUTO_INCREMENT |  |
| tbkeu_akun | MODIFY id_akun bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_akun bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_akun_karismaerp_ref | MODIFY id_ref bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_ref bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_dummy_source | MODIFY id_dummy bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_dummy bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_jenis_jurnal | MODIFY id_jenis_jurnal smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_jenis_jurnal smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_karismaerp_import_batch | MODIFY id_batch bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_batch bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_karismaerp_kelompok_jurnal | MODIFY id_kelompok_karismaerp bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_kelompok_karismaerp bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_mapping_akun | MODIFY id_mapping bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_mapping bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_nomor_dokumen | MODIFY id_nomor bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_nomor bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_pembayaran | MODIFY id_pembayaran bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_pembayaran bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_pembayaran_alokasi | MODIFY id_alokasi bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_alokasi bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_periode_fiskal | MODIFY id_periode bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_periode bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_periode_fiskal_log | MODIFY id_log bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_log bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_posting_exception | MODIFY id_exception bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_exception bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_report_rule_akun | MODIFY id_rule bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_rule bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_saldo_awal_akun | MODIFY id_saldo_awal bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_saldo_awal bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbkeu_sub_klasifikasi_akun | MODIFY id_sub_klasifikasi bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT | MODIFY id_sub_klasifikasi bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT |  |
| tbpo_akun_tr | MODIFY id_akun int(11) NOT NULL AUTO_INCREMENT | MODIFY id_akun int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_barang_nk | MODIFY id_brg_nk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_brg_nk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_barang_nk_lokasi | MODIFY id_lokasi int(11) NOT NULL AUTO_INCREMENT | MODIFY id_lokasi int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_barang_packaging | MODIFY id_packaging int(11) NOT NULL AUTO_INCREMENT | MODIFY id_packaging int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_detail_po | MODIFY id_det_po int(11) NOT NULL AUTO_INCREMENT | MODIFY id_det_po int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_detail_po_nk | MODIFY id_det_po_nk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_det_po_nk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_detail_req | MODIFY id_det_po_nk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_det_po_nk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_diskon | MODIFY id_diskon int(11) NOT NULL AUTO_INCREMENT | MODIFY id_diskon int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_diskon_merk | MODIFY id_diskon int(11) NOT NULL AUTO_INCREMENT | MODIFY id_diskon int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_file_bukti_beli | MODIFY id_fk_bukti int(11) NOT NULL AUTO_INCREMENT | MODIFY id_fk_bukti int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_file_nk | MODIFY id_file_nk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_file_nk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_formula | MODIFY id_formula int(11) NOT NULL AUTO_INCREMENT | MODIFY id_formula int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_formula_result | MODIFY id_result int(11) NOT NULL AUTO_INCREMENT | MODIFY id_result int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_formula_variable | MODIFY id_variable int(11) NOT NULL AUTO_INCREMENT | MODIFY id_variable int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_generate_kd | MODIFY id int(11) NOT NULL AUTO_INCREMENT | MODIFY id int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_generate_kd_ponk | MODIFY id int(11) NOT NULL AUTO_INCREMENT | MODIFY id int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_generateqrcode | MODIFY id_gqrcode int(11) NOT NULL AUTO_INCREMENT | MODIFY id_gqrcode int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_kat_br | MODIFY id_kat_br int(11) NOT NULL AUTO_INCREMENT | MODIFY id_kat_br int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_note_barang | MODIFY id_nt_barang int(25) NOT NULL AUTO_INCREMENT | MODIFY id_nt_barang int(25) NOT NULL AUTO_INCREMENT |  |
| tbpo_note_direktur | MODIFY id_note int(11) NOT NULL AUTO_INCREMENT | MODIFY id_note int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_note_pembelian | MODIFY id_nt_pembelian int(11) NOT NULL AUTO_INCREMENT | MODIFY id_nt_pembelian int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_notetemplate | MODIFY id_nt_template int(12) NOT NULL AUTO_INCREMENT | MODIFY id_nt_template int(12) NOT NULL AUTO_INCREMENT |  |
| tbpo_nt_tmp_pembelian | MODIFY id_tmp_nt_pembelian int(12) NOT NULL AUTO_INCREMENT | MODIFY id_tmp_nt_pembelian int(12) NOT NULL AUTO_INCREMENT |  |
| tbpo_po | MODIFY id_po int(11) NOT NULL AUTO_INCREMENT | MODIFY id_po int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_po_nk | MODIFY id_po_nk int(12) NOT NULL AUTO_INCREMENT | MODIFY id_po_nk int(12) NOT NULL AUTO_INCREMENT |  |
| tbpo_ratings | MODIFY id_rating int(11) NOT NULL AUTO_INCREMENT | MODIFY id_rating int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_req_masterbarang | MODIFY id_reqmbarang int(11) NOT NULL AUTO_INCREMENT | MODIFY id_reqmbarang int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_req_nk | MODIFY id_po_nk int(12) NOT NULL AUTO_INCREMENT | MODIFY id_po_nk int(12) NOT NULL AUTO_INCREMENT |  |
| tbpo_satuan | MODIFY id_satuan int(5) NOT NULL AUTO_INCREMENT | MODIFY id_satuan int(5) NOT NULL AUTO_INCREMENT |  |
| tbpo_set_note | MODIFY id_set_note int(5) NOT NULL AUTO_INCREMENT | MODIFY id_set_note int(5) NOT NULL AUTO_INCREMENT |  |
| tbpo_set_tax | MODIFY id_tax int(5) NOT NULL AUTO_INCREMENT | MODIFY id_tax int(5) NOT NULL AUTO_INCREMENT |  |
| tbpo_sosialisasi | MODIFY id int(11) NOT NULL AUTO_INCREMENT | MODIFY id int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_suplier | MODIFY id_suplier int(11) NOT NULL AUTO_INCREMENT | MODIFY id_suplier int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_tmp_diskon | MODIFY id_tmp_diskon int(11) NOT NULL AUTO_INCREMENT | MODIFY id_tmp_diskon int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_tmp_item | MODIFY id_tmp int(11) NOT NULL AUTO_INCREMENT | MODIFY id_tmp int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_tmp_item_nk | MODIFY id_tmp_nk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_tmp_nk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_tmp_note_barang | MODIFY id_nt_tmp_barang int(11) NOT NULL AUTO_INCREMENT | MODIFY id_nt_tmp_barang int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_tmp_tax | MODIFY id_tmp_tax int(11) NOT NULL AUTO_INCREMENT | MODIFY id_tmp_tax int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_tracking_po | MODIFY id_po_tracking int(11) NOT NULL AUTO_INCREMENT | MODIFY id_po_tracking int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_transaksi | MODIFY id_transnk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_transnk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_transaksi_tmp | MODIFY id_transnk int(11) NOT NULL AUTO_INCREMENT | MODIFY id_transnk int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_transaksi_trashbin | MODIFY id_trashbin int(11) NOT NULL AUTO_INCREMENT | MODIFY id_trashbin int(11) NOT NULL AUTO_INCREMENT |  |
| tbpo_user | MODIFY id_user int(12) NOT NULL AUTO_INCREMENT | MODIFY id_user int(12) NOT NULL AUTO_INCREMENT |  |
| tbq_module | MODIFY id_qmodule int(11) NOT NULL AUTO_INCREMENT | MODIFY id_qmodule int(11) NOT NULL AUTO_INCREMENT |  |
| tbq_nilaim | MODIFY id int(11) NOT NULL AUTO_INCREMENT | MODIFY id int(11) NOT NULL AUTO_INCREMENT |  |
| tbq_review_pic | MODIFY id_review int(11) NOT NULL AUTO_INCREMENT | MODIFY id_review int(11) NOT NULL AUTO_INCREMENT |  |
| tbq_review_q | MODIFY id_reviewq int(11) NOT NULL AUTO_INCREMENT | MODIFY id_reviewq int(11) NOT NULL AUTO_INCREMENT |  |

## Foreign Key Berbeda

| Tabel | Object | Definisi local_bram | Definisi Yoga |
| --- | --- | --- | --- |
| tbhrd_nilai_lingkungan | ADD CONSTRAINT tbhrd_nilai_lingkungan_fk_location FOREIGN KEY (location_id) REFERENCES tbhrd_lokasi (id) | ADD CONSTRAINT tbhrd_nilai_lingkungan_fk_location FOREIGN KEY (location_id) REFERENCES tbhrd_lokasi (id) |  |
| tbhrd_nilai_lingkungan | ADD CONSTRAINT tbhrd_nilai_lingkungan_fk_rating FOREIGN KEY (rating_id) REFERENCES tbhrd_issue_rating (id) | ADD CONSTRAINT tbhrd_nilai_lingkungan_fk_rating FOREIGN KEY (rating_id) REFERENCES tbhrd_issue_rating (id) |  |
| tbhrd_nilai_lingkungan | ADD CONSTRAINT tbhrd_nilai_lingkungan_fk_status FOREIGN KEY (status_id) REFERENCES tbhrd_issue_status (id) | ADD CONSTRAINT tbhrd_nilai_lingkungan_fk_status FOREIGN KEY (status_id) REFERENCES tbhrd_issue_status (id) |  |
| tbkeu_akun | ADD CONSTRAINT fk_tbkeu_akun_klasifikasi FOREIGN KEY (id_klasifikasi) REFERENCES tbkeu_klasifikasi_akun (id_klasifikasi) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_akun_klasifikasi FOREIGN KEY (id_klasifikasi) REFERENCES tbkeu_klasifikasi_akun (id_klasifikasi) ON UPDATE CASCADE |  |
| tbkeu_akun | ADD CONSTRAINT fk_tbkeu_akun_parent FOREIGN KEY (parent_id) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_akun_parent FOREIGN KEY (parent_id) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE |  |
| tbkeu_akun_karismaerp_ref | ADD CONSTRAINT fk_tbkeu_akun_karismaerp_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_akun_karismaerp_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_akun_karismaerp_ref | ADD CONSTRAINT fk_tbkeu_akun_karismaerp_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_akun_karismaerp_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_akun_karismaerp_ref | ADD CONSTRAINT fk_tbkeu_akun_karismaerp_sub FOREIGN KEY (id_sub_klasifikasi) REFERENCES tbkeu_sub_klasifikasi_akun (id_sub_klasifikasi) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_akun_karismaerp_sub FOREIGN KEY (id_sub_klasifikasi) REFERENCES tbkeu_sub_klasifikasi_akun (id_sub_klasifikasi) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_jurnal | ADD CONSTRAINT fk_tbkeu_jurnal_jenis FOREIGN KEY (id_jenis_jurnal) REFERENCES tbkeu_jenis_jurnal (id_jenis_jurnal) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_jurnal_jenis FOREIGN KEY (id_jenis_jurnal) REFERENCES tbkeu_jenis_jurnal (id_jenis_jurnal) ON UPDATE CASCADE |  |
| tbkeu_jurnal | ADD CONSTRAINT fk_tbkeu_jurnal_periode FOREIGN KEY (id_periode) REFERENCES tbkeu_periode_fiskal (id_periode) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_jurnal_periode FOREIGN KEY (id_periode) REFERENCES tbkeu_periode_fiskal (id_periode) ON UPDATE CASCADE |  |
| tbkeu_jurnal | ADD CONSTRAINT fk_tbkeu_jurnal_reversal FOREIGN KEY (reversal_of_journal_id) REFERENCES tbkeu_jurnal (id_jurnal) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_jurnal_reversal FOREIGN KEY (reversal_of_journal_id) REFERENCES tbkeu_jurnal (id_jurnal) ON UPDATE CASCADE |  |
| tbkeu_jurnal_detail | ADD CONSTRAINT fk_tbkeu_jurnal_detail_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_jurnal_detail_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE |  |
| tbkeu_jurnal_detail | ADD CONSTRAINT fk_tbkeu_jurnal_detail_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_jurnal_detail_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON UPDATE CASCADE |  |
| tbkeu_jurnal_log | ADD CONSTRAINT fk_tbkeu_jurnal_log_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_jurnal_log_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON UPDATE CASCADE |  |
| tbkeu_karismaerp_kelompok_jurnal | ADD CONSTRAINT fk_tbkeu_karismaerp_kelompok_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_karismaerp_kelompok_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_mapping_akun | ADD CONSTRAINT fk_tbkeu_mapping_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_mapping_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE |  |
| tbkeu_pembayaran | ADD CONSTRAINT fk_tbkeu_pembayaran_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_pembayaran_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_pembayaran_alokasi | ADD CONSTRAINT fk_tbkeu_payment_alloc_payment FOREIGN KEY (id_pembayaran) REFERENCES tbkeu_pembayaran (id_pembayaran) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_payment_alloc_payment FOREIGN KEY (id_pembayaran) REFERENCES tbkeu_pembayaran (id_pembayaran) ON UPDATE CASCADE |  |
| tbkeu_periode_fiskal_log | ADD CONSTRAINT fk_tbkeu_periode_log_periode FOREIGN KEY (id_periode) REFERENCES tbkeu_periode_fiskal (id_periode) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_periode_log_periode FOREIGN KEY (id_periode) REFERENCES tbkeu_periode_fiskal (id_periode) ON UPDATE CASCADE |  |
| tbkeu_posting_exception | ADD CONSTRAINT fk_tbkeu_exception_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_exception_jurnal FOREIGN KEY (id_jurnal) REFERENCES tbkeu_jurnal (id_jurnal) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_report_rule_akun | ADD CONSTRAINT fk_tbkeu_report_rule_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_report_rule_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_report_rule_akun | ADD CONSTRAINT fk_tbkeu_report_rule_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_report_rule_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_saldo_awal_akun | ADD CONSTRAINT fk_tbkeu_saldo_awal_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_saldo_awal_akun FOREIGN KEY (id_akun) REFERENCES tbkeu_akun (id_akun) ON UPDATE CASCADE |  |
| tbkeu_sub_klasifikasi_akun | ADD CONSTRAINT fk_tbkeu_sub_klasifikasi_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_sub_klasifikasi_batch FOREIGN KEY (id_batch) REFERENCES tbkeu_karismaerp_import_batch (id_batch) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_sub_klasifikasi_akun | ADD CONSTRAINT fk_tbkeu_sub_klasifikasi_header FOREIGN KEY (id_akun_header) REFERENCES tbkeu_akun (id_akun) ON DELETE SET NULL ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_sub_klasifikasi_header FOREIGN KEY (id_akun_header) REFERENCES tbkeu_akun (id_akun) ON DELETE SET NULL ON UPDATE CASCADE |  |
| tbkeu_sub_klasifikasi_akun | ADD CONSTRAINT fk_tbkeu_sub_klasifikasi_klasifikasi FOREIGN KEY (id_klasifikasi) REFERENCES tbkeu_klasifikasi_akun (id_klasifikasi) ON UPDATE CASCADE | ADD CONSTRAINT fk_tbkeu_sub_klasifikasi_klasifikasi FOREIGN KEY (id_klasifikasi) REFERENCES tbkeu_klasifikasi_akun (id_klasifikasi) ON UPDATE CASCADE |  |
| tbpo_formula_result | ADD CONSTRAINT tbpo_formula_result_ibfk_1 FOREIGN KEY (id_formula) REFERENCES tbpo_formula (id_formula) | ADD CONSTRAINT tbpo_formula_result_ibfk_1 FOREIGN KEY (id_formula) REFERENCES tbpo_formula (id_formula) |  |
| tbpo_formula_variable | ADD CONSTRAINT tbpo_formula_variable_ibfk_1 FOREIGN KEY (id_formula) REFERENCES tbpo_formula (id_formula) ON DELETE CASCADE | ADD CONSTRAINT tbpo_formula_variable_ibfk_1 FOREIGN KEY (id_formula) REFERENCES tbpo_formula (id_formula) ON DELETE CASCADE |  |

## Table Option / Collation Berbeda

Jumlah: 247. Detail lengkap ada di CSV: `docs/database/schema-diff-kiucoid-local-vs-u471548307-20260722.csv` dengan kategori `table_option_diff`.

Interpretasi: mayoritas bukan perubahan kolom, melainkan perbedaan eksplisit `COLLATE=utf8mb4_general_ci` di local dibanding Yoga yang hanya menyimpan `DEFAULT CHARSET=utf8mb4`. Sebelum migration collation, cek default collation database production agar tidak memicu mixed-collation baru.

## File Detail Lengkap

CSV audit lengkap: `docs/database/schema-diff-kiucoid-local-vs-u471548307-20260722.csv`.
