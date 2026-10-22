<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountImporter;
use App\Services\InvalidExportException;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Imports an export file (hostpilot:export) into an existing account.
 */
class ImportData extends Command
{
    protected $signature = 'hostpilot:import
        {file : Pfad zur Export-Datei}
        {--to= : E-Mail-Adresse des Zielkontos (muss auf diesem Server schon registriert sein)}
        {--dry-run : Nur anzeigen, was importiert wuerde, nichts schreiben}
        {--allow-duplicates : Auch Events importieren, die es mit gleichem Titel und Datum schon gibt}
        {--force : Ohne Rueckfrage importieren}';

    protected $description = 'Liest eine Export-Datei in ein bestehendes Konto ein';

    public function handle(AccountImporter $importer): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file) || ! is_readable($file)) {
            $this->error("Die Datei {$file} wurde nicht gefunden oder ist nicht lesbar.");

            return self::FAILURE;
        }

        $email = Str::lower(trim((string) $this->option('to')));

        if ($email === '') {
            $this->error('Bitte das Zielkonto angeben: --to=name@example.com');

            return self::FAILURE;
        }

        $target = User::where('email', $email)->first();

        if (! $target) {
            $this->error("Kein Konto mit der E-Mail-Adresse {$email} gefunden. Bitte zuerst auf der Seite registrieren.");

            return self::FAILURE;
        }

        try {
            $events = $importer->read((string) file_get_contents($file));
        } catch (InvalidExportException $exception) {
            $this->error('Die Datei kann nicht importiert werden:');
            $this->line($exception->getMessage());
            $this->line('Es wurde nichts veraendert.');

            return self::FAILURE;
        }

        $plan = $importer->plan($target, $events, (bool) $this->option('allow-duplicates'));

        $this->line('Zielkonto: '.$target->name.' <'.$target->email.'>');

        foreach ($plan['import'] as $event) {
            $this->line(sprintf(
                '  importieren: %s (%s) - %d Programmpunkt(e), %d Rezept(e), %d Einkaufs-Eintrag/-Eintraege, %d Thema/Themen',
                $event['title'],
                $event['date'],
                count($event['agenda']),
                count($event['recipes']),
                count($event['shopping']),
                count($event['topics']),
            ));
        }

        foreach ($plan['skipped'] as $label) {
            $this->warn("  uebersprungen (gibt es schon): {$label}");
        }

        if ($plan['import'] === []) {
            $this->info('Nichts zu importieren.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Testlauf: Es wurde nichts geschrieben.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(count($plan['import']).' Event(s) jetzt importieren?', true)) {
            $this->line('Abgebrochen. Es wurde nichts veraendert.');

            return self::FAILURE;
        }

        $counts = $importer->import($target, $plan['import']);

        $this->info(sprintf(
            'Fertig: %d Event(s), %d Programmpunkt(e), %d Rezept(e), %d Einkaufs-Eintrag/-Eintraege, %d Thema/Themen und %d Bild(er) importiert.',
            $counts['events'],
            $counts['agenda'],
            $counts['recipes'],
            $counts['shopping'],
            $counts['topics'],
            $counts['images'],
        ));
        $this->line('Loesche die Export-Datei jetzt vom Server, sie enthaelt deine Inhalte.');

        return self::SUCCESS;
    }
}
