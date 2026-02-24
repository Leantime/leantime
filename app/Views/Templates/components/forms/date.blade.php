@props([
    'name',
    'label' => null,
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
    'withTime' => false,
    'timeName' => null,
    'timeValue' => '',
])

@php
    $inputId = $id ?? $name;
    $resolvedTimeName = $timeName ?? $name . '_time';

    $inputClasses = 'form-control tw:w-full'
        . ($error ? ' tw:input-error' : '');
@endphp

<div class="tw:form-control tw:w-full{{ $inline ? ' tw:flex tw:flex-row tw:items-center tw:gap-4' : '' }}">
    @if($label)
        <label class="tw:label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" for="{{ $inputId }}">
            <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
        </label>
    @endif
    <div class="{{ $inline ? 'tw:flex-1' : '' }}{{ $withTime ? ' tw:flex tw:gap-2' : '' }}">
        <div class="date-picker-form-control{{ $withTime ? ' tw:flex-1' : '' }}">
            <input
                type="text"
                name="{{ $name }}"
                id="{{ $inputId }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                {{ $attributes->merge(['class' => $inputClasses . ' dates']) }}
            />
        </div>
        @if($withTime)
            <input
                type="time"
                name="{{ $resolvedTimeName }}"
                id="{{ $inputId }}_time"
                value="{{ old($resolvedTimeName, $timeValue) }}"
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                class="form-control"
            />
        @endif
    </div>
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
