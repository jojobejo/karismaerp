# Dokumentasi Auto-Generate Nomor Referensi Jurnal Umum Mengikuti Tanggal Transaksi

Tanggal: 1 September 2026  
Modul: Keuangan / Buku Besar - Jurnal Umum  
Author: Senior Fullstack Developer  

---

## 1. Latar Belakang & Kebutuhan Bisnis
Sebelumnya, pada formulir input Transaksi Jurnal Umum (`buku_besar/jurnal_umum`), nomor referensi (`GJ-dmyXXXXX`) selalu dibuat menggunakan tanggal sistem hari ini (`date('dmy')`).

Ketika pengguna mengubah tanggal transaksi (misalnya mundur ke tanggal 11), nomor referensi yang ter-generate tidak berubah dan tetap menggunakan format tanggal hari ini.

**Kebutuhan yang Diterapkan**:
1. Ketika tanggal transaksi pada form diubah (misalnya memilih tanggal 11), nomor referensi auto-generate langsung otomatis berubah formatnya mengikuti tanggal yang dipilih (misal: `GJ-110926...`).
2. Nomor urut auto-generate mengambil nomor urut transaksi jurnal umum paling akhir pada tanggal tersebut (misalkan pada tanggal 11 nomor referensi terakhir berakhiran `00050`, maka auto-generate berikutnya menghasilkan `00051`, meneruskan urutan transaksi terakhir di tanggal 11).
3. Jika pada tanggal tersebut belum ada transaksi, nomor urut otomatis dimulai dari `00001`.

---

## 2. Perubahan Teknis yang Diterapkan

### A. Controller: `application/controllers/keuangan/C_BukuBesar.php`
1. **Method Private `_generate_next_ref($tanggal = null)`**:
   - Menerima parameter tanggal transaksi (format `Y-m-d`).
   - Mengekstrak string tanggal dengan format `dmy` (contoh: 11 September 2026 menjadi `110926`).
   - Melakukan query ke tabel `tbkeu_jurnal` mencari semua nomor jurnal dengan prefix `GJ-{dmy}`.
   - Menghitung nilai urutan numerik terbesar (`$max_seq`).
   - Menghasilkan nomor referensi baru: `GJ-` + `{dmy}` + `sprintf('%05d', $max_seq + 1)` (contoh: `GJ-11092600051`).
2. **Method `jurnal_umum()`**:
   - Memanggil `$this->_generate_next_ref(date('Y-m-d'))` untuk inisialisasi awal formulir saat halaman pertama kali dibuka.
3. **Method `get_next_ref()` (AJAX)**:
   - Menerima parameter `tanggal` via `GET`/`POST`.
   - Mengembalikan response JSON `{ success: true, next_ref: '...' }` secara dinamis sesuai tanggal yang dikirimkan.

### B. View: `application/views/content/keuangan/jurnal_umum.php`
1. **Fungsi JavaScript `fetchNextRef(customDate)`**:
   - Mengambil nilai tanggal terpilih dari `#form-tanggal` (atau parameter `customDate`).
   - Mengirimkan parameter `{ tanggal: tgl }` ke endpoint `buku_besar/jurnal_umum_next_ref`.
   - Mengisi elemen `#form-ref` dengan nomor referensi baru hasil respon server.
2. **Event Listener `#form-tanggal`**:
   - Menambahkan event listener `change input` pada elemen tanggal `#form-tanggal`:
     ```javascript
     $('#form-tanggal').on('change input', function() {
         let selectedDate = $(this).val();
         if (selectedDate) {
             fetchNextRef(selectedDate);
         }
     });
     ```
3. **Fungsi `clearForm()`**:
   - Reset form memanggil `fetchNextRef('<?= date("Y-m-d") ?>')` untuk mengembalikan nomor referensi ke tanggal hari ini.

---

## 3. Hasil Pengujian & Verifikasi
1. **Sintaks PHP**:
   - `php -l` pada `C_BukuBesar.php` dan `jurnal_umum.php` bebas dari kesalahan sintaks.
2. **Logika Nomor Urut Berdasarkan Tanggal**:
   - Pada tanggal yang sudah memiliki data (contoh: tanggal `26-08-2026` yang memiliki transaksi `GJ-26082600001` dan `GJ-26082600002`), sistem menghasilkan nomor berikutnya `GJ-26082600003`.
   - Pada tanggal baru yang belum memiliki transaksi (contoh: tanggal `11-09-2026`), sistem menghasilkan nomor `GJ-11092600001`.
   - Jika terdapat transaksi berakhiran `00050` pada tanggal terpilih, sistem meneruskan secara urut ke `00051`.
3. **Responsif Frontend**:
   - Setiap kali user memilih atau mengganti tanggal pada kolom input Tanggal Jurnal Umum, input Referensi langsung ter-update secara otomatis tanpa reload halaman.
