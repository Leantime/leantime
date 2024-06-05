@php
    /**
     * @todo Move this to Composer, or find a better
     *       way to add filters for all passed variables
     */
    use Leantime\Domain\Auth\Models\Roles;
    $settingsLink = $tpl->dispatchTplFilter(
        'settingsLink',
        $settingsLink,
        ['type' => $menuType]
    );
@endphp


@dispatchEvent('beforeMenu')

<ul class="nav nav-tabs nav-stacked">

    @dispatchEvent('afterMenuOpen')

    @if ($allAvailableProjects
        || !session()->has("currentProject")
        || $menuType == "personal"
        || $menuType == "company")

        <li class="dropdown scrollableMenu">

            <ul style="display:block;">

                @foreach ($menuStructure as $key => $menuItem)

                    @switch ($menuItem['type'])
                        @case('header')
                            <li>
                                <a href="javascript:void(0);">
                                    <strong>{!! __($menuItem['title']) !!}</strong>
                                </a>
                            </li>
                            @break

                        @case('separator')
                            <li class="separator"></li>
                            @break

                        @case('item')
                            <li
                                @if (
                                    $module == $menuItem['module']
                                    && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))
                                )
                                    class="active"
                                @endif
                            >
                                <a href="{!! BASE_URL . $menuItem['href'] !!}">
                                    {!! __($menuItem['title']) !!}
                                </a>
                            </li>
                            @break

                        @case('submenu')
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
                                            <li class="title">
                                                <a href="javascript:void(0);">
                                                    <strong>{!! __($submenuItem['title']) !!}</strong>
                                                </a>
                                            </li>
                                            @break
                                        @case('item')
                                            <li
                                                @if(
                                                    $module == $submenuItem['module']
                                                    && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))
                                                )
                                                    class='active'
                                                @endif
                                            >
                                                <a href="{{ BASE_URL . $submenuItem['href'] }}"
                                                   data-tippy-content="{{ strip_tags(__($submenuItem['tooltip'])) }}"
                                                   data-tippy-placement="right">
                                                    {!! __($submenuItem['title']) !!}
                                                </a>
                                            </li>
                                    @endswitch
                                @endforeach
                            </ul>
                            @break
                    @endswitch
                @endforeach

                @if ($login::userIsAtLeast(Roles::$manager) && $menuType != 'company' && $menuType != 'personal' && $menuType != 'projecthub')
                    <li class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
                        <a href="{{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }}/{{ session("currentProject") }}">
                            {!! $settingsLink['label']  !!}
                        </a>
                    </li>
                @endif

                @if ($menuType == 'personal')
                    <li class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
                        <a href="@if(isset($settingsLink['url'])) {{ $settingsLink['url']  }} @else {{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }} @endif">
                            {!! __($settingsLink['label'])  !!}
                        </a>
                    </li>
                @endif


            </ul>

        </li>

    @endif

    @dispatchEvent('beforeMenuClose')

</ul>
@dispatchEvent('afterMenuClose')


@once
    @push('scripts')
        <script>
            jQuery(document).ready(function () {
                leantime.menuController.initProjectSelector();
                leantime.menuController.initLeftMenuHamburgerButton();
            });
        </script>
    @endpush
@endonce
