@props([
    'name',
    'label' => null,
    'value' => '',
    'required' => false,
    'error' => null,
    'placeholder' => '',
    'rows' => 4,
])

<div class="tw:form-control tw:w-full">
    @if($label)
        <label class="tw:label" for="{{ $name }}">
            <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'tw:textarea tw:textarea-bordered tw:w-full' . ($error ? ' tw:textarea-error' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    @if($error)
        <label class="tw:label">
            <span class="tw:label-text-alt tw:text-error">{{ $error }}</span>
        </label>
    @endif
</div>
