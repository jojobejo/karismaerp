# Input Nominal Format Indonesia pada Admin Transaksi

## Masalah

Kolom harga dan subtotal pada modal Edit Transaksi sebelumnya menggunakan input HTML `number` dan `parseFloat()`. Format Indonesia seperti `63.963,96` tidak kompatibel dengan parser tersebut dan dapat terbaca sebagai deretan digit `6396396`.

## Perbaikan

- Kolom harga dan subtotal menerima format Indonesia melalui input teks dengan papan ketik desimal.
- Nilai ditampilkan menggunakan pemisah ribuan titik dan desimal koma.
- Kalkulasi subtotal menggunakan parser angka lokal.
- Sebelum request dikirim, angka dinormalisasi ke format mesin, misalnya `63.963,96` menjadi `63963.96`.
- Model LPB melakukan parsing ulang di sisi server sebagai perlindungan apabila endpoint dipanggil tanpa antarmuka web.

## Contoh

| Input pengguna | Nilai tersimpan |
|---|---:|
| `63.963,96` | `63963.96` |
| `63.963,964` | `63963.964` |
| `63963.96` | `63963.96` |

Perubahan nilai transaksi tetap menjalankan sinkronisasi jurnal sesuai alur Admin Transaksi.
