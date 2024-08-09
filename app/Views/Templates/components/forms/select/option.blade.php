@props([
    "label" => '',
    "value" => '',
    "selected" => 'false',
    "disabled" => 'false'
])

{
    value: '{{ $value }}',
    label: '{{ $slot }}',
    selected: {{ $selected }},
    disabled: {{ $disabled }},
    customProperties: {
        random: 'I am a custom property'
    }
},
