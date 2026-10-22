# Perbaikan Ajax DataTables User Management

## Gejala dan penyebab

Endpoint `master/user-management/list` menghasilkan HTTP 500 ketika skema `tb_karyawan` tidak memiliki kolom `jabatan`. Ekspresi fallback `NULL AS jabatan` sebelumnya digabungkan dengan daftar field yang otomatis di-escape CodeIgniter Query Builder sehingga `NULL` diperlakukan sebagai identifier SQL.

## Perbaikan

Field tabel tetap diproses dengan escaping standar. Ekspresi `NULL AS jabatan` kini ditambahkan terpisah dengan escaping dinonaktifkan. Jika kolom `jabatan` tersedia pada skema lain, model tetap memilih `k.jabatan`.

Perubahan hanya pada aplikasi dan tidak memerlukan perubahan skema database.

## Validasi

Pemeriksaan sintaks PHP pada model lolos. Respons DataTables pada browser perlu dimuat ulang untuk verifikasi tampilan.
