# Dokumentasi Teknis Development: Fitur Checker By, Tombol Posting Data & Rumus DPP LPB

**Tanggal Development**: 6 Agustus 2026  
**Framework**: CodeIgniter 3 + PHP 7.4/8.x + MySQL/MariaDB + jQuery & DataTables  
**Pengembang**: Senior Software Engineer / Full-Stack Developer  

---

## 1. Ringkasan Modifikasi Sistem

Pengembangan ini mencakup tiga area utama pada modul Logistics & Purchasing (ICS LPB):
1. **Fitur Input Checker By pada Penerimaan PO (`ics/detail_po`)**:
   - Ditambahkan field input `Checker By` (`#final_checker_by`) sebelum field `Keterangan` khusus untuk pengguna ber-role `admin` dan `admlpb`.
   - Mengirimkan nilai `checker_by` & `checker_name` melalui AJAX request ke endpoint `ics/ajax_finalize_tmp_po_received`.
2. **Fitur Posting Data LPB pada Record LPB (`ics/detail_record_lpb`)**:
   - Ditambahkan tombol **Posting Data** (`#btnPurchasingPostLpb` & `#btnPostLpb`) pada antarmuka Purchasing dan Admin saat status LPB berstatus `UNPOST` (status = 0).
   - Memanggil AJAX endpoint `ics/ajax_post_lpb` untuk memperbarui status LPB menjadi `POST` (status = 1) secara instan tanpa reload halaman.
3. **Analisis Teknis & Perpajakan Kalkulasi DPP & DPP Nilai Lain-Lain**:
   - Evaluasi kesesuaian rumus DPP dan DPP Nilai Lain berdasarkan **UU No. 7 Tahun 2021 (UU HPP)** serta peraturan perpajakan Indonesia (PMK PPN).

---

## 2. Struktur Component & Code Flow

```
[User Browser (Admin/ADMLPB/Purchasing)]
       │
       ├──> (ics/detail_po) Input Header LPB (Nosj, Gudang, Checker By, Keterangan)
       │       │
       │       └──> POST AJAX `ics/ajax_finalize_tmp_po_received`
       │               │
       │               └──> C_Ics::ajax_finalize_tmp_po_received()
       │                       └──> M_Logistik::finalize_tmp_po_received()
       │                               └──> INSERT `tb_lpb` (checker_by, checker_name)
       │
       └──> (ics/detail_record_lpb) Tampilan Record LPB
               │
               ├──> Menampilkan Informasi Header (Checker By + Checker Name)
               │
               └──> Klik Tombol "Posting Data" (Role: Purchasing & Admin)
                       │
                       └──> POST AJAX `ics/ajax_post_lpb`
                               └──> C_Ics::ajax_post_lpb()
                                       └──> M_Logistik::update_lpb_status(id_lpb, 1, user, ket)
```

---

## 3. Detail File Terlibat

1. `application/controllers/logistik/C_Ics.php`:
   - Method `detail_po()`: Menyiapkan penanda role `$data['is_admlpb_user']`, `$data['is_admin_po']`, dan `$data['show_checker_input']`.
   - Method `ajax_finalize_tmp_po_received()`: Menerima payload `checker_by` & `checker_name` dan menyimpannya ke tabel `tb_lpb`.
   - Method `ajax_post_lpb()`: Handler update status LPB dari `UNPOST` (0) menjadi `POST` (1).
2. `application/views/content/logistik/ics/detail_po.php`:
   - Form input `#final_checker_by` diletakkan sebelum `#final_keterangan`.
   - Pengiriman parameter `checker_by` & `checker_name` pada fungsi `ajax_finalize_tmp_po_received`.
3. `application/views/content/logistik/ics/detail_record_lpb.php`:
   - Tombol `#btnPurchasingPostLpb` ditambahkan pada panel aksi purchasing (`#lpbPurchasingVerifyActions`).
   - Penanganan event `click` untuk me-rekam LPB menjadi `POST`.
   - Penyajian informasi `Checker` pada grid header `#lpbDetailHeaderGrid`.

---

## 4. Jalur Pengujian Teknis (Verification Steps)

1. **Pengujian Form Checker By (`ics/detail_po`)**:
   - Buka halaman `ics/detail_po?no_po=...&kd_suplier=...` dengan akun Admin atau ADMLPB.
   - Verifikasi elemen `#final_checker_by` tampil tepat di sebelum `#final_keterangan`.
   - Masukkan nama checker (misal `Budi S (CHK-01)`) dan klik tombol **Simpan**.
   - Cek database `tb_lpb` bahwa kolom `checker_by` dan `checker_name` terisi data tersebut.
2. **Pengujian Tombol Posting Data (`ics/detail_record_lpb`)**:
   - Buka `ics/detail_record_lpb?kd_po=...` dengan akun Purchasing atau Admin.
   - Pilih record LPB yang berstatus `UNPOST`.
   - Verifikasi tombol **Posting Data** berwarna hijau (`#btnPurchasingPostLpb`) muncul di panel aksi.
   - Klik tombol **Posting Data**, perhatikan konfirmasi SweetAlert2 dan ubah status LPB menjadi `POST`.
