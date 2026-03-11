<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}" data-theme="{{ session('usersettings.colorMode', 'light') }}">
<head>
    @include('global::sections.header')

    @stack('styles')
    <style>
        .leantimeLogo { position: fixed; bottom: 10px; right: 10px; }

        .regcontent {
            width: auto;
        }
    </style>
</head>

<body class="loginpage" style="height:100%; ">
<div class="" style="background:url({{BASE_URL}}/assets/images/spotlightBg.png); background-size: cover; height:100%; background-attachment: fixed;">
    <div style="    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.2);
    backdrop-filter: var(--glass-blur-subtle);
    -webkit-backdrop-filter: var(--glass-blur-subtle);
    padding-top: 150px;
    overflow: hidden;">
        <div class="regpanel" style="
         margin: auto;
        background: var(--color-bg-card);
        max-width: 50%;
        box-shadow: 0px 0px 50px rgba(0,0,0,0.4);
        border-radius: 10px;
        overflow:hidden;">
            <div class="row">
                <div class="col-md-7">
                    <div class="regpanelinner" style="padding:30px;">

                        <a href=""><img src="{{ BASE_URL }}/dist/images/logo_blue.svg" style="width:50%;"/></a><br /><br />

                        @isset($action, $module)
                            @include("$module::$action")
                        @else
                            @yield('content')
                        @endisset
                    </div>
                </div>
                <div class="col-md-5 regLeft" style="position:relative; background:var(--element-gradient); padding:20px; height:auto;">

                    <h1 style="position: relative; z-index: 5; width:100%; font-size:16px;">
                        <span style="font-size:26px">Sign Up</span><br /><br />
                        No set up required!<br />Enjoy the extra time. 🎉<br />
                    </h1>

                </div>
            </div>

        </div>
    </div>
</div>

@include('global::sections.pageBottom')
@stack('scripts')
</body>

</html>
