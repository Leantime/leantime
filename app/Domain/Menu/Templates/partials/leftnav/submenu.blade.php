<li class="submenuToggle">
    <a href="javascript:void(0);" class="flex justify-between items-center"
        @if ($menuItem['visual'] !== 'always') onclick="leantime.menuController.toggleSubmenu('{{ $menuItem['id'] }}')" @endif>
        <strong>{!! __($menuItem['title']) !!}</strong>
        <i class="submenuCaret fa fa-angle-{{ $menuItem['visual'] == 'closed' ? 'right' : 'down' }}"
            id="submenu-icon-{{ $menuItem['id'] }}"></i>
    </a>

    <ul id="submenu-{{ $menuItem['id'] }}" class="ml-4 submenu {{ $menuItem['visual'] == 'closed' ? 'closed' : 'open' }}">
        @foreach ($menuItem['submenu'] as $subkey => $submenuItem)
            @switch ($submenuItem['type'])
                @case('header')
                    @include('menu::partials.leftnav.header', [
                        'menuItem' => $submenuItem,
                        'module' => $module,
                        'action' => $action,
                    ])
                @break

                @case('item')
                    @include('menu::partials.leftnav.item', [
                        'menuItem' => $submenuItem,
                        'module' => $module,
                        'action' => $action,
                    ])
                @break 
            @endswitch 
        @endforeach
    </ul>
</li>
