# Perbaikan Checker Loading SO & Siklus Status Trip Pengiriman

Tanggal: 06 Oktober 2026  
Modul: Logistik & Sales Order (Checker Loading SO & Delivery Trip)

---

## 1. Latar Belakang Masalah

Pada halaman `checker/so_loading`, ditemukan dua permasalahan terkait alur operasional:
1. **Trip yang sudah difakturkan statusnya tetap `PROSES_FAKTUR`**:
   - Status trip pengiriman tidak pernah diperbarui setelah Admin SC memfakturkan SO dan sistem menerbitkan DO.
   - Pada pencarian trip di `M_Logistik::check_and_auto_create_do`, status `PROSES_FAKTUR` tidak disertakan dalam pencarian trip aktif rute, sehingga DO otomatis terbentuk tanpa relasi `id_trip`.
2. **Tabel "Pilih Rute Loading" hilang saat Checker keluar halaman setelah Start Loading**:
   - Checker memulai loading (`start`), mencentang barang di halaman detail (`checker_loaded = 1`), lalu keluar atau kembali ke halaman utama `checker/so_loading`.
   - Rute tersebut tiba-tiba hilang dari tabel "Pilih Rute Loading", padahal Checker **belum** menekan tombol "Selesai Loading".
   - Penyebabnya adalah filter query `AND (sod.checker_loaded IS NULL OR sod.checker_loaded = 0 OR sod.checker_loaded = 2)` di `C_Checker::so_loading()`. Begitu semua item dicentang bernilai `1`, klausul `EXISTS` langsung bernilai `FALSE`, mengeliminasi rute sebelum proses loading selesai dan sebelum DO dibuat.

---

## 2. Rincian Perbaikan

### A. Database
- Memperluas enum status pada tabel `tb_delivery_trip`:
  ```sql
  ALTER TABLE tb_delivery_trip MODIFY COLUMN status ENUM(
      'DRAFT','VERIFIKASI','SIAP_LOADING','PROSES_LOADING',
      'PROSES_FAKTUR','SIAP_BERANGKAT','MENUNGGU_TAMBAHAN',
      'PROSES_TAMBAHAN','DITUTUP','BERANGKAT','SELESAI'
  ) NOT NULL DEFAULT 'DRAFT';
  ```

### B. Controller & Model
1. **`application/controllers/logistik/C_Checker.php`**:
   - Menghapus syarat `AND (sod.checker_loaded IS NULL OR sod.checker_loaded = 0 OR sod.checker_loaded = 2)` dari query `$routes` pada fungsi `so_loading()`. Rute tetap tampil selama item belum masuk DO, sehingga tombol **`Lanjut Loading`** (biru) atau badge **`Proses Faktur`** (kuning) dapat tampil sesuai kondisi aktivitas loading.
   - Menyertakan status `SIAP_BERANGKAT` pada pemuatan trip aktif di `$trip_rows` serta memanggil fungsi sinkronisasi otomatis `sync_trip_status()`.
   - Memperbolehkan penutupan trip jika trip tersebut kosong (0 SO) sehingga trip yang tidak terpakai dapat ditutup dengan rapi.
2. **`application/models/M_DeliveryTrip.php`**:
   - Menambahkan konstanta `STATUS_READY_TO_GO = 'SIAP_BERANGKAT'`.
   - Menambahkan method `sync_trip_status($id_trip)` untuk mendeteksi apakah suatu trip sudah memiliki DO atau seluruh SO di dalamnya sudah completed/difakturkan, lalu otomatis mengupdate statusnya menjadi `SIAP_BERANGKAT`.
3. **`application/models/M_Logistik.php`**:
   - Memasukkan `'PROSES_FAKTUR'` ke dalam list pencarian trip aktif di `check_and_auto_create_do()`.
   - Mengupdate status trip menjadi `'SIAP_BERANGKAT'` setelah DO berhasil terbentuk.
4. **`application/controllers/sales/C_SalesOrder.php`**:
   - Memanggil `M_DeliveryTrip->sync_trip_status()` setelah faktur berhasil disimpan pada fungsi `simpan_faktur()`.
5. **`application/views/content/logistik/checker/so_loading.php`**:
   - Menambahkan badge hijau bertuliskan **Siap Berangkat** untuk trip dengan status `SIAP_BERANGKAT`.
   - Menampilkan tombol aksi "Buka Tambahan" dan "Tutup Trip" ketika trip sudah siap berangkat atau tidak memiliki beban tonase.
