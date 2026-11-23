{{--
    Section tabs inside a card or dialog (navigation.section-tabs): To-Do, project and client settings, Company
    settings, My Profile, project selector. Each tab is one part of the thing you edit.

    Keeps the ul > li > a[href=#id] + div#id markup because plugins print tabs into these sets through hooks:
    a plugin echoes `<li><a href="#x">…</a></li>` into the `tabs` slot and `<div id="x">…</div>` into the
    panels. `core/sectionTabs.js` makes the ARIA tablist out of it (roving tabindex, Arrow/Home/End) and
    shows one panel at a time. New section tabs that plugins don't extend can use navigation.tabs
    variant="underline" instead; both look the same.

    Props:
      label     string  REQUIRED. aria-label for the tab list.
      remember  string  Where the open tab is kept, so a reload shows it again: 'hash' (URL hash; page-level
                        sets whose forms post back to `…#section`), 'query:{param}' (a query parameter),
                        'local:{key}' (localStorage). The URL hash naming a tab always opens that tab.

    Slots:
      tabs      x-global::navigation.section-tabs.tab items (and the hooks plugins print into).
      default   The panels: one `<div id="…">` per tab, direct children of the box.
--}}
@props([
    'label',
    'remember' => null,
])

<div {{ $attributes->merge(['class' => 'tabbedwidget lt-section-tabs']) }}
     data-lt-section-tabs
     @if ($remember) data-tabs-remember="{{ $remember }}" @endif>
    <ul class="lt-section-tabs-nav" aria-label="{{ $label }}">
        {{ $tabs }}
    </ul>
    {{ $slot }}
</div>
