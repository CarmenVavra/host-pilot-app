{{-- Footer links required on every page (Impressum, Datenschutzerklärung) --}}
<footer class="site-footer">
    <nav aria-label="Rechtliches" class="site-footer__nav">
        <a href="{{ route('legal.imprint') }}" @if (request()->routeIs('legal.imprint')) aria-current="page" @endif>Impressum</a>
        <a href="{{ route('legal.privacy') }}" @if (request()->routeIs('legal.privacy')) aria-current="page" @endif>Datenschutz</a>
    </nav>
</footer>
