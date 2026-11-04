# Dampak Database: Pengaturan Akun Khusus Admin

Pembatasan pengaturan akun ke admin dan penolakan SSO tanpa penetapan untuk klien yang memiliki akun impor tidak mengubah schema. Mekanisme menggunakan `tb_portal_sso_client_account` dan `tb_portal_sso_client_account_assignment` yang sudah ada. Pastikan migration assignment sudah diterapkan sebelum admin melakukan penetapan.
