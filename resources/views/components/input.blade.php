@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'bag' => 'default'])
@php
    $id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name));
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $error = $errors->getBag($bag)->first($errorKey);
    $describedBy = trim(($help ? $id.'-help ' : '').($error ? $id.'-error' : ''));
@endphp
<div class="field">
    <label class="field__label" for="{{ $id }}">{{ $label }}</label>
    <input {{ $attributes->merge(['class' => 'input'.($error ? ' is-invalid' : '')])->except('id') }}
           id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password' && $type !== 'file') value="{{ old($errorKey, $value) }}" @endif
           @if ($error) aria-invalid="true" @endif
           @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
    @if ($help)<span class="field__help" id="{{ $id }}-help">{{ $help }}</span>@endif
    @if ($error)<span class="error" id="{{ $id }}-error">{{ $error }}</span>@endif
</div>
