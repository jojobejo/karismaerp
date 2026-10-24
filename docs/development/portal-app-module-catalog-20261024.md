# Katalog Setting Module per Aplikasi

## Tujuan dan cakupan

Halaman Akses Aplikasi menampilkan tombol **Detail Module** pada setiap kartu aplikasi. Tombol membuka halaman **Setting Module** untuk aplikasi tersebut. Admin dengan izin `master/user-management` dapat menambah, mengubah, mengaktifkan/nonaktifkan, mengurutkan, dan menghapus daftar module.

Klien juga dapat mengelola katalog milik aplikasinya melalui REST API. Autentikasi memakai HTTP Basic dengan `app_code` sebagai username dan client secret sebagai password. Server memeriksa hash secret terhadap klien aktif yang terdaftar. Kode aplikasi untuk operasi tulis selalu diambil dari klien terautentikasi sehingga klien tidak dapat menulis ke katalog aplikasi lain.

## Data module

- `module_code`: identitas stabil, unik dalam satu aplikasi.
- `module_name`: nama yang dibaca pengguna.
- `description`: ringkasan fungsi module.
- `module_url`: path aplikasi atau URL HTTPS tujuan yang disediakan klien; Portal menyimpan nilai ini dan tidak mengunjungi URL tersebut.
- `sort_order` dan `is_active`: kendali tampilan katalog.

## Cara penggunaan admin

1. Buka User Management, klik gear di kolom **Akses** untuk user yang dikelola.
2. Pada kartu aplikasi yang ingin diatur, klik **Detail Module**.
3. Tambahkan module dengan kode, nama, deskripsi, route/URL opsional, urutan, dan status aktif.
4. Gunakan tombol ubah untuk memperbarui data atau hapus untuk menghapus baris secara permanen.

## API klien

Gunakan HTTPS dan header `Authorization: Basic base64(app_code:client_secret)`. Jangan menaruh secret di URL atau log. Semua operasi hanya mencakup `app_code` yang terikat pada kredensial itu.

| Method | Route | Fungsi |
|---|---|---|
| GET | `/api/client/modules` | Daftar seluruh module klien, termasuk yang nonaktif |
| GET | `/api/client/modules/{module_code}` | Detail satu module |
| POST | `/api/client/modules` | Membuat module |
| PUT/PATCH | `/api/client/modules/{module_code}` | Memperbarui module |
| DELETE | `/api/client/modules/{module_code}` | Menghapus module secara permanen |

Contoh pembuatan JSON:

```json
{
  "module_code": "purchase-order",
  "module_name": "Purchase Order",
  "description": "Pengajuan dan pemantauan purchase order.",
  "module_url": "/purchasing/po",
  "sort_order": 10,
  "is_active": 1
}
```

API mengembalikan JSON dengan status HTTP standar: `201` untuk pembuatan, `200` untuk berhasil, `400` untuk JSON/metode input yang salah, `401` untuk kredensial salah, `404` jika module tidak ditemukan, `409` untuk kode duplikat, `422` untuk validasi, dan `503` jika migration belum diterapkan.

## Rekomendasi pengembangan lanjutan

Katalog ini adalah metadata dan belum otomatis membuat menu, route, atau hak akses di aplikasi klien. Masing-masing aplikasi tetap harus membaca daftar module dan menerapkan otorisasi internalnya sendiri. Untuk pemakaian produksi lintas banyak klien, tambahkan audit log perubahan, soft delete/arsip, dan pembatasan frekuensi API agar perubahan dapat ditelusuri serta kesalahan hapus dapat dipulihkan.

## Database dan validasi

Skema baru berada di `docs/database/portal_app_module_20261024.sql`; rollback menghapus tabel dan seluruh data katalog. Migration belum dijalankan. Lint PHP dan UAT API/browser perlu dilakukan setelah penerapan pada database pengembangan.
