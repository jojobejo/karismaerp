# Database Dashboard Menu Retur

Tanggal: 2026-07-22

## Status Database

Tidak ada perubahan struktur database.

## Catatan

Perubahan tombol `Retur` dilakukan dari code dashboard statis di `M_Dashboard::module_sections()`. Tidak ada migrasi, insert, update, atau perubahan tabel menu.

Jika di kemudian hari menu dashboard dipindahkan penuh ke `tb_menu`, route yang perlu didaftarkan adalah:

```text
ics/retur
```

Hak akses tetap perlu mengikuti rule Logistik, Purchasing, Keuangan, dan IT seperti yang sudah dijaga di controller.
