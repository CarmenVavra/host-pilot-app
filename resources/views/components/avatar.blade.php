@props(['person' => null])
@if ($person)
    <span {{ $attributes->merge(['class' => 'avatar avatar--c'.($person->color % 4)]) }} title="{{ $person->name }}">
        <span aria-hidden="true">{{ $person->initials }}</span>
        <span class="sr-only">Verantwortlich: {{ $person->name }}</span>
    </span>
@endif
