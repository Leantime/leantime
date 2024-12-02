@if(!isset($menuItem['role']) || $login::userIsAtLeast($menuItem['role'] ?? 'editor'))

    <li class="submenuToggle">
        <a href="javascript:void(0);"
           @if ( $menuItem['visual'] !== 'always' )
               onclick="leantime.menuController.toggleSubmenu('{{ $menuItem['id'] }}')"
            @endif
        >
            <i class="submenuCaret fa fa-angle-{{ $menuItem['visual'] == 'closed' ? 'right' : 'down' }}"
               id="submenu-icon-{{ $menuItem['id'] }}"></i>
            <strong>{!! __($menuItem['title']) !!}</strong>
        </a>
    </li>
    <ul id="submenu-{{ $menuItem['id'] }}" class="submenu {{ $menuItem['visual'] == 'closed' ? 'closed' : 'open' }}">
        @foreach ($menuItem['submenu'] as $subkey => $submenuItem)
            @switch ($submenuItem['type'])
                @case('header')
                    @include("menu::partials.leftnav.header", ["menuItem" => $submenuItem, "module" => $module, "action" => $action])
                    @break
                @case('item')
                    @include("menu::partials.leftnav.item", ["menuItem" => $submenuItem, "module" => $module, "action" => $action])
            @endswitch
        @endforeach
    </ul>

@endif

