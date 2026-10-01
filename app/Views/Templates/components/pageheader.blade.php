{{--
    Shared page header card.

    Slots:
      default  The title block (h5 subtitle + h1), rendered inside .pagetitle.
      actions  Optional right-hand cluster (page actions, ⋮ menus, meta). Rendered
               as a SIBLING of .pagetitle so the header's flex layout pushes it to
               the right edge — putting it inside .pagetitle (or wrapping the whole
               header in a bootstrap .row/.col grid) drags in the grid's negative
               margins and knocks the header card out of line with the content
               card below it.
--}}
@dispatchEvent('beforePageHeaderOpen')

<div {{ $attributes->merge([ 'class' => 'pageheader' ]) }}>

    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon"><span class="{{ $icon ?? 'fa fa-home'}}"></span></div>

    <div class="pagetitle">
        {{ $slot }}
    </div>

    @isset($actions)
        <div {{ $actions->attributes->merge(['class' => 'pageheader-right']) }}>{{ $actions }}</div>
    @endisset

    @dispatchEvent('beforePageHeaderClose')

</div>

@dispatchEvent('afterPageHeaderClose')
