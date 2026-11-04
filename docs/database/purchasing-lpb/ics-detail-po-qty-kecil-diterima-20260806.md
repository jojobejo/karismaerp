# Documentation - ICS Detail PO Kolom Qty Kecil Diterima & Bugfix Permission Logs

Tanggal: 2026-08-06

## Scope Fitur & Bug Fix
1. Penambahan kolom **Qty Kecil** setelah kolom **Qty Diterima** pada route `ics/detail_po` yang dihitung secara otomatis berdasarkan konversi dimensi barang.
2. Menyajikan **Total Qty Draft** (Qty Besar) dan **Total Qty Kecil** pada ringkasan draft penerimaan (*summary stat cards*).
3. **Pembersihan bug alert `file_put_contents permission denied`** pada proses simpan LPB final (Solusi A).

## Solusi A yang Diimplementasikan
- Menghapus blok penulisan log debug `file_put_contents(APPPATH . 'logs/ajax_finalize_debug.txt', ...)` pada method `ajax_finalize_tmp_po_received()` di `C_Ics.php`.
- Hal ini menghilangkan pemicu *PHP Warning: Permission denied* saat server mencoba menulis file log pada direktori yang tidak memiliki izin tulis.
- Respon server kini mengembalikan data JSON murni (`application/json`) tanpa tercampur teks warning HTML, sehingga AJAX di frontend berhasil memproses respon dan menampilkan dialog sukses/error secara normal.

## File yang Diubah
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_po.php`
