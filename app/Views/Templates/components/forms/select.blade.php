@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'required' => false,
    'error' => null,
    'placeholder' => null,
    'id' => null,
    'disabled' => false,
    'multiple' => false,
    'inline' => false,
    'labelWidth' => null,
    'help' => null,
    'inputSize' => null,
    'bare' => false,
])

@php
    $selectId = $id ?? $name;

    $sizeClass = match($inputSize) {
        'sm' => ' tw:select-sm',
        'lg' => ' tw:select-lg',
        default => '',
    };

    $selectClasses = $bare
        ? ''
        : 'tw:select tw:select-bordered tw:w-full' . $sizeClass
            . ($error ? ' tw:select-error' : '');
@endphp

@if($bare)
    <select
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $selectId }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes }}
    >
        @if($placeholder)
            <option value="" disabled {{ $selected === null ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endif
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ (string) $optValue === (string) old($name, $selected) ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
@else
    <div class="tw:form-control tw:w-full{{ $inline ? ' tw:flex tw:flex-row tw:items-center tw:gap-4' : '' }}">
        @if($label)
            <label class="tw:label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" for="{{ $selectId }}">
                <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
            </label>
        @endif
        <div class="{{ $inline ? 'tw:flex-1' : '' }}">
            <select
                name="{{ $name }}{{ $multiple ? '[]' : '' }}"
                id="{{ $selectId }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $multiple ? 'multiple' : '' }}
                {{ $attributes->merge(['class' => $selectClasses]) }}
            >
                @if($placeholder)
                    <option value="" disabled {{ $selected === null ? 'selected' : '' }}>{{ $placeholder }}</option>
                @endif
                @foreach($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ (string) $optValue === (string) old($name, $selected) ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
                {{ $slot }}
            </select>
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
