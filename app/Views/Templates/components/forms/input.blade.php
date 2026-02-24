{{-- Backward-compat wrapper: maps old API → forms.text-input naming-doc API --}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
    'error' => null,
    'placeholder' => '',
    'id' => null,
    'disabled' => false,
    'readonly' => false,
    'inline' => false,
    'labelWidth' => null,
    'help' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'autocomplete' => null,
    'inputSize' => null,
    'bare' => false,
])

@php
    $scale = match($inputSize) {
        'sm' => 's',
        'lg' => 'l',
        default => null,
    };
@endphp

<x-globals::forms.text-input
    :name="$name"
    :label-text="$label"
    :type="$type"
    :value="$value"
    :required="$required"
    :validation-text="$error"
    :placeholder="$placeholder"
    :id="$id"
    :disabled="$disabled"
    :readonly="$readonly"
    :inline="$inline"
    :label-width="$labelWidth"
    :caption="$help"
    :min="$min"
    :max="$max"
    :step="$step"
    :autocomplete="$autocomplete"
    :scale="$scale"
    :bare="$bare"
    {{ $attributes }}
>
    @isset($addon)
        <x-slot:addon>{{ $addon }}</x-slot:addon>
    @endisset
</x-globals::forms.text-input>
