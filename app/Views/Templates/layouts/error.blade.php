<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}" data-theme="{{ session('usersettings.colorMode', 'light') }}">
<head>
    @include('global::sections.header')
    <style>
        .leantimeLogo { position: fixed; bottom: 10px; right: 10px; }
    </style>
    @stack('styles')
</head>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm tw:p-[10px]" style="background:var(--header-gradient)">
    <a href="{!! BASE_URL !!}" target="_blank">
        <img src="{{ BASE_URL }}/dist/images/logo.svg" class="tw:h-full "/>
    </a>
</div>

<div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-[1fr_2fr]" style="height:100%; width: 99%;">
    <div class="hidden-phone regLeft">

        <div class="logo">
            <a href="{!! BASE_URL !!}" target="_blank"><img src="{{ BASE_URL }}/dist/images/logo.svg" /></a>
        </div>

        <div class="welcomeContent">
                <h1 class="mainWelcome">
                    Oops, something is off.
                </h1>
        </div>
    </div>
    <div class="regRight">

        <div class="regpanel">
            <div class="regpanelinner">

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
