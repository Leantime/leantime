@props([
    'link' => '#',
    'type' => 'primary',
    'tag'  => 'a',
])

<{{ $tag }} {{ $attributes->merge([
    'class' => 'btn btn-' . $type
] + ($tag == 'a' ? ['href' => $link] : [])) }}>
    {{ $slot }}
</{{ $tag }}>
