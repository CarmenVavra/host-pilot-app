<article class="recipe-detail">
    <x-cover :model="$recipe" class="hero hero--recipe">
        <a class="back-btn hero__back only-mobile" href="{{ route('events.recipes.index', $event) }}" aria-label="Zurück zu den Rezepten">
            <x-icon name="chevron-left" :stroke="2.5" />
        </a>
    </x-cover>

    <div class="recipe-detail__head">
        <span class="tag">{{ $recipe->courseEnum()->value }}</span>
        <h2 class="recipe-detail__title" id="recipe-title">{{ $recipe->title }}</h2>
        <div class="meta">
            <span>{{ $event->guests }} Portionen</span>
            @if ($recipe->duration_label)<span>{{ $recipe->duration_label }}</span>@endif
            @if ($recipe->diet)<span>{{ $recipe->diet }}</span>@endif
        </div>
    </div>

    <section class="recipe-section" aria-labelledby="ingredients-title">
        <div class="recipe-section__head">
            <h3 id="ingredients-title">Zutaten</h3>
            <span class="badge-forest">skaliert auf {{ $event->guests }} Gäste</span>
        </div>
        @if ($recipe->ingredients->isEmpty())
            <p class="muted">Noch keine Zutaten erfasst.</p>
        @else
            <ul class="ingredients">
                @foreach ($recipe->ingredients as $ingredient)
                    <li><span>{{ $ingredient->name }}</span><b>{{ $ingredient->scaledLabel($recipe->portions, $event->guests) }}</b></li>
                @endforeach
            </ul>
        @endif
        @if ($recipe->portions !== $event->guests)
            <p class="field__help">Originalrezept für {{ $recipe->portions }} Portionen.</p>
        @endif
    </section>

    <section class="recipe-section" aria-labelledby="steps-title">
        <div class="recipe-section__head"><h3 id="steps-title">Zubereitung</h3></div>
        @if (empty($recipe->steps))
            <p class="muted">Noch keine Zubereitungsschritte erfasst.</p>
        @else
            <ol class="steps">
                @foreach ($recipe->steps as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        @endif
    </section>

    <div class="recipe-detail__actions">
        <a class="btn btn--ghost" href="{{ route('events.recipes.edit', [$event, $recipe]) }}"><x-icon name="pencil" :size="18" />Bearbeiten</a>
        <form method="POST" action="{{ route('events.recipes.destroy', [$event, $recipe]) }}" data-confirm="Rezept «{{ $recipe->title }}» löschen?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger"><x-icon name="trash" :size="18" />Löschen</button>
        </form>
    </div>
</article>
