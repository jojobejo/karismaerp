# Menggunakan Portal pada database dev yang sudah dibersihkan

Masuk melalui `https://localhost/karismaerp/Auth` memakai akun `admin`. Dari halaman Portal, menu admin menyediakan **Application Directory**, **Integrasi SSO**, **Dokumentasi API**, **Cek API Klien SSO**, **User Management**, dan **Module Management**.

Database saat ini hanya memiliki akun `admin`. Tambahkan akun pengguna melalui User Management bila diperlukan. Sebelum kartu aplikasi klien dapat diluncurkan, daftarkan klien di Integrasi SSO, isi callback serta URL peluncuran, lalu tetapkan akses aplikasi dan akun klien kepada pengguna yang tepat. Jangan menganggap 13 entri Application Directory sebagai klien SSO yang sudah aktif.

Modul ERP lama tidak didukung oleh database dev yang sudah dibersihkan. Backup dan prosedur pemulihan tercatat pada [dokumentasi database](../../database/portal/portal_only_prune_dev_20261104.md).
