@dispatchEvent('beforeUserinfoMenuOpen')

<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')
    @php
        $companyLogo = session()->exists("companysettings.logoPath") && session("companysettings.logoPath") !== false && session("companysettings.logoPath") !== ''
            ? session("companysettings.logoPath")
            : null;
    @endphp
    <x-global::actions.dropdown variant="panel" menu-as="ul">
        <x-slot:trigger class="profileHandler {{ $companyLogo ? 'includeLogo' : '' }}" href="{{ BASE_URL }}/users/editOwn/" preload="mouseover">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
            @if ($companyLogo)
                <img src="{{ $companyLogo }}" class="logo tw-pl-1" />
            @endif
        </x-slot:trigger>
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
            @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$admin))
                <a href='{{BASE_URL}}/plugins/marketplace#/help/support' >
                    <span class="fa-solid fa-hand-holding-heart" style="color:#f61067;"></span> {{ __('link.support_us') }}
                </a>
            @else
                <a href='#/help/support'  >
                    <span class="fa-solid fa-hand-holding-heart" style="color:#f61067;"></span> {{ __('link.support_us') }}
                </a>
            @endif
        </li>
        <li class="border">
            <a href='{{ BASE_URL }}/auth/logout'>
                {!! __("menu.sign_out") !!}
            </a>
        </li>
        @dispatchEvent('beforeUserinfoDropdownMenuClose')
    </x-global::actions.dropdown>
@dispatchEvent('beforeUserinfoMenuClose')
</div>
@dispatchEvent('afterUserinfoMenuClose')
