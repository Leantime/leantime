@props([
    'name',
    'labelText' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
    'validationText' => null,
    'validationState' => null,
    'placeholder' => '',
    'id' => null,
    'disabled' => false,
    'readonly' => false,
    'inline' => false,
    'labelWidth' => null,
    'caption' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'autocomplete' => null,
    'scale' => null,
    'bare' => false,
])

@php
    $inputId = $id ?? $name;

    $sizeClass = match($scale) {
        's', 'sm' => ' tw:input-sm',
        'l', 'lg' => ' tw:input-lg',
        default => '',
    };

    $hasError = $validationText || $validationState === 'error';

    $inputClasses = $bare
        ? ''
        : 'form-control tw:w-full' . $sizeClass
            . ($hasError ? ' tw:input-error' : '')
            . ($disabled ? ' tw:input-disabled' : '');

    $extraAttrs = [];
    if ($min !== null) $extraAttrs['min'] = $min;
    if ($max !== null) $extraAttrs['max'] = $max;
    if ($step !== null) $extraAttrs['step'] = $step;
    if ($autocomplete !== null) $extraAttrs['autocomplete'] = $autocomplete;
@endphp

@if($type === 'hidden')
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        {{ $attributes }}
    />
@elseif($bare)
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->merge($extraAttrs) }}
    />
@else
    <div class="tw:form-control tw:w-full{{ $inline ? ' tw:flex tw:flex-row tw:items-center tw:gap-4' : '' }}">
        @if($labelText)
            <label class="tw:label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" for="{{ $inputId }}">
                <span class="tw:label-text">{{ $labelText }}@if($required) <span class="tw:text-error">*</span>@endif</span>
            </label>
        @endif
        <div class="{{ $inline ? 'tw:flex-1' : '' }}">
            @if(isset($addon))
                <div class="tw:join tw:w-full">
                    <input
                        type="{{ $type }}"
                        name="{{ $name }}"
                        id="{{ $inputId }}"
                        value="{{ old($name, $value) }}"
                        placeholder="{{ $placeholder }}"
                        {{ $required ? 'required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}
                        {{ $readonly ? 'readonly' : '' }}
                        {{ $attributes->merge(array_merge(['class' => $inputClasses . ' tw:join-item'], $extraAttrs)) }}
                    />
                    <div class="tw:join-item">
                        {{ $addon }}
                    </div>
                </div>
            @else
                <input
                    type="{{ $type }}"
                    name="{{ $name }}"
                    id="{{ $inputId }}"
                    value="{{ old($name, $value) }}"
                    placeholder="{{ $placeholder }}"
                    {{ $required ? 'required' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    {{ $readonly ? 'readonly' : '' }}
                    {{ $attributes->merge(array_merge(['class' => $inputClasses], $extraAttrs)) }}
                />
            @endif
            @if($caption)
                <label class="tw:label">
                    <span class="tw:label-text-alt">{{ $caption }}</span>
                </label>
            @endif
            @if($validationText)
                <label class="tw:label">
                    <span class="tw:label-text-alt tw:text-error">{{ $validationText }}</span>
                </label>
            @endif
        </div>
    </div>
@endif
