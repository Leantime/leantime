@props([
    'name',
    'label' => null,
    'labelText' => null,
    'checked' => false,
    'value' => '1',
    'toggle' => false,
    'scale' => null,             // xs|s|m|l
    'id' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $resolvedLabel = $labelText ?? $label;
    $checkboxId = $id ?? $name;

    $sizeClass = match($scale) {
        'xs' => $toggle ? ' tw:toggle-xs' : ' tw:checkbox-xs',
        's'  => $toggle ? ' tw:toggle-sm' : ' tw:checkbox-sm',
        'l'  => $toggle ? ' tw:toggle-lg' : ' tw:checkbox-lg',
        default => ' tw:checkbox-sm',
    };
    $inputClass = $toggle
        ? 'tw:toggle tw:toggle-primary' . $sizeClass
        : 'tw:checkbox tw:checkbox-primary' . $sizeClass;
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
