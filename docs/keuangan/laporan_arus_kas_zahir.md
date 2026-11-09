# Dokumentasi Modul: Laporan Arus Kas (Zahir Style) — Rangkuman & Rincian

## 1. Deskripsi Fitur
Modul Laporan Arus Kas menyajikan arus kas masuk dan keluar (*Cash Flow Statement*) perusahaan PT Karisma Indoargo Universal berdasarkan pergerakan akun Kas & Bank pada transaksi jurnal akuntansi berstatus `POSTED`. 
Format tampilan dan interaksi dirancang mengacu pada standar laporan **Zahir Accounting Karisma**:
1. **Dialog Filter Data**: Pop-up dialog sebelum laporan dibuka dengan opsi pemilihan rentang tanggal (`Dari` > `Hingga`).
2. **Arus Kas - Rangkuman**: Menyajikan rekapitulasi nilai arus kas per kelompok/kategori dan aktivitas (Operasi, Investasi, Pendanaan).
3. **Arus Kas - Rincian**: Menyajikan rincian per baris akun (dengan format kode akun Zahir `XXX-XX`), nilai penerimaan/pengeluaran kas, subtotal per kategori, dan total per aktivitas.
4. **Rekonsiliasi Kas**: Menyajikan Total Keluar/Masuk Kas, Saldo Awal Kas & Bank, serta Saldo Akhir Kas & Bank.

---

## 2. Struktur Pengelompokan Akun Arus Kas

| Bagian | Kategori | Rentang Kode Akun | Contoh Akun |
| :--- | :--- | :--- | :--- |
| **Top Items** | Biaya Dibayar Dimuka | `150xx` | `150-12` Q UM Perjalanan Dinas, `150-14` Q Premi Asuransi, `150-20` Q UM Pembangunan Gedung |
| | Biaya Adm & Umum | `660xx` - `699xx` | `660-10` Q Biaya ATK, `660-11` Q Biaya Listrik, `670-xx` Biaya Perjalanan Dinas |
| **Operating Activities** | Piutang Usaha | `130xx` - `139xx` | `130-12` Q Piutang Non Dagang, `130-99` Piutang Usaha |
| | Persediaan | `140xx` | Persediaan Barang Dagang |
| | Utang Usaha | `210xx` - `219xx` | `210-13` Q Hutang Angsuran, `210-98` Hutang Usaha |
| | Beban atas Pendapatan | `510xx` - `599xx` | `510-13` Q Biaya Ongkos Angkut Pembelian |
| | Beban Pemasaran Dan Penjualan | `610xx` - `659xx` | `610-11` Q Biaya THR, `610-13` Q Biaya Bonus Penjualan, `640-xx` Ongkos Angkut Penjualan |
| | Pendapatan Usaha | `410xx` - `499xx` | Penjualan Tunai |
| | Pendapatan Luar Usaha | `810xx` - `899xx` | `810-10` Q Pendapatan Bonus, `810-12` Q Pendapatan Bunga Bank |
| | Beban Luar Usaha | `910xx` - `989xx` | `910-11` Q Biaya Adm Bank, `910-12` Q Biaya Pajak Bunga |
| | Beban Pajak | `990xx` - `999xx` | `990-10` Beban Pajak Penghasilan |
| **Investing Activities** | Investasi Jangka Panjang | `160xx` | `160-12` Q Surat Berharga |
| | Harta Tetap Berwujud | `170xx` - `189xx` | `170-12` Q Inventaris Kantor, Kendaraan, Gedung |
| **Financing Activities** | Modal | `310xx` - `399xx` | `310-31` A Prive, Modal Disetor |
| | Kewajiban Jangka Panjang | `220xx` - `299xx` | Hutang Bank Jangka Panjang |

---

