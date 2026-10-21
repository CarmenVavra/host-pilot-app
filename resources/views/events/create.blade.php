@extends('layouts.app')

@section('title', 'Neues Event')

@section('content')
    <div class="m-header only-mobile">
        <a class="m-header__back" href="{{ route('events.index') }}" aria-label="Zurück zur Übersicht">
            <x-icon name="chevron-left" :size="22" :stroke="2.5" />
        </a>
        <h1 class="m-header__title">Neues Event</h1>
    </div>

    <header class="d-head only-desktop">
        <div>
            <nav class="breadcrumb" aria-label="Brotkrumen"><a href="{{ route('events.index') }}">Events</a> / Neu</nav>
            <h1 class="h1">Neues Event</h1>
        </div>
    </header>

    <div class="form-page">
        @include('events.partials.form', ['action' => route('events.store'), 'method' => 'POST', 'submit' => 'Event erstellen'])
    </div>
@endsection
