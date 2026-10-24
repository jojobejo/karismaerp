# Prasyarat Database Integrasi SSO Klien

## Batas data

Portal dan aplikasi klien memiliki database terpisah. Portal tidak membuat akun, tabel mapping, role, atau permission pada database klien.

## Database Portal

Pastikan migration berikut sudah diterapkan pada database Portal target:

| Berkas | Kegunaan |
|---|---|
| `portal_sso_20261022.sql` | Tabel registrasi klien dan code otorisasi sementara. |
| `portal_sso_secret_reveal_20261022.sql` | Kolom secret terenkripsi untuk administrasi Portal. |
| `portal_sso_launch_uri_20261024.sql` | Kolom URL peluncuran terpisah dari callback. |

Detail dan rollback tiap migration ada di folder `docs/database/`. Tabel hak akses user aplikasi adalah tabel Portal yang sudah ada; flow ini tidak memerlukan tabel izin baru. Jalankan setiap migration hanya jika skema belum tersedia, setelah backup.

## Database aplikasi klien

Klien perlu menyimpan link antara subject Portal yang stabil dan akun lokal. Gunakan tabel/struktur yang sudah ada bila memenuhi syarat berikut:

- `external_sub` menyimpan nilai `data.user.sub` persis, tidak hanya username.
- Simpan sumber/issuer Portal dan `local_user_id` yang mengacu ke primary key akun lokal.
- Unique constraint pada `(provider, external_sub)` mencegah subject dipakai lebih dari satu kali.
- Foreign key ke akun lokal menjaga referensi tetap valid; gunakan aturan `ON DELETE RESTRICT` atau kebijakan setara.
- Jika kebijakan bisnis satu akun lokal hanya boleh terkait satu identitas Portal, tambahkan unique constraint `(provider, local_user_id)`.
- Role dan permission tetap disimpan di schema aplikasi klien. Secret klien, authorization code, dan verifier tidak disimpan sebagai data permanen akun.

Nama tabel, tipe primary key, engine, dan migration harus disesuaikan dengan database klien. Contoh struktur konseptual:

```text
client_identity_link
  id                 primary key
  provider           issuer Portal yang dikonfigurasi
  external_sub       subject Portal (unique bersama provider)
  local_user_id      foreign key ke akun lokal
  created_at
  updated_at
```

### Aturan pemetaan

1. Jika subject telah terpetakan, gunakan `local_user_id` dari mapping; jangan pindah akun hanya karena username berubah.
2. Untuk login pertama, klien boleh meminta admin menautkan akun secara eksplisit atau mencocokkan username hanya bila hasilnya tepat satu akun aktif. Simpan `sub` setelah tautan berhasil.
3. Tolak subject duplikat, akun lokal tidak ada/nonaktif, mapping konflik, atau role lokal yang tidak valid.
4. User dan level akses diverifikasi ulang berdasarkan aturan lokal klien sebelum session dibuat.

## Perubahan skema pada pembaruan Oktober 2026

Dokumentasi dan flow autentikasi Portal tidak menambah schema Portal selain migration URL peluncuran di atas. Klien menentukan sendiri apakah membutuhkan tabel mapping baru; tidak ada DDL universal yang aman untuk semua skema akun. Pembaruan pergantian akun/session hanya perubahan perilaku aplikasi klien dan tidak memerlukan DDL Portal tambahan.
