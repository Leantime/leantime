<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">
<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body class="" hx-ext="preload, head-support">

    @include('global::sections.appAnnouncement')

    {{-- Loading indicator for SPA navigation --}}
    <div id="page-loading" class="htmx-indicator"
         style="position:fixed;top:0;left:0;z-index:9999;height:3px;width:100%;background:var(--accent1);pointer-events:none;"></div>

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
            <div class="rightpanel {{ $section }}"
                 hx-boost="true"
                 hx-target=".primaryContent"
                 hx-select=".primaryContent"
                 hx-swap="outerHTML show:window:top"
                 hx-indicator="#page-loading">
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

    {{-- Global modal (native <dialog>) — replaces nyroModal --}}
    <dialog id="global-modal" class="tw:modal">
        <div class="tw:modal-box tw:max-w-3xl tw:p-0" id="global-modal-box">
            <form method="dialog" style="margin:0;position:absolute;right:8px;top:8px;z-index:10;">
                <button class="tw:btn tw:btn-sm tw:btn-circle tw:btn-ghost" aria-label="Close">
                    <i class="fa fa-xmark"></i>
                </button>
            </form>
            <div id="global-modal-content" class="nyroModalCont" style="padding:20px;">
                <div style="display:flex;justify-content:center;padding:40px;">
                    <span class="tw:loading tw:loading-spinner tw:loading-lg"></span>
                </div>
            </div>
        </div>
        <form method="dialog" class="tw:modal-backdrop"><button>close</button></form>
    </dialog>

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::helpermodal')
</body>

</html>
