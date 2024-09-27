@dispatchEvent('beforeUserinfoMenuOpen')
<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')

    <x-global::actions.dropdown contentRole='ghost' position="left">
        <x-slot:labelText>
            @if(session()->exists("companysettings.logoPath") && session("companysettings.logoPath") !== false)
                <a href="{{ BASE_URL }}/users/editOwn/" class="profileHandler includeLogo">
                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
                    <img src="{{ session("companysettings.logoPath") }}" class="logo"/>
                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
            @else
                <a href="{{ BASE_URL }}/users/editOwn/" class="profileHandler">
                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
            @endif
        </x-slot:labeltext>

        <x-slot:menu>
            <x-global::actions.dropdown.item>
                <a href='{{ BASE_URL }}/users/editOwn/'>
                    {!! __("menu.my_profile") !!}
                </a>
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item>
                <a href='{{ BASE_URL }}/users/editOwn#theme'>
                    {!! __("menu.theme") !!}
                </a>
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item>
                <a href='{{ BASE_URL }}/users/editOwn#settings'>
                    {!! __("menu.settings") !!}
                </a>
            </x-global::actions.dropdown.item>

            <x-global::actions.dropdown.item class="nav-header border">
                {!! __("menu.help_support") !!}
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item>
                <a href='javascript:void(0);' onclick="leantime.helperController.showHelperModal('{{ $modal }}', 300, 500);">
                    {!! __("menu.what_is_this_page") !!}
                </a>
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item>
                <a href='https://leantime.io/knowledge-base' target="_blank">
                    {!! __("menu.knowledge_base") !!}
                </a>
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item>
                <a href='https://discord.gg/4zMzJtAq9z' target="_blank">
                    {!! __("menu.community") !!}
                </a>
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item>
                <a href='https://leantime.io/contact-us' target="_blank">
                    {!! __("menu.contact_us") !!}
                </a>
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item class="border">
                <a href='{{ BASE_URL }}/auth/logout'>
                    {!! __("menu.sign_out") !!}
                </a>
            </x-global::actions.dropdown.item>
        </x-slot:menu>
        
    </x-global::actions.dropdown>

    @dispatchEvent('beforeUserinfoMenuClose')
</div>

@dispatchEvent('afterUserinfoMenuClose')
