@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
    'error' => null,
    'placeholder' => '',
])

<div class="tw:form-control tw:w-full">
    @if($label)
        <label class="tw:label" for="{{ $name }}">
            <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'tw:input tw:input-bordered tw:w-full' . ($error ? ' tw:input-error' : '')]) }}
    />
    @if($error)
        <label class="tw:label">
            <span class="tw:label-text-alt tw:text-error">{{ $error }}</span>
        </label>
    @endif
</div>
