@props([
    'name',
    'label' => null,
    'value',
    'checked' => false,
])

<label class="tw:label tw:cursor-pointer tw:justify-start tw:gap-3">
    <input
        type="radio"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'tw:radio tw:radio-primary']) }}
    />
    @if($label)
        <span class="tw:label-text">{{ $label }}</span>
    @endif
</label>
