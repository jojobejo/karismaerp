# Import Faktur Z Eksternal

## Ringkasan

Fitur ini menambahkan kemampuan import Faktur Z yang dibuat di luar KARISMA ERP ke halaman `sales_order/pecah_faktur`. Faktur hasil import dapat langsung diproses dengan alur pemecahan yang sudah ada untuk menghasilkan Faktur H.

Import hanya membuat sumber data pemecahan. Proses ini tidak mengurangi stok, tidak membuat delivery order, dan tidak membentuk jurnal penjualan.

## Cara penggunaan

1. Buka menu **Pecah Faktur Z** (`sales_order/pecah_faktur`).
2. Klik tombol **Import Faktur Z** untuk membuka modal **Import Faktur Z Eksternal**, lalu klik **Unduh Template**.
3. Isi sheet **Data Faktur Z**. Satu baris mewakili satu barang dan data header harus diulang pada setiap baris faktur yang sama.
4. Gunakan sheet **Referensi Customer** dan **Referensi Barang** untuk memilih kode yang valid.
5. Pada modal **Import Faktur Z Eksternal**, pilih file `.xlsx` atau `.xls` yang sudah diisi, lalu klik **Proses Import**.
6. Setelah berhasil, Faktur Z tampil pada daftar faktur belum dipecah dan dapat diproses secara manual, massal, atau otomatis per kios.

## Kolom template

| Kolom | Wajib | Keterangan |
|---|---:|---|
| `no_faktur_z` | Ya | Unik, diawali huruf `Z`, maksimal 30 karakter. |
| `tanggal_faktur` | Ya | Format `YYYY-MM-DD`. |
| `kd_customer` | Ya | Harus tersedia pada master customer. |
| `gudang_id` | Tidak | Kode gudang referensi; jika kosong memakai wilayah pengguna atau penanda `EXTERNAL`. |
| `salesman` | Tidak | Nama salesman pada faktur asal. |
| `cara_pembayaran` | Tidak | Default `cash`, maksimal 20 karakter. |
| `tanggal_jatuh_tempo` | Tidak | Jika kosong menggunakan tanggal faktur. |
| `kd_barang` | Ya | Harus merupakan barang aktif pada master barang. |
| `qty_box` | Ya | Jumlah box, berupa bilangan bulat nol atau lebih. |
| `qty_pcs` | Ya | Tambahan pcs di luar box; nilainya harus lebih kecil dari `isi_per_box`. |
| `satuan` | Tidak | Jika kosong menggunakan satuan master barang. |
| `isi_per_box` | Tidak | Jika kosong atau nol menggunakan isi master barang. |
| `harga_satuan_z` | Ya | Harga per pcs yang sudah termasuk PPN, sebelum potongan pemecahan 20%. |
| `diskon_persen` | Tidak | Angka 0–100. |
| `pajak_persen` | Tidak | Angka 0–100. |
| `no_lot` | Tidak | Nomor lot barang. |
| `expired_date` | Tidak | Format `YYYY-MM-DD`; jika kosong disimpan sebagai referensi non-kedaluwarsa. |
| `berat_gram` | Tidak | Jika kosong menggunakan berat master barang. |
| `kubikasi_m3` | Tidak | Jika kosong menggunakan kubikasi master barang. |
| `catatan` | Tidak | Catatan sumber faktur eksternal. |

## Validasi dan keamanan data

- File maksimal 5 MB dan 5.000 baris data.
- Format kolom harus sesuai template.
- Total kuantitas terkecil dihitung dengan rumus `(qty_box × isi_per_box) + qty_pcs`.
- `total_harga` dihitung dari total pcs × harga include PPN setelah diskon. `pajak_persen` digunakan untuk mengekstrak DPP dan PPN, bukan menambahkan pajak ke harga.
- Template lama yang hanya memiliki kolom `qty` ditolak karena tidak dapat membedakan box dan pcs. Pengguna harus mengunduh template terbaru.
- Nomor faktur diperiksa pada tabel faktur dan sales order agar tidak terjadi duplikasi.
- Customer dan barang diverifikasi terhadap master aktif.
- Header faktur yang diulang harus konsisten untuk seluruh detail barang.
- Import memakai transaksi database. Jika satu baris gagal, seluruh file dibatalkan.
- File yang diunggah dibaca dari file sementara dan tidak disimpan permanen di server.

## Implementasi teknis

Untuk kompatibilitas penuh dengan mesin pemecahan yang sudah ada, setiap Faktur Z eksternal disimpan sebagai:

- Sales order referensi berawalan `IMPZ/`, berstatus `completed`, `is_faktur_z = 1`, dan sumber `EXTERNAL_Z` apabila kolom sumber tersedia.
- Faktur penjualan berstatus `draft` dengan sumber `EXTERNAL_Z`. Kombinasi ini hanya diaktifkan pada modul Pecah Faktur sehingga tidak masuk piutang maupun antrean logistik.
- Detail sales order dan detail faktur sebagai referensi kuantitas pemecahan.
- Log aktivitas dengan aksi `IMPORT_FAKTUR_Z`.

SO referensi `EXTERNAL_Z` disembunyikan dari daftar Sales Order reguler, dan status `draft` dikecualikan dari daftar persiapan delivery order.

Tidak ada perubahan struktur database yang diperlukan.
