@props([
    'asLink' => false,
    'color' => match ($color ?? null) {
        'yellow' => ['yellow-500', 'bg-yellow-500'],
        'red' => ['red-500', 'bg-red-500'],
        'blue' => ['blue-500', 'bg-blue-500'],
        'green' => ['green', 'bg-green'],
        'primary' => ['primary', 'bg-primary'],
        'gray', default => ['gray-900', 'bg-gray-900'],
    },
])

@if ($asLink)
<a
@else
<span
@endif
{{ $attributes->merge([
    'class' => 'mix-blend-difference px-2.5 py-0.5 rounded' . ($asLink ? 'text-white' . $color[1] : $color[0] . 'bg-gray-300'),
] + ($asLink ? ['href' => $url ?? '#'] : [])) }}>
    {{ $slot }}
@if ($asLink)
</a>
@else
</span>
@endif
