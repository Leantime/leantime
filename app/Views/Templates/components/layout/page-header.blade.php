{{--
    layout.page-header — the header card at the top of a page: icon, a small line above the
    title (eyebrow), the title, and a right-hand cluster.

    Header rule (2026-08-03): LEFT = orientation (where you are), RIGHT = information about it
    (meta, then status, then the ⋮ menu), one vertically-centred row.

    Props:
      icon     string  Font Awesome classes for the round icon, e.g. 'fa fa-address-book'.
                       Omit for no icon (login, install).
      eyebrow  string  The small line above the title ("Administration", "Client / Project").
                       Escaped; use the `eyebrow` slot for markup.

    Slots:
      default   The title text. Rendered inside the <h1>; markup is allowed, so escape
                user-provided text at the call site.
      eyebrow   Eyebrow with markup (instead of the prop).
      switcher  A `subjectSwitcher` in place of the plain <h1> (it renders its own <h1>).
                Define it inside an @if to fall back to the default-slot title.
      status    Badges right after the title ("Active", "On track").
      actions   The right-hand cluster: meta text, buttons, the header-menu ⋮ dropdown.

    Plugin hooks (named after the page, e.g. leantime.domain.clients.templates.showAll.*):
    beforePageHeaderOpen, afterPageHeaderOpen, beforePageHeaderClose, afterPageHeaderClose.

    Anything else (id, class, data-*) passes through to the header.
--}}
@props([
    'icon' => null,
    'eyebrow' => null,
])

@dispatchEvent('beforePageHeaderOpen')

<div {{ $attributes->class(['pageheader']) }}>

    @dispatchEvent('afterPageHeaderOpen')

    @if ($icon)
        <div class="pageicon"><span class="{{ $icon }}"></span></div>
    @endif

    <div class="pagetitle">
        @if (trim((string) $eyebrow) !== '')
            <h5>{{ $eyebrow }}</h5>
        @endif
        @isset($switcher)
            {{ $switcher }}
        @else
            <h1>{{ $slot }}</h1>
        @endisset
    </div>

    @isset($status)
        <div class="pageheader-status">{{ $status }}</div>
    @endisset

    @isset($actions)
        <div {{ $actions->attributes->class(['pageheader-right']) }}>{{ $actions }}</div>
    @endisset

    @dispatchEvent('beforePageHeaderClose')

</div>

@dispatchEvent('afterPageHeaderClose')
