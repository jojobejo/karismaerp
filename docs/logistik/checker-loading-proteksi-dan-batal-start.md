# Dokumentasi Teknis: Proteksi Qty Dimuat & Pembatalan Start Loading Rute

## 1. Ringkasan Fitur
Modul: **Logistik / Checker SO Loading**
Halaman Terkait:
- `checker/so_loading/detail/{rute}` (Detail Loading Rute SO)
- `checker/so_loading` (Daftar Loading Rute SO)

Tujuan:
1. Mencegah kesalahan input oleh Checker pada kolom **Qty Dimuat**:
   - Jika barang pesanan adalah **box utuh saja** (tidak ada sisa pcs), kolom input pcs dinonaktifkan (`readonly`).
   - Jika barang pesanan adalah **eceran/pcs saja** (jumlah pesanan < isi per box), kolom input box dinonaktifkan (`readonly`).
2. Menambahkan fitur **Batalkan Start Loading**:
   - Memungkinkan Checker / Koordinator membatalkan proses loading yang terlanjur di-klik "Mulai Loading" karena ketidaksengajaan/salah klik.
   - Mengembalikan status rute ke `SIAP_LOADING`, mereset waktu mulai, dan membersihkan progres sementara yang belum diterbitkan Surat Jalan / DO.

---

## 2. Implementasi Teknis

### A. Proteksi Kolom Qty Dimuat (`so_loading_detail.php`)
- **Logika Penentuan Status Readonly**:
  ```php
  // Jika barang memiliki isi > 1 (ada satuan box & pcs):
  $is_box_disabled = ($isi_item > 1 && $qty_box_max <= 0);
  $is_pcs_disabled = ($isi_item > 1 && $qty_box_max > 0 && $qty_pcs_max <= 0);
  ```
- **Penerapan Atribut**:
  - Kolom yang dinonaktifkan diberi atribut `readonly`, `tabindex="-1"`, serta styling abu-abu (`background-color: #e9ecef; cursor: not-allowed;`).
  - Diberikan handler `onkeydown` untuk mencegah pengetikan pada input readonly.
  - Spinner atas/bawah pada input type number dihilangkan dan listener mouse wheel ditambahkan `blur()` untuk mencegah perubahan angka tidak disengaja.

### B. Fitur Batalkan Start Loading
1. **Model (`application/models/M_Checker.php`)**:
   - Method: `cancel_route_loading($rute)`
   - Proses:
     - Mereset record activity log rute tanggal hari ini dari `PROSES_LOADING` kembali menjadi `SIAP_LOADING`.
     - Mengosongkan `waktu_mulai`, `nik_checker`, `nama_checker`, `status_pause`, `waktu_pause`, `alasan_pause`, dan `progres_loading`.
     - Mengembalikan status trip di `tbmg_trip` jika statusnya `PROSES_LOADING`.
     - Mengembalikan nilai `checker_loaded` ke `0` untuk item detail yang belum terbit Surat Jalan (`no_do IS NULL`).

2. **Controller (`application/controllers/logistik/C_Checker.php`)**:
   - Method: `cancel_so_loading_rute()`
   - Menerima request AJAX POST berisi parameter `rute`.
   - Mengembalikan respons JSON `{ status: 'success'|'error', message: '...' }`.

3. **Routing (`application/config/routes.php`)**:
   - Route: `checker/so_loading/cancel` -> `logistik/C_Checker/cancel_so_loading_rute`.

4. **Views**:
   - `so_loading_detail.php`: Menampilkan tombol **"Batalkan Start Loading"** berwarna kuning (`btn-outline-warning`) di samping badge status saat rute sedang `PROSES_LOADING`.
   - `so_loading.php`: Menampilkan tombol batalkan start loading (`.btn-cancel-start-route`) pada baris tabel rute yang berstatus `PROSES_LOADING`.

---

## 3. Cara Penggunaan oleh Pengguna
1. **Input Qty Dimuat**:
   - Buka halaman detail loading rute.
   - Checker hanya dapat mengisi kolom yang relevan (jika pesanan murni box, kolom pcs terkunci; jika pesanan murni eceran, kolom box terkunci).
2. **Membatalkan Start Loading**:
   - Jika tombol "Mulai Loading" tertekan secara tidak sengaja, klik tombol **"Batalkan Start Loading"**.
   - Muncul konfirmasi SweetAlert: "Apakah Anda yakin ingin membatalkan status mulai loading untuk rute ini?".
   - Konfirmasi akan mengembalikan rute ke status **Siap Loading** sehingga Checker dapat memulai kembali di saat yang tepat.

