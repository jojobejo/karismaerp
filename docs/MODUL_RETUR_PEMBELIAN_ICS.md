# Dokumentasi Modul Retur Pembelian (`ics/retur/pembelian`)

## 1. Ringkasan Fitur & Alur Bisnis
Modul **Retur Pembelian** pada route `ics/retur/pembelian` dirancang untuk memfasilitasi proses pengembalian barang ke supplier dengan sumber stok yang berasal dari **Gudang Retur Pembelian** (`Gdg. Retur Pembelian`).

### Alur Penggunaan:
1. **Master Gudang Retur Pembelian**:
   - Sistem secara otomatis memeriksa dan membuat master gudang **`Gdg. Retur Pembelian`** (`tipe: INDUK`, `is_active: 1`) pada tabel `tb_gudang` apabila belum tersedia (terdaftar pada `id_gudang = 32`).
   - Tersedia tombol pintas **Mutasi ke Gdg. Retur Pembelian** di bagian atas halaman untuk memindahkan stok barang yang akan diretur ke gudang tersebut.
2. **Form Input Barang Retur (Kolom Sebelah Kiri)**:
   - **Pilih Supplier**: Pengguna memilih supplier terlebih dahulu dari `tbpo_suplier`.
   - **Cari Nama Barang**: Menampilkan daftar barang yang memiliki saldo stok fisik tersedia (`qty_on_hand - qty_reserved > 0`) di **Gudang Retur Pembelian** (`tberp_stock_batch`).
   - **Nested Dinamis No Lot**: Menampilkan daftar `no_lot` yang tersedia berdasarkan nama/kode barang yang dipilih dan **Gudang Retur Pembelian**.
   - **Nested Dinamis Expired Date**: Menyesuaikan pilihan `expired_date` berdasarkan `no_lot` dan barang yang dipilih, sekaligus menampilkan batas maksimum stok batch dalam satuan kecil.
   - **Input Qty (Satuan Kecil)**: Pengguna mengisi kuantitas retur dalam satuan kecil (`PCS`, `BTL`, `BKS`, dll.) dan menekan tombol **Buat / Tambah ke Draft**.
3. **Draft, Penyelesaian, & Posting Data (Kolom Sebelah Kanan)**:
   - **Tabel Draft**: Kolom tabel sebelah kanan disesuaikan persis dengan kolom yang diinput di sebelah kiri:
     `No`, `Kode Barang`, `Nama Barang`, `No Lot`, `Expired Date`, `Qty (Satuan Kecil)`, dan `# (Hapus)`.
   - **Input Penyelesaian & Alasan Retur Setelah Draft Dibuat**: Setelah minimal 1 barang masuk ke dalam draft, panel penyelesaian di bawah tabel draft menjadi aktif untuk mengisi:
     - `Tanggal Retur`
     - `Jenis Penyelesaian` (`POTONG_HUTANG`, `KLAIM_SUPPLIER`, `REFUND_KAS_BANK`, `REPLACEMENT`)
     - `Alasan Retur`
   - **Tombol Posting Data**: Menekan tombol **Posting Data Retur Pembelian** akan menyimpan dokumen retur berstatus `POSTED`, mengurangi stok fisik dari `tberp_stock_batch` pada Gudang Retur Pembelian, serta mencatat mutasi keluar di `tberp_stock_ledger` (`tipe = RBELI`, `ref_type = RETUR_PEMBELIAN`).

---

## 2. Skema Database & Migrasi
1. **Master Gudang (`tb_gudang`)**:
   - Nama Gudang: `Gdg. Retur Pembelian`
   - Tipe: `INDUK`
   - Status: `is_active = 1`
2. **Tabel Draft Sementara (`tb_tmp_retur_pembelian`)**:
   - Dibuat otomatis melalui `M_ReturPembelian::ensure_schema()` untuk menyimpan item draft per `user_inputer` sebelum diposting.
3. **Tabel Transaksi Retur (`tb_retur_pembelian` & `tb_retur_pembelian_detail`)**:
   - Menyimpan header dan detail retur pembelian beserta log aktivitas di `tb_retur_pembelian_log`.

---

## 3. Daftar Endpoint & Routing (`application/config/routes.php`)
- `GET ics/retur/pembelian` &rarr; `logistik/C_Ics/retur_pembelian`
- `GET ics/retur/pembelian/supplier_select2` &rarr; `logistik/C_Ics/ajax_retur_pembelian_supplier_select2`
- `GET ics/retur/pembelian/gudang_barang_select2` &rarr; `logistik/C_Ics/ajax_retur_pembelian_gudang_barang_select2`
- `GET ics/retur/pembelian/gudang_lot_select2` &rarr; `logistik/C_Ics/ajax_retur_pembelian_gudang_lot_select2`
- `GET ics/retur/pembelian/gudang_exp_select2` &rarr; `logistik/C_Ics/ajax_retur_pembelian_gudang_exp_select2`
- `GET ics/retur/pembelian/draft_list` &rarr; `logistik/C_Ics/ajax_retur_pembelian_draft_list`
- `POST ics/retur/pembelian/draft_add` &rarr; `logistik/C_Ics/ajax_retur_pembelian_draft_add`
- `POST ics/retur/pembelian/draft_delete` &rarr; `logistik/C_Ics/ajax_retur_pembelian_draft_delete`
- `POST ics/retur/pembelian/draft_clear` &rarr; `logistik/C_Ics/ajax_retur_pembelian_draft_clear`
- `POST ics/retur/pembelian/post_draft` &rarr; `logistik/C_Ics/ajax_retur_pembelian_post_draft`

---

## 4. File yang Dimodifikasi
- `application/config/routes.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_ReturPembelian.php`
- `application/views/content/logistik/ics/returform_pembelian.php`
- `application/views/content/logistik/ics/ajax_retur_pembelian.php`
