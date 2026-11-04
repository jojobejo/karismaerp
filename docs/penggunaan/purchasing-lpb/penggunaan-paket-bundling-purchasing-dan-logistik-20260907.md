# Panduan Penggunaan Modul Paket Bundling (Purchasing & Logistik) — KarismaERP

**Tanggal:** 07 September 2026  
**Status:** Aktif / Siap Digunakan  
**Aplikasi:** KarismaERP (CodeIgniter 3)  

---

## 1. Latar Belakang & Konsep Paket Bundling

Dalam strategi penjualan dan promosi produk, manajemen/Direktur menginstruksikan pembuatan **Paket Bundling** (misalnya: *Paket Jitu*, *Paket Super Promo*, dll).  
Satu paket bundling terdiri dari beberapa barang komponen dengan jumlah tertentu per paket.

Contoh Komposisi:
- **1 Paket Jitu** =
  - 1 Ltr Spontas
  - 1 Ltr Round Up
  - 1 Pcs Kaos Jitu

Jika Purchasing meminta pembuatan **250 Box Paket Jitu**, sistem secara otomatis menghitung total kebutuhan barang:
$$\text{Total Kebutuhan} = \text{Isi per Paket} \times \text{Jumlah Paket}$$
- Spontas 1 Ltr: $1 \times 250 = 250\text{ Ltr}$
- Round Up 1 Ltr: $1 \times 250 = 250\text{ Ltr}$
- Kaos Jitu: $1 \times 250 = 250\text{ Pcs}$

---

## 2. Alur Kerja (End-to-End Workflow)

```mermaid
sequenceDiagram
    autonumber
    actor DIR as Direktur / Manajemen
    actor PUR as Purchasing
    actor LOG as Logistik / Gudang
    actor ACC as Accounting
    participant SYS as KarismaERP Engine

    DIR->>PUR: Instruksi Pembuatan Paket Bundling
    PUR->>SYS: Buat Formula Paket (Komposisi Barang)
    PUR->>SYS: Input Request Pembuatan Paket (Qty Target)
    SYS-->>LOG: Notifikasi / Monitoring Request Masuk
    LOG->>SYS: Cek Ketersediaan Stok Komponen di Gudang Induk (ID 2)
    LOG->>SYS: Realisasi Pembuatan (Assembly) langsung dari Gudang Induk
    SYS->>SYS: Potong Stok Komponen Gudang Induk & Tambah Stok Paket Jadi
    SYS->>ACC: Otomatis Terbitkan Draft Dokumen Penyesuaian Barang (Ref #APB...)
    ACC->>SYS: Verifikasi Akun & Post Jurnal Penyesuaian Persediaan
    SYS->>SYS: Jurnal Akuntansi & Kartu Stok Resmi Terbit (Tanpa Double Potong Batch)
    opt Skenario Khusus Eceran
        LOG->>SYS: Pembongkaran Paket (Disassembly)
        SYS->>SYS: Potong Stok Paket & Kembalikan Komponen ke Eceran Bebas
    end
```

---

## 3. Fitur di Sisi Purchasing

### A. Master Formula Bundling (`/purchasing/bundling/formula`)
- Tempat Purchasing mendaftarkan formula paket dan komposisi detail tiap komponen.
- Menentukan barang induk paket (misal: `PKT-JITU-01`) dan daftar barang penyusunnya beserta `qty_per_paket`.

### B. Request Paket Bundling (`/purchasing/bundling/request`)
1. **Buat Request Baru (`/purchasing/bundling/request/create`)**:
   - Pilih barang paket bundling.
   - Masukkan tanggal target selesai dan jumlah box/paket yang diminta.
   - Sistem secara otomatis menampilkan tabel kebutuhan komponen beserta jumlah total yang dibutuhkan dan estimasi modal HPP.
   - Klik **Simpan Request**. Nomor dokumen otomatis digenerate (contoh: `RPB-20260907-0001`).
2. **Monitoring & Detail (`/purchasing/bundling/request/detail/{id}`)**:
   - Melihat progress realisasi pembuatan oleh tim Logistik (Persentase Progress, Qty Realisasi, Sisa yang belum dibuat).
   - Melihat histori perakitan (assembly) per tanggal dan operator.

---

## 4. Fitur di Sisi Logistik & Integrasi Accounting (Opsi A)

