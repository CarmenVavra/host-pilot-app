@props(['event', 'active' => null, 'mobile' => true])
@php
    $tabs = [
        'ablauf' => ['Ablauf', route('events.agenda.index', $event)],
        'rezepte' => ['Rezepte', route('events.recipes.index', $event)],
        'einkauf' => ['Einkauf', route('events.shopping.index', $event)],
        'themen' => ['Themen', route('events.topics.index', $event)],
    ];
@endphp

{{-- Phone: back + event name + segmented control --}}
@if ($mobile)
<div class="only-mobile">
    <div class="m-header">
        <a class="m-header__back" href="{{ route('events.show', $event) }}" aria-label="Zurück zum Event">
            <x-icon name="chevron-left" :size="22" :stroke="2.5" />
        </a>
        <h1 class="m-header__title">{{ $event->title }}</h1>
    </div>
    <nav class="segmented segmented--tabs" aria-label="Bereiche des Events">
        @foreach ($tabs as $key => [$label, $url])
            <a class="segmented__item" href="{{ $url }}" @if ($active === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</div>
@endif

{{-- Web: breadcrumb, title, meta and tabs --}}
<header class="e-head only-desktop">
    <nav class="breadcrumb" aria-label="Brotkrumen">
        <a href="{{ route('events.index') }}">Events</a> /
        <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a>
    </nav>
    <div class="e-head__row">
        <h1 class="e-head__title">{{ $event->title }}</h1>
        <div class="meta">
            <span>{{ $event->date_time_label }}</span>
            <span>{{ $event->guests }} Gäste</span>
            @if ($event->location)<span>{{ $event->location }}</span>@endif
        </div>
    </div>
    <nav class="tabs" aria-label="Bereiche des Events">
        @foreach ($tabs as $key => [$label, $url])
            <a class="tabs__item" href="{{ $url }}" @if ($active === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
        <span class="tabs__spacer"></span>
        {{ $actions ?? '' }}
    </nav>
</header>
