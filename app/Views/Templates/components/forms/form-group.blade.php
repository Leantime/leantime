{{-- Backward-compat wrapper: maps old API → forms.form-field naming-doc API --}}
@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'inline' => false,
    'labelWidth' => null,
])

<x-globals::forms.form-field
    :label-text="$label"
    :name="$name"
    :required="$required"
    :validation-text="$error"
    :caption="$help"
    :inline="$inline"
    :label-width="$labelWidth"
    {{ $attributes }}
>{{ $slot }}</x-globals::forms.form-field>
