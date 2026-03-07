@props([
    'name',
    'label' => null,
    'labelText' => null,
    'value' => '',
    'required' => false,
    'error' => null,
    'validationText' => null,
    'validationState' => null,
    'placeholder' => '',
    'rows' => 4,
    'id' => null,
    'disabled' => false,
    'readonly' => false,
    'caption' => null,
    'help' => null,
])

@php
    $resolvedLabel = $labelText ?? $label;
    $resolvedError = $validationText ?? $error;
    $resolvedHelp = $caption ?? $help;
    $hasError = $resolvedError || $validationState === 'error';
    $textareaId = $id ?? $name;
@endphp

<div class="tw:form-control tw:w-full">
    @if($resolvedLabel)
        <label class="tw:label" for="{{ $textareaId }}">
            <span class="tw:label-text">{{ $resolvedLabel }}@if($required) <span class="tw:text-error">*</span>@endif</span>
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->merge(['class' => 'form-control tw:w-full' . ($hasError ? ' tw:textarea-error' : '')]) }}
    >{{ old($name, $value) }}</textarea>
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