## 3. Rumus & Logika Perhitungan Kas
1. **Akun Kas & Bank**: Seluruh akun yang berawalan `110` (Kas) dan `120` (Bank).
2. **Saldo Awal**:
   $$\text{Saldo Awal} = \sum (\text{Debit Kas} - \text{Kredit Kas}) \quad \text{untuk transaksi sebelum } \text{start\_date}$$
3. **Efek Kas per Akun Non-Kas**:
   Untuk setiap baris jurnal majemuk yang melibatkan kas dalam periode laporan:
   $$\text{Efek Kas} = \text{Kredit} - \text{Debit}$$
   - Akun Harta/Beban jika berkurang (dikredit) = Kas Masuk ($+$)
   - Akun Kewajiban/Modal/Pendapatan jika bertambah (dikredit) = Kas Masuk ($+$)
   - Sebaliknya jika didebit = Kas Keluar ($-$)
4. **Total Keluar/Masuk Kas**:
   $$\text{Total Kas} = \text{Biaya Dibayar Dimuka} + \text{Biaya Adm \& Umum} + \text{Total Operasi} + \text{Total Investasi} + \text{Total Pendanaan}$$
5. **Saldo Akhir**:
   $$\text{Saldo Akhir} = \text{Saldo Awal} + \text{Total Keluar/Masuk Kas}$$

---

## 4. Rincian Teknis Implementasi (MVC)
* **Model** (`application/models/M_Journal.php`):
  - `get_laporan_arus_kas($startDate, $endDate)`: Mengambil dan mengkalkulasi saldo awal, mutasi kas, pengelompokan akun, dan saldo akhir.
  - `map_account_to_cash_flow_category($kode_akun)`: Memetakan akun ke grup dan kategori.
  - `format_kode_akun_zahir($kode)`: Format strip kode akun (e.g. `150-12`).
* **Controller** (`application/controllers/keuangan/C_Laporan.php`):
  - `arus_kas_rangkuman()`: Menampilkan view rangkuman.
  - `arus_kas_rincian()`: Menampilkan view rincian.
  - `arus_kas_export($type)`: Mengunduh laporan ke file Excel (`.xls`).
* **Routing** (`application/config/routes.php`):
  - `laporan/keuangan/arus-kas-rangkuman`
  - `laporan/keuangan/arus-kas-rincian`
  - `laporan/keuangan/arus-kas-export/(:any)`
* **Views**:
  - `application/views/content/keuangan/laporan/laporan_keuangan/index.php`: Penambahan kartu menu Rangkuman & Rincian.
  - `application/views/content/keuangan/laporan/laporan_keuangan/_modal_filter_zahir.php`: Dialog pop-up filter tanggal dengan shortcut `Alt+B` (Batal) dan `Alt+O` (OK).
  - `application/views/content/keuangan/laporan/laporan_keuangan/arus_kas_rangkuman.php`: Tampilan sheet rangkuman + toolbar aksi.
  - `application/views/content/keuangan/laporan/laporan_keuangan/arus_kas_rincian.php`: Tampilan sheet rincian + toolbar aksi.
  - `application/views/content/keuangan/laporan/laporan_keuangan/arus_kas_excel.php`: Template download Excel.

---

## 5. Cara Penggunaan / User Guide
1. Akses menu **Laporan > Laporan Keuangan** (`https://karismaerp.test/laporan/keuangan`).
2. Klik menu **Arus Kas - Rangkuman** atau **Arus Kas - Rincian**.
3. Dialog **Filter Data** bergaya Zahir akan muncul:
   - Tentukan tanggal **Dari** dan **Hingga**.
   - Tekan tombol **OK** (atau shortcut `Alt + O`) untuk membuka laporan.
   - Tekan **Batal** (atau shortcut `Alt + B`) untuk menutup dialog.
4. Di dalam halaman laporan:
   - Klik **Filter Data** untuk mengubah periode tanggal sewaktu-waktu.
   - Klik **Cetak / PDF** untuk mencetak dengan layout dokumen cetak resmi.
   - Klik **Excel** untuk mengunduh laporan ke format Excel.

