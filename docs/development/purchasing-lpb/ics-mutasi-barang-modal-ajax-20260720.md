# Development Aplikasi - ICS Mutasi Barang Modal AJAX

Tanggal: 2026-07-20

## Scope

Route utama:

- `ics/mutasi_barang/input`

File yang dikembangkan:

- `application/views/content/logistik/ics/input_mutasi_barang.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Ics.php`

## Ringkasan Perubahan

- View input mutasi barang dibentuk ulang menjadi layar operasional "Pemindahan Barang Antar Gudang" dengan grid utama seperti referensi UI.
- Modal `Data Persediaan` memuat barang dari gudang asal via AJAX tanpa reload.
- Modal `Input Lot` menampilkan lot/expired/qty/satuan yang dipilih untuk barang draft.
- Modal `Data Lot Barang` memuat pilihan lot barang via AJAX berdasarkan barang dan gudang asal.
- Pemilihan barang, pemilihan lot, update qty, hapus baris, dan rekam mutasi berjalan dengan jQuery AJAX.
- SweetAlert digunakan untuk validasi, konfirmasi hapus/batal, dan notifikasi sukses/gagal.
- Setelah rekam final sukses, halaman tidak reload. Draft dikosongkan via AJAX dan nomor referensi baru dikirim dari backend.
- Grid utama `input_tmp_mutasi` sekarang default menampilkan 5 baris kosong dan otomatis menyediakan baris kosong tambahan ketika jumlah draft mencapai batas tersebut.
- Grid utama di halaman input mutasi tetap menjadi area lihat draft dan memilih baris yang akan dihapus.
- Klik baris grid utama tidak lagi membuka modal input lot.
- Kolom `Jumlah` bisa diklik untuk update qty inline via AJAX tanpa reload.
- Kolom `Satuan` bisa diklik untuk update satuan inline memakai Select2 dari master `tbpo_satuan`.
- Kolom aksi diganti dari `Pjk` menjadi `#` dan berisi tombol icon edit.
- Tombol edit membuka modal `Edit Barang Mutasi` dengan pola form seperti modal input barang mutasi: qty, Select2 No Lot, Select2 Expired Date, dan readonly Qty Stock.
- Dashboard `ics/mutasi_barang` menampilkan 1 baris master per `noreff`/faktur mutasi, walaupun di dalam faktur terdapat beberapa barang.
- Filter dashboard mutasi memakai query header-level yang sama dan mengirim parameter status dari dropdown filter.
- Join inputer dashboard mutasi dibuat ke subquery karyawan unik per NIK agar duplicate `tb_karyawan.nik` tidak menggandakan baris faktur.
- Modal `Data Persediaan` dibatasi 10 data per halaman.
- Klik baris pada modal `Data Lot Barang` langsung menyimpan lot/expired/qty ke tabel input lot temporary dan database `tb_tmp_mutasi`.
- Validasi stok lot dilakukan saat simpan input barang dan rekam final.
- Alur `Data Persediaan` diubah dari modal menjadi halaman view baru `ics/mutasi_barang/list_barang`.
- Tombol `List Barang` ditambahkan sejajar dengan pilihan `Dari Gudang` dan `Ke Gudang` pada halaman input mutasi.
- Halaman `Data Persediaan` membuka modal input melalui double-click baris barang.
- Modal input pada halaman `Data Persediaan` menyediakan Select2 `No Lot`, Select2 `Expired Date`, readonly `Qty Stock`, dan input `Jumlah`.
- Tabel draft mutasi menampilkan kolom tambahan `No Lot` dan `Expired Date`.
- Modal input barang mutasi dibuat lebih dinamis dengan ringkasan barang, panel input, panel stok tersedia, konfirmasi rekam, dan SweetAlert/toast pada event penting.

## Endpoint AJAX

- `ics/ajax_list_barang_mutasi_gudang`
  - Parameter: `id_gudang`, `term`, `page`, `per_page`
  - Fungsi: memuat daftar barang persediaan gudang asal.
- `ics/mutasi_barang/list_barang`
  - Parameter: `fromgdg`, `tujuangdg`
  - Fungsi: halaman view baru `Data Persediaan`.
- `ics/ajax_mutasi_lot_select2`
  - Parameter: `id_gudang`, `kode_barang_system`, `nama_barang`, `term`
  - Fungsi: Select2 searchable untuk No Lot berdasarkan barang dan gudang asal.
- `ics/ajax_mutasi_exp_select2`
  - Parameter: `id_gudang`, `kode_barang_system`, `nama_barang`, `no_lot`, `term`
  - Fungsi: Select2 searchable untuk Expired Date berdasarkan No Lot terpilih.
- `ics/ajax_mutasi_lot_qty`
  - Parameter: `id_gudang`, `kode_barang_system`, `nama_barang`, `no_lot`, `exp_date`
  - Fungsi: memuat readonly Qty Stock tersedia untuk kombinasi lot dan expired.
- `ics/ajax_add_tmp_mutasi`
  - Parameter POST: `kode_barang`, `kode_barang_system`, `nama_barang`, `qty`, `satuan_id`, `gudang_asal`
  - Fungsi: menambah barang ke draft temporary dan mengembalikan `id` temporary.
- `ics/ajax_lot_tmp_mutasi`
  - Parameter: `id`, `id_gudang`, `term`, `page`, `per_page`
  - Fungsi: memuat lot/expired tersedia untuk barang temporary.
- `ics/ajax_update_tmp_mutasi`
  - Parameter POST: `id`, `id_gudang`, `no_lot`, `exp_date`, `qty`, `satuan_id`
  - Fungsi: menyimpan update lot, expired date, qty, dan satuan pada draft.
- `ics/ajax_update_tmp_mutasi_field`
  - Parameter POST: `id`, `field`, `value`, `id_gudang`
  - Fungsi: menyimpan update inline kolom `qty` atau `satuan_id` pada draft.
- `ics/ajax_delete_tmp_mutasi`
  - Parameter POST: `id`
  - Fungsi: menghapus baris draft.
- `ics/ajax_rekam_mutasi`
  - Parameter POST: header mutasi.
  - Fungsi: menyimpan final mutasi dan mengembalikan `new_ref` untuk input berikutnya.
- `ics/ajax_filter_mutasi`
  - Parameter: `gudang`, `tanggal`/`daterange`, `status`
  - Fungsi: memfilter dashboard mutasi pada level master faktur, bukan detail barang.

## Catatan Teknis

- Gudang asal dikunci ketika draft sudah memiliki barang agar sumber stock tidak berubah di tengah input.
- Backend menolak transaksi jika `Dari Gudang` dan `Ke Gudang` sama.
- Halaman `Data Persediaan` menggunakan 10 baris per request; pilihan lot dan expired date memakai Select2 AJAX.
- Update inline kolom `Satuan` mengambil opsi dari `tbpo_satuan` yang sudah dikirim controller ke view.
- Endpoint `ajax_add_tmp_mutasi` kini memvalidasi stok database jika payload sudah membawa `no_lot` dan `exp_date`.
