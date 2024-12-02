<li>
    <details {{ $menuItem['visual'] == 'closed' ? '' : 'open' }}>
        <summary onclick="leantime.menuController.toggleSubmenu('{{ $menuItem['id'] }}', this.parentNode.open ? 'open' : 'closed')">
            <strong>{!! __($menuItem['title']) !!}</strong>
        </summary>
        <ul id="submenu-{{ $menuItem['id'] }}" class="">
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
    </details>
</li>
