# Database UAT SSO KIU PO lokal — 31 Oktober 2026

Tidak ada perubahan skema database (no schema change). Database produksi tidak diakses.

## Data dan perubahan konfigurasi

- Portal: database lokal `kiucoid_karismaerp_local`.
- Klien aktif dari registry `kiu_po`: checkout `C:\xampp\htdocs\poerp`, database lokal `kiucoid_po`; bukan checkout `kiupobaru`.
- Assignment existing: `tb_users:27` (bram) → `picit`. Akun klien ditemukan tepat satu: `tb_user.id_user=13`, level 4. Izin dan assignment tidak diubah.
- Satu baris `tb_portal_sso_client` untuk `kiu_po` diperbarui: launch_uri dari `https://10.10.10.26/poerp` menjadi `https://10.10.10.26/poerp/sso/login`. Callback tetap `https://10.10.10.26/poerp/sso/callback`.
- Update memakai syarat nilai lama dan menghasilkan affected_rows=1. Snapshot sebelum perubahan: `C:\xampp\sso-uat-backups\20261031\kiu_po-registry-before.json`.
- Code sekali pakai dan histori dapat bertambah melalui login SSO normal. Tidak ada pengubahan password atau pembuatan akun uji.

## Rollback konfigurasi registry

Jalankan hanya jika perlu membatalkan URL peluncuran dan nilai saat ini masih sesuai hasil perubahan:

```sql
UPDATE tb_portal_sso_client
SET launch_uri = 'https://10.10.10.26/poerp'
WHERE app_code = 'kiu_po'
  AND launch_uri = 'https://10.10.10.26/poerp/sso/login';
```

Rollback ini mengembalikan perilaku halaman login lokal; bukan perbaikan SSO. Tidak diperlukan rollback DDL. Tabel `tb_user` klien tidak memiliki kolom status aktif; pemeriksaan lokal memastikan akun ada secara unik, username persis, kode terisi, dan level dashboard yang didukung (1–5). Jangan mengklaim pengujian status nonaktif tanpa mekanisme tersebut.
