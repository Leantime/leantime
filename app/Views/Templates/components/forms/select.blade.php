@props([
    'name',
    'label' => null,
    'labelText' => null,
    'options' => [],
    'items' => null,
    'selected' => null,
    'required' => false,
    'error' => null,
    'validationText' => null,
    'validationState' => null,
    'placeholder' => null,
    'id' => null,
    'disabled' => false,
    'multiple' => false,
    'inline' => false,
    'labelWidth' => null,
    'help' => null,
    'caption' => null,
    'inputSize' => null,
    'scale' => null,
    'bare' => false,
])

@php
    // Naming-doc aliases: prefer new names, fall back to old
    $resolvedLabel = $labelText ?? $label;
    $resolvedOptions = $items ?? $options;
    $resolvedError = $validationText ?? $error;
    $resolvedHelp = $caption ?? $help;
    $resolvedScale = $scale ?? $inputSize;
    $hasError = $resolvedError || $validationState === 'error';

    $selectId = $id ?? $name;

    $sizeClass = match($resolvedScale) {
        's', 'sm' => ' tw:select-sm',
        'l', 'lg' => ' tw:select-lg',
        default => '',
    };

    $selectClasses = $bare
        ? ''
        : 'form-control tw:w-full' . $sizeClass
            . ($hasError ? ' tw:select-error' : '');
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
        @foreach($resolvedOptions as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ (string) $optValue === (string) old($name, $selected) ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
@else
    <div class="tw:form-control tw:w-full{{ $inline ? ' tw:flex tw:flex-row tw:items-center tw:gap-4' : '' }}">
        @if($resolvedLabel)
            <label class="tw:label{{ $inline && $labelWidth ? ' ' . $labelWidth : '' }}" for="{{ $selectId }}">
                <span class="tw:label-text">{{ $resolvedLabel }}@if($required) <span class="tw:text-error">*</span>@endif</span>
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
                @foreach($resolvedOptions as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ (string) $optValue === (string) old($name, $selected) ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
                {{ $slot }}
            </select>
            @if($resolvedHelp)
                <label class="tw:label">
                    <span class="tw:label-text-alt">{{ $resolvedHelp }}</span>
                </label>
            @endif
            @if($resolvedError)
                <label class="tw:label">
                    <span class="tw:label-text-alt tw:text-error">{{ $resolvedError }}</span>
                </label>
            @endif
        </div>
    </div>
@endif
