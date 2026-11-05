# Dokumentasi Modul Import Faktur H (Cetak Nota LX-310 & Import Pelunasan Jurnal Q + A)

## 1. Gambaran Umum
Modul **Faktur H Import** (`sales_order/faktur_h_import`) melayani dua kebutuhan utama secara terintegrasi:
1. **Import & Cetak Nota Faktur H (Epson LX-310)**: Mengimpor daftar pecahan faktur (Kode H) beserta rincian barangnya dari Excel untuk dicetak pada printer dot-matrix Epson LX-310.
2. **Import Pelunasan Faktur H & Pembentukan Jurnal Otomatis (Jurnal Acak Q & Jurnal A)**: Melunasi daftar Faktur H hasil import secara massal melalui unggah file Excel (maupun secara batch checkbox / satuan), sehingga setiap Faktur H yang lunas otomatis memiliki **Jurnal Pembayaran Acak (Q)** dan **Jurnal Kas Masuk (A)** di modul Keuangan & Buku Besar.

---

## 2. Struktur Tabel Database
Tabel-tabel berikut dibuat dan diperbarui secara otomatis (auto-migration) melalui [`M_SalesOrder::ensure_faktur_h_import_tables()`](file:///c:/laragon/www/karismaerp/application/models/M_SalesOrder.php#L4404-L4530):

1. **`tbso_faktur_h_import` (Header Faktur H & Status Pelunasan)**
   - Kolom utama faktur: `id_import_h`, `no_faktur`, `parent_no_faktur`, `no_so`, `kd_customer`, `customer_name`, `kontak_person`, `nama_toko`, `alamat`, `kota`, `kd_rute`, `tanggal_faktur`, `tanggal_jatuh_tempo`, `tempo`, `salesman`, `catatan`, `total_item`, `total_qty`, `grand_total`.
   - Kolom pelunasan & jurnal:
     - `total_bayar` (`DECIMAL(18,2)`) — Nominal Netto Faktur H yang telah dilunasi.
     - `sisa_tagihan` (`DECIMAL(18,2)`) — Sisa tagihan Faktur H.
     - `status_bayar` (`VARCHAR(20)`) — `'belum_lunas'`, `'sebagian'`, atau `'lunas'`.
     - `tanggal_bayar` (`DATE`) — Tanggal pelunasan.
     - `metode_pembayaran` (`VARCHAR(100)`) — Akun Kas/Bank penerima (contoh: `Q Mandiri`, `Q Kas Ayat Silang`, `Q Kas`).
     - `skema_potongan` (`INT`) — `20` (Potongan 20% Tax), `40` (Potongan 20% Tax + 20% Fee Petugas), atau `0` (Tanpa Potongan).
     - `nominal_bruto` (`DECIMAL(18,2)`) — Nominal bruto sebelum potongan.
     - `nominal_potongan` (`DECIMAL(18,2)`) — Nominal potongan penjualan.
     - `id_jurnal_q` & `nomor_jurnal_q` — Referensi ke header **Jurnal Pembayaran Acak (Q)** di `tbkeu_jurnal`.
     - `id_jurnal_a` & `nomor_jurnal_a` — Referensi ke header **Jurnal Kas Masuk (A)** di `tbkeu_jurnal`.

2. **`tbso_faktur_h_import_detail` (Rincian Item Barang)**
   - Menyimpan daftar barang per Faktur H (`kd_barang`, `nama_barang`, `no_lot`, `expired_date`, `qty`, `satuan`, `hrg_satuan`, `disc`, `total_harga`).

3. **`tbso_faktur_h_import_pembayaran` (Riwayat Pelunasan Faktur H Import)**
   - Menyimpan log transaksi pelunasan (`id_pembayaran_h`, `id_import_h`, `no_faktur`, `tanggal_pembayaran`, `jumlah_bayar`, `nominal_bruto`, `nominal_potongan`, `skema_potongan`, `metode_pembayaran`, `id_jurnal_q`, `nomor_jurnal_q`, `id_jurnal_a`, `nomor_jurnal_a`, `import_batch`).

---

## 3. Skema Akuntansi Jurnal Pembayaran Acak (Q) & Jurnal (A)
Logika penjurnalan diimplementasikan pada [`M_Journal::post_jurnal_pelunasan_faktur_h_import()`](file:///c:/laragon/www/karismaerp/application/models/M_Journal.php#L1970-L2270) mengikuti standar akuntansi Faktur H / Pecah Faktur Z:

### Contoh Perhitungan (Skema Default Potongan 20%):
Misalkan Faktur H bernominal **Rp 1.000.000** (Netto 80%) dan dilunasi **Rp 1.000.000**:
- **Nominal Netto (Faktur H)** = `Rp 1.000.000`
- **Nominal Bruto (100%)** = `Rp 1.000.000 / 0.8` = `Rp 1.250.000`
- **Nominal Potongan (20%)** = `Rp 1.250.000 - Rp 1.000.000` = `Rp 250.000`

#### 1. Jurnal Pembayaran Acak (Q) (`source_module = 'KEUANGAN'`, `source_type = 'PEMBAYARAN_FAKTUR'`)
| Baris | Kode Akun | Nama Akun | Keterangan | Debit | Kredit |
| :--- | :--- | :--- | :--- | ---: | ---: |
| 1 | `12014` / Sesuai Metode | `Q Mandiri` / `Q Kas Ayat Silang` / `Q Kas` | Penerimaan dari [Nama Customer H] | Rp 1.250.000 | Rp 0 |
| 2 | `13099` | `Piutang Usaha` | Penerimaan dari [Nama Customer H] | Rp 0 | Rp 1.000.000 |
| 3 | `11010` | `Q Kas` | Penerimaan dari [Nama Customer H] | Rp 0 | Rp 250.000 |

#### 2. Jurnal Kas Masuk (A) — Kode `KM` (`source_module = 'KEUANGAN'`, `source_type = 'PEMBAYARAN_FAKTUR'`)
- **Jika Skema Potongan 20% (Tax):**
  | Baris | Kode Akun | Nama Akun | Keterangan | Debit | Kredit |
  | :--- | :--- | :--- | :--- | ---: | ---: |
  | 1 | `11030` | `A Kas` | PND u/Pot [Customer] F.[No Faktur] (Tax) | Rp 250.000 | Rp 0 |
  | 2 | `13036` | `A PND atas Pendapatan Pot Penj KIU (Tax)` | PND u/Pot [Customer] F.[No Faktur] (Tax) | Rp 0 | Rp 250.000 |

- **Jika Skema Potongan 40% (20% Tax + 20% Fee Petugas):**
  - Debit `11030` (`A Kas`) sebesar total potongan (40%).
  - Kredit Baris 1: `13036` (`A PND atas Pendapatan Pot Penj KIU (Tax)`) sebesar 50% dari potongan (20%).
  - Kredit Baris 2: `13041` (`A PND atas Pendapatan Pot Penj KIU (Fee Petugas)`) sebesar 50% dari potongan (20%).

---

## 4. Panduan Penggunaan Fitur Pelunasan Faktur H Import

### Cara 1: Import Excel Pelunasan (Direkomendasikan untuk Banyak Faktur)
1. Buka menu **Faktur H Import** (`sales_order/faktur_h_import`).
2. Klik tombol **Template Pelunasan (.xlsx)** (atau pilih **Terisi Otomatis: Faktur H Belum Lunas**).
   - Sistem akan mengunduh file Excel yang **sudah berisi seluruh daftar nomor Faktur H yang belum lunas** beserta nominal tagihannya, sehingga Anda tidak perlu mengetik nomor faktur satu per satu.
3. Periksa/sesuaikan kolom pada Excel:
   - `no_faktur` *(Wajib)*: Nomor Faktur H yang akan dilunasi.
   - `tanggal_pembayaran`: Tanggal pelunasan (`YYYY-MM-DD`).
   - `jumlah_pembayaran`: Nominal Faktur H yang dibayar (jika dikosongkan, otomatis lunas penuh sesuai nominal tagihan faktur).
   - `metode_pembayaran`: Contoh `Q Mandiri`, `Q Kas Ayat Silang`, `Q Kas`.
   - `skema_potongan`: `20`, `40`, atau `0`.
   - `parent_no_faktur` *(Opsional)*: Nomor Faktur Z induk untuk referensi keterangan pada Jurnal A.
4. Klik tombol hijau **Import Pelunasan Faktur H (Jurnal Q & A)**, pilih file Excel tersebut, lalu klik **Proses Import Pelunasan & Buat Jurnal**.

### Cara 2: Pelunasan Batch via Checkbox di Tabel
1. Centang beberapa (atau klik **Pilih Semua**) Faktur H pada tabel.
2. Klik tombol **Lunasi Terpilih**, pilih Tanggal Pelunasan, Metode Pembayaran, dan Skema Potongan, lalu klik **Proses Lunasi Semua Terpilih**.

### Cara 3: Melihat & Membatalkan (Unpost) Jurnal Pelunasan
- Pada kolom **Jurnal Acak (Q) & Jurnal (A)** di tabel, klik badge nomor jurnal **`Q: MR-...`** atau **`A: KM-...`** untuk membuka pop-up rincian akun Debit & Kredit dari kedua jurnal tersebut.
- Jika ingin membatalkan atau mengoreksi pelunasan, klik tombol **Unpost** (ikon panah putar balik kuning) pada baris faktur atau gunakan tombol **Unpost Pelunasan Terpilih**.
