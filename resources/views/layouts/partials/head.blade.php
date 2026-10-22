<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#f5eec2">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="HostPilot">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
<link rel="icon" href="{{ asset('app-icons/icon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('app-icons/apple-touch-icon.png') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
