@extends('layouts.app')

@section('title', 'Eintrag hinzufügen · '.$event->title)

@section('content')
    <div class="m-header only-mobile">
        <a class="m-header__back" href="{{ route('events.shopping.index', $event) }}" aria-label="Zurück zur Einkaufsliste">
            <x-icon name="chevron-left" :size="22" :stroke="2.5" />
        </a>
        <h1 class="m-header__title">Eintrag hinzufügen</h1>
    </div>

    <header class="d-head only-desktop">
        <div>
            <nav class="breadcrumb" aria-label="Brotkrumen">
                <a href="{{ route('events.index') }}">Events</a> /
                <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a> /
                <a href="{{ route('events.shopping.index', $event) }}">Einkauf</a>
            </nav>
            <h1 class="h1">Eintrag hinzufügen</h1>
        </div>
    </header>

    <div class="form-page">
        <form class="form" method="POST" action="{{ route('events.shopping.store', $event) }}">
            @csrf
            <x-input name="name" label="Artikel" required maxlength="80" placeholder="z. B. Servietten" autofocus />
            <div class="form-grid form-grid--2">
                <x-input name="amount" label="Menge (optional)" inputmode="decimal" placeholder="z. B. 2" />
                <x-select name="unit" label="Einheit" :options="\App\Enums\Unit::values()" value="Stk" placeholder="–" />
            </div>
            <x-select name="category" label="Kategorie" :options="\App\Enums\ShoppingCategory::values()" value="Sonstiges" required />
            <p class="field__help">Manuelle Einträge bleiben erhalten, wenn die Liste neu aus den Rezepten generiert wird.</p>
            <div class="form-actions">
                <button type="submit" class="btn">Hinzufügen</button>
                <a class="btn btn--ghost" href="{{ route('events.shopping.index', $event) }}">Abbrechen</a>
            </div>
        </form>
    </div>
@endsection
