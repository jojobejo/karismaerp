# Dokumentasi Penggunaan Stock Opname

Tanggal dokumen: 2026-07-05  
Modul: Stockopname Karisma ERP

## Ringkasan Akses

| Peran | Menu/URL utama | Fungsi |
| --- | --- | --- |
| User stockopname | `stockopname/input` | Scan QR, input manual, opname request, histori input |
| Supervisor opname | `supervisi-opname` | Cek scan/manual, afirmasi request, lihat hasil wilayah |
| Supervisor opname | `supervisi-opname/tracking` | Tracking perbandingan Tim 1 dan Tim 2 per wilayah |
| Admin | `admin/stockopname` dan `admin/stockopname/monitoring` | Monitoring, koreksi data, master opname, pending, export |

## Penggunaan Untuk User Stockopname

Syarat akses:

- Login memakai user dengan `lv = 1`.
- Jobdesk user adalah `STOCKOPNAME`.
- User sudah memiliki data `tim` dan `wilayah` pada session/login.

Langkah input dengan scan QR:

1. Buka menu `Input Opname`.
2. Klik tombol `Scan`.
3. Arahkan kamera ke QR kartu stock.
4. Setelah QR terbaca, sistem menampilkan `Nama Barang` dan `Expired Date`.
5. Isi `Qty Box` dan/atau `Qty Pcs`.
6. Pastikan total qty sudah benar.
7. Klik `Input Opname`.
8. Data masuk ke hasil opname dengan sumber input `scan_qrcode`.

Langkah input manual:

1. Buka menu `Input Opname`.
2. Klik tombol `Input Manual`.
3. Pilih `Nama Barang`.
4. Pilih `Expired Date`.
5. Isi `Qty Box` dan/atau `Qty Pcs`.
6. Klik `Input Opname`.
7. Data manual langsung masuk ke hasil opname dan juga dicatat pada tabel manual input.

Langkah membuat opname request:

1. Buka menu `Input Opname`.
2. Klik tombol `Opname Request`.
3. Pilih `Nama Barang`.
4. Isi `Expired Date` sesuai barang fisik.
5. Isi `Qty Box` dan/atau `Qty Pcs`.
6. Klik `Input Opname`.
7. Request tersimpan untuk diafirmasi supervisor/admin sebelum masuk final sebagai hasil opname.

Melihat histori input:

1. Dari halaman `Input Opname`, klik `Histori Input`.
2. Sistem menampilkan daftar input milik user login.
3. Jika ada kesalahan dan tombol hapus tersedia, user dapat menghapus histori input miliknya sesuai aturan aplikasi.

Catatan penting:

- Minimal salah satu dari `Qty Box` atau `Qty Pcs` wajib diisi.
- Total qty dihitung dari `qty = (qty_box * dimensi) + qty_pcs`.
- Jika scan QR tidak menemukan barang, gunakan `Input Manual` atau buat `Opname Request`.

## Penggunaan Untuk Supervisor Opname

Syarat akses:

- Login memakai user dengan `lv = 1`.
- Jobdesk dikenali sebagai `SUPERVISIOR_OPNAME`, `SUPERVISOR_OPNAME`, atau variasi dengan spasi/tanda hubung.
- Supervisor hanya melihat wilayah yang berada pada kewenangannya.

Melakukan cek QR:

1. Buka menu `Supervisi Opname`.
2. Klik tombol `Scan`.
3. Klik `Buka Scan`.
4. Scan QR kartu stock.
5. Sistem menampilkan identitas barang, expired date, dan dimensi.
6. Panel ini dipakai untuk cek data, bukan untuk menyimpan qty opname.

Melakukan cek manual:

1. Buka menu `Supervisi Opname`.
2. Klik tombol `Input Manual`.
3. Pilih `Nama Barang`.
4. Pilih `Expired Date`.
5. Sistem menampilkan data barang yang dipilih.
6. Panel ini dipakai untuk verifikasi, bukan untuk menyimpan qty opname.

Afirmasi request opname:

1. Pada halaman `Supervisi Opname`, lihat bagian `Daftar Request Opname`.
2. Gunakan `Filter Wilayah` bila supervisor menangani lebih dari satu wilayah.
3. Gunakan kolom `Cari Nama Barang Request Opname` bila data request banyak.
4. Periksa nama barang, expired date, wilayah, inputer, dan qty request.
5. Klik `Afirmasi Request`.
6. Setelah dikonfirmasi, request masuk ke hasil opname sesuai data yang diajukan user.

