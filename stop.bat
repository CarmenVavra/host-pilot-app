@echo off
REM ==========================================================
REM  HostPilot - stoppt Backend (Port 8350) und Frontend (Vite)
REM ==========================================================
setlocal
cd /d "%~dp0"

set APP_PORT=8350
set VITE_PORT=5350

echo [HostPilot] Stoppe Backend und Frontend ...

REM --- Fenster aus start.bat schliessen (inkl. Kindprozesse) ---
taskkill /F /T /FI "WINDOWTITLE eq HostPilot-Backend*" >nul 2>&1
taskkill /F /T /FI "WINDOWTITLE eq HostPilot-Frontend*" >nul 2>&1

REM --- Falls noch etwas auf dem Backend-Port lauscht: beenden ---
for /f "tokens=5" %%p in ('netstat -ano ^| findstr /R /C:":%APP_PORT%  *0\.0\.0\.0:0 " /C:":%APP_PORT%  *\[::\]:0 "') do (
    taskkill /F /T /PID %%p >nul 2>&1
)

REM --- Vite-Dev-Server (fester Port, siehe vite.config.js) ---
for /f "tokens=5" %%p in ('netstat -ano ^| findstr /R /C:":%VITE_PORT%  *0\.0\.0\.0:0 " /C:":%VITE_PORT%  *\[::\]:0 "') do (
    taskkill /F /T /PID %%p >nul 2>&1
)
if exist "public\hot" del /q "public\hot"

echo [HostPilot] Gestoppt.
exit /b 0