### A. Monitoring Request Purchasing (`/logistik/bundling/monitoring` & `/logistik/bundling/detail/{id}`)
- Logistik melihat daftar seluruh request paket yang masuk dari Purchasing.
- Mengecek kesiapan stok komponen yang tersedia langsung di **Gudang Induk (ID 2)**.
- Status request:
  - `MENUNGGU_PROSES`: Belum ada assembly yang dilakukan.
  - `PROSES_SEBAGIAN`: Sudah dirakit sebagian (partial fulfillment).
  - `SELESAI`: Seluruh target paket telah selesai dirakit ($100\%$).

### B. Pembuatan Paket Bundling Fisik (Assembly) (`/logistik/bundling/assembly/{id_request}`)
- Logistik mengambil bahan komponen langsung dari **Gudang Induk (ID 2)** sesuai ketersediaan batch lot.
- Mendukung perakitan bertahap / harian (contoh: request 100 paket, hari ini selesai 10 paket).
- Saat tombol **Simpan Perakitan** diklik:
  1. Stok fisik komponen terpotong otomatis dari Gudang Induk (tipe `ASSEMBLY_KOMPONEN_OUT`).
  2. Stok fisik paket jadi bertambah di Gudang Penerima Paket (tipe `ASSEMBLY_PAKET`).
  3. **Otomatis Diterbitkan 1 Dokumen Transaksi Draft ke Modul Penyesuaian Barang Accounting (`tbkeu_penyesuaian_barang` dengan status `DRAFT` dan nomor `APB...`)**.
  4. Dokumen draft tersebut sudah berisi rincian:
     - Barang Komponen: Nilai kuantitas minus (pengurangan persediaan bahan baku).
     - Produk Paket Jadi: Nilai kuantitas positif (penambahan persediaan produk jadi).
     - Akun persediaan dan HPP telah ter-mapping otomatis.

### C. Verifikasi & Posting oleh Accounting (`/persediaan/penyesuaian_barang`)
- Bagian Accounting membuka menu **Penyesuaian Barang**.
- Dokumen draft perakitan dari Logistik akan muncul dengan keterangan jelas: `Perakitan Paket Bundling [Nama Paket] ([Qty] Box) Ref #ASM-...`.
- Accounting memverifikasi akun-akun biaya/persediaan, kemudian menekan **Post Jurnal**.
- Sistem menerbitkan jurnal akuntansi resmi ke buku besar dan riwayat kartu stok gudang tanpa menduplikasi pemotongan batch fisik.

---

## 5. Mekanisme Penjualan Eceran & Pembongkaran (Disassembly)

### Aturan Anti-Double Counting (Pencegahan Stok Ganda)
> [!IMPORTANT]
> Barang yang sudah dirakit fisik menjadi Paket Bundling **tidak boleh** tercatat ganda sebagai stok eceran bebas.  
> Jika ada permintaan customer untuk membeli barang satuan (eceran), namun stok komponen bebas habis sementara stok paket bundling masih tersedia, Logistik **WAJIB** melakukan proses **Pembongkaran Paket (Disassembly)** terlebih dahulu.

### Cara Melakukan Pembongkaran Paket (`/logistik/bundling/disassembly/create`)
1. Buka menu **Pembongkaran Paket (Disassembly)** di Logistik.
2. Pilih kode paket bundling yang akan dibongkar dan tentukan gudang asal (misal Gudang Bundling).
3. Masukkan jumlah box yang akan dibongkar (misal: 10 Box).
4. Klik **Proses Pembongkaran**.
5. Dampak sistematis:
   - Stok Paket Bundling berkurang 10 Box.
   - Stok masing-masing barang komponen bertambah kembali (10 unit per komponen) dan berstatus eceran bebas.
   - Tercatat di kartu stok dengan dokumen `DSB-YYYYMMDD-XXXX`.

---

## 6. Audit Trail & Kartu Stok (Single Source of Truth)

Semua pergerakan barang dalam modul Bundling terintegrasi langsung dengan kartu stok KarismaERP:
| Jenis Transaksi | Dokumen | Gudang | Mutasi Barang | Tipe Ledger |
| :--- | :--- | :--- | :--- | :--- |
| Mutasi Komponen | KIUMTSI... | Gdg 2 $\rightarrow$ Gdg 12 | Komponen | `MUTASI_BUNDLING` |
| Assembly Paket | ASM... | Gdg 12 | Komponen: **OUT**, Paket: **IN** | `ASSEMBLY_PAKET` |
| Disassembly Paket | DSB... | Gdg 12 | Paket: **OUT**, Komponen: **IN** | `DISASSEMBLY_PAKET` |
