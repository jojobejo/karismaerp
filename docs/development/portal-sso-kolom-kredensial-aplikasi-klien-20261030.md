# Dokumentasi Kolom Kredensial Aplikasi Klien pada Integrasi SSO

## Perubahan

Panduan penggunaan akun klien Portal SSO kini menjelaskan nama field yang perlu disiapkan atau dipetakan pada aplikasi klien:

- `username`: identitas akun lokal yang dicocokkan dengan klaim opsional `client_account_username` dari respons token SSO.
- `password`: kredensial untuk autentikasi lokal aplikasi klien. Password dapat masuk dalam CSV akun yang diimpor administrator melalui menu **Akun List**, dengan pemetaan header ke data password yang benar.

Untuk login SSO, aplikasi klien mencocokkan username akun terpilih dan membuat session lokal. Portal tidak mengirim password melalui redirect, URL, browser, atau respons token. Password tetap diverifikasi pada aplikasi klien hanya pada alur login lokal/non-SSO. Bila aplikasi klien memakai nama kolom berbeda, tim klien memetakan field tersebut ke padanan `username` dan `password` pada kode autentikasinya.

## Integrasi dan batas cakupan

Portal KarismaERP sudah menyediakan pengelolaan akun klien, penetapan akun per user Portal, dan klaim username akun pilihan untuk integrasi klien seperti KIU PO. Panduan penggunaan akun klien diperjelas agar admin dan tim klien menghubungkan field kredensial lokal dengan klaim SSO yang tersedia. Perubahan ini berupa dokumentasi; tidak mengubah protokol token atau mengirim password ke aplikasi klien.

### Klaim token yang perlu diperiksa klien

Respons sukses `POST /api/sso/token` memuat `data.user.sub`, `user_id`, `auth_source`, `username`, dan `name`, serta `data.app_code` dan `data.state`. Jika admin telah menetapkan akun klien per user, Portal menambahkan `data.client_account_username`. Klaim akun tersebut opsional dan hanya membawa username; password serta role/permission aplikasi klien tidak masuk respons.

Klien menggunakan `sub` sebagai kunci mapping eksternal yang stabil, memastikan `app_code` dan `state` cocok dengan transaksi aktif, lalu memeriksa status akun dan role lokal sebelum membuat session. `username` dan `name` dipakai sebagai atribut profil, bukan sebagai dasar tunggal penggabungan akun. Jika `client_account_username` diterima, klien mencocokkannya dengan akun lokal yang disediakan admin. Jika akun lokal tidak ada atau tidak aktif, klien menolak login atau menjalankan proses linking yang telah disetujui; jangan memberi role otomatis.

### Persiapan sisi aplikasi klien

| Komponen | Fungsi |
| --- | --- |
| Base URL Portal, `app_code`, secret server, callback HTTPS | Menentukan tujuan authorize/token, identitas klien, dan callback yang terdaftar persis. |
| HTTP client backend dan sertifikat HTTPS tepercaya | Mengirim pertukaran code dari server dengan validasi sertifikat; secret tidak pernah masuk browser. |
| Route start dengan `state` dan PKCE | Mengikat callback ke permintaan login yang dimulai klien dan membuktikan kepemilikan verifier saat code ditukar. |
| Route callback server | Memvalidasi state, mengirim POST token, memvalidasi respons dan klaim sebelum login lokal. |
| Tabel akun lokal serta mapping unik `sub` | Menghubungkan satu identitas Portal ke akun aplikasi klien tanpa bentrok username. |
| Akun lokal dan kolom username/password (bila login lokal juga dipakai) | Menyediakan akun dan kredensial lokal; pada SSO hanya username akun terpilih dicocokkan, bukan password. |
| Session, role, dan authorization lokal | Membuat session aman serta melindungi route, menu, aksi, dan data dengan policy aplikasi klien. |
| Logging dan prosedur support | Mencatat status/error tanpa secret, code, verifier, password, atau ID session. |

### Error dan penanganan awal

| Status/error | Arti dan tindakan |
| --- | --- |
| `400 invalid_request` | Parameter atau format request salah; periksa metode, field, encoding, state, challenge, dan verifier. |
| `400 invalid_grant` | Code tidak valid, kedaluwarsa/terpakai, atau secret/callback/verifier/status klien tidak cocok; mulai transaksi login baru dan cek konfigurasi server. |
| `403 access_denied` | User Portal belum diberi izin ke aplikasi; admin Portal memeriksa akses user-app. |
| `403 account_not_assigned` | Aplikasi memiliki daftar akun klien tetapi admin belum menetapkan akun untuk user Portal tersebut. |
| `503 temporarily_unavailable` | Portal gagal menyimpan/menerbitkan code; periksa layanan/database Portal dan mulai login baru. |
| `401 invalid_client` pada `login-event` | Secret atau `app_code` untuk request histori tidak valid; periksa secret aktif server klien. |
| `409 login_event_rejected` | Portal tidak mengonfirmasi pencatatan histori; periksa tabel histori dan log Portal sebelum menyatakan event tercatat. |
| `405 method_not_allowed` | Metode salah: authorize memakai GET, token dan login-event memakai POST. |

Setiap error harus mencegah pembuatan session klien. Jika token berhasil tetapi akses halaman ditolak, diagnosis beralih ke mapping, status user, role, atau permission lokal klien; klaim token tidak membawa hak akses tersebut.

## Database

Tidak ada perubahan skema database atau migration. Struktur akun Portal tetap mengikuti `docs/database/portal-sso-daftar-akun-klien-20261029.md`; database lokal aplikasi klien mengikuti skema yang sudah dimiliki klien.

## Berkas

- `docs/penggunaan/portal-sso-daftar-akun-klien.md`: petunjuk CSV dan nama field lokal untuk tim klien.
- `docs/database/portal-sso-daftar-akun-klien-20261029.md`: skema penyimpanan akun Portal, terpisah dari dokumentasi development.

## Validasi dan tindak lanjut

Perubahan dokumentasi ditinjau terhadap kontrak kode SSO saat ini: Portal mengirim `client_account_username`; password tidak masuk respons token. Belum dilakukan UAT langsung pada aplikasi klien. Tim klien perlu memeriksa nama kolom akun dan memastikan pemetaan username serta autentikasi password lokal sesuai schema aplikasi mereka.
