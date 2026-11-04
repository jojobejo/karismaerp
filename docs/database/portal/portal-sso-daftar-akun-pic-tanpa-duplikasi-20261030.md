# Dampak Database: Deduplikasi Pilihan PIC Akun Klien SSO

Perbaikan daftar PIC pada `admin/portal-sso-clients/accounts/{app_code}` tidak mengubah skema maupun data database. Tidak ada migration atau rollback SQL yang diperlukan.

Pilihan PIC tetap bersumber dari tabel `tb_karyawan` dan `tb_users`. Tampilan hanya menggabungkan akun dengan username yang sama menjadi satu opsi; relasi PIC yang sudah tersimpan pada `tb_portal_sso_client_account` tidak dimutasi oleh pembukaan halaman.
