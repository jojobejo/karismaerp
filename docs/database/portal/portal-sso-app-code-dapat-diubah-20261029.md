# Catatan Database app_code Klien SSO

Tanggal: 2026-10-29

Tidak ada perubahan schema database. Perubahan `app_code` dilakukan saat runtime pada `tb_portal_sso_client`, `tb_portal_sso_code`, dan jika tersedia `tb_portal_app_access` dalam satu transaksi.

Aturan akses dipindahkan hanya jika kode tujuan belum memiliki baris pada `tb_portal_app_access`; jika sudah ada, transaksi ditolak untuk mencegah penggabungan izin aplikasi yang berbeda. Tidak diperlukan migration SQL.

Validasi database/SSO runtime dan UAT role-based belum dilakukan.
