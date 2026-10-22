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

## 1. Upload-Paket erstellen

Auf deinem Rechner im Projektordner:

```bash
build-package.bat
```

Das Skript (Doppelklick genügt) macht alles Nötige und verändert dein Entwicklungs-Projekt nicht:

1. führt die Tests aus und bricht bei einem Fehler ab (mit `build-package.bat /skip-tests` überspringbar),
2. baut das Frontend (`npm run build`),
3. kopiert die Dateien in einen Arbeitsordner und lässt alles Überflüssige weg,
4. installiert die PHP-Pakete **ohne** Entwickler-Werkzeuge (`composer install --no-dev`),
5. prüft das Paket (keine `.env`, keine Datenbank, Frontend-Build und `vendor` vorhanden),
6. erstellt `dist\hostpilot-upload-JJJJMMTT-HHMM.zip` (ca. 8 MB).

**Was im ZIP nicht enthalten ist:** `.env`, `database/*.sqlite`, `node_modules/`, `tests/`, `docs/`, `UI_Mockups/`,
die Demo-Daten (`database/seeders`) und alle `.bat`-Dateien. Ein versehentliches `php artisan db:seed` auf dem Server scheitert
deshalb mit einer Fehlermeldung, statt den Demo-User mit dem Passwort `password` anzulegen.

## 2. ZIP hochladen

1. Entpacke das ZIP lokal oder lade es hoch und entpacke es auf dem Server.
2. Der **Inhalt** (nicht der Ordner `dist`) kommt in das Projektverzeichnis auf dem Webspace, z. B. `…/hostpilot/`.
3. Prüfe, dass `storage/` und `bootstrap/cache/` vom Server **beschreibbar** sind (Rechte 775 bzw. 755/775, je nach Hoster, prüfen).

**Updates:** Erzeuge ein neues Paket und überschreibe die Dateien auf dem Server. Deine Server-`.env` und der Ordner
`storage/app/public` (hochgeladene Bilder) bleiben dabei bestehen, weil sie nicht im ZIP sind.

## 3. Webverzeichnis auf `public/` stellen

> **Wichtig – Ordnerstruktur:** Nur der Ordner `public/` ist aus dem Internet erreichbar. Alles andere (`.env`, `app/`,
> `vendor/`, `storage/` …) liegt **eine Ebene darüber** im Projektordner und darf **nicht** in `public/` liegen.
> Die `.env` gehört neben `artisan` und `composer.json`, **niemals** in den Ordner `public/`.
>
> **Hinweis nginx vor Apache:** Antwortet dein Webhosting mit `server: nginx` (prüfbar mit `curl -I https://deine-domain.at`),
> steht häufig ein nginx vor einem Apache (so bei Plesk; im Fehlerprotokoll steht dann «Apache-Fehler»). nginx liefert
> **statische Dateien direkt aus**, ohne Apache und die `public/.htaccess` zu fragen. Versteckte Dateien (`.env`, `.git` …)
> werden deshalb ausgeliefert, sobald sie im öffentlichen Ordner liegen. Die `.htaccess` schützt davor nicht.

Die Domain muss auf `…/dein-projekt/public` zeigen, **nicht** auf den Projektordner.
Sonst wären `.env` und der Quellcode aus dem Internet lesbar. Die Einstellung findest du im CCP bei
der Domain unter «Verzeichnis»/«Webroot» (prüfen).

Prüfe nach dem Hochladen, dass `https://deine-domain.at/.env` **nicht** mit `200` antwortet (404, 403 oder 500 sind in Ordnung,
`200` bedeutet: Die Datei liegt im öffentlichen Ordner und ist für alle abrufbar).

**Falls die `.env` doch einmal öffentlich war:** Sofort verschieben und danach alle darin stehenden Geheimnisse erneuern
(Datenbank-Passwort, Postfach-Passwort, neuer `APP_KEY` mit `php artisan key:generate --force`).

## 4. Einrichten mit dem Skript (empfohlen, per SSH)

Das Skript `server-setup.sh` erledigt die Schritte 4 bis 7 gefragt und fehlerarm:

```bash
bash server-setup.sh
```

Es fragt nach Adresse, Datenbank, Postfach und Impressum-Angaben (Passwörter werden nicht angezeigt) und
schreibt daraus eine **gültige `.env`** mit Rechten `600`, ohne Platzhalter und mit korrekt behandelten Sonderzeichen.
Danach erzeugt es einen neuen App-Schlüssel, legt die Datenbank-Tabellen an, verknüpft den Bilder-Ordner, schaltet die
den Bilder-Ordner (relativ) und prüft am Ende die Seite (`/up`, `/login`, `/impressum`, `/.env`). Zusätzlich:

