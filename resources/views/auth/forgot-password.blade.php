@extends('layouts.guest')

@section('title', 'Passwort vergessen')

@section('content')
    <div class="kicker">Kein Problem</div>
    <h1 class="auth__title">Passwort vergessen</h1>
    <p class="lead">Gib deine E-Mail-Adresse ein. Wir senden dir einen Link, mit dem du ein neues Passwort festlegen kannst.</p>

    <form class="form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-input name="email" label="E-Mail" type="email" required autofocus autocomplete="username" />
        <button type="submit" class="btn btn--block">Link senden</button>
        <div class="auth__links">
            <a href="{{ route('login') }}">Zurück zur Anmeldung</a>
        </div>
    </form>
@endsection
