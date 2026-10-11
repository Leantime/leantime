{{--
    A card inside the content box: border, soft shadow, 16px corners, optional header and footer rows.

    Not the page content box itself (`.maincontentinner`); this is what sits inside it.

    Props:
      tone   string  'default' | 'success' | 'warning' | 'danger' | 'info' | 'muted' | 'empty'.
                     Tones tint the background and border; 'muted' is a quiet hint,
                     'empty' a dashed placeholder for "nothing here yet".
      title  string  Optional header title. A string prop is escaped; for markup (a translation
                     with an icon) pass it as the `title` slot instead. The card wraps it
                     in `.lt-card-title`, so callers never write that container.
      flush  bool    Body without padding, for a list or table that runs edge to edge.
      compact bool   Tighter body padding (12px 16px) for rows that repeat down a page.
      as     string  Root tag, default 'div'. Use 'li' when the card is an item of a list.

    Slots:
      default  The body.
      title    The header title when it needs markup (same as the prop, not escaped).
      header   Extra items in the header row after the title: badges, a number, actions.
      footer   Optional footer row content.

    Anything else (id, data-*, extra classes) passes through to the card element.
--}}
@props([
    'tone' => 'default',
    'title' => null,
    'flush' => false,
    'compact' => false,
    'as' => 'div',
])

@php
    $hasHeader = $title !== null || isset($header);
    $as = in_array($as, ['div', 'li', 'section', 'article'], true) ? $as : 'div';
@endphp

<{{ $as }} {{ $attributes->class([
    'lt-card',
    'lt-card--' . $tone => $tone !== 'default',
    'lt-card--flush' => $flush,
]) }}>
    @if ($hasHeader)
        <div class="lt-card-hd">
            @if ($title !== null)
                <div class="lt-card-title">{{ $title }}</div>
            @endif
            {{ $header ?? '' }}
        </div>
    @endif

    <div @class(['lt-card-bd', 'lt-card-bd--flush' => $flush, 'lt-card-bd--compact' => $compact])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="lt-card-ft">{{ $footer }}</div>
    @endisset
</{{ $as }}>
