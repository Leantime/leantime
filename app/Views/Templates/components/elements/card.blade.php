{{--
    A card inside the content box: border, soft shadow, 16px corners, optional header and footer rows.

    Not the page content box itself (`.maincontentinner`); this is what sits inside it.

    Props:
      tone   string  'default' | 'accent' | 'success' | 'warning' | 'danger' | 'info' | 'muted' | 'empty'.
                     'accent' is the highlight card (a trial banner, the recommended plan).
                     Tones tint the background and border; 'muted' is a quiet hint,
                     'empty' a dashed placeholder for "nothing here yet".
      title  string  Optional header title. A string prop is escaped; for markup (a translation
                     with an icon) pass it as the `title` slot instead. The card wraps it
                     in `.lt-card-title`, so callers never write that container.
      titleAs string Heading tag for the title, default 'h4' (keeps the section in the heading
                     outline); 'h2'-'h6' or 'div' when the card sits under a deeper heading.
      flush  bool    Body without padding, for a list or table that runs edge to edge.
                     Wins over `compact` when both are set.
      compact bool   Tighter body padding (12px 16px) for rows that repeat down a page.
      as     string  Root tag, default 'div'. Use 'li' when the card is an item of a list.
      selected bool  The picked card in a set of choices (plan, template, route): accent border.
                     The caller owns the input (radio/button) and its aria state.

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
    'titleAs' => 'h4',
    'flush' => false,
    'compact' => false,
    'as' => 'div',
    'selected' => false,
])

@php
    $hasHeader = $title !== null || isset($header);
    $as = in_array($as, ['div', 'li', 'section', 'article'], true) ? $as : 'div';
    $titleAs = in_array($titleAs, ['h2', 'h3', 'h4', 'h5', 'h6', 'div'], true) ? $titleAs : 'h4';
@endphp

<{{ $as }} {{ $attributes->class([
    'lt-card',
    'lt-card--' . $tone => $tone !== 'default',
    'lt-card--flush' => $flush,
    'lt-card--selected' => $selected,
]) }}>
    @if ($hasHeader)
        <div class="lt-card-hd">
            @if ($title !== null)
                <{{ $titleAs }} class="lt-card-title">{{ $title }}</{{ $titleAs }}>
            @endif
            {{ $header ?? '' }}
        </div>
    @endif

    <div @class(['lt-card-bd', 'lt-card-bd--flush' => $flush, 'lt-card-bd--compact' => $compact && ! $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="lt-card-ft">{{ $footer }}</div>
    @endisset
</{{ $as }}>
