# Sinkronisasi Status Trip Setelah Loading

## Masalah

Aktivitas loading sudah berstatus `DONE`, tetapi badge pada bagian **Trip Pengiriman Aktif** masih menampilkan `PROSES_LOADING`.

## Penyebab

Proses selesai loading hanya memperbarui tabel `tb_loading_lk` atau `tb_loading_kk`. Status terkait pada `tb_delivery_trip` tidak ikut diperbarui.

## Perbaikan

- Menambahkan status trip `PROSES_FAKTUR`.
- Penyelesaian loading memperbarui aktivitas loading dan trip dalam satu transaksi database.
- Daftar trip aktif tetap menampilkan trip tersebut dengan badge **Proses Faktur**.
- Migrasi menyinkronkan trip lama yang loading-nya sudah `DONE` tetapi status trip masih `PROSES_LOADING`.

## Alur Status

`SIAP_LOADING` → `PROSES_LOADING` → `PROSES_FAKTUR`

Trip tambahan mengikuti `PROSES_TAMBAHAN` → `PROSES_FAKTUR` setelah loading tambahannya selesai.
