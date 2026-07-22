@echo off
title Apotek Almaira - Full Stack Launcher
cd /d "%~dp0"

echo ===================================================
echo   APOTEK ALMAIRA - FULL STACK AUTO LAUNCHER
echo   PT Nur Madani Farma
echo ===================================================
echo.

echo [1/6] Migrasi database...
php artisan migrate --force
if errorlevel 1 goto :error

echo [2/6] Storage link...
if not exist "public\storage" php artisan storage:link

echo [3/6] Clear cache...
php artisan optimize:clear

echo [4/6] Build assets (production fallback)...
if not exist "public\build\manifest.json" (
    call npm run build
)

echo [5/6] Menjalankan Backend + Frontend...
start "Apotek Almaira - Laravel" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"
start "Apotek Almaira - Vite" cmd /k "npm run dev"

echo [6/6] Membuka browser...
timeout /t 4 >nul
start http://127.0.0.1:8000
start http://127.0.0.1:8000/catalog
start http://127.0.0.1:8000/mitra/daftar

echo.
echo ===================================================
echo   SISTEM BERJALAN
echo   Staff Login : http://127.0.0.1:8000/login
echo   E-Catalog   : http://127.0.0.1:8000/catalog
echo   Mitra Daftar: http://127.0.0.1:8000/mitra/daftar
echo   Mitra Login : http://127.0.0.1:8000/mitra/login
echo.
echo   Akun demo: taufiq / Almaira@2026
echo ===================================================
echo Jangan tutup jendela Laravel dan Vite.
pause
goto :eof

:error
echo GAGAL memulai. Periksa PHP, database, dan .env
pause
exit /b 1
