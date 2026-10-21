@extends('layouts.app')

@section('title', $event->title)

@section('content')
    @php
        $agendaCount = $event->agendaItems->count();
        $recipeCount = $event->recipes->count();
        $agendaSub = $agendaCount === 1 ? '1 Punkt' : $agendaCount.' Punkte';
        if ($nextItem) {
            $agendaSub .= ' · als Nächstes '.$nextItem->time.' '.$nextItem->title;
        }
        $recipeSub = ($recipeCount === 1 ? '1 Rezept' : $recipeCount.' Rezepte')
            .($event->courseRangeLabel() ? ' · '.$event->courseRangeLabel() : '');
    @endphp

    {{-- Phone: cover with back button, title and meta --}}
    <div class="only-mobile">
        <x-cover :model="$event" class="hero">
            <a class="back-btn hero__back" href="{{ route('events.index') }}" aria-label="Zurück zur Übersicht">
                <x-icon name="chevron-left" :stroke="2.5" />
            </a>
        </x-cover>
        <div class="page-head page-head--tight">
            <div class="kicker">{{ $event->relative_label }}</div>
            <h1 class="h1 h1--event">{{ $event->title }}</h1>
            <div class="meta">
                <span>{{ $event->date_time_label }}</span>
                <span>{{ $event->guests }} Gäste</span>
                @if ($event->location)<span>{{ $event->location }}</span>@endif
            </div>
        </div>
        <nav class="segmented segmented--tabs segmented--spaced" aria-label="Bereiche des Events">
            <a class="segmented__item" href="{{ route('events.agenda.index', $event) }}">Ablauf</a>
            <a class="segmented__item" href="{{ route('events.recipes.index', $event) }}">Rezepte</a>
            <a class="segmented__item" href="{{ route('events.shopping.index', $event) }}">Einkauf</a>
        </nav>
    </div>

    <x-event-header :event="$event" :mobile="false" />

    <div class="hub">
        <a class="hub-card" href="{{ route('events.agenda.index', $event) }}">
            <div class="hub-card__row">
                <span class="hub-card__icon hub-card__icon--lime"><x-icon name="clock" :size="22" /></span>
                <span class="hub-card__text">
                    <span class="hub-card__title">Ablauf</span>
                    <span class="hub-card__sub">{{ $agendaSub }}</span>
                </span>
                <x-icon name="chevron-right" :stroke="2.5" />
            </div>
        </a>
        <a class="hub-card" href="{{ route('events.recipes.index', $event) }}">
            <div class="hub-card__row">
                <span class="hub-card__icon hub-card__icon--green"><x-icon name="file-text" :size="22" /></span>
                <span class="hub-card__text">
                    <span class="hub-card__title">Rezepte</span>
                    <span class="hub-card__sub">{{ $recipeSub }}</span>
                </span>
                <x-icon name="chevron-right" :stroke="2.5" />
            </div>
        </a>
        <a class="hub-card" href="{{ route('events.shopping.index', $event) }}">
            <div class="hub-card__row">
                <span class="hub-card__icon hub-card__icon--forest"><x-icon name="cart" :size="22" /></span>
                <span class="hub-card__text">
                    <span class="hub-card__title">Einkauf</span>
                    <span class="hub-card__sub">{{ $doneCount }} von {{ $totalCount }} erledigt</span>
                </span>
                <x-icon name="chevron-right" :stroke="2.5" />
            </div>
            <x-progress :done="$doneCount" :total="$totalCount" />
        </a>
    </div>

    <div class="event-actions">
        <a class="btn btn--ghost" href="{{ route('events.edit', $event) }}"><x-icon name="pencil" :size="18" />Event bearbeiten</a>
        <form method="POST" action="{{ route('events.destroy', $event) }}" data-confirm="Event «{{ $event->title }}» mit Ablauf, Rezepten und Einkaufsliste löschen?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger"><x-icon name="trash" :size="18" />Event löschen</button>
        </form>
    </div>
@endsection
