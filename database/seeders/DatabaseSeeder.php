<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Services\ShoppingListGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data matching the design mockups («Geburtstags-Brunch Lena» etc.).
 *
 * Login: mira@hostpilot.test / password
 */
class DatabaseSeeder extends Seeder
{
    public function run(ShoppingListGenerator $generator): void
    {
        $mira = User::factory()->create([
            'name' => 'Mira Keller',
            'email' => 'mira@hostpilot.test',
        ]);

        $today = Carbon::today();

        $this->raclette($mira, $today->copy()->addDays(24), $generator);
        $this->brunch($mira, $today->copy()->addDays(33), $generator);
        $this->christmas($mira, $today->copy()->addDays(65));
        $this->summer($mira, $today->copy()->subDays(40));
    }

    private function brunch(User $host, Carbon $date, ShoppingListGenerator $generator): void
    {
        $event = $this->event($host, [
            'title' => 'Geburtstags-Brunch Lena',
            'date' => $date,
            'time' => '10:00',
            'location' => 'Bei Mira, Zürich',
            'guests' => 12,
            'cover_style' => 0,
        ]);

        $people = $this->people($event, [
            ['Mira Keller', 'MK', 0],
            ['Jonas Brunner', 'JB', 1],
            ['Tim Steiner', 'TS', 2],
            ['Alle', 'AL', 3],
        ]);

        foreach ([
            ['08:30', 'Einkauf abholen, Tisch eindecken', 'Bäckerei Frei, Markt', 'MK'],
            ['09:30', 'Zopf aufbacken, Shakshuka ansetzen', 'Ofen auf 180 °C', 'JB'],
            ['10:00', 'Gäste kommen · Aperitif', 'Holunder-Spritz', 'MK'],
            ['10:30', 'Brunch-Buffet eröffnen', 'Warmes zuerst', 'AL'],
            ['12:00', 'Torte & Kerzen', 'Überraschung für Lena', 'TS'],
            ['13:30', 'Kaffee & Spiele', 'Spielekiste aus dem Keller', 'AL'],
        ] as [$time, $title, $note, $who]) {
            $event->agendaItems()->create(['time' => $time, 'title' => $title, 'note' => $note, 'person_id' => $people[$who]]);
        }

        $this->recipe($event, 'Birchermüesli mit Beeren', 'Frühstück', 12, 15, 'vegetarisch', 3, [
            ['Haferflocken', 600, 'g', 'Gewürze & Vorrat'],
            ['Milch', 1000, 'ml', 'Milch & Eier'],
            ['Joghurt nature', 500, 'g', 'Milch & Eier'],
            ['Äpfel', 4, 'Stk', 'Obst & Gemüse'],
            ['Beeren', 1200, 'g', 'Obst & Gemüse'],
            ['Honig', 4, 'EL', 'Gewürze & Vorrat'],
        ], [
            'Haferflocken mit Milch und Joghurt mischen, über Nacht quellen lassen.',
            'Äpfel raffeln und unterheben, mit Honig abschmecken.',
            'Mit Beeren anrichten.',
        ]);

        $this->recipe($event, 'Holunder-Spritz', 'Aperitif', 12, 10, 'vegan', 4, [
            ['Holunderblütensirup', 1000, 'ml', 'Getränke'],
            ['Prosecco', 6, 'Fl.', 'Getränke'],
            ['Mineralwasser', 2, 'l', 'Getränke'],
            ['Minze', 2, 'Bund', 'Obst & Gemüse'],
        ], [
            'Gläser mit Eis füllen.',
            'Sirup, Prosecco und Mineralwasser im Verhältnis 1:3:2 eingiessen.',
            'Mit Minze garnieren.',
        ]);

        $this->recipe($event, 'Shakshuka', 'Brunch', 12, 35, 'vegetarisch', 5, [
            ['Eier', 12, 'Stk', 'Milch & Eier'],
            ['Pelati', 1600, 'g', 'Gewürze & Vorrat'],
            ['Zwiebeln', 4, 'Stk', 'Obst & Gemüse'],
            ['Peperoni', 6, 'Stk', 'Obst & Gemüse'],
            ['Knoblauchzehen', 6, 'Stk', 'Obst & Gemüse'],
            ['Kreuzkümmel', 4, 'TL', 'Gewürze & Vorrat'],
            ['Feta', 400, 'g', 'Milch & Eier'],
        ], [
            'Zwiebeln, Peperoni und Knoblauch in Olivenöl weich dünsten.',
            'Gewürze und Pelati zugeben, 15 Min. köcheln lassen.',
            'Mulden formen, Eier hineingleiten lassen, zugedeckt stocken lassen.',
            'Mit Feta und Kräutern servieren.',
        ]);

        $this->recipe($event, 'Rührei mit Kräutern', 'Brunch', 12, 15, 'vegetarisch', 6, [
            ['Eier', 24, 'Stk', 'Milch & Eier'],
            ['Butter', 100, 'g', 'Milch & Eier'],
            ['Rahm', 200, 'ml', 'Milch & Eier'],
            ['Schnittlauch', 2, 'Bund', 'Obst & Gemüse'],
        ], [
            'Eier mit Rahm verquirlen, salzen und pfeffern.',
            'In Butter bei mittlerer Hitze langsam stocken lassen.',
            'Mit Schnittlauch bestreuen.',
        ]);

        $this->recipe($event, 'Zitronen-Tarte', 'Nachspeise', 12, 75, 'vegetarisch', 7, [
            ['Mehl', 250, 'g', 'Gewürze & Vorrat'],
            ['Butter', 400, 'g', 'Milch & Eier'],
            ['Zucker', 200, 'g', 'Gewürze & Vorrat'],
            ['Eier', 4, 'Stk', 'Milch & Eier'],
            ['Zitronen', 4, 'Stk', 'Obst & Gemüse'],
        ], [
            'Mehl, Butter und die Hälfte des Zuckers zu einem Mürbeteig verarbeiten, kühl stellen.',
            'Teig in die Form legen und 15 Min. blind backen.',
            'Eier, Zucker, Zitronensaft und -abrieb verrühren, auf den Boden giessen.',
            'Bei 160 °C 25 Min. backen und auskühlen lassen.',
        ]);

        $generator->generate($event);

        $event->shoppingItems()
            ->whereIn('name', ['Zwiebeln', 'Peperoni', 'Eier', 'Butter', 'Pelati'])
            ->update(['done' => true]);

        $event->shoppingItems()->create([
            'name' => 'Geburtstagskerzen', 'amount' => 1, 'unit' => 'Pck.',
            'category' => 'Sonstiges', 'done' => false, 'source' => 'manual',
        ]);
    }

