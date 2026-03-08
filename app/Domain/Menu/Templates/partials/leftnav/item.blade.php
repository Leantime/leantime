@if(!isset($menuItem['role']) || $login::userIsAtLeast($menuItem['role'] ?? 'editor'))

    <li
        @if(
            $module == $menuItem['module']
            && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))
        )
            class='active'
        @endif
    >
        <a href="{{ BASE_URL . $menuItem['href'] }}"
           hx-boost="true"
           hx-target=".primaryContent"
           hx-select=".primaryContent"
           hx-swap="outerHTML show:window:top"
           hx-indicator="#page-loading"
           data-tippy-content="{{ strip_tags(__($menuItem['tooltip'])) }}"
           data-tippy-placement="right"
           preload="mouseover"
           @if(isset($menuItem['attributes']))
               @foreach($menuItem['attributes'] as $key => $value)
                   {{ $key }}="{{ $value }}"
               @endforeach
           @endif
        >
            @if(!empty($menuItem['icon']))
                @if(str_starts_with($menuItem['icon'], 'fa'))
                    <span class="{{ $menuItem['icon'] }}"></span>
                @else
                    <x-globals::elements.icon :name="$menuItem['icon']" />
                @endif
            @endif
            {{ strip_tags(__($menuItem['title'])) }}
        </a>
    </li>

@endif
