# Data Registry Portal: URL Peluncuran Langsung SSO

Tidak ada perubahan schema atau migration database. Perubahan hanya memperbarui nilai `launch_uri` untuk klien aktif `kiu_po` pada database Portal lokal.

- Sebelum: `https://10.10.10.26/poerp/`
- Sesudah: `https://10.10.10.26/poerp/sso/login`
- Callback dipertahankan: `https://10.10.10.26/poerp/sso/callback`
- Client secret tidak dirotasi.

Perubahan sudah diterapkan dan diverifikasi ulang pada database Portal lokal. SQL pembaruan dengan prasyarat nilai lama tersedia di `portal_sso_launch_direct_20261030.sql`; pemulihan tersedia di `portal_sso_launch_direct_rollback_20261030.sql`.
