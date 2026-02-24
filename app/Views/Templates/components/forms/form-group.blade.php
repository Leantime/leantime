@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'inline' => false,
    'labelWidth' => null,
])

@php
    $inputId = $name;
@endphp

<div {{ $attributes->merge([
    'class' => 'form-group' . ($inline ? ' tw:flex tw:flex-row tw:items-start tw:gap-4' : ''),
]) }}>
    @if($label)
        <label class="control-label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" @if($inputId) for="{{ $inputId }}" @endif>
            {{ $label }}@if($required) <span class="tw:text-error">*</span>@endif
        </label>
    @endif
    <div class="{{ $inline ? 'tw:flex-1' : '' }}">
        {{ $slot }}
        @if($help)
            <span class="tw:text-xs tw:opacity-60 tw:block tw:mt-1">{{ $help }}</span>
        @endif
        @if($error)
            <span class="tw:text-xs tw:text-error tw:block tw:mt-1">{{ $error }}</span>
        @endif
    </div>
</div>
