# Panduan Otomatisasi Git Push Harian (Jam 4 Sore / 16:00) — Karisma ERP

## 1. Deskripsi Fitur
Sistem telah dilengkapi dengan mekanisme otomatisasi **Auto Git Push** yang berjalan setiap hari pukul **16:00 WIB (4 Sore)** menggunakan **Windows Task Scheduler**.
Sistem akan:
1. Memeriksa apakah ada file atau kode yang dimodifikasi / ditambahkan di folder lokal (`c:\laragon\www\karismaerp`).
2. **Jika ada perubahan**:
   - Menjalankan `git add -A`.
   - Membuat commit otomatis: `Auto backup harian local: YYYY-MM-DD HH:mm:ss`.
   - Menjalankan `git pull --rebase origin <branch_aktif>` untuk mencegah konflik remote.
   - Menjalankan `git push origin <branch_aktif>`.
   - Mencatat status sukses/gagal ke berkas log.
3. **Jika tidak ada perubahan**:
   - Melewati proses push (*skip*) dan tidak membuat commit kosong.
   - Mencatat log info bahwa tidak ada perubahan.

---

## 2. Berkas & Lokasi
- **Script Launcher**: [auto_git_push.bat](file:///c:/laragon/www/karismaerp/scripts/auto_git_push.bat)
- **Script Engine**: [auto_git_push.ps1](file:///c:/laragon/www/karismaerp/scripts/auto_git_push.ps1)
- **Berkas Log Riwayat**: `application/logs/auto_git_push.log`

---

## 3. Detail Penjadwalan (Windows Task Scheduler)
- **Nama Tugas**: `KarismaERP_AutoGitPush`
- **Jadwal**: Setiap hari (Daily) pukul **16:00:00 WIB**
- **Trigger**: Otomatis oleh sistem operasi Windows

---

## 4. Cara Uji Manual & Pengelolaan Tugas

### Menjalankan Backup Manual Kapan Saja
Jika Anda ingin langsung melakukan backup dan push saat ini juga tanpa menunggu jam 16:00, cukup klik 2x berkas:
```
c:\laragon\www\karismaerp\scripts\auto_git_push.bat
```
Atau jalankan melalui terminal:
```bash
c:\laragon\www\karismaerp\scripts\auto_git_push.bat
```

### Memeriksa Riwayat Log
Buka file `application/logs/auto_git_push.log` untuk melihat riwayat proses backup dan push.

### Memeriksa Status Tugas di Windows
Jalankan di Command Prompt / PowerShell:
```bash
schtasks /query /tn "KarismaERP_AutoGitPush"
```

### Mengubah Jam Jadwal (Opsional)
Jika ingin mengubah jam pelaksanaan (misal menjadi jam 17:00 / 5 sore):
```bash
schtasks /change /tn "KarismaERP_AutoGitPush" /st 17:00
```
