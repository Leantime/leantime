@props([
    'name',
    'label' => null,
    'labelText' => null,
    'value',
    'checked' => false,
    'scale' => null,             // xs|s|m|l
    'id' => null,
    'disabled' => false,
])

@php
    $resolvedLabel = $labelText ?? $label;
    $radioId = $id ?? null;

    $sizeClass = match($scale) {
        'xs' => ' tw:radio-xs',
        's'  => ' tw:radio-sm',
        'l'  => ' tw:radio-lg',
        default => ' tw:radio-sm',
    };
    $hasLabel = $resolvedLabel || !$slot->isEmpty();
    $hxVals = $attributes->get('hx-vals');
@endphp

@if($hasLabel)
<label class="tw:label tw:cursor-pointer tw:justify-start tw:gap-3">
@endif
    <input
        type="radio"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($radioId) id="{{ $radioId }}" @endif
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if($hxVals) hx-vals='{!! $hxVals !!}' @endif
        {{ $attributes->except('hx-vals')->merge(['class' => 'tw:radio tw:radio-primary' . $sizeClass]) }}
    />
    @if($resolvedLabel)
        <span class="tw:label-text">{{ $resolvedLabel }}</span>
    @elseif(!$slot->isEmpty())
        <span class="tw:label-text">{{ $slot }}</span>
    @endif
@if($hasLabel)
</label>
@endif
