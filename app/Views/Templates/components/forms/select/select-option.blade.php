@props([
    'value' => '',
    'selected' => false
])

<option value="{{ $value }}" {{ $selected ? 'selected' : '' }}>
    {!! $slot !!}
</option>