# Dokumentasi Teknis: Skema Potongan Penjurnalan Pembayaran Faktur Z

## 1. Deskripsi Fitur
Fitur ini menyediakan opsi pemilihan skema potongan pelunasan pada halaman form pembayaran Faktur Z (`/keuangan/pembayaran/bayar/{id_faktur}`):
1. **Tanpa Potongan (0%)**
2. **Potongan 20% (Tax)**
3. **Potongan 40% (20% Tax + 20% Fee Petugas)**

Mekanisme ini berlaku untuk pelunasan pembayaran menggunakan Bank (seperti Q Mandiri dsb) maupun Q Kas Ayat Silang.

---

## 2. Ketentuan Penjurnalan Akuntansi

### A. Faktur Induk (Z) — Jurnal Asli
Faktur Induk Z selalu dicatat lunas secara penuh tanpa potongan:
- **Debit**: Akun Kas / Bank yang dipilih (misal `120-14` Q Mandiri / Q Kas Ayat Silang) sebesar total pembayaran.
- **Kredit**: `130-99` (Piutang Usaha) sebesar total tagihan/pembayaran.
- **Keterangan**: `Penerimaan dari [Nama Customer] (Z)`

---

### B. Faktur Pecahan (H) — Jurnal Acak (Q)
Diterbitkan untuk setiap faktur pecahan H yang terkait dengan Faktur Z:
- **Debit**: Akun Kas / Bank yang dipilih sebesar proporsi pembayaran bruto pecahan.
- **Kredit**: `130-99` (Piutang Usaha) sebesar nilai tagihan netto pecahan:
  - Skema 0%: 100% (penuh)
  - Skema 20%: 80% dari bruto
  - Skema 40%: 60% dari bruto
- **Kredit**: `110-10` (Q Kas) sebagai penyeimbang selisih potongan:
  - Skema 0%: Tidak ada baris Q Kas
  - Skema 20%: 20% dari bruto
  - Skema 40%: 40% dari bruto
- **Keterangan**: `Penerimaan dari [Nama Customer / Kios Pecahan]`

---

### C. Jurnal Kas Masuk (A) — Kode KM
Diterbitkan otomatis apabila dipilih skema potongan 20% atau 40%:

#### 1. Jika Skema Potongan 20% (Tax):
- **Debit**: `110-30` (A Kas) sebesar Total Potongan 20%
- **Kredit**: `130-36` (A PND atas Pendapatan Pot Penj KIU (Tax)) sebesar Total Potongan 20%
- **Keterangan**: `PND u/Pot [Nama Customer Induk Z] F.[No Faktur Z] (Tax)`

#### 2. Jika Skema Potongan 40% (20% Tax + 20% Fee Petugas):
- **Debit**: `110-30` (A Kas) sebesar Total Potongan 40%
- **Kredit**: `130-36` (A PND atas Pendapatan Pot Penj KIU (Tax)) sebesar 20% pertama (Tax)
- **Keterangan**: `PND u/Pot [Nama Customer Induk Z] F.[No Faktur Z] (Tax)`
- **Kredit**: `130-41` (A PND atas Pendapatan Pot Penj KIU (Fee Petugas)) sebesar 20% kedua (Fee Petugas)
- **Keterangan**: `PND u/Pot [Nama Customer Induk Z] F.[No Faktur Z] (Fee Petugas)`

---

## 3. File yang Dimodifikasi
1. `application/views/content/keuangan/pembayaran_form.php`:
   - Penambahan radio selection 3 pilihan potongan (0%, 20%, 40%) khusus Faktur Z.
   - Ringkasan daftar faktur pecahan terkait.
2. `application/controllers/keuangan/C_pembayaran.php`:
   - Pengambilan data pecahan dan pengiriman flag `is_faktur_z` ke view form pembayaran.
   - Penangkapan parameter `skema_potongan_z` dan penyimpanannya ke tabel `tbkeu_pembayaran_faktur`.
3. `application/models/M_Journal.php`:
   - Implementasi pembuatan Jurnal Asli Z, Jurnal Acak Q pecahan, dan Jurnal A.
4. `application/models/M_pembayaran.php`:
   - Penambahan sinkronisasi status seluruh rangkaian jurnal saat proses unpost & post ulang.
5. `application/controllers/keuangan/C_BukuBesar.php` & `application/views/content/keuangan/pembayaran_faktur_detail.php`:
   - Tampilan modal detail multi-jurnal terintegrasi (Jurnal Asli, Jurnal Q, dan Jurnal A).

