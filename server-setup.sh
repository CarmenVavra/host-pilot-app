#!/usr/bin/env bash
# ==========================================================
#  HostPilot - gefuehrte Einrichtung auf dem Server (netcup)
#
#  Aufruf im Projektordner (dort, wo die Datei "artisan" liegt):
#      bash server-setup.sh
#
#  Das Skript fragt alle Werte ab, schreibt die .env (Rechte 600),
#  erzeugt den App-Schluessel, legt die Datenbank-Tabellen an und
#  verknuepft den Bilder-Ordner (relativ).
#  Es werden bewusst KEINE Config-/Routen-/View-Caches angelegt, siehe unten.
#  Passwoerter werden beim Tippen nicht angezeigt und nirgends ausgegeben.
# ==========================================================
set -u

say()  { printf '\n%s\n' "$*"; }
fail() { printf '\nFEHLER: %s\n' "$*" >&2; exit 1; }

# ---------- 1. Voraussetzungen ----------
[ -f artisan ] || fail 'Bitte im Projektordner starten (dort, wo die Datei "artisan" liegt).'
command -v php >/dev/null 2>&1 || fail 'php wurde nicht gefunden.'
php -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' \
    || fail "PHP $(php -r 'echo PHP_VERSION;') ist zu alt. HostPilot braucht PHP 8.2 oder neuer (im Kundenkontrollpanel bei der Domain einstellen)."
[ -f vendor/autoload.php ] || fail 'Der Ordner "vendor" fehlt oder ist unvollstaendig. Bitte das Upload-ZIP vollstaendig entpacken.'

say "HostPilot-Einrichtung (PHP $(php -r 'echo PHP_VERSION;'))"
echo 'Du brauchst: die Daten deiner MySQL-Datenbank und deines E-Mail-Postfachs'
echo '(beides aus dem netcup-Kundenkontrollpanel) sowie deine Impressum-Angaben.'
echo 'Mit Enter uebernimmst du einen Vorschlag in [eckigen Klammern].'

# ---------- 2. Sicherheit: .env im oeffentlichen Ordner ----------
if [ -e public/.env ]; then
    say 'ACHTUNG: public/.env gefunden. Dort ist die Datei fuer jeden im Internet abrufbar!'
    read -r -p 'Jetzt loeschen? [J/n]: ' a
    case ${a:-J} in
        n|N|nein|Nein|no|No|NO) echo 'Nicht geloescht. Bitte selbst entfernen.' ;;
        *)   rm -f public/.env && echo 'public/.env geloescht.' ;;
    esac
fi

# ---------- 3. Eingabe ----------
# ask VAR "Frage" [Vorschlag] [secret]
ask() {
    local var=$1 prompt=$2 def=${3:-} mode=${4:-} val= txt
    while true; do
        if [ -n "$def" ]; then txt="$prompt [$def]: "; else txt="$prompt: "; fi
        if [ "$mode" = secret ]; then
            read -r -s -p "$txt" val; echo
        else
            read -r -p "$txt" val
        fi
        [ -z "$val" ] && val=$def
        if [ -z "$val" ]; then echo '  -> Bitte einen Wert eingeben.'; continue; fi
        if [ "$mode" != secret ]; then
            case $val in
                *'<'*|*'>'*) echo '  -> Bitte ohne spitze Klammern < > eingeben.'; continue ;;
            esac
        fi
        printf -v "$var" '%s' "$val"
        return
    done
}

# Wert fuer die .env in doppelte Anfuehrungszeichen setzen. Der .env-Parser erlaubt
# darin genau die Escapes \\ \" und \$ (alles andere ist woertlich). So bleiben
# Passwoerter mit Sonderzeichen unveraendert.
q() { local s=$1; s=${s//\\/\\\\}; s=${s//\"/\\\"}; s=${s//\$/\\\$}; printf '"%s"' "$s"; }

if [ -f .env ]; then
    say 'Es gibt bereits eine .env.'
    read -r -p 'Ersetzen (die alte wird als .env.alt-DATUM gesichert)? [j/N]: ' a
    case ${a:-N} in j|J|ja|Ja|JA|y|Y|yes|Yes|YES) ;; *) fail 'Abgebrochen. Die vorhandene .env bleibt unveraendert.' ;; esac
fi

