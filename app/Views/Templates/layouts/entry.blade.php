<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">
<head>
    @include('global::sections.header')
    <style>
        .leantimeLogo { position: fixed; bottom: 10px; right: 10px; }
    </style>
    @stack('styles')
</head>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm tw-p-[10px]" style="background:var(--header-gradient)">
    <a href="{!! BASE_URL !!}" target="_blank">
        <img src="{{ BASE_URL }}/dist/images/logo.svg" class="tw-h-full "/>
    </a>
</div>

<div class="row" style="height:100vh; width: 95vw;">
    <div class="col-md-4 hidden-phone regLeft">

        <div class="logo">
            <a href="{!! BASE_URL !!}" target="_blank">
                <img src="{{ BASE_URL }}/dist/images/logo.svg" />
            </a>
        </div>

        <div class="welcomeContent">
            @dispatchFilter('welcomeText', '<h1 class="mainWelcome">'.$language->__("headlines.welcome_back").'</h1>')
        </div>

        @dispatchFilter('belowWelcomeText', '')

    </div>
    <div class="col-md-8 col-sm-12 regRight">

        <div class="regpanel">
            <div class="regpanelinner">

                @if($logoPath != '')
                    <a href="{!! BASE_URL !!}" target="_blank">

                        @if(!str_ends_with($logoPath, "dist/images/logo.svg" ))
                            <img src="{{ $logoPath }}" class="tw-h-full "/>
                        @endif
                    </a>
                @endif

                @isset($action, $module)
                    @include("$module::$action")
                @else
                    @yield('content')
                @endisset
            </div>
        </div>

    </div>
    <div class="leantimeLogo">
        <img style="height: 25px;" src="{!! BASE_URL !!}/dist/images/logo-powered-by-leantime.png">
    </div>
</div>

@include('global::sections.pageBottom')
@stack('scripts')
</body>

</html>
