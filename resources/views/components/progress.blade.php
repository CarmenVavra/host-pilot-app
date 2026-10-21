@props(['done', 'total', 'label' => 'Einkauf erledigt'])
@php($percent = $total > 0 ? round($done / $total * 100) : 0)
<div {{ $attributes->merge(['class' => 'progress']) }} role="progressbar" aria-label="{{ $label }}"
     aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $done }}" data-shop-progressbar>
    <div class="progress__bar" style="width: {{ $percent }}%" data-shop-progress></div>
</div>
