@dispatchEvent('beforeUserinfoMenuOpen')

@php
    $profileImg = BASE_URL . '/api/users?profileImage=' . ($user['id'] ?? -1) . '&v=' . format($user['modified'] ?? -1)->timestamp();
    $hasLogo = session()->exists("companysettings.logoPath") && session("companysettings.logoPath") !== false && session("companysettings.logoPath") !== '';
    $triggerLabel = '<img src="' . e($profileImg) . '" class="profilePicture" />';
    if ($hasLogo) {
        $triggerLabel .= '<img src="' . e(session("companysettings.logoPath")) . '" class="logo tw:pl-1" />';
    }
@endphp

<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')
    <x-globals::elements.dropdown :label="$triggerLabel" buttonClass="profileHandler {{ $hasLogo ? 'includeLogo' : '' }}" align="end">
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
                <a href='#/help/support' >
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
    </x-globals::elements.dropdown>
    @dispatchEvent('beforeUserinfoMenuClose')
</div>
@dispatchEvent('afterUserinfoMenuClose')
