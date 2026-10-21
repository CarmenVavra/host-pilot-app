<!DOCTYPE html>
<html lang="de-CH">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <main class="auth" id="main">
        <div class="auth__card">
            <a class="brand" href="{{ route('login') }}">
                <span class="brand__logo" aria-hidden="true">H</span>HostPilot
            </a>
            @yield('content')
        </div>
    </main>
    <x-toast />
</body>
</html>
