@php
    $units = \App\Enums\Unit::values();
    $categories = \App\Enums\ShoppingCategory::values();
    $prefix = "ingredients[{$index}]";
    $key = "ingredients.{$index}";
@endphp
<div class="ingredient-row" data-ingredient-row>
    <div class="field ingredient-row__name">
        <label class="field__label" for="ing-{{ $index }}-name">Zutat</label>
        <input class="input @error($key.'.name') is-invalid @enderror" id="ing-{{ $index }}-name" name="{{ $prefix }}[name]" value="{{ $row['name'] ?? '' }}" maxlength="80" placeholder="z. B. Eier">
        @error($key.'.name')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label class="field__label" for="ing-{{ $index }}-amount">Menge</label>
        <input class="input @error($key.'.amount') is-invalid @enderror" id="ing-{{ $index }}-amount" name="{{ $prefix }}[amount]" value="{{ $row['amount'] ?? '' }}" inputmode="decimal" placeholder="12">
        @error($key.'.amount')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label class="field__label" for="ing-{{ $index }}-unit">Einheit</label>
        <select class="select" id="ing-{{ $index }}-unit" name="{{ $prefix }}[unit]">
            @foreach ($units as $unit)
                <option value="{{ $unit }}" @selected(($row['unit'] ?? '') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="field ingredient-row__category">
        <label class="field__label" for="ing-{{ $index }}-category">Kategorie</label>
        <select class="select" id="ing-{{ $index }}-category" name="{{ $prefix }}[category]">
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(($row['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div class="ingredient-row__remove">
        <button type="button" class="icon-btn icon-btn--danger" data-remove-ingredient aria-label="Zutat entfernen" title="Zutat entfernen">
            <x-icon name="trash" :size="18" />
        </button>
    </div>
</div>
