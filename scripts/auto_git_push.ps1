# =============================================================================
# Script Auto Git Push Harian - Karisma ERP
# Menjalankan auto-commit & push jika ada perubahan di lokal setiap jam 16:00
# =============================================================================

$projectDir = "c:\laragon\www\karismaerp"
Set-Location $projectDir

$logDir = "$projectDir\application\logs"
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}

$logFile = "$logDir\auto_git_push.log"
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

# Cek apakah ada perubahan di working directory (modified, added, deleted, untracked)
$status = git status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Add-Content -Path $logFile -Value "[$timestamp] [INFO] Tidak ada perubahan di lokal. Lewati push."
    exit 0
}

# Dapatkan nama branch aktif saat ini
$branch = (git branch --show-current).Trim()
if ([string]::IsNullOrWhiteSpace($branch)) {
    $branch = "development_karismaerp"
}

Add-Content -Path $logFile -Value "[$timestamp] [PROCESS] Perubahan terdeteksi pada branch '$branch'. Menjalankan commit dan push..."

try {
    # 1. Stage semua file perubahan
    git add -A 2>&1 | Out-File -FilePath $logFile -Append

    # 2. Commit dengan pesan berbahasa Indonesia
    $commitMsg = "Auto backup harian local: $timestamp"
    git commit -m $commitMsg 2>&1 | Out-File -FilePath $logFile -Append

    # 3. Pull rebase dari remote origin terlebih dahulu untuk mencegah konflik
    git pull --rebase origin $branch 2>&1 | Out-File -FilePath $logFile -Append

    # 4. Push ke GitHub
    git push origin $branch 2>&1 | Out-File -FilePath $logFile -Append

    if ($LASTEXITCODE -eq 0) {
        Add-Content -Path $logFile -Value "[$timestamp] [SUCCESS] Berhasil auto push ke GitHub branch '$branch'."
    } else {
        Add-Content -Path $logFile -Value "[$timestamp] [ERROR] Gagal melakukan git push (Exit code: $LASTEXITCODE). Silakan periksa koneksi atau kredensial GitHub."
    }
} catch {
    Add-Content -Path $logFile -Value "[$timestamp] [EXCEPTION] Terjadi kesalahan: $_"
}
