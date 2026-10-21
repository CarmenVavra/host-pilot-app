@extends('layouts.app')

@section('title', ($recipe->exists ? 'Rezept bearbeiten' : 'Neues Rezept').' · '.$event->title)

@section('content')
    @php
        $heading = $recipe->exists ? 'Rezept bearbeiten' : 'Neues Rezept';
        $back = $recipe->exists ? route('events.recipes.show', [$event, $recipe]) : route('events.recipes.index', $event);
        $courses = \App\Enums\Course::values();
        $units = \App\Enums\Unit::values();
        $categories = \App\Enums\ShoppingCategory::values();

        $rows = old('ingredients', $recipe->exists
            ? $recipe->ingredients->map(fn ($i) => ['name' => $i->name, 'amount' => $i->amount, 'unit' => $i->unit, 'category' => $i->category])->all()
            : []);
        if (count($rows) === 0) {
            $rows = [['name' => '', 'amount' => '', 'unit' => 'Stk', 'category' => 'Obst & Gemüse']];
        }
        $steps = old('steps', implode("\n", $recipe->steps ?? []));
    @endphp

    <div class="m-header only-mobile">
        <a class="m-header__back" href="{{ $back }}" aria-label="Zurück">
            <x-icon name="chevron-left" :size="22" :stroke="2.5" />
        </a>
        <h1 class="m-header__title">{{ $heading }}</h1>
    </div>

    <header class="d-head only-desktop">
        <div>
            <nav class="breadcrumb" aria-label="Brotkrumen">
                <a href="{{ route('events.index') }}">Events</a> /
                <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a> /
                <a href="{{ route('events.recipes.index', $event) }}">Rezepte</a>
            </nav>
            <h1 class="h1">{{ $heading }}</h1>
        </div>
    </header>

    <div class="form-page">
        @if ($errors->any())
            <div class="alert" role="alert">Bitte prüfe die markierten Felder.</div>
        @endif

        <form class="form" method="POST" enctype="multipart/form-data"
              action="{{ $recipe->exists ? route('events.recipes.update', [$event, $recipe]) : route('events.recipes.store', $event) }}">
            @csrf
            @if ($recipe->exists)
                @method('PUT')
            @endif

            <x-input name="title" label="Titel" :value="$recipe->title" required maxlength="120" placeholder="z. B. Shakshuka" />

            <div class="form-grid form-grid--2">
                <x-select name="course" label="Gang" :options="$courses" :value="$recipe->course" required />
                <x-input name="portions" label="Portionen" type="number" :value="$recipe->portions" required min="1" max="500" inputmode="numeric"
                         help="Für wie viele Portionen die Mengen im Rezept gedacht sind." />
            </div>

            <div class="form-grid form-grid--2">
                <x-input name="duration_minutes" label="Dauer in Minuten" type="number" :value="$recipe->duration_minutes" min="1" max="1440" inputmode="numeric" />
                <x-input name="diet" label="Ernährungsform" :value="$recipe->diet" maxlength="60" placeholder="z. B. vegetarisch" list="diet-options" />
                <datalist id="diet-options">
                    <option value="vegetarisch"><option value="vegan"><option value="glutenfrei"><option value="laktosefrei"><option value="mit Fleisch"><option value="mit Fisch">
                </datalist>
            </div>

            <section class="card" aria-labelledby="ingredients-heading">
                <h2 id="ingredients-heading">Zutaten</h2>
                <div class="ingredient-rows" data-ingredient-rows data-next="{{ count($rows) }}">
                    @foreach ($rows as $index => $row)
                        @include('recipes.partials.ingredient-row', ['index' => $index, 'row' => $row])
                    @endforeach
                </div>
                <p class="card__foot">
                    <button type="button" class="btn btn--ghost" data-add-ingredient><x-icon name="plus" :size="18" :stroke="2.5" />Zutat hinzufügen</button>
                </p>
            </section>

            <div class="field">
                <label class="field__label" for="f-steps">Zubereitung</label>
                <textarea class="textarea @error('steps') is-invalid @enderror" id="f-steps" name="steps" aria-describedby="f-steps-help"
                          placeholder="Ein Schritt pro Zeile">{{ $steps }}</textarea>
                <span class="field__help" id="f-steps-help">Ein Schritt pro Zeile – die Nummerierung erfolgt automatisch.</span>
                @error('steps')<span class="error">{{ $message }}</span>@enderror
            </div>

            <x-input name="cover" label="Bild (optional)" type="file" accept="image/*" help="JPG, PNG oder WebP, max. 5 MB." />
            @if ($recipe->cover_path)
                <label class="checkbox">
                    <input type="hidden" name="remove_cover" value="0">
                    <input type="checkbox" name="remove_cover" value="1"> Aktuelles Bild entfernen
                </label>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn">Rezept speichern</button>
                <a class="btn btn--ghost" href="{{ $back }}">Abbrechen</a>
            </div>
        </form>

        <template id="ingredient-row-template">
            @include('recipes.partials.ingredient-row', ['index' => '__INDEX__', 'row' => ['name' => '', 'amount' => '', 'unit' => 'Stk', 'category' => 'Obst & Gemüse']])
        </template>
    </div>
@endsection
