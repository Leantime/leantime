@props([
    'name',
    'value' => '',
    'id' => null,
    'class' => '',
    'readonly' => false,
    'disabled' => false,
])

@php
    $inputId = $id ?? $name;
    $hasValue = $value !== '' && $value !== null && $value !== '0000-00-00';
@endphp

<span class="date-inline-picker">
    {{-- Hidden input for flatpickr & form submission --}}
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $value }}"
        autocomplete="off"
        {{ $readonly ? 'readonly' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="dates detail-date-value {{ $class }}"
        style="{{ $hasValue ? '' : 'display:none' }}"
    />
    {{-- Calendar icon trigger — visible when no value --}}
    @if (! $readonly && ! $disabled)
        <button type="button" class="date-inline-trigger" style="{{ $hasValue ? 'display:none' : '' }}">
            <i class="fa-regular fa-calendar-plus"></i>
        </button>
    @endif
</span>
