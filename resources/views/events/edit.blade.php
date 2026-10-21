@extends('layouts.app')

@section('title', 'Event bearbeiten')

@section('content')
    <div class="m-header only-mobile">
        <a class="m-header__back" href="{{ route('events.show', $event) }}" aria-label="Zurück zum Event">
            <x-icon name="chevron-left" :size="22" :stroke="2.5" />
        </a>
        <h1 class="m-header__title">Event bearbeiten</h1>
    </div>

    <header class="d-head only-desktop">
        <div>
            <nav class="breadcrumb" aria-label="Brotkrumen">
                <a href="{{ route('events.index') }}">Events</a> /
                <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a> / Bearbeiten
            </nav>
            <h1 class="h1">Event bearbeiten</h1>
        </div>
    </header>

    <div class="form-page">
        @include('events.partials.form', ['action' => route('events.update', $event), 'method' => 'PUT', 'submit' => 'Speichern'])
    </div>
@endsection
