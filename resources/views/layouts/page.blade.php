<!DOCTYPE html>
<html lang="de-CH">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <a class="skip-link" href="#main">Zum Inhalt springen</a>

    {{-- Standalone page for content that must be reachable with and without login --}}
    <header class="page-top">
        <a class="brand" href="{{ auth()->check() ? route('events.index') : route('login') }}">
            <span class="brand__logo" aria-hidden="true">HP</span>HostPilot
        </a>
        <a class="page-top__link" href="{{ auth()->check() ? route('events.index') : route('login') }}">
            {{ auth()->check() ? 'Zurück zur App' : 'Zur Anmeldung' }}
        </a>
    </header>

    <main id="main" class="legal" tabindex="-1">
        @yield('content')
    </main>

    <x-legal-links />
</body>
</html>
