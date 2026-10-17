{{--
    DEPRECATED: use layout.page-header. Kept through 3.12 for plugin releases that still call
    <x-global::pageheader> (CustomFields, GoogleCalendar, Copilot, StrategyPro, PgmPro); remove in the release after.

    Shared page header card.

    Props:
      icon     string  Font Awesome classes for the header icon (default 'fa fa-home').

    Slots:
      default  The title block (h5 subtitle + h1), rendered inside .pagetitle.
      status   Optional badge(s) next to the title ("Active", "Trial", "On track").
      actions  Optional right-hand cluster (page actions, ⋮ menus, meta). Rendered
               as a SIBLING of .pagetitle so the header's flex layout pushes it to
               the right edge — putting it inside .pagetitle (or wrapping the whole
               header in a bootstrap .row/.col grid) drags in the grid's negative
               margins and knocks the header card out of line with the content
               card below it.
--}}
@props([
    'icon' => 'fa fa-home',
])

@dispatchEvent('beforePageHeaderOpen')

<div {{ $attributes->merge([ 'class' => 'pageheader' ]) }}>

    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon"><span class="{{ $icon }}"></span></div>

    <div class="pagetitle">
        {{ $slot }}
    </div>

    @isset($status)
        <div class="pageheader-status">{{ $status }}</div>
    @endisset

    @isset($actions)
        <div {{ $actions->attributes->merge(['class' => 'pageheader-right']) }}>{{ $actions }}</div>
    @endisset

    @dispatchEvent('beforePageHeaderClose')

</div>

@dispatchEvent('afterPageHeaderClose')
