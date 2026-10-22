<?php

/*
 * Angaben für Impressum und Datenschutzerklärung.
 *
 * Werden über die .env gepflegt (LEGAL_*), damit keine persönlichen Daten im
 * Code stehen. Fehlende Pflichtangaben werden auf den Seiten als Platzhalter
 * markiert (siehe App\Support\LegalInfo).
 */
return [
    // AT (Österreich), DE (Deutschland) oder CH (Schweiz): bestimmt die Rechtsgrundlagen im Text.
    'country' => env('LEGAL_COUNTRY', 'AT'),

    'name' => env('LEGAL_NAME'),
    'street' => env('LEGAL_STREET'),
    'city' => env('LEGAL_CITY'),
    'email' => env('LEGAL_EMAIL'),

    // Optional
    'phone' => env('LEGAL_PHONE'),
    'vat_id' => env('LEGAL_VAT_ID'),

    // Hosting-Anbieter (für die Datenschutzerklärung)
    'host' => env('LEGAL_HOST', 'netcup GmbH, Daumstraße 16, 76185 Karlsruhe, Deutschland'),
];
