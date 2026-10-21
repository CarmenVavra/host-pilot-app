@extends('layouts.guest')

@section('title', 'Neues Passwort')

@section('content')
    <h1 class="auth__title">Neues Passwort festlegen</h1>

    <form class="form" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-input name="email" label="E-Mail" type="email" :value="$email" required autocomplete="username" />
        <x-input name="password" label="Neues Passwort" type="password" required autofocus autocomplete="new-password" help="Mindestens 8 Zeichen." />
        <x-input name="password_confirmation" label="Passwort bestätigen" type="password" required autocomplete="new-password" />
        <button type="submit" class="btn btn--block">Passwort speichern</button>
    </form>
@endsection
