# Peta dokumentasi KARISMA ERP

Dokumentasi disusun menurut **jenis dokumen**, lalu **modul**. Nama berkas lama dipertahankan agar riwayat fitur dan tanggalnya tetap dapat ditelusuri.

| Modul | Development aplikasi | Database dan migrasi | Panduan penggunaan |
| --- | --- | --- | --- |
| Accounting dan keuangan | [Development](development/accounting/) | [Database](database/accounting/) | [Penggunaan](penggunaan/accounting/) |
| Dashboard | [Development](development/dashboard/) | [Database](database/dashboard/) | [Penggunaan](penggunaan/dashboard/) |
| HRD | [Development](development/hrd/) | [Database](database/hrd/) | — |
| Logistik | [Development](development/logistik/) | [Database](database/logistik/) | — |
| Manajemen user | [Development](development/manajemen-user/) | [Database](database/manajemen-user/) | — |
| Persediaan dan master barang | [Development](development/persediaan/) | [Database](database/persediaan/) | [Penggunaan](penggunaan/persediaan/) |
| Portal dan SSO | [Development](development/portal/) | [Database](database/portal/) | [Penggunaan](penggunaan/portal/) |
| Purchasing dan LPB | [Development](development/purchasing-lpb/) | [Database](database/purchasing-lpb/) | [Penggunaan](penggunaan/purchasing-lpb/) |
| Sales | [Development](development/sales/) | [Database](database/sales/) | [Penggunaan](penggunaan/sales/) |
| Stock opname | [Development](development/stockopname/) | [Database](database/stockopname/) | [Penggunaan](penggunaan/stockopname/) |
| Lintas modul | [Development](development/umum/) | [Database](database/umum/) | [Penggunaan](penggunaan/umum/) |

SQL migrasi di luar `docs/database/` berada di [database/sql/](../database/sql/), juga per modul. Dump acuan master tetap di `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`; dump, backup, dan hasil perbandingan di `db/` serta `database/backups/` bukan migrasi yang boleh dijalankan berurutan.

## Aturan penambahan dokumen

- Catatan implementasi aplikasi: `docs/development/<modul>/`.
- Perubahan skema, data, migrasi, rollback, atau catatan tanpa perubahan skema: `docs/database/<modul>/`.
- Langkah pengguna dan hak akses: `docs/penggunaan/<modul>/`.
- SQL migrasi: `docs/database/<modul>/` bila terkait langsung dengan dokumen di sana, atau `database/sql/<modul>/` untuk skrip operasional yang sudah memakai lokasi itu. Dokumen database harus merujuk path SQL yang tepat.
- Jangan menyalin SQL dump sebagai migrasi. Baca prasyarat, urutan, backup, dan rollback pada dokumen modul sebelum menjalankan skrip.

Penataan folder ini hanya memindahkan berkas dan memperbarui rujukan path. Tidak ada perubahan skema database atau eksekusi migrasi.
