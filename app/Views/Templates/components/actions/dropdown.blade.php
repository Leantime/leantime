@props([
    'variant' => 'menu',  // menu | header-menu | filter | button | panel | subject
    'label' => null,      // trigger content when no `trigger` slot is given (raw HTML — pass translations, not user data)
    'icon' => null,       // icon class for the trigger (menu / header-menu default to the ⋮ icon)
    'href' => 'javascript:void(0);', // link triggers only: where the trigger points without JS
    'as' => null,         // wrapper tag override (e.g. "li" inside the head menu)
    'menuAs' => null,     // menu tag override ("div" for a filter whose menu holds a form)
    'triggerClass' => '', // extra classes on the trigger
    'menuClass' => '',    // extra classes on the menu (pull-right, editCanvasDropdown extras, …)
    'menuId' => null,     // id of the menu (htmx targets)
    'menuStyle' => null,  // inline style of the menu
    'keepOpen' => false,  // clicks inside the menu don't close it (panels with inputs, tabs or filters)
    'ariaLabel' => null,  // accessible name for icon-only triggers
])

{{--
    actions.dropdown — every Bootstrap menu / nav dropdown that is not a value picker (chips are
    forms.chip, form fields are forms.select). The variant is the established DOM shape, so the
    existing CSS (dropdowns.css) and the Bootstrap 2 data-api keep working unchanged.

      menu         card / row ⋮            div.inlineDropDownContainer > a.ticketDropDown ⋮ > ul
      header-menu  page header ⋮           span.dropdown.headerEditDropdown > a.btn-transparent ⋮ > ul.editCanvasDropdown
      filter       view / group-by / sort  div.btn-group.viewDropDown > button.btn > ul
      button       "New ▾" split buttons   div.btn-group > button.btn-primary + caret > ul
      panel        popover with content    div > a > div.dropdown-menu (inputs, tabs, htmx content)
      subject      page-title switcher     span.dropdown.dropdownWrapper > a.header-title-dropdown ▾ > ul (see subjectSwitcher)

    Slots:
      default  the menu content: raw <li> items (menus) or any markup (panels)
      trigger  optional trigger content; its attributes (data-tippy-content, hx-*, id, …) go on the trigger

    Wrapper attributes (class, style, id, …) pass through. Bootstrap closes every variant on Escape
    and on any click outside.
--}}
@php
    $shapes = [
        'menu' => ['div', 'inlineDropDownContainer', 'a', 'dropdown-toggle ticketDropDown', 'ul', 'dropdown-menu'],
        'header-menu' => ['span', 'dropdown dropdownWrapper headerEditDropdown', 'a', 'dropdown-toggle btn btn-transparent', 'ul', 'dropdown-menu editCanvasDropdown'],
        'filter' => ['div', 'btn-group viewDropDown', 'button', 'btn dropdown-toggle', 'ul', 'dropdown-menu'],
        'button' => ['div', 'btn-group', 'button', 'btn btn-primary dropdown-toggle', 'ul', 'dropdown-menu'],
        'panel' => ['div', '', 'a', 'dropdown-toggle', 'div', 'dropdown-menu'],
        'subject' => ['span', 'dropdown dropdownWrapper', 'a', 'dropdown-toggle header-title-dropdown', 'ul', 'dropdown-menu'],
    ];
    [$wrapperTag, $wrapperClass, $triggerTag, $baseTriggerClass, $menuTag, $baseMenuClass] = $shapes[$variant] ?? $shapes['menu'];
    $wrapperTag = $as ?? $wrapperTag;
    $menuTag = $menuAs ?? $menuTag;

    $triggerIcon = $icon ?? match ($variant) {
        'menu' => 'fa fa-ellipsis-v',
        'header-menu' => 'fa-solid fa-ellipsis-v',
        default => null,
    };

    $triggerAttributes = isset($trigger) ? $trigger->attributes : new \Illuminate\View\ComponentAttributeBag;
    $triggerAttributes = $triggerAttributes->class(array_filter([$baseTriggerClass, $triggerClass]))->merge(array_filter([
        'href' => $triggerTag === 'a' ? $href : null,
        'type' => $triggerTag === 'button' ? 'button' : null,
        'role' => $variant === 'subject' ? 'button' : null,
        'data-toggle' => 'dropdown',
        'aria-haspopup' => $variant === 'subject' ? 'true' : null,
        'aria-expanded' => $variant === 'subject' ? 'false' : null,
        'aria-label' => $ariaLabel,
    ], fn ($value) => $value !== null));
@endphp
<{{ $wrapperTag }} {{ $attributes->class(array_filter([$wrapperClass])) }}>
    <{{ $triggerTag }} {{ $triggerAttributes }}>
        @if (isset($trigger))
            {{ $trigger }}
        @else
            @if ($triggerIcon)<i class="{{ $triggerIcon }}" aria-hidden="true"></i>@endif
            {!! $label !!}
            @if ($variant === 'button') <span class="caret"></span>@endif
            @if ($variant === 'subject') <i class="fa fa-caret-down" aria-hidden="true"></i>@endif
        @endif
    </{{ $triggerTag }}>
    <{{ $menuTag }} class="{{ trim($baseMenuClass.' '.$menuClass) }}"@if ($menuId) id="{{ $menuId }}"@endif @if ($menuStyle) style="{{ $menuStyle }}"@endif @if ($keepOpen) onclick="event.stopPropagation();"@endif>
        {{ $slot }}
    </{{ $menuTag }}>
</{{ $wrapperTag }}>
