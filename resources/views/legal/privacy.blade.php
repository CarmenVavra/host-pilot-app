@extends('layouts.page')

@section('title', 'Datenschutzerklärung')

@section('content')
    @include('legal.partials.notice')

    <h1 class="legal__title">Datenschutzerklärung</h1>
    <p class="legal__basis">Informationen nach Art. 13 und 14 der Datenschutz-Grundverordnung (DSGVO)</p>

    <section aria-labelledby="p-controller">
        <h2 id="p-controller">1. Verantwortliche Stelle</h2>
        <address class="legal__address">
            <x-legal-value :legal="$legal" key="name" /><br>
            <x-legal-value :legal="$legal" key="street" />, <x-legal-value :legal="$legal" key="city" /><br>
            {{ $legal->countryName() }}<br>
            E-Mail:
            @if ($legal->filled('email'))
                <a href="mailto:{{ $legal->get('email') }}">{{ $legal->get('email') }}</a>
            @else
                <x-legal-value :legal="$legal" key="email" />
            @endif
        </address>
    </section>

    <section aria-labelledby="p-overview">
        <h2 id="p-overview">2. Welche Daten wir verarbeiten und warum</h2>
        <p>HostPilot ist eine Web-App zur Planung von Events. Wir verarbeiten nur die Daten, die dafür nötig sind.</p>
        <ul>
            <li>
                <strong>Konto (Registrierung und Anmeldung):</strong> Name, E-Mail-Adresse und Passwort. Das Passwort wird
                nur verschlüsselt (gehasht) gespeichert. Zweck ist die Bereitstellung deines Kontos und der App-Funktionen,
                Rechtsgrundlage ist die Vertragserfüllung (Art. 6 Abs. 1 lit. b DSGVO).
            </li>
            <li>
                <strong>Deine Inhalte:</strong> Events (Titel, Datum, Ort, Gästezahl, Cover-Bild), Ablaufpläne, Rezepte,
                Einkaufslisten und Themen mit Notizen. Diese Daten speichern wir, damit du sie nutzen kannst
                (Art. 6 Abs. 1 lit. b DSGVO).
            </li>
            <li>
                <strong>Namen von Helferinnen und Helfern:</strong> Im Ablaufplan kannst du Vornamen oder Namen von
                Personen eintragen, die etwas übernehmen. Bitte gib nur Namen ein, mit deren Verwendung die Personen
                einverstanden sind. Es werden keine Kontaktdaten dieser Personen gespeichert.
            </li>
            <li>
                <strong>E-Mail-Versand:</strong> Wenn du ein neues Passwort anforderst, senden wir dir eine E-Mail mit
                einem Link zum Zurücksetzen (Art. 6 Abs. 1 lit. b DSGVO). Weitere E-Mails senden wir nicht.
            </li>
            <li>
                <strong>Server-Protokolle:</strong> Beim Aufruf der Seite verarbeitet unser Hosting-Anbieter technisch
                notwendige Daten wie IP-Adresse, Datum und Uhrzeit, aufgerufene Seite und Browsertyp. Das dient der
                sicheren und stabilen Bereitstellung (berechtigtes Interesse, Art. 6 Abs. 1 lit. f DSGVO).
            </li>
        </ul>
    </section>

    <section aria-labelledby="p-cookies">
        <h2 id="p-cookies">3. Cookies</h2>
        <p>
            HostPilot verwendet ausschliesslich technisch notwendige Cookies. Sie sind für die Anmeldung und die
            Sicherheit der App erforderlich und werden ohne Einwilligung gesetzt:
        </p>
        <ul>
            <li><strong>Sitzungs-Cookie:</strong> hält dich angemeldet, solange du die App nutzt.</li>
            <li><strong>XSRF-Token:</strong> schützt Formulare vor Missbrauch (Cross-Site-Request-Forgery).</li>
            <li><strong>«Angemeldet bleiben»:</strong> nur wenn du diese Option bei der Anmeldung aktivierst.</li>
        </ul>
        <p>Wir setzen keine Tracking-, Analyse- oder Werbe-Cookies ein und binden keine Inhalte von Drittanbietern ein.</p>
    </section>

    <section aria-labelledby="p-fonts">
        <h2 id="p-fonts">4. Schriftarten</h2>
        <p>
            Die verwendete Schrift «Archivo» wird von unserem eigenen Server ausgeliefert. Beim Aufruf der Seite wird
            keine Verbindung zu Google oder anderen Schriftanbietern hergestellt.
        </p>
    </section>

    <section aria-labelledby="p-hosting">
        <h2 id="p-hosting">5. Hosting</h2>
        <p>
            Die App läuft bei {{ $legal->get('host') }}. Mit dem Anbieter besteht ein Vertrag zur Auftragsverarbeitung
            nach Art. 28 DSGVO.
        </p>
    </section>

    <section aria-labelledby="p-retention">
        <h2 id="p-retention">6. Speicherdauer</h2>
        <p>
            Deine Daten bleiben gespeichert, solange dein Konto besteht. Du kannst dein Konto jederzeit selbst löschen
            (siehe «Profil» → «Konto löschen»). Dabei werden dein Konto, alle Events mit Ablauf, Rezepten, Einkaufslisten,
            Themen und hochgeladenen Bildern unwiderruflich gelöscht. Server-Protokolle werden vom Hosting-Anbieter nach
            dessen Fristen gelöscht.
        </p>
    </section>

    <section aria-labelledby="p-recipients">
        <h2 id="p-recipients">7. Weitergabe an Dritte</h2>
        <p>
            Wir geben deine Daten nicht an Dritte weiter, ausser an den Hosting-Anbieter als Auftragsverarbeiter und
            soweit wir gesetzlich dazu verpflichtet sind. Es findet keine automatisierte Entscheidungsfindung oder
            Profilbildung statt.
        </p>
    </section>

    <section aria-labelledby="p-rights">
        <h2 id="p-rights">8. Deine Rechte</h2>
        <p>Du hast das Recht auf:</p>
        <ul>
            <li>Auskunft über die gespeicherten Daten (Art. 15 DSGVO),</li>
            <li>Berichtigung unrichtiger Daten – Name und E-Mail kannst du im Profil selbst ändern (Art. 16 DSGVO),</li>
            <li>Löschung – direkt in der App über «Konto löschen» (Art. 17 DSGVO),</li>
            <li>Einschränkung der Verarbeitung (Art. 18 DSGVO),</li>
            <li>Datenübertragbarkeit (Art. 20 DSGVO),</li>
            <li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO).</li>
        </ul>
        <p>
            Wende dich dafür an die oben genannte E-Mail-Adresse. Du hast ausserdem das Recht, dich bei einer
            Datenschutz-Aufsichtsbehörde zu beschweren:
            @switch($legal->country())
                @case('DE')
                    bei der für dich zuständigen Landesdatenschutzbehörde (Übersicht unter
                    <a href="https://www.bfdi.bund.de" rel="noopener noreferrer">bfdi.bund.de</a>).
                    @break
                @case('CH')
                    beim Eidgenössischen Datenschutz- und Öffentlichkeitsbeauftragten
                    (<a href="https://www.edoeb.admin.ch" rel="noopener noreferrer">edoeb.admin.ch</a>).
                    @break
                @default
                    bei der österreichischen Datenschutzbehörde
                    (<a href="https://www.dsb.gv.at" rel="noopener noreferrer">dsb.gv.at</a>).
            @endswitch
        </p>
    </section>

    <section aria-labelledby="p-security">
        <h2 id="p-security">9. Sicherheit</h2>
        <p>
            Die Verbindung zur App ist per HTTPS verschlüsselt. Passwörter werden nicht im Klartext gespeichert.
            Jede Person sieht ausschliesslich ihre eigenen Events.
        </p>
    </section>

    <p class="legal__basis">Stand: Oktober 2026</p>
@endsection
