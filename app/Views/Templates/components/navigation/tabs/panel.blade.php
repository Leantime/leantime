{{--
    One tab panel paired with x-global::navigation.tabs.tab by `name`.

    Renders the full panel contract (id/role/aria-labelledby/tabindex +
    data attributes) so consumers never hand-write it. Works in both
    component modes: `toggle` (the tabs script shows/hides these) and
    `manual` (the page's own JS moves them — pass extra classes/attrs
    through, e.g. class="ra-page" inside a deck track).

    Props:
      name   string  REQUIRED. Must match the paired tab's name.
      group  string  Usually inherited via @aware from the parent tabs
                     component; pass explicitly when the panel renders
                     outside the component tree (deck tracks, separate
                     partials).
--}}
@props([
    'name',
    'group' => null,
])
@aware(['group' => null])

<div id="{{ $group }}-panel-{{ $name }}"
     role="tabpanel"
     aria-labelledby="{{ $group }}-tab-{{ $name }}"
     tabindex="0"
     data-tabs-panel="{{ $name }}"
     data-tabs-group="{{ $group }}"
     {{ $attributes }}>
    {{ $slot }}
</div>
