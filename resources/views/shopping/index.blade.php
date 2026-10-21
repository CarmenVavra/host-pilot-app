@extends('layouts.app')

@section('title', 'Einkauf · '.$event->title)

@section('content')
    @php
        $hasRecipes = $event->recipes_count > 0;
        $recipesLabel = $event->recipes_count === 1 ? '1 Rezept' : $event->recipes_count.' Rezepten';
    @endphp

    <x-event-header :event="$event" active="einkauf">
        <x-slot:actions><a class="btn btn--ghost" href="{{ route('events.shopping.create', $event) }}"><x-icon name="plus" :size="18" :stroke="2.5" />Eintrag hinzufügen</a></x-slot:actions>
    </x-event-header>

    {{-- Web: toolbar --}}
    <div class="toolbar">
        @include('shopping.partials.stepper', ['suffix' => 'web'])
        <form method="POST" action="{{ route('events.shopping.generate', $event) }}">
            @csrf
            <button type="submit" class="btn" @disabled(! $hasRecipes)><x-icon name="sparkle" :size="18" />Aus Rezepten generieren</button>
        </form>
        <div class="toolbar__text">
            @if ($hasRecipes)
                Fasst Zutaten aus {{ $recipesLabel }} zusammen,<br>Mengen skaliert auf {{ $event->guests }} Gäste.
            @else
                Noch keine Rezepte – füge zuerst Rezepte hinzu.
            @endif
        </div>
        <div class="toolbar__progress">
            <div class="toolbar__progress-label"><span>Erledigt</span><span><span data-shop-done>{{ $doneCount }}</span>/{{ $totalCount }}</span></div>
            <x-progress :done="$doneCount" :total="$totalCount" />
        </div>
    </div>

    {{-- Phone: generate button, progress and stepper --}}
    <div class="only-mobile">
        <form class="generate" method="POST" action="{{ route('events.shopping.generate', $event) }}">
            @csrf
            <button type="submit" class="btn" @disabled(! $hasRecipes)>
                <x-icon name="sparkle" />
                <span class="generate__label">Aus Rezepten generieren</span>
                <span class="chip">{{ $event->guests }} Gäste</span>
            </button>
        </form>
        <div class="shop-progress">
            <x-progress class="progress--paper" :done="$doneCount" :total="$totalCount" />
            <span><span data-shop-done>{{ $doneCount }}</span>/{{ $totalCount }}</span>
        </div>
        <div class="m-stepper">@include('shopping.partials.stepper', ['suffix' => 'phone'])</div>
    </div>

    @if ($categories->isEmpty())
        <div class="empty">
            <div class="empty__title">Die Einkaufsliste ist leer</div>
            <p>
                @if ($hasRecipes)
                    Generiere die Liste aus deinen {{ $recipesLabel }} oder ergänze Artikel von Hand.
                @else
                    Füge Rezepte hinzu oder ergänze Artikel von Hand.
                @endif
            </p>
            <a class="btn btn--ghost" href="{{ route('events.shopping.create', $event) }}"><x-icon name="plus" :size="18" :stroke="2.5" />Eintrag hinzufügen</a>
        </div>
    @else
        <div class="categories">
            @foreach ($categories as $category => $items)
                <section class="category" data-category aria-labelledby="cat-{{ $loop->index }}">
                    <div class="category__head">
                        <h2 id="cat-{{ $loop->index }}">{{ $category }}</h2>
                        <span data-category-count aria-label="erledigt">{{ $items->where('done', true)->count() }}/{{ $items->count() }}</span>
                    </div>
                    <ul class="shop-items">
                        @foreach ($items as $item)
                            <li class="shop-item {{ $item->done ? 'is-done' : '' }}" data-shop-item>
                                <form class="shop-item__form" method="POST" action="{{ route('events.shopping.toggle', [$event, $item]) }}" data-toggle-form>
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="shop-item__toggle" aria-pressed="{{ $item->done ? 'true' : 'false' }}">
                                        <span class="check" aria-hidden="true"><x-icon name="check" :size="14" :stroke="4" /></span>
                                        <span class="shop-item__name">{{ $item->name }}</span>
                                        <span class="shop-item__qty">{{ $item->quantity_label }}</span>
                                    </button>
                                </form>
                                @if ($item->isManual())
                                    <form class="shop-item__delete" method="POST" action="{{ route('events.shopping.destroy', [$event, $item]) }}" data-confirm="«{{ $item->name }}» von der Liste löschen?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn--danger" aria-label="«{{ $item->name }}» löschen" title="Löschen">
                                            <x-icon name="trash" :size="16" />
                                        </button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif

    <x-fab :href="route('events.shopping.create', $event)" label="Artikel manuell ergänzen" />
@endsection
