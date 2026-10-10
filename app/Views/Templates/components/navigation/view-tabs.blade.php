@props([
    'tabs' => [],     // [['url' => …, 'label' => … (raw HTML: icon + text), 'active' => bool], …] — the page's views
    'label' => null,  // accessible name of the view nav; defaults to the tab labels joined by " / "
])

{{--
    navigation.view-tabs — the bar between the page header and the content box: the page's VIEWS on the
    left (Kanban / Table / List, Wall / Kanban, Week / List…) and its ACTIONS on the right (New, Filter,
    Group By, view settings, export). Mount it inside .maincontent, before .maincontentinner. A page
    without views passes no tabs and gets just the right-aligned actions.

        <x-global::navigation.view-tabs :tabs="[
            ['url' => BASE_URL.'/ideas/showBoards', 'label' => __('buttons.idea_wall'), 'active' => true],
            ['url' => BASE_URL.'/ideas/advancedBoards', 'label' => __('buttons.idea_kanban'), 'active' => false],
        ]">
            <x-slot:actions>
                <x-global::forms.button tag="a" link="#/ideas/ideaDialog" contentRole="primary">…</x-global::forms.button>
                <x-global::actions.dropdown variant="filter">…</x-global::actions.dropdown>
            </x-slot:actions>
        </x-global::navigation.view-tabs>

    Filter / view-setting dropdowns in the actions get the white pill look with `trigger-class="btn-link"`
    (or class="btn-link" on their trigger slot); primary buttons stay primary. Views are links (server-side
    active), not an in-page tablist — for in-page panels use navigation.tabs.
--}}
@php
    $navLabel = $label ?? implode(' / ', array_map(fn ($tab) => trim(strip_tags((string) $tab['label'])), $tabs));
@endphp
<div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
    @if (count($tabs) > 0)
        <nav class="lt-tabs-group" aria-label="{{ $navLabel }}">
            <ul>
                @foreach ($tabs as $tab)
                    <li class="{{ ! empty($tab['active']) ? 'active' : '' }}">
                        <a href="{{ $tab['url'] }}"
                           @if (! empty($tab['active'])) aria-current="page" @endif
                           preload="mouseover">
                            {!! $tab['label'] !!}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

    @isset($actions)
        <div class="lt-tabs-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
