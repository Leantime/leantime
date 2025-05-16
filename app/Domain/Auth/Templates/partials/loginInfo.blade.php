@dispatchEvent('beforeUserinfoMenuOpen')

<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')
    @if(session()->exists("companysettings.logoPath") && session("companysettings.logoPath") !== false && session("companysettings.logoPath") !== '')
        <a href='{{ BASE_URL }}/users/editOwn/' preload="mouseover" class="dropdown-toggle profileHandler includeLogo" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
            <img src="{{ session("companysettings.logoPath") }}" class="logo tw-pl-1" />
        </a>
    @else
        <a href='{{ BASE_URL }}/users/editOwn/' preload="mouseover" class="dropdown-toggle profileHandler" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
        </a>
    @endif
    <ul class="dropdown-menu">
        @dispatchEvent('afterUserinfoDropdownMenuOpen')
        <li>
            <a href='{{ BASE_URL }}/users/editOwn/' preload="mouseover">
                {!! __("menu.my_profile") !!}
            </a>
        </li>
        @dispatchEvent('afterMyProfile')
        <li>
            <a href='{{ BASE_URL }}/users/editOwn#theme' preload="mouseover">
                {!! __("menu.theme") !!}
            </a>
        </li>
        @dispatchEvent('afterTheme')
        <li>
            <a href='{{ BASE_URL }}/users/editOwn#settings' preload="mouseover">
                {!! __("menu.settings") !!}
            </a>
        </li>
        @dispatchEvent('afterSettings')

        <li class="border">
            <a href='{{ BASE_URL }}/auth/logout'>
                {!! __("menu.sign_out") !!}
            </a>
        </li>
        @dispatchEvent('beforeUserinfoDropdownMenuClose')
    </ul>
   @dispatchEvent('beforeUserinfoMenuClose')
</div>
@dispatchEvent('afterUserinfoMenuClose')
