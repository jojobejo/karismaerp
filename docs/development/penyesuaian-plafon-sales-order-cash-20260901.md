# Dokumentasi Penyesuaian Validasi Plafon untuk Transaksi Cash pada Sales Order (SO)

Tanggal: 1 September 2026  
Modul: Sales Order (SO)  
Author: Senior Fullstack Developer  

---

## 1. Latar Belakang & Masalah
Sebelumnya, sistem Sales Order menerapkan validasi pembatasan total pesanan terhadap plafon aktif customer (`custPlafon`). Jika `grandTotal > custPlafon` (untuk customer dengan plafon selain 1.000), sistem secara otomatis menolak dan memblokir submit SO.

Hal ini menimbulkan kendala operasional ketika customer berbelanja dengan skema **Cash (Tunai)** namun nilai belanjanya melebihi plafon kreditnya. Pembayaran cash dibayar lunas seketika tanpa menimbulkan piutang/kredit, sehingga seharusnya transaksi cash dapat dilakukan oleh seluruh customer tanpa dibatasi kuota plafon.

---

## 2. Aturan Bisnis yang Diperbarui
1. **Transaksi Cash Bebas Plafon**:
   - Jika `cara_pembayaran` yang dipilih adalah **`cash`**, validasi pengecekan batas plafon diabaikan.
   - Semua customer dapat melakukan transaksi tunai dengan nominal berapapun tanpa terhalang jumlah plafon kredit.
2. **Transaksi Non-Cash Tetap Dibatasi Plafon**:
   - Untuk cara pembayaran selain cash (seperti `tempo` atau `bg`), validasi batas plafon tetap aktif dan wajib berada dalam batas `custPlafon`.
3. **Plafon 1.000**:
   - Aturan khusus customer plafon 1.000 tetap berlaku (hanya diperbolehkan memilih metode pembayaran Cash atau Transfer).

---

## 3. Berkas yang Diperbarui

### A. Frontend: `application/views/content/sales/so_form.php`
- Pada listener event `submit` pada formulir `#form-so`:
  - Mengambil nilai `cara_pembayaran` terpilih.
  - Memperbarui kondisi validasi:
    ```javascript
    // Pembayaran Cash dapat dilakukan oleh semua customer berapapun jumlah transaksinya tanpa memperdulikan plafon
    if (cp !== 'cash' && custPlafon !== null && custPlafon !== 1000 && grandTotal > custPlafon) {
        e.preventDefault();
        salesToast('error', 'Grand total SO (Rp ' + Math.round(grandTotal).toLocaleString('id-ID') + ') melebihi plafon customer (Rp ' + Math.round(custPlafon).toLocaleString('id-ID') + ').');
        return;
    }
    ```

### B. Backend: `application/controllers/sales/C_SalesOrder.php`
- **Method `store()`**:
  - Inisialisasi `$cp = strtolower(trim((string)($post['cara_pembayaran'] ?? '')));`.
  - Pengecekan `grand_total > plafon` hanya diberlakukan jika `$cp !== 'cash'`.
- **Method `update()`**:
  - Inisialisasi `$cp = strtolower(trim((string)($post['cara_pembayaran'] ?? '')));`.
  - Pengecekan `grand_total > plafon` saat edit SO hanya diberlakukan jika `$cp !== 'cash'`.

### C. Model: `application/models/M_pembayaran.php`
- **Method `get_unpaid_customer_kd_map()`**:
  - Memperbaiki bug regex PCRE CI3 (`preg_match compilation failed: regular expression is too large at offset 69718`).
  - Sebelumnya, seluruh customer plafon 1.000 (mencapai 6.947 customer saat login user admin) dilempar ke `$this->db->where_in('f.kd_customer', $kdList)` dengan query summary yang berat, menyebabkan string kondisi SQL berukuran > 70KB dan gagal di parse regex internal CodeIgniter 3.
  - Dioptimasi menggunakan query terarah yang hanya mengambil faktur `selesai_do` dengan `sisa_tagihan > 0`, serta pencocokan hash map lookup (`array_flip`) di PHP. Waktu eksekusi turun drastis ke ~2 ms dan bebas dari error regex buffer limit.

---

## 4. Hasil Verifikasi
- Pengujian sintaks PHP (`php -l`) pada controller, model, dan view berhasil (bebas syntax error).
- Form SO sekarang mengizinkan transaksi submit dengan cara pembayaran Cash meskipun nominal belanja melebihi plafon customer.
- Halaman `sales_order/create` dapat dibuka dengan lancar oleh user Admin tanpa error regex CI3.

