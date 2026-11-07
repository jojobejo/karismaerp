# Diagnosis ERR_SSL_PROTOCOL_ERROR Portal production

Tanggal pemeriksaan: 5 November 2026. Host: `portal.karismaerp.com`.

## Hasil pemeriksaan

- `curl -I http://portal.karismaerp.com/` mengembalikan `301` ke `https://portal.karismaerp.com/`. Respons dikirim melalui Hostinger CDN (`Server: hcdn`, `platform: hostinger`); asal aturan redirect belum dapat dipastikan dari header ini saja.
- `curl -I https://portal.karismaerp.com/` gagal pada negosiasi TLS dengan `SEC_E_ILLEGAL_MESSAGE`. Percobaan dengan `curl -k` juga gagal. `openssl s_client` menerima `tlsv1 alert internal error` (alert 80). Kegagalan bukan sekadar pemeriksaan kepercayaan sertifikat di klien uji.
- Pemeriksaan ulang terhadap dua alamat IPv4 DNS yang terjawab (`185.124.137.202` dan `88.223.91.206`) sama-sama gagal pada handshake HTTPS. Alamat CDN dapat berubah; jangan menghapus record DNS hanya berdasarkan keluaran ini.
- Handshake HTTPS gagal sebelum request mencapai CodeIgniter. PHP, `application/config/config.php`, dan `.htaccess` tidak dapat memperbaiki handshake TLS pada sisi CDN/hosting.
- HTTPS lokal `https://localhost/karismaerp/` berhasil merespons HTTP 200. Hasil ini hanya membuktikan konfigurasi TLS XAMPP lokal berfungsi.
- Pemilik hosting melaporkan status SSL **Active** dan CDN **aktif** di hPanel. Status SSL di panel belum membuktikan bahwa handshake HTTPS pada jalur CDN berhasil.
- Laporan pemeriksaan hosting berikutnya menyatakan **belum ada sertifikat SSL yang terpasang untuk `portal.karismaerp.com`** dan mengusulkan pemasangan Lifetime SSL. Usulan tersebut belum diterapkan karena perubahan dinonaktifkan pada pemeriksaan itu. Informasi ini berasal dari laporan hosting yang diberikan pemilik, bukan hasil akses langsung ke hPanel dari workspace. Perbedaan dengan status Active sebelumnya perlu dicocokkan pada entri subdomain dan website yang sama.

## Tindakan pada hosting

1. Di hPanel, pastikan subdomain `portal.karismaerp.com` terdaftar pada website/hosting yang benar. Periksa DNS A/AAAA/CNAME dan status CDN khusus subdomain tersebut. Cocokkan tujuan DNS dengan konfigurasi website di hPanel. Jika CDN mengelola DNS, jangan mengubah record kelolaannya secara manual.
2. Buka **Websites → Manage site → Security → SSL** untuk entri website **`portal.karismaerp.com`**, bukan hanya domain induk atau website lain. Cocokkan status dengan laporan hosting terbaru. Bila belum terpasang, pilih **Install SSL** untuk Lifetime SSL. Tunggu sampai statusnya Active. Pemasangan ini belum dilakukan dalam diagnosis workspace.
3. Jika status untuk subdomain yang tepat sudah Active tetapi HTTPS masih gagal, periksa **Performance → CDN**. Hostinger menyediakan **Disable** sementara sebagai uji isolasi; langkah ini mengubah rute DNS ke hosting langsung, sehingga tunggu propagasi sebelum menguji ulang. Bila HTTPS pulih tanpa CDN, laporkan masalah edge CDN ke Hostinger sebelum mengaktifkannya kembali. Sertakan host, waktu kejadian, hasil `curl`, dan ID request dari respons HTTP, misalnya `b2361609afa88268f28a5e7a7724d873-dci-edge8` pada pemeriksaan 5 November 2026. Jika HTTPS tetap gagal tanpa CDN, periksa pemetaan subdomain dan sertifikat pada origin hosting bersama dukungan Hostinger.
4. Setelah SSL pulih, jalankan `curl.exe -I https://portal.karismaerp.com/` tanpa `-k`. Pastikan ada respons HTTP, lalu uji halaman login dan SSO dari browser. Hasil TLS sukses saja belum membuktikan koneksi database atau login berfungsi.

Jangan membuka login Portal melalui HTTP sebagai jalan pintas: kredensial, cookie sesi, dan alur SSO membutuhkan HTTPS. Perubahan aplikasi lokal tidak perlu dideploy untuk insiden handshake ini; konfigurasi TLS hosting harus diperbaiki terlebih dahulu.

Referensi: [panduan Hostinger untuk ERR_SSL_PROTOCOL_ERROR](https://www.hostinger.com/support/4469061-how-to-solve-err_ssl_protocol_error-at-hostinger/), [pemasangan Lifetime SSL](https://www.hostinger.com/support/1583258-how-to-install-lifetime-ssl-at-hostinger/), [diagnosis CDN Hostinger](https://www.hostinger.com/support/hostinger-cdn-troubleshooting-website-errors/).
