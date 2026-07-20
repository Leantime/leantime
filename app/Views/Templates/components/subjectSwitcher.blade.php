{{--
    Subject switcher — the "Parent // Current ▾" page-title dropdown.

    Consolidates the `header-title-dropdown` pattern that was hand-rolled inline
    across ~18 templates (To-Dos sprint switcher, canvas boards, wiki, ideas,
    goals, projects…). One place, one markup, reusable.

    Renders an <h1> with an optional parent crumb, a separator, and a Bootstrap
    dropdown whose toggle shows the current subject. The MENU ITEMS are the
    slot — each consumer supplies its own <li> options (they carry their own
    href/onclick), so the switch behavior stays domain-specific while the
    chrome is shared.

    Keeps the existing classes (.header-title-dropdown, .dropdown,
    .dropdown-menu) so the established CSS (dropdowns.css) and Bootstrap
    data-toggle behavior apply unchanged — migrating a consumer is a
    zero-visual-change swap.

    Props:
      parent      string|null  Parent crumb label (e.g. "To-Dos"). May contain
                               markup (rendered raw) since callers pass __().
      parentHref  string|null  Optional link for the parent crumb.
      current     string       The current subject name (escaped — user data safe).
      separator   string       House-style divider. Default "//".
      switchStyle 'legacy'|'pill'  Visual variant. 'legacy' = the established
                               underlined-caret look. 'pill' is reserved for the
                               modern treatment (styled in a follow-up); the prop
                               exists now so it's a one-line flip later.

    Slot: the <li> dropdown-menu items.
--}}
@props([
    'parent' => null,
    'parentHref' => null,
    'current' => '',
    'separator' => '//',
    'switchStyle' => 'legacy',
])

<h1 @class(['subjectSwitcher', 'subjectSwitcher--pill' => $switchStyle === 'pill'])>
    @if (! empty($parent))
        @if (! empty($parentHref))
            <a href="{{ $parentHref }}" class="subjectSwitcher-parent">{!! $parent !!}</a>
        @else
            {!! $parent !!}
        @endif
        <span class="subjectSwitcher-sep" aria-hidden="true">{!! $separator !!}</span>
    @endif
    <span class="dropdown dropdownWrapper">
        <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ $current }}
            <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu">
            {{ $slot }}
        </ul>
    </span>
</h1>
