@props([
    'name',
    'label' => null,
    'labelText' => null,
    'checked' => false,
    'value' => '1',
    'toggle' => false,
    'id' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $resolvedLabel = $labelText ?? $label;
    $checkboxId = $id ?? $name;
    $inputClass = $toggle ? 'tw:toggle tw:toggle-primary' : 'tw:checkbox tw:checkbox-primary';
    $hasLabel = $resolvedLabel || !$slot->isEmpty();
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
    @if($resolvedLabel)
        <span class="tw:label-text">{{ $resolvedLabel }}</span>
    @elseif(!$slot->isEmpty())
        <span class="tw:label-text">{{ $slot }}</span>
    @endif
@if($hasLabel)
</label>
@endif
