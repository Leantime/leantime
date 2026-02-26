@props([
    'name',
    'label' => null,
    'labelText' => null,
    'value',
    'checked' => false,
    'id' => null,
    'disabled' => false,
])

@php
    $resolvedLabel = $labelText ?? $label;
    $radioId = $id ?? null;
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
        {{ $attributes->except('hx-vals')->merge(['class' => 'tw:radio tw:radio-primary']) }}
    />
    @if($resolvedLabel)
        <span class="tw:label-text">{{ $resolvedLabel }}</span>
    @elseif(!$slot->isEmpty())
        <span class="tw:label-text">{{ $slot }}</span>
    @endif
@if($hasLabel)
</label>
@endif
