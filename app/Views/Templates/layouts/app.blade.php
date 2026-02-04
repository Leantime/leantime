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

    {{-- Toast notifications --}}
    @if(session()->has('toast'))
        @php
            $toastMessage = session('toast')['message'] ?? session('toast.message', '');
            $toastType = session('toast')['type'] ?? session('toast.type', 'success');
        @endphp
        @if($toastMessage)
            <div
                class="toast-notification toast-notification--{{ $toastType }}"
                role="alert"
                aria-live="polite"
                aria-atomic="true"
                data-toast
            >
                <div class="toast-notification__content">
                    @if($toastType === 'success')
                        <svg class="toast-notification__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @elseif($toastType === 'error')
                        <svg class="toast-notification__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @else
                        <svg class="toast-notification__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 6V10M10 14H10.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    @endif
                    <p class="toast-notification__message">{{ $toastMessage }}</p>
                </div>
                <button
                    type="button"
                    class="toast-notification__dismiss"
                    aria-label="Close notification"
                    onclick="this.closest('[data-toast]').remove()"
                >×</button>
            </div>
        @endif
    @endif
</body>

</html>
