# Pengembangan Konfirmasi Penerimaan Berkas Expedisi

## Tujuan

Menyediakan alur terukur antara HRD4 sebagai penginput laporan expedisi dan Logistik sebagai penerima berkas fisik. Setiap konfirmasi memiliki waktu, petugas, dan catatan sehingga HRD4 dapat memantau penyelesaian dokumen tanpa mengandalkan konfirmasi lisan.

## Alur aplikasi

1. HRD4 membuka menu **Laporan Expedisi** lalu memilih **Input Laporan Baru**.
2. HRD4 mengisi data expedisi dan wajib memilih **Penerima Berkas** dari pengguna departemen Logistik. Field ini berada sebelum **Inputer**. Nilai awal Inputer mengikuti pengguna login, namun kolomnya dapat diubah untuk mencatat petugas Security Pos yang benar-benar melakukan input.
3. Sistem menyimpan laporan dengan status **Belum Diterima**.
4. Petugas Logistik yang dipilih masuk ke aplikasi. Akun `yunika` diarahkan langsung ke menu **Konfirmasi Berkas Expedisi**.
5. Halaman Logistik hanya menampilkan laporan yang ditujukan kepada pengguna aktif. Untuk status **Belum Diterima**, pengguna memilih **Konfirmasi**, mengisi catatan opsional, lalu menyimpan.
6. Sistem mengubah status menjadi **Sudah Diterima**, menyimpan penerima dan waktu konfirmasi, serta menambah riwayat audit.
7. HRD4 melihat kolom Penerima Berkas, Inputer, Status Berkas, Diterima Pada, dan tombol Riwayat pada daftar laporan.

## Batas hak akses

- Hanya username `HRD4` yang dapat menambah, mengubah, dan menghapus Laporan Expedisi.
- Petugas berdepartemen `LOGISTIK` hanya dapat melihat laporan dengan `penerima_berkas_id` miliknya.
- Server memverifikasi penerima yang login, status masih `BELUM_DITERIMA`, dan ID laporan sebelum menerima konfirmasi. Tombol pada halaman bukan satu-satunya pengamanan.
- Riwayat penerimaan merupakan audit trail. Konfirmasi tidak menghapus riwayat sebelumnya.

## Pengujian penerimaan

1. Login sebagai HRD4, buat laporan baru untuk Yunika, dan pastikan status awal **Belum Diterima**.
2. Login sebagai Yunika (`yunika` / password awal `1234`) dan pastikan hanya data yang ditujukan kepadanya tampil.
3. Konfirmasi salah satu berkas dan pastikan status berubah menjadi **Sudah Diterima** serta waktu penerimaan tampil pada HRD4.
4. Buka **Riwayat** dan pastikan nama Yunika, waktu, status, serta catatan tersimpan.
5. Uji URL konfirmasi dengan akun non-Logistik dan akun Logistik lain; sistem harus menolak akses tersebut.