    private function raclette(User $host, Carbon $date, ShoppingListGenerator $generator): void
    {
        $event = $this->event($host, [
            'title' => 'Raclette-Abend',
            'date' => $date,
            'time' => '19:00',
            'location' => 'Bei Jonas, Bern',
            'guests' => 8,
            'cover_style' => 1,
        ]);

        $people = $this->people($event, [['Mira Keller', 'MK', 0], ['Jonas Brunner', 'JB', 1], ['Alle', 'AL', 3]]);

        $event->agendaItems()->create(['time' => '18:00', 'title' => 'Raclette-Öfen aufstellen', 'note' => 'Verlängerungskabel!', 'person_id' => $people['JB']]);
        $event->agendaItems()->create(['time' => '19:00', 'title' => 'Gäste kommen', 'note' => null, 'person_id' => $people['AL']]);
        $event->agendaItems()->create(['time' => '21:30', 'title' => 'Meringues mit Rahm', 'note' => null, 'person_id' => $people['MK']]);

        $this->recipe($event, 'Raclette mit Gschwellti', 'Hauptgang', 4, 40, 'vegetarisch', 2, [
            ['Raclettekäse', 800, 'g', 'Milch & Eier'],
            ['Kartoffeln', 1, 'kg', 'Obst & Gemüse'],
            ['Cornichons', 1, 'Dose', 'Gewürze & Vorrat'],
            ['Silberzwiebeln', 1, 'Dose', 'Gewürze & Vorrat'],
        ], ['Kartoffeln in der Schale weich kochen.', 'Käse in Pfännchen schmelzen und über die Kartoffeln geben.']);

        $this->recipe($event, 'Meringues mit Doppelrahm', 'Nachspeise', 8, 10, 'vegetarisch', 7, [
            ['Meringues', 16, 'Stk', 'Brot & Backwaren'],
            ['Doppelrahm', 500, 'ml', 'Milch & Eier'],
            ['Beeren', 400, 'g', 'Obst & Gemüse'],
        ], ['Meringues mit Doppelrahm und Beeren anrichten.']);

        $generator->generate($event);
    }

    private function christmas(User $host, Carbon $date): void
    {
        $event = $this->event($host, [
            'title' => 'Weihnachtsessen Familie',
            'date' => $date,
            'time' => '18:00',
            'location' => 'Zuhause',
            'guests' => 12,
            'cover_style' => 2,
        ]);

        $this->people($event, [['Mira Keller', 'MK', 0], ['Alle', 'AL', 3]]);
    }

    private function summer(User $host, Carbon $date): void
    {
        $event = $this->event($host, [
            'title' => 'Grillabend am See',
            'date' => $date,
            'time' => '18:30',
            'location' => 'Zürichhorn',
            'guests' => 10,
            'cover_style' => 4,
        ]);

        $this->people($event, [['Mira Keller', 'MK', 0], ['Alle', 'AL', 3]]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function event(User $host, array $attributes): Event
    {
        return $host->events()->create($attributes);
    }

    /**
     * @param  list<array{0: string, 1: string, 2: int}>  $people
     * @return array<string, int> initials => id
     */
    private function people(Event $event, array $people): array
    {
        $ids = [];

        foreach ($people as [$name, $initials, $color]) {
            $ids[$initials] = $event->people()->create(['name' => $name, 'initials' => $initials, 'color' => $color])->id;
        }

        return $ids;
    }

    /**
     * @param  list<array{0: string, 1: int|float, 2: string, 3: string}>  $ingredients
     * @param  list<string>  $steps
     */
    private function recipe(Event $event, string $title, string $course, int $portions, int $minutes, string $diet, int $cover, array $ingredients, array $steps): void
    {
        $recipe = $event->recipes()->create([
            'title' => $title,
            'course' => $course,
            'portions' => $portions,
            'duration_minutes' => $minutes,
            'diet' => $diet,
            'steps' => $steps,
            'cover_style' => $cover,
        ]);

        foreach ($ingredients as $position => [$name, $amount, $unit, $category]) {
            $recipe->ingredients()->create(compact('name', 'amount', 'unit', 'category', 'position'));
        }
    }
}
