# Petunjuk Penggunaan: Fitur Checker By & Posting Data LPB

**Target Pengguna**: Admin LPB, Purchasing, Admin Sistem  
**Modul**: ICS (Inventory & Control System) - Logistik & Purchasing  

---

## 1. Cara Input Data Checker By pada Detail PO (`ics/detail_po`)

### Langkah-langkah:
1. Login ke aplikasi KarismaERP menggunakan akun **Admin** atau **Admin LPB (ADMLPB)**.
2. Buka menu **ICS / Logistik** -> pilih menu **Detail PO** (`ics/detail_po`).
3. Pada halaman penerimaan draft PO, perhatikan form header penerimaan barang.
4. Di sebelah kiri field **Keterangan**, terdapat field input baru **Checker By**.
5. Masukkan **Nama / Kode Checker** yang melakukan fisik pemeriksaan barang di gudang (contoh: `Ahmad / CHK-02`).
6. Lengkapi field Tanggal SJ, Jenis PO, Gudang, dan Keterangan (jika ada).
7. Masukkan rincian barang & lot yang diterima, lalu klik tombol **Simpan**.
8. Data `Checker By` otomatis tersimpan ke database dan akan disajikan pada setiap tampilan Detail LPB.

> [!TIP]
> Input **Checker By** mempermudah penelusuran (traceability) penanggung jawab fisik penerimaan barang di gudang apabila terjadi selisih qty atau komplain barang rusak.

---

## 2. Cara Posting Data LPB pada Halaman Record LPB (`ics/detail_record_lpb`)

### Langkah-langkah:
1. Login menggunakan akun **Purchasing** atau **Admin**.
2. Masuk ke halaman **Detail Record LPB** (`ics/detail_record_lpb`).
3. Pilih salah satu LPB dari daftar yang berstatus **UNPOST** (berwarna merah/kuning).
4. Periksa rincian data harga, invoice, faktur pajak, dan barang pada LPB tersebut.
5. Pada bagian bawah panel aksi atau tabel detail, klik tombol **Posting Data** berwarna hijau (`Posting Data` / `POST`).
6. Sistem akan menampilkan notifikasi konfirmasi dan memperbarui status LPB menjadi **POST** (Status = 1).
7. LPB yang telah di-POST siap digunakan untuk proses akuntansi, verifikasi hutang supplier, maupun pelaporan keuangan.

---

## 3. Menampilkan Data Checker pada Detail LPB

Pada halaman **Detail Record LPB** (`ics/detail_record_lpb`), kotak informasi **Checker** pada grid header akan menampilkan gabungan data `Checker By` dan `Checker Name` yang telah di-input oleh Admin LPB (contoh tampilan: `CHK-02 (Ahmad)`).
