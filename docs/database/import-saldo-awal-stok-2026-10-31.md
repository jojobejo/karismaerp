# Impor Saldo Awal Stok 31 Oktober 2026

## Sumber

- File: `C:\Users\prayoga\Downloads\saldo awal stok.xlsx`
- Sheet sumber: sheet pertama
- Target snapshot: `tberp_stock_batch`
- Target riwayat: `tberp_stock_ledger` dengan tipe `SALDO_AWAL`

## Aturan impor

- Struktur sembilan kolom harus sama dengan struktur ekspor `tberp_stock_batch`.
- Kode barang yang belum tersedia pada `tbpo_barang` tetap dipertahankan sesuai file sumber dan dilaporkan sebagai peringatan agar saldo tidak hilang.
- Gudang harus tersedia dan aktif pada `tb_gudang`.
- Kuantitas tidak boleh negatif dan reservasi tidak boleh melebihi saldo fisik.
- Kombinasi barang, gudang, lot, dan tanggal kedaluwarsa yang duplikat digabungkan dengan menjumlahkan kuantitas agar sesuai unique key database.
- Impor hanya boleh berjalan ketika tabel batch dan ledger stok masih kosong.
- Seluruh insert dijalankan dalam satu transaksi database; kegagalan satu baris membatalkan seluruh impor.

## Perintah

Validasi:

```powershell
C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe scripts\import_saldo_awal_stock.php --file="C:\Users\prayoga\Downloads\saldo awal stok.xlsx"
```

Eksekusi:

```powershell
C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe scripts\import_saldo_awal_stock.php --file="C:\Users\prayoga\Downloads\saldo awal stok.xlsx" --execute
```

## Hasil eksekusi

- Baris sumber: 2.566.
- Baris `tberp_stock_batch`: 2.560.
- Enam baris batch duplikat digabung tanpa mengubah total kuantitas.
- Total `qty_on_hand`: 5.738.378,001.
- Total `qty_reserved`: 0,000.
- Baris ledger `SALDO_AWAL`: 2.560 dengan total 5.738.378,001.
- Selisih batch terhadap ledger: 0.
- Duplikasi unique key, gudang invalid, dan kuantitas invalid: 0.
- Terdapat 170 baris dari 112 kode barang historis yang belum tersedia pada master `tbpo_barang`; seluruhnya dipertahankan sesuai sumber.

Cadangan sebelum impor:

`outputs/database-backup/stock_batch_ledger_sebelum_import_20261031_145348.sql`
