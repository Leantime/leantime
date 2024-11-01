@php
    /**
     * @todo Move this to Composer, or find a better
     *       way to add filters for all passed variables
     */
    use Leantime\Domain\Auth\Models\Roles;
    $settingsLink = $tpl->dispatchTplFilter('settingsLink', $settingsLink, ['type' => $menuType]);
@endphp


@dispatchEvent('beforeMenu')



@dispatchEvent('afterMenuOpen')

@if ($allAvailableProjects || !session()->has('currentProject') || $menuType == 'personal' || $menuType == 'company')


    <ul class="menu" hx-indicator="#global-loader" hx-boost="true">

        @foreach ($menuStructure as $key => $menuItem)
            @switch ($menuItem['type'])
                @case('header')
                    <li class="menu-title">{!! __($menuItem['title']) !!}</li>
                @break

                @case('separator')
                    <li class="separator"></li>
                @break

                @case('item')
                    <li>
                        <a href="{!! BASE_URL . $menuItem['href'] !!}" @if ($module == $menuItem['module'] && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))) class="active" @endif>
                            {!! __($menuItem['title']) !!}
                        </a>
                    </li>
                @break

                @case('submenu')
                    <li>
                        <details {{ $menuItem['visual'] == 'closed' ? '' : 'open' }}>
                            <summary>{!! __($menuItem['title']) !!}</summary>
                            <ul id="submenu-{{ $menuItem['id'] }}" class="before:bg-base-100">
                                @foreach ($menuItem['submenu'] as $subkey => $submenuItem)
                                    @switch ($submenuItem['type'])
                                        @case('header')
                                            <li class="menu-title">{!! __($submenuItem['title']) !!}</li>
                                        @break

                                        @case('item')
                                            <li>
                                                <a href="{{ BASE_URL . $submenuItem['href'] }}"
                                                    data-tippy-content="{{ strip_tags(__($submenuItem['tooltip'])) }}"
                                                    data-tippy-placement="right"
                                                    class="focus:bg-neutral
                                                   @if ($module == $submenuItem['module'] && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))) active @endif
                                                    ">
                                                    {!! __($submenuItem['title']) !!}
                                                </a>
                                            </li>
                                        @endswitch
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @break
                @endswitch
            @endforeach

            @if (
                $login::userIsAtLeast(Roles::$manager) &&
                    $menuType != 'company' &&
                    $menuType != 'personal' &&
                    $menuType != 'projecthub')
                <li
                    class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
                    <a
                        href="{{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }}/{{ session('currentProject') }}">
                        {!! $settingsLink['label'] !!}
                    </a>
                </li>
            @endif

            @if ($menuType == 'personal')
                <li
                    class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
                    <a
                        href="@if (isset($settingsLink['url'])) {{ $settingsLink['url'] }} @else {{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }} @endif">
                        {!! __($settingsLink['label']) !!}
                    </a>
                </li>
            @endif


        </ul>


    @endif

    @dispatchEvent('beforeMenuClose')


    @dispatchEvent('afterMenuClose')


    @once
        @push('scripts')
            <script>
                jQuery(document).ready(function() {
                    leantime.menuController.initProjectSelector();
                    leantime.menuController.initLeftMenuHamburgerButton();
                });
            </script>
        @endpush
    @endonce
