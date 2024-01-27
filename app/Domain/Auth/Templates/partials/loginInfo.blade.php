@dispatchEvent('beforeUserinfoMenuOpen')

<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')
    @if(isset($_SESSION["companysettings.logoPath"]) && $_SESSION["companysettings.logoPath"] !== false)
        <a href='{{ BASE_URL }}/users/editOwn/' class="dropdown-toggle profileHandler includeLogo" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}&v={{ format($user['modified'])->timestamp() }}" class="profilePicture"/>
            <img src="{{ $_SESSION["companysettings.logoPath"] }}" class="logo"/>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
    @else
        <a href='{{ BASE_URL }}/users/editOwn/' class="dropdown-toggle profileHandler" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}&v={{ format($user['modified'])->timestamp() }}" class="profilePicture"/>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
    @endif
    <ul class="dropdown-menu">
        @dispatchEvent('afterUserinfoDropdownMenuOpen')
        <li>
            <a href='{{ BASE_URL }}/users/editOwn/'>
                {!! __("menu.my_profile") !!}
            </a>
        </li>
        <li>
            <a href='{{ BASE_URL }}/users/editOwn#theme'>
                {!! __("menu.theme") !!}
            </a>
        </li>
        <li>
            <a href='{{ BASE_URL }}/users/editOwn#settings'>
                {!! __("menu.settings") !!}
            </a>
        </li>

        <li class="nav-header border">{!! __("menu.help_support") !!}</li>
        <li>
            <a href='javascript:void(0);'
               onclick="leantime.helperController.showHelperModal('{{ $modal }}', 300, 500);">
                {!! __("menu.what_is_this_page") !!}
            </a>
        </li>
        <li>
            <a href='https://leantime.io/knowledge-base' target="_blank">
                {!! __("menu.knowledge_base") !!}
            </a>
        </li>
        <li>
            <a href='https://discord.gg/4zMzJtAq9z' target="_blank">
                {!! __("menu.community") !!}
            </a>
        </li>
        <li>
            <a href='https://leantime.io/contact-us' target="_blank">
                {!! __("menu.contact_us") !!}
            </a>
        </li>
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
