@props(['href', 'label'])
@push('fab')
    <a class="fab only-mobile" href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}">
        <x-icon name="plus" :size="28" :stroke="2.5" />
    </a>
@endpush
