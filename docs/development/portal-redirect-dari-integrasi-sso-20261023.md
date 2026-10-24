# Redirect Portal dari Integrasi SSO

Tanggal: 23 Oktober 2026

Catatan ini menjelaskan perilaku sebelum pemisahan URL peluncuran dan callback. Setelah perubahan 24 Oktober 2026, kartu aplikasi pada route `portal` menggunakan `tb_portal_sso_client.launch_uri` dari menu **Integrasi SSO** untuk klien aktif. `redirect_uri` hanya menjadi callback authorization-code dan tidak lagi menjadi tujuan kartu. URL peluncuran tidak disimpan ulang pada pengaturan akses per user.

Aplikasi yang ditampilkan tetap ditentukan oleh data hak akses user di menu **Akses Aplikasi**. Integrasi yang belum terdaftar atau nonaktif tidak menyediakan tujuan kartu; ketika diklik, portal meminta user menghubungi admin untuk mengatur redirect aplikasi tersebut.

Untuk memperbarui tujuan kartu, admin membuka **Integrasi SSO**, mendaftarkan atau memperbarui URL peluncuran HTTPS dan callback HTTPS, lalu memastikan integrasi aktif. Satu `app_code` menggunakan satu URL peluncuran dan satu callback terdaftar.

Perubahan skema `launch_uri` tercatat di `docs/database/portal_sso_launch_uri_20261024.md`. Migration sudah diterapkan pada database lokal pengembangan; deployment production dan UAT browser login admin/user masih diperlukan.
