# Development - Fasilitas Per User dan Masking Nominal LPB

Tanggal: 2026-08-30  
Modul: Master User, Access & Facility Control, ICS LPB

## Tujuan

Development ini menambahkan kontrol fasilitas per user dan menerapkan rules awal untuk akun Admin LPB (`admlpb`) agar tidak dapat melihat nominal rupiah pada sajian data LPB.

## Rules Bisnis

User Admin LPB tetap dapat melihat data operasional LPB:

- kuantitas;
- kode barang;
- nama barang;
- satuan;
- supplier;
- nomor PO/LPB;
- tanggal;
- status barang;
- status harga.

User Admin LPB tidak melihat data nominal:

- harga satuan;
- subtotal / total harga;
- diskon;
- DPP;
- PPN;
- jumlah hutang;
- grand total LPB;
- HPP;
- margin;
- nilai persediaan.

Jika nominal dibutuhkan sebagai konteks validasi, sistem menampilkan status seperti `Harga tersedia` atau `Menunggu accounting`.

## Modul Fasilitas Per User

Route utama:

```text
master/user-facility
```

Route pendukung:

```text
master/user-facility/users
master/user-facility/matrix/{id_user}
master/user-facility/update
```

File yang ditambahkan:

```text
application/models/master/M_Userfacility.php
application/controllers/master/C_Userfacility.php
application/views/content/master/userfacility/index.php
```

Modul ini menyediakan matrix fasilitas per user:

- akses menu/module;
- aksi tambah;
- aksi edit;
- aksi hapus;
- aksi approve;
- aksi print;
- aksi export;
- lihat nominal LPB;
- lihat HPP;
- lihat margin;
- lihat harga beli;
- lihat harga jual;
- akses cabang;
- akses gudang;
- akses status dokumen.

## Database

Tabel baru dibuat otomatis saat modul dibuka:

```sql
CREATE TABLE IF NOT EXISTS tb_user_facility (
    id_user_facility INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    facility_key VARCHAR(120) NOT NULL,
    facility_label VARCHAR(180) NOT NULL,
    module_key VARCHAR(80) NOT NULL DEFAULT 'general',
    facility_group VARCHAR(80) NOT NULL DEFAULT 'Umum',
    is_allowed TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id_user_facility),
    UNIQUE KEY uniq_user_facility (user_id, facility_key),
    KEY idx_facility_key (facility_key),
    KEY idx_module_key (module_key)
);
```

Default behavior:

- `lpb.view_nominal` bernilai `false` untuk user `admlpb`, `adminloglpb`, `admlpb2`, atau jobdesk `ADMLPB`, `ADMINLOGLPB`, `ADMLPB2`.
- User lain mengikuti behavior lama, yaitu nominal tetap tampil kecuali ada override fasilitas per user.

## Area LPB yang Diamankan

1. `ics/icspo`
   - Kolom `Grand Total` diganti menjadi `Status Harga` untuk user tanpa fasilitas nominal.

2. `ics/detail_record_lpb`
   - Data nominal pada AJAX detail LPB dan detail purchasing dimasking dari controller.
   - Tabel purchasing menampilkan status harga, bukan harga/DPP/PPN/total.
   - Tombol update/split harga tidak tampil untuk user tanpa fasilitas nominal.
   - Grand total DPP dan Grand Total Harga disembunyikan.

3. `ics/lpb_report`
   - Response DataTables tidak mengirim nilai nominal untuk user tanpa fasilitas nominal.
   - Card `Nilai Total Pembelian` menampilkan `Tersembunyi`.
   - Kolom finansial menampilkan status harga.

4. `ics/lpb_report/export_excel`
   - Kolom nominal berisi `TERSEMBUNYI`, bukan angka.

5. `ics/summary_hutang`
   - Ditolak untuk user tanpa fasilitas nominal karena halaman ini berbasis nilai hutang/nominal.

6. Print LPB
   - Data rows tetap melalui masking controller. Template print saat ini tidak menampilkan nominal, sehingga aman untuk rules ADMLPB.

7. `ics/import_lpb`
   - Tombol import disembunyikan dari laporan LPB untuk user tanpa fasilitas nominal.
   - Route import, upload, proses, dan download template ditolak karena template dan preview import memuat DPP, PPN, dan grand total.

8. `ics/retur/pembelian/adjustment`
   - Route adjustment harga LPB dan AJAX pendukung ditolak untuk user tanpa fasilitas nominal.

## Catatan Pengujian Manual

Gunakan akun `admlpb`:

1. Buka `ics/data_lpb`.
2. Pastikan kolom `Grand Total` tidak tampil sebagai rupiah.
3. Buka detail LPB.
4. Pastikan harga satuan, DPP, PPN, total harga, dan grand total tidak tampil.
5. Buka `ics/lpb_report`.
6. Pastikan card nilai total tersembunyi dan export Excel tidak membawa angka nominal.
7. Coba akses `ics/summary_hutang`; sistem harus menolak akses.

Gunakan admin/superadmin:

1. Buka `master/user-facility`.
2. Pilih user.
3. Ubah fasilitas `lpb.view_nominal`.
4. Cek kembali sajian LPB sesuai nilai fasilitas.
