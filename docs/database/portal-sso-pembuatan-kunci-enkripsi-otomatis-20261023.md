# Catatan Database: Kunci Enkripsi SSO Otomatis

Fitur pembuatan kunci enkripsi SSO otomatis tidak menambah, mengubah, atau menghapus tabel, kolom, indeks, maupun data. Kunci disimpan pada berkas konfigurasi lokal aplikasi `application/config/portal_sso_local.php`, bukan di database.

Tidak ada migration SQL atau langkah database yang diperlukan. Petunjuk penggunaan dan backup kunci berada di `docs/development/portal-sso-pembuatan-kunci-enkripsi-otomatis-20261023.md`.
