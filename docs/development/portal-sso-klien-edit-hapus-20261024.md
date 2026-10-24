# Edit dan Hapus Integrasi SSO Portal

## Perubahan aplikasi

Menambahkan tombol **Edit** dan **Hapus** pada daftar integrasi di route `admin/portal-sso-clients`. Identitas target adalah `app_code` yang menjadi primary key unik pada `tb_portal_sso_client`; request edit dan hapus membawa kode tersebut pada segmen route.

- Edit mengambil detail dengan `GET admin/portal-sso-clients/edit/{app_code}` dan hanya mengirim URL peluncuran, callback, status, serta kode aplikasi. Hash dan secret terenkripsi tidak dikirim ke browser.
- Perubahan URL disimpan lewat `POST admin/portal-sso-clients/update/{app_code}`. Secret dan status aktif/nonaktif tetap dipertahankan.
- Hapus memakai `POST admin/portal-sso-clients/delete/{app_code}` dengan token CSRF formulir. Sistem menghapus kode otorisasi terkait dan baris client dalam satu transaksi.
- Hapus meminta konfirmasi browser. Session klien yang telah terbentuk tidak dicabut oleh tindakan ini.

## Cara penggunaan

1. Buka **Portal → Integrasi SSO Aplikasi Klien**.
2. Pada baris aplikasi yang benar, tekan tombol pensil untuk mengubah URL peluncuran atau callback. Pastikan kode aplikasi yang tampil sesuai target.
3. Tekan **Simpan perubahan**. Secret klien tidak berubah sehingga konfigurasi secret pada server aplikasi klien tidak perlu diperbarui.
4. Untuk menghapus integrasi, tekan tombol tempat sampah pada baris target dan konfirmasi kode aplikasi.

Penghapusan tidak menghapus aturan akses user pada `tb_portal_app_access`. Aturan tersebut tidak diterapkan selama client terhapus dan dapat dipakai kembali jika client dengan kode yang sama didaftarkan ulang.

## Database dan verifikasi

Tidak ada perubahan schema database. PHP lint dan pemeriksaan whitespace dilakukan untuk file yang berubah. Pengujian browser dengan akun administrator belum dilakukan.
