# Laporan Scan Seluruh Fungsi KARISMA ERP

Tanggal scan: 2026-07-24  
Workspace: `C:\xampp\htdocs\karismaerp`  
Mode: read-only; tidak ada perubahan kode atau database.

## Ringkasan

Scan mencakup 265 file PHP executable milik aplikasi pada `application/controllers`, `application/models`, `application/libraries`, `application/helpers`, dan `application/modules`. Ditemukan 3.307 named PHP functions/methods dan 1.194 route entries dari dua route registry.

Anonymous closure, fungsi JavaScript inline pada view, vendor/third-party, folder cache/logs, dan framework `system/` tidak dimasukkan karena tidak memiliki identitas fungsi/endpoint yang stabil untuk pemetaan departemen.

## Distribusi fungsi

| Departemen | Jumlah fungsi | Fungsi dengan route |
|---|---:|---:|
| KEUANGAN | 338 | 69 |
| SALES | 239 | 35 |
| LOGISTIK | 1.167 | 299 |
| HRD | 134 | 18 |
| GA | 46 | 2 |
| IT | 263 | 19 |
| PURCHASING | 1.120 | 60 |
| **Total** | **3.307** | **502** |

## Isi workbook

- `Ringkasan`: jumlah file, fungsi, route, dan distribusi departemen.
- `Semua Fungsi`: daftar gabungan seluruh fungsi tanpa pemisahan sheet departemen.
- `Per Departemen`: daftar yang sama diurutkan berdasarkan KEUANGAN, SALES, LOGISTIK, HRD, GA, IT, PURCHASING.
- `KEUANGAN`, `SALES`, `LOGISTIK`, `HRD`, `GA`, `IT`, `PURCHASING`: daftar fungsi per departemen.
- `Routes`: route URL, target controller/function, departemen, dan status mapping.
- `Metodologi`: aturan scope, klasifikasi, deskripsi, dan batasan scan.

## Struktur setiap baris fungsi

Setiap baris berisi nomor, departemen, layer, file, line, class, function, tipe fungsi, deskripsi singkat, jumlah route, route terkait, dan basis klasifikasi.

Deskripsi fungsi dibuat otomatis dari nama fungsi dan komentar terdekat. Deskripsi ini cocok untuk inventaris awal dan pembagian ownership, tetapi fungsi ambigu tetap perlu dikonfirmasi oleh business owner saat penyusunan SOP atau RACI.

## Hasil verifikasi

- Workbook berhasil diekspor ke format `.xlsx`.
- Workbook berhasil diimpor kembali untuk pemeriksaan struktur.
- Formula error scan: 0 error.
- Tidak ada insert, update, delete, migration, atau perubahan konfigurasi yang dijalankan.

