# Konsep - Revisi Harga LPB, HPP Average, dan Dampak Accounting

Tanggal: 2026-08-30  
Status: Bahan tinjauan untuk development lanjutan

## Latar Belakang

LPB menjadi acuan HPP KarismaERP. HPP menggunakan metode average. Jika harga pada LPB yang sudah dipakai direvisi, dampaknya tidak hanya ke pembelian, tetapi juga ke:

- nilai persediaan;
- HPP;
- neraca;
- laporan laba rugi;
- margin penjualan;
- hutang supplier;
- jurnal accounting;
- histori transaksi penjualan yang sudah terjadi.

Karena itu, harga LPB yang sudah berjalan tidak ideal jika diedit langsung tanpa kontrol accounting.

## Prinsip Rekomendasi

LPB sebaiknya diperlakukan sebagai dokumen penerimaan barang secara operasional. Perubahan nilai setelah LPB berjalan sebaiknya masuk sebagai dokumen koreksi atau adjustment accounting, bukan menimpa histori LPB begitu saja.

Prinsip utamanya:

- LPB asli tetap menjadi histori audit.
- Revisi harga dicatat sebagai dokumen baru.
- Accounting melakukan afirmasi sebelum angka keuangan berubah.
- Dampak ke stok, HPP, dan jurnal dihitung berdasarkan kondisi barang.
- Periode accounting yang sudah closing tidak berubah diam-diam.

## Workflow Rekomendasi

1. ADMLPB input LPB berdasarkan penerimaan fisik.
2. Harga LPB masuk status administrasi harga:
   - `Belum Final`;
   - `Menunggu Accounting`;
   - `Final`;
   - `Direvisi`.
3. Jika ada perubahan harga setelah LPB dipakai, user membuat dokumen `Revisi Harga LPB`.
4. Accounting meninjau dokumen revisi.
5. Sistem menghitung dampak revisi:
   - qty masih stok;
   - qty sudah terjual;
   - qty sudah habis;
   - status periode accounting.
6. Setelah disetujui, sistem membuat adjustment dan jurnal koreksi.

## Perlakuan Accounting

### Barang Masih Ada di Stok

Selisih harga untuk qty yang masih tersedia menyesuaikan nilai persediaan.

Contoh:

- Harga lama: Rp10.000
- Harga benar: Rp12.000
- Selisih: Rp2.000
- Sisa stok: 5 pcs
- Koreksi persediaan: Rp10.000

Efek accounting:

- nilai persediaan naik/turun;
- hutang supplier atau akun koreksi menyesuaikan.

### Barang Sudah Terjual Sebagian

Selisih harga harus dipisahkan:

- bagian qty yang masih stok masuk ke koreksi nilai persediaan;
- bagian qty yang sudah terjual masuk ke koreksi HPP.

Ini penting agar nilai persediaan tidak menanggung biaya barang yang sudah keluar.

### Barang Sudah Terjual Semua

Jika stok dari LPB tersebut sudah habis, koreksi tidak boleh masuk ke persediaan. Seluruh selisih masuk sebagai koreksi HPP atau akun koreksi periode berjalan sesuai kebijakan accounting.

## Periode Accounting Lock

Perlu konsep kunci periode accounting.

Jika periode LPB belum closing:

- accounting dapat memilih revisi yang memengaruhi periode transaksi asli;
- sistem tetap menyimpan audit trail.

Jika periode LPB sudah closing:

- sistem tidak mengubah jurnal lama secara langsung;
- sistem membuat jurnal koreksi di periode berjalan;
- pembukaan ulang periode hanya boleh dilakukan oleh superadmin/accounting dengan alasan resmi.

## Opsi Penyelesaian

### Opsi 1 - Edit LPB Langsung

Kelebihan:

- sederhana;
- cepat untuk data yang belum berjalan.

Kelemahan:

- berisiko mengubah histori;
- laporan lama dapat berubah;
- sulit diaudit jika barang sudah terjual.

Rekomendasi:

- hanya boleh untuk LPB belum POST, belum ada invoice, belum ada jurnal, dan belum ada penjualan.

### Opsi 2 - Revisi Harga dengan Approval Accounting

Kelebihan:

- aman untuk audit;
- cocok dengan ERP;
- menjaga histori LPB;
- bisa menghitung dampak stok dan HPP.

Kelemahan:

- perlu modul workflow tambahan;
- perlu perhitungan dampak average cost.

Rekomendasi:

- menjadi opsi utama KarismaERP.

### Opsi 3 - Harga Final Sebelum Barang Dijual

Kelebihan:

- HPP lebih stabil sejak awal.

Kelemahan:

- operasional bisa terhambat jika barang harus segera dipakai/dijual.

Rekomendasi:

- cocok untuk barang tertentu yang high value atau sangat sensitif margin.

## Rekomendasi Development Lanjutan

Buat modul `Revisi Harga LPB` dengan komponen:

- daftar LPB yang bisa direvisi;
- detail barang dan harga lama;
- input harga baru;
- alasan revisi;
- deteksi transaksi penjualan terkait;
- simulasi dampak ke stok dan HPP;
- approval accounting;
- jurnal koreksi otomatis;
- audit log;
- proteksi periode closing.

Status minimal dokumen:

- `DRAFT`;
- `SUBMITTED`;
- `APPROVED_ACCOUNTING`;
- `POSTED`;
- `REJECTED`;
- `VOID`.

Output yang perlu disiapkan:

- adjustment nilai persediaan;
- adjustment HPP;
- jurnal hutang supplier atau akun koreksi;
- laporan histori revisi harga LPB;
- log siapa, kapan, sebelum, sesudah, dan alasan revisi.