say '--- Adresse der Seite ---'
while true; do
    ask APP_URL 'Adresse (mit https://)' 'https://hostpilot.caryssa.at'
    APP_URL=${APP_URL%/}
    [[ $APP_URL =~ ^https://[A-Za-z0-9.-]+(:[0-9]+)?$ ]] && break
    echo '  -> Das sieht nicht wie eine Adresse aus. Beispiel: https://hostpilot.caryssa.at'
done

say '--- Datenbank (netcup Kundenkontrollpanel -> Datenbanken) ---'
ask DB_HOST 'Datenbank-Server (Host)' 'localhost'
ask DB_PORT 'Port' '3306'
ask DB_NAME 'Datenbank-Name'
ask DB_USER 'Datenbank-Benutzer'
ask DB_PASS 'Datenbank-Passwort (wird nicht angezeigt)' '' secret

say '--- E-Mail-Postfach fuer "Passwort vergessen" ---'
ask MAIL_HOST 'SMTP-Server'
ask MAIL_PORT 'SMTP-Port (587 oder 465)' '587'
ask MAIL_USER 'Postfach / Benutzername'
ask MAIL_PASS 'Postfach-Passwort (wird nicht angezeigt)' '' secret
ask MAIL_FROM 'Absender-Adresse' "$MAIL_USER"
if [ "$MAIL_PORT" = 465 ]; then MAIL_SCHEME=smtps; else MAIL_SCHEME=null; fi

say '--- Impressum / Datenschutz (Pflichtangaben, Oesterreich) ---'
ask LEGAL_NAME 'Vor- und Nachname'
ask LEGAL_STREET 'Strasse und Hausnummer'
ask LEGAL_CITY 'PLZ und Ort'
ask LEGAL_EMAIL 'Kontakt-E-Mail' "$MAIL_FROM"

# ---------- 4. .env schreiben ----------
KEY=$(php -r 'echo "base64:".base64_encode(random_bytes(32));') || fail 'Schluessel konnte nicht erzeugt werden.'

if [ -f .env ]; then
    BACKUP=".env.alt-$(date +%Y%m%d%H%M)"
    cp .env "$BACKUP" && chmod 600 "$BACKUP"
    echo "Alte .env gesichert als $BACKUP (enthaelt Geheimnisse - spaeter loeschen)."
fi

umask 077
{
    echo 'APP_NAME=HostPilot'
    echo 'APP_ENV=production'
    echo 'APP_DEBUG=false'
    echo "APP_KEY=$KEY"
    echo "APP_URL=$(q "$APP_URL")"
    echo 'APP_LOCALE=de'
    echo 'APP_FALLBACK_LOCALE=de'
    echo
    echo 'LOG_CHANNEL=stack'
    echo 'LOG_STACK=daily'
    echo 'LOG_LEVEL=warning'
    echo
    echo 'DB_CONNECTION=mysql'
    echo "DB_HOST=$(q "$DB_HOST")"
    echo "DB_PORT=$(q "$DB_PORT")"
    echo "DB_DATABASE=$(q "$DB_NAME")"
    echo "DB_USERNAME=$(q "$DB_USER")"
    echo "DB_PASSWORD=$(q "$DB_PASS")"
    echo
    echo 'SESSION_DRIVER=database'
    echo 'SESSION_LIFETIME=120'
    echo 'SESSION_SECURE_COOKIE=true'
    echo 'SESSION_SAME_SITE=lax'
    echo
    echo 'QUEUE_CONNECTION=sync'
    echo 'CACHE_STORE=file'
    echo 'FILESYSTEM_DISK=local'
    echo
    echo 'MAIL_MAILER=smtp'
    echo "MAIL_HOST=$(q "$MAIL_HOST")"
    echo "MAIL_PORT=$(q "$MAIL_PORT")"
    echo "MAIL_USERNAME=$(q "$MAIL_USER")"
    echo "MAIL_PASSWORD=$(q "$MAIL_PASS")"
    echo "MAIL_SCHEME=$MAIL_SCHEME"
    echo "MAIL_FROM_ADDRESS=$(q "$MAIL_FROM")"
    echo 'MAIL_FROM_NAME=HostPilot'
    echo
    echo 'LEGAL_COUNTRY=AT'
    echo "LEGAL_NAME=$(q "$LEGAL_NAME")"
    echo "LEGAL_STREET=$(q "$LEGAL_STREET")"
    echo "LEGAL_CITY=$(q "$LEGAL_CITY")"
    echo "LEGAL_EMAIL=$(q "$LEGAL_EMAIL")"
} > .env.neu || fail '.env konnte nicht geschrieben werden (Schreibrechte im Projektordner?).'
mv -f .env.neu .env && chmod 600 .env
# Nur die .env soll streng geschuetzt sein. Alles, was Laravel danach anlegt (Caches, Logs),
# muss der Webserver lesen koennen - sonst gibt es eine leere 500-Seite.
umask 022
say '.env geschrieben (Rechte 600, nur du kannst sie lesen).'

# ---------- 5. Laravel einrichten ----------
chmod -R u+rwX,go+rX storage bootstrap/cache 2>/dev/null
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

php artisan optimize:clear >/dev/null 2>&1

say 'Lege die Datenbank-Tabellen an ...'
if ! php artisan migrate --force; then
    fail 'Die Datenbank-Einrichtung ist fehlgeschlagen. Meist stimmen Host, Name, Benutzer oder Passwort nicht. Pruefe die Werte im Kundenkontrollpanel und starte "bash server-setup.sh" erneut.'
fi

# Relativer Link per "ln -s": Die Kommandozeile sieht beim netcup-Webhosting andere (gekuerzte)
# Pfade als der Webserver. Ein absoluter Link (artisan storage:link) wuerde fuer den Webserver ins
# Leere zeigen, ein relativer funktioniert in beiden. ("artisan storage:link --relative" geht hier
# nicht, es braucht das Paket symfony/filesystem, das im Produktiv-Paket nicht enthalten ist.)
if [ -L public/storage ] || [ -e public/storage ]; then rm -f public/storage; fi
ln -s ../storage/app/public public/storage 2>/dev/null \
    && echo 'Bilder-Ordner verknuepft (relativ).' \
    || echo 'Hinweis: Der Bilder-Link konnte nicht angelegt werden (Symlinks evtl. nicht erlaubt). Bild-Upload bitte pruefen.'

# WICHTIG: Kein "config:cache" / "route:cache" / "view:cache"!
# Diese Caches brennen die Pfade der Kommandozeile fest ein. Der Webserver sieht andere Pfade
# (open_basedir) und scheitert dann bei jeder Seite mit einer leeren 500-Seite.

# ---------- 6. Aufraeumen ----------
if ls hostpilot-upload-*.zip >/dev/null 2>&1; then
    read -r -p 'Das Upload-ZIP im Projektordner loeschen? [J/n]: ' a
    case ${a:-J} in n|N|nein|Nein|no|No|NO) ;; *) rm -f hostpilot-upload-*.zip && echo 'ZIP geloescht.' ;; esac
