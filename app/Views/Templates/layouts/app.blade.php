<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">
<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body class="" hx-ext="preload">

    @include('global::sections.appAnnouncement')

    <div class="mainwrapper menu{{ session("menuState") ?? "closed" }}">

        <div class="header">

            <div class="headerinner">
                <a class="btnmenu" href="javascript:void(0);"></a>

                <a class="barmenu" href="javascript:void(0);">
                    <span class="fa fa-bars"></span>
                </a>

                <div class="logo">
                    <a
                        href="{{ BASE_URL }}"
                        style="background-image: url('{{ BASE_URL }}/dist/images/logo.svg')"
                    >&nbsp;</a>
                </div>

                @include('menu::headMenu')
            </div><!-- headerinner -->

        </div><!-- header -->



        <div class="overlay" style="position: relative">
            <div class="leftpanel">
                <div class="leftmenu">
                    @include('menu::menu')
                </div><!-- leftmenu -->
            </div>
            <div class="rightpanel {{ $section }}">
                <div class="primaryContent">
                    @isset($action, $module)
                        @include("$module::$action")
                    @else
                        @yield('content')
                    @endisset
                    <div class="clearfix"></div>
                    @include('global::sections.footer')
                </div>

            </div>

        </div><!-- rightpanel -->

    </div><!-- mainwrapper -->

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::helpermodal')
</body>

</html>
