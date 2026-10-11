{{--
    A card inside the content box: border, soft shadow, 16px corners, optional header and footer rows.

    Not the page content box itself (`.maincontentinner`); this is what sits inside it.

    Props:
      tone   string  'default' | 'success' | 'warning' | 'danger' | 'info' | 'muted' | 'empty'.
                     Tones tint the background and border; 'muted' is a quiet hint,
                     'empty' a dashed placeholder for "nothing here yet".
      title  string  Optional header title (escaped). For a richer header (badges,
                     a number on the right) use the `header` slot instead; put the
                     name in an element with class `lt-card-title`.
      flush  bool    Body without padding, for a list or table that runs edge to edge.

    Slots:
      default  The body.
      header   Optional header row content (flex row, centred, 12px gap).
      footer   Optional footer row content.

    Anything else (id, data-*, extra classes) passes through to the card element.
--}}
@props([
    'tone' => 'default',
    'title' => null,
    'flush' => false,
])

@php
    $hasHeader = $title !== null || isset($header);
@endphp

<div {{ $attributes->class([
    'lt-card',
    'lt-card--' . $tone => $tone !== 'default',
    'lt-card--flush' => $flush,
]) }}>
    @if ($hasHeader)
        <div class="lt-card-hd">
            @isset($header)
                {{ $header }}
            @else
                <div class="lt-card-title">{{ $title }}</div>
            @endisset
        </div>
    @endif

    <div @class(['lt-card-bd', 'lt-card-bd--flush' => $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="lt-card-ft">{{ $footer }}</div>
    @endisset
</div>
