# Dampak Database: Modal Penetapan Akun

Perubahan tampilan tombol dan modal pada `admin/portal-app-access` tidak mengubah skema atau data. Tidak diperlukan migration atau rollback tambahan. Penetapan tetap menggunakan `tb_portal_sso_client_account_assignment` yang dibuat oleh migration `portal_sso_client_account_assignment_20261030.sql`.
