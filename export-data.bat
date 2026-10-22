@echo off
REM ==========================================================
REM  HostPilot - exportiert die lokalen Events in eine Datei,
REM  die du auf den Server uebertragen kannst.
REM
REM  Aufruf:   export-data.bat
REM  Ergebnis: storage\app\exports\hostpilot-export-DATUM.json
REM
REM  Enthalten: Events mit Ablauf, Rezepten, Einkauf, Themen und
REM  Bildern. NICHT enthalten: Passwoerter und Konto-Daten.
REM  Import auf dem Server: siehe docs\DEPLOYMENT-netcup.md, Abschnitt 10.
REM ==========================================================
setlocal
cd /d "%~dp0"

set "EMAIL=carmen.vavra@chello.at"
set /p "EMAIL=E-Mail deines LOKALEN Kontos [%EMAIL%]: "

echo.
call php artisan hostpilot:export --user=%EMAIL%
if errorlevel 1 (
    echo.
    echo [HostPilot] Export fehlgeschlagen.
    pause
    exit /b 1
)

echo.
echo Naechste Schritte:
echo   1. Die Datei aus storage\app\exports auf den Server hochladen (NICHT in den Ordner public!)
echo   2. Auf dem Server importieren, siehe docs\DEPLOYMENT-netcup.md, Abschnitt 10
echo.
if exist "storage\app\exports" start "" "storage\app\exports"
pause
exit /b 0
