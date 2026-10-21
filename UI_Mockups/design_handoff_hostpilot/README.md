# Handoff: HostPilot — Event-Planung (Handy-App & Web-App)

## Overview
HostPilot hilft Gastgebern, Events zu planen. Hierarchie: **Event** (oberste Ebene) → je Event genau ein **Ablaufplan**, eine Sammlung von **Rezepten** (nach Gang gruppiert) und eine **Einkaufsliste** (aus Rezepten generiert + manuell ergänzbar). Das Beispiel-Event in allen Screens: «Geburtstags-Brunch Lena», So 23. Nov 2026, 10:00, 12 Gäste, «Bei Mira, Zürich».

UI-Sprache: Deutsch (Schweizer Schreibweise, «ss» statt «ß», z. B. «Portionsgrösse»).

## About the Design Files
Die Dateien in diesem Bundle sind **Design-Referenzen in HTML** (Prototypen für Look und Verhalten), **kein Produktionscode zum Kopieren**. Aufgabe: die Designs in der Ziel-Codebasis nachbauen (React Native / SwiftUI / Flutter für die App, React/Vue/etc. fürs Web) mit deren etablierten Patterns und Bibliotheken. Existiert noch kein Stack, wähle den passendsten (Empfehlung: React Native/Expo + React-Web mit geteilter Domänenlogik).
`reference/HostPilot.dc.html` ist der Quelltext der Mockups (inline-Styles, Daten in `renderVals()` am Dateiende).

## Fidelity
**High-fidelity.** Finale Farben, Typografie, Abstände, Rahmen. Bitte pixelgenau umsetzen. Platzhalter: Cover-/Rezeptbilder sind diagonale Streifenmuster und müssen durch Nutzerfotos ersetzt werden (Fotos in Schwarz-Weiss ist Modernist-Vorgabe, hier optional/Farbfoto möglich — mit Design abstimmen).

## Screenshots
`screenshots/` — Handy (820×1664, 2×; Gerät 410×832 CSS-px) und Web (1200×760).
Handy: `mobile-1a-event-uebersicht`, `1b-event-hub`, `1c-ablaufplan`, `1d-rezepte`, `1e-rezept-detail`, `1f-einkaufsliste`.
Web: `web-1g-event-uebersicht`, `1h-ablauf`, `1i-rezepte`, `1j-einkauf`.

## Design Tokens
**Stil «Modernist»:** flach, rechtwinklig (**Radius 0** überall, ausser Geräterahmen im Mockup), 2-px-Linien, linksbündig, kein Weichzeichner-Schatten.

Farben
- Creme (Grund): `#f5eec2`
- Papier (Karten/Sidebar): `#fbf7df`
- Indigo (Text, Linien, aktive Tabs, Kategorie-Header): `#39395f`
- Waldgrün (Primäraktion, FAB, abgehakt, Fortschritt): `#416a59`
- Grün: `#73a24e` · Limette (Tags, Akzent-Icon, Gang-Tag): `#a9c25d`
- Gedämpfter Text: `#5a5a7a`
- Terrakotta (Löschen): `#b5533c`
- Trennlinien innerhalb Listen: `rgba(57,57,95,.35)` 1 px; Hauptlinien `#39395f` 2 px
- Text auf Waldgrün/Indigo: `#f5eec2` bzw. `#fff`; Text auf Grün/Limette: Indigo (Kontrast!)

Typografie: **Archivo** (Google Fonts, 400/600/800) für alles.
- H1 Seitentitel Handy 32–36/1.05, Web 38–44/1; weight 800, letter-spacing −0.015em
- Kicker: 12 px, 600, uppercase, letter-spacing .08em, Waldgrün
- Card-Titel 20–22 px/800; Body 13–15 px; Tags 11 px/800 uppercase .05em
- Kategorie-Header (Einkauf): 12 px/800 uppercase .07em, Indigo-Fläche, Creme-Text

Abstand: Handy-Seitenrand 20 px; Web-Content-Padding 36 px; Kartenpadding 12–18 px; Gaps 12–24 px.
Schatten: nur FAB «harter» Versatz `4px 4px 0 #39395f`.
Icons: Lucide (im Mockup inline: calendar, users, plus, chevron-left/right, clock, pencil, trash, sparkle, shopping-cart, file-text), Strichstärke 2, `stroke-linecap: square`.

## Screens / Views

### Handy (Gerät 410×832, Screen 390×812, Statusleiste 46 px)

