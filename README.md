# Profi-Tipp für Laravel-Entwickler: "Laravel Boost MCP"

Um Claude Code noch mächtiger im Umgang mit Ihrer Datenbank zu machen, empfiehlt sich die Installation des Open-Source-Pakets `laravel/boost` via Composer im Projekt:

```bash
composer require laravel/boost --dev
```

## Dieses Paket registriert sich als sogenannter Model Context Protocol (MCP) Server. Dadurch kann Claude Code direkt mit den Internals Ihrer Laravel-App interagieren. Claude kann dann

- Live Ihre echte lokale Datenbank-Struktur abfragen (ohne die Dateien lesen zu müssen).
- Artisan Tinker-Befehle im Hintergrund ausführen, um Daten direkt zu prüfen.

## Server start and stop script

- Mach im Projektordner eine start.bat, um den Server zu starten
- Mach im Projektordner eine stop.bat, um den Server zu soppen

---

# HostPilot – Event-Planung (Web- & Handy-App)

Laravel 12 + Blade, umgesetzt nach `UI_Mockups/design_handoff_hostpilot` (Stil «Modernist»).
Eine responsive App: unter 960 px Breite das Handy-Layout (Segmented Control, FAB, Wisch-Gesten),
darüber die Web-App mit Sidebar, Tabs und Seitenpanels. Über das Web-Manifest lässt sie sich auf dem Handy
als App installieren («Zum Startbildschirm hinzufügen»).

## Starten / Stoppen

- `start.bat` – installiert bei Bedarf Abhängigkeiten, legt DB + Demo-Daten an, startet Backend (http://localhost:8350) und Vite (Port 5350)
- `stop.bat` – beendet beide

Demo-Login: `mira@hostpilot.test` / `password`

## Funktionen

- Registrierung, Login, Passwort vergessen/zurücksetzen (Mail-Treiber `log` → `storage/logs/laravel.log`), Passwort ändern im Profil
- Events (anstehend/vergangen), Cover-Foto-Upload, Gästezahl
- Ablaufplan: Timeline, Verantwortliche, Wisch/Long-Press → Ändern/Löschen
- Rezepte nach Gang, Zutaten mit den Originalmengen des Rezepts (keine automatische Umrechnung)
- Einkaufsliste: «Aus Rezepten generieren» (summiert gleiche Zutaten, behält manuelle Einträge und Haken), Live-Abhaken
- Themen: beliebig viele Gesprächsthemen pro Event mit Überschrift und Notizen (anlegen, bearbeiten, löschen)

## Online stellen

- Anleitung für netcup: [docs/DEPLOYMENT-netcup.md](docs/DEPLOYMENT-netcup.md), Vorlage für die Server-`.env`: `.env.production.example`
- Impressum und Datenschutzerklärung (`/impressum`, `/datenschutz`) lesen ihre Angaben aus den `LEGAL_*`-Werten der `.env`
- `build-package.bat` erzeugt das fertige Upload-ZIP (`dist/hostpilot-upload-*.zip`)
- `server-setup.sh` richtet die App auf dem Server gefragt ein (`.env`, Schlüssel, Datenbank, Bilder-Verknüpfung): `bash server-setup.sh`
- Auf dem Server **nie** `db:seed` ausführen (Demo-User mit bekanntem Passwort)

## Entwicklung

```bash
php artisan test
./vendor/bin/phpstan analyse --memory-limit=1G
./vendor/bin/pint
npm run build
php artisan migrate:fresh --seed
```
