@echo off
REM ==========================================================
REM  HostPilot - erzeugt das Upload-ZIP fuer netcup
REM
REM  Aufruf:   build-package.bat              (mit Tests)
REM            build-package.bat /skip-tests  (ohne Tests)
REM
REM  Ergebnis: dist\hostpilot-upload-JJJJMMTT-HHMM.zip
REM
REM  Das Entwicklungs-Projekt bleibt unveraendert: Gearbeitet wird in
REM  dist\package. Das ZIP enthaelt vendor/ ohne Entwickler-Werkzeuge und
REM  den fertigen Frontend-Build, aber KEINE .env, KEINE Datenbank,
REM  KEINE Tests und KEINE Demo-Daten (Seeder).
REM ==========================================================
setlocal EnableExtensions EnableDelayedExpansion
cd /d "%~dp0"

set "SKIP_TESTS=0"
if /i "%~1"=="/skip-tests" set "SKIP_TESTS=1"

set "SRC=%CD%"
set "DIST=%SRC%\dist"
set "STAGE=%DIST%\package"
set "TAR=%SystemRoot%\System32\tar.exe"

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmm"') do set "STAMP=%%i"
set "ZIP=%DIST%\hostpilot-upload-%STAMP%.zip"

echo.
echo [1/7] Voraussetzungen pruefen ...
where php >nul 2>&1 || (echo        PHP wurde nicht gefunden. & goto :error)
where composer >nul 2>&1 || (echo        Composer wurde nicht gefunden. & goto :error)
where npm >nul 2>&1 || (echo        npm wurde nicht gefunden. & goto :error)
if not exist "%TAR%" (echo        tar.exe wurde nicht gefunden ^(Windows 10/11 erforderlich^). & goto :error)

echo.
if "%SKIP_TESTS%"=="1" (
    echo [2/7] Tests uebersprungen ^(/skip-tests^)
) else (
    echo [2/7] Tests ausfuehren ...
    call php artisan test || (echo        Tests fehlgeschlagen - Paket wird nicht erstellt. & goto :error)
)

echo.
echo [3/7] Frontend bauen ^(Vite^) ...
if not exist "node_modules" (
    call npm ci || goto :error
)
call npm run build || goto :error

echo.
echo [4/7] Dateien zusammenstellen ...
if exist "%STAGE%" rmdir /s /q "%STAGE%"
mkdir "%STAGE%" || goto :error

robocopy "%SRC%" "%STAGE%" /E /NFL /NDL /NJH /NJS /NP ^
    /XD "%DIST%" "%SRC%\vendor" "%SRC%\tests" "%SRC%\docs" "%SRC%\UI_Mockups" "%SRC%\storage" "%SRC%\bootstrap\cache" "%SRC%\public\storage" "%SRC%\database\seeders" "%SRC%\database\factories" ^
        node_modules .git .claude .idea .vscode ^
    /XF .env .env.backup .env.production .gitignore .gitattributes .phpunit.result.cache ^
        *.sqlite *.sqlite-journal *.log *.bat ^
        phpunit.xml phpstan.neon package.json package-lock.json vite.config.js CLAUDE.md hot
if errorlevel 8 (echo        Kopieren fehlgeschlagen. & goto :error)

REM Leere Laufzeit-Ordner, die Laravel beschreiben muss
mkdir "%STAGE%\storage\app\public" "%STAGE%\storage\framework\cache\data" "%STAGE%\storage\framework\sessions" "%STAGE%\storage\framework\views" "%STAGE%\storage\logs" "%STAGE%\bootstrap\cache" >nul 2>&1

echo.
echo [5/7] PHP-Abhaengigkeiten ohne Entwickler-Werkzeuge installieren ...
call composer install --working-dir="%STAGE%" --no-dev --optimize-autoloader --no-interaction --no-progress || goto :error

echo.
echo [6/7] Paket pruefen ...
if exist "%STAGE%\.env" (echo        FEHLER: .env im Paket. & goto :error)
dir /s /b "%STAGE%\*.sqlite" 2>nul | findstr /r "." >nul && (echo        FEHLER: Datenbank-Datei im Paket. & goto :error)
if not exist "%STAGE%\public\build\manifest.json" (echo        FEHLER: Frontend-Build fehlt. & goto :error)
if not exist "%STAGE%\public\.htaccess" (echo        FEHLER: public\.htaccess fehlt. & goto :error)
if not exist "%STAGE%\vendor\autoload.php" (echo        FEHLER: vendor fehlt. & goto :error)
findstr /i /c:"laravel/pail" /c:"nunomaduro/collision" /c:"phpunit" "%STAGE%\bootstrap\cache\packages.php" >nul 2>&1 && (echo        FEHLER: Entwickler-Pakete im Paket. & goto :error)
echo        OK

echo.
echo [7/7] ZIP erstellen ...
"%TAR%" -a -c -f "%ZIP%" -C "%STAGE%" . || goto :error
rmdir /s /q "%STAGE%"

for %%F in ("%ZIP%") do set "BYTES=%%~zF"
set /a MB=BYTES/1048576

echo.
echo ==========================================================
echo  Fertig: %ZIP%
echo  Groesse: ca. %MB% MB
echo.
echo  Naechste Schritte ^(Details: docs\DEPLOYMENT-netcup.md^):
echo   1. ZIP entpacken und Inhalt auf den Webspace hochladen
echo   2. .env.production.example nach .env kopieren und ausfuellen
echo   3. php artisan key:generate --force
echo   4. php artisan migrate --force      ^(NIE db:seed^)
echo   5. php artisan storage:link
echo ==========================================================
exit /b 0

:error
echo.
echo [HostPilot] Paket konnte nicht erstellt werden.
if exist "%STAGE%" rmdir /s /q "%STAGE%" >nul 2>&1
exit /b 1