**1a Event-Übersicht (Dashboard)** — Brand-Zeile (Logo-Quadrat 28 px Waldgrün «H» + «HostPilot» 18/800, rechts Avatar-Quadrat 34 px 2 px Rahmen «MK»). Kicker «3 Events geplant», H1 «Deine Events». Segmented Control 2 Optionen «Anstehend» (aktiv: Indigo/Creme) | «Vergangen», 2 px Rahmen. Event-Karten (vertikal, gap 14): 2 px Indigo-Rahmen, Papier; Cover 96 px hoch mit Badge oben links («in 24 Tagen», Indigo/Creme, 11 px uppercase); unter 2-px-Linie Titel + Zeile Kalender-Icon Datum («Fr, 14. Nov») und Users-Icon «8 Gäste». Sortiert nach Datum aufsteigend. FAB 60×60 Waldgrün, «+», rechts 20/unten 24. **Tap Karte → 1b; FAB → Event anlegen.**

**1b Event-Detail & Hub** — Cover 112–150 px mit Zurück-Button (38 px Quadrat, Creme, 2 px Rahmen). Kicker «in 33 Tagen», H1 32 px Eventname, Meta-Zeile (Datum · Uhrzeit, Gäste, Ort). Segmented Control mit 3 Segmenten Ablauf | Rezepte | Einkauf (hier keines aktiv). Darunter 3 Hub-Karten: Icon-Quadrat 44 px (Limette / Grün / Waldgrün), Titel 17/800, Untertitel (Ablauf: «6 Punkte · als Nächstes 08:30 …»; Rezepte: «5 Rezepte»; Einkauf: «5 von 10 erledigt» + Fortschrittsbalken 10 px, Füllung Waldgrün) und Chevron.

**1c Ablaufplan** — Header: Zurück + Eventname 18/800; Segmented (Ablauf aktiv). Titel «So, 23. November» 22 px, rechts Hinweis «Wisch = bearbeiten». Vertikale Timeline: Spalten Uhrzeit (50 px, 16/800) | Schiene (26 px: 12-px-Quadrat mit 2 px Rahmen, durchgehende 2-px-Linie) | Karte (Papier, 2 px Rahmen, Titel 15/800, Notiz 12 px gedämpft, rechts Verantwortlicher als 30-px-Quadrat mit Initialen). Aktueller/hervorgehobener Punkt: Quadrat Waldgrün. **Wisch nach links** zeigt Aktionen «Ändern» (Waldgrün) und «Löschen» (Terrakotta), je ~68 px breit; Long-Press öffnet dieselben Aktionen. FAB «+» = Programmpunkt hinzufügen (Uhrzeit, Titel, Verantwortliche/r, optionale Notiz).

**1d Rezepte** — Segmented (Rezepte aktiv). Gruppierung nach Gang; Gruppenkopf: H4 18 px + Anzahl rechts, 2 px Unterlinie. Gänge: Frühstück, Brunch, Mittag, Abend, Aperitif, Vorspeise, Hauptgang, Nachspeise u. a. (leere Gänge ausblenden; Reihenfolge nach Tagesverlauf). Rezeptzeile: Thumbnail 52 px (2 px Rahmen), Name 16/800, Gang-Tag (Limette), «12 Portionen». Tap → 1e. FAB «+» neues Rezept.

**1e Rezept-Detail** — Cover 112 px mit Zurück, Gang-Tag, Titel 34 px, Meta (Portionen · Dauer · Ernährungsform). Abschnitt «Zutaten» (2-px-Linie oben) mit Badge «skaliert auf 12 Gäste» (Waldgrün/weiss); Zeilen Name links, Menge rechts fett. Abschnitt «Zubereitung»: nummerierte Schritte (Nummer Waldgrün 800).

**1f Einkaufsliste** — Segmented (Einkauf aktiv). Primärbutton volle Breite «Aus Rezepten generieren» (Waldgrün, Sparkle-Icon, linksbündig; rechts Chip «12 Gäste» Limette) — fasst Zutaten aller zugeordneten Rezepte zusammen, summiert gleiche Zutaten, skaliert Mengen (Rezept-Portionen → Gästezahl). Fortschrittsbalken + «5/10». Kategorien (Obst & Gemüse, Milch & Eier, Fleisch & Fisch, Gewürze & Vorrat, Getränke, …): Header Indigo mit «erledigt/gesamt»; Zeile: Checkbox 22 px (2 px Rahmen; erledigt: Waldgrün + Check, Name durchgestrichen, Opacity .55), Name 15/600, Menge rechts 13/800. Tap auf Zeile togglet. FAB «+» = manuell ergänzen.

