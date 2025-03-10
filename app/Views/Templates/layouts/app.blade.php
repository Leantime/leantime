<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">

<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body data-theme="leantime" hx-ext="preload">

    @include('global::sections.appAnnouncement')

    <div id="global-loader" class="full-width-loader">
        <div class="indeterminate" style=""></div>
    </div>

    <div class="mainwrapper menu{{ session('menuState') ?? 'closed' }}">

        <div class="header">

            <div class="headerinner">
                <a class="btnmenu" href="javascript:void(0);"></a>

                <a class="barmenu btn btn-ghost btn-circle" href="javascript:void(0);">
                    <span class="fa fa-bars"></span>
                </a>

                <div class="logo btn btn-ghost">
                    <a href="{{ BASE_URL }}"
                        style="background-image: url('{{ BASE_URL }}/dist/images/logo.svg')">&nbsp;</a>
                </div>

                @include('menu::headMenu')
            </div><!-- headerinner -->


        </div><!-- header -->

        <div class="overlay relative mt-lg">
            <div class="leftpanel">
                <div class="leftmenu">
                    @include('menu::menu')
                </div><!-- leftmenu -->
            </div>
            <div class="rightpanel {{ $section }}">
                <div class="primaryContent">
                    @yield('content')
                    <div class="clearfix"></div>
                    @include('global::sections.footer')
                </div>
            </div>
        </div><!-- rightpanel -->

    </div><!-- mainwrapper -->
    <div id="modal-wrapper" hx-indicator="#global-loader">
        <x-global::content.modal.modal-buttons />

        <x-global::content.modal id="main-page-modal" />
    </div>

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::includes.helpermodal')
</body>

</html>
