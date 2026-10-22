@extends('layouts.page')

@section('title', 'Impressum')

@section('content')
    @include('legal.partials.notice')

    <h1 class="legal__title">Impressum</h1>

    <p class="legal__basis">
        @switch($legal->country())
            @case('DE')
                Angaben gemäß § 5 Digitale-Dienste-Gesetz (DDG)
                @break
            @case('CH')
                Anbieterkennzeichnung
                @break
            @default
                Angaben gemäß § 5 E-Commerce-Gesetz (ECG) und Offenlegung gemäß § 25 Mediengesetz
        @endswitch
    </p>

    <section aria-labelledby="provider">
        <h2 id="provider">Diensteanbieter / Medieninhaber</h2>
        <address class="legal__address">
            <x-legal-value :legal="$legal" key="name" /><br>
            <x-legal-value :legal="$legal" key="street" /><br>
            <x-legal-value :legal="$legal" key="city" /><br>
            {{ $legal->countryName() }}
        </address>
    </section>

    <section aria-labelledby="contact">
        <h2 id="contact">Kontakt</h2>
        <p>
            E-Mail:
            @if ($legal->filled('email'))
                <a href="mailto:{{ $legal->get('email') }}">{{ $legal->get('email') }}</a>
            @else
                <x-legal-value :legal="$legal" key="email" />
            @endif
            @if ($legal->filled('phone'))
                <br>Telefon: {{ $legal->get('phone') }}
            @endif
        </p>
    </section>

    @if ($legal->filled('vat_id'))
        <section aria-labelledby="vat">
            <h2 id="vat">Umsatzsteuer-Identifikationsnummer</h2>
            <p>{{ $legal->get('vat_id') }}</p>
        </section>
    @endif

    @if ($legal->country() === 'AT')
        <section aria-labelledby="direction">
            <h2 id="direction">Grundlegende Richtung</h2>
            <p>HostPilot ist eine Web-App zur Planung von Events: Ablauf, Rezepte, Einkaufsliste und Gesprächsthemen.</p>
        </section>
    @endif

    <section aria-labelledby="content-liability">
        <h2 id="content-liability">Haftung für Inhalte</h2>
        <p>
            Die Inhalte (Events, Rezepte, Notizen, Bilder) werden von den angemeldeten Nutzerinnen und Nutzern selbst
            eingegeben. Sie sind für ihre Inhalte selbst verantwortlich. Bei Hinweisen auf Rechtsverletzungen entfernen
            wir die betroffenen Inhalte umgehend.
        </p>
    </section>
@endsection
