{{--
    elements.list-item — one row of a list: leading icon (or avatar), title, a muted meta line,
    and trailing badges / numbers / actions.

    Put the rows in `<ul class="lt-list">`; rows after the first get a hairline divider.
    Title 14 / semibold, meta 12px muted (type rule in COMPONENTS.md).

    Props:
      title   string  Row title (escaped). For markup use the `title` slot.
      href    string  Makes the title a link. Extra link attributes (class="milestoneModal") go in `linkClass`.
      linkClass string Classes for the title link (modal openers etc.).
      icon    string  Font Awesome classes for the leading icon.
      tone    string  Icon colour: 'default' | 'accent' | 'success' | 'warning' | 'danger' | 'info' | 'muted'.
      as      string  Root tag, 'li' (default) or 'div'.

    Slots:
      title     Title with markup.
      default   The meta line under the title (dates, owner, "overdue since …").
      leading   Replaces the icon: an avatar, a status dot, a checkbox.
      trailing  Right side: badges, a number, a ⋮ menu.

    Anything else (id, data-*, hx-*, extra classes) passes through to the row.
--}}
@props([
    'title' => null,
    'href' => null,
    'linkClass' => '',
    'icon' => null,
    'tone' => 'default',
    'as' => 'li',
])

@php
    $as = $as === 'div' ? 'div' : 'li';
    $tones = ['default', 'accent', 'success', 'warning', 'danger', 'info', 'muted'];
    $tone = in_array($tone, $tones, true) ? $tone : 'default';
    $titleContent = isset($title) && $title instanceof \Illuminate\View\ComponentSlot ? $title : e($title ?? '');
    $hasMeta = trim((string) $slot) !== '';
@endphp

<{{ $as }} {{ $attributes->class(['lt-list-item']) }}>
    @isset($leading)
        <span class="lt-list-item-lead">{{ $leading }}</span>
    @elseif ($icon)
        <span class="lt-list-item-lead lt-list-item-icon--{{ $tone }}"><i class="{{ $icon }}" aria-hidden="true"></i></span>
    @endisset

    <span class="lt-list-item-main">
        <span class="lt-list-item-title">
            @if ($href)
                <a href="{{ $href }}" @if ($linkClass) class="{{ $linkClass }}" @endif>{!! $titleContent !!}</a>
            @else
                {!! $titleContent !!}
            @endif
        </span>
        @if ($hasMeta)
            <span class="lt-list-item-meta">{{ $slot }}</span>
        @endif
    </span>

    @isset($trailing)
        <span class="lt-list-item-trail">{{ $trailing }}</span>
    @endisset
</{{ $as }}>
