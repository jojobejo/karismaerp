@echo off
setlocal enabledelayedexpansion

:: =============================================================================
:: Script Auto Push GitHub Harian - Karisma ERP
:: Berjalan setiap hari jam 16:00 (4 Sore) melalui Windows Task Scheduler
:: =============================================================================

cd /d "c:\laragon\www\karismaerp"

:: Pastikan direktori log tersedia
if not exist "application\logs" (
    mkdir "application\logs"
)

set LOG_FILE=application\logs\auto_git_push.log
set TIMESTAMP=%date% %time%

:: Cek apakah ada perubahan di working directory (unstaged, staged, atau untracked)
for /f "delims=" %%i in ('git status --porcelain 2^>nul') do (
    set HAS_CHANGES=1
    goto :has_changes_found
)
set HAS_CHANGES=0

:has_changes_found
if "%HAS_CHANGES%"=="0" (
    echo [%TIMESTAMP%] [INFO] Tidak ada perubahan di lokal. Lewati push. >> "%LOG_FILE%"
    exit /b 0
)

:: Ambil nama branch aktif
for /f "tokens=*" %%b in ('git branch --show-current 2^>nul') do (
    set CURRENT_BRANCH=%%b
)

if "%CURRENT_BRANCH%"=="" (
    set CURRENT_BRANCH=development_karismaerp
)

echo [%TIMESTAMP%] [PROCESS] Perubahan terdeteksi pada branch "%CURRENT_BRANCH%". Memulai auto commit dan push... >> "%LOG_FILE%"

:: Stage semua perubahan
git add -A >> "%LOG_FILE%" 2>&1

:: Commit perubahan dengan format tanggal & jam
git commit -m "Auto backup harian local: %TIMESTAMP%" >> "%LOG_FILE%" 2>&1

:: Tarik perubahan remote terlebih dahulu dengan rebase untuk mencegah konflik
git pull --rebase origin "%CURRENT_BRANCH%" >> "%LOG_FILE%" 2>&1

:: Push ke GitHub
git push origin "%CURRENT_BRANCH%" >> "%LOG_FILE%" 2>&1

if %ERRORLEVEL% EQU 0 (
    echo [%TIMESTAMP%] [SUCCESS] Berhasil auto push ke GitHub branch "%CURRENT_BRANCH%". >> "%LOG_FILE%"
) else (
    echo [%TIMESTAMP%] [ERROR] Gagal melakukan push ke GitHub (Kode: %ERRORLEVEL%). Periksa koneksi internet atau hak akses. >> "%LOG_FILE%"
)

exit /b 0
