# Dokumentasi Penyesuaian Alur Pembayaran Faktur dengan Metode Lain Saat BG Belum Cair

Tanggal: 1 September 2026  
Modul: Keuangan - Pembayaran Faktur Customer  
Author: Senior Fullstack Developer  

---

## 1. Latar Belakang & Masalah
Sebelumnya, ketika customer memiliki riwayat pembayaran faktur menggunakan Bilyet Giro (BG) yang belum cair (`status_bg = 'pending'`), sistem mengalami kendala:
1. **Form Bayar Terkunci pada Mode Pencairan BG**:
   Halaman form pembayaran (`keuangan/pembayaran/bayar/$id_faktur`) mendeteksi keberadaan record pending BG (`$is_bg_cair_mode = !empty($pending_bg)`). Akibatnya, seluruh form secara otomatis beralih fungsi menjadi form "Konfirmasi BG Sudah Cair" dengan action POST ke `cair/$pending_bg_id`. Form untuk menginput pembayaran baru menjadi hilang sehingga kasir/keuangan tidak dapat memproses pembayaran baru.
2. **Checkbox BG Otomatis Tercentang**:
   Pada form pembayaran, checkbox `check_is_bg` otomatis tercentang jika faktur memiliki `cara_pembayaran === 'bg'`. Hal ini menyebabkan submit pembayaran baru terdeteksi sebagai pembayaran BG lagi (`$is_pending = true`), yang kemudian ditolak oleh backend dengan peringatan bahwa BG sebelumnya belum dicairkan.
3. **Kebutuhan Bisnis**:
   Customer yang memiliki BG belum cair seharusnya **tetap dapat melakukan pembayaran menggunakan metode lain** (seperti Tunai/Kas, Transfer Bank, maupun Potong Saldo Retur) tanpa harus menunggu BG tersebut cair.

---

## 2. Solusi & Perubahan yang Diterapkan

### A. Controller: `application/controllers/keuangan/C_pembayaran.php`
- **Method `bayar($id_faktur)`**:
  - Menjadikan mode pencairan BG eksplisit melalui parameter query `?cair_bg=id_pembayaran`:
    ```php
    $cair_bg_id = $this->input->get('cair_bg');
    $data['is_bg_cair_mode'] = false;
    if ($cair_bg_id) {
        $cair_bg = $this->db->get_where('tbkeu_pembayaran_faktur', [
            'id_pembayaran' => (int)$cair_bg_id,
            'id_faktur'     => (int)$faktur['id_faktur'],
            'status_bg'     => 'pending',
        ])->row_array();
        if ($cair_bg) {
            $data['is_bg_cair_mode'] = true;
            $data['pending_bg']      = $cair_bg;
        }
    }
    ```
  - Secara default saat membuka `bayar/$id_faktur`, halaman bertindak sebagai **Form Input Pembayaran Baru** (`$is_bg_cair_mode = false`).
  - Variabel `$pending_bg` tetap dikirimkan agar tampilan view dapat menyajikan banner notifikasi informatif dan shortcut tindakan pencairan.
- **Method `simpan($id_faktur)`**:
  - Memperjelas pesan notifikasi peringatan jika user mencoba memilih metode BG lagi saat masih ada BG pending, serta menginfokan bahwa metode lain (Kas/Transfer) tetap bisa digunakan.

### B. View: `application/views/content/keuangan/pembayaran_form.php`
1. **Tampilan Form Bersih Tanpa Banner**:
   - Banner alert peringatan BG pending maupun banner mode konfirmasi pencairan BG telah dihilangkan sepenuhnya dari halaman `bayar/{id_faktur}` dan `bayar/{id_faktur}?cair_bg={id_pembayaran}`, sehingga tampilan form pembayaran dan form pencairan BG menjadi bersih, fokus, dan rapi.
