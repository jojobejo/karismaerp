# Redirect Portal dari Integrasi SSO

Tanggal: 23 Oktober 2026

Kartu aplikasi pada route `portal` menggunakan nilai **Callback Terdaftar** dari menu **Integrasi SSO** (`admin/portal-sso-clients`) untuk integrasi aktif. Sumber data adalah `tb_portal_sso_client.redirect_uri` dengan `is_enabled = 1`. Callback tidak disimpan ulang pada pengaturan akses per user.

Aplikasi yang ditampilkan tetap ditentukan oleh data hak akses user di menu **Akses Aplikasi**. Integrasi yang belum terdaftar atau nonaktif tidak menyediakan tujuan kartu; ketika diklik, portal meminta user menghubungi admin untuk mengatur redirect aplikasi tersebut.

Untuk memperbarui tujuan kartu, admin membuka **Integrasi SSO**, mendaftarkan atau memperbarui callback HTTPS untuk `app_code` terkait, dan memastikan integrasi aktif. Satu `app_code` menggunakan satu callback terdaftar.

Tidak ada perubahan skema database pada penyesuaian ini. PHP lint lolos untuk model, controller, dan view yang diperbarui. Uji browser login admin/user belum dilakukan.