### Web (1200×760 Referenz; App-Shell)
Shell: Sidebar 232 px (Papier, rechts 2 px Linie): Logo, Abschnitt «Events» (aktives Event: Indigo-Fläche), Profil unten. Hauptbereich: Header mit 2 px Unterlinie.

**1g Event-Übersicht** — H1 44 px, rechts Segmented + Button «Neues Event» (Waldgrün). Raster 3 Spalten, gap 24; Karten wie Handy (Cover 130 px). Letzte Zelle: gestrichelte «Neues Event erstellen»-Kachel.

**1h Ablauf** — Breadcrumb «Events / Eventname», H1 38 px, Meta rechts. Tabs: Text 16/800, aktiver Tab 4 px Waldgrün-Unterstrich. Zweispaltig: links Timeline (Uhrzeit 64 px, Karten mit Verantwortlichem + Name, Edit-/Löschen-Icons immer sichtbar); rechts 300-px-Panel «Neuer Programmpunkt» (Uhrzeit, Titel, Verantwortlich als Avatar-Auswahl, Button «Hinzufügen»).

**1i Rezepte** — Links gruppierte Liste (Zeile: Thumbnail 44 px, Name, Gang-Tag, «12 Portionen»; ausgewählte Zeile Limette 33 % Alpha); rechts 380-px-Detailpanel (Cover 110 px, Zutaten, Zubereitung) wie 1e.

**1j Einkauf** — Toolbar (Papier, 2 px Unterlinie): Gäste-Stepper (− / 12 / +), Button «Aus Rezepten generieren», Erklärtext, Fortschritt «Erledigt 5/10». Darunter Kategorien in 3 Spalten (gap 32), Zeilen wie 1f.

## Interactions & Behavior
- Navigation: Übersicht → Event → Tabs Ablauf/Rezepte/Einkauf (Segmented Control Handy, Tabs Web); Rezept → Rezept-Detail.
- Einkauf: Tap togglet Erledigt; Fortschritt/Zähler (gesamt + je Kategorie) live.
- Gästezahl ändern → Mengen in Rezept-Detail und generierter Einkaufsliste neu skaliert. Rundung: Stück/Bund/Fl./TL aufrunden auf ganze Zahlen; g auf 10 g, ≥1000 g als kg (1 Nachkommastelle); ml analog als l.
- «Aus Rezepten generieren»: bestehende manuelle Einträge und Haken erhalten; neue Zutaten ergänzen, doppelte zusammenführen; Bestätigung/Toast «32 Zutaten aus 5 Rezepten für 12 Gäste».
- Ablauf: Wisch/Long-Press → Ändern/Löschen (Löschen mit Bestätigung oder Undo-Snackbar), «+» hinzufügen; Sortierung nach Uhrzeit.
- Hover (Web): Hintergrund eine Stufe aufhellen/abdunkeln aus Palette; Pressed: Waldgrün leicht dunkler. Fokus: `outline: 2px solid #416a59; offset 2px`. Disabled: 45 % Opacity.
- Nicht im Mockup (noch zu designen): Leerzustände, Ladezustände, Fehler, Formulare «Event/Rezept anlegen», Teilnehmerverwaltung, Gästezahl-Eingabe auf Eventebene.

## State Management
Datenmodell: `Event {id, title, date, time, location, guests, cover}` · `AgendaItem {id, eventId, time, title, note, assigneeId}` · `Recipe {id, eventId, title, course, portions, duration, diet, ingredients[{name, amount, unit, category}], steps[]}` · `ShoppingItem {id, eventId, name, amount, unit, category, done, source: 'recipe'|'manual'}` · `Person {id, initials, name}`.
Ableitungen: Fortschritt = done/total; skalierte Menge = amount × guests / recipe.portions; «in X Tagen» aus Datum. Beispielwerte im Mockup sind für 12 Gäste kalibriert (Basis 12).

## Assets
Keine Bitmap-Assets. Icons: Lucide. Schrift: Archivo (Google Fonts). Bilder: Platzhalter-Streifen → durch Nutzerfotos ersetzen.

## Files
- `reference/HostPilot.dc.html` — alle 10 Screens (Daten + Logik am Dateiende)
- `screenshots/*.png` — gerenderte Screens
