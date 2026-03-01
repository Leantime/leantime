<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}" data-theme="{{ session('usersettings.colorMode', 'light') }}">
<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body class="" hx-ext="preload, head-support" data-module="{{ $module ?? '' }}">

    @include('global::sections.appAnnouncement')

    {{-- Loading indicator for SPA navigation --}}
    <div id="page-loading" class="htmx-indicator" role="status"
         style="position:fixed;top:0;left:0;z-index:9999;height:3px;background:var(--accent1);pointer-events:none;">
        <span class="sr-only">{{ __('label.loading') }}</span>
    </div>

    <div class="mainwrapper menu{{ session("menuState") ?? "closed" }}"
         @if(!empty($themeBgUrl)) style="background-image: url({{ filter_var($themeBgUrl, FILTER_SANITIZE_URL) }}); background-size: var(--background-size, cover); background-position: center; background-repeat: no-repeat;" @endif
    >

        <div class="header">

            <div class="headerinner">
                <a class="btnmenu" href="javascript:void(0);" aria-label="{{ __('menu.toggle_sidebar') }}"></a>

                <a class="barmenu" href="javascript:void(0);" aria-label="{{ __('menu.toggle_navigation') }}">
                    <x-global::elements.icon name="menu" />
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
                <div class="primaryContent" aria-live="polite">
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
    <dialog id="global-modal">
        <div id="global-modal-box">
            <form method="dialog" style="margin:0;position:absolute;right:10px;top:10px;z-index:10;">
                <x-globals::forms.button variant="icon-only" element="button" leading-visual="close" aria-label="Close" />
            </form>
            <div id="global-modal-content">
                <div style="display:flex;justify-content:center;padding:40px;">
                    <x-globals::feedback.skeleton type="text" count="1" />
                </div>
            </div>
        </div>
    </dialog>

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::helpermodal')
</body>

</html>
