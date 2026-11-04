# Perbaikan Daftar PIC Akun Klien SSO

## Perubahan

Pada route `admin/portal-sso-clients/accounts/{app_code}`, daftar pilihan PIC sebelumnya menggabungkan akun dari `tb_karyawan` dan `tb_users` tanpa deduplikasi. Bila username yang sama tercatat di kedua tabel, pilihan PIC tampil lebih dari sekali.

Pilihan PIC kini dideduplikasi berdasarkan username tanpa membedakan kapitalisasi. Urutan sumber yang ada tetap dipakai sehingga akun `tb_karyawan` menjadi pilihan utama bila duplikatnya juga ada di `tb_users`. Pemetaan akun lama tetap dicari berdasarkan pasangan sumber dan ID agar identitas tersimpan tidak berubah. Jika akun lama berasal dari sumber duplikat yang tidak ditampilkan sebagai opsi utama, tampilan memilih opsi dengan username yang sama; perubahan sumber hanya terjadi setelah admin menyimpan pilihan.

## Berkas terkait

- `application/controllers/admin/C_PortalSsoClients.php`
- `application/views/content/admin/portal_sso_client_accounts.php`

## Validasi

Perubahan ini dibatasi pada daftar pilihan PIC untuk modul akun klien. UAT pada data nyata dan browser belum dilakukan.
