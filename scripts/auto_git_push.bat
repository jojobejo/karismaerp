@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "c:\laragon\www\karismaerp\scripts\auto_git_push.ps1"
exit /b %ERRORLEVEL%
