# KARISMA ERP — Accounting Development Rules

## Project

Aplikasi menggunakan:

* CodeIgniter 3
* PHP 7.4 
* MariaDB 10.4
* AJAX untuk proses tanpa reload
* Existing UI template KARISMA ERP

Spesifikasi utama accounting tersedia pada:

`docs/accounting/MASTER_SPEC.md`

Baca file tersebut sebelum merencanakan atau mengubah kode.

## Mandatory workflow

Untuk setiap task:

1. Baca file yang relevan.
2. Telusuri flow existing sebelum mengubah kode.
3. Buat implementation plan.
4. Tampilkan daftar file yang akan diubah.
5. Jangan mengubah kode sebelum memahami dependency.
6. Buat perubahan dalam scope kecil.
7. Jalankan syntax check dan test yang tersedia.
8. Tampilkan git diff summary.
9. Dokumentasikan hasil dan risiko.

## Database safety

* Jangan terhubung ke database production.
* Jangan menjalankan query destructive tanpa instruksi eksplisit.
* Jangan menggunakan DROP TABLE pada tabel existing.
* Jangan rename tabel existing.
* Jangan menghapus data existing.
* Migration harus mempunyai rollback.
* Semua nominal accounting baru menggunakan DECIMAL(19,4).
* Jangan menggunakan FLOAT atau DOUBLE untuk jurnal baru.
* Gunakan InnoDB dan utf8mb4.
* Jangan menambahkan foreign key ke tabel legacy sebelum audit orphan selesai.
* Jangan mengubah SQL dump master.
* SQL dump hanya digunakan sebagai sumber analisis struktur.

## Tables outside accounting scope

Tabel berikut sepenuhnya berada di luar scope accounting:

* tbpo_transaksi
* tbpo_transaksi_tmp
* tbpo_transaksi_trashbin
* tbpo_akun_tr

Dilarang:

* membaca tabel tersebut dari modul accounting;
* menulis ke tabel tersebut;
* mengubah strukturnya;
* menambah foreign key;
* memigrasikan datanya;
* membuat dependency;
* menggunakan data akunnya;
* menjadikannya sumber jurnal.

Tabel PO lain seperti `tbpo_po` dan `tbpo_detail_po` hanya boleh dianalisis sebagai source document pembelian apabila memang dibutuhkan oleh flow existing.

## Accounting invariants

* Jurnal menggunakan double-entry.
* Total debit harus sama dengan total kredit.
* Jurnal POSTED bersifat immutable.
* Koreksi dilakukan melalui reversal.
* Posting harus idempotent.
* Periode CLOSED tidak boleh menerima posting.
* Akun HEADER tidak boleh digunakan untuk posting.
* Akun nonaktif tidak boleh digunakan untuk transaksi baru.
* Kode akun tidak boleh di-hardcode.
* Account resolution wajib melalui account mapping.
* Jangan menyimpan jurnal parsial.
* Posting wajib menggunakan database transaction.
* Semua laporan keuangan bersumber dari jurnal POSTED.

## Code standards

* Gunakan Query Builder CodeIgniter jika sesuai.
* Gunakan parameter binding.
* Validasi seluruh input server-side.
* Jangan hanya mengandalkan validasi JavaScript.
* Controller harus tipis.
* Business logic accounting ditempatkan pada service/library.
* Query laporan ditempatkan pada model atau report service.
* Hindari duplikasi query.
* Gunakan nama method dan variabel yang menjelaskan domain.
* Tambahkan komentar hanya untuk keputusan bisnis yang tidak jelas dari kode.

## AJAX response

Gunakan bentuk konsisten:

{
"success": true,
"message": "",
"data": {},
"errors": {},
"meta": {
"request_id": "",
"timestamp": ""
}
}

## Documentation

Perbarui dokumentasi setelah setiap fase:

* docs/accounting/TASKS.md
* docs/accounting/DECISIONS.md
* application/libraries/Accounting/docs/

## Stop conditions

Hentikan implementasi dan laporkan temuan apabila:

* source transaction tidak jelas;
* terdapat dua tabel yang tampak menjadi master untuk entitas sama;
* tipe data source tidak konsisten;
* mapping HPP tidak dapat ditentukan;
* status transaksi yang menjadi trigger posting tidak diketahui;
* ditemukan risiko kehilangan data;
* perubahan memerlukan modifikasi tabel di luar scope.

Jangan menebak keputusan akuntansi kritis.