- löscht es auf Nachfrage eine versehentlich in `public/` liegende `.env`,
- sichert eine vorhandene `.env` als `.env.alt-DATUM` (später löschen, sie enthält Geheimnisse),
- zeigt bei Problemen die letzte Fehlerzeile aus dem Log.

Das Skript ist im Upload-ZIP enthalten. Vorher bereithalten: Datenbank-Name, -Benutzer und -Passwort sowie die
SMTP-Daten deines Postfachs aus dem Kundenkontrollpanel.

Die folgenden Abschnitte 4a bis 7 beschreiben dasselbe von Hand. Du brauchst sie nur, wenn du das Skript nicht nutzen kannst.

## 4a. `.env` von Hand anlegen

1. Kopiere `.env.production.example` auf dem Server nach `.env`.
2. Ersetze alle `<…>`-Werte: Domain, Datenbank, Postfach und die **Impressum-Angaben** (`LEGAL_*`). Die spitzen Klammern müssen
   dabei **mit** verschwinden, sonst bricht `artisan` mit «Invalid URI: Host is malformed» ab.
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
ln -s ../storage/app/public public/storage
```

Es muss ein **relativer** Link sein: Beim netcup-Webhosting sieht die Kommandozeile andere (gekürzte) Pfade als der Webserver.
Ein absoluter Link (`php artisan storage:link`) würde für den Webserver ins Leere zeigen und Bilder nicht ausliefern.
(`php artisan storage:link --relative` geht auf dem Server nicht, es braucht ein Paket, das im Produktiv-Paket fehlt.)

Falls dein Tarif keine symbolischen Links erlaubt (prüfen), melde dich bei mir: Dann stellen wir den Bilder-Speicher
auf einen Ordner direkt unter `public/` um.

## 7. Keine Zwischenspeicher für Konfiguration, Routen und Ansichten

> **Führe bei netcup bewusst KEIN `php artisan config:cache`, `route:cache` oder `view:cache` aus.**

Diese Befehle speichern absolute Dateipfade fest ab. Die SSH-Kommandozeile sieht beim netcup-Webhosting aber **andere
(gekürzte) Pfade** als der Webserver, z. B. `/deine-domain/projekt/storage` statt
`/var/www/vhosts/…/deine-domain/projekt/storage`. Der Webserver darf nur seine eigenen Pfade lesen (`open_basedir`) und
antwortet dann bei **jeder** Seite mit einer **leeren 500-Seite**, ohne Eintrag im Log.

Ohne diese Caches läuft HostPilot für den erwarteten Gebrauch schnell genug. Falls du schon einmal Caches erzeugt hast:

```bash
php artisan optimize:clear
```

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
- [ ] Kein Demo-User vorhanden (`mira@hostpilot.test` darf nicht existieren; das Paket enthält keinen Seeder)
- [ ] App auf dem Handy geöffnet und über «Zum Startbildschirm hinzufügen» installiert

## 9. Betrieb

**Backups:** Sichere regelmässig die Datenbank (netcup-Backup oder Export im CCP) **und** den Ordner
`storage/app/public` (hochgeladene Bilder).

**Updates einspielen:**

1. Lokal ändern und testen, dann `build-package.bat` ausführen.
2. Das neue Paket hochladen und die Dateien überschreiben.
3. Mit SSH: `php artisan migrate --force` und `php artisan optimize:clear` (keine Caches wieder einschalten, siehe Abschnitt 7).

**Fehler suchen:**
1. `storage/logs/laravel-YYYY-MM-DD.log`. `APP_DEBUG` bleibt auf `false`, damit keine internen Details angezeigt werden.
2. Bei einer **leeren 500-Seite ohne Log-Eintrag** bricht PHP vor Laravel ab. Dann hilft das **Fehlerprotokoll der Domain** im
   Hosting-Panel (Plesk: «Protokolle», Einträge «Apache-Fehler»). Typisch ist eine `open_basedir restriction`, die auf falsche
   Pfade hinweist (siehe Abschnitt 7).

**Weitere Härtung (optional):** Sicherheits-Header (HSTS, Content-Security-Policy) über das CCP bzw. die Webserver-Einstellungen des Hosters,
eigene Fehlerseiten für 404/500 und E-Mail-Bestätigung bei der Registrierung, wenn die App öffentlich zugänglich ist.
