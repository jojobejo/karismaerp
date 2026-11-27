# Tempo berdasarkan Produk Fokus pada Sales Order

## Aturan

- Sales Order dari gudang konsinyasi memakai tempo 90 hari untuk semua barang.
- Sales Order dari gudang biasa: Produk Fokus A, B, atau C memakai tempo 60 hari.
- Nilai Produk Fokus kosong, tanda `-`, atau nilai lainnya memakai tempo 30 hari.
- Barang dengan tempo berbeda tidak dapat digabung dalam satu Sales Order.

## Perubahan sistem

Modal **Pilih Barang** menampilkan kolom Produk Fokus dan Tempo. Setelah barang dipilih, nilai Produk Fokus dan tempo juga ditampilkan pada baris item. Validasi di form memberi notifikasi saat tempo item berbeda, dan controller memvalidasi ulang berdasarkan gudang serta master `tbpo_barang` sebelum Sales Order disimpan atau diperbarui.

Tempo dihitung dari master barang dan jenis gudang; nilai tersembunyi dari browser tidak digunakan sebagai sumber validasi.
