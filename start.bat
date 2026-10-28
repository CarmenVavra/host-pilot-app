@echo off
REM ==========================================================
REM  HostPilot - startet Backend (Laravel, Port 8350)
REM  und Frontend (Vite Dev-Server) in eigenen Fenstern.
REM ==========================================================
setlocal
cd /d "%~dp0"

set APP_PORT=8350

REM --- Port pruefen ---
netstat -ano | findstr /R /C:":%APP_PORT%  *0\.0\.0\.0:0 " /C:":%APP_PORT%  *\[::\]:0 " >nul
if %errorlevel%==0 (
    echo [HostPilot] Port %APP_PORT% ist bereits belegt. Laeuft die App schon? Sonst zuerst stop.bat ausfuehren.
    pause
    exit /b 1
)

REM --- Erstinstallation (nur wenn noetig) ---
if not exist "vendor\autoload.php" (
    echo [HostPilot] Installiere PHP-Abhaengigkeiten ...
    call composer install --no-interaction || goto :error
)
if not exist "node_modules" (
    echo [HostPilot] Installiere Node-Abhaengigkeiten ...
    call npm install || goto :error
)
if not exist ".env" (
    copy ".env.example" ".env" >nul
    call php artisan key:generate --ansi || goto :error
)
if not exist "database\database.sqlite" (
    echo [HostPilot] Lege Datenbank mit Demo-Daten an ...
    type nul > "database\database.sqlite"
    call php artisan migrate --seed --force || goto :error
) else (
    call php artisan migrate --force || goto :error
)
if not exist "public\storage" (
    call php artisan storage:link || goto :error
)

REM --- Frontend: Vite Dev-Server (Hot Reload) ---
if exist "public\hot" del /q "public\hot"
start "HostPilot-Frontend" /min cmd /k "npm run dev"

REM --- Backend: Laravel ---
start "HostPilot-Backend" /min cmd /k "php artisan serve --host=127.0.0.1 --port=%APP_PORT%"

REM --- kurz warten, dann Browser oeffnen ---
ping -n 5 127.0.0.1 >nul
start "" "http://localhost:%APP_PORT%"

echo.
echo [HostPilot] Laeuft auf http://localhost:%APP_PORT%
echo [HostPilot] Demo-Login: mira@hostpilot.test / password
echo [HostPilot] Beenden mit stop.bat
exit /b 0

:error
echo [HostPilot] Start fehlgeschlagen.
pause
exit /b 1
