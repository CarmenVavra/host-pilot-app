@extends('layouts.app')

@section('title', 'Deine Events')

@section('content')
    @php
        $kicker = $upcomingCount === 1 ? '1 Event geplant' : $upcomingCount.' Events geplant';
        $filters = ['anstehend' => 'Anstehend', 'vergangen' => 'Vergangen'];
    @endphp

    {{-- Phone: brand bar --}}
    <div class="brandbar only-mobile">
        <a class="brand" href="{{ route('events.index') }}"><span class="brand__logo" aria-hidden="true">HP</span>HostPilot</a>
        <a class="avatar-link" href="{{ route('profile.edit') }}" aria-label="Profil von {{ auth()->user()->name }}">
            <span aria-hidden="true">{{ auth()->user()->initials() }}</span>
        </a>
    </div>

    <header class="page-head d-head">
        <div>
            <div class="kicker">{{ $kicker }}</div>
            <h1 class="h1">Deine Events</h1>
        </div>
        <div class="d-head__actions only-desktop">
            <nav class="segmented" aria-label="Events filtern">
                @foreach ($filters as $key => $label)
                    <a class="segmented__item" href="{{ route('events.index', ['filter' => $key]) }}"
                       @if ($filter === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <a class="btn" href="{{ route('events.create') }}"><x-icon name="plus" :size="18" :stroke="2.5" />Neues Event</a>
        </div>
    </header>

    <nav class="segmented only-mobile" aria-label="Events filtern">
        @foreach ($filters as $key => $label)
            <a class="segmented__item" href="{{ route('events.index', ['filter' => $key]) }}"
               @if ($filter === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($events->isEmpty())
        <div class="empty">
            <div class="empty__title">{{ $filter === 'vergangen' ? 'Noch keine vergangenen Events' : 'Noch keine Events geplant' }}</div>
            <p>Lege dein erstes Event an – danach planst du Ablauf, Rezepte und Einkauf.</p>
            <a class="btn" href="{{ route('events.create') }}"><x-icon name="plus" :size="18" :stroke="2.5" />Event erstellen</a>
        </div>
    @else
        <ul class="event-list" role="list">
            @foreach ($events as $item)
                <li>
                    <a class="event-card" href="{{ route('events.show', $item) }}">
                        <x-cover :model="$item">
                            <span class="cover__badge">{{ $item->relative_label }}</span>
                        </x-cover>
                        <div class="event-card__body">
                            <h2 class="event-card__title">{{ $item->title }}</h2>
                            <div class="meta">
                                <span class="meta__item"><x-icon name="calendar" :size="16" />{{ $item->short_date }}</span>
                                <span class="meta__item"><x-icon name="users" :size="16" />{{ $item->guests }} Gäste</span>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
            <li class="only-desktop">
                <a class="new-tile" href="{{ route('events.create') }}">
                    <span class="new-tile__icon"><x-icon name="plus" :size="24" :stroke="2.5" /></span>
                    Neues Event erstellen
                </a>
            </li>
        </ul>
    @endif

    <x-fab :href="route('events.create')" label="Neues Event anlegen" />
@endsection
