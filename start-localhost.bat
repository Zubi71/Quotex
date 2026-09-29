@echo off
title OTC Signal Intelligence - Localhost Launcher
color 0b

echo ========================================================
echo        OTC SIGNAL INTELLIGENCE - LOCAL TERMINAL
echo ========================================================
echo.

cd /d "%~dp0"

echo [1/3] Preparing backend configuration...
if not exist "apps\api\.env" (
    copy "apps\api\.env.example" "apps\api\.env" >nul
)
if not exist "apps\api\database\database.sqlite" (
    type nul > "apps\api\database\database.sqlite"
)

echo [2/3] Starting Laravel API Backend on http://localhost:8000 ...
start "OTC API (Port 8000)" cmd /k "cd apps\api && php artisan serve --port=8000"

echo [3/3] Starting Next.js Web Terminal on http://localhost:3000 ...
start "OTC Web Terminal (Port 3000)" cmd /k "cd apps\web && npm run dev"

echo.
echo ========================================================
echo Terminal successfully launched!
echo.
echo Frontend: http://localhost:3000
echo API:      http://localhost:8000
echo.
echo Login with default demo credentials:
echo Email:    trader@otcsignal.local
echo Password: password123
echo ========================================================
echo.
timeout /t 3 >nul
start http://localhost:3000