2. **Mode Form Sesuai Kebutuhan**:
   - Jika diakses dari tombol **"Bayar"** (`bayar/{id_faktur}`): Form berfungsi sebagai form input pembayaran baru (Cash, Transfer, Retur).
   - Jika diakses dari tombol **"Cairkan BG"** (`bayar/{id_faktur}?cair_bg={id}`): Form berfungsi sebagai form konfirmasi pencairan BG dengan tombol submit "BG Sudah Cair" dan tombol "Batal".
3. **Checkbox BG**:
   - Default centang hanya aktif jika berada pada mode pencairan BG (`$is_bg_checked = $is_bg_cair_mode ? true : false`).
   - Penambahan sinkronisasi JavaScript antara dropdown metode pembayaran dan checkbox `check_is_bg`. Jika user memilih akun kas/bank non-BG, checkbox otomatis tidak dicentang sehingga tidak dianggap sebagai pembayaran BG.
5. **Kalkulasi Nominal Default**:
   - Menghitung sisa tagihan non-BG (`sisa_tagihan - total_bg_pending`) sebagai saran nominal default pembayaran baru.
6. **Aksi Cairkan Langsung di Histori Pembayaran**:
   - Pada tabel riwayat pembayaran sebelah kiri, setiap baris pembayaran BG yang berstatus `Belum Cair` dilengkapi dengan tombol aksi shortcut **"Cairkan"**.

### C. View: `application/views/content/keuangan/pembayaran_faktur_detail.php`
- **Dua Tombol pada Kolom Aksi Faktur Customer**:
  - Pada halaman detail customer (`keuangan/pembayaran/customer/{kd_customer}`):
    - Jika faktur memiliki pembayaran BG yang belum cair (`total_bg_pending > 0`), kolom Aksi secara otomatis menampilkan **2 tombol**:
      1. **Tombol "Cairkan BG"** (kuning/warning): Mengarahkan langsung ke form konfirmasi pencairan BG (`bayar/{id_faktur}?cair_bg={id_pembayaran_bg_pending}`).
      2. **Tombol "Bayar"** (hijau/success): Mengarahkan ke form input pembayaran baru (`bayar/{id_faktur}`) untuk melakukan pembayaran menggunakan metode lain (Cash, Transfer, Retur).
    - Jika faktur tidak memiliki BG pending, kolom Aksi hanya menampilkan 1 tombol "Bayar" seperti biasa.
- Pada tabel riwayat pembayaran (AJAX render) di bagian bawah detail customer faktur, baris BG berstatus `Belum Cair` juga ditambahkan tombol aksi **"Cairkan"** yang langsung membuka URL form pencairan BG faktur terkait.

---

## 3. Hasil Pengujian & Verifikasi
1. **Sintaks PHP**:
   - `php -l` pada seluruh controller, model, dan view terkait menghasilkan status bebas error sintaks.
2. **Pengujian Customer KARI07**:
   - Faktur `LBYINV2808260001` (ID Faktur: 4) milik customer `KARI07` memiliki tagihan Rp 950.000 dengan BG pending Rp 100.000 (ID Pembayaran: 4).
   - Terverifikasi berhasil me-render 2 tombol pada kolom Aksi: **"Cairkan BG"** (mengarah ke `bayar/4?cair_bg=4`) dan **"Bayar"** (mengarah ke `bayar/4`).
3. **Skenario Form Bayar Biasa**:
   - Membuka `keuangan/pembayaran/bayar/$id_faktur` menampilkan form pembayaran baru (bukan form pencairan).
   - Pengguna bebas memilih metode pembayaran Tunai/Kas, Transfer Bank (BCA, Mandiri, dll.), atau Retur.
   - Pembayaran dapat disimpan ke database tanpa terhalang status BG yang belum cair.
4. **Skenario Pencairan BG**:
   - Pengguna dapat mengklik tombol "Cairkan BG" pada tabel daftar faktur, banner, maupun tabel riwayat pembayaran untuk langsung mengonfirmasi pencairan dana BG.
