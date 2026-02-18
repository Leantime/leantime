@props([
    'name',
    'label' => null,
    'required' => false,
    'accept' => null,
    'error' => null,
    'bare' => false,
])

@if($bare)
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $accept ? "accept=$accept" : '' }}
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    />
@else
    <div class="tw:form-control tw:w-full">
        @if($label)
            <label class="tw:label" for="{{ $name }}">
                <span class="tw:label-text">{{ $label }}@if($required) <span class="tw:text-error">*</span>@endif</span>
            </label>
        @endif
        <input
            type="file"
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $accept ? "accept=$accept" : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'tw:file-input tw:file-input-bordered tw:w-full' . ($error ? ' tw:file-input-error' : '')]) }}
        />
        @if($error)
            <label class="tw:label">
                <span class="tw:label-text-alt tw:text-error">{{ $error }}</span>
            </label>
        @endif
    </div>
@endif
