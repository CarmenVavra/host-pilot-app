@extends('layouts.app')

@section('title', 'Ablauf · '.$event->title)

@section('content')
    <x-event-header :event="$event" active="ablauf" />

    @php
        $items = $event->agendaItems;
        $selectedPerson = old('person_id', $editing ? $editing->person_id : $event->people->first()?->id);
        // The green marker: the item running right now on the event day, otherwise the event start.
        $highlightLabel = $event->date->isToday() ? 'Läuft gerade' : 'Beginn des Events'.($event->time ? ' ('.$event->time.')' : '');
    @endphp

    <div class="split">
        {{-- Timeline --}}
        <section class="split__main {{ $formOpen ? 'only-desktop' : '' }}" aria-labelledby="agenda-title">
            <div class="section-head">
                <h2 id="agenda-title">{{ $event->medium_date }}</h2>
                @if ($items->isNotEmpty())<span class="hint" aria-hidden="true">Wisch = bearbeiten</span>@endif
            </div>
            <h2 class="sr-only only-desktop">Ablauf am {{ $event->medium_date }}</h2>

            @if ($items->isEmpty())
                <div class="empty">
                    <div class="empty__title">Noch kein Ablauf</div>
                    <p>Plane, wer wann was erledigt – vom Einkauf bis zum Dessert.</p>
                    <a class="btn only-mobile" href="{{ route('events.agenda.create', $event) }}"><x-icon name="plus" :size="18" :stroke="2.5" />Programmpunkt hinzufügen</a>
                </div>
            @else
                <ol class="timeline">
                    @foreach ($items as $item)
                        <li class="timeline__item {{ $item->id === $highlightedId ? 'is-current' : '' }}">
                            <div class="timeline__time">
                                <time>{{ $item->time }}</time>
                                @if ($item->id === $highlightedId)<span class="sr-only">({{ $highlightLabel }})</span>@endif
                            </div>
                            <div class="timeline__rail" aria-hidden="true"><span class="timeline__dot"></span><span class="timeline__line"></span></div>
                            <div class="timeline__body">
                                <div class="swipe" data-swipe>
                                    {{-- Phone: revealed by swiping left or long-press --}}
                                    <div class="swipe__actions">
                                        <a class="swipe__action swipe__action--edit" href="{{ route('events.agenda.edit', [$event, $item]) }}">
                                            <x-icon name="pencil" :size="18" />Ändern<span class="sr-only">: {{ $item->title }}</span>
                                        </a>
                                        <form class="swipe__action-form" method="POST" action="{{ route('events.agenda.destroy', [$event, $item]) }}"
                                              data-confirm="Programmpunkt «{{ $item->title }}» löschen?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="swipe__action swipe__action--delete">
                                                <x-icon name="trash" :size="18" />Löschen<span class="sr-only">: {{ $item->title }}</span>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="agenda-card">
                                        <div class="agenda-card__text">
                                            <div class="agenda-card__title">{{ $item->title }}</div>
                                            @if ($item->note)<div class="agenda-card__note">{{ $item->note }}</div>@endif
                                        </div>
                                        {{-- Fixed-width column so the initials line up on every card --}}
                                        <div class="agenda-card__who">
                                            @if ($item->person)
                                                <x-avatar :person="$item->person" />
                                                <span class="agenda-card__name only-desktop" aria-hidden="true">{{ $item->person->name }}</span>
                                            @endif
                                        </div>
                                        {{-- Web: tools always visible --}}
                                        <div class="agenda-card__tools">
                                            <a class="icon-btn" href="{{ route('events.agenda.edit', [$event, $item]) }}" aria-label="«{{ $item->title }}» ändern" title="Ändern">
                                                <x-icon name="pencil" :size="18" />
                                            </a>
                                            <form method="POST" action="{{ route('events.agenda.destroy', [$event, $item]) }}" data-confirm="Programmpunkt «{{ $item->title }}» löschen?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-btn icon-btn--danger" aria-label="«{{ $item->title }}» löschen" title="Löschen">
                                                    <x-icon name="trash" :size="18" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
                @if ($highlightedId)
                    <p class="timeline-legend">
                        <span class="timeline-legend__dot" aria-hidden="true"></span>
                        Grün markiert: {{ $highlightLabel }}
                    </p>
                @endif
            @endif
        </section>

        {{-- New / edit panel: side panel on the web, own screen on the phone --}}
        <aside class="split__side split__side--300 {{ $formOpen ? '' : 'only-desktop' }}" aria-labelledby="panel-title">
            <div class="panel">
                <h2 id="panel-title">{{ $editing ? 'Programmpunkt ändern' : 'Neuer Programmpunkt' }}</h2>

                <form class="form" method="POST"
                      action="{{ $editing ? route('events.agenda.update', [$event, $editing]) : route('events.agenda.store', $event) }}">
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif

                    <x-input name="time" label="Uhrzeit" type="time" :value="$editing?->time ?? $defaultTime" required />
                    <x-input name="title" label="Titel" :value="$editing?->title" required maxlength="120" placeholder="z. B. Brunch aufräumen" />
                    <x-input name="note" label="Notiz (optional)" :value="$editing?->note" maxlength="255" placeholder="z. B. Ofen auf 180 °C" />

                    <fieldset class="picker">
                        <legend>Verantwortlich</legend>
                        @foreach ($event->people as $person)
                            <label class="picker__option" title="{{ $person->name }}">
                                <input type="radio" name="person_id" value="{{ $person->id }}" @checked((string) $selectedPerson === (string) $person->id)>
                                <span class="picker__face"><span aria-hidden="true">{{ $person->initials }}</span><span class="sr-only">{{ $person->name }}</span></span>
                            </label>
                        @endforeach
                        <label class="picker__option" title="Niemand">
                            <input type="radio" name="person_id" value="" @checked(blank($selectedPerson))>
                            <span class="picker__face"><span aria-hidden="true">–</span><span class="sr-only">Niemand</span></span>
                        </label>
                        <a class="picker__face picker__add" href="#new-person" data-open-person aria-label="Person hinzufügen" title="Person hinzufügen">+</a>
                        @error('person_id')<span class="error">{{ $message }}</span>@enderror
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" class="btn btn--block">{{ $editing ? 'Speichern' : 'Hinzufügen' }}</button>
                        @if ($formOpen)
                            <a class="btn btn--ghost btn--block" href="{{ route('events.agenda.index', $event) }}">Abbrechen</a>
                        @endif
                    </div>
                </form>

                <details class="add-person" id="new-person" @if ($errors->has('name')) open @endif>
                    <summary>+ Person hinzufügen</summary>
                    <form class="inline-form" method="POST" action="{{ route('events.people.store', $event) }}">
                        @csrf
                        <label class="sr-only" for="person-name">Name der Person</label>
                        <input class="input" id="person-name" name="name" required maxlength="60" placeholder="Name" value="{{ old('name') }}">
                        <button type="submit" class="btn">OK</button>
                    </form>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </details>

                @if ($items->isNotEmpty())
                    <div class="panel__foot only-desktop">
                        {{ $items->count() === 1 ? '1 Punkt' : $items->count().' Punkte' }} · {{ $items->first()->time }} – {{ $items->last()->time }}<br>
                        <span class="muted">Sortiert nach Uhrzeit. Auf dem Handy: nach links wischen zum Bearbeiten.</span>
                    </div>
                @endif
            </div>
        </aside>
    </div>

    @unless ($formOpen)
        <x-fab :href="route('events.agenda.create', $event)" label="Programmpunkt hinzufügen" />
    @endunless
@endsection
