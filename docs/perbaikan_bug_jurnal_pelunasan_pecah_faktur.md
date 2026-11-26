# Dokumentasi Perbaikan Bug: Jurnal Pelunasan Pecah Faktur & Skema Tanpa Potongan (0%)

## 1. Ringkasan Permasalahan
Ketika melakukan *Unpost Pembayaran* lalu *Import Pelunasan* ulang untuk Faktur Z yang dipecah dengan skema **Tanpa Potongan (0% / Harga Utuh 100%)** — contohnya `Z26002964` (`H26000648`, `H26000649`, `H26000650`, `H26000651` milik *Sahabat Tani/Regina II / Maristela, CV*) — hasil **Export Pembayaran & Jurnal** masih memunculkan potongan 20% (`110-10 Q Kas` dan `Jurnal (A) Kas Masuk`).

### Akar Penyebab (Root Cause)
1. Faktur `H26000648` s/d `H26000651` dipecah dengan harga barang 100% utuh (`@ Rp 72.000`, total `Rp 86.400.000` sama persis dengan `Z26002964`), namun kolom header `tbso_faktur_z_pecah.skema_potongan` saat pembuatan lama masih menyimpan angka default `20` (dan selector JS `$(this).find('input').prop('disabled', true)` berpotensi ikut memengaruhi input form pada kondisi tertentu).
2. Akibatnya, template Excel pelunasan memiliki nilai `20` pada kolom `skema_potongan`. Ketika file Excel tersebut diimpor kembali, `import_pelunasan_faktur_z_pecah()` membaca angka `20` tersebut secara mentah dan membagi tagihan 100% dengan `0.8` (`24.984.000 / 0.8 = 31.230.000`), sehingga terbentuk potongan 20% fiktif di atas harga 100%.

## 2. Solusi & Perbaikan Kode
1. **Validasi Rasio Otomatis 100% Tanpa Potongan (`C_SalesOrder`, `M_SalesOrder`, `M_Journal`)**:
   - Saat pemecahan faktur (`proses_split_faktur_batch`), pembuatan template pelunasan (`template_import_pelunasan_faktur_z_pecah`), maupun proses import pelunasan (`import_pelunasan_faktur_z_pecah`), sistem kini menghitung rasio total nilai seluruh Faktur Pecahan H terhadap Faktur Z Induk.
   - Apabila total Faktur H sama dengan 100% Faktur Z Induk (`rasio >= 0.98`), sistem otomatis mengunci **`skema_potongan = 0`** (tanpa potongan), bahkan bila kolom `skema_potongan` di file Excel lama masih berisi angka `20`.
2. **Proteksi Input Hidden `pakai_potongan_20` (`faktur_split_batch_form.php`)**:
   - Membatasi penonaktifan input baris kosong saat submit hanya pada `input[name^="splits["]` agar field konfigurasi utama tidak pernah terdampak.
3. **Registrasi Jenis Jurnal `BC` & `BR` (`M_Journal`, `C_SalesOrder`)**:
   - Memastikan prefix jurnal bank `BC` dan `BR` terdaftar di `tbkeu_jenis_jurnal` dan dipetakan sebagai `CR` pada kolom `Ref.` saat export Excel.
