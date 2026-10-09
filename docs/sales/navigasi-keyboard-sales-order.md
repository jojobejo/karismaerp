# Navigasi Keyboard Form Sales Order

## Tujuan

Form pembuatan dan pengeditan Sales Order dapat dioperasikan menggunakan keyboard tanpa perpindahan fokus ke navbar, sidebar, breadcrumb, atau footer.

## Perilaku Tab

- Saat halaman dibuka, fokus berada pada kolom Nomor SO.
- `Tab` berpindah maju antar-field dan tombol kerja di dalam form.
- `Shift + Tab` berpindah mundur.
- Setelah field terakhir, fokus kembali ke field pertama dalam form.
- Field readonly informatif dilewati, kecuali field Customer yang membuka dialog pemilihan.
- Ketika modal Customer atau Barang aktif, fokus Tab dibatasi di dalam modal tersebut.

## Pemilihan Customer dan Barang

1. Fokus pada Customer otomatis membuka modal dan menempatkan kursor di pencarian.
2. Ketik kata pencarian, lalu tekan panah bawah untuk menuju hasil.
3. Gunakan panah atas/bawah untuk berpindah hasil dan `Enter` untuk memilih.
4. Setelah Customer dipilih, fokus berpindah ke Gudang.
5. Pada modal Barang, mekanisme pencarian dan pemilihan sama. Setelah barang dipilih, fokus berpindah ke Qty Box pada baris aktif.

Tombol `Enter` pada field form tetap dapat digunakan untuk maju ke field berikutnya, kecuali pada textarea.
