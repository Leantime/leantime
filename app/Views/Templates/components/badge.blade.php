@props([
    'asLink' => false,
    'color' => match ($color ?? null) {
        'yellow' => ['tw-yellow-500', 'tw-bg-yellow-500'],
        'red' => ['tw-red-500', 'tw-bg-red-500'],
        'blue' => ['tw-blue-500', 'tw-bg-blue-500'],
        'green' => ['tw-green', 'tw-bg-green'],
        'primary' => ['tw-primary', 'tw-bg-primary'],
        'gray' => ['tw-gray-500', 'tw-bg-gray-500'],
        default => ['tw-gray-500', 'tw-bg-gray-500'],
    },
])

@if ($asLink)
    <a
@else
    <span
@endif
{{ $attributes->merge([
    'class' => 'tw-px-2.5 tw-py-0.5 tw-rounded ' . ($asLink ? 'text-white ' . $color[1] : $color[0] . ' tw-bg-gray-300'),
] + ($asLink ? ['href' => $url ?? '#'] : [])) }}>
    {{ $slot }}
@if ($asLink)
    </a>
@else
    </span>
@endif
