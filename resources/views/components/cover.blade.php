@props(['model'])
{{-- User photo if available, otherwise the striped Modernist placeholder. --}}
<div {{ $attributes->merge(['class' => 'cover'.($model->cover_url ? '' : ' cover--s'.($model->cover_style % 8))]) }}>
    @if ($model->cover_url)
        <img src="{{ $model->cover_url }}" alt="" loading="lazy">
    @endif
    {{ $slot }}
</div>
