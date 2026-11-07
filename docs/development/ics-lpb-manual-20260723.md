# Development - LPB Manual Purchasing

Tanggal: 2026-07-23

## Ringkasan

Route `ics/icspo` disederhanakan untuk menghilangkan filter tanggal, tombol `Tampil`, tombol `Sync PO`, dan teks waktu sync terakhir. Halaman ini sekarang menyediakan tombol:

- `Input LPB Manual`
- `Laporan LPB`
- Tombol existing `Data LPB` dan `Data Retur` tetap tampil untuk role yang sebelumnya memilikinya.

## Modul Baru

### Input LPB Manual

Route:

```text
ics/lpb_manual
ics/lpb_manual/barang
ics/lpb_manual/store
```

Controller:

```text
application/controllers/logistik/C_Ics.php
```

View:

```text
application/views/content/logistik/ics/lpb_manual.php
```

Model:

```text
application/models/M_Logistik.php
```

Fungsi utama:

- `lpb_manual()`
- `ajax_lpb_manual_barang()`
- `ajax_lpb_manual_store()`
- `M_Logistik::search_lpb_manual_barang()`
- `M_Logistik::validate_lpb_manual_payload()`
- `M_Logistik::create_lpb_manual()`

Inputer memilih barang melalui Select2 Ajax dari `tbpo_barang`. Lot dan expired date wajib diisi manual pada setiap baris. LPB manual tidak memakai data PO; sistem membuat referensi manual `LPBMyymmdd0001` dan menyimpannya ke `kd_po`, `no_po`, serta `manual_ref_no`.

### Laporan LPB

Route:

```text
ics/lpb_report
```

Laporan menampilkan LPB manual Purchasing dan LPB hasil input Logistik dari PO. Filter `source`:

- `all`
- `manual`
- `logistik`

### Log Sistem LPB Manual

Route:

```text
ics/lpb_manual_log
```

Log ini dipisahkan dari `tb_lpb_log` dan disiapkan untuk dashboard IT. Dashboard IT mendapat menu `Log LPB Manual`.

## Hak Akses

- Input LPB Manual: Purchasing, ADMIN PO, Admin, dan IT/Admin support.
- Laporan LPB: Purchasing, Logistik, IT, Admin.
- Log Sistem LPB Manual: IT/Admin support dan Admin.

## Alur Simpan

Saat `Simpan LPB Manual` berhasil:

1. Insert header ke `tb_lpb` dengan `source_type = MANUAL`.
2. Insert detail ke `tb_lpb_detail`.
3. Insert lot/expired ke `tb_lpb_batch`.
4. Upsert stok masuk ke `tberp_stock_batch`.
5. Insert jejak stok ke `tberp_stock_ledger` dengan `tipe = IN` dan `ref_type = LPB_MANUAL`.
6. Insert aktivitas LPB ke `tb_lpb_log`.
7. Insert log sistem terpisah ke `tb_lpb_manual_log`.

## Cara Pakai

1. Login sebagai akun Purchasing.
2. Buka `ics/icspo`.
3. Klik `Input LPB Manual`.
4. Pilih tanggal, jenis LPB, gudang, lalu isi No SJ/Invoice bila ada.
5. Klik `Tambah Barang`, pilih barang dari list `tbpo_barang`.
6. Isi qty, no lot, expired date, dan harga satuan bila diperlukan.
7. Klik `Simpan LPB Manual`.
8. Cek hasilnya di `ics/lpb_report?source=manual`.

