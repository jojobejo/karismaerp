# Dokumentasi Penerbitan Faktur Realisasi Konsinyasi Mandiri

## 1. Latar Belakang & Konsep Bisnis
Pada skema transaksi konsinyasi:
1. **Faktur Induk Titip Jual (SO/Faktur Customer)**:
   - Diterbitkan saat barang pertama kali dititipkan ke kios (misal nomor `TINV2609260001` sejumlah 120 pcs).
   - Tercatat pada tabel utama `tbso_faktur_penjualan` dan stoknya tercatat sebagai titipan kios di `tb_konsinyasi_settlement` (`status = 'DI_KIOS'`).
2. **Pelunasan Bertahap oleh Kios**:
   - Kios tidak selalu membayar sekaligus 120 pcs, melainkan membayar sesuai barang yang laku di kiosnya (misal termin 1 bayar 50 pcs, termin 2 bayar 10 pcs, termin 3 bayar 5 pcs, dst.).
   - Setiap kali kios melakukan pembayaran di menu **Keuangan -> Pembayaran Faktur** (`keuangan/pembayaran/bayar/{id_faktur}`), sistem menerbitkan **Faktur Konsinyasi Baru** secara mandiri:
     - Termin 1 (50 pcs) $\rightarrow$ Faktur `TINV2609260001-1`
     - Termin 2 (10 pcs) $\rightarrow$ Faktur `TINV2609260001-2`
     - Termin 3 (5 pcs) $\rightarrow$ Faktur `TINV2609260001-3`
     - Dst. hingga faktur induk lunas.
3. **Pemisahan dari `tbso_faktur_penjualan`**:
   - Faktur bertanda strip (`-1`, `-2`, dst.) **SAMA SEKALI TIDAK DISIMPAN** di `tbso_faktur_penjualan`.
   - Hal ini bertujuan agar tidak merusak data kartu piutang customer, tidak menduplikasi omzet penjualan customer, dan menjaga integritas tabel penjualan umum.
   - Faktur ini disimpan secara mandiri pada tabel register konsinyasi: **`tb_konsinyasi_faktur`**.

---

## 2. Struktur Database Baru & Terkait

### A. Tabel Utama: `tb_konsinyasi_faktur`
Menyimpan seluruh arsip dan data penerbitan faktur konsinyasi realisasi:
- `id_faktur_konsinyasi` (Primary Key, Auto Increment)
- `no_faktur_konsinyasi` (Contoh: `TINV2609260001-1`, UNIQUE)
- `id_faktur_induk` & `no_faktur_induk` (Referensi faktur titip jual asal, e.g. `12` / `TINV2609260001`)
- `id_pembayaran` (Foreign key ke `tbkeu_pembayaran_faktur.id_pembayaran`)
- `id_settlement` (Foreign key ke `tb_konsinyasi_settlement.id_settlement`)
- `termin_ke` (Nomor termin pelunasan: 1, 2, 3...)
- `tanggal_faktur` (Tanggal pelunasan/terbitnya faktur)
- `kd_customer` & `nama_customer` (Kios/customer yang membeli)
- `kd_suplier` & `nama_suplier` (Supplier pemilik barang konsinyasi)
- `kd_barang`, `nama_barang`, `no_lot`, `expired_date` (Identitas batch barang)
- `qty` & `satuan` (Jumlah barang yang dibeli kios pada termin tersebut)
- `hrg_satuan` & `subtotal` (Nilai realisasi penjualan)
- `jumlah_bayar` & `metode_pembayaran` (Nominal dan akun pembayaran kas/bank)
- `status` (`LAKU`, `BILLED`, `CANCELLED`)

### B. Tabel Pembayaran: `tbkeu_pembayaran_faktur`
- Kolom baru: `no_faktur_konsinyasi` (`VARCHAR(50) NULL`) mencatat nomor faktur konsinyasi yang diterbitkan pada transaksi pembayaran tersebut.

### C. Tabel Settlement: `tb_konsinyasi_settlement`
- Kolom `no_faktur` menyimpan nomor faktur konsinyasi realisasi (`TINV2609260001-1`, dst.) untuk baris berstatus `LAKU` atau `BILLED`.
- Kolom `id_pembayaran` menghubungkan baris settlement dengan bukti pembayaran terkait.

---

## 3. Komponen Alur Sistem (Source Code)

1. **`M_Konsinyasi::terbitkan_faktur_konsinyasi($id_faktur, $id_pembayaran)`**:
   - Menghitung urutan termin pembayaran yang sah.
   - Mengenerate nomor faktur `NO_FAKTUR-TERMIN`.
   - Mengupdate `tbkeu_pembayaran_faktur.no_faktur_konsinyasi`.
   - Menginsert / mengupdate record faktur di tabel `tb_konsinyasi_faktur`.
   - Mengaitkan record settlement di `tb_konsinyasi_settlement`.
2. **`M_Journal::_sync_settlement_on_payment()`**:
   - Memecah sisa titipan barang konsinyasi di kios (`DI_KIOS`).
   - Mengupdate porsi laku menjadi `status = 'LAKU'` dengan nomor faktur konsinyasi.
   - Otomatis memicu `terbitkan_faktur_konsinyasi()`.
3. **`C_pembayaran::print_faktur_konsinyasi($id_pembayaran)`**:
   - Mengambil data dari `tb_konsinyasi_faktur` berdasarkan `id_pembayaran`.
   - Menampilkan dokumen cetak dengan nomor faktur konsinyasi resmi (`TINV...-1`, dst.).
4. **`C_Konsinyasi::print_faktur($id_settlement)`**:
   - Mengambil data dari `tb_konsinyasi_faktur` untuk dicetak langsung dari modul Purchasing Konsinyasi.
