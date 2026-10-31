# Modal Penetapan Akun pada Akses Aplikasi

Kartu klien pada `admin/portal-app-access?source=tb_users&user_id={id}` kini menampilkan username akun yang ditetapkan. Tombol **Akun** di samping **Detail Module** membuka modal agar admin dapat melihat penetapan saat ini, memilih akun yang belum dipakai, mengganti akun, atau mencabut penetapan. Tombol hanya aktif jika izin aplikasi sudah tersimpan.

Modal menggunakan endpoint `account-options` dan `account-save` yang sama dengan halaman penetapan sebelumnya. Validasi akses user, ketersediaan akun, dan unique assignment tetap dilakukan di server/database. Tidak ada perubahan pada aturan integrasi SSO atau password akun.

## Validasi

Pemeriksaan diff dilakukan. UAT browser untuk tampilan modal dan penyimpanan assignment belum dilakukan.
