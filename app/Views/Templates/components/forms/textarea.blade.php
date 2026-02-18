@props([
    'name',
    'label' => null,
    'value' => '',
    'required' => false,
    'error' => null,
    'placeholder' => '',
    'rows' => 4,
    'id' => null,
    'disabled' => false,
    'readonly' => false,
])

@php
    $textareaId = $id ?? $name;
@endphp

<div class="tw:form-control tw:w-full">
    @if($label)
        <label class="tw:label" for="{{ $textareaId }}">
            <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
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
        {{ $attributes->merge(['class' => 'tw:textarea tw:textarea-bordered tw:w-full' . ($error ? ' tw:textarea-error' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    @if($error)
        <label class="tw:label">
            <span class="tw:label-text-alt tw:text-error">{{ $error }}</span>
        </label>
    @endif
</div>
