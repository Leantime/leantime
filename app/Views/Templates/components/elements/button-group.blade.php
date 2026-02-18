@props([
    'vertical' => false,
])

<div {{ $attributes->merge([
    'class' => 'btn-group' . ($vertical ? ' btn-group-vertical' : ''),
]) }}>
    {{ $slot }}
</div>
