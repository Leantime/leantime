@dispatchEvent('beforeUserinfoMenuOpen')

@php
    // Client portal users (commenters) get a slimmed-down account page that only
    // exposes personal data + theme; everyone else uses the full /users/editOwn.
    $editOwnBase = session('userdata.role') === \Leantime\Domain\Auth\Models\Roles::$commenter
        ? BASE_URL.'/clientportal/editOwn'
        : BASE_URL.'/users/editOwn';
@endphp

<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')
    @if(session()->exists("companysettings.logoPath") && session("companysettings.logoPath") !== false && session("companysettings.logoPath") !== '')
        <a href='{{ $editOwnBase }}/' preload="mouseover" class="dropdown-toggle profileHandler includeLogo" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
            <img src="{{ session("companysettings.logoPath") }}" class="logo tw-pl-1" />
        </a>
    @else
        <a href='{{ $editOwnBase }}/' preload="mouseover" class="dropdown-toggle profileHandler" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
        </a>
    @endif
    <ul class="dropdown-menu">
        @dispatchEvent('afterUserinfoDropdownMenuOpen')
        <li>
            <a href='{{ $editOwnBase }}/' preload="mouseover">
                {!! __("menu.my_profile") !!}
            </a>
        </li>
        @dispatchEvent('afterMyProfile')
        <li>
            <a href='{{ $editOwnBase }}#theme' preload="mouseover">
                {!! __("menu.theme") !!}
            </a>
        </li>
        @dispatchEvent('afterTheme')
        @if(session('userdata.role') !== \Leantime\Domain\Auth\Models\Roles::$commenter)
            <li>
                <a href='{{ $editOwnBase }}#settings' preload="mouseover">
                    {!! __("menu.settings") !!}
                </a>
            </li>
            @dispatchEvent('afterSettings')
        @endif

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
