<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">
<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body>
    <div class="mainwrapper menu{{ $_SESSION['menuState'] }}">

        <div class="leftpanel">

            <a class="barmenu" href="javascript:void(0);">
                <span class="fa fa-bars"></span>
            </a>

            <div class="logo">
                <a
                    href="{{ BASE_URL }}"
                    style="background-image: url({{{ str_replace('http:', '', $_SESSION['companysettings.logoPath']) }}}"
                >&nbsp;</a>
            </div>

            <div class="leftmenu">
                @include('menu::menu')
            </div><!-- leftmenu -->

        </div><!-- leftpanel -->

        <div class="header">

            <div class="headerinner">
                @include('menu::headMenu')
            </div><!-- headerinner -->

        </div><!-- header -->

        <div class="rightpanel" style="position: relative">
            @isset($action, $module)
                @include("$module::$action")
            @else
                @yield('content')
            @endisset

            @include('global::sections.footer')
        </div><!-- rightpanel -->

    </div><!-- mainwrapper -->

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::helpermodal')
</body>

</html>
