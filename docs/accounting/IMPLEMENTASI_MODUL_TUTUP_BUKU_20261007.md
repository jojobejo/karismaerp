# Implementasi Modul Tutup Buku KarismaERP

Tanggal implementasi: 7 Oktober 2026  
Database acuan dan lokal: `kiucoid_karismaerp_local`

## Ringkasan

Modul tutup buku berversi telah ditambahkan sebagai pengganti perubahan status periode secara langsung. Implementasi mencakup validasi pra-closing, approval oleh user berbeda, snapshot saldo akun, checksum, jurnal penutup tahunan, serta permohonan buka buku dengan approval Manager dan Direktur.

Migration telah dijalankan pada database lokal dari:

`docs/database/accounting_closing_period_20261007.sql`

## Struktur Database

- `tbkeu_closing_config`: konfigurasi akun dan kebijakan closing.
- `tbkeu_closing_period`: header closing dengan jenis, versi, status, approver, dan checksum.
- `tbkeu_closing_validation_run`: satu eksekusi validasi.
- `tbkeu_closing_validation_detail`: hasil setiap aturan validasi.
- `tbkeu_closing_balance_account`: snapshot saldo GL per akun.
- `tbkeu_reopen_request`: permohonan buka buku dan batas waktu koreksi.
- `tbkeu_reopen_approval_log`: audit approval Manager dan Direktur.

Snapshot tidak menggunakan `ON DELETE CASCADE`. Satu periode dapat mempunyai beberapa versi closing setelah reopen dan re-closing.

## Alur Tutup Buku Bulanan

1. Buka menu Jurnal.
2. Pada periode `OPEN`, tekan **Tutup Bulan**.
3. Isi catatan pengajuan.
4. Sistem memeriksa jurnal draft, jurnal tidak balance, posting exception, dan pembayaran belum teralokasi.
5. Jika lolos, status menjadi `READY_TO_APPROVE`.
6. User lain menekan **Setujui & Eksekusi**.
7. Sistem menyimpan snapshot saldo, checksum, dan mengubah periode menjadi `CLOSED` secara atomik.

Pemohon biasa tidak dapat menyetujui pengajuannya sendiri. Akun superuser `admin` atau akun dengan session `is_admin_dashboard` diperbolehkan melakukan self-approval untuk kebutuhan administrasi darurat; identitas pemohon dan approver tetap tersimpan pada audit trail.

## Perilaku Laporan Laba-Rugi Setelah Closing

Halaman `jurnal/laba-rugi` otomatis memilih periode fiskal berstatus `OPEN`. Dengan demikian, setelah suatu bulan ditutup dan periode berikutnya dibuka, tampilan awal laba-rugi menggunakan rentang periode baru dan dimulai dari nol.

Data periode lama tidak dihapus. Pengguna dapat memilih periode `CLOSED` pada filter **Periode Fiskal** untuk membuka laporan historis. Opsi **Rentang tanggal manual** tetap tersedia untuk kebutuhan analisis lintas periode.

Reset bulanan ini merupakan reset rentang pelaporan, bukan jurnal penghapusan saldo. Jurnal penutup akun nominal hanya dibuat pada tutup buku tahunan.

## Alur Tutup Buku Tahunan

Tutup tahun hanya tersedia untuk periode yang tanggal akhirnya berada pada bulan Desember. Sebelum digunakan, tim accounting wajib menentukan akun laba ditahan:

```sql
UPDATE tbkeu_closing_config
SET id_akun = :id_akun_laba_ditahan,
    updated_by = :id_user
WHERE config_key = 'RETAINED_EARNINGS_ACCOUNT';
```

Sistem kemudian akan:

1. Mengambil saldo akun dengan klasifikasi `LABA_RUGI` dalam rentang periode.
2. Membuat jurnal yang membalik seluruh saldo akun nominal menjadi nol.
3. Memindahkan laba/rugi bersih ke akun laba ditahan.
4. Mem-posting jurnal melalui `Accounting_service`.
5. Menyimpan snapshot final setelah jurnal penutup.

Pada database lokal ditemukan beberapa kandidat akun laba ditahan. Konfigurasi sengaja dibiarkan kosong agar akun entitas yang salah tidak dipilih otomatis.

## Alur Buka Buku

1. Pada periode `CLOSED`, tekan **Ajukan Buka**.
2. Isi alasan dan rencana koreksi.
3. User berjabatan Manager Accounting memberikan approval pertama.
4. User Direktur yang berbeda memberikan approval final.
5. Periode dibuka maksimum 24 jam.
6. Koreksi dilakukan menggunakan reversal dan jurnal pengganti.
7. Periode wajib ditutup kembali. Closing berikutnya memakai versi baru; snapshot lama menjadi `SUPERSEDED`.

User pemohon, approver Manager, dan approver Direktur harus berbeda sesuai tahap yang diperiksa service.

## Penguncian Periode

`Period_lock_service::validate_date()` disediakan sebagai pemeriksaan tunggal untuk seluruh modul sumber. Setiap operasi create, update, delete, post, unpost, void, atau reversal wajib memanggil service ini sebelum mengubah transaksi.

Jalur jurnal telah mempunyai proteksi periode di `Accounting_service`. Integrasi bertahap masih diperlukan pada seluruh controller/model legacy penjualan, purchasing, kas, retur, LPB, dan stok karena modul-modul tersebut memiliki banyak entry point lama.

## Validasi yang Sudah Aktif

- Tidak ada jurnal `DRAFT`.
- Kode periode sesuai tahun-bulan tanggal mulai dan rentangnya satu bulan kalender penuh.
- Tidak ada jurnal `POSTED` yang tidak balance atau berbeda dengan total detail.
- Tidak ada posting exception `OPEN`.
- Tidak ada pembayaran posted yang belum dialokasikan.
- Akun laba ditahan terkonfigurasi untuk tutup tahun.

## Batasan Tahap Ini

- Rekonsiliasi GL terhadap AR/AP belum menjadi aturan blocking karena document ledger AR/AP tunggal belum tersedia.
- Rekonsiliasi nilai persediaan belum tersedia karena stock ledger masih menyimpan kuantitas tanpa historical cost.
- Fixed asset, bank reconciliation, dan tax ledger belum tersedia.
- Antarmuka konfigurasi akun laba ditahan belum dibuat; konfigurasi dilakukan melalui SQL setelah keputusan accounting.
- Approval jabatan menggunakan nilai session `jobdesk`; nomenklatur jabatan produksi perlu dicocokkan sebelum UAT.

## UAT Minimum

1. Pengajuan closing gagal ketika ada jurnal draft.
2. Pengajuan closing lolos setelah seluruh blocking issue selesai.
3. Pemohon tidak dapat menyetujui closing sendiri.
4. User lain dapat menyetujui dan periode menjadi `CLOSED`.
5. Posting jurnal ke periode closed ditolak.
6. Reopen tidak dapat dilakukan langsung melalui endpoint lama.
7. Reopen membutuhkan approval Manager dan Direktur berbeda.
8. Re-closing menghasilkan `closing_version` baru.
9. Snapshot lama tidak terhapus dan berubah menjadi `SUPERSEDED`.
10. Tutup tahun menolkan seluruh akun laba-rugi dan memindahkan selisih ke laba ditahan.
