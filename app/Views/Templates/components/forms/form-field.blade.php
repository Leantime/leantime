@props([
    'labelText' => null,
    'name' => null,
    'required' => false,
    'validationText' => null,
    'validationState' => null,
    'caption' => null,
    'inline' => false,
    'labelWidth' => null,
])

@php
    $inputId = $name;
@endphp

<div {{ $attributes->merge([
    'class' => 'form-group' . ($inline ? ' tw:flex tw:flex-row tw:items-start tw:gap-4' : ''),
]) }}>
    @if($labelText)
        <label class="control-label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" @if($inputId) for="{{ $inputId }}" @endif>
            {{ $labelText }}@if($required) <span class="tw:text-error">*</span>@endif
        </label>
    @endif
    <div class="{{ $inline ? 'tw:flex-1' : '' }}">
        {{ $slot }}
        @if($caption)
            <span class="tw:text-xs tw:opacity-60 tw:block tw:mt-1">{{ $caption }}</span>
        @endif
        @if($validationText)
            <span class="tw:text-xs tw:text-error tw:block tw:mt-1">{{ $validationText }}</span>
        @endif
    </div>
</div>
