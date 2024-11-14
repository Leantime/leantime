@props([
    "size",
])

<div style="
        display:inline-block;
        width:{{ $size }};
        height: {{ $size }};
        vertical-align: middle;
        background:url({{ BASE_URL }}/dist/images/loading-animation.svg);
        background-size: contain;"></div>
