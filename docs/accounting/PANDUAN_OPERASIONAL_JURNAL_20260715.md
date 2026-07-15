# Panduan Operasional Modul Jurnal KARISMA ERP

Dokumen ini adalah panduan produksi setelah migration `accounting_hardening_20260715.sql` diterapkan. Halaman utama berada di `/accounting`; simulator hanya berada di `/accounting-test`.

## Prinsip yang wajib dijaga

- Hanya jurnal `POSTED` yang masuk laporan.
- Jurnal `POSTED` tidak diedit atau dihapus. Koreksi dilakukan dengan reversal, lalu buat transaksi/jurnal pengganti.
- Tanggal transaksi harus berada dalam periode fiskal `OPEN`.
- Debit dan kredit harus sama hingga 4 desimal.
- Auto-post selalu membaca ulang tabel final. Nominal dari browser/controller tidak dipercaya sebagai sumber akuntansi.
- Satu event sumber hanya boleh menghasilkan satu jurnal karena `idempotency_key` dan unique source event.
- Exception `OPEN` harus nol saat tutup periode.

## Alur transaksi

```text
Faktur final ──> SALES_INVOICE (Piutang / Pendapatan / PPN)
             └─> GOODS_ISSUE   (HPP / Persediaan)

LPB final ─────> validasi harga seluruh item ──> GOODS_RECEIPT
                                      └─ gagal ─> Posting Exception

Pembayaran ────> validasi outstanding invoice ─> Kas/Bank vs Piutang/Hutang

Jurnal manual ─> DRAFT ─> review ─> POSTED ─> laporan
                                      └─ salah ─> REVERSAL
```

## Cara penggunaan

### 1. Menyiapkan periode

1. Buka `/accounting` sebagai admin keuangan.
2. Buat periode dengan kode unik, tanggal mulai/selesai, dan alasan approval.
3. Sistem menolak periode yang bertabrakan.
4. Sebelum `CLOSE`, selesaikan seluruh jurnal `DRAFT`, exception `OPEN`, dan rekonsiliasi.
5. Isi alasan penutupan. Sistem menolak penutupan bila masih ada draft atau jurnal tidak balance.
6. `REOPEN` hanya untuk koreksi resmi dan wajib menyertakan alasan approval.

### 2. Membuat jurnal manual

1. Isi tanggal, nomor dokumen, idempotency key, dan keterangan.
2. Tambahkan minimal dua baris pada akun posting yang mengizinkan jurnal manual.
3. Pastikan indikator debit/kredit sama, lalu **Simpan Draft**.
4. Buka detail jurnal, cocokkan akun, nominal, dokumen, dan periode.
5. Klik **Posting Draft**. Setelah `POSTED`, jurnal tidak dapat diedit.

### 3. Auto-post faktur penjualan

Saat Sales mengonfirmasi DO siap loading, sistem membaca `tbso_faktur_penjualan` dan `tbso_faktur_detail` berdasarkan nomor faktur. Sistem membuat dua jurnal terpisah:

- `SALES_INVOICE`: debit piutang; kredit penjualan dan PPN keluaran.
- `GOODS_ISSUE`: debit HPP; kredit persediaan.

Mengulang konfirmasi tidak menggandakan jurnal. Jika sumber batal, detail kosong, nominal nol, periode tertutup, atau mapping tidak tersedia, proses masuk ke Posting Exception.

### 4. Auto-post LPB

Setelah LPB final tersimpan, sistem menghitung harga perolehan per barang dari `tb_pre_po_invoice_adjustment.harga_satuan`, lalu fallback ke `tb_pre_po.hrg_satuan`. Semua item wajib memiliki harga positif.

Jika ada harga yang belum tersedia, LPB tetap sah secara logistik tetapi jurnal tidak dibuat. Finance/Purchasing melengkapi harga, lalu membuka **Posting Exception** dan memilih **Retry**. Jangan mengisi jurnal persediaan nol sebagai jalan pintas.

### 5. Pembayaran dan alokasi

1. Pilih customer payment atau supplier payment.
2. Isi nomor dan tanggal pembayaran.
3. Tambahkan nomor invoice dan nilai alokasi.
4. Sistem menolak invoice kosong, invoice ganda dalam satu pembayaran, dan alokasi melebihi outstanding jurnal.
5. Nilai belum dialokasikan tetap tercatat menggunakan nomor pembayaran dan harus ditindaklanjuti sebelum close period.

### 6. Reversal

1. Pilih jurnal `POSTED` yang belum pernah direversal.
2. Isi alasan koreksi yang dapat diaudit.
3. Sistem mengunci jurnal sumber, membuat jurnal pembalik dengan debit/kredit tertukar, dan menandai sumber melalui `reversed_at`.
4. Jurnal sumber tetap berstatus database `POSTED` agar sumber dan pembalik sama-sama masuk laporan; UI menampilkan status `REVERSED`.

### 7. Exception queue

- **Retry**: gunakan setelah akar masalah diperbaiki (mapping, periode, harga sumber).
- **Resolve**: hanya bila jurnal dibuat melalui proses pengganti yang terdokumentasi.
- **Ignore**: hanya untuk data uji/bukan transaksi; catatan wajib diisi.
- Exception berulang untuk sumber/error sama tidak membuat spam baris, tetapi menaikkan occurrence count.

## Laporan dan kontrol harian

- Buku besar menampilkan saldo berjalan per akun.
- Neraca saldo memakai hanya detail jurnal dalam rentang tanggal yang dipilih.
- Laba rugi memakai pergerakan dalam periode.
- Neraca bersifat kumulatif sampai tanggal akhir.
- Piutang/hutang dikelompokkan berdasarkan nomor dokumen agar pembayaran dapat mengurangi invoice yang benar.

Setiap akhir hari jalankan `docs/database/accounting_uat_database_20260715.sql`. Hasil `FAIL` adalah blocker; hasil `REVIEW` harus diberi keputusan dan bukti.

## Hak akses dan keamanan

- Halaman accounting hanya untuk admin/keuangan yang sudah login.
- Semua request accounting memakai token session khusus accounting, walaupun CSRF global aplikasi legacy belum diaktifkan.
- `/accounting/auto-post` tidak tersedia. Simulator nominal manual hanya tersedia melalui `/accounting-test/auto-post`.
- Jangan membuka route `accounting-test` untuk user operasional produksi.

