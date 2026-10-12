# Database Konfirmasi Penerimaan Berkas Expedisi

## Skrip deployment

Jalankan [create_expedisi_penerimaan_20260928.sql](../../database/sql/create_expedisi_penerimaan_20260928.sql) pada database Karisma ERP setelah backup. Skrip dibuat idempoten untuk kolom dan tabel, serta tidak membuat ulang akun apabila username `yunika` sudah tersedia.

## Perubahan tabel `tb_expedisi`

| Kolom | Fungsi |
| --- | --- |
| `penerima_berkas_id` | ID pengguna Logistik yang ditetapkan HRD4. |
| `status_berkas` | Status `BELUM_DITERIMA` atau `SUDAH_DITERIMA`. |
| `diterima_oleh_id` | ID petugas yang benar-benar melakukan konfirmasi. |
| `diterima_pada` | Waktu konfirmasi diterima. |
| `catatan_penerimaan` | Catatan opsional dari Logistik. |

Kolom lama `nm_inputer` tetap digunakan. Form mengisinya dengan nilai awal pengguna login, tetapi HRD4 dapat mengubahnya agar nama petugas Security Pos yang benar-benar menginput tercatat dan tampil pada daftar laporan. Tidak ada penghapusan atau perubahan nilai data expedisi lama. Data lama tanpa penerima berkas tidak akan muncul pada antrean petugas Logistik sampai ditetapkan penerimanya.

## Tabel audit baru

`tb_expedisi_penerimaan_riwayat` menyimpan satu rekam audit untuk setiap konfirmasi sukses, dengan ID laporan, ID penerima, status, catatan, waktu penerimaan, dan waktu pembuatan rekam.

## Akun awal

Skrip menambahkan akun `yunika` di `tb_user` dengan departemen `LOGISTIK`, akses level `1`, dan password awal yang telah di-hash untuk nilai `1234`. Password awal harus diganti oleh administrator setelah validasi login pertama.
