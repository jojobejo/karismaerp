# Checklist Database untuk SSO Portal ke KIU PO

Tanggal analisis: 31 Oktober 2026

## Ringkasan

Tidak ada perubahan schema yang direkomendasikan untuk menyelesaikan alur login Bram. Implementasi saat ini memakai tabel SSO/akun yang sudah ditambahkan pada fitur Portal dan tabel pemetaan subject di KIU PO. Kesiapan tabel pada database deployment belum dibuktikan dalam analisis source code ini.

## Pemeriksaan database Portal

- [ ] Pastikan klien `kiu_po` ada dan aktif pada `tb_portal_sso_client`.
- [ ] Pastikan `redirect_uri` berisi callback HTTPS KIU PO yang tepat dan `launch_uri` menunjuk route awal SSO KIU PO.
- [ ] Pastikan `tb_portal_app_access` mengizinkan aplikasi `kiu_po` untuk kombinasi `auth_source` + `user_id` Bram.
- [ ] Pastikan katalog akun pada `tb_portal_sso_client_account` memuat akun kerja KIU PO yang disetujui.
- [ ] Pastikan tepat satu assignment Bram tersedia pada `tb_portal_sso_client_account_assignment`; cek unique key dan relasi `app_code`/`account_id`.
- [ ] Pastikan `tb_portal_sso_code` tersedia untuk authorization code sekali pakai.
- [ ] Pastikan `tb_portal_sso_login_history` sudah diterapkan karena KIU PO menolak session bila konfirmasi histori gagal.
- [ ] Validasi konfigurasi melalui query metadata/hasil tersamarkan. Jangan mengekspor atau menempelkan hash, ciphertext, client secret, password, atau data pribadi ke tiket.

## Pemeriksaan database KIU PO

- [ ] Pastikan akun username hasil assignment ada tepat satu kali di `tbpo_user`.
- [ ] Pastikan level akses akun berada pada rentang yang didukung aplikasi dan akun sesuai kebijakan aktif/nonaktif lokal.
- [ ] Pastikan `tbpo_sso_identity` tersedia dengan constraint unik yang diperlukan untuk `external_sub` dan relasi ke `id_user`.
- [ ] Periksa mapping subject Bram dan pastikan tidak menunjuk akun lain yang masih harus dipertahankan.
- [ ] Pastikan perubahan assignment akun terkoordinasi dengan mapping subject dan prosedur audit; jangan mengubah password lokal sebagai jalan pintas SSO.

## Riwayat skema dari dokumentasi

Fitur assignment memakai migration Portal `docs/database/portal_sso_client_account_20261029.sql` dan `docs/database/portal_sso_client_account_assignment_20261030.sql`. Histori memakai `docs/database/portal_sso_login_history_20261030.sql`. Tabel identitas KIU PO dibuat melalui `C:\xampp\htdocs\kiupobaru\database\sql\portal_sso_identity_20261023.sql`. Dokumen migration/rollback masing-masing tetap menjadi sumber prosedur penerapan.

Perubahan URL peluncuran langsung yang didokumentasikan pada 30 Oktober hanya mengubah nilai `launch_uri` pada database Portal lokal, tanpa perubahan schema. Jangan menganggap nilai lokal itu sudah diterapkan pada production; bandingkan nilai aktual deployment dengan callback dan route yang aktif sebelum UAT.

## Hasil analisis

**No schema change untuk checklist ini.** Jalankan hanya pemeriksaan baca-saja sampai akses ke database deployment disetujui dan environment target diketahui. Jangan menjalankan migration/rollback atau mengubah data assignment sebagai bagian dari analisis ini.
