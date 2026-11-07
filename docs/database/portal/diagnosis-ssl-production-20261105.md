# Dampak database dari insiden SSL Portal production

`ERR_SSL_PROTOCOL_ERROR` pada `portal.karismaerp.com` terjadi sebelum request mencapai aplikasi dan koneksi database. Diagnosis tanggal 5 November 2026 tidak mengubah skema, tabel, kolom, indeks, data, kredensial, atau konfigurasi koneksi database. Tidak ada migrasi maupun SQL yang perlu dijalankan untuk perbaikan TLS.

Setelah HTTPS pulih, masalah database atau login yang mungkin muncul harus didiagnosis terpisah sesuai [panduan koneksi production](../umum/koneksi-production-environment.md).
