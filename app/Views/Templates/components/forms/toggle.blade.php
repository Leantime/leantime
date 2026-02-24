{{-- Stub: forms/toggle — split from checkbox toggle prop --}}
@props([
    'name',
    'labelText' => null,
    'checked' => false,
    'value' => '1',
    'id' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $resolvedLabel = $labelText;
    $toggleId = $id ?? $name;
    $hasLabel = $resolvedLabel || !$slot->isEmpty();
@endphp

@if($hasLabel)
<label class="tw:label tw:cursor-pointer tw:justify-start tw:gap-3">
@endif
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $toggleId }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'tw:toggle tw:toggle-primary']) }}
    />
    @if($resolvedLabel)
        <span class="tw:label-text">{{ $resolvedLabel }}</span>
    @elseif(!$slot->isEmpty())
        <span class="tw:label-text">{{ $slot }}</span>
    @endif
@if($hasLabel)
</label>
@endif
