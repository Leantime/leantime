@extends($layout)

@section('content')

@include("auth::partials.onboardingProgress", ['percentComplete' => 64, 'current' => 'personalization', 'completed' => ['account', 'theme']])

<h2>🎨 Creating A Comfortable View</h2>
<p>Your favorite color mode and scheme.<br /></p>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">
        <input type="hidden" name="step" value="3" />

        {{  $tpl->displayInlineNotification() }}



        <div class="row">
            <div class="col-md-12">
                <label for="colormode" >{{ __('label.colormode') }}</label>

                <x-global::selectable :selected="($userColorMode == 'light') ? 'true' : ''" :id="'light'" :name="'colormode'" :value="'light'" :label="'Light'" onclick="leantime.snippets.toggleTheme('light')">
                    <label for="colormode-light" class="tw-w-[200px]">
                        <i class="fa-solid fa-sun tw-font-xxl"></i>
                    </label>
                </x-global::selectable>

                <x-global::selectable :selected="($userColorMode == 'dark') ? 'true' : ''" :id="'dark'" :name="'colormode'" :value="'dark'" :label="'Dark'" onclick="leantime.snippets.toggleTheme('dark')">
                    <label for="colormode-light" class="tw-w-[200px]">
                        <i class="fa-solid fa-moon tw-font-xxl"></i>
                    </label>
                </x-global::selectable>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-md-12">
                <label>Color Scheme</label>
                @foreach($availableColorSchemes as $key => $scheme )
                    <x-global::selectable class="circle" :selected="($userColorScheme == $key) ? 'true' : ''" :id="$key" :name="'colorscheme'" :value="$key" :label="__($scheme['name'])"  onclick="leantime.snippets.toggleColors('{{ $scheme['primaryColor'] }}','{{ $scheme['secondaryColor'] }}');">
                        <label for="color-{{ $key }}" class="colorCircle"
                               style="background:linear-gradient(135deg, {{ $scheme["primaryColor"] }} 20%, {{ $scheme["secondaryColor"] }} 100%);">
                        </label>
                    </x-global::selectable>
                @endforeach

            </div>
        </div>
        <br /> <br />
        <div class="tw-text-right">
            <a href="{{BASE_URL}}/auth/userInvite/{{$inviteId}}?step=2" class="btn btn-secondary" style="width:auto; margin-right:10px">Back</a>
            <input type="submit" name="createAccount" class="tw-w-auto" style="width:auto" value="<?php echo $tpl->language->__("buttons.next"); ?>" />
        </div>


    </form>

</div>

@endsection
