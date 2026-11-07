# Dokumentasi Fitur Menu Klik Kanan (Detail Jurnal, Print, Unpost) Pembayaran Faktur Customer

- **Tanggal Rilis**: 02 September 2026
- **Modul**: Keuangan - Pembayaran Faktur Customer (`keuangan/pembayaran`)
- **Penanggung Jawab**: Senior Fullstack Developer & Senior Data Analyst (KARISMA ERP)
- **Status Implementasi**: Selesai & Teruji

---

## 1. Latar Belakang & Tujuan
Pada halaman rincian faktur dan riwayat pembayaran customer (`/keuangan/pembayaran/customer/{kd_customer}`), menu klik kanan dan manajemen posting pembayaran mencakup 4 aksi utama:
1. **Detail Jurnal**: Melihat perincian jurnal umum akuntansi (debit & kredit) yang seimbang.
2. **Print**: Mencetak bukti / kwitansi penerimaan pembayaran faktur resmi PT KARISMA INDOARGO UNIVERSAL.
3. **Unpost Pembayaran**:
   - Saat pembayaran di-unpost, status pembayaran berubah menjadi **`DRAFT`**.
   - Status jurnal akuntansi terkait di `tbkeu_jurnal` kembali menjadi **`DRAFT`**.
   - Sisa piutang faktur kembali utuh (pembayaran tidak lagi mengurangi tagihan).
   - Pada halaman input pembayaran (`/keuangan/pembayaran/bayar/{id_faktur}`), pembayaran yang di-unpost **hilang/tidak tampil** di daftar histori pelunasan faktur yang sah.
   - Pada halaman Daftar Jurnal Pembayaran (`/jurnal/pembayaran`), jurnal yang berstatus `DRAFT` (pembayarannya telah di-unpost) **otomatis tidak ditampilkan**, sehingga hanya jurnal berstatus `POSTED` yang muncul di buku besar kas/bank.
4. **Hapus Pembayaran**:
   - Opsi hapus pada context-menu **hanya aktif** jika status pembayaran telah menjadi **`DRAFT`** (atau telah di-unpost).
   - Jika pembayaran masih berstatus `POSTED`, tombol hapus dinonaktifkan (disabled) untuk melindungi integritas transaksi buku besar.
   - Saat dihapus, record pembayaran dan jurnal draft terkait dihapus secara permanen dari basis data.
5. **Buka & Posting Draft (Double Click)**:
   - Pada tabel Riwayat Pembayaran, baris yang berstatus **`DRAFT`** dapat di-**klik 2 kali (double click)** untuk langsung membuka form pembayaran (`/keuangan/pembayaran/bayar/{id_faktur}?draft={id_pembayaran}`).
   - Form pembayaran secara otomatis terisi oleh data draft yang belum terposting tersebut (metode pembayaran, tanggal, nominal, diskon, bank/BG, keterangan).
   - Tampilan nominal pada baris draft disajikan rapi dalam format coret merah tanpa teks tambahan.

---

## 2. Struktur Database & Perubahan Skema
Tabel: `tbkeu_pembayaran_faktur`

| Kolom | Tipe Data | Nullable | Keterangan |
|---|---|---|---|
| `status` | VARCHAR(20) | NOT NULL DEFAULT 'POSTED' | Status posting pembayaran (`POSTED` atau `UNPOST`) |
| `unpost_by` | VARCHAR(100) | NULL | Username/nama karyawan yang melakukan unpost |
| `unpost_at` | DATETIME | NULL | Timestamp waktu dilakukannya unpost |
| `unpost_reason` | VARCHAR(255) | NULL | Alasan pembatalan posting |

Tabel Terkait:
- `tbkeu_jurnal`: Kolom `status` (`DRAFT`, `POSTED`, `REVERSED`, `VOID`). Saat unpost pembayaran, record jurnal dengan `source_module = 'KEUANGAN'` dan `source_id = {id_pembayaran}` diubah statusnya menjadi `'DRAFT'`.
- `tb_customer`: Kolom `plafon_aktif`. Dilakukan reversal plafon customer saat pembayaran di-unpost.
- `tbso_faktur_penjualan`: Perhitungan sisa tagihan secara dinamis mengecualikan pembayaran berstatus `UNPOST`.

---

## 3. Logika & Alur Bisnis

### A. Alur Unpost Pembayaran
1. User mengklik kanan baris pembayaran pada tabel riwayat pembayaran.
2. User memilih opsi **Unpost Pembayaran**.
3. Sistem memunculkan dialog konfirmasi (SweetAlert2) untuk memverifikasi tindakan.
4. Jika disetujui, AJAX POST dikirim ke `/keuangan/pembayaran/ajax_unpost_pembayaran`.
5. Pada Model `M_pembayaran::unpost_payment`:
   - Status pembayaran diubah menjadi `UNPOST`.
   - Jurnal terkait di `tbkeu_jurnal` diubah statusnya menjadi `DRAFT`.
   - Plafon customer yang sebelumnya bertambah saat pembayaran direverse (dikurangi kembali).
   - Saldo retur (jika menggunakan metode retur) otomatis dipulihkan karena pembayaran tidak lagi terhitung sebagai saldo terpakai.
   - Sisa tagihan faktur otomatis kembali bertambah sebesar nominal pembayaran yang di-unpost.
6. Halaman otomatis dimuat ulang sehingga tampilan sisa piutang pada tabel atas langsung terupdate realtime.

### B. Alur Cetak Bukti Pembayaran (Print)
1. User mengklik kanan baris pembayaran lalu memilih **Print Bukti Pembayaran**.
2. Browser membuka tab baru dengan URL `/keuangan/pembayaran/print_bukti/{id_pembayaran}`.
3. Menampilkan layout dokumen resmi voucher penerimaan pembayaran dengan informasi:
   - Identitas perusahaan PT KARISMA INDOARGO UNIVERSAL.
   - No. Kwitansi / Pembayaran & Status Posting (`POSTED` / `UNPOST`).
   - Identitas customer (nama, kode, alamat, telepon).
   - Identitas faktur (nomor faktur, nomor SO, tanggal jatuh tempo).
   - Rincian pembayaran (metode kas/bank, jumlah bayar, diskon/potongan).
   - Konversi terbilang nominal Rupiah otomatis.
   - Ringkasan sisa piutang faktur.
   - 3 kolom tanda tangan (Dibuat Oleh, Mengetahui, dan Diterima Dari).
   - Otomatis memicu dialog cetak browser (`window.print()`).

---

## 4. File yang Terlibat
1. `application/models/M_pembayaran.php`:
   - Penambahan kolom pada `_ensure_payment_table()`
   - Filter `UNPOST` pada `_total_pembayaran_sql()` dan `get_customer_saldo_retur()`
   - Method `unpost_payment()` & `post_payment()`
   - Method `get_payment_detail_for_print()` & `terbilang()`
2. `application/controllers/keuangan/C_pembayaran.php`:
   - Endpoint `ajax_unpost_pembayaran()`
   - Endpoint `ajax_post_pembayaran()`
   - Endpoint `print_bukti()`
3. `application/views/content/keuangan/pembayaran_faktur_detail.php`:
   - Context menu klik kanan: Detail Jurnal, Print, Unpost/Posting
   - Kolom dan badge status posting (`POSTED` / `UNPOST`) pada tabel riwayat pembayaran
   - Handler JavaScript untuk AJAX request dan konfirmasi
4. `application/views/content/keuangan/pembayaran_bukti_print.php`:
   - Template view cetak bukti pembayaran faktur
