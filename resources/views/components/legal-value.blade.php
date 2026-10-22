@props(['legal', 'key'])
{{-- A configured legal detail, or a highlighted placeholder when it is still missing. --}}
@if ($legal->filled($key))<span>{{ $legal->get($key) }}</span>@else<mark class="placeholder">{{ $legal->get($key) }}</mark>@endif
