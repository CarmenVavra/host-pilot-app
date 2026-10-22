<!DOCTYPE html>
<html lang="de-CH">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <a class="skip-link" href="#main">Zum Inhalt springen</a>

    <div class="app">
        {{-- Web app: sidebar navigation --}}
        <aside class="sidebar" aria-label="Hauptnavigation">
            <div class="sidebar__brand">
                <a class="brand" href="{{ route('events.index') }}">
                    <span class="brand__logo" aria-hidden="true">HP</span>HostPilot
                </a>
            </div>
            <div class="sidebar__label" id="sidebar-events">Events</div>
            <nav aria-labelledby="sidebar-events" class="sidebar__nav">
                <a class="sidebar__link" href="{{ route('events.index') }}"
                   @if (request()->routeIs('events.index')) aria-current="page" @endif>Alle Events</a>
                @foreach ($navEvents as $navEvent)
                    <a class="sidebar__link {{ $navEvent->isPast() ? 'is-past' : '' }}"
                       href="{{ route('events.show', $navEvent) }}"
                       @if (isset($event) && $event->exists && $event->id === $navEvent->id) aria-current="page" @endif>{{ $navEvent->title }}</a>
                @endforeach
            </nav>
            <a class="sidebar__profile" href="{{ route('profile.edit') }}">
                <span class="avatar avatar--lg avatar--c3" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                <span class="sidebar__profile-text">
                    {{ auth()->user()->name }}<br>
                    <span class="sidebar__profile-sub">Profil &amp; Passwort</span>
                </span>
            </a>
        </aside>

        <main id="main" class="main" tabindex="-1">
            <div class="statusbar-space only-mobile"></div>
            @yield('content')
            <x-legal-links />
        </main>
    </div>

    @stack('fab')
    <x-toast />
    <x-confirm-dialog />
</body>
</html>
