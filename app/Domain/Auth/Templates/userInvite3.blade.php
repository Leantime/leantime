@extends($layout)

@section('content')

@include("auth::partials.onboardingProgress", ['percentComplete' => 64, 'current' => 'personalization', 'completed' => ['account', 'theme']])

<h2>🎨 Creating A Comfortable View</h2>
<p>Your favorite color mode and scheme.<br /></p>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">
        <input type="hidden" name="step" value="3" />

        {{  $tpl->displayInlineNotification() }}



        <div>
                <label for="colormode" >{{ __('label.colormode') }}</label>

                <x-globals::selectable :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                    <label for="colormode-light" class="tw:w-[200px]">
                        <i class="fa-solid fa-sun tw:font-xxl"></i>
                    </label>
                </x-globals::selectable>

                <x-globals::selectable :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                    <label for="colormode-light" class="tw:w-[200px]">
                        <i class="fa-solid fa-moon tw:font-xxl"></i>
                    </label>
                </x-globals::selectable>
        </div>
        <br />
        <div>
                <label>Color Scheme</label>
                @foreach($availableColorSchemes as $key => $scheme )
                    <x-globals::selectable class="circle" :selected="($userColorScheme == $key) ? 'true' : ''" :id="$key" :name="'colorscheme'" :value="$key" :label="__($scheme['name'])"  onclick="leantime.snippets.toggleColors('{{ $scheme['primaryColor'] }}','{{ $scheme['secondaryColor'] }}');">
                        <label for="color-{{ $key }}" class="colorCircle"
                               style="background:linear-gradient(135deg, {{ $scheme["primaryColor"] }} 20%, {{ $scheme["secondaryColor"] }} 100%);">
                        </label>
                    </x-globals::selectable>
                @endforeach

        </div>
        <br /> <br />
        <div class="align-right">
            <x-globals::forms.button link="{{ BASE_URL }}/auth/userInvite/{{ $inviteId }}?step=2" type="secondary" style="width:auto; margin-right:10px">Back</x-globals::forms.button>
            <x-globals::forms.button submit type="primary" name="createAccount" class="tw:w-auto" style="width:auto">{{ __("buttons.next") }}</x-globals::forms.button>
        </div>


    </form>

</div>

@endsection
