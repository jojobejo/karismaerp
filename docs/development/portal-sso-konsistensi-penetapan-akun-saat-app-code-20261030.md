# Konsistensi SSO Saat `app_code` Diubah

## Temuan pemindaian

Portal dan KIU PO sudah memiliki alur login SSO Authorization Code + PKCE. Portal mengarahkan user ke aplikasi melalui URL peluncuran; KIU PO memulai kembali pemeriksaan Portal, memvalidasi callback, menukar code dari backend, memetakan akun kerja yang ditetapkan, lalu membuat session lokal. Login ke Portal tanpa request SSO melanjutkan ke halaman Portal.

Sebelum perbaikan, operasi ubah `app_code` memindahkan registry, izin, code sementara, dan katalog akun, tetapi tidak memindahkan tabel penetapan akun. Setelah perubahan kode aplikasi, penetapan user tertinggal dengan kode lama sehingga SSO dapat menolak user yang sebelumnya sudah disiapkan.

## Perubahan

- Operasi rename kini memperbarui `app_code` pada tabel penetapan akun di dalam transaksi rename yang sama.
- Histori login ikut mengikuti `app_code` baru supaya catatan tetap terhubung ke aplikasi yang sama dan tetap dapat difilter admin.
- Penghapusan aplikasi membersihkan penetapan akun sebelum katalog akun, agar tidak menyisakan penetapan tanpa akun.
- Histori login tetap disimpan ketika aplikasi dihapus sebagai catatan audit.
- Cookie session Portal dan KIU PO dibatasi ke HTTPS (`Secure`) dan tidak dapat dibaca JavaScript (`HttpOnly`).

## Dampak database

Tidak ada perubahan skema. Perubahan hanya menjaga konsistensi data pada tabel yang sudah tersedia. Migration atau rollback tambahan tidak diperlukan.

## Pemeriksaan

- Pemeriksaan parser PHP dilakukan untuk model yang berubah.
- Pemeriksaan parser PHP dilakukan untuk konfigurasi session Portal dan KIU PO.
- Belum ada UAT browser atau pengujian terhadap database target. Sebelum rilis, uji rename aplikasi yang memiliki akun dan penetapan aktif, login SSO user terkait, filter histori dengan kode baru, serta penghapusan aplikasi dan retensi histori.

## Batas aktivasi

Keberadaan kode tidak membuktikan migration sudah diterapkan pada server. Pastikan tabel penetapan dan histori sudah tersedia sebelum memakai fungsi terkait. Portal serta KIU PO harus memiliki HTTPS dan konfigurasi client ID, callback, secret, dan URL peluncuran yang cocok.
