@extends('layouts.guest')

@section('title', 'Anmelden')

@section('content')
    <div class="kicker">Willkommen zurück</div>
    <h1 class="auth__title">Anmelden</h1>

    <form class="form" method="POST" action="{{ route('login') }}">
        @csrf
        <x-input name="email" label="E-Mail" type="email" required autofocus autocomplete="username" />
        <x-input name="password" label="Passwort" type="password" required autocomplete="current-password" />
        <label class="checkbox"><input type="checkbox" name="remember" value="1"> Angemeldet bleiben</label>
        <button type="submit" class="btn btn--block">Anmelden</button>
        <div class="auth__links">
            <a href="{{ route('password.request') }}">Passwort vergessen?</a>
            <a href="{{ route('register') }}">Konto erstellen</a>
        </div>
    </form>
@endsection
