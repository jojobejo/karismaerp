# Sinkronisasi Siap Loading Sales ke Checker

## Alur

Ketika Sales menekan **Siap Loading** pada halaman SO per rute:

1. Sistem memastikan rute terdaftar dan memiliki jenis `LK` atau `KK`.
2. SO berstatus `open` atau `partial` pada rute tersebut berubah menjadi `sedang_verifikasi`.
3. Rute dicatat pada tabel Checker sesuai jenisnya:
   - `LK` masuk ke `tb_loading_lk`.
   - `KK` masuk ke `tb_loading_kk`.
4. Status aktivitas Checker langsung menjadi `SIAP_LOADING`.

Jika aktivitas rute yang belum diarsipkan sudah tersedia, sistem memperbarui aktivitas tersebut dan tidak membuat data ganda.

## Contoh

Rute `JBR` terdaftar sebagai jenis `KK`. Setelah Sales menekan **Siap Loading**, rute `JBR` tampil pada tabel **Loading KK** di halaman `/checker`.

## Proses Checker Loading SO

1. Checker membuka `/checker/so_loading` dan menekan **Start** pada rute.
2. Sistem mengisi checker, `waktu_mulai`, status `PROSES_LOADING`, dan progres awal 0% pada aktivitas LK/KK.
3. Setiap pilihan **Muat** atau **Tidak Muat** memperbarui progres aktivitas berdasarkan perbandingan item yang sudah dipilih dengan seluruh item rute pada tanggal transaksi tersebut.
4. Progres selama proses dibatasi maksimal 99%.
5. Tombol **Selesai Loading** hanya dapat digunakan setelah seluruh item dipilih.
6. Saat selesai, sistem mengisi `waktu_selesai`, progres 100%, dan status `DONE`.
7. Kolom durasi pada halaman `/checker` dihitung dari `waktu_mulai` sampai `waktu_selesai`, dikurangi waktu pause jika ada.

Pada halaman detail, tombol **Pause** menghentikan perhitungan durasi dan menonaktifkan proses Muat/Tidak Muat serta Selesai Loading. Tombol berubah menjadi **Lanjutkan**; saat diklik, durasi pause diakumulasikan ke `total_pause_secs` dan proses dapat diteruskan tanpa menghitung waktu jeda.

Tombol **Siapkan Barang** tersedia di samping Pause setelah loading dimulai. Ketika dipilih, status Activity Warehouse menjadi `PENYIAPAN_BARANG` dan seluruh aksi Muat/Tidak Muat serta Selesai Loading dikunci. Tombol kemudian berubah menjadi **Selesai Siapkan**. Setelah penyiapan diselesaikan, status kembali menjadi `PROSES_LOADING` dan aksi loading aktif kembali.

Penyiapan barang hanya dapat dilakukan satu kali untuk setiap aktivitas loading. Setelah **Selesai Siapkan** dipilih, tombol penyiapan disembunyikan dan backend menolak penyiapan ulang.

Jika loading sudah `DONE` tetapi masih ada barang berstatus Tidak Muat, rute tetap ditampilkan pada daftar Checker sebagai **Proses Faktur**. Tombol Start tidak ditampilkan kembali karena proses loading sudah selesai; rute menunggu Admin SC melakukan repost atau proses faktur lanjutan.

Label **Proses Faktur** dapat diklik untuk melihat kembali seluruh hasil loading pada tanggal SO terkait, termasuk barang Dimuat dan Tidak Dimuat. Tombol **Detail** pada Activity Warehouse juga menampilkan daftar barang loading beserta status prosesnya. Peran `SALESCK` dapat membuka detail ini sebagai pemantauan hanya-baca dan tidak memperoleh tombol operasional Checker.

Validasi barang yang sudah masuk Delivery Order dilakukan per `id_so_detail`, bukan per header SO. Dengan demikian, jika sebagian barang dalam satu SO sudah pernah masuk DO, sisa barang yang belum masuk DO tetap wajib dipilih sebagai Muat atau Tidak Dimuat. Loading tidak dapat diselesaikan selama masih ada satu pun item berstatus Belum Diproses.

Tombol Start dan Muat/Tidak Muat tidak dapat digunakan sebelum aktivitas dimulai dan tersedia untuk peran Checker, Manager Checker, atau Admin Logistik.
