@props([
    'labelText' => null,
    'name' => null,
    'required' => false,
    'validationText' => null,
    'validationState' => null,
    'caption' => null,
    'labelPosition' => 'top',
    'inline' => false,
    'labelWidth' => null,
])

@php
    $inputId = $name;
    // Backward compat: inline=true maps to labelPosition='left'
    $resolvedPosition = $inline ? 'left' : $labelPosition;
    $isInline = $resolvedPosition === 'left';
    // Validation state color mapping
    $validationColor = match($validationState) {
        'error'   => 'tw:text-error',
        'warning' => 'tw:text-warning',
        'success' => 'tw:text-success',
        default   => null,
    };
@endphp

<div {{ $attributes->merge([
    'class' => 'form-group tw:mb-sm' . ($isInline ? ' tw:flex tw:flex-row tw:items-start tw:gap-4' : ''),
]) }}>
    @if($labelText)
        <label class="control-label{{ $isInline && $labelWidth ? ' ' . $labelWidth : '' }}"
               @if($inputId) for="{{ $inputId }}" @endif>
            @if(isset($leadingVisual))
                <span class="tw:inline-flex tw:items-center tw:mr-1">{{ $leadingVisual }}</span>
            @endif
            {{ $labelText }}@if($required) <span class="tw:text-error">*</span>@endif
        </label>
    @endif
    <div class="{{ $isInline ? 'tw:flex-1' : '' }}">
        <div class="tw:flex tw:items-center tw:gap-1">
            {{ $slot }}
            @if(isset($trailingVisual))
                <span class="tw:inline-flex tw:items-center tw:ml-1">{{ $trailingVisual }}</span>
            @endif
        </div>
        @if($caption)
            <span class="tw:text-xs tw:opacity-60 tw:block tw:mt-1">{{ $caption }}</span>
        @endif
        @if($validationText)
            <span class="tw:text-xs {{ $validationColor ?? 'tw:text-error' }} tw:block tw:mt-1">{{ $validationText }}</span>
        @endif
    </div>
</div>
