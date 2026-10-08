{{--
    Header global search bar.

    One DOM element, two positions: on desktop the <li> is lifted out of the header's float
    flow and centred between the left (work modes) and right (icons) lists by search.css +
    searchController.js; below the desktop breakpoint it is a plain icon in the right-hand
    list that expands a full-width input panel under the header.
--}}
<li class="globalSearch" id="globalSearch">
    <a
        href="javascript:void(0);"
        class="globalSearch__toggle"
        aria-label="{{ __('search.label') }}"
        data-tippy-content="{{ __('search.label') }}"
    ><span class="fa-solid fa-magnifying-glass"></span></a>

    <form
        class="globalSearch__form"
        role="search"
        action="{{ BASE_URL }}/search/show"
        method="get"
        autocomplete="off"
    >
        <span class="fa-solid fa-magnifying-glass globalSearch__icon" aria-hidden="true"></span>
        <input
            type="search"
            name="q"
            id="globalSearchInput"
            class="globalSearch__input"
            placeholder="{{ __('search.placeholder') }}"
            aria-label="{{ __('search.label') }}"
            role="combobox"
            aria-autocomplete="list"
            aria-controls="globalSearchResults"
            aria-expanded="false"
            hx-get="{{ BASE_URL }}/hx/search/quick/get"
            hx-trigger="input changed delay:300ms"
            hx-target="#globalSearchResults"
            hx-sync="this:replace"
            hx-indicator="#globalSearchSpinner"
        />
        <kbd class="globalSearch__kbd" aria-hidden="true">⌘K</kbd>
        <span id="globalSearchSpinner" class="htmx-indicator globalSearch__spinner" aria-hidden="true">
            <span class="fa fa-spinner fa-spin"></span>
        </span>
    </form>

    <div
        class="dropdown-menu globalSearch__results"
        id="globalSearchResults"
        role="listbox"
        aria-label="{{ __('search.label') }}"
    ></div>
</li>
