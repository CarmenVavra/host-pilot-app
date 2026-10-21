@extends('layouts.app')

@section('title', ($isDetail && $selected ? $selected->title : 'Rezepte').' · '.$event->title)

@section('content')
    {{-- On the phone the recipe detail is a screen of its own with a cover header. --}}
    <x-event-header :event="$event" active="rezepte" :mobile="! $isDetail">
        <x-slot:actions><a class="btn" href="{{ route('events.recipes.create', $event) }}"><x-icon name="plus" :size="18" :stroke="2.5" />Neues Rezept</a></x-slot:actions>
    </x-event-header>

    <div class="split">
        {{-- Recipe list grouped by course --}}
        <section class="split__main {{ $isDetail ? 'only-desktop' : '' }}" aria-label="Rezepte nach Gang">
            @if ($groups->isEmpty())
                <div class="empty">
                    <div class="empty__title">Noch keine Rezepte</div>
                    <p>Füge Rezepte hinzu – die Zutaten landen auf Knopfdruck in der Einkaufsliste.</p>
                    <a class="btn" href="{{ route('events.recipes.create', $event) }}"><x-icon name="plus" :size="18" :stroke="2.5" />Rezept hinzufügen</a>
                </div>
            @else
                <div class="recipe-list">
                    @foreach ($groups as $course => $recipes)
                        <section class="course-group" aria-labelledby="course-{{ $loop->index }}">
                            <div class="course-group__head">
                                <h2 id="course-{{ $loop->index }}">{{ $course }}</h2>
                                <span class="course-group__count">{{ $recipes->count() }} {{ $recipes->count() === 1 ? 'Rezept' : 'Rezepte' }}</span>
                            </div>
                            @foreach ($recipes as $recipe)
                                <a class="recipe-row {{ $selected && $selected->id === $recipe->id ? 'is-selected' : '' }}"
                                   href="{{ route('events.recipes.show', [$event, $recipe]) }}"
                                   @if ($selected && $selected->id === $recipe->id) aria-current="true" @endif>
                                    <x-cover :model="$recipe" />
                                    <span class="recipe-row__main">
                                        <span class="recipe-row__name">{{ $recipe->title }}</span>
                                        <span class="recipe-row__meta">
                                            <span class="tag">{{ $course }}</span>
                                            <span class="recipe-row__portions">{{ $event->guests }} Portionen</span>
                                        </span>
                                    </span>
                                    <x-icon name="chevron-right" :size="18" :stroke="2.5" class="icon recipe-row__chevron" />
                                </a>
                            @endforeach
                        </section>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Recipe detail: side panel on the web, own screen on the phone --}}
        @if ($selected)
            <aside class="split__side split__side--380 {{ $isDetail ? '' : 'only-desktop' }}" aria-labelledby="recipe-title">
                @include('recipes.partials.detail', ['recipe' => $selected])
            </aside>
        @endif
    </div>

    @unless ($isDetail)
        <x-fab :href="route('events.recipes.create', $event)" label="Neues Rezept" />
    @endunless
@endsection
