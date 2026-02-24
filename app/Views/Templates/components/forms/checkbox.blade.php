@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => '1',
    'toggle' => false,
    'id' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $checkboxId = $id ?? $name;
    $inputClass = $toggle ? 'tw:toggle tw:toggle-primary' : 'tw:checkbox tw:checkbox-primary';
    $hasLabel = $label || !$slot->isEmpty();
@endphp

@if($hasLabel)
<label class="tw:label tw:cursor-pointer tw:justify-start tw:gap-3">
@endif
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $checkboxId }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $inputClass]) }}
    />
    @if($label)
        <span class="tw:label-text">{{ $label }}</span>
    @elseif(!$slot->isEmpty())
        <span class="tw:label-text">{{ $slot }}</span>
    @endif
@if($hasLabel)
</label>
@endif
