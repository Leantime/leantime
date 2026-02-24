<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">
<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body class="colorScheme-{{ session('usersettings.colorScheme') ?? 'themeDefault' }} colorMode-{{ session('usersettings.colorMode') ?? 'light' }}" hx-ext="preload">

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
                    @php $current_path = $_SERVER['REQUEST_URI'];@endphp
                    @php $pattern = '/^\/timesheets\/editTime\/[0-9]+$/'; @endphp
                    @if(!(preg_match($pattern, $current_path)))
                        @include('menu::menu')
                    @endif
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
