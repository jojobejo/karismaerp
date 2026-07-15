Baca:

* `AGENTS.md`
* `docs/accounting/MASTER_SPEC.md`
* `docs/accounting/AUDIT_RESULT.md`
* `docs/accounting/TASKS.md`
* `docs/accounting/DECISIONS.md`

Kerjakan hanya:

`FASE [NOMOR] — [NAMA FASE]`

## Scope fase

[Masukkan fitur yang boleh dikerjakan.]

## Di luar scope

[Masukkan fitur yang belum boleh dikerjakan.]

## Langkah kerja

1. Inspeksi implementasi existing yang relevan.
2. Cocokkan dengan hasil audit.
3. Buat rencana perubahan.
4. Tampilkan daftar file yang akan dibuat dan diubah.
5. Implementasikan hanya scope fase ini.
6. Jangan mengubah public contract existing tanpa alasan.
7. Jalankan syntax check.
8. Jalankan migration/test pada database lokal bila tersedia.
9. Jalankan test case fase ini.
10. Periksa git diff.
11. Perbarui dokumentasi.

## Acceptance criteria

[Masukkan acceptance criteria terukur.]

## Validasi wajib

* Tidak ada perubahan pada tabel di luar scope.
* Tidak ada dependency terhadap:

  * tbpo_transaksi
  * tbpo_transaksi_tmp
  * tbpo_transaksi_trashbin
  * tbpo_akun_tr
* Tidak ada kode akun hardcode.
* Tidak ada penggunaan FLOAT atau DOUBLE untuk nominal accounting baru.
* Semua query tulis yang saling terkait berada dalam database transaction.
* Semua endpoint mempunyai validasi permission.
* Semua AJAX response mengikuti format project.
* Semua perubahan mempunyai penjelasan dan test evidence.

## Output akhir

Tampilkan:

1. Ringkasan perubahan.
2. Daftar file baru.
3. Daftar file berubah.
4. Migration yang dibuat.
5. Test yang dijalankan.
6. Hasil test.
7. Risiko tersisa.
8. Git diff summary.
9. Task berikutnya yang belum dikerjakan.

Jangan melanjutkan ke fase berikutnya.
