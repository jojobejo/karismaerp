# Konsistensi Data SSO Saat `app_code` Diubah

Tidak ada perubahan schema database, kolom, index, atau migration baru.

Pengaturan cookie session Secure/HttpOnly di aplikasi juga tidak memerlukan perubahan database.

Operasi rename aplikasi kini memperbarui `app_code` pada tabel yang sudah tersedia:

- `tb_portal_app_access`
- `tb_portal_sso_code`
- `tb_portal_sso_client_account`
- `tb_portal_sso_client_account_assignment`
- `tb_portal_sso_login_history`
- `tb_portal_sso_client`

Semua pembaruan dilakukan dalam satu transaksi. Penghapusan aplikasi membersihkan code sementara, assignment akun, katalog akun, dan registry sesuai alur aplikasi; histori login dipertahankan untuk audit. Tidak diperlukan migration atau rollback schema.
