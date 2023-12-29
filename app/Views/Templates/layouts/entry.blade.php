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

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">

        <div class="logo tw-absolute tw-top-[50px] tw-left-0 tw-ml-[100px] tw-p-0">
            <a href="{!! BASE_URL !!}" target="_blank"><img src="{{ BASE_URL }}/dist/images/logo.svg" /></a>
        </div>

        <div class="row">
            <div class="col-md-12" style="position:relative;">
                <h1 class="mainWelcome">
                    @dispatchFilter('welcomeText', $language->__("headlines.welcome_back"))
                </h1>
                <span class="iq-objects-04 iq-fadebounce">
                    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight">

        <div class="regpanel">
            <div class="regpanelinner">

                @if($logoPath != '')
                    <a href="{!! BASE_URL !!}" target="_blank">
                        <img src="{{{ $logoPath }}}" class="tw-h-full "/>
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