Tracking inputer wilayah:

1. Buka menu `Tracking Inputer Wilayah`.
2. Pilih `Filter Wilayah Supervisi`.
3. Gunakan pencarian nama/kode barang jika diperlukan.
4. Filter status dapat diisi `SAMA` atau `RE-CHECK`.
5. Tabel menampilkan perbandingan `Qty Tim 1` dan `Qty Tim 2`.

Arti status tracking:

- `SAMA`: Tim 1 dan Tim 2 sama-sama sudah input dan total qty sama.
- `RE-CHECK`: salah satu tim belum input atau total qty Tim 1 dan Tim 2 berbeda.

## Penggunaan Untuk Admin

Monitoring opname:

1. Buka `admin/stockopname/monitoring`.
2. Pantau card `Result Tim 1` dan `Result Tim 2`.
3. Periksa progress input, compare all barang, dan compare by expired date.
4. Gunakan `Refresh` untuk memuat data terbaru.
5. Gunakan tombol export Excel untuk kebutuhan laporan:
   - `Compare Stock Buku vs Stock Opname - All Barang`
   - `Compare Stock Buku vs Stock Opname - By Expired Date`
   - `Data master Opname All Barang`
   - `Data Master Opname Barang with Expired Date`
   - `Data Opname`

Monitoring request dan manual:

1. Dari monitoring, buka `Request Opname User` untuk melihat request user.
2. Gunakan tab `Request Masuk` dan `Sudah Diafirmasi`.
3. Filter data berdasarkan tim, wilayah, atau inputer.
4. Pilih satu atau beberapa data, lalu klik `Afirmasi`.
5. Untuk input manual, buka `Input Manual Opname User`.
6. Pilih data manual, lalu klik `Bulk Afirmasi Manual Input` jika data perlu diafirmasi.

Detail input opname:

1. Dari monitoring atau detail pending, buka `Detail Input Opname`.
2. Lihat ringkasan kode barang, stock buku, qty Tim 1, qty Tim 2, dan status.
3. Pada `Stock Buku Per Expired Date`, admin dapat melihat stock utama, stock buku 0, pending, dan status per expired date.
4. Pada `Data Hasil Input Opname`, admin dapat melihat input per tim.
5. Admin dapat melakukan tambah input, update qty, delete input, repost dari recycle, tambah request ke hasil opname, atau hapus request sesuai tombol yang tersedia.

Kelola barang pending:

1. Buka `admin/stockopname/barang-pending`.
2. Pilih kode barang dari master opname.
3. Pilih expired date.
4. Isi kode faktur bila diminta.
5. Isi `Qty Box` dan/atau `Qty Pcs`.
6. Sistem menghitung total qty otomatis dari dimensi.
7. Klik simpan.
8. Data pending tersinkron ke `stockopname_master_item` berdasarkan `nama_barang + expired_date`.

Detail pending opname:

1. Buka `admin/stockopname/monitoring/pending-opname`.
2. Lihat total item, total qty, total pcs, dan total box.
3. Pilih mode hitung pending:
   - `Qty dasar + pending`
   - `Qty dasar - pending`
4. Klik simpan untuk menerapkan mode dan resync pending.
5. Gunakan pencarian untuk mencari kode, nama, expired, atau lot.
6. Gunakan tombol lihat detail, edit, atau delete pada baris pending.

Master opname dan QR:

1. Buka `admin/stockopname/master_opname`.
2. Pantau total data, QR selesai, QR pending, dan QR gagal.
3. Klik `Generate QR Code` untuk generate batch QR.
4. Klik `Retry Data Gagal` untuk mengulang data QR yang gagal.
5. Pilih barang untuk melihat preview asset.
6. Gunakan `Print Preview Asset`, `Print Kartu Stock`, atau `Print Sebagian` sesuai kebutuhan.

Master barang katalog:

1. Buka `admin/stockopname/master_barang`.
2. Gunakan pencarian untuk menemukan barang.
3. Tambah atau edit master barang bila diperlukan.
4. Pada edit, admin dapat mengubah kode barang, nama barang, dan dimensi P/L/T.
5. Sistem menghitung ulang dimensi/kubikasi dari `p * l * t`.

