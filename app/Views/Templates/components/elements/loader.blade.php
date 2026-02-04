@props([
    "size",
])

<div style="
        display:inline-block;
        width:{{ $size }};
        height: {{ $size }};
        vertical-align: middle;
        background:url({{ BASE_URL }}/dist/images/svg/loading-animation.svg);
        background-size: contain;"></div>
