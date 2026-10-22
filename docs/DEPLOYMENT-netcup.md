# HostPilot online stellen (netcup Webhosting)

Diese Anleitung beschreibt den Weg von deinem Rechner auf einen netcup-Webspace.
Menünamen im Kundenkontrollpanel (CCP) können sich ändern. Wo ich mir bei einer Funktion
deines Tarifs nicht sicher bin, steht **«prüfen»** dabei.

## 0. Voraussetzungen (im CCP prüfen)

- PHP **8.2 oder neuer** für die Domain einstellen.
- Eine **MySQL/MariaDB-Datenbank** anlegen (Name, Benutzer, Passwort notieren).
- **SSL-Zertifikat** (Let's Encrypt) für die Domain aktivieren, die App darf nur über `https://` laufen.
- Ein **E-Mail-Postfach** anlegen (z. B. `noreply@deine-domain.at`) für «Passwort vergessen».
- Das Webverzeichnis der Domain auf den Ordner **`public`** der App stellen (siehe Schritt 3).
- Zugriff: **SSH** (praktisch, nicht in jedem Tarif – prüfen) oder **FTP/SFTP**.

## 1. Lokal vorbereiten

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

- `vendor/` entsteht dabei lokal. Du lädst es mit hoch (oder führst `composer install` auf dem Server aus, falls SSH vorhanden ist).
- `public/build/` (CSS, JS, Schrift) wird **nicht** in Git gespeichert und muss immer lokal gebaut und hochgeladen werden.
- Zum Entwickeln danach wieder `composer install` ausführen, damit die Dev-Werkzeuge zurück sind.

## 2. Dateien hochladen

Lade den gesamten Projektordner hoch, **ausser**:

| Nicht hochladen | Warum |
|---|---|
| `.env` | enthält lokale Einstellungen; auf dem Server entsteht eine eigene |
| `node_modules/` | wird nur zum Bauen gebraucht |
| `database/*.sqlite` | lokale Test-Datenbank mit Demo-Daten |
| `tests/`, `UI_Mockups/`, `.git/`, `*.bat` | nur für die Entwicklung |

Der Ordner `storage/` und `bootstrap/cache/` müssen vom Server **beschreibbar** sein (Rechte 775 bzw. 755/775, je nach Hoster – prüfen).

## 3. Webverzeichnis auf `public/` stellen

Die Domain muss auf `…/dein-projekt/public` zeigen, **nicht** auf den Projektordner.
Sonst wären `.env` und der Quellcode aus dem Internet lesbar. Die Einstellung findest du im CCP bei
der Domain unter «Verzeichnis»/«Webroot» (prüfen).

Prüfe nach dem Hochladen, dass `https://deine-domain.at/.env` einen **Fehler 404/403** liefert.

## 4. `.env` auf dem Server anlegen

1. Kopiere `.env.production.example` auf dem Server nach `.env`.
2. Ersetze alle `<…>`-Werte: Domain, Datenbank, Postfach und die **Impressum-Angaben** (`LEGAL_*`).
3. Erzeuge einen **neuen** App-Schlüssel (mit SSH):

```bash
php artisan key:generate --force
```

Ohne SSH: erzeuge ihn lokal mit `php artisan key:generate --show` und trage den Wert als `APP_KEY=` in die Server-`.env` ein.
Verwende **nie** den Schlüssel deiner lokalen `.env`.

## 5. Datenbank anlegen

Mit SSH im Projektordner:

```bash
php artisan migrate --force
```

> **Wichtig:** Führe auf dem Server **niemals** `php artisan db:seed` aus. Der Seeder legt den Demo-User
> `mira@hostpilot.test` mit dem Passwort `password` an, das wäre ein offenes Einfallstor.

Ohne SSH (prüfen): Du kannst die Migrationen auch von deinem Rechner gegen die Server-Datenbank laufen lassen,
sofern netcup den Zugriff von aussen erlaubt. Sonst frage den Support nach einer SSH-Möglichkeit.

## 6. Bilder-Ordner verknüpfen

Hochgeladene Cover-Bilder liegen in `storage/app/public` und werden über `public/storage` ausgeliefert:

```bash
php artisan storage:link
```

Falls dein Tarif keine symbolischen Links erlaubt (prüfen), melde dich bei mir: Dann stellen wir den Bilder-Speicher
auf einen Ordner direkt unter `public/` um.

## 7. Für den Betrieb optimieren

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Nach jeder Änderung an `.env` oder an Routen vorher `php artisan optimize:clear` ausführen.

## 8. Checkliste vor dem Go-live

- [ ] `https://…` funktioniert, `http://` leitet auf `https://` um
- [ ] `https://…/.env` ist **nicht** erreichbar
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`
- [ ] **Impressum** und **Datenschutz** zeigen deine Angaben, kein gelber Platzhalter-Hinweis mehr
- [ ] Datenschutz inhaltlich gelesen und bei Bedarf rechtlich geprüft (Generator wie eRecht24, WKO oder Jurist)
- [ ] Mit dem **Hoster einen Auftragsverarbeitungsvertrag (AV-Vertrag)** abgeschlossen (im CCP meist online möglich)
- [ ] Registrieren, Anmelden, Abmelden getestet
- [ ] **«Passwort vergessen»** getestet: Die E-Mail kommt an und der Link funktioniert
- [ ] Event mit Cover-Bild angelegt, das Bild wird angezeigt
- [ ] «Konto löschen» mit einem Test-Konto getestet
- [ ] Kein Demo-User vorhanden (`mira@hostpilot.test` darf nicht existieren)
- [ ] App auf dem Handy geöffnet und über «Zum Startbildschirm hinzufügen» installiert

## 9. Betrieb

**Backups:** Sichere regelmässig die Datenbank (netcup-Backup oder Export im CCP) **und** den Ordner
`storage/app/public` (hochgeladene Bilder).

**Updates einspielen:**

1. Lokal ändern, testen (`php artisan test`), `npm run build`.
2. Geänderte Dateien (inkl. `public/build/`) hochladen.
3. Mit SSH: `php artisan migrate --force` und `php artisan optimize:clear`, danach wieder `config:cache`, `route:cache`, `view:cache`.

**Fehler suchen:** `storage/logs/laravel-YYYY-MM-DD.log`. `APP_DEBUG` bleibt auf `false`, damit keine internen Details angezeigt werden.

**Weitere Härtung (optional):** Sicherheits-Header (HSTS, Content-Security-Policy) über die `.htaccess` oder das CCP,
eigene Fehlerseiten für 404/500 und E-Mail-Bestätigung bei der Registrierung, wenn die App öffentlich zugänglich ist.
