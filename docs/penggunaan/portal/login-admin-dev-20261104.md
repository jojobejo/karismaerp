# Masuk ke modul admin Portal dev

1. Buka `https://localhost/karismaerp/Auth` pada lingkungan lokal yang memakai database `kiucoid_karismaerp_dev`.
2. Masuk dengan username `admin` dan password yang diberikan kepada pemilik akun. Password tidak dicatat di repository.
3. Setelah masuk ke `/portal`, buka menu admin untuk **Integrasi SSO**, **Application Directory**, atau **User Management** sesuai kebutuhan.
4. Ganti password sementara setelah login pertama melalui prosedur pengelolaan akun yang berlaku. Hindari memakai password yang sama pada lingkungan lain.

Jika proses PHP/Apache memakai `KARISMA_DB_PROFILE=portalerp`, koneksi diarahkan ke database `portalerp` yang terpisah. Akun dev ini tidak otomatis tersedia pada profil tersebut.
