# Development - ICS Detail PO Modal Satuan PO dan Qty Sisa

Tanggal: 2026-07-18

## Scope

Route `ics/detail_po` pada modal `Draft Penerimaan Barang` diperbarui untuk:

- Mengganti label `Qty Kecil Sisa` menjadi `Qty Sisa`.
- Menyesuaikan input `Satuan` berdasarkan satuan dari data PO yang sedang dipilih.

## File yang Berubah

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_po.php`

## Detail Implementasi

View `detail_po.php` tidak lagi membangun pilihan satuan dari seluruh master `tb_satuan`. Kolom `Satuan` pada baris modal menjadi readonly dan nilainya diambil dari `data-satuan` tombol barang PO.

Controller `C_Ics::detail_po()` tidak lagi mengirim `list_satuan` untuk modal ini karena satuan tidak dipilih dari master umum.

Model `M_Logistik::get_po_remaining_qty_by_item()` menambahkan field `satuan` dari `tbpo_detail_po` agar AJAX save dapat memvalidasi satuan terhadap data PO.

Controller `C_Ics::ajax_save_tmp_po_received()` sekarang:

- Membaca satuan PO dari hasil validasi item.
- Menolak draft jika satuan baris berbeda dari satuan PO.
- Menyimpan satuan draft memakai satuan PO yang valid.
- Mengubah pesan validasi user-facing menjadi `Qty Sisa`.

## Tata Cara Penggunaan

1. Buka route `ics/detail_po`.
2. Klik tombol tambah draft penerimaan pada salah satu barang PO.
3. Modal `Draft Penerimaan Barang` menampilkan `Qty Sisa`.
4. Pada baris input draft, kolom `Satuan` otomatis terisi sesuai satuan barang pada PO dan tidak dapat diganti manual.
5. Simpan draft penerimaan seperti biasa.

## Catatan QA

- Pastikan barang dengan satuan PO berbeda menampilkan satuan masing-masing saat modal dibuka.
- Pastikan submit AJAX menolak payload yang satuannya tidak sama dengan `tbpo_detail_po.satuan`.
- Pastikan pesan validasi tidak lagi menampilkan wording `qty kecil sisa` kepada user.
