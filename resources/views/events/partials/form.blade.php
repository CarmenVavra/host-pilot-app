<form class="form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-input name="title" label="Titel" :value="$event->title" required maxlength="120" placeholder="z. B. Geburtstags-Brunch Lena" />

    <div class="form-grid form-grid--2">
        <x-input name="date" label="Datum" type="date" :value="$event->date?->format('Y-m-d')" required />
        <x-input name="time" label="Uhrzeit" type="time" :value="$event->time" />
    </div>

    <div class="form-grid form-grid--2">
        <x-input name="location" label="Ort" :value="$event->location" maxlength="120" placeholder="z. B. Bei Mira, Zürich" />
        <x-input name="guests" label="Gäste" type="number" :value="$event->guests" required min="1" max="500" inputmode="numeric" />
    </div>

    <x-input name="cover" label="Cover-Bild (optional)" type="file" accept="image/*" help="JPG, PNG oder WebP, max. 5 MB. Ohne Bild wird ein Muster verwendet." />

    @if ($event->cover_path)
        <label class="checkbox">
            <input type="hidden" name="remove_cover" value="0">
            <input type="checkbox" name="remove_cover" value="1"> Aktuelles Cover-Bild entfernen
        </label>
    @endif

    <div class="form-actions">
        <button type="submit" class="btn">{{ $submit }}</button>
        <a class="btn btn--ghost" href="{{ $event->exists ? route('events.show', $event) : route('events.index') }}">Abbrechen</a>
    </div>
</form>
