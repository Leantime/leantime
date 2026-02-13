@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'required' => false,
    'error' => null,
    'placeholder' => null,
])

<div class="tw:form-control tw:w-full">
    @if($label)
        <label class="tw:label" for="{{ $name }}">
            <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
        </label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'tw:select tw:select-bordered tw:w-full' . ($error ? ' tw:select-error' : '')]) }}
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
    @if($error)
        <label class="tw:label">
            <span class="tw:label-text-alt tw:text-error">{{ $error }}</span>
        </label>
    @endif
</div>
