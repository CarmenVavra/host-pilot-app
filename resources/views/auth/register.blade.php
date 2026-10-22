@extends('layouts.guest')

@section('title', 'Registrieren')

@section('content')
    <div class="kicker">Events planen leicht gemacht</div>
    <h1 class="auth__title">Konto erstellen</h1>

    <form class="form" method="POST" action="{{ route('register') }}">
        @csrf
        <x-input name="name" label="Name" required autofocus autocomplete="name" maxlength="80" />
        <x-input name="email" label="E-Mail" type="email" required autocomplete="username" />
        <x-input name="password" label="Passwort" type="password" required autocomplete="new-password" help="Mindestens 8 Zeichen." />
        <x-input name="password_confirmation" label="Passwort bestätigen" type="password" required autocomplete="new-password" />
        <p class="field__help">
            Mit der Registrierung bestätigst du, dass du die
            <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">Datenschutzerklärung<span class="sr-only"> (öffnet in neuem Tab)</span></a>
            zur Kenntnis genommen hast.
        </p>
        <button type="submit" class="btn btn--block">Registrieren</button>
        <div class="auth__links">
            <a href="{{ route('login') }}">Bereits registriert? Anmelden</a>
        </div>
    </form>
@endsection
