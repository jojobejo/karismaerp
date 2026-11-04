# Tabel `portal_user`

## Tujuan

Mencatat akun ERP yang diberi akses ke portal tanpa menggandakan identitas, profil karyawan, atau password. `tb_users` tetap menjadi sumber identitas dan autentikasi. Tabel ini hanya menyimpan hubungan akun dan status akses portal.

## Struktur

| Kolom | Fungsi |
|---|---|
| `id` | Primary key tabel portal. |
| `user_id` | ID akun pada `tb_users`; unik agar satu akun hanya memiliki satu baris konfigurasi portal. |
| `status` | Status akses khusus portal (`1` aktif, `0` nonaktif), terpisah dari status akun ERP. |
| `created_at`, `updated_at` | Waktu pembuatan dan perubahan data. |

Foreign key membatasi `user_id` ke akun yang benar-benar ada pada `tb_users`. Penghapusan akun yang masih direferensikan ditolak (`RESTRICT`); nonaktifkan akses atau tangani relasinya secara eksplisit sebelum menghapus akun.

## Pembuatan

Jalankan [portal_user_20261024.sql](portal_user_20261024.sql) pada database yang sudah memiliki tabel `tb_users` dengan primary key `id int(11)`. Skrip menggunakan `CREATE TABLE IF NOT EXISTS`, sehingga tabel yang sudah ada tidak ditimpa.

Contoh memberi akses portal ke akun ERP ID 123:

```sql
INSERT INTO `portal_user` (`user_id`, `status`)
VALUES (123, 1);
```

Nonaktifkan akses portal tanpa mengubah status akun ERP:

```sql
UPDATE `portal_user`
SET `status` = 0
WHERE `user_id` = 123;
```

## Batasan dan catatan

- Skrip ini hanya menyiapkan struktur dan tidak memindahkan data dari `tb_karyawan` atau `tb_users`.
- Jangan mencocokkan atau menggabungkan akun hanya berdasarkan username/nama; audit NIK dan pemilik akun sebelum membuat relasi.
- `tb_karyawan` tidak dijadikan foreign key karena lampiran menunjukkan identitas yang dapat berbeda dari akun `tb_users` dan perlu pemetaan bisnis yang tervalidasi.
- Belum ada perubahan skema yang dijalankan pada database; penerapan memerlukan eksekusi skrip pada lingkungan target.
