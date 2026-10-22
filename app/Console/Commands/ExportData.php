<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Exports all events of one account into a single JSON file (images included).
 */
class ExportData extends Command
{
    protected $signature = 'hostpilot:export
        {--user= : E-Mail-Adresse des Kontos, dessen Events exportiert werden}
        {--output= : Zieldatei (Standard: storage/app/exports/hostpilot-export-DATUM.json)}';

    protected $description = 'Exportiert alle Events eines Kontos (mit Ablauf, Rezepten, Einkauf, Themen und Bildern) in eine Datei';

    public function handle(AccountExporter $exporter): int
    {
        $email = Str::lower(trim((string) $this->option('user')));

        if ($email === '') {
            $this->error('Bitte die E-Mail-Adresse des Kontos angeben: --user=name@example.com');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Kein Konto mit der E-Mail-Adresse {$email} gefunden.");

            return self::FAILURE;
        }

        $data = $exporter->export($user);

        $path = (string) ($this->option('output') ?: storage_path('app/exports/hostpilot-export-'.now()->format('Ymd-His').'.json'));
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        /** @var list<array<string, mixed>> $events */
        $events = $data['events'];
        $recipes = array_sum(array_map(fn (array $event) => count($event['recipes']), $events));
        $images = array_sum(array_map(
            fn (array $event) => ($event['cover'] ? 1 : 0) + count(array_filter($event['recipes'], fn (array $recipe) => $recipe['cover'])),
            $events,
        ));

        $this->info(count($events).' Event(s), '.$recipes.' Rezept(e) und '.$images.' Bild(er) exportiert.');
        $this->line('Datei: '.$path.' ('.number_format((int) filesize($path) / 1024, 0, ',', "'").' KB)');
        $this->line('Die Datei enthaelt deine Inhalte, aber keine Passwoerter oder Konto-Daten.');

        return self::SUCCESS;
    }
}
