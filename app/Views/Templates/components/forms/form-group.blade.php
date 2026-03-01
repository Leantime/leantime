{{-- Backward-compat wrapper: maps old API -> forms.form-field naming-doc API --}}
@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'inline' => false,
    'labelWidth' => null,
    'labelPosition' => 'top',
    'validationState' => null,
])

<x-globals::forms.form-field
    :label-text="$label"
    :name="$name"
    :required="$required"
    :validation-text="$error"
    :validation-state="$validationState"
    :caption="$help"
    :inline="$inline"
    :label-width="$labelWidth"
    :label-position="$labelPosition"
    {{ $attributes }}
>
    @if(isset($leadingVisual))
        <x-slot:leadingVisual>{{ $leadingVisual }}</x-slot:leadingVisual>
    @endif
    @if(isset($trailingVisual))
        <x-slot:trailingVisual>{{ $trailingVisual }}</x-slot:trailingVisual>
    @endif
    {{ $slot }}
</x-globals::forms.form-field>
