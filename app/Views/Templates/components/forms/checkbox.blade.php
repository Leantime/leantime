@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => '1',
    'toggle' => false,
])

<label class="tw:label tw:cursor-pointer tw:justify-start tw:gap-3">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => $toggle ? 'tw:toggle tw:toggle-primary' : 'tw:checkbox tw:checkbox-primary']) }}
    />
    @if($label)
        <span class="tw:label-text">{{ $label }}</span>
    @endif
</label>
