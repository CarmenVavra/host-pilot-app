@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="m-header only-mobile">
        <a class="m-header__back" href="{{ route('events.index') }}" aria-label="Zurück zur Übersicht">
            <x-icon name="chevron-left" :size="22" :stroke="2.5" />
        </a>
        <h1 class="m-header__title">Profil</h1>
    </div>

    <header class="d-head only-desktop">
        <div>
            <div class="kicker">Konto</div>
            <h1 class="h1">Profil</h1>
        </div>
    </header>

    <div class="form-page stack">
        <section class="card" aria-labelledby="profile-heading">
            <h2 id="profile-heading">Persönliche Angaben</h2>
            <form class="form" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <x-input name="name" label="Name" :value="$user->name" required autocomplete="name" maxlength="80" />
                <x-input name="email" label="E-Mail" type="email" :value="$user->email" required autocomplete="username" />
                <div class="form-actions"><button type="submit" class="btn">Speichern</button></div>
            </form>
        </section>

        <section class="card" aria-labelledby="password-heading">
            <h2 id="password-heading">Passwort ändern</h2>
            <form class="form" method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <x-input name="current_password" label="Aktuelles Passwort" type="password" bag="updatePassword" required autocomplete="current-password" />
                <x-input name="password" label="Neues Passwort" type="password" bag="updatePassword" required autocomplete="new-password" help="Mindestens 8 Zeichen." />
                <x-input name="password_confirmation" label="Neues Passwort bestätigen" type="password" bag="updatePassword" required autocomplete="new-password" />
                <div class="form-actions"><button type="submit" class="btn">Passwort ändern</button></div>
            </form>
        </section>

        <section class="card" aria-labelledby="session-heading">
            <h2 id="session-heading">Abmelden</h2>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn--ghost"><x-icon name="logout" :size="18" />Abmelden</button>
            </form>
        </section>
    </div>
@endsection
