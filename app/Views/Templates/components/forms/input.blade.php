@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
    'error' => null,
    'placeholder' => '',
    'id' => null,
    'disabled' => false,
    'readonly' => false,
    'inline' => false,
    'labelWidth' => null,
    'help' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'autocomplete' => null,
    'inputSize' => null,
    'bare' => false,
])

@php
    $inputId = $id ?? $name;

    $sizeClass = match($inputSize) {
        'sm' => ' tw:input-sm',
        'lg' => ' tw:input-lg',
        default => '',
    };

    $inputClasses = $bare
        ? ''
        : 'form-control tw:w-full' . $sizeClass
            . ($error ? ' tw:input-error' : '')
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
        @if($label)
            <label class="tw:label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" for="{{ $inputId }}">
                <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
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
            @if($help)
                <label class="tw:label">
                    <span class="tw:label-text-alt">{{ $help }}</span>
                </label>
            @endif
            @if($error)
                <label class="tw:label">
                    <span class="tw:label-text-alt tw:text-error">{{ $error }}</span>
                </label>
            @endif
        </div>
    </div>
@endif
