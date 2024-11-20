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

                    @includeIf("menu::partials.leftnav.".$menuItem['type'], ["menuItem" => $menuItem, "module" => $module, "action" => $action])

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
                            @if($showSettingsIndicator)
                                <span class='label label-primary feature-label'>New</span>
                            @endif
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