fi

# ---------- 7. Kontrolle von innen ----------
PROBLEM=0
if command -v curl >/dev/null 2>&1; then
    say "Kontrolle der Seite $APP_URL ..."
    for path in /up /login /impressum /.env; do
        code=$(curl -s -o /dev/null -m 15 -w '%{http_code}' "$APP_URL$path" 2>/dev/null || true)
        printf '  %-12s -> %s\n' "$path" "${code:-keine Antwort}"
        if [ "$path" = /.env ]; then
            [ "$code" = 200 ] && PROBLEM=1
        else
            [ "$code" = 200 ] || PROBLEM=1
        fi
    done
    echo '  (/.env darf NICHT 200 sein; 404 ist richtig. /login, /up und /impressum sollten 200 sein.)'
fi

if [ "$PROBLEM" = 1 ]; then
    say 'Eingerichtet, aber die Kontrolle meldet noch Probleme. Letzte Fehlermeldung aus dem Log:'
    LOG=$(ls -t storage/logs/*.log 2>/dev/null | head -n 1)
    if [ -n "$LOG" ]; then
        grep -a 'ERROR' "$LOG" | tail -n 1 | cut -c1-400
    else
        echo '  (kein Log gefunden - bitte den Fehlerlog im Kundenkontrollpanel ansehen)'
    fi
    echo 'Schick mir diese Zeile, dann sehe ich die Ursache.'
    exit 2
fi

say 'Fertig. Oeffne die Seite im Browser und registriere dein Konto.'
echo 'Vergiss nicht: Hatte die alte .env oeffentlich im Netz gelegen, verwende hier NEUE'
echo 'Passwoerter fuer Datenbank und Postfach (vorher im Kundenkontrollpanel aendern).'
