# Filter Laporan Penjualan

Buka **Laporan → Penjualan → tab Penjualan**, lalu pilih laporan. Gunakan **Filter Data** untuk mengubah kriteria dan **OK** untuk memuat hasil. **Batal** membuang perubahan yang belum diterapkan. Periode awal mengikuti bulan berjalan.

| Laporan | Filter yang tersedia |
| --- | --- |
| Penjualan dan retur | Nama dan klasifikasi pelanggan, karyawan dan klasifikasi salesman, periode, status transaksi |
| eFaktur Penjualan | Nama dan klasifikasi pelanggan, periode, status ekspor, keberadaan nomor seri |
| Jurnal Penjualan dan Jurnal Potongan Penjualan | Periode pada tab Standar, keterangan dan nomor jurnal pada tab Pencarian |
| Uang Muka | Nama pelanggan atau supplier, periode, status transaksi kas |

Klasifikasi pelanggan memakai karakteristik kios dari master pelanggan. Klasifikasi salesman memakai pengelompokan berdasarkan nama sales pada master pelanggan dan memakai nama salesman dari transaksi bila kelompoknya kosong.

Laporan Uang Muka mengambil nilai baris akun uang muka pada kas masuk dan kas keluar. Akun dikenali dari pemetaan aktif `UANG_MUKA_CUSTOMER` dan `UANG_MUKA_SUPPLIER`, serta kode akun standar `22110` dan `15110`.

Sumber faktur penjualan saat ini belum menyediakan status ekspor atau nomor seri eFaktur. Pilihan **Ekspor tercatat** dan **Ada nomor seri** menghasilkan daftar kosong selama informasi tersebut belum tersedia dari sumber laporan.
