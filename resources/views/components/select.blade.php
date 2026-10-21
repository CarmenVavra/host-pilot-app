@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null])
@php
    $id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name));
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $error = $errors->first($errorKey);
    $current = old($errorKey, $value);
@endphp
<div class="field">
    <label class="field__label" for="{{ $id }}">{{ $label }}</label>
    <select {{ $attributes->merge(['class' => 'select'.($error ? ' is-invalid' : '')])->except('id') }}
            id="{{ $id }}" name="{{ $name }}"
            @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected((string) $current === (string) $option)>{{ $option }}</option>
        @endforeach
    </select>
    @if ($error)<span class="error" id="{{ $id }}-error">{{ $error }}</span>@endif
</div>
